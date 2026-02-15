<?php


include '../../../conectarbanco.php';

// Conecta ao banco de dados
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

// Verifica a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}


$sql = "SELECT client_id, client_secret, url FROM ad_suitpay LIMIT 1";
$result = $conn->query($sql);

// Verifica se há resultados
if ($result->num_rows > 0) {
    // Retorna apenas a primeira linha (LIMIT 1)
    $row = $result->fetch_assoc();
    $client_id = $row["client_id"];
    $client_secret = $row["client_secret"];
    $url = $row["url"];
} else {
    // Se não houver resultados, retorna um erro
    echo json_encode(array('error' => "Nenhuma credencial encontrada "));
    exit();
}

// Recebe os dados via JSON
$data = json_decode(file_get_contents("php://input"), true);

// Dados da requisição
$name = $data['name'];
$cpf = $data['document'];
$amount = $data['valuedeposit'];

$staticPart = 'https://api.justpayoficial.com/webhook-suitpay/pix.php';

$callbackUrl = $baseUrl . $staticPart;

// Dados para a requisição na API da SuitPay
$payload = array(
    'requestNumber' => '12356',
    'dueDate' => '2023-12-31',
    'amount' => floatval($amount),
    'client' => array(
        'name' => $name,
        'document' => $cpf,
        'email' => 'cliente@email.com'
    ),
    'callbackUrl' => $staticPart
);

// Realiza a requisição para a API da SuitPay
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    "ci: $client_id",
    "cs: $client_secret"
));

$response = curl_exec($ch);
curl_close($ch);

// Verifica se a requisição foi bem sucedida
if ($response === false) {
    $error = curl_error($ch);
    $errorCode = curl_errno($ch);
    echo json_encode(array('error' => "Erro na requisição para a API da SuitPay: $error (Código: $errorCode)"));
} else {
    // Retorna a resposta da API da SuitPay
    echo $response;
}

// Fecha a conexão com o banco de dados
$conn->close();
