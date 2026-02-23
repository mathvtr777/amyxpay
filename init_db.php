<?php
include 'web/conectarbanco.php';

$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS user_providers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    provider_name VARCHAR(255) NOT NULL,
    api_key TEXT,
    api_token TEXT,
    client_id TEXT,
    client_secret TEXT,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Tabela user_providers criada com sucesso!";
}
else {
    echo "Erro ao criar tabela: " . $conn->error;
}

$conn->close();
?>
