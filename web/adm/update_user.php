<?php
session_start();

include '../conectarbanco.php';

// Verificar se o usuário está autenticado
if (!isset($_SESSION['email'])) {
    die('Usuário não autenticado.');
}

// Obter as informações do formulário
$id = $_POST['id'];
$nome = $_POST['nome'];
$email = $_POST['email'];
$saldo = $_POST['saldo'];
$permission = $_POST['permission'];

// Conectar ao banco de dados
$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

// Verificar a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Preparar a consulta para atualizar os dados do usuário
$sql = "UPDATE users SET nome = ?, email = ?, saldo = ?, permission = ? WHERE id = ?";
$stmt = $conn->prepare($sql);

// Verificar se a preparação da consulta foi bem-sucedida
if ($stmt === false) {
    die("Erro ao preparar a consulta: " . $conn->error);
}

// Associar os parâmetros e executar a consulta
$stmt->bind_param("ssdii", $nome, $email, $saldo, $permission, $id);
if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'Erro ao atualizar usuário: ' . $stmt->error;
}

// Fechar a declaração e a conexão
$stmt->close();
$conn->close();
?>
