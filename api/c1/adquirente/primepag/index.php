<?php
include '../../../conectarbanco.php';

// 1. Buscar credenciais PrimePag do banco
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
$sql = "SELECT client_id, client_secret, secret_key, webhook_url FROM ad_primepag LIMIT 1";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $clientId = $row['client_id'];
    $clientSecret = $row['client_secret'];
    $secretKey = $row['secret_key']; // Para MD5 se necessário
    $webhookUrl = $row['webhook_url']; // <-- Pegando a URL do webhook do banco
} else {
    die("Erro: Não foi possível obter as credenciais PrimePag.");
}
$conn->close();

// 2. Defina os endpoints conforme ambiente
$baseUrl = "https://api.primepag.com.br"; // Troque para produção quando necessário
$urlAuth = $baseUrl . "/auth/generate_token";
$urlPixPayment = $baseUrl . "/v1/pix/payments";

$response = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ip = $_SERVER['REMOTE_ADDR'];
    date_default_timezone_set('America/Sao_Paulo');
    $dataHora = date('Y-m-d H:i:s');

    $conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
    if ($conn->connect_error) {
        die("Conexão falhou: " . $conn->connect_error);
    }

    // Log da solicitação de saque
    $sqlLog = "INSERT INTO logs_ip_cash_out (ip, data) VALUES (?, ?)";
    $stmtLog = $conn->prepare($sqlLog);
    $stmtLog->bind_param("ss", $ip, $dataHora);
    $stmtLog->execute();
    $stmtLog->close();
    $conn->close();

    // Lê os dados da solicitação
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['cpf']) || empty($input['cpf'])) {
        echo json_encode(['K' => 'O CPF do beneficiário é obrigatório.']);
        exit;
    }

    if (!isset($input['amount']) || !is_numeric($input['amount']) || empty($input['amount'])) {
        echo json_encode(['K' => 'O valor deve ser um número e não pode ser vazio.']);
        exit;
    }

    $cpf = $input['cpf'];
    $amount = $input['amount'] * 100; // Convertendo para centavos
    $beneficiaryName = isset($input['beneficiaryName']) ? $input['beneficiaryName'] : 'usuario padrao';
    $idempotentId = uniqid('PAGAMENTO_'); // Gera um id único para evitar duplicidade

    // Verifica a chave de segurança
    $conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
    $sql = "SELECT COUNT(*) as count FROM seguranca WHERE keyseguranca = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $input['keyseguranca']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] === 0) {
        echo json_encode(['K' => 'Acesso negado K-404']);
        exit;
    }

    // 3. Autenticação OAuth2.0 para obter access_token
    $authHeader = 'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret);
    $authBody = json_encode(['grant_type' => 'client_credentials']);
    $ch = curl_init($urlAuth);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $authBody);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        $authHeader,
        'Content-Type: application/json'
    ]);
    $authResponse = curl_exec($ch);
    curl_close($ch);

    $authJson = json_decode($authResponse, true);
    if (!isset($authJson['access_token'])) {
        echo json_encode(['K' => 'Erro ao autenticar na PrimePag', 'detalhe' => $authResponse]);
        exit;
    }
    $accessToken = $authJson['access_token'];

    // 4. Montar dados para pagamento Pix (DICT) incluindo o webhook dinâmico
    $pixData = [
        "initiation_type" => "dict",
        "idempotent_id" => $idempotentId,
        "receiver_name" => $beneficiaryName,
        "receiver_document" => $cpf,
        "value_cents" => (int)$amount,
        "pix_key_type" => "cpf",
        "pix_key" => $cpf,
        "authorized" => false,
        "callback_url" => $webhookUrl // <-- webhook dinâmico aqui
    ];

    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $urlPixPayment,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($pixData)
    ]);
    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        $response = json_encode(['K' => 'Erro: ' . curl_error($curl)]);
    } else {
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($httpCode != 201) {
            $response = json_encode(['K' => "Error: $httpCode - " . $response]);
        }
    }

    curl_close($curl);
    echo $response;

} else {
    echo json_encode(['error' => 'HELLO']);
}
