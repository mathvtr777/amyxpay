<?php
session_start();
require_once __DIR__ . '/../conectarbanco.php';

$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error) {
    die("Erro na conexão com banco de dados.");
}

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if (empty($slug)) {
    die("Link inválido.");
}

// Optional Domain Verification Check (to ensure it runs on the right Saas domain)
// This can be enhanced further based on TenantResolver in the future.

// Query the link
$sql = "SELECT * FROM payment_links WHERE slug = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Link não encontrado ou expirado.");
}

$link = $result->fetch_assoc();
$stmt->close();

// Validation 1: Status
if ($link['status'] != 1) {
    die("Este link de pagamento está inativo.");
}

// Validation 2: Expiration
if (!empty($link['expires_at'])) {
    $now = new DateTime();
    $expires = new DateTime($link['expires_at']);
    if ($now > $expires) {
        die("Este link de pagamento já expirou.");
    }
}

// Validation 3: User Active
$user_id = $link['user_id'];
$sql_user = "SELECT status FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_status);
$stmt->fetch();
$stmt->close();

if ($user_status == 0 || $user_status == 5) {
    die("A conta do vendedor está inativa.");
}

// If we pass validations, route them to the checkout processor.
// Since uranoPAY has `checkout/v2/index.php` that uses `id` from `checkout_build`, 
// here we have a standalone Payment Link. It needs a lightweight checkout page 
// OR we just use `checkout/v2/` by mocking a "Payment Link Checkout Context".
// But the user's `checkout/v2/` requires a `$_GET['id']` mapping to `checkout_build` string hash.

// The simplest integration without touching checkout/v2 heavily is to 
// create a "pay" specific UI right here, or dispatch to a unified UI.
// But as `checkout_build` is deeply coupled with Checkout V2, 
// let's create a beautiful isolated Checkout screen right here in `pay/index.php`!
// Since "Links de Pagamento" is a new feature with less visual customization than "Products",
// rendering a sleek Pix/Card payment interface specifically for this is optimal.
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($link['name']); ?> - Pagamento Seguro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: white; }
        .glass-panel { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.1); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-800 via-slate-900 to-black">

<div class="w-full max-w-md glass-panel rounded-3xl p-8 shadow-2xl relative overflow-hidden">
    <!-- Decorative gradient glow -->
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-purple-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse"></div>
    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-blue-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse"></div>

    <div class="relative z-10">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-white mb-2"><?php echo htmlspecialchars($link['name']); ?></h1>
            <?php if (!empty($link['description'])): ?>
                <p class="text-slate-400 text-sm"><?php echo nl2br(htmlspecialchars($link['description'])); ?></p>
            <?php
endif; ?>
        </div>

        <form id="payForm" method="POST" action="process_link_payment.php" class="space-y-6">
            <input type="hidden" name="link_id" value="<?php echo $link['id']; ?>">
            <input type="hidden" name="provider_id" value="<?php echo $link['provider_id']; ?>">
            
            <div class="bg-slate-800/50 rounded-2xl p-6 flex flex-col items-center justify-center border border-slate-700/50">
                <span class="text-sm font-medium text-slate-400 uppercase tracking-widest mb-2">Total a Pagar</span>
                
                <?php if ($link['editable_amount'] == 1): ?>
                    <div class="flex items-center text-3xl font-bold text-white">
                        <span class="mr-2 text-xl text-slate-500">R$</span>
                        <input type="text" name="amount" required placeholder="0,00" class="bg-transparent border-b border-slate-600 focus:border-purple-500 outline-none w-32 text-center transition-colors" oninput="this.value = this.value.replace(/[^0-9,]/g, '')">
                    </div>
                <?php
else: ?>
                    <div class="text-4xl font-black text-white">
                        <span class="text-2xl text-slate-500 mr-1">R$</span><?php echo number_format($link['amount'], 2, ',', '.'); ?>
                    </div>
                    <input type="hidden" name="amount" value="<?php echo number_format($link['amount'], 2, ',', ''); ?>">
                <?php
endif; ?>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Seu Nome</label>
                    <input type="text" name="customer_name" required class="w-full bg-black/40 border border-slate-700 rounded-xl px-4 py-3 outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-all text-white placeholder-slate-600" placeholder="Nome completo">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Email</label>
                    <input type="email" name="customer_email" required class="w-full bg-black/40 border border-slate-700 rounded-xl px-4 py-3 outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-all text-white placeholder-slate-600" placeholder="voce@email.com">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">CPF / CNPJ</label>
                    <input type="text" name="customer_document" required class="w-full bg-black/40 border border-slate-700 rounded-xl px-4 py-3 outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-all text-white placeholder-slate-600" placeholder="000.000.000-00">
                </div>
            </div>

            <button type="submit" id="btn-pay" class="w-full bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-500 hover:to-blue-500 text-white font-bold py-4 rounded-xl transition-all shadow-lg hover:shadow-purple-500/25 active:scale-[0.98] flex items-center justify-center gap-2">
                <span class="material-icons-round">pix</span>
                Gerar PIX
            </button>
            <p class="text-center text-xs text-slate-500 flex items-center justify-center gap-1 mt-4">
                <span class="material-icons-round text-xs">lock</span> Pagamento 100% Seguro
            </p>
        </form>
        
        <div id="payment-result" class="mt-6 hidden">
            <!-- QR Code will render here -->
        </div>

    </div>
</div>

<script>
    document.getElementById('payForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-pay');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="material-icons-round animate-spin">refresh</span> Processando...';
        btn.disabled = true;

        const formData = new FormData(this);

        try {
            // we will create process_link_payment.php which talks to providers
            const response = await fetch('process_link_payment.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.text();
            
            document.getElementById('payForm').classList.add('hidden');
            const resDiv = document.getElementById('payment-result');
            resDiv.innerHTML = result;
            resDiv.classList.remove('hidden');
        } catch (error) {
            alert('Erro ao processar pagamento. Tente novamente.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
</script>

</body>
</html>
