<?php
session_start();
include '../conectarbanco.php';

$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST['id'])) {
        die("ID do produto é obrigatório.");
    }

    $id = intval($_POST['id']);

    // Tab 1: Produto
    $produto_name = trim($_POST['produto_name'] ?? '');
    $valor_checkout = floatval(str_replace(',', '.', $_POST['valor_checkout'] ?? 0));
    $status = intval($_POST['status'] ?? 0);
    $user_provider_id = !empty($_POST['user_provider_id']) ? intval($_POST['user_provider_id']) : null;

    $descricao = trim($_POST['descricao'] ?? '');
    $permitir_parcelamento = isset($_POST['permitir_parcelamento']) ? 1 : 0;
    $quantidade_max = !empty($_POST['quantidade_max']) ? intval($_POST['quantidade_max']) : null;
    $sku_interno = trim($_POST['sku_interno'] ?? '');

    // Tab 2: Pagamento
    $pix_expiracao = !empty($_POST['pix_expiracao']) ? intval($_POST['pix_expiracao']) : 30;
    $permitir_cupom = isset($_POST['permitir_cupom']) ? 1 : 0;
    $taxa_extra = !empty($_POST['taxa_extra']) ? floatval(str_replace(',', '.', $_POST['taxa_extra'])) : 0.00;

    // Tab 3: Visual
    $cor_principal = trim($_POST['cor_principal'] ?? '#a855f7');
    $cor_botao = trim($_POST['cor_botao'] ?? '#7c3aed');
    $texto_botao = trim($_POST['texto_botao'] ?? 'Comprar Agora');
    $mostrar_resumo = isset($_POST['mostrar_resumo']) ? 1 : 0;

    // Tab 4: Eventos
    $obrigado_page = trim($_POST['obrigado_page'] ?? '');
    $webhook_url = trim($_POST['webhook_url'] ?? '');
    $pixel_meta = trim($_POST['pixel_meta'] ?? '');
    $pixel_google = trim($_POST['pixel_google'] ?? '');
    $api_externa_url = trim($_POST['api_externa_url'] ?? '');

    // Tab 5: Segurança
    $limite_vendas = !empty($_POST['limite_vendas']) ? intval($_POST['limite_vendas']) : null;
    $max_tentativas = !empty($_POST['max_tentativas']) ? intval($_POST['max_tentativas']) : null;
    $modo_teste = isset($_POST['modo_teste']) ? 1 : 0;

    $fields = [
        "name_produto = ?" => ["s", $produto_name],
        "descricao = ?" => ["s", $descricao],
        "valor = ?" => ["d", $valor_checkout],
        "permitir_parcelamento = ?" => ["i", $permitir_parcelamento],
        "quantidade_max = ?" => ["i", $quantidade_max],
        "sku_interno = ?" => ["s", $sku_interno],
        "user_provider_id = ?" => ["i", $user_provider_id],
        "pix_expiracao = ?" => ["i", $pix_expiracao],
        "permitir_cupom = ?" => ["i", $permitir_cupom],
        "taxa_extra = ?" => ["d", $taxa_extra],
        "cor_principal = ?" => ["s", $cor_principal],
        "cor_botao = ?" => ["s", $cor_botao],
        "texto_botao = ?" => ["s", $texto_botao],
        "mostrar_resumo = ?" => ["i", $mostrar_resumo],
        "obrigado_page = ?" => ["s", $obrigado_page],
        "webhook_url = ?" => ["s", $webhook_url],
        "pixel_meta = ?" => ["s", $pixel_meta],
        "pixel_google = ?" => ["s", $pixel_google],
        "api_externa_url = ?" => ["s", $api_externa_url],
        "ativo = ?" => ["i", $status],
        "limite_vendas = ?" => ["i", $limite_vendas],
        "max_tentativas = ?" => ["i", $max_tentativas],
        "modo_teste = ?" => ["i", $modo_teste]
    ];

    // Tratar uploads de arquivos opcionais
    $target_dir = "../uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (isset($_FILES['formFile']) && $_FILES['formFile']['error'] == UPLOAD_ERR_OK) {
        $target_file = $target_dir . basename($_FILES["formFile"]["name"]);
        if (move_uploaded_file($_FILES["formFile"]["tmp_name"], $target_file)) {
            $fields["logo_produto = ?"] = ["s", $target_file];
        }
    }

    if (isset($_FILES['bannerFile']) && $_FILES['bannerFile']['error'] == UPLOAD_ERR_OK) {
        $target_file_banner = $target_dir . basename($_FILES["bannerFile"]["name"]);
        if (move_uploaded_file($_FILES["bannerFile"]["tmp_name"], $target_file_banner)) {
            $fields["banner_produto = ?"] = ["s", $target_file_banner];
        }
    }

    $set_clause = implode(", ", array_keys($fields));
    $sql = "UPDATE checkout_build SET $set_clause WHERE id = ?";

    $types = "";
    $values = [];
    foreach ($fields as $col => $data) {
        $types .= $data[0];
        $values[] = $data[1];
    }
    // Para o WHERE id = ?
    $types .= "i";
    $values[] = $id;

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$values);
        if ($stmt->execute()) {
            header("Location: index.php?msg=edit_success");
            exit();
        }
        else {
            echo "Erro ao atualizar: " . $stmt->error;
        }
        $stmt->close();
    }
    else {
        echo "Erro ao preparar a query: " . $conn->error;
    }
}
else {
    echo "Método de requisição inválido.";
}

$conn->close();
?>
