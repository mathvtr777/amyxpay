<?php
require_once 'conectarbanco.php';

$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS domains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    domain VARCHAR(255) NOT NULL UNIQUE,
    type ENUM('custom', 'subdomain') NOT NULL DEFAULT 'custom',
    status ENUM('pending', 'active', 'suspended') NOT NULL DEFAULT 'pending',
    ssl_status ENUM('none', 'pending', 'active', 'failed') NOT NULL DEFAULT 'none',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (domain),
    INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table 'domains' created or already exists.\n";
}
else {
    echo "Error creating table: " . $conn->error . "\n";
}

// Também adicionando chaves necessárias na tabela caso ocorra erro.
$conn->close();
?>
