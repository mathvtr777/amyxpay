<?php
session_start();

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

$sql = "SELECT user_id, nome, status, permission, transacoes_aproved FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_id, $nome, $status, $permission, $transacoes_aproved);
$stmt->fetch();

$_SESSION['user_id'] = $user_id;

$stmt->close();
$conn->close();
?>

<?php
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../");
    exit;
}
?>

<?php
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
$sql = "SELECT user_id, nome, status, permission, transacoes_aproved FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_id, $nome, $status, $permission, $transacoes_aproved);
$stmt->fetch();
$user_id_var = $user_id;
$stmt->close();
$conn->close();
?>

<?php
include '../conectarbanco.php';

if (!isset($_SESSION['user_id'])) {
    die("Usuário não autenticado.");
}

$user_id = $_SESSION['user_id'];
date_default_timezone_set('America/Sao_Paulo');

$dataHoje = date('Y-m-d');
$mesAtual = date('Y-m');
$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Queries for counts
$sqlDia = "SELECT COUNT(*) as total FROM solicitacoes WHERE status = 'PAID_OUT' AND DATE(real_data) = '$dataHoje' AND user_id = '$user_id'";
$resultDia = $conn->query($sqlDia);
$rowDia = $resultDia->fetch_assoc();
$totalaprovadasHoje = $rowDia['total'] ?? 0;

$sqlMes = "SELECT COUNT(*) as total FROM solicitacoes WHERE status = 'PAID_OUT' AND DATE_FORMAT(real_data, '%Y-%m') = '$mesAtual' AND user_id = '$user_id'";
$resultMes = $conn->query($sqlMes);
$rowMes = $resultMes->fetch_assoc();
$totalaprovadasMes = $rowMes['total'] ?? 0;

$sqlTotal = "SELECT COUNT(*) as total FROM solicitacoes WHERE status = 'PAID_OUT' AND user_id = '$user_id'";
$resultTotal = $conn->query($sqlTotal);
$rowTotal = $resultTotal->fetch_assoc();
$totalaprovadas = $rowTotal['total'] ?? 0;

$sqltotalsolicitacoes = "SELECT COUNT(*) as total FROM solicitacoes WHERE user_id = '$user_id'";
$resulttotalsolicitacoes = $conn->query($sqltotalsolicitacoes);
$rowtotalsolicitacoes = $resulttotalsolicitacoes->fetch_assoc();
$totalsolicitacoes = $rowtotalsolicitacoes['total'] ?? 0;

// Queries for amounts (Replacing amount with amount logic)
// In the new system, amount is the only relevant value.

$sqlValorHoje = "SELECT SUM(amount) as total_valor FROM solicitacoes WHERE status = 'PAID_OUT' AND DATE(real_data) = '$dataHoje' AND user_id = '$user_id'";
$resultValorHoje = $conn->query($sqlValorHoje);
$rowValorHoje = $resultValorHoje->fetch_assoc();
$valorAprovadoHoje = $rowValorHoje['total_valor'] ?? 0;

$sqlValorMes = "SELECT SUM(amount) as total_valor FROM solicitacoes WHERE status = 'PAID_OUT' AND DATE_FORMAT(real_data, '%Y-%m') = '$mesAtual' AND user_id = '$user_id'";
$resultValorMes = $conn->query($sqlValorMes);
$rowValorMes = $resultValorMes->fetch_assoc();
$valorAprovadoMes = $rowValorMes['total_valor'] ?? 0;

$sqlValorTotal = "SELECT SUM(amount) as total_valor FROM solicitacoes WHERE status = 'PAID_OUT' AND user_id = '$user_id'";
$resultValorTotal = $conn->query($sqlValorTotal);
$rowValorTotal = $resultValorTotal->fetch_assoc();
$valorAprovadoTotal = $rowValorTotal['total_valor'] ?? 0;

// Reusing amount for "Liquido" since there are no fees in this DB anymore
$valorDepositoAprovadoHoje = $valorAprovadoHoje;
$valorDepositoAprovadoMes = $valorAprovadoMes;
$valorDepositoAprovadoTotal = $valorAprovadoTotal;

$conn->close();
?>

<?php
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/../';
?>

<?php ob_start(); ?>
<?php $styles = ob_get_clean(); ?>

<?php ob_start(); ?>
<script>
    const userId = <?php echo json_encode($_SESSION['user_id']); ?>;
    console.log("User ID:", userId);
  </script>

            <div class="main-content app-content">
                <div class="container-fluid">
                    <div class="d-flex align-items-center justify-content-between my-4 page-header-breadcrumb flex-wrap gap-2">
                        <div>
                            <p class="fw-medium fs-20 mb-0">Transações Aprovadas de entrada</p>
                        </div>
</div>

<!-- Start:: row-1 -->
<div class="row">
    <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
        <div class="card custom-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div>
                            <span class="d-block mb-2">Transações aprovadas</span>
                            <h5 class="mb-4 fs-4"><?php echo number_format($totalaprovadas); ?></h5>
                        </div>
                        <span class="text-success me-2 fw-medium d-inline-block"></span><span class="text-muted">Total</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
        <div class="card custom-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div>
                            <span class="d-block mb-2">Transações aprovadas</span>
                            <h5 class="mb-4 fs-4"><?php echo number_format($totalaprovadasHoje); ?></h5>
                        </div>
                        <span class="text-success me-2 fw-medium d-inline-block"></span><span class="text-muted">Hoje</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
        <div class="card custom-card main-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div>
                            <span class="d-block mb-2">Transações aprovadas</span>
                            <h5 class="mb-4 fs-4"><?php echo number_format($totalaprovadasMes); ?></h5>
                        </div>
                        <span class="text-success me-2 fw-medium d-inline-block"></span><span class="text-muted">Mês</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
        <div class="card custom-card main-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div>
                            <span class="d-block mb-2">Transações geral</span>
                            <h5 class="mb-4 fs-4"><?php echo number_format($totalsolicitacoes); ?></h5>
                        </div>
                        <span class="text-danger me-2 fw-medium d-inline-block"></span><span class="text-muted">Total Pendente + Aprovada</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End:: row-1 -->


<!-- Start:: row-2 -->
<div class="row">
    <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-6 col-sm-6 col-12">
        <div class="card custom-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div>
                            <span class="d-block mb-2">Valor aprovado</span>
                            <h5 class="mb-4 fs-4"><?php echo number_format($valorAprovadoTotal, 2, ',', '.'); ?></h5>
                        </div>
                        <span class="text-success me-2 fw-medium d-inline-block"></span><span class="text-muted">Total Bruto</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-6 col-sm-6 col-12">
        <div class="card custom-card main-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div>
                            <span class="d-block mb-2">Valor aprovado</span>
                            <h5 class="mb-4 fs-4"><?php echo number_format($valorAprovadoHoje, 2, ',', '.'); ?></h5>
                        </div>
                        <span class="text-success me-2 fw-medium d-inline-block"></span><span class="text-muted">Hoje Bruto</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-6 col-sm-6 col-12">
        <div class="card custom-card main-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div>
                            <span class="d-block mb-2">Valor aprovado</span>
                            <h5 class="mb-4 fs-4"><?php echo number_format($valorAprovadoMes, 2, ',', '.'); ?></h5>
                        </div>
                        <span class="text-success me-2 fw-medium d-inline-block"></span><span class="text-muted">Mês Bruto</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End:: row-2 -->


<!-- Start:: row-3 -->
<div class="row">
    <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-6 col-sm-6 col-12">
        <div class="card custom-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div>
                            <span class="d-block mb-2">Valor aprovado</span>
                            <h5 class="mb-4 fs-4"><?php echo number_format($valorDepositoAprovadoTotal, 2, ',', '.'); ?></h5>
                        </div>
                        <span class="text-success me-2 fw-medium d-inline-block"></span><span class="text-muted">Total liquido</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-6 col-sm-6 col-12">
        <div class="card custom-card main-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div>
                            <span class="d-block mb-2">Valor aprovado</span>
                            <h5 class="mb-4 fs-4"><?php echo number_format($valorDepositoAprovadoHoje, 2, ',', '.'); ?></h5>
                        </div>
                        <span class="text-success me-2 fw-medium d-inline-block"></span><span class="text-muted">Hoje liquido</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-6 col-sm-6 col-12">
        <div class="card custom-card main-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div>
                            <span class="d-block mb-2">Valor aprovado</span>
                            <h5 class="mb-4 fs-4"><?php echo number_format($valorDepositoAprovadoMes, 2, ',', '.'); ?></h5>
                        </div>
                        <span class="text-success me-2 fw-medium d-inline-block"></span><span class="text-muted">Mês liquido</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- End:: row-3 -->

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    flatpickr('.flatpickr', {
        enableTime: false,
        dateFormat: "Y-m-d",
        locale: "pt"
    });
</script>

                </div>
            </div>

<?php $content = ob_get_clean(); ?>

<?php ob_start(); ?>
        <!-- Apex Charts JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/apexcharts/apexcharts.min.js"></script>
<?php $scripts = ob_get_clean(); ?>

<?php include '../layouts/base.php'; ?>
<script src="../assets/js/apexcharts-area.js"></script>
