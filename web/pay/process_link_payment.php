<?php
error_reporting(0);
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('<div class="p-4 bg-red-500/10 border border-red-500/20 text-red-500 rounded-xl text-center">Método não permitido.</div>');
}

require_once __DIR__ . '/../conectarbanco.php';
require_once __DIR__ . '/../checkout/providers/ProviderFactory.php';

$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error) {
    die('<div class="p-4 bg-red-500/10 border border-red-500/20 text-red-500 rounded-xl text-center">Erro no banco de dados.</div>');
}

// 1. Get input
$link_id = intval($_POST['link_id'] ?? 0);
$provider_id = intval($_POST['provider_id'] ?? 0);
$customer_name = trim($_POST['customer_name'] ?? '');
$customer_document = preg_replace('/\D/', '', $_POST['customer_document'] ?? '');
$customer_email = trim($_POST['customer_email'] ?? '');

$amount_raw = $_POST['amount'] ?? '0';
$amount_raw = str_replace('.', '', $amount_raw); // Remove thousands
$amount_raw = str_replace(',', '.', $amount_raw);
$amount = floatval($amount_raw);

if (!$link_id || !$provider_id || !$customer_name || !$customer_document || $amount <= 0) {
    die('<div class="p-4 bg-red-500/10 border border-red-500/20 text-red-500 rounded-xl text-center">Dados inválidos. Verifique os valores informados.</div>');
}

// 2. Load the Link
$stmt_l = $conn->prepare("SELECT user_id FROM payment_links WHERE id = ? AND status = 1");
$stmt_l->bind_param("i", $link_id);
$stmt_l->execute();
$res_l = $stmt_l->get_result();
if ($res_l->num_rows === 0) {
    die('<div class="p-4 bg-red-500/10 border border-red-500/20 text-red-500 rounded-xl text-center">Link inválido ou expirado.</div>');
}
$linkRow = $res_l->fetch_assoc();
$stmt_l->close();

require_once __DIR__ . '/../app/Services/PlanService.php';
$planService = new \App\Services\PlanService($conn, $linkRow['user_id']);

if (!$planService->isActive()) {
    die('<div class="p-4 bg-red-500/10 border border-red-500/20 text-red-500 rounded-xl text-center">A conta do vendedor está inativa ou o plano expirou.</div>');
}

// 3. Load the Provider
$stmt_p = $conn->prepare("SELECT provider_name, api_key, api_token, client_id, client_secret FROM user_providers WHERE id = ?");
$stmt_p->bind_param("i", $provider_id);
$stmt_p->execute();
$res_p = $stmt_p->get_result();
if ($res_p->num_rows === 0) {
    die('<div class="p-4 bg-red-500/10 border border-red-500/20 text-red-500 rounded-xl text-center">Provedor não configurado.</div>');
}
$providerRow = $res_p->fetch_assoc();
$stmt_p->close();

// 4. Create Payment via ProviderFactory
$credentials = [
    'api_key' => $providerRow['api_key'],
    'api_token' => $providerRow['api_token'],
    'client_id' => $providerRow['client_id'],
    'client_secret' => $providerRow['client_secret']
];

$externalRef = 'link_' . uniqid() . '_' . $link_id;

try {
    $providerObj = ProviderFactory::create($providerRow['provider_name'], $credentials);
    $payment = $providerObj->createPixPayment($amount, $customer_name, $customer_document, $externalRef);
}
catch (Exception $e) {
    die('<div class="p-4 bg-red-500/10 border border-red-500/20 text-red-500 rounded-xl text-center">Erro no provedor PIX: ' . htmlspecialchars($e->getMessage()) . '</div>');
}

if (!isset($payment['transactionId'])) {
    die('<div class="p-4 bg-red-500/10 border border-red-500/20 text-red-500 rounded-xl text-center">Falha ao gerar cobrança. Tente novamente mais tarde.</div>');
}

// 5. Save Transaction to Solicitacoes
$transactionId = $payment['transactionId'];
$paymentCode = $payment['paymentCode'] ?? '';
$qrcodeBase64 = $payment['qrcodeImage'] ?? '';
$status = 'pending';
$today = date('Y-m-d');

$sql_insert = "INSERT INTO solicitacoes 
    (externalreference, idtransaction, client_name, client_document, client_email, amount, status, paymentcode, paymentCodeBase64, provider_ref, real_data)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt_ins = $conn->prepare($sql_insert);
$stmt_ins->bind_param("sssssdsssss",
    $externalRef, $transactionId, $customer_name, $customer_document, $customer_email,
    $amount, $status, $paymentCode, $qrcodeBase64,
    $providerRow['provider_name'], $today
);
$stmt_ins->execute();
$stmt_ins->close();
$conn->close();

// 6. Output HTML rendering for frontend injection
?>
<div class="bg-white rounded-3xl p-8 max-w-sm mx-auto text-center shadow-xl animate-fade-in text-slate-900 border-4 border-emerald-500">
    <div class="mx-auto flex items-center justify-center w-16 h-16 bg-emerald-100 rounded-full mb-4">
        <span class="material-icons-round text-emerald-500 text-3xl">check_circle</span>
    </div>
    
    <h2 class="text-xl font-bold mb-2">Pedido Gerado!</h2>
    <p class="text-slate-500 text-sm mb-6">Escaneie o QR Code abaixo no app do seu banco ou copie o código Pix Copia e Cola.</p>
    
    <?php if (!empty($qrcodeBase64)): ?>
        <div class="border-2 border-slate-100 rounded-2xl p-4 inline-block bg-white shadow-sm mb-6 cursor-pointer hover:border-purple-300 transition-colors">
            <img src="data:image/png;base64,<?php echo htmlspecialchars($qrcodeBase64); ?>" class="w-48 h-48" alt="QR Code PIX">
        </div>
    <?php
endif; ?>
    
    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 mb-6">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Pix Copia e Cola</p>
        <div class="flex items-center gap-2">
            <input type="text" id="pixCode" readonly value="<?php echo htmlspecialchars($paymentCode); ?>" class="w-full bg-transparent text-sm font-mono text-slate-700 outline-none select-all truncate">
            <button type="button" onclick="copyPix()" class="bg-slate-200 hover:bg-purple-100 text-slate-600 hover:text-purple-600 p-2 rounded-lg transition-colors flex-shrink-0" title="Copiar código">
                <span class="material-icons-round text-[18px]">content_copy</span>
            </button>
        </div>
    </div>
    
    <div class="flex items-center justify-center gap-2 text-slate-400 text-xs">
        <span class="material-icons-round text-[14px]">timer</span>
        Aguardando pagamento...
    </div>
</div>

<script>
function copyPix() {
    var copyText = document.getElementById("pixCode");
    copyText.select();
    copyText.setSelectionRange(0, 99999); // Mobile
    navigator.clipboard.writeText(copyText.value).then(() => {
        alert("Código PIX copiado!");
    });
}
// Optionally check status periodically...
</script>
