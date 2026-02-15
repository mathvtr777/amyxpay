<?php
session_start();

// Incluir arquivo de configuração do banco de dados
include '../conectarbanco.php';

// Configurar o fuso horário para Brasília
date_default_timezone_set('America/Sao_Paulo');

// Obter o e-mail da sessão
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// Verificar se o e-mail está presente
if (empty($email)) {
    echo "Sessão inválida. Faça login novamente.";
    exit;
}

// Receber e sanitizar dados do formulário
$valor = isset($_POST['valor']) ? floatval($_POST['valor']) : 0;
$tipo_chave = 'CPF'; // Tipo de chave fixo como CPF
$chave = isset($_POST['chave']) ? $_POST['chave'] : '';

// Validar dados
if ($valor <= 0 || empty($chave)) {
    echo "Dados inválidos. Verifique o valor e a chave.";
    exit;
}

// Conectar ao banco de dados
$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

// Verificar a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Obter o user_id e taxa_cash_out do banco de dados com base no e-mail da sessão
$sql = $conn->prepare("SELECT user_id, taxa_cash_out FROM users WHERE email = ?");
$sql->bind_param("s", $email);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows == 0) {
    echo "Usuário não encontrado.";
    $sql->close();
    $conn->close();
    exit;
}

$row = $result->fetch_assoc();
$user_id = $row['user_id'];
$taxa_cash_out = $row['taxa_cash_out'];

// Verificar se já existe um saque em processamento (status 0)
$sqlCheckSaque = "SELECT COUNT(*) FROM retiradas WHERE user_id = ? AND status = '0'";
$stmtCheckSaque = $conn->prepare($sqlCheckSaque);
$stmtCheckSaque->bind_param("s", $user_id);
$stmtCheckSaque->execute();
$stmtCheckSaque->bind_result($countSaque);
$stmtCheckSaque->fetch();
$stmtCheckSaque->close();

// Se houver um saque em processamento, exibe mensagem e sai
if ($countSaque > 0) {
    echo "Já existe um saque em processamento. Aguarde.";
    exit; // Evita continuar o processamento
}




// Consultar a soma dos depósitos líquidos para o user_id
$sqlSumDepositoLiquido = "SELECT SUM(deposito_liquido) AS sumDepositoLiquido FROM solicitacoes WHERE user_id = ? AND status = 'PAID_OUT'";
$stmtSumDepositoLiquido = $conn->prepare($sqlSumDepositoLiquido);
$stmtSumDepositoLiquido->bind_param("s", $user_id);
$stmtSumDepositoLiquido->execute();
$stmtSumDepositoLiquido->bind_result($sumDepositoLiquido);
$stmtSumDepositoLiquido->fetch();
$stmtSumDepositoLiquido->close();

// Consultar a soma dos saques aprovados
$sqlSumSaquesAprovados = "SELECT SUM(amount) AS sumSaquesAprovados FROM solicitacoes_cash_out WHERE user_id = ? AND status = 'COMPLETED'";
$stmtSumSaquesAprovados = $conn->prepare($sqlSumSaquesAprovados);
$stmtSumSaquesAprovados->bind_param("s", $user_id);
$stmtSumSaquesAprovados->execute();
$stmtSumSaquesAprovados->bind_result($sumSaquesAprovados);
$stmtSumSaquesAprovados->fetch();
$stmtSumSaquesAprovados->close();

// Calcular o saldo líquido
$saldoliquido = $sumDepositoLiquido - ($sumSaquesAprovados ?: 0); // Se não houver saques aprovados, considera 0

if ($valor > $saldoliquido) {

    header('Location: index.php'); // Redireciona para a página principal
    exit;
}

// Calcular o valor líquido
$valor_liquido = $valor - $taxa_cash_out;







// Preparar e executar a inserção na tabela retiradas
$sql = $conn->prepare("INSERT INTO retiradas (user_id, referencia, valor, valor_liquido, tipo_chave, chave, status, data_solicitacao, data_pagamento, taxa_cash_out) VALUES (?, NULL, ?, ?, ?, ?, '0', NOW(), NULL, ?)");
$sql->bind_param("sddsss", $user_id, $valor, $valor_liquido, $tipo_chave, $chave, $taxa_cash_out);

if ($sql->execute()) {
    // Redirecionar para a página index.php após o sucesso
    header('Location: index.php');
    exit;
} else {
    echo "Erro ao registrar a solicitação de saque: " . $conn->error;
}

// Fechar a conexão
$sql->close();
$conn->close();
?>
