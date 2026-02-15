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
    header('Content-Type: application/json');
    http_response_code(200);
    $requestData = json_decode(file_get_contents('php://input'), true);


    if (!isset($requestData['amount'], $requestData['client'], $requestData['api-key'])) {

        http_response_code(400);
        echo json_encode(["error" => "Falha na solicitação: Dados incompletos"]);
        exit;
    }


    include 'conectarbanco.php';


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
            $apiUrl = "/api/v1/adquirente/suitpay/";
        }
        elseif ($adquirente_ref == 'bspay') {
            $apiUrl = "/api/v1/adquirente/bspay/";
        }
        elseif ($adquirente_ref == 'pagpix') {
            $apiUrl = "/api/v1/adquirente/pagpix/";

            $sql_taxa_pix = "SELECT taxa_pix_cash_in FROM ad_pagpix WHERE id = 1";
            $stmt_taxa_pix = $conn->prepare($sql_taxa_pix);
            $stmt_taxa_pix->execute();
            $stmt_taxa_pix->bind_result($taxa_pix_cash_in_adquirente);
            $stmt_taxa_pix->fetch();
            $stmt_taxa_pix->close();

            if ($taxa_pix_cash_in_adquirente === null) {
                $taxa_pix_cash_in_adquirente = '4';
            }
        }
        elseif ($adquirente_ref === 'primepag') {
            $apiUrl = "/api/v1/adquirente/primepag/";

            $sql_taxa_pix = "SELECT taxa_pix_cash_in FROM ad_primepag WHERE id = 1";
            $stmt_taxa_pix = $conn->prepare($sql_taxa_pix);
            $stmt_taxa_pix->execute();
            $stmt_taxa_pix->bind_result($taxa_pix_cash_in_adquirente);
            $stmt_taxa_pix->fetch();
            $stmt_taxa_pix->close();

            if ($taxa_pix_cash_in_adquirente === null) {
                $taxa_pix_cash_in_adquirente = '4';
            }
        }
        else {
            echo json_encode(["status" => "error", "message" => "Ad not found"]);
            exit;
        }
        $horaAtual = date('Y-m-d H:i:s');


        $apiData = array(
            "name" => $requestData['client']['name'],
            "document" => $requestData['client']['document'],
            "valuedeposit" => $requestData['amount']
        );


        $apiOptions = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($apiData)
            )
        );


        $apiContext = stream_context_create($apiOptions);
        $apiResponse = file_get_contents($apiUrl, false, $apiContext);


        if ($apiResponse === false) {

            echo json_encode(["status" => "error", "message" => "Erro ao solicitar à API secundária"]);
        }
        else {

            $apiResponseData = json_decode($apiResponse, true);

            if (isset($apiResponseData['paymentCode'])) {

                echo json_encode([
                    "idTransaction" => $apiResponseData['idTransaction'],
                    "message" => "ok",
                    "paymentCode" => $apiResponseData['paymentCode'],
                    "paymentCodeBase64" => $apiResponseData['paymentCodeBase64'],
                    "status" => "success"
                ]);


                $sql_taxa = "SELECT taxa_cash_in FROM users WHERE user_id = ?";
                $stmt_taxa = $conn->prepare($sql_taxa);
                $stmt_taxa->bind_param("s", $usuario);
                $stmt_taxa->execute();
                $stmt_taxa->bind_result($taxa_cash_in);
                $stmt_taxa->fetch();
                $stmt_taxa->close();


                if ($taxa_cash_in === null) {
                    $taxa_cash_in = '4';
                }



                $sql_taxa_pix = "SELECT taxa_pix_valor_real_cash_in_padrao FROM app LIMIT 1";
                $stmt_taxa_pix = $conn->prepare($sql_taxa_pix);
                $stmt_taxa_pix->execute();
                $stmt_taxa_pix->bind_result($taxa_pix_cash_in_valor_fixo);
                $stmt_taxa_pix->fetch();
                $stmt_taxa_pix->close();




                if ($taxa_pix_cash_in_valor_fixo === null) {
                    $taxa_pix_cash_in_valor_fixo = 0.00;
                }



                $taxa_cash_in_percentage = $taxa_cash_in / 100;
                $amount = floatval($requestData['amount']);
                $deposito_liquido = $amount - ($amount * $taxa_cash_in_percentage);

                $postbackUrl = '';
                if (isset($requestData['postback'])) {
                    $postbackUrl = $requestData['postback'];
                }

                $externalReference = generateRandomString(32);
                $status = 'WAITING_FOR_APPROVAL';


                $sql_insert = "INSERT INTO solicitacoes 
                (user_id, externalreference, amount, client_name, client_document, client_email, real_data, paymentCode, idtransaction, paymentCodeBase64, status, adquirente_ref, taxa_cash_in, deposito_liquido, client_telefone, taxa_pix_cash_in_adquirente, taxa_pix_cash_in_valor_fixo, postback) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                if ($stmt_insert = $conn->prepare($sql_insert)) {
                    $stmt_insert->bind_param(
                        "ssssssssssssssssss",
                        $usuario,
                        $externalReference,
                        $requestData['amount'],
                        $requestData['client']['name'],
                        $requestData['client']['document'],
                        $requestData['client']['email'],
                        $horaAtual,
                        $apiResponseData['paymentCode'],
                        $apiResponseData['idTransaction'],
                        $apiResponseData['paymentCodeBase64'],
                        $status,
                        $adquirente_ref,
                        $taxa_cash_in,
                        $deposito_liquido,
                        $requestData['client']['telefone'],
                        $taxa_pix_cash_in_adquirente,
                        $taxa_pix_cash_in_valor_fixo,
                        $postbackUrl
                    );
                    if ($stmt_insert->execute()) {
                        $stmt_insert->close();
                    }
                    else {

                        echo json_encode(["status" => "error", "message" => "Error 1"]);
                    }
                }
                else {

                    echo json_encode(["status" => "error", "message" => "Error 2"]);
                }
            }
            else {

                echo json_encode(["status" => "error", "message" => "Error 3"]);
            }
        }
    }
    else {

        echo json_encode(["status" => "error", "message" => "Chave da API inválida"]);
    }


    $conn->close();
}
else {


    echo json_encode(["error" => "The method " . $_SERVER['REQUEST_METHOD'] . " invalid"]);
}
?>
