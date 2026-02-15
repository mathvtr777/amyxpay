<?php
include '../conectar_api_banco.php';

$conn_api = new mysqli('localhost', $config_api['db_user'], $config_api['db_pass'], $config_api['db_name']);

// Verifica se houve algum erro na conexão
if ($conn_api->connect_error) {
  die("Erro na conexão com o banco de dados da API: " . $conn_api->connect_error);
}

// Função para gerar uma string aleatória de 64 caracteres
function generateRandomString($length)
{
  return bin2hex(random_bytes($length / 2));
}

// Função para verificar se um user_id já existe na tabela users_key
function userIdExistsInUsersKey($user_id, $conn_api)
{
  $checkUserIdQuery = "SELECT user_id FROM users_key WHERE user_id = ?";
  $checkUserIdStmt = $conn_api->prepare($checkUserIdQuery);
  if (!$checkUserIdStmt) {
      die("Erro na preparação da consulta de verificação de user_id: " . $conn_api->error);
  }

  $checkUserIdStmt->bind_param("s", $user_id);
  $checkUserIdStmt->execute();
  $checkUserIdStmt->store_result();
  $exists = $checkUserIdStmt->num_rows > 0;
  $checkUserIdStmt->close();
  return $exists;
}

// Gerar a chave API e definir o status
$apiKey = generateRandomString(64);
$statusApi = 'active';

// Verificar se o user_id já existe na tabela users_key
if (!userIdExistsInUsersKey($user_id, $conn_api)) {
  // Inserir na tabela users_key
  $insertKeyQuery = "INSERT INTO users_key (id, user_id, api_key, status) VALUES (?, ?, ?, ?)";
  $stmtKey = $conn_api->prepare($insertKeyQuery);
  if (!$stmtKey) {
    die("Erro na preparação da consulta para inserir chave API: " . $conn_api->error);
  }

  $stmtKey->bind_param("isss", $nextId, $user_id, $apiKey, $statusApi);
  if (!$stmtKey->execute()) {
    die("Erro ao inserir chave API: " . $stmtKey->error);
  }

  $stmtKey->close();
} else {
  echo "O user_id já existe na tabela users_key.";
}

$conn_api->close();
?>

