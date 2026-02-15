<?php
// Inclua o arquivo de conexão com o banco de dados
include '../conectarbanco.php';
// Cria uma conexão com o banco de dados
$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

// Verifica se o parâmetro 'id' está presente na URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Prepara a consulta SQL para excluir o produto
    $sql = "DELETE FROM checkout_build WHERE id = ?";
    
    // Prepara e executa a declaração
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            // Redireciona de volta para a página principal com uma mensagem de sucesso
            header("Location: index.php?message=Produto excluído com sucesso");
        } else {
            // Mensagem de erro em caso de falha
            echo "Erro ao excluir o produto: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Erro ao preparar a consulta: " . $conn->error;
    }
} else {
    echo "ID do produto não fornecido.";
}

$conn->close();
?>
