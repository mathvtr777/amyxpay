<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: ../");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_plan_id = isset($_POST['new_plan_id']) ? (int)$_POST['new_plan_id'] : 1;

    include '../conectarbanco.php';
    $conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

    if ($conn->connect_error) {
        die("Erro na conexão com o banco de dados: " . $conn->connect_error);
    }

    $email = $_SESSION['email'];

    // If upgrading to PRO (id 2), give 30 days active.
    // If downgrading to STARTER (id 1), clear expiration.
    $expires_at = null;
    $status = 'active';

    if ($new_plan_id == 2) {
        $expiresDate = new \DateTime();
        $expiresDate->modify('+30 days');
        $expires_at = $expiresDate->format('Y-m-d H:i:s');
    }

    $sql = "UPDATE users SET plan_id = ?, plan_expires_at = ?, subscription_status = ? WHERE email = ?";
    $stmt = $conn->prepare($sql);

    // Bind string (s) for optional null date, strings for others.
    $stmt->bind_param("isss", $new_plan_id, $expires_at, $status, $email);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();

    if ($success) {
        // Redirect back with a flag for SweetAlert
        $param = $new_plan_id == 2 ? 'upgraded=true' : 'downgraded=true';
        header("Location: index.php?" . $param);
        exit;
    }
    else {
        header("Location: index.php?error=true");
        exit;
    }
}
else {
    header("Location: index.php");
    exit;
}
