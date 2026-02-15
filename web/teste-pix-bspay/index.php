<?php
// Definir variáveis de configuração
$client_id = 'Pauloy_0651416185';
$client_secret = '2a68da4f041a0c40cb07e4cf20f0512973c43b230f227762df06838afd274a7c';
$endpoint_token = 'https://api.bspay.co/v2/oauth/token';
$endpoint_qrcode = 'https://api.bspay.co/v2/pix/qrcode';

// Função para obter o token de acesso
function getAccessToken($client_id, $client_secret, $endpoint_token) {
    $auth = base64_encode("$client_id:$client_secret");
    $headers = [
        'Authorization: Basic ' . $auth,
        'Content-Type: application/x-www-form-urlencoded'
    ];

    $postFields = [
        'grant_type' => 'client_credentials'
    ];

    $ch = curl_init($endpoint_token);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
    curl_setopt($ch, CURLOPT_POST, true);
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        echo 'Erro cURL: ' . curl_error($ch);
        curl_close($ch);
        return null;
    }
    
    curl_close($ch);

    // Exibir a resposta completa da API para depuração
    echo '<h2>Resposta da API de Token:</h2>';
    echo '<pre>' . htmlspecialchars($response) . '</pre>';

    $data = json_decode($response, true);
    return $data['access_token'] ?? null;
}

// Função para gerar o QR Code Pix
function generatePixQRCode($access_token, $endpoint_qrcode) {
    $headers = [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ];

    $postData = [
        'amount' => '200', // Valor como string
        'payerQuestion' => 'Pagamento referente a X produto/serviço',
        'external_id' => '123456',
        'postbackUrl' => 'https://linkdoseuwebhook.com',
        'payer' => [
            'name' => 'Roronoa Zoro',
            'document' => '70291669492',
            'email' => 'roronoazoro@gmail.com'
        ]
    ];

    $ch = curl_init($endpoint_qrcode);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_POST, true);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Erro cURL: ' . curl_error($ch);
        curl_close($ch);
        return null;
    }
    
    curl_close($ch);

    // Exibir a resposta completa da API para depuração
    echo '<h2>Resposta da API de QR Code Pix:</h2>';
    echo '<pre>' . htmlspecialchars($response) . '</pre>';

    return json_decode($response, true);
}

// Obter o token de acesso
$access_token = getAccessToken($client_id, $client_secret, $endpoint_token);

// Exibir o token de acesso
if ($access_token) {
    echo '<h2>Token de Acesso:</h2>';
    echo '<pre>' . htmlspecialchars($access_token) . '</pre>';
} else {
    die('Não foi possível obter o token de acesso.');
}

// Gerar o QR Code Pix
$pixData = generatePixQRCode($access_token, $endpoint_qrcode);

// Exibir o QR Code
if (isset($pixData['qrcode']) && isset($pixData['transactionId'])) {
    echo '<h1>QR Code Pix Gerado</h1>';
    echo '<p>Transaction ID: ' . htmlspecialchars($pixData['transactionId']) . '</p>';
    echo '<p>Status: ' . htmlspecialchars($pixData['status']) . '</p>';
    echo '<p>Amount: ' . htmlspecialchars($pixData['amount']) . '</p>';
    echo '<p>External ID: ' . htmlspecialchars($pixData['external_id']) . '</p>';
    echo '<p>Due Date: ' . htmlspecialchars($pixData['calendar']['dueDate']) . '</p>';
    echo '<p>Payer Name: ' . htmlspecialchars($pixData['debtor']['name']) . '</p>';
    echo '<p>Payer Document: ' . htmlspecialchars($pixData['debtor']['document']) . '</p>';
    echo '<img src="data:image/png;base64,' . htmlspecialchars($pixData['qrcode']) . '" alt="QR Code Pix">';
} else {
    echo 'Não foi possível gerar o QR Code Pix.';
}
?>
