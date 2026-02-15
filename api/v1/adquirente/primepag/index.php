<?php
include '../../../conectarbanco.php';

// Conecta ao banco de dados
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Busca as credenciais PrimePag
$sql = "SELECT client_id, client_secret FROM ad_primepag LIMIT 1";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $client_id = $row["client_id"];
    $client_secret = $row["client_secret"];
} else {
    echo json_encode(array('error' => "Nenhuma credencial PrimePag encontrada"));
    exit();
}
$conn->close();

// Recebe os dados via JSON
$data = json_decode(file_get_contents("php://input"), true);
$name = $data['name'];
$cpf = $data['document'];
$amount = $data['valuedeposit'];

// 1. Gera o token de acesso OAuth2
$baseUrl = "https://api.primepag.com.br";
$urlAuth = $baseUrl . "/auth/generate_token";
$authHeader = 'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret);
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
    echo json_encode(['error' => 'Erro ao autenticar na PrimePag', 'detalhe' => $authResponse]);
    exit();
}
$accessToken = $authJson['access_token'];

// 2. Monta os dados para gerar QRCode Pix
$urlQrCode = $baseUrl . "/v1/pix/qrcodes";
$payload = [
    "value_cents" => intval($amount * 100), // valor em centavos
    "generator_name" => $name,
    "generator_document" => $cpf,
    "expiration_time" => 1800, // segundos (30min)
    "external_reference" => uniqid("REF_")
];

$headers = [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
];

$ch = curl_init($urlQrCode);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    $error = curl_error($ch);
    $errorCode = curl_errno($ch);
    echo json_encode(array('error' => "Erro na requisição para a API PrimePag: $error (Código: $errorCode)"));
    curl_close($ch);
    exit();
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 201) {
    $responseData = json_decode($response, true);
    if (isset($responseData['qrcode'])) {
        $qrcode = $responseData['qrcode'];
        $paymentCode = $qrcode['content'];
        $referenceCode = $qrcode['reference_code'];
        $paymentCodeBase64 = base64_encode($paymentCode);

        echo json_encode([
            "status" => "success",
            "message" => "ok",
            "paymentCode" => $paymentCode,
            "referenceCode" => $referenceCode,
            "paymentCodeBase64" => $paymentCodeBase64
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Resposta da API PrimePag inválida', 'detalhe' => $response]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro na API PrimePag', 'http_code' => $httpCode, 'detalhe' => $response]);
}
?>
