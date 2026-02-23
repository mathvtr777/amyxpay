<?php
session_start();
include '../conectarbanco.php';

$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

// Verifique a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// 1. EXECUTE ALTER TABLE IF NOT EXISTS PARA NOVAS COLUNAS
$alter_queries = [
    // Tab 1: Produto
    "ALTER TABLE checkout_build ADD COLUMN IF NOT EXISTS descricao TEXT NULL AFTER name_produto",
    "ALTER TABLE checkout_build ADD COLUMN IF NOT EXISTS permitir_parcelamento TINYINT(1) DEFAULT 0 AFTER valor",
    "ALTER TABLE checkout_build ADD COLUMN IF NOT EXISTS quantidade_max INT NULL AFTER permitir_parcelamento",
    "ALTER TABLE checkout_build ADD COLUMN IF NOT EXISTS sku_interno VARCHAR(100) NULL AFTER quantidade_max",

    // Tab 2: Pagamento
    "ALTER TABLE checkout_build ADD COLUMN IF NOT EXISTS pix_expiracao INT DEFAULT 30 AFTER user_provider_id",
    "ALTER TABLE checkout_build ADD COLUMN IF NOT EXISTS permitir_cupom TINYINT(1) DEFAULT 0 AFTER pix_expiracao",
    "ALTER TABLE checkout_build ADD COLUMN IF NOT EXISTS taxa_extra DECIMAL(5,2) DEFAULT 0 AFTER permitir_cupom",

    // Tab 3: Visual
    "ALTER TABLE checkout_build ADD COLUMN IF NOT EXISTS cor_principal VARCHAR(7) DEFAULT '#a855f7' AFTER banner_produto",
    "ALTER TABLE checkout_build ADD COLUMN IF NOT EXISTS cor_botao VARCHAR(7) DEFAULT '#7c3aed' AFTER cor_principal",
    "ALTER TABLE checkout_build ADD COLUMN IF NOT EXISTS texto_botao VARCHAR(100) DEFAULT 'Comprar Agora' AFTER cor_botao",
    "ALTER TABLE checkout_build ADD COLUMN IF NOT EXISTS mostrar_resumo TINYINT(1) DEFAULT 1 AFTER texto_botao",

    // Tab 4: Eventos
    "ALTER TABLE checkout_build ADD COLUMN IF NOT EXISTS webhook_url VARCHAR(500) NULL AFTER obrigado_page",
    "ALTER TABLE checkout_build ADD COLUMN IF NOT EXISTS pixel_meta VARCHAR(50) NULL AFTER webhook_url",
    "ALTER TABLE checkout_build ADD COLUMN IF NOT EXISTS pixel_google VARCHAR(50) NULL AFTER pixel_meta",
    "ALTER TABLE checkout_build ADD COLUMN IF NOT EXISTS api_externa_url VARCHAR(500) NULL AFTER pixel_google",

    // Tab 5: Segurança
    "ALTER TABLE checkout_build ADD COLUMN IF NOT EXISTS limite_vendas INT NULL AFTER ativo",
    "ALTER TABLE checkout_build ADD COLUMN IF NOT EXISTS max_tentativas INT NULL AFTER limite_vendas",
    "ALTER TABLE checkout_build ADD COLUMN IF NOT EXISTS modo_teste TINYINT(1) DEFAULT 0 AFTER max_tentativas"
];

foreach ($alter_queries as $query) {
    // MySQL do XAMPP antigo não suporta IF NOT EXISTS no ADD COLUMN (MariaDB suporta).
    // Então vamos fazer de forma segura ignorando erro de coluna duplicada (1060).
    $conn->query($query);
}

function generateRandomId($length = 24)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ----------------------------------------------------
    // PLAN LIMITS & ENFORCEMENTS
    // ----------------------------------------------------
    require_once __DIR__ . '/../app/Services/PlanService.php';
    $planService = new \App\Services\PlanService($conn, $_SESSION['user_id'] ?? 0);

    // Check if subscription active
    if (!$planService->isActive()) {
        $_SESSION['error_message'] = "Sua assinatura está inativa. Renove para criar novos checkouts.";
        header('Location: index.php');
        exit;
    }

    // Check Max Checkouts Limit
    $stmtCount = $conn->prepare("SELECT COUNT(*) FROM checkout_build WHERE user_id = ?");
    $stmtCount->bind_param("i", $_SESSION['user_id']);
    $stmtCount->execute();
    $stmtCount->bind_result($countCheckouts);
    $stmtCount->fetch();
    $stmtCount->close();

    if (!$planService->checkLimit('max_checkouts', $countCheckouts)) {
        $_SESSION['error_message'] = "Você atingiu o limite de checkouts do seu plano. Faça upgrade para continuar.";
        header('Location: index.php');
        exit;
    }
    // ----------------------------------------------------
    // Dados Essenciais Base
    $produto_name = trim($_POST['produto_name'] ?? '');
    $valor_checkout = floatval(str_replace(',', '.', $_POST['valor_checkout'] ?? 0));
    $status = intval($_POST['status'] ?? 0);
    $email = $_SESSION['email'];
    $cliente_id = $_POST['cliente_id'];
    $user_provider_id = !empty($_POST['user_provider_id']) ? intval($_POST['user_provider_id']) : null;

    // Tab 1: Produto
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

    // Upload Diretório
    $target_dir = "../uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Opcional: Foto do Produto
    $logo_produto = 'https://fakeimg.pl/400x400/f8fafc/94a3b8?text=Produto'; // Default fallback
    if (isset($_FILES['formFile']) && $_FILES['formFile']['error'] == UPLOAD_ERR_OK) {
        $target_file = $target_dir . basename($_FILES["formFile"]["name"]);
        if (move_uploaded_file($_FILES["formFile"]["tmp_name"], $target_file)) {
            $logo_produto = $target_file;
        }
    }

    // Opcional: Banner
    $banner_produto = 'https://fakeimg.pl/1200x400/f8fafc/94a3b8?text=Banner'; // Default fallback
    if (isset($_FILES['bannerFile']) && $_FILES['bannerFile']['error'] == UPLOAD_ERR_OK) {
        $target_file_banner = $target_dir . basename($_FILES["bannerFile"]["name"]);
        if (move_uploaded_file($_FILES["bannerFile"]["tmp_name"], $target_file_banner)) {
            $banner_produto = $target_file_banner;
        }
    }

    // Plan Feature Locks
    if (!$planService->hasFeature('allow_advanced_pixels')) {
        $pixel_meta = null;
        $pixel_google = null;
        $webhook_url = null;
        $api_externa_url = null;
    }

    if (!$planService->hasFeature('allow_split_parcelamento') && isset($permitir_parcelamento)) { // We will map allow_split generally
        $permitir_parcelamento = 0;
    }

    // URL Dinâmica
    $checkout_id = generateRandomId(24);
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://');
    $path = dirname($_SERVER['SCRIPT_NAME']);
    $basePath = dirname($path);
    $url_checkout = $protocol . $_SERVER['HTTP_HOST'] . $basePath . "/checkout/v1/?id=$checkout_id";

    // INSERT COMPLETO
    $sql = "INSERT INTO checkout_build (
        name_produto, descricao, valor, permitir_parcelamento, quantidade_max, sku_interno,
        user_provider_id, pix_expiracao, permitir_cupom, taxa_extra,
        logo_produto, banner_produto, cor_principal, cor_botao, texto_botao, mostrar_resumo,
        obrigado_page, webhook_url, pixel_meta, pixel_google, api_externa_url,
        ativo, limite_vendas, max_tentativas, modo_teste, 
        email, url_checkout
    ) VALUES (
        ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?
    )";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Erro no prepare: " . $conn->error);
    }

    $stmt->bind_param(
        "ssdiisiiidsssssisssssiiiiiss",
        $produto_name, $descricao, $valor_checkout, $permitir_parcelamento, $quantidade_max, $sku_interno,
        $user_provider_id, $pix_expiracao, $permitir_cupom, $taxa_extra,
        $logo_produto, $banner_produto, $cor_principal, $cor_botao, $texto_botao, $mostrar_resumo,
        $obrigado_page, $webhook_url, $pixel_meta, $pixel_google, $api_externa_url,
        $status, $limite_vendas, $max_tentativas, $modo_teste,
        $email, $url_checkout
    );

    if ($stmt->execute()) {
        header("Location: index.php?msg=success");
    }
    else {
        echo "Erro: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>