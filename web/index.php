<?php
require_once 'conectarbanco.php';
require_once 'app/Middleware/TenantResolver.php';

$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

$resolver = new \App\Middleware\TenantResolver($conn);
$tenantContext = $resolver->resolve();

if ($tenantContext['type'] === 'saas_admin') {
    // É o domínio do dono da plataforma (uranopay.com)
    header("Location: login/");
    exit;
}
else {
    // É o domínio de um lojista (ex: minhaloja.com)
    // Redireciona silenciosamente para o index do checkout dele
    // Em Produção ideal usar ModRewrite, aqui faremos include ou header

    // Podemos tentar fazer um require direto para manter a URL limpa (White-label puro)
    // Ou repassar via header provisório. Faremos um require para melhor SEO:

    if (file_exists('checkout/v2/index.php')) {
        // Mocking the Request URI for checkout/v2/ index.php to parse ?id= if needed
        require 'checkout/v2/index.php';
        exit;
    }
    else {
        echo "Motor de Checkout Indisponível.";
        exit;
    }
}
?>
