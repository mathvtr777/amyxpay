<?php
require_once __DIR__ . '/../conectarbanco.php';

$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS payment_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    amount DECIMAL(10,2) NOT NULL,
    editable_amount TINYINT(1) DEFAULT 0,
    provider_id INT NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    max_payments INT NULL,
    expires_at DATETIME NULL,
    thank_you_url VARCHAR(500) NULL,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table payment_links created successfully\n";
}
else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();
?>
