<?php
include '../conectarbanco.php';

// Cria uma conexão com o banco de dados
$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

// Verifica se a conexão foi bem-sucedida
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verifica se o método de requisição é POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recebe e sanitiza os dados do formulário
    $id = isset($_POST['id']) ? $conn->real_escape_string($_POST['id']) : '';
    $valor_checkout = isset($_POST['valor_checkout']) ? $conn->real_escape_string($_POST['valor_checkout']) : '';
    $obrigado_page = isset($_POST['obrigado_page']) ? $conn->real_escape_string($_POST['obrigado_page']) : '';

    // Verifica se os dados estão definidos e não estão vazios
    if ($id && $valor_checkout && $obrigado_page) {
        // Prepara a query para atualizar os dados
        $stmt = $conn->prepare("UPDATE checkout_build SET valor = ?, obrigado_page = ? WHERE id = ?");
        
        if ($stmt) {
            // Liga os parâmetros e executa a query
            $stmt->bind_param("ssi", $valor_checkout, $obrigado_page, $id);
            if ($stmt->execute()) {
                // Sucesso
                header("Location: index.php"); // Redireciona para uma página de sucesso ou para onde desejar
                exit();
            } else {
                // Erro na execução da query
                echo "Erro ao atualizar o registro: " . $stmt->error;
            }
            $stmt->close();
        } else {
            // Erro na preparação da query
            echo "Erro ao preparar a query: " . $conn->error;
        }
    } else {
        echo "Todos os campos são obrigatórios.";
    }
} else {
    echo "Método de requisição inválido.";
}

// Fecha a conexão com o banco de dados
$conn->close();
?>
