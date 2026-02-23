<?php
session_start();

// 1. Authentication & Connection
if (!isset($_SESSION['email'])) {
    header("Location: ../");
    exit;
}

include '../conectarbanco.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$email = $_SESSION['email'];

// 2. Fetch User Data
$sql_user = "SELECT user_id, nome, status, permission, total_transacoes, transacoes_aproved FROM users WHERE email = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $email);
$stmt_user->execute();
$stmt_user->bind_result($user_id, $nome, $status, $permission, $total_transacoes, $transacoes_aproved);
$stmt_user->fetch();
$stmt_user->close();

if (empty($user_id)) {
    die("Usuário não encontrado.");
}
$_SESSION['user_id'] = $user_id;

// 3. Fetch Sales Statistics
// Total de vendas (paid)
$stmt = $conn->prepare("SELECT SUM(amount) FROM solicitacoes WHERE user_id = ? AND status = 'paid'");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($totalSalesAmount);
$stmt->fetch();
$stmt->close();

// Total de vendas pendentes
$stmt = $conn->prepare("SELECT SUM(amount) FROM solicitacoes WHERE user_id = ? AND status = 'pending'");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($pendingSalesAmount);
$stmt->fetch();
$stmt->close();

// Contagem de vendas por status
$stmt = $conn->prepare("SELECT COUNT(*) FROM solicitacoes WHERE user_id = ? AND status = 'paid'");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($paidCount);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) FROM solicitacoes WHERE user_id = ? AND status = 'pending'");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($pendingCount);
$stmt->fetch();
$stmt->close();

// 4. Filters logic
$filter_sql = "";
$params = [$user_id];
$types = "s";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    $filter_sql .= " AND (client_name LIKE ? OR client_email LIKE ? OR externalreference LIKE ?)";
    $search_term = "%{$search}%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';
if ($date_filter === 'hoje') {
    $filter_sql .= " AND DATE(real_data) = CURDATE()";
}
elseif ($date_filter === '7_dias') {
    $filter_sql .= " AND real_data >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
}
elseif ($date_filter === '30_dias') {
    $filter_sql .= " AND real_data >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
}
elseif ($date_filter === 'mes') {
    $filter_sql .= " AND MONTH(real_data) = MONTH(CURDATE()) AND YEAR(real_data) = YEAR(CURDATE())";
}

$method_filter = isset($_GET['method_filter']) ? $_GET['method_filter'] : '';
if ($method_filter === 'pix') {
    $filter_sql .= " AND (provider_ref LIKE '%pix%' OR externalreference LIKE '%pix%')";
}
elseif ($method_filter === 'cartao') {
    $filter_sql .= " AND (provider_ref LIKE '%card%' OR provider_ref LIKE '%credit%')";
}
elseif ($method_filter === 'boleto') {
    $filter_sql .= " AND (provider_ref LIKE '%boleto%' OR provider_ref LIKE '%ticket%')";
}

$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
if ($status_filter === 'pago') {
    $filter_sql .= " AND status = 'paid'";
}
elseif ($status_filter === 'pendente') {
    $filter_sql .= " AND status = 'pending'";
}
elseif ($status_filter === 'cancelado') {
    $filter_sql .= " AND (status = 'cancelled' OR status = 'canceled')";
}

// 5. Pagination & History
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Total records
$count_sql = "SELECT COUNT(*) FROM solicitacoes WHERE user_id = ?" . $filter_sql;
$stmt_count = $conn->prepare($count_sql);
if (!empty($params) && count($params) > 1) {
    $stmt_count->bind_param(substr($types, 0, count($params)), ...$params);
}
else {
    $stmt_count->bind_param("s", $user_id);
}
$stmt_count->execute();
$stmt_count->bind_result($totalRecords);
$stmt_count->fetch();
$stmt_count->close();

$totalPages = ceil($totalRecords / $limit);

// Fetch Sales History
$sql_history = "SELECT id, externalreference, amount, client_name, client_email, status, real_data, provider_ref 
                FROM solicitacoes 
                WHERE user_id = ?" . $filter_sql . " 
                ORDER BY real_data DESC 
                LIMIT ? OFFSET ?";
$stmt_history = $conn->prepare($sql_history);

// Prepare parameters for history query
$history_params = $params;
$history_params[] = $limit;
$history_params[] = $offset;
$history_types = $types . "ii";

$stmt_history->bind_param($history_types, ...$history_params);
$stmt_history->execute();
$result_history = $stmt_history->get_result();

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/../';

// Calcula Metricas
$ticketMedio = $paidCount > 0 ? $totalSalesAmount / $paidCount : 0;
$approvalRate = $total_transacoes > 0 ? ($paidCount / $total_transacoes) * 100 : 0;

ob_start();
?>
<style>
    .status-pago {
        background-color: rgba(16, 185, 129, 0.1);
        color: #10b981;
        box-shadow: 0 0 10px rgba(16, 185, 129, 0.2);
    }
    .status-pendente {
        background-color: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }
    .status-cancelado {
        background-color: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }
    .glass-card {
        background: linear-gradient(135deg, rgba(168, 85, 247, 0.1) 0%, rgba(26, 26, 26, 0.8) 100%);
        border: 1px solid rgba(168, 85, 247, 0.2);
    }
    ::-webkit-scrollbar {
        width: 6px;
    }
    ::-webkit-scrollbar-track {
        background: transparent;
    }
    ::-webkit-scrollbar-thumb {
        background: #2E2E2E;
        border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #a855f7;
    }
</style>

<!-- Header Section -->
<header class="flex flex-wrap items-center justify-between gap-4 mb-8 mt-2">
    <div class="space-y-1">
        <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-50">Minhas Vendas</h2>
        <p class="text-slate-500 dark:text-slate-400 text-sm">Gerencie seu histórico de vendas e transações em tempo real.</p>
    </div>
</header>

<?php
// Calculate growth for Total Vendas (Current month vs Last month)
$currentMonthPaid = 0;
$lastMonthPaid = 0;
// Current month
$stmt = $conn->prepare("SELECT SUM(amount) FROM solicitacoes WHERE user_id = ? AND status = 'paid' AND MONTH(real_data) = MONTH(CURDATE()) AND YEAR(real_data) = YEAR(CURDATE())");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($currentMonthPaid);
$stmt->fetch();
$stmt->close();
// Last month
$stmt = $conn->prepare("SELECT SUM(amount) FROM solicitacoes WHERE user_id = ? AND status = 'paid' AND MONTH(real_data) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(real_data) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($lastMonthPaid);
$stmt->fetch();
$stmt->close();

if ($lastMonthPaid > 0) {
    $salesGrowth = (($currentMonthPaid - $lastMonthPaid) / $lastMonthPaid) * 100;
}
else if ($currentMonthPaid > 0) {
    $salesGrowth = 100; // 100% growth if last month was 0
}
else {
    $salesGrowth = 0;
}
$growthIcon = $salesGrowth >= 0 ? 'trending_up' : 'trending_down';
$growthColor = $salesGrowth >= 0 ? 'text-emerald-400 bg-emerald-400/10' : 'text-red-400 bg-red-400/10';

// Calculate Progress Bar for Total Vendas (Paid vs Total Amount Attempted)
$totalAttemptedAmount = $totalSalesAmount + $pendingSalesAmount;
$salesProgressPct = $totalAttemptedAmount > 0 ? ($totalSalesAmount / $totalAttemptedAmount) * 100 : 0;

// Ticket Medio Chart Data (Last 5 days average ticket)
$ticketBars = [];
$maxTicket = 1; // Prevent division by zero
for ($i = 4; $i >= 0; $i--) {
    $stmt = $conn->prepare("SELECT IFNULL(SUM(amount)/COUNT(*), 0) FROM solicitacoes WHERE user_id = ? AND status = 'paid' AND DATE(real_data) = DATE_SUB(CURDATE(), INTERVAL ? DAY)");
    $stmt->bind_param("si", $user_id, $i);
    $stmt->execute();
    $stmt->bind_result($dayTicketAvg);
    $stmt->fetch();
    $stmt->close();
    $ticketBars[] = $dayTicketAvg;
    if ($dayTicketAvg > $maxTicket)
        $maxTicket = $dayTicketAvg;
}
?>

<!-- Stats Grid -->
<section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="glass-card rounded-xl p-6 shadow-xl shadow-black/50">
        <div class="flex justify-between items-start mb-4">
            <p class="text-slate-400 text-sm font-medium uppercase tracking-wider">Total Vendas</p>
            <span class="flex items-center <?php echo $growthColor; ?> text-xs font-bold px-2 py-1 rounded-full">
                <span class="material-icons-round text-[14px] mr-1"><?php echo $growthIcon; ?></span>
                <?php echo number_format(abs($salesGrowth), 1, ',', '.'); ?>%
            </span>
        </div>
        <h3 class="text-3xl font-extrabold text-slate-50">R$ <?php echo number_format($totalSalesAmount ?? 0, 2, ',', '.'); ?></h3>
        <div class="mt-4 h-1.5 w-full bg-slate-800 rounded-full overflow-hidden">
            <div class="h-full bg-primary rounded-full shadow-[0_0_8px_rgba(168,85,247,0.5)]" style="width: <?php echo $salesProgressPct; ?>%"></div>
        </div>
    </div>
    <div class="glass-card rounded-xl p-6 shadow-xl shadow-black/50">
        <div class="flex justify-between items-start mb-4">
            <p class="text-slate-400 text-sm font-medium uppercase tracking-wider">Ticket Médio</p>
            <span class="flex items-center <?php echo $growthColor; ?> text-xs font-bold px-2 py-1 rounded-full text-transparent bg-transparent" style="visibility: hidden;">
                <!-- Hide growth for ticket medio for now, display generic info if needed -->
            </span>
        </div>
        <h3 class="text-3xl font-extrabold text-slate-50">R$ <?php echo number_format($ticketMedio, 2, ',', '.'); ?></h3>
        <div class="mt-4 flex gap-1 items-end h-14">
            <?php foreach ($ticketBars as $index => $avg):
    $heightPct = ($avg / $maxTicket) * 100;
    $heightPct = max(20, $heightPct); // Minimum 20% height for visibility
    if ($avg == 0)
        $heightPct = 10;
    $opacityClass = $index == 4 ? 'bg-primary shadow-[0_0_8px_rgba(168,85,247,0.5)]' : 'bg-primary/' . (20 + ($index * 10)); // Gradually more solid
?>
                <div class="w-full rounded-sm <?php echo $opacityClass; ?>" style="height: <?php echo $heightPct; ?>%;"></div>
            <?php
endforeach; ?>
        </div>
    </div>
    <div class="glass-card rounded-xl p-6 shadow-xl shadow-black/50">
        <div class="flex justify-between items-start mb-4">
            <p class="text-slate-400 text-sm font-medium uppercase tracking-wider">Taxa de Aprovação</p>
            <span class="flex items-center text-emerald-400 text-xs font-bold px-2 py-1 bg-emerald-400/10 rounded-full" style="visibility:hidden">
            </span>
        </div>
        <div class="flex items-end justify-between">
            <h3 class="text-3xl font-extrabold text-slate-50"><?php echo number_format($approvalRate, 1, ',', '.'); ?>%</h3>
            <div class="relative w-12 h-12">
                <svg class="w-full h-full transform -rotate-90">
                    <circle class="text-slate-800" cx="24" cy="24" fill="transparent" r="20" stroke="currentColor" stroke-width="4"></circle>
                    <circle class="text-primary transition-all duration-1000 ease-out" cx="24" cy="24" fill="transparent" r="20" stroke="currentColor" stroke-dasharray="125.6" stroke-dashoffset="<?php echo 125.6 - (125.6 * ($approvalRate / 100)); ?>" stroke-width="4"></circle>
                </svg>
            </div>
        </div>
        <p class="mt-2 text-xs text-slate-500 italic">Conversão de vendas</p>
    </div>
</section>

<!-- Filters Section -->
<form method="GET" action="" id="filterForm">
    <section class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="relative flex-1 group">
            <span class="material-icons-round absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">search</span>
            <input name="search" value="<?php echo htmlspecialchars($search); ?>" class="w-full pl-12 pr-4 py-3 rounded-lg border-2 border-slate-200 dark:border-primary/20 bg-white dark:bg-surface-dark focus:ring-0 focus:border-primary text-slate-900 dark:text-slate-100 placeholder:text-slate-500 transition-all" placeholder="Buscar por cliente, e-mail ou referência..." type="text"/>
            
            <?php if ($search !== ''): ?>
            <a href="?" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 transition-colors">
                <span class="material-icons-round text-sm">close</span>
            </a>
            <?php
endif; ?>
        </div>
        <div class="flex gap-3 flex-wrap">
            <div class="relative">
                <select name="date_filter" onchange="document.getElementById('filterForm').submit();" class="appearance-none flex items-center gap-2 pl-4 pr-10 py-3 rounded-lg border-2 border-slate-200 dark:border-primary/20 bg-white dark:bg-surface-dark text-slate-700 dark:text-slate-300 text-sm font-medium hover:bg-slate-50 dark:hover:bg-surface-dark/80 transition-colors min-w-[140px] focus:ring-0 cursor-pointer">
                    <option value="">Qualquer Data</option>
                    <option value="hoje" <?php if ($date_filter === 'hoje')
    echo 'selected'; ?>>Hoje</option>
                    <option value="7_dias" <?php if ($date_filter === '7_dias')
    echo 'selected'; ?>>Últimos 7 dias</option>
                    <option value="30_dias" <?php if ($date_filter === '30_dias')
    echo 'selected'; ?>>Últimos 30 dias</option>
                    <option value="mes" <?php if ($date_filter === 'mes')
    echo 'selected'; ?>>Este Mês</option>
                </select>
                <span class="material-icons-round text-[18px] absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">expand_more</span>
            </div>
            <div class="relative">
                <select name="method_filter" onchange="document.getElementById('filterForm').submit();" class="appearance-none flex items-center gap-2 pl-4 pr-10 py-3 rounded-lg border-2 border-slate-200 dark:border-primary/20 bg-white dark:bg-surface-dark text-slate-700 dark:text-slate-300 text-sm font-medium hover:bg-slate-50 dark:hover:bg-surface-dark/80 transition-colors min-w-[140px] focus:ring-0 cursor-pointer">
                    <option value="">Qualquer Método</option>
                    <option value="pix" <?php if ($method_filter === 'pix')
    echo 'selected'; ?>>Pix</option>
                    <option value="cartao" <?php if ($method_filter === 'cartao')
    echo 'selected'; ?>>Cartão</option>
                    <option value="boleto" <?php if ($method_filter === 'boleto')
    echo 'selected'; ?>>Boleto</option>
                </select>
                <span class="material-icons-round text-[18px] absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">expand_more</span>
            </div>
            <div class="relative">
                <select name="status_filter" onchange="document.getElementById('filterForm').submit();" class="appearance-none flex items-center gap-2 pl-4 pr-10 py-3 rounded-lg border-2 border-slate-200 dark:border-primary/20 bg-white dark:bg-surface-dark text-slate-700 dark:text-slate-300 text-sm font-medium hover:bg-slate-50 dark:hover:bg-surface-dark/80 transition-colors min-w-[140px] focus:ring-0 cursor-pointer">
                    <option value="">Qualquer Status</option>
                    <option value="pago" <?php if ($status_filter === 'pago')
    echo 'selected'; ?>>Pago</option>
                    <option value="pendente" <?php if ($status_filter === 'pendente')
    echo 'selected'; ?>>Pendente</option>
                    <option value="cancelado" <?php if ($status_filter === 'cancelado')
    echo 'selected'; ?>>Cancelado</option>
                </select>
                <span class="material-icons-round text-[18px] absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">expand_more</span>
            </div>
        </div>
    </section>
</form>

<!-- Sales Table Container -->
<section class="bg-white dark:bg-surface-dark rounded-xl shadow-2xl shadow-black/20 border border-slate-200 dark:border-primary/5 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-black/40 border-b border-slate-200 dark:border-primary/10">
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Data / Hora</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Cliente</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Método</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest text-right">Valor</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest text-center">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-primary/5">
                <?php if ($result_history->num_rows > 0): ?>
                    <?php while ($row = $result_history->fetch_assoc()): ?>
                        <?php
        $statusClass = 'status-pendente';
        $statusLabel = 'Pendente';

        if ($row['status'] === 'paid') {
            $statusClass = 'status-pago';
            $statusLabel = 'Pago';
        }
        elseif ($row['status'] === 'cancelled' || $row['status'] === 'canceled') {
            $statusClass = 'status-cancelado';
            $statusLabel = 'Cancelado';
        }

        // Tentar deduzir o método do provedor
        $methodName = 'Indisponível';
        $methodIcon = 'help_outline';
        $provTitle = $row['provider_ref'] ?? '';

        if (stripos($provTitle, 'pix') !== false || stripos($row['externalreference'], 'pix') !== false) {
            $methodName = 'Pix';
            $methodIcon = 'qr_code_2';
        }
        else if (stripos($provTitle, 'card') !== false || stripos($provTitle, 'credit') !== false) {
            $methodName = 'Cartão';
            $methodIcon = 'credit_card';
        }
        else if (stripos($provTitle, 'boleto') !== false || stripos($provTitle, 'ticket') !== false) {
            $methodName = 'Boleto';
            $methodIcon = 'barcode';
        }
        else {
            $methodName = 'Pix'; // Por padrão deixar Pix
            $methodIcon = 'qr_code_2';
        }
?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-[#252525] transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold dark:text-slate-200"><?php echo date('d/m/Y', strtotime($row['real_data'])); ?></span>
                                    <span class="text-xs text-slate-500"><?php echo date('H:i:s', strtotime($row['real_data'])); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold dark:text-slate-200"><?php echo htmlspecialchars($row['client_name'] ?: 'Não Informado'); ?></span>
                                    <span class="text-xs text-slate-500"><?php echo htmlspecialchars($row['client_email'] ?: 'Sem e-mail'); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="material-icons-round text-[20px] text-slate-400"><?php echo $methodIcon; ?></span>
                                    <span class="text-sm font-medium dark:text-slate-400"><?php echo $methodName; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm font-extrabold text-primary">R$ <?php echo number_format($row['amount'], 2, ',', '.'); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center">
                                    <span class="<?php echo $statusClass; ?> text-[11px] font-bold px-3 py-1 rounded-full uppercase tracking-tighter"><?php echo $statusLabel; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="p-1 hover:bg-slate-200 dark:hover:bg-primary/20 rounded transition-colors">
                                    <span class="material-icons-round text-slate-400">more_vert</span>
                                </button>
                            </td>
                        </tr>
                    <?php
    endwhile; ?>
                <?php
else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                            Nenhuma venda encontrada.
                        </td>
                    </tr>
                <?php
endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-slate-200 dark:border-primary/10 flex items-center justify-between">
        <p class="text-sm text-slate-500">Página <span class="font-bold dark:text-slate-300"><?php echo $page; ?></span> de <span class="font-bold dark:text-slate-300"><?php echo max(1, $totalPages); ?></span></p>
        <div class="flex gap-2">
            <?php
// Preserve GET query parameters for pagination
$queryParams = $_GET;

// Generate link for previous page
$queryParams['page'] = max(1, $page - 1);
$prevLink = '?' . http_build_query($queryParams);
?>
            <a href="<?php echo $prevLink; ?>" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-primary/20 bg-white dark:bg-surface-dark <?php echo($page <= 1) ? 'text-slate-400 cursor-not-allowed pointer-events-none' : 'text-slate-600 dark:text-slate-300 hover:border-primary transition-all'; ?>">
                <span class="material-icons-round text-[20px]">chevron_left</span>
            </a>
            
            <?php
// Generate link for next page
$queryParams['page'] = min($totalPages, $page + 1);
$nextLink = '?' . http_build_query($queryParams);
?>
            <a href="<?php echo $nextLink; ?>" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-primary/20 bg-white dark:bg-surface-dark <?php echo($page >= $totalPages) ? 'text-slate-400 cursor-not-allowed pointer-events-none' : 'text-slate-600 dark:text-slate-300 hover:border-primary transition-all'; ?>">
                <span class="material-icons-round text-[20px]">chevron_right</span>
            </a>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include '../layouts/base_new.php';
$conn->close();
?>
