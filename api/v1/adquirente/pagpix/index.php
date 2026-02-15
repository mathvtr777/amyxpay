<?php

include '../../../conectarbanco.php';

// Conecta ao banco de dados
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

// Verifica a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Busca a secret_key e URLs na tabela ad_pagpix
$sql = "SELECT secret_key, url_cash_in FROM ad_pagpix LIMIT 1";
$result = $conn->query($sql);

// Verifica se há resultados
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $secret_key = $row["secret_key"];
    $url_cash_in = $row["url_cash_in"];
} else {
    echo json_encode(array('error' => "Nenhuma credencial encontrada"));
    exit();
}

// Recebe os dados via JSON
$data = json_decode(file_get_contents("php://input"), true);

// Dados da requisição
$name = $data['name'];
$cpf = $data['document'];
$amount = $data['valuedeposit'];

// Gera um UUID para o cliente
function generateUUID() {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// Dados para a requisição na API da PagPix
$payload = array(
    'amount' => floatval($amount) * 100,  // Multiplica o valor por 100
    'paymentMethod' => 'PIX',
    'customer' => array(
        'name' => $name,
        'email' => 'cliente@email.com',
        'document' => array(
            'number' => $cpf,
            'type' => 'CPF'
        ),
        'phone' => '(99) 98765-4321',
        'externaRef' => 'string',
        'address' => array(
            'street' => 'Rua Exemplo',
            'streetNumber' => '123',
            'complement' => 'Complemento',
            'zipCode' => '00000-000',
            'neighborhood' => 'Bairro',
            'city' => 'Cidade',
            'state' => 'SP',
            'country' => 'br'
        )
    ),
    'pix' => array(
        'expiresInDays' => 2
    ),
    'items' => array(
        array(
            'title' => 'Produto Exemplo',
            'quantity' => 1,
            'unitPrice' => floatval($amount),
            'tangible' => 1
        )
    ),
    'postbackUrl' => 'https://api.uranopay.com/v1/adquirente/pagpix/webhook/',
    'metadata' => 'metadata',
    'traceable' => true,
    'ip' => '127.0.0.1'
);

// Cabeçalhos para a requisição
$headers = array(
    'Authorization: Basic ' . base64_encode($secret_key),
    'Content-Type: application/json'
);

// Inicializa a requisição cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url_cash_in); // Usando a URL cash_in da tabela
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

// Executa a requisição
$response = curl_exec($ch);

// Verifica se houve erro
if (curl_errno($ch)) {
    $error = curl_error($ch);
    $errorCode = curl_errno($ch);
    echo json_encode(array('error' => "Erro na requisição para a API PagPix: $error (Código: $errorCode)"));
} else {
    // Decodifica a resposta da API
    $responseData = json_decode($response, true);

    // Verifica se a resposta contém os dados necessários
    if (isset($responseData['status']) && $responseData['status'] == 200) {
        $data = $responseData['data'];

        // Extrai os dados necessários
        $paymentCode = $data['pix']['qrcode'];
        $idTransaction = $data['id'];
        $paymentCodeBase64 = base64_encode($paymentCode); // Codifica o QR code em base64

        // Responde com os dados formatados
        echo json_encode([
            "status" => "success",
            "message" => "ok",
            "paymentCode" => $paymentCode,
            "idTransaction" => $idTransaction,
            "paymentCodeBase64" => $paymentCodeBase64
        ]);
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Resposta da API PagPix inválida'));
    }
}

// Fecha a conexão cURL e a conexão com o banco de dados
curl_close($ch);
$conn->close();
?>
