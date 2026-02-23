<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$transactionId = trim($input['transactionId'] ?? $input['idtransaction'] ?? '');

if (!$transactionId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'transactionId é obrigatório.']);
    exit;
}

require_once __DIR__ . '/../conectarbanco.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro de conexão.']);
    exit;
}

// Query by idtransaction (the provider's transaction ID stored in solicitacoes)
$stmt = $conn->prepare("SELECT status FROM solicitacoes WHERE idtransaction = ? LIMIT 1");
$stmt->bind_param("s", $transactionId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$row) {
    echo json_encode(['success' => true, 'status' => 'pending']);
    exit;
}

// Normalize status: the old system used 'PAID_OUT', new uses 'paid'
$status = strtolower($row['status']);
if ($status === 'paid_out' || $status === 'paid') {
    $status = 'paid';
}

echo json_encode(['success' => true, 'status' => $status]);
