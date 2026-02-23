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

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = intval($_SESSION['user_id']);
    $link_id = intval($_REQUEST['id'] ?? 0);

    // Hard delete
    $stmt = $conn->prepare("DELETE FROM payment_links WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $link_id, $user_id);

    if ($stmt->execute()) {
        header("Location: index.php?msg=deleted");
    }
    else {
        header("Location: index.php?msg=error");
    }
    $stmt->close();
}

$conn->close();
?>
