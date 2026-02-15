

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

$sql = "SELECT user_id, nome, status, permission, saldo, transacoes_aproved FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_id, $nome, $status, $permission, $saldo, $transacoes_aproved);
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

// Verifica se houve algum erro na conexão
if ($conn->connect_error) {
  die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

// Recuperar o e-mail da sessão
$email = $_SESSION['email'];

// Consulta SQL para obter informações do usuário com base no e-mail da sessão
$sql = "SELECT user_id, nome, status, permission, saldo, transacoes_aproved FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_id, $nome, $status, $permission, $saldo, $transacoes_aproved);
$stmt->fetch();

// Armazenar user_id em uma variável
$user_id_var = $user_id;

$stmt->close();
$conn->close();
?>







<?php
include '../conectarbanco.php';


// Verifica se o user_id está armazenado na sessão
if (!isset($_SESSION['user_id'])) {
    die("Usuário não autenticado.");
}

$user_id = $_SESSION['user_id']; // Obtém o user_id da sessão

date_default_timezone_set('America/Sao_Paulo');

$dataHoje = date('Y-m-d');
$mesAtual = date('Y-m');
$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Consulta para o dia atual com filtro pelo user_id
$sqlDia = "SELECT COUNT(*) as total FROM solicitacoes WHERE status = 'PAID_OUT' AND DATE(real_data) = '$dataHoje' AND user_id = '$user_id'";
$resultDia = $conn->query($sqlDia);
if ($resultDia->num_rows > 0) {
    $rowDia = $resultDia->fetch_assoc();
    $totalaprovadasHoje = $rowDia['total'];
} else {
    $totalaprovadasHoje = 0;
}

// Consulta para o mês atual com filtro pelo user_id
$sqlMes = "SELECT COUNT(*) as total FROM solicitacoes WHERE status = 'PAID_OUT' AND DATE_FORMAT(real_data, '%Y-%m') = '$mesAtual' AND user_id = '$user_id'";
$resultMes = $conn->query($sqlMes);
if ($resultMes->num_rows > 0) {
    $rowMes = $resultMes->fetch_assoc();
    $totalaprovadasMes = $rowMes['total'];
} else {
    $totalaprovadasMes = 0;
}

// Consulta para todas as solicitações aprovadas com filtro pelo user_id
$sqlTotal = "SELECT COUNT(*) as total FROM solicitacoes WHERE status = 'PAID_OUT' AND user_id = '$user_id'";
$resultTotal = $conn->query($sqlTotal);
if ($resultTotal->num_rows > 0) {
    $rowTotal = $resultTotal->fetch_assoc();
    $totalaprovadas = $rowTotal['total'];
} else {
    $totalaprovadas = 0;
}

// Consulta para o total de solicitações com filtro pelo user_id
$sqltotalsolicitacoes = "SELECT COUNT(*) as total FROM solicitacoes WHERE user_id = '$user_id'";
$resulttotalsolicitacoes = $conn->query($sqltotalsolicitacoes);
if ($resulttotalsolicitacoes->num_rows > 0) {
    $rowtotalsolicitacoes = $resulttotalsolicitacoes->fetch_assoc();
    $totalsolicitacoes = $rowtotalsolicitacoes['total'];
} else {
    $totalsolicitacoes = 0;
}

$conn->close();
?>


<?php
include '../conectarbanco.php';


// Verifica se o user_id está armazenado na sessão
if (!isset($_SESSION['user_id'])) {
    die("Usuário não autenticado.");
}

$user_id = $_SESSION['user_id']; // Obtém o user_id da sessão

date_default_timezone_set('America/Sao_Paulo');

$dataHoje = date('Y-m-d');
$mesAtual = date('Y-m');

$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Valor total aprovado hoje (PAID_OUT) filtrado pelo user_id
$sqlValorHoje = "SELECT SUM(amount) as total_valor FROM solicitacoes WHERE status = 'PAID_OUT' AND DATE(real_data) = '$dataHoje' AND user_id = '$user_id'";
$resultValorHoje = $conn->query($sqlValorHoje);

if ($resultValorHoje->num_rows > 0) {
    $rowValorHoje = $resultValorHoje->fetch_assoc();
    $valorAprovadoHoje = $rowValorHoje['total_valor'] ? $rowValorHoje['total_valor'] : 0;
} else {
    $valorAprovadoHoje = 0;
}

// Valor total aprovado no mês (PAID_OUT) filtrado pelo user_id
$sqlValorMes = "SELECT SUM(amount) as total_valor FROM solicitacoes WHERE status = 'PAID_OUT' AND DATE_FORMAT(real_data, '%Y-%m') = '$mesAtual' AND user_id = '$user_id'";
$resultValorMes = $conn->query($sqlValorMes);

if ($resultValorMes->num_rows > 0) {
    $rowValorMes = $resultValorMes->fetch_assoc();
    $valorAprovadoMes = $rowValorMes['total_valor'] ? $rowValorMes['total_valor'] : 0;
} else {
    $valorAprovadoMes = 0;
}

// Valor total aprovado (PAID_OUT) filtrado pelo user_id
$sqlValorTotal = "SELECT SUM(amount) as total_valor FROM solicitacoes WHERE status = 'PAID_OUT' AND user_id = '$user_id'";
$resultValorTotal = $conn->query($sqlValorTotal);

if ($resultValorTotal->num_rows > 0) {
    $rowValorTotal = $resultValorTotal->fetch_assoc();
    $valorAprovadoTotal = $rowValorTotal['total_valor'] ? $rowValorTotal['total_valor'] : 0;
} else {
    $valorAprovadoTotal = 0;
}

$conn->close();
?>
<?php
include '../conectarbanco.php';


// Verifica se o user_id está armazenado na sessão
if (!isset($_SESSION['user_id'])) {
    die("Usuário não autenticado.");
}

$user_id = $_SESSION['user_id']; // Obtém o user_id da sessão

date_default_timezone_set('America/Sao_Paulo');

$dataHoje = date('Y-m-d');
$mesAtual = date('Y-m');

$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Valor total de depósito líquido aprovado hoje (PAID_OUT) filtrado pelo user_id
$sqlDepositoHoje = "SELECT SUM(deposito_liquido) as total_valor FROM solicitacoes WHERE status = 'PAID_OUT' AND DATE(real_data) = '$dataHoje' AND user_id = '$user_id'";
$resultDepositoHoje = $conn->query($sqlDepositoHoje);

if ($resultDepositoHoje->num_rows > 0) {
    $rowDepositoHoje = $resultDepositoHoje->fetch_assoc();
    $valorDepositoAprovadoHoje = $rowDepositoHoje['total_valor'] ? $rowDepositoHoje['total_valor'] : 0;
} else {
    $valorDepositoAprovadoHoje = 0;
}

// Valor total de depósito líquido aprovado no mês (PAID_OUT) filtrado pelo user_id
$sqlDepositoMes = "SELECT SUM(deposito_liquido) as total_valor FROM solicitacoes WHERE status = 'PAID_OUT' AND DATE_FORMAT(real_data, '%Y-%m') = '$mesAtual' AND user_id = '$user_id'";
$resultDepositoMes = $conn->query($sqlDepositoMes);

if ($resultDepositoMes->num_rows > 0) {
    $rowDepositoMes = $resultDepositoMes->fetch_assoc();
    $valorDepositoAprovadoMes = $rowDepositoMes['total_valor'] ? $rowDepositoMes['total_valor'] : 0;
} else {
    $valorDepositoAprovadoMes = 0;
}

// Valor total de depósito líquido aprovado (PAID_OUT) filtrado pelo user_id
$sqlDepositoTotal = "SELECT SUM(deposito_liquido) as total_valor FROM solicitacoes WHERE status = 'PAID_OUT' AND user_id = '$user_id'";
$resultDepositoTotal = $conn->query($sqlDepositoTotal);

if ($resultDepositoTotal->num_rows > 0) {
    $rowDepositoTotal = $resultDepositoTotal->fetch_assoc();
    $valorDepositoAprovadoTotal = $rowDepositoTotal['total_valor'] ? $rowDepositoTotal['total_valor'] : 0;
} else {
    $valorDepositoAprovadoTotal = 0;
}

$conn->close();
?>





<!-- Este código gera o URL base do site combinando o protocolo, o nome de domínio e o caminho do diretório -->
<?php
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/../';
?>
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>



<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>


<script>
    // Recuperar o user_id do PHP e imprimir no console
    const userId = <?php echo json_encode($_SESSION['user_id']); ?>;
    console.log("User ID:", userId);
  </script>




            <div class="main-content app-content">
                <div class="container-fluid">

                    <!-- Start::page-header -->
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
                    <div>
                        <div class="main-card-icon success">
                            <div class="avatar avatar-lg bg-success-transparent border border-success border-opacity-10">
                                <div class="avatar avatar-sm svg-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M40,192a16,16,0,0,0,16,16H216a8,8,0,0,0,8-8V88a8,8,0,0,0-8-8H56A16,16,0,0,1,40,64Z" opacity="0.2"></path><path d="M40,64V192a16,16,0,0,0,16,16H216a8,8,0,0,0,8-8V88a8,8,0,0,0-8-8H56A16,16,0,0,1,40,64h0A16,16,0,0,1,56,48H192" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path><circle cx="180" cy="140" r="12"></circle></svg>
                                </div>
                            </div>
                        </div>
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
                    <div>
                        <div class="main-card-icon success">
                            <div class="avatar avatar-lg bg-success-transparent border border-success border-opacity-10">
                                <div class="avatar avatar-sm svg-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M40,192a16,16,0,0,0,16,16H216a8,8,0,0,0,8-8V88a8,8,0,0,0-8-8H56A16,16,0,0,1,40,64Z" opacity="0.2"></path><path d="M40,64V192a16,16,0,0,0,16,16H216a8,8,0,0,0,8-8V88a8,8,0,0,0-8-8H56A16,16,0,0,1,40,64h0A16,16,0,0,1,56,48H192" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path><circle cx="180" cy="140" r="12"></circle></svg>
                                </div>
                            </div>
                        </div>
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
                    <div>
                        <div class="main-card-icon secondary">
                            <div class="avatar avatar-lg bg-secondary-transparent border border-secondary border-opacity-10">
                                <div class="avatar avatar-sm svg-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256"><path d="M216,72H56a8,8,0,0,1,0-16H192a8,8,0,0,0,0-16H56A24,24,0,0,0,32,64V192a24,24,0,0,0,24,24H216a16,16,0,0,0,16-16V88A16,16,0,0,0,216,72Zm0,128H56a8,8,0,0,1-8-8V86.63A23.84,23.84,0,0,0,56,88H216Zm-48-60a12,12,0,1,1,12,12A12,12,0,0,1,168,140Z"></path></svg>
                                </div>
                            </div>
                        </div>
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
                    <div>
                        <div class="main-card-icon orange">
                            <div class="avatar avatar-lg avatar-rounded bg-primary-transparent svg-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256"><path d="M224,200h-8V40a8,8,0,0,0-8-8H152a8,8,0,0,0-8,8V80H96a8,8,0,0,0-8,8v40H48a8,8,0,0,0-8,8v64H32a8,8,0,0,0,0,16H224a8,8,0,0,0,0-16ZM160,48h40V200H160ZM104,96h40V200H104ZM56,144H88v56H56Z"></path></svg>
                            </div>
                        </div>
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
                    <div>
                        <div class="main-card-icon success">
                            <div class="avatar avatar-lg bg-success-transparent border border-success border-opacity-10">
                                <div class="avatar avatar-sm svg-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M40,192a16,16,0,0,0,16,16H216a8,8,0,0,0,8-8V88a8,8,0,0,0-8-8H56A16,16,0,0,1,40,64Z" opacity="0.2"></path><path d="M40,64V192a16,16,0,0,0,16,16H216a8,8,0,0,0,8-8V88a8,8,0,0,0-8-8H56A16,16,0,0,1,40,64h0A16,16,0,0,1,56,48H192" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path><circle cx="180" cy="140" r="12"></circle></svg>
                                </div>
                            </div>
                        </div>
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
                    <div>
                        <div class="main-card-icon success">
                            <div class="avatar avatar-lg bg-success-transparent border border-success border-opacity-10">
                                <div class="avatar avatar-sm svg-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256"><path d="M200,168a48.05,48.05,0,0,1-48,48H136v16a8,8,0,0,1-16,0V216H104a48.05,48.05,0,0,1-48-48,8,8,0,0,1,16,0,32,32,0,0,0,32,32h48a32,32,0,0,0,0-64H112a48,48,0,0,1,0-96h8V24a8,8,0,0,1,16,0V40h8a48.05,48.05,0,0,1,48,48,8,8,0,0,1-16,0,32,32,0,0,0-32-32H112a32,32,0,0,0,0,64h40A48.05,48.05,0,0,1,200,168Z"></path></svg>
                                </div>
                            </div>
                        </div>
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
                    <div>
                        <div class="main-card-icon secondary">
                            <div class="avatar avatar-lg bg-secondary-transparent border border-secondary border-opacity-10">
                                <div class="avatar avatar-sm svg-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256"><path d="M216,72H56a8,8,0,0,1,0-16H192a8,8,0,0,0,0-16H56A24,24,0,0,0,32,64V192a24,24,0,0,0,24,24H216a16,16,0,0,0,16-16V88A16,16,0,0,0,216,72Zm0,128H56a8,8,0,0,1-8-8V86.63A23.84,23.84,0,0,0,56,88H216Zm-48-60a12,12,0,1,1,12,12A12,12,0,0,1,168,140Z"></path></svg>
                                </div>
                            </div>
                        </div>
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
                    <div>
                        <div class="main-card-icon success">
                            <div class="avatar avatar-lg bg-success-transparent border border-success border-opacity-10">
                                <div class="avatar avatar-sm svg-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M40,192a16,16,0,0,0,16,16H216a8,8,0,0,0,8-8V88a8,8,0,0,0-8-8H56A16,16,0,0,1,40,64Z" opacity="0.2"></path><path d="M40,64V192a16,16,0,0,0,16,16H216a8,8,0,0,0,8-8V88a8,8,0,0,0-8-8H56A16,16,0,0,1,40,64h0A16,16,0,0,1,56,48H192" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path><circle cx="180" cy="140" r="12"></circle></svg>
                                </div>
                            </div>
                        </div>
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
                    <div>
                        <div class="main-card-icon success">
                            <div class="avatar avatar-lg bg-success-transparent border border-success border-opacity-10">
                                <div class="avatar avatar-sm svg-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256"><path d="M200,168a48.05,48.05,0,0,1-48,48H136v16a8,8,0,0,1-16,0V216H104a48.05,48.05,0,0,1-48-48,8,8,0,0,1,16,0,32,32,0,0,0,32,32h48a32,32,0,0,0,0-64H112a48,48,0,0,1,0-96h8V24a8,8,0,0,1,16,0V40h8a48.05,48.05,0,0,1,48,48,8,8,0,0,1-16,0,32,32,0,0,0-32-32H112a32,32,0,0,0,0,64h40A48.05,48.05,0,0,1,200,168Z"></path></svg>
                                </div>
                            </div>
                        </div>
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
                    <div>
                        <div class="main-card-icon secondary">
                            <div class="avatar avatar-lg bg-secondary-transparent border border-secondary border-opacity-10">
                                <div class="avatar avatar-sm svg-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256"><path d="M216,72H56a8,8,0,0,1,0-16H192a8,8,0,0,0,0-16H56A24,24,0,0,0,32,64V192a24,24,0,0,0,24,24H216a16,16,0,0,0,16-16V88A16,16,0,0,0,216,72Zm0,128H56a8,8,0,0,1-8-8V86.63A23.84,23.84,0,0,0,56,88H216Zm-48-60a12,12,0,1,1,12,12A12,12,0,0,1,168,140Z"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- End:: row-3 -->













<!-- Inclua os CSS e JS do Flatpickr (ou outra biblioteca de date picker que você preferir) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>







<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>

        <!-- Apex Charts JS -->
        <script src="<?php echo $baseUrl; ?>/assets/libs/apexcharts/apexcharts.min.js"></script>
        
 

<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include '../layouts/base.php'; ?>
<!-- This code use for render base file -->

 

<!-- Internal Apex Area Charts JS -->
<script src="../assets/js/apexcharts-area.js"></script>













<!-- Internal Apex Area Charts JS -->
<script src="../assets/js/apexcharts-area.js"></script>



