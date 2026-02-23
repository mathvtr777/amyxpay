<?php
session_start();

// Verificar se o e-mail está presente na sessão
if (!isset($_SESSION['email'])) {
    header("Location: ../");
    exit;
}

include '../conectarbanco.php';

$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

$email = $_SESSION['email'];

// Consultar informações do usuário
$sql = "SELECT user_id, nome, status, permission, transacoes_aproved FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_id, $nome, $status, $permission, $transacoes_aproved);
$stmt->fetch();

$_SESSION['user_id'] = $user_id;
$user_id_var = $user_id;

$stmt->close();

// Solicitações recentes
include '../conectar_api_banco.php';
$conn_api = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

$sql_solicitacoes = "SELECT id, amount, client_name, real_data, status 
                     FROM solicitacoes 
                     WHERE user_id = ? 
                     ORDER BY id DESC 
                     LIMIT 5";
$stmt_sol = $conn_api->prepare($sql_solicitacoes);
$stmt_sol->bind_param("s", $user_id_var);
$stmt_sol->execute();
$result_solicitacoes = $stmt_sol->get_result();
$stmt_sol->close();

// Estatísticas
$totalPaidOutCount = 0;
$totalRequestsCount = 0;
$sumPaidAmount = 0;
$sumNetProfit = 0;

$sqlStats = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'PAID_OUT' THEN 1 ELSE 0 END) as paid_count,
    SUM(CASE WHEN status = 'PAID_OUT' THEN amount ELSE 0 END) as paid_sum,
    SUM(CASE WHEN status = 'PAID_OUT' THEN amount ELSE 0 END) as net_sum
    FROM solicitacoes WHERE user_id = ?";
$stmt_stats = $conn_api->prepare($sqlStats);
$stmt_stats->bind_param("s", $user_id_var);
$stmt_stats->execute();
$stmt_stats->bind_result($totalRequestsCount, $totalPaidOutCount, $sumPaidAmount, $sumNetProfit);
$stmt_stats->fetch();
$stmt_stats->close();

// No novo sistema Pix Checkout, não há mais carteira/saque
$saldoliquido = $sumNetProfit ?: 0;

// Dados para o gráfico
$dates = [];
$values = [];
$firstDayOfMonth = date('Y-m-01');
$lastDayOfMonth = date('Y-m-t');
$dailyValues = [];

$sql_graph = "SELECT real_data, amount FROM solicitacoes WHERE user_id = ? AND status = 'PAID_OUT' AND real_data BETWEEN ? AND ? ORDER BY real_data";
$stmt_graph = $conn_api->prepare($sql_graph);
$stmt_graph->bind_param("sss", $user_id_var, $firstDayOfMonth, $lastDayOfMonth);
$stmt_graph->execute();
$stmt_graph->bind_result($g_date, $g_amount);
while ($stmt_graph->fetch()) {
    $dateKey = date('Y-m-d', strtotime($g_date));
    if (!isset($dailyValues[$dateKey]))
        $dailyValues[$dateKey] = 0;
    $dailyValues[$dateKey] += $g_amount;
}
$stmt_graph->close();

$currentDate = $firstDayOfMonth;
while ($currentDate <= $lastDayOfMonth) {
    $dates[] = date('d/m', strtotime($currentDate));
    $values[] = $dailyValues[$currentDate] ?? 0;
    $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
}

$conn_api->close();
$conn->close();

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/../';

ob_start();

?>
<div class="space-y-1">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Olá, <?php echo htmlspecialchars($nome); ?></h1>
    <p class="text-slate-500 dark:text-slate-400 text-sm">Vamos tornar o dia de hoje produtivo!</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Saldo -->
    <div class="glass dark:bg-surface-dark p-6 rounded-2xl purple-glow group transition-all hover:translate-y-[-4px]">
        <div class="flex justify-between items-start mb-6">
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">Saldo Disponível</p>
                <h3 class="text-2xl font-bold text-slate-900 dark:text-white">R$ <?php echo number_format($saldoliquido, 2, ',', '.'); ?></h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary transition-transform group-hover:scale-110">
                <span class="material-icons-round">account_balance_wallet</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500 dark:text-slate-400">Disponível para saque</span>
        </div>
    </div>

    <!-- PIX Pago -->
    <div class="glass dark:bg-surface-dark p-6 rounded-2xl purple-glow group transition-all hover:translate-y-[-4px]">
        <div class="flex justify-between items-start mb-6">
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">PIX Pago</p>
                <h3 class="text-2xl font-bold text-slate-900 dark:text-white">R$ <?php echo number_format($sumPaidAmount, 2, ',', '.'); ?></h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary transition-transform group-hover:scale-110">
                <span class="material-icons-round">payments</span>
            </div>
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400">Neste Mês</p>
    </div>

    <!-- PIX Gerados -->
    <div class="glass dark:bg-surface-dark p-6 rounded-2xl purple-glow group transition-all hover:translate-y-[-4px]">
        <div class="flex justify-between items-start mb-6">
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">PIX Gerados</p>
                <h3 class="text-2xl font-bold text-slate-900 dark:text-white"><?php echo $totalRequestsCount; ?></h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary transition-transform group-hover:scale-110">
                <span class="material-icons-round">qr_code_2</span>
            </div>
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400">Neste Mês</p>
    </div>

    <!-- Volume -->
    <div class="glass dark:bg-surface-dark p-6 rounded-2xl purple-glow group transition-all hover:translate-y-[-4px]">
        <div class="flex justify-between items-start mb-6">
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">Volume Transacionado</p>
                <h3 class="text-2xl font-bold text-slate-900 dark:text-white">R$ <?php echo number_format($sumPaidAmount, 2, ',', '.'); ?></h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary transition-transform group-hover:scale-110">
                <span class="material-icons-round">trending_up</span>
            </div>
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400">Neste Mês</p>
    </div>
</div>

<!-- Chart -->
<div class="glass dark:bg-surface-dark rounded-2xl overflow-hidden">
    <div class="p-6 border-b border-slate-200 dark:border-border-dark flex justify-between items-center">
        <h2 class="font-bold text-lg uppercase tracking-wide text-slate-900 dark:text-white">Estatísticas de Vendas</h2>
        <div class="flex items-center gap-4">
            <select class="bg-transparent border-none text-xs text-slate-500 focus:ring-0 cursor-pointer">
                <option>Últimos 30 dias</option>
                <option>Últimos 7 dias</option>
            </select>
            <button class="p-1 text-slate-500 hover:text-primary">
                <span class="material-icons-round text-sm">more_vert</span>
            </button>
        </div>
    </div>
    <div class="p-8">
        <div class="mb-6">
            <p class="text-xs text-slate-400 mb-1">Movimentação</p>
            <p class="text-2xl font-bold text-slate-900 dark:text-white">R$ <?php echo number_format($sumPaidAmount, 2, ',', '.'); ?></p>
        </div>
        <div class="relative h-64 w-full">
            <canvas id="salesChart"></canvas>
        </div>
        <div class="mt-8 flex justify-between text-xs text-slate-500 w-full overflow-hidden">
            <!-- Labels generated by Chart.js -->
        </div>
    </div>
</div>

<!-- Bottom Section -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Recent Transactions -->
    <div class="lg:col-span-2 glass dark:bg-surface-dark rounded-2xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-bold text-slate-900 dark:text-white">Transações Recentes</h2>
            <button class="text-primary text-xs font-semibold hover:underline">Ver todas</button>
        </div>
        <div class="space-y-4">
            <?php while ($row = $result_solicitacoes->fetch_assoc()): ?>
                <div class="flex items-center justify-between p-4 rounded-xl hover:bg-slate-100 dark:hover:bg-background-dark/50 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full <?php echo $row['status'] == 'PAID_OUT' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-yellow-500/10 text-yellow-500'; ?> flex items-center justify-center">
                            <span class="material-icons-round text-sm">
                                <?php echo $row['status'] == 'PAID_OUT' ? 'check' : 'schedule'; ?>
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo htmlspecialchars($row['client_name'] ?: 'Cliente Anonimo'); ?></p>
                            <p class="text-[11px] text-slate-500"><?php echo date('d/m H:i', strtotime($row['real_data'])); ?></p>
                        </div>
                    </div>
                    <span class="text-sm font-bold <?php echo $row['status'] == 'PAID_OUT' ? 'text-emerald-500' : 'text-slate-500'; ?>">
                        + R$ <?php echo number_format($row['amount'], 2, ',', '.'); ?>
                    </span>
                </div>
            <?php
endwhile; ?>
        </div>
    </div>

    <!-- Upgrade Plan (Static Placeholder) -->
    <div class="glass dark:bg-surface-dark rounded-2xl p-6 flex flex-col items-center justify-center text-center space-y-4">
        <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
            <span class="material-icons-round text-3xl">rocket_launch</span>
        </div>
        <h3 class="font-bold text-slate-900 dark:text-white">Atualize seu Plano</h3>
        <p class="text-sm text-slate-500 px-4">Obtenha taxas menores e suporte prioritário 24/7 para seu negócio.</p>
        <button class="w-full py-3 bg-primary hover:bg-primary/90 text-white font-bold rounded-xl transition-all purple-glow">
            Ver Planos
        </button>
    </div>
</div>
<?php $content = ob_get_clean(); ?>

<?php ob_start(); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    // Purple gradient based on new design theme color #a855f7
    gradient.addColorStop(0, 'rgba(168, 85, 247, 0.4)');
    gradient.addColorStop(1, 'rgba(168, 85, 247, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($dates); ?>,
            datasets: [{
                label: 'Vendas (R$)',
                data: <?php echo json_encode($values); ?>,
                borderColor: '#a855f7',
                borderWidth: 3,
                pointBackgroundColor: '#a855f7',
                pointBorderColor: 'rgba(255,255,255,0.8)',
                pointHoverRadius: 6,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(26, 26, 26, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    padding: 10,
                    boxPadding: 4
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255,255,255,0.05)', drawBorder: false },
                    ticks: { color: '#64748b', font: { size: 10 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#64748b', font: { size: 10 } }
                }
            }
        }
    });
</script>
<?php $scripts = ob_get_clean(); ?>

<?php include '../layouts/base_new.php'; ?>