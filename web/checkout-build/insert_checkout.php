<?php
session_start();
include '../conectarbanco.php';

$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

// Verifique a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

function generateRandomId($length = 24) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $produto_name = $_POST['produto_name'];
    $valor_checkout = $_POST['valor_checkout'];
    $obrigado_page = $_POST['obrigado_page'];
    $status = $_POST['status'];
    $email = $_SESSION['email']; // Pega o email da sessão
    $cliente_id = $_POST['cliente_id']; // Pega o cliente_id da sessão

    // Manipulação do upload da imagem do produto
    $logo_produto = '';
    if (isset($_FILES['formFile']) && $_FILES['formFile']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["formFile"]["name"]);
        if (move_uploaded_file($_FILES["formFile"]["tmp_name"], $target_file)) {
            $logo_produto = $target_file;
        } else {
            echo "Erro ao fazer upload da imagem do produto.";
            exit;
        }
    } else {
        echo "Erro ao fazer upload da imagem do produto.";
        exit;
    }

    // Manipulação do upload do banner
    $banner_produto = '';
    if (isset($_FILES['bannerFile']) && $_FILES['bannerFile']['error'] == UPLOAD_ERR_OK) {
        $target_file_banner = $target_dir . basename($_FILES["bannerFile"]["name"]);
        if (move_uploaded_file($_FILES["bannerFile"]["tmp_name"], $target_file_banner)) {
            $banner_produto = $target_file_banner;
        } else {
            echo "Erro ao fazer upload do banner.";
            exit;
        }
    } else {
        echo "Erro ao fazer upload do banner.";
        exit;
    }

    // Gera um ID aleatório
    $checkout_id = generateRandomId(24); // Gera um ID aleatório com 24 caracteres

    // Insere o registro inicial com a coluna key_gateway
    $sql = "INSERT INTO checkout_build (name_produto, valor, obrigado_page, logo_produto, banner_produto, ativo, email, url_checkout, key_gateway) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $url_checkout = "https://$_SERVER[HTTP_HOST]/checkout/v1/?id=$checkout_id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdsssssss", $produto_name, $valor_checkout, $obrigado_page, $logo_produto, $banner_produto, $status, $email, $url_checkout, $cliente_id);

    if ($stmt->execute()) {
        echo "Novo checkout criado com sucesso!";
        header("Location: index.php"); // Redireciona para a página principal
    } else {
        echo "Erro: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
