<?php
// Credenciais fornecidas
$client_id = '71a7dfce-9115-45dd-b257-c3653feee793';
$client_secret = 'e790d928-856a-4f6d-9d74-420c7cd92f0e';

// Dados da cobrança de teste
$nome = 'Teste PrimePag';
$cpf = '12345678909'; // CPF só números, 11 dígitos
$valor = 10.50; // Valor em reais
$valor_centavos = intval($valor * 100);
$referencia = 'TESTE' . rand(1000,9999);

// 1. Gerar token OAuth2
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
    echo "<b>Erro ao autenticar na PrimePag:</b><br>";
    echo "<pre>" . htmlspecialchars($authResponse) . "</pre>";
    exit;
}
$accessToken = $authJson['access_token'];

// 2. Gerar QRCode Pix
$urlQrCode = $baseUrl . "/v1/pix/qrcodes";
$payload = [
    "value_cents" => $valor_centavos,
    "generator_name" => $nome,
    "generator_document" => $cpf, // CPF só números
    "expiration_time" => 1800,
    "external_reference" => $referencia
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
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode != 201) {
    echo "<b>Erro ao gerar QR Code:</b><br>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    exit;
}

$data = json_decode($response, true);
$pix = $data['qrcode']['content'];
$reference_code = $data['qrcode']['reference_code'];

// Exibir código copia e cola e QR visual
echo "<h2>QR Code Pix PrimePag</h2>";
echo "<b>Valor:</b> R$ " . number_format($valor, 2, ',', '.') . "<br>";
echo "<b>Referência:</b> " . htmlspecialchars($reference_code) . "<br>";
echo "<b>Código Pix (copia e cola):</b><br><textarea cols='60' rows='3'>" . htmlspecialchars($pix) . "</textarea><br><br>";
echo '<img src="https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=' . urlencode($pix) . '"><br>';
?>
