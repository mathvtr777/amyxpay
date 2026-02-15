<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

# if is not a post request, exit
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit;
}

function bad_request($message = 'Bad Request')
{
    http_response_code(400);
    echo json_encode(array('error' => $message));
    exit;
}

# get the payload
$payload = file_get_contents('php://input');

# decode the payload
$payload = json_decode($payload, true);

# log the payload for debugging
file_put_contents('teste.txt', json_encode($payload));

# if the payload is not valid json, exit
if (is_null($payload)) {
    bad_request('Invalid JSON');
}

# check if the payload is a valid transaction type
if (!isset($payload['type']) || $payload['type'] !== 'transaction') {
    bad_request('Invalid transaction type');
}

# extract necessary data from 'data' object
if (!isset($payload['data'])) {
    bad_request('Missing transaction data');
}

$transactionData = $payload['data'];
$externalReference = $transactionData['id'] ?? null; # Assuming 'id' is the transaction reference
$status = $transactionData['status'] ?? null;

if (!$externalReference || !$status) {
    bad_request('Missing externalReference or status');
}

function get_conn()
{
    include '../../../../conectarbanco.php';
    $conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);
    
    if ($conn->connect_error) {
        bad_request('Database connection failed: ' . $conn->connect_error);
    }
    
    return $conn;
}

# handle the PAID_OUT equivalent status
if ($status === 'paid') {
    $conn = get_conn();
    
    # get the payment from the database
    $sql = sprintf("SELECT * FROM solicitacoes WHERE idtransaction = '%s'", $conn->real_escape_string($externalReference));
    $result = $conn->query($sql);

    if (!$result) {
        bad_request('Database query failed: ' . $conn->error);
    }

    $result = $result->fetch_assoc();

    # if the payment is not found, exit
    if (!$result) {
        bad_request('Payment not found');
    }

    # if the payment is already confirmed, exit
    if ($result['status'] === 'PAID_OUT') {
        bad_request('Payment already confirmed');
    }
    
    $postbackUrl = $result['postback'];
    $payload = array(
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
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);
    curl_close($ch);
    
    # update the payment status to 'PAID_OUT'
    $sql = sprintf("UPDATE solicitacoes SET status = 'PAID_OUT' WHERE idtransaction = '%s'", $conn->real_escape_string($externalReference));
    
    if (!$conn->query($sql)) {
        bad_request('Failed to update payment status: ' . $conn->error);
    }
    
    # return a success response
    echo json_encode(array('success' => true, 'message' => 'Pagamento PIX confirmado.'));
    http_response_code(200);
    exit;
}

# if status is not 'paid', return a bad request
bad_request('Invalid payment status');
?>
