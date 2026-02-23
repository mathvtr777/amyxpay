<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

function generateRandomString($length = 32)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charLength - 1)];
    }
    return $randomString;
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestData = json_decode(file_get_contents('php://input'), true);

    if (!isset($requestData['amount'], $requestData['client'])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Dados incompletos"]);
        exit;
    }

    include 'conectarbanco.php';
    $conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

    if ($conn->connect_error) {
        die(json_encode(["status" => "error", "message" => "Database connection failed"]));
    }

    $seller_id = 0;
    $provider_config = null;
    $postbackUrl = $requestData['postback'] ?? '';

    // 1. Resolve Seller & Provider
    if (isset($requestData['checkout_id']) && !empty($requestData['checkout_id'])) {
        // New Flow: Based on Checkout ID
        $stmt = $conn->prepare("SELECT user_id, user_provider_id FROM checkout_build WHERE id = ?");
        $stmt->bind_param("s", $requestData['checkout_id']);
        $stmt->execute();
        $stmt->bind_result($seller_id, $provider_id);
        if (!$stmt->fetch()) {
            $stmt->close();
            echo json_encode(["status" => "error", "message" => "Checkout não encontrado."]);
            exit;
        }
        $stmt->close();

        if ($provider_id) {
            $stmt = $conn->prepare("SELECT provider_name, client_id, client_secret, api_key, api_url FROM user_providers WHERE id = ?");
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $provider_config = $res->fetch_assoc();
            $stmt->close();
        }
        else {
            echo json_encode(["status" => "error", "message" => "Provedor não configurado para este checkout."]);
            exit;
        }
    }
    elseif (isset($requestData['api-key'])) {
        // Legacy Flow: Based on API Key (kept for backward compatibility if needed)
        $sql = "SELECT user_id FROM users_key WHERE api_key = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $requestData['api-key']);
        $stmt->execute();
        $stmt->bind_result($seller_id);
        if (!$stmt->fetch()) {
            $stmt->close();
            echo json_encode(["status" => "error", "message" => "Chave de API inválida."]);
            exit;
        }
        $stmt->close();

        // Use Platform Default Provider (SuitPay) if no specific provider logic in legacy
        $provider_config = [
            'provider_name' => 'suitpay',
            'client_id' => 'PLATFORM_CLIENT_ID', // Replace with DB fetch if needed
            'client_secret' => 'PLATFORM_CLIENT_SECRET',
            'api_url' => 'https://ws.suitpay.app/api/v1/gateway/request-qrcode'
        ];
        // Ideally, we should fetch platform keys from 'ad_suitpay' table like before
        // But for now, let's focus on the new flow.
        echo json_encode(["status" => "error", "message" => "Use o novo checkout (v2_url) para processar pagamentos."]);
        exit;
    }
    else {
        echo json_encode(["status" => "error", "message" => "Identificador de checkout ausente."]);
        exit;
    }

    if (!$provider_config) {
        echo json_encode(["status" => "error", "message" => "Configuração do provedor não encontrada."]);
        exit;
    }

    // 2. Process Payment via Provider
    $apiResponseData = null;
    $externalReference = generateRandomString(32);
    $providerName = strtolower($provider_config['provider_name']);

    if ($providerName === 'suitpay') {
        // SuitPay Logic
        $apiUrl = "https://ws.suitpay.app/api/v1/gateway/request-qrcode"; // Official SuitPay URL
        // Or use $provider_config['api_url'] if set

        $dueDate = date('Y-m-d', strtotime('+1 day'));

        $payload = [
            'requestNumber' => $externalReference,
            'dueDate' => $dueDate,
            'amount' => floatval($requestData['amount']),
            'client' => [
                'name' => $requestData['client']['name'],
                'document' => $requestData['client']['document'],
                'email' => $requestData['client']['email'] ?? 'cliente@email.com'
            ],
            'callbackUrl' => !empty($postbackUrl) ? $postbackUrl : "https://" . $_SERVER['HTTP_HOST'] . "/api/v1/webhook"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "ci: " . $provider_config['client_id'],
            "cs: " . $provider_config['client_secret']
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response) {
            $apiResponseData = json_decode($response, true);
        // SuitPay usually returns: idTransaction, paymentCode, paymentCodeBase64
        }
    }
    else {
        echo json_encode(["status" => "error", "message" => "Provedor $providerName ainda não implementado."]);
        exit;
    }

    // 3. Save to Database
    if ($apiResponseData && isset($apiResponseData['paymentCode'])) {
        $status = 'PENDING'; // Or WAITING_FOR_APPROVAL
        $currentTime = date('Y-m-d H:i:s');

        // Fee Calculation (Simplified for now - can fetch from DB if needed)
        $taxa_cash_in = 4.00; // Example 4%
        $taxa_pix_cash_in_valor_fixo = 0.00;

        $amount = floatval($requestData['amount']);
        $deposito_liquido = $amount - ($amount * ($taxa_cash_in / 100)) - $taxa_pix_cash_in_valor_fixo;

        $sql_insert = "INSERT INTO solicitacoes 
        (user_id, externalreference, amount, client_name, client_document, client_email, real_data, paymentCode, idtransaction, paymentCodeBase64, status, adquirente_ref, taxa_cash_in, deposito_liquido, client_telefone, taxa_pix_cash_in_valor_fixo, postback, provider_ref) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param(
            "ssdsssssssssdssdss",
            $seller_id,
            $externalReference,
            $amount,
            $requestData['client']['name'],
            $requestData['client']['document'],
            $requestData['client']['email'],
            $currentTime,
            $apiResponseData['paymentCode'],
            $apiResponseData['idTransaction'],
            $apiResponseData['paymentCodeBase64'],
            $status,
            $providerName, // adquirente_ref
            $taxa_cash_in,
            $deposito_liquido,
            $requestData['client']['telefone'], // Ensure this is passed
            $taxa_pix_cash_in_valor_fixo,
            $postbackUrl,
            $providerName // provider_ref
        );

        if ($stmt_insert->execute()) {
            echo json_encode([
                "status" => "success",
                "paymentCode" => $apiResponseData['paymentCode'],
                "paymentCodeBase64" => $apiResponseData['paymentCodeBase64'],
                "idTransaction" => $apiResponseData['idTransaction'],
                "message" => "QRCode gerado com sucesso via $providerName"
            ]);
        }
        else {
            echo json_encode(["status" => "error", "message" => "Erro ao salvar transação: " . $stmt_insert->error]);
        }
        $stmt_insert->close();

    }
    else {
        echo json_encode(["status" => "error", "message" => "Falha na resposta do provedor.", "details" => $response]);
    }

    $conn->close();
}
else {
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
}
?>
