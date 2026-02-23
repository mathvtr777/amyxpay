<?php
session_start();

// Validar sessão
if (!isset($_SESSION['email'])) {
    header("Location: ../");
    exit;
}

include '../conectarbanco.php';

$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

// Fetch user details for the header
$email = $_SESSION['email'];
$sql = "SELECT nome, permission FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($nome, $permission);
$stmt->fetch();
$stmt->close();

// Global PlanService included via base_new.php

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/../';

ob_start();

// Get the instantiated PlanService from the layout
global $planService;
$currentPlanId = $planService ? $planService->getPlanId() : 1;
$currentPlanName = $planService ? escapeshellcmd($planService->getPlanName()) : 'STARTER';
?>

<div class="space-y-8 max-w-6xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-3">
                <span class="material-icons-round text-primary">stars</span>
                Meu Plano
            </h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Gerencie sua assinatura e acesse recursos exclusivos para escalar seu negócio.</p>
        </div>
        
        <div class="bg-primary/10 border border-primary/20 text-primary px-4 py-2 rounded-xl font-medium flex items-center gap-2">
            Seu plano atual: <span class="font-bold uppercase"><?php echo $currentPlanName; ?></span>
        </div>
    </div>

    <!-- Pricing Toggle/Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- PLANO STARTER -->
        <div class="glass dark:bg-surface-dark rounded-3xl p-8 border-2 transition-all <?php echo $currentPlanId == 1 ? 'border-primary/50 shadow-[0_0_30px_rgba(168,85,247,0.15)] ring-1 ring-primary/20 scale-[1.02]' : 'border-transparent opacity-80 hover:opacity-100'; ?>">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300 uppercase tracking-widest">Iniciante</span>
                    <h2 class="text-3xl font-bold text-slate-900 dark:text-white mt-4">STARTER</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-2">Perfeito para quem está começando agora.</p>
                </div>
                <?php if ($currentPlanId == 1): ?>
                <div class="w-10 h-10 rounded-full bg-primary/20 text-primary flex items-center justify-center">
                    <span class="material-icons-round">check</span>
                </div>
                <?php
endif; ?>
            </div>

            <div class="mb-8">
                <span class="text-4xl font-black text-slate-900 dark:text-white">Grátis</span>
                <span class="text-slate-500 text-sm">/para sempre</span>
            </div>

            <ul class="space-y-4 mb-8">
                <li class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                    <span class="material-icons-round text-primary text-[18px]">check_circle</span>
                    <span class="font-medium">Até 10 Checkouts</span>
                </li>
                <li class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                    <span class="material-icons-round text-primary text-[18px]">check_circle</span>
                    <span class="font-medium">1 Domínio Personalizado</span>
                </li>
                <li class="flex items-center gap-3 text-sm text-slate-400 dark:text-slate-500 line-through">
                    <span class="material-icons-round text-[18px]">close</span>
                    <span>Múltiplos Provedores de PIX</span>
                </li>
                <li class="flex items-center gap-3 text-sm text-slate-400 dark:text-slate-500 line-through">
                    <span class="material-icons-round text-[18px]">close</span>
                    <span>Modo PIX Parcelado</span>
                </li>
                <li class="flex items-center gap-3 text-sm text-slate-400 dark:text-slate-500 line-through">
                    <span class="material-icons-round text-[18px]">close</span>
                    <span>Webhooks e Pixels (Meta/Google)</span>
                </li>
            </ul>

            <?php if ($currentPlanId == 1): ?>
                <button disabled title="Você já está neste plano." class="w-full py-4 bg-slate-100 dark:bg-slate-800 text-slate-400 font-bold rounded-xl cursor-not-allowed">
                    Seu Plano Atual
                </button>
            <?php
else: ?>
                <form action="process_plan_change.php" method="POST">
                    <input type="hidden" name="new_plan_id" value="1">
                    <button type="submit" class="w-full py-4 bg-transparent border-2 border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 font-bold rounded-xl transition-all">
                        Voltar para o Starter
                    </button>
                </form>
            <?php
endif; ?>
        </div>

        <!-- PLANO PRO -->
        <div class="glass dark:bg-surface-dark rounded-3xl p-8 border-2 transition-all relative overflow-hidden <?php echo $currentPlanId == 2 ? 'border-primary shadow-[0_0_40px_rgba(168,85,247,0.3)] ring-1 ring-primary/50 scale-[1.02]' : 'border-primary/30 hover:border-primary/60'; ?>">
            <!-- Premium Scanline Effect -->
            <div style="position:absolute;inset:0;background:linear-gradient(to bottom,transparent 40%,rgba(168,85,247,0.05) 51%,transparent 60%);background-size:100% 8px;pointer-events:none;z-index:0;"></div>
            
            <div class="flex justify-between items-start mb-6 relative z-10">
                <div>
                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-amber-500/20 text-amber-500 border border-amber-500/30 uppercase tracking-widest">Recomendado</span>
                    <h2 class="text-3xl font-black bg-gradient-to-r from-primary to-purple-400 bg-clip-text text-transparent mt-4">PRO</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-2">Liberdade total para escalar suas vendas.</p>
                </div>
                <?php if ($currentPlanId == 2): ?>
                <div class="w-10 h-10 rounded-full bg-primary/20 text-primary flex items-center justify-center">
                    <span class="material-icons-round">check</span>
                </div>
                <?php
endif; ?>
            </div>

            <div class="mb-8 relative z-10">
                <span class="text-xl font-bold text-slate-400 mr-1">R$</span>
                <span class="text-5xl font-black text-slate-900 dark:text-white">97</span>
                <span class="text-slate-500 text-sm">/mês</span>
            </div>

            <ul class="space-y-4 mb-8 relative z-10">
                <li class="flex items-center gap-3 text-sm text-slate-700 dark:text-white">
                    <span class="material-icons-round text-primary text-[18px]">done_all</span>
                    <span class="font-bold">Checkouts Ilimitados</span>
                </li>
                <li class="flex items-center gap-3 text-sm text-slate-700 dark:text-white">
                    <span class="material-icons-round text-primary text-[18px]">done_all</span>
                    <span class="font-bold">Domínios Ilimitados</span>
                </li>
                <li class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                    <span class="material-icons-round text-primary text-[18px]">check_circle</span>
                    <span>Integração com Múltiplos Provedores</span>
                </li>
                <li class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                    <span class="material-icons-round text-primary text-[18px]">check_circle</span>
                    <span>Modo PIX Parcelado Ativado</span>
                </li>
                <li class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                    <span class="material-icons-round text-primary text-[18px]">check_circle</span>
                    <span>Webhooks para Automação</span>
                </li>
                <li class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                    <span class="material-icons-round text-primary text-[18px]">check_circle</span>
                    <span>Pixels de Rastreio (Meta/Google)</span>
                </li>
            </ul>

            <div class="relative z-10">
            <?php if ($currentPlanId == 2): ?>
                <button disabled title="Você já está neste plano." class="w-full py-4 bg-slate-100 dark:bg-slate-800 text-primary font-bold rounded-xl cursor-not-allowed border border-primary/20">
                    Plano Ativo
                </button>
            <?php
else: ?>
                <form action="process_plan_change.php" method="POST" id="btn-upgrade-form">
                    <input type="hidden" name="new_plan_id" value="2">
                    <button type="submit" class="w-full py-4 bg-primary hover:bg-primary/90 text-white font-bold rounded-xl transition-all shadow-[0_0_20px_rgba(168,85,247,0.4)] hover:shadow-[0_0_30px_rgba(168,85,247,0.6)] transform hover:-translate-y-1">
                        Assinar Plano PRO
                    </button>
                </form>
            <?php
endif; ?>
            </div>
        </div>
    </div>
</div>

<?php

// Add sweet alert script for notifications
$scripts = "
<script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('upgraded') === 'true') {
            Swal.fire({
                icon: 'success',
                title: 'Parabéns!',
                text: 'Você agora é um usuário PRO. Todos os recursos foram desbloqueados!',
                confirmButtonColor: '#a855f7',
                background: document.documentElement.classList.contains('dark') ? '#1C1F26' : '#fff',
                color: document.documentElement.classList.contains('dark') ? '#fff' : '#0F1115'
            });
            window.history.replaceState({}, document.title, window.location.pathname);
        } else if (urlParams.get('downgraded') === 'true') {
            Swal.fire({
                icon: 'info',
                title: 'Plano Alterado',
                text: 'Seu plano foi revertido para o STARTER. Alguns limites foram reaplicados.',
                confirmButtonColor: '#a855f7',
                background: document.documentElement.classList.contains('dark') ? '#1C1F26' : '#fff',
                color: document.documentElement.classList.contains('dark') ? '#fff' : '#0F1115'
            });
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
</script>
";

$content = ob_get_clean();
include '../layouts/base_new.php';
?>
