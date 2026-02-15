<?php
date_default_timezone_set('America/Sao_Paulo');
include '../../../conectarbanco.php';

// Busca a secret_key da tabela ad_primepag 
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
$sql = "SELECT secret_key FROM ad_primepag LIMIT 1";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $secretKey = $row['secret_key'];
} else {
    $secretKey = '';
}
$conn->close();

$jsonData = file_get_contents('php://input');
$httpStatus = 200;

if ($jsonData) {
    $currentDateTime = date('d/m/Y H:i:s');
    $dataToSave = "Data e Hora: " . $currentDateTime . "\n";
    $dataToSave .= "JSON Recebido: " . $jsonData . "\n";
    $dataToSave .= "--------------------\n";
    $filePath = 'dados_cashout.txt';
    file_put_contents($filePath, $dataToSave, FILE_APPEND);

    $payload = json_decode($jsonData, true);

    // Validação do hash md5 (opcional, mas recomendado)
    if (isset($payload['md5']) && $secretKey !== '') {
        $md5Recebido = $payload['md5'];
        $string = '';
        if ($payload['notification_type'] === 'pix_qrcode' || $payload['notification_type'] === 'pix_static_qrcode') {
            $msg = $payload['message'];
            $string = "qrcode.{$msg['reference_code']}.{$msg['end_to_end']}.{$msg['value_cents']}.$secretKey";
        } elseif ($payload['notification_type'] === 'pix_payment') {
            $msg = $payload['message'];
            $string = "payment.{$msg['reference_code']}.{$msg['idempotent_id']}.{$msg['value_cents']}.$secretKey";
        } elseif ($payload['notification_type'] === 'crypto_receivement') {
            $msg = $payload['message'];
            $string = "cryptoreceivement.{$msg['payer_address']}.{$msg['operation_code']}.{$msg['value']}.$secretKey";
        } elseif ($payload['notification_type'] === 'crypto_payment') {
            $msg = $payload['message'];
            $string = "cryptopayment.{$msg['reference_code']}.{$msg['operation_code']}.{$msg['value']}.$secretKey";
        }

        if ($string !== '') {
            $md5Calculado = md5($string);
            if ($md5Calculado !== $md5Recebido) {
                file_put_contents($filePath, "MD5 INVÁLIDO: $md5Recebido (esperado: $md5Calculado)\n", FILE_APPEND);
                $httpStatus = 401;
                echo json_encode(['status' => 'error', 'message' => 'MD5 inválido']);
                http_response_code($httpStatus);
                exit;
            }
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Dados armazenados com sucesso']);
    http_response_code($httpStatus);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Nenhum dado recebido']);
    http_response_code(400);
}
