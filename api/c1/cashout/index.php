<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

function generateRandomString($length = 32) {
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

    if (!isset($requestData['api-key'], $requestData['name'], $requestData['cpf'], $requestData['keypix'], $requestData['amount'])) {
        http_response_code(400);
        echo json_encode(["error" => "Falha na solicitação: Dados incompletos"]);
        exit;
    }

    include '../../conectarbanco.php';

    $conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);
    if ($conn->connect_error) {
        die("Falha na conexão com o banco de dados: " . $conn->connect_error);
    }

    $sql = "SELECT user_id FROM users_key WHERE api_key = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $requestData['api-key']);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($usuario);
        $stmt->fetch();
        $stmt->close();

        if (empty($usuario)) {
            echo json_encode(["status" => "error", "message" => "user_id não encontrado"]);
            exit;
        }

        $sql_saldo = "SELECT saldo FROM users WHERE user_id = ?";
        $stmt_saldo = $conn->prepare($sql_saldo);
        $stmt_saldo->bind_param("s", $usuario);
        $stmt_saldo->execute();
        $stmt_saldo->bind_result($saldo);
        $stmt_saldo->fetch();
        $stmt_saldo->close();

        $amount = floatval($requestData['amount']);
        if ($saldo < $amount) {
            echo json_encode(["status" => "error", "message" => "Saldo insuficiente"]);
            exit;
        }

        $adquirente_ref = '';
        $sql_adquirente = "SELECT adquirente FROM adquirentes WHERE status = 1 LIMIT 1";
        $stmt_adquirente = $conn->prepare($sql_adquirente);
        $stmt_adquirente->execute();
        $stmt_adquirente->bind_result($adquirente_ref);
        $stmt_adquirente->fetch();
        $stmt_adquirente->close();

        if (empty($adquirente_ref)) {
            $adquirente_ref = 'default';
        }

        $apiUrl = '';
        if ($adquirente_ref === 'suitpay') {
            $apiUrl = "https://api.uranopay.com/c1/adquirente/suitpay/";
        } elseif ($adquirente_ref === 'bspay') {
            $apiUrl = "https://api.uranopay.com/c1/adquirente/bspay/";
        } elseif ($adquirente_ref === 'pagpix') {
            $apiUrl = "https://api.uranopay.com/c1/adquirente/pagpix/";
        } elseif ($adquirente_ref === 'primepag') {
            $apiUrl = "https://api.uranopay.com/c1/adquirente/primepag/"; // NOVO: integração PrimePag
        } else {
            echo json_encode(["status" => "error", "message" => "Adquirente não reconhecido"]);
            exit;
        }

        $horaAtual = date('Y-m-d H:i:s');

        $sql_taxa = "SELECT taxa_cash_out FROM users WHERE user_id = ?";
        $stmt_taxa = $conn->prepare($sql_taxa);
        $stmt_taxa->bind_param("s", $usuario);
        $stmt_taxa->execute();
        $stmt_taxa->bind_result($taxa_cash_out);
        $stmt_taxa->fetch();
        $stmt_taxa->close();

        if ($taxa_cash_out === null) {
            $taxa_cash_out = 4; 
        }

        $cash_out_liquido = $amount - $taxa_cash_out;

        $apiData = [
            "keyseguranca" => "24142414dertinhoiakdsj4847jdks92m",
            "cpf" => $requestData['cpf'],
            "amount" => $cash_out_liquido,
            "beneficiaryName" => $requestData['name']
        ];

        $apiOptions = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($apiData)
            ]
        ];

        $apiContext = stream_context_create($apiOptions);
        $apiResponse = file_get_contents($apiUrl, false, $apiContext);

        if ($apiResponse === false) {
            echo json_encode(["status" => "error", "message" => "Erro K-101"]);
        } else {
            $apiResponseData = json_decode($apiResponse, true);

            if (isset($apiResponseData['status']) && $apiResponseData['status'] === 200) {
                echo json_encode(["status" => "success", "message" => "K-200"]);

                $externalReference = generateRandomString(32);
                $status = '2';
                $pixKey = $requestData['keypix'];
                $type = 'PIX';

                $sql_insert = "INSERT INTO solicitacoes_cash_out 
                    (user_id, externalreference, amount, beneficiaryname, beneficiarydocument, pix, type, pixkey, date, status, taxa_cash_out, cash_out_liquido) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                if ($stmt_insert = $conn->prepare($sql_insert)) {
                    $stmt_insert->bind_param(
                        "sssssssssdsd",
                        $usuario,
                        $externalReference,
                        $requestData['amount'],
                        $requestData['name'],
                        $requestData['cpf'],
                        $pixKey,
                        $type,
                        $pixKey,
                        $horaAtual,
                        $status,
                        $taxa_cash_out,
                        $cash_out_liquido
                    );

                    $stmt_insert->execute();
                    $stmt_insert->close();

                    $finalApiResponse = file_get_contents("https://api.uranopay.com/cron/executor1.php");
                } else {
                    error_log("Erro ao preparar a consulta SQL para inserção");
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Erro: resposta da API secundária inesperada"]);
            }
        }
    } else {
        http_response_code(401);
        echo json_encode(["error" => "Falha na autenticação - K-402"]);
    }

    $conn->close();
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método não permitido"]);
}
?>
