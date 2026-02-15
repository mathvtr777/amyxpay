<?php
include '../../../conectarbanco.php';

$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$sql = "SELECT secret_key, url_cash_out FROM ad_pagpix LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $secretKey = $row['secret_key'];
    $urlCashIn = $row['url_cash_out'];
} else {
    die("Erro: Não foi possível obter a chave secret_key.");
}

$conn->close();

$headers = [
    'Authorization: Basic ' . base64_encode($secretKey),
    'Content-Type: application/json'
];

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
    $postbackUrl = "https://api.uranopay.com/c1/adquirente/pagpix/webhook/";

    // Verifica a chave de segurança diretamente na tabela (sem o sistema de chaveseguranca)
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

    $pixKey = $cpf;

    // Dados para a solicitação do saque
    $cashoutData = [
        'amount' => (int)$amount,
        'pixKey' => (string)$pixKey,
        'pixType' => 'CPF',
        'beneficiaryName' => (string)$beneficiaryName,
        'beneficiaryDocument' => (string)$cpf,
        'description' => "Saque",
        'postbackUrl' => (string)$postbackUrl
    ];

    // Realiza a requisição para o serviço externo
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $urlCashIn,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($cashoutData)
    ]);

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        $response = json_encode(['K' => 'Erro: ' . curl_error($curl)]);
    } else {
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            $response = json_encode(['K' => "Error: $httpCode - " . $response]);
        }
    }

    curl_close($curl);

    echo $response;
} else {
    echo json_encode(['error' => 'HELLO']);
}
