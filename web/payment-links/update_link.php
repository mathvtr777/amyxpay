<?php
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['user_id'])) {
    header('Location: ../');
    exit;
}

require_once __DIR__ . '/../conectarbanco.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_SESSION['user_id']);
    $link_id = intval($_POST['id'] ?? 0);

    // Required fields
    $name = trim($_POST['name'] ?? '');
    $provider_id = intval($_POST['provider_id'] ?? 0);

    // Optional/Toggle fields
    $description = trim($_POST['description'] ?? '');
    $editable_amount = isset($_POST['editable_amount']) ? 1 : 0;

    // Amount formatting (BRL to Decimal)
    $amount_raw = $_POST['amount'] ?? '0';
    $amount_raw = str_replace('.', '', $amount_raw); // remove thousands helper
    $amount_raw = str_replace(',', '.', $amount_raw); // comma to dot
    $amount = floatval($amount_raw);

    // Optional Settings
    $expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
    $max_payments = !empty($_POST['max_payments']) ? intval($_POST['max_payments']) : null;
    $thank_you_url = trim($_POST['thank_you_url'] ?? '');
    $status = isset($_POST['status']) ? intval($_POST['status']) : 1;

    // Verify ownership
    $stmt_check = $conn->prepare("SELECT id FROM payment_links WHERE id = ? AND user_id = ?");
    $stmt_check->bind_param("ii", $link_id, $user_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows === 0) {
        die("Invalid link or access denied.");
    }
    $stmt_check->close();

    // Update
    $sql = "UPDATE payment_links SET name=?, description=?, amount=?, editable_amount=?, provider_id=?, max_payments=?, expires_at=?, thank_you_url=?, status=? WHERE id=? AND user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdiisssiii", $name, $description, $amount, $editable_amount, $provider_id, $max_payments, $expires_at, $thank_you_url, $status, $link_id, $user_id);

    if ($stmt->execute()) {
        header("Location: index.php?msg=updated");
    }
    else {
        header("Location: index.php?msg=error");
    }
    $stmt->close();
}

$conn->close();
?>
