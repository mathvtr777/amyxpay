<?php

header("Content-Type: application/json");

// Adiciona cabeçalhos CORS
header("Access-Control-Allow-Origin: *"); // Permite requisições de qualquer origem
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Permite os métodos POST e OPTIONS
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Permite os cabeçalhos Content-Type e Authorization



ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



// Se o método é OPTIONS, apenas responde com status 200
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // 200 OK
    exit;
}



# if it is not a post request, exit
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    exit;
}

function bad_request($message = 'Bad Request') {
    http_response_code(400);
    echo json_encode(array('error' => $message));
    exit;
}

# get the payload
$payload = file_get_contents('php://input');

# decode the payload
$payload = json_decode($payload, true);

# if the payload is not valid json, exit
if (is_null($payload) || !isset($payload['idtransaction'])) {
    bad_request('Invalid JSON or missing idtransaction');
}

# external reference
$idtransaction = $payload['idtransaction'];

# function to get the database connection
function get_conn() {
    include '../../conectarbanco.php';
    return new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);
}

# connect to the database
$conn = get_conn();

# check the connection
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(array('error' => 'Database connection failed: ' . $conn->connect_error));
    exit;
}

# prepare the SQL statement
$sql = "SELECT status FROM solicitacoes WHERE idtransaction = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $idtransaction);

# execute the query
$stmt->execute();
$result = $stmt->get_result();

# check if a row was found
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $status = $row['status'];
    echo json_encode(array('status' => $status));
} else {
    bad_request('No matching record found');
}

# close the statement and connection
$stmt->close();
$conn->close();

http_response_code(200); // OK
exit;
