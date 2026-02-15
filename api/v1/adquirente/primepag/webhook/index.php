<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit;
}

function bad_request($message = 'Bad Request')
{
    http_response_code(400);
    echo json_encode(array('error' => $message));
    exit;
}

// Recebe e decodifica o payload
$payload = file_get_contents('php://input');
$payload = json_decode($payload, true);

// Log para debug
file_put_contents('primepag_webhook_teste.txt', json_encode($payload));

// Valida JSON
if (is_null($payload)) {
    bad_request('Invalid JSON');
}

// Verifica se é notificação de pagamento Pix da PrimePag
if (!isset($payload['notification_type']) || $payload['notification_type'] !== 'pix_payment') {
    bad_request('Invalid notification type');
}

if (!isset($payload['message']) || !is_array($payload['message'])) {
    bad_request('Missing message data');
}

$message = $payload['message'];
$referenceCode = $message['reference_code'] ?? null;
$status = $message['status'] ?? null;

// Só processa se o status for "completed" (pago)
if (!$referenceCode || $status !== 'completed') {
    bad_request('Missing reference_code or invalid status');
}

// Função de conexão
function get_conn()
{
    include '../../../../conectarbanco.php';
    $conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);
    if ($conn->connect_error) {
        bad_request('Database connection failed: ' . $conn->connect_error);
    }
    return $conn;
}

$conn = get_conn();

// Busca a solicitação pelo reference_code
$sql = sprintf("SELECT * FROM solicitacoes WHERE idtransaction = '%s'", $conn->real_escape_string($referenceCode));
$result = $conn->query($sql);

if (!$result) {
    bad_request('Database query failed: ' . $conn->error);
}

$result = $result->fetch_assoc();

if (!$result) {
    bad_request('Payment not found');
}

// Se já estiver como PAID_OUT, não faz nada
if ($result['status'] === 'PAID_OUT') {
    bad_request('Payment already confirmed');
}

// Faz o postback para o sistema original, se necessário
$postbackUrl = $result['postback'];
$payloadPostback = array(
    'amount' => floatval($result['amount']),
    'idTransaction' => $result['idtransaction'],
    'paymentMethod' => 'PIX',
    'status' => 'paid'
);
$headers = array(
    'Content-Type: application/json'
);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $postbackUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payloadPostback));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec($ch);
curl_close($ch);

// Atualiza o status para PAID_OUT
$sql = sprintf("UPDATE solicitacoes SET status = 'PAID_OUT' WHERE idtransaction = '%s'", $conn->real_escape_string($referenceCode));
if (!$conn->query($sql)) {
    bad_request('Failed to update payment status: ' . $conn->error);
}

echo json_encode(array('success' => true, 'message' => 'Pagamento PIX PrimePag confirmado.'));
http_response_code(200);
exit;
?>
