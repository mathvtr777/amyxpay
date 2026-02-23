<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido.']);
    exit;
}

// Parse JSON body
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$checkout_id = isset($input['checkout_id']) ? intval($input['checkout_id']) : 0;
$name = trim($input['name'] ?? $input['customer_name'] ?? '');
$document = preg_replace('/\D/', '', $input['document'] ?? $input['cpf'] ?? '');
$amount = floatval($input['amount'] ?? 0);

if (!$checkout_id || !$name || !$document || $amount <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dados inválidos. Preencha todos os campos.']);
    exit;
}

// DB connection
require_once __DIR__ . '/../conectarbanco.php';
require_once __DIR__ . '/../app/Middleware/TenantResolver.php';

$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro de conexão com banco de dados.']);
    exit;
}

// Inicializa Resolver e valida o Domínio do Cliente
$resolver = new \App\Middleware\TenantResolver($conn);
$tenantContext = $resolver->resolve();

// Bloqueia pagamento no dominio principal (SaaS Admin)
if ($tenantContext['type'] === 'saas_admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Vendas desativadas no dominio root.']);
    exit;
}

$tenantUserId = $tenantContext['user_id'];

require_once __DIR__ . '/../app/Services/PlanService.php';
$planService = new \App\Services\PlanService($conn, $tenantUserId);

// Bloqueia checkout se plano expirado ou inativo
if (!$planService->isActive()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'A conta do lojista está inativa ou o plano expirou.']);
    exit;
}

// 1. Fetch checkout info — handle both old schema (key_gateway) and new schema (user_provider_id)
$checkout = null;

// Check if user_provider_id column exists (post-migration)
$col_check = $conn->query("SHOW COLUMNS FROM checkout_build LIKE 'user_provider_id'");
$has_provider_col = ($col_check && $col_check->num_rows > 0);

if ($has_provider_col) {
    $stmt = $conn->prepare("SELECT id, name_produto, valor, user_provider_id, obrigado_page, email FROM checkout_build WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $checkout_id);
    $stmt->execute();
    $checkout = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
else {
    $stmt = $conn->prepare("SELECT id, name_produto, valor, key_gateway, obrigado_page, email FROM checkout_build WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $checkout_id);
    $stmt->execute();
    $checkout = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (!$checkout) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Checkout não encontrado. (Ou não pertence a este lojista)']);
    exit;
}

// 2. Find the provider credentials
$provider_row = null;

if ($has_provider_col && !empty($checkout['user_provider_id'])) {
    // New schema: direct FK to user_providers
    $stmt2 = $conn->prepare("SELECT id, provider_name, api_key, api_token, client_id, client_secret FROM user_providers WHERE id = ? LIMIT 1");
    $stmt2->bind_param("i", $checkout['user_provider_id']);
    $stmt2->execute();
    $provider_row = $stmt2->get_result()->fetch_assoc();
    $stmt2->close();
}

// Fallback: if user_provider_id is NULL (legacy checkouts), find the checkout owner's first active provider
if (!$provider_row) {
    // Find the user who owns this checkout via email or by matching a user in user_providers
    // Try to find via checkout email matching users table
    $checkout_email = $checkout['email'] ?? '';
    if (!empty($checkout_email)) {
        $stmt_user = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        if ($stmt_user) {
            $stmt_user->bind_param("s", $checkout_email);
            $stmt_user->execute();
            $user_row = $stmt_user->get_result()->fetch_assoc();
            $stmt_user->close();
            if ($user_row) {
                $owner_id = $user_row['id'];
                $stmt_fp = $conn->prepare("SELECT id, provider_name, api_key, api_token, client_id, client_secret FROM user_providers WHERE user_id = ? LIMIT 1");
                $stmt_fp->bind_param("s", $owner_id);
                $stmt_fp->execute();
                $provider_row = $stmt_fp->get_result()->fetch_assoc();
                $stmt_fp->close();
            }
        }
    }

    // Last resort: get first available provider in user_providers
    if (!$provider_row) {
        $stmt_any = $conn->prepare("SELECT id, provider_name, api_key, api_token, client_id, client_secret FROM user_providers LIMIT 1");
        $stmt_any->execute();
        $provider_row = $stmt_any->get_result()->fetch_assoc();
        $stmt_any->close();
    }
}

if (!$provider_row) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'error' => 'Nenhum provedor de pagamento configurado. Acesse "Provedores" no painel e adicione suas credenciais.'
    ]);
    exit;
}

// 3. Instantiate provider and create payment
require_once __DIR__ . '/providers/ProviderFactory.php';

$credentials = [
    'api_key' => $provider_row['api_key'],
    'api_token' => $provider_row['api_token'],
    'client_id' => $provider_row['client_id'],
    'client_secret' => $provider_row['client_secret'],
];

$externalRef = 'pay_' . uniqid() . '_' . $checkout_id;

try {
    $provider = ProviderFactory::create($provider_row['provider_name'], $credentials);
    $payment = $provider->createPixPayment($amount, $name, $document, $externalRef);
}
catch (Exception $e) {
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Erro ao gerar pagamento: ' . $e->getMessage()]);
    exit;
}

// 4. Save transaction to solicitacoes (using the real column names from the DB)
$transactionId = $payment['transactionId'];
$paymentCode = $payment['paymentCode'];
$qrcodeBase64 = $payment['qrcodeImage'] ?? '';
$status = 'pending';
$today = date('Y-m-d');
$email_cliente = $checkout['email'] ?? '';
$adquirente_ref = $provider_row['provider_name'];

$insertSql = "INSERT INTO solicitacoes 
    (externalreference, idtransaction, client_name, client_document, client_email, amount, status, paymentcode, paymentCodeBase64, provider_ref, real_data)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt3 = $conn->prepare($insertSql);
if ($stmt3) {
    $stmt3->bind_param("sssssdsssss",
        $externalRef,
        $transactionId,
        $name,
        $document,
        $email_cliente,
        $amount,
        $status,
        $paymentCode,
        $qrcodeBase64,
        $adquirente_ref,
        $today
    );
    $stmt3->execute();
    if ($stmt3->error) {
        error_log('process_payment INSERT error: ' . $stmt3->error);
    }
    $stmt3->close();
}

$conn->close();

// 5. Return response
echo json_encode([
    'success' => true,
    'externalReference' => $externalRef,
    'paymentCode' => $paymentCode,
    'qrcodeImage' => $payment['qrcodeImage'] ?? null,
    'transactionId' => $transactionId,
    'expiresIn' => $payment['expiresIn'] ?? 1800,
    'amount' => $amount,
]);
