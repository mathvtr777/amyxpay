<?php
session_start();

// Verificar se o e-mail está presente na sessão
if (!isset($_SESSION['email'])) {
    // Se o e-mail não estiver presente na sessão, redirecione para outra página
    header("Location: ../");
    exit; // Certifique-se de sair do script após o redirecionamento
}

include '../conectarbanco.php';

$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

// Verifica se houve algum erro na conexão
if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

// Recuperar o e-mail da sessão
$email = $_SESSION['email'];

// Consultar o status do usuário pelo email
$sql = "SELECT status FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($status);
$stmt->fetch();

$stmt->close();
$conn->close();

// Verificar o status do usuário
if ($status == 0) {
    // Exibir o modal se o status for 0
    echo '
  <script>
    window.onload = function() {
      var myModal = new bootstrap.Modal(document.getElementById("statusModal"), {});
      myModal.show();
    }
  </script>';
}


// Verificar se o e-mail está presente na sessão
if (!isset($_SESSION['email'])) {
    // Se o e-mail não estiver presente na sessão, redirecione para outra página
    header("Location: ../");
    exit; // Certifique-se de sair do script após o redirecionamento
}

include '../conectarbanco.php';

$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

// Verifica se houve algum erro na conexão
if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

// Recuperar o e-mail da sessão
$email = $_SESSION['email'];

$sql = "SELECT user_id, nome, status, permission, saldo, transacoes_aproved FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_id, $nome, $status, $permission, $saldo, $transacoes_aproved);
$stmt->fetch();

// Armazenar o user_id na sessão
$_SESSION['user_id'] = $user_id;

$stmt->close();
$conn->close();
?>




<?php

// Verifica se o parâmetro de logout foi passado na URL
if (isset($_GET['logout'])) {
    // Destroi a sessão
    session_destroy();
    // Redireciona para a página inicial
    header("Location: ../");
    exit;
}
?>





<?php

// Verificar se o e-mail está presente na sessão
if (!isset($_SESSION['email'])) {
    // Se o e-mail não estiver presente na sessão, redirecione para outra página
    header("Location: ../");
    exit; // Certifique-se de sair do script após o redirecionamento
}

// Incluir o arquivo de configuração do banco de dados
include '../conectarbanco.php';

// Criar uma conexão com o banco de dados usando as credenciais fornecidas
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
// Incluir o arquivo de configuração do banco de dados
include '../conectar_api_banco.php';

// Criar a conexão usando as credenciais fornecidas no arquivo incluído
$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

// Verifica se houve algum erro na conexão
if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

// Definir o ID do usuário (presumindo que já está definido em $user_id_var)
$user_id2 = $user_id_var;

// Atualizar a consulta SQL para buscar as últimas 4 solicitações ordenadas por ID decrescente
$sql_solicitacoes = "SELECT id, externalreference, amount, deposito_liquido, client_name, client_document, client_email, real_data, status, paymentcode 
                     FROM solicitacoes 
                     WHERE user_id = ? 
                     ORDER BY id DESC 
                     LIMIT 4";
$stmt_solicitacoes = $conn->prepare($sql_solicitacoes);
$stmt_solicitacoes->bind_param("s", $user_id2); // 's' para string
$stmt_solicitacoes->execute();
$result_solicitacoes = $stmt_solicitacoes->get_result();

// Fechar a conexão
$stmt_solicitacoes->close();
$conn->close();
?>


<?php
// Incluir o arquivo de conexão
include '../conectar_api_banco.php';

// Criar a conexão usando as credenciais fornecidas no arquivo incluído
$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

// Verificar a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    die("User ID não encontrado na sessão.");
}

// Inicializar variáveis
$totalPaidOut = 0;
$totalRequests = 0;
$sumAmountPaidOut = 0;
$realDate = null; // Variável para a coluna real_date

// Obtendo o user_id da sessão
$user_id2 = $_SESSION['user_id'];

// Consultar o número de linhas com status = 'PAID_OUT' para o user_id
$sqlPaidOut = "SELECT COUNT(*) AS totalPaidOut FROM solicitacoes WHERE user_id = ? AND status = 'PAID_OUT'";
$stmtPaidOut = $conn->prepare($sqlPaidOut);
$stmtPaidOut->bind_param("s", $user_id2);
$stmtPaidOut->execute();
$stmtPaidOut->bind_result($totalPaidOut);
$stmtPaidOut->fetch();
$stmtPaidOut->close();

// Consultar o número total de linhas para o user_id
$sqlTotalRequests = "SELECT COUNT(*) AS totalRequests FROM solicitacoes WHERE user_id = ?";
$stmtTotalRequests = $conn->prepare($sqlTotalRequests);
$stmtTotalRequests->bind_param("s", $user_id2);
$stmtTotalRequests->execute();
$stmtTotalRequests->bind_result($totalRequests);
$stmtTotalRequests->fetch();
$stmtTotalRequests->close();

// Consultar a soma dos valores na coluna amount para as linhas com status = 'PAID_OUT' e user_id correspondente
$sqlSumAmountPaidOut = "SELECT SUM(amount) AS sumAmountPaidOut FROM solicitacoes WHERE user_id = ? AND status = 'PAID_OUT'";
$stmtSumAmountPaidOut = $conn->prepare($sqlSumAmountPaidOut);
$stmtSumAmountPaidOut->bind_param("s", $user_id2);
$stmtSumAmountPaidOut->execute();
$stmtSumAmountPaidOut->bind_result($sumAmountPaidOut);
$stmtSumAmountPaidOut->fetch();
$stmtSumAmountPaidOut->close();

// Consultar a soma dos depósitos líquidos para o user_id
$sqlSumDepositoLiquido = "SELECT SUM(deposito_liquido) AS sumDepositoLiquido FROM solicitacoes WHERE user_id = ? AND status = 'PAID_OUT'";
$stmtSumDepositoLiquido = $conn->prepare($sqlSumDepositoLiquido);
$stmtSumDepositoLiquido->bind_param("s", $user_id2);
$stmtSumDepositoLiquido->execute();
$stmtSumDepositoLiquido->bind_result($sumDepositoLiquido);
$stmtSumDepositoLiquido->fetch();
$stmtSumDepositoLiquido->close();

// Consultar a soma dos saques aprovados na tabela `retiradas`
$sqlSumSaquesAprovados = "SELECT SUM(valor_liquido) AS sumSaquesAprovados FROM retiradas WHERE user_id = ? AND status = 1";
$stmtSumSaquesAprovados = $conn->prepare($sqlSumSaquesAprovados);
$stmtSumSaquesAprovados->bind_param("s", $user_id2);
$stmtSumSaquesAprovados->execute();
$stmtSumSaquesAprovados->bind_result($sumSaquesAprovados);
$stmtSumSaquesAprovados->fetch();
$stmtSumSaquesAprovados->close();

// Calcular o saldo líquido
$saldoliquido = $sumDepositoLiquido - ($sumSaquesAprovados ?: 0); // Se não houver saques aprovados, considera 0

// Consultar a data real mais recente (se necessário)
$sqlRealDate = "SELECT MAX(real_data) AS realDate FROM solicitacoes WHERE user_id = ?";
$stmtRealDate = $conn->prepare($sqlRealDate);
$stmtRealDate->bind_param("s", $user_id2);
$stmtRealDate->execute();
$stmtRealDate->bind_result($realDate);
$stmtRealDate->fetch();
$stmtRealDate->close();

// Fechar a conexão
$conn->close();

?>






<?php
$dates = [];
$values = [];

$firstDayOfMonth = date('Y-m-01');
$lastDayOfMonth = date('Y-m-t');

$dailyValues = [];


include '../conectar_api_banco.php';

$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$sql = "SELECT real_data, amount FROM solicitacoes WHERE user_id = ? AND status = 'PAID_OUT' AND real_data BETWEEN ? AND ? ORDER BY real_data";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $user_id2, $firstDayOfMonth, $lastDayOfMonth);
$stmt->execute();
$stmt->bind_result($realDate, $amount);

while ($stmt->fetch()) {
    $dateKey = date('Y-m-d', strtotime($realDate));
    if (!isset($dailyValues[$dateKey])) {
        $dailyValues[$dateKey] = 0;
    }
    $dailyValues[$dateKey] += $amount;
}
$stmt->close();

$conn->close();
$currentDate = $firstDayOfMonth;
while ($currentDate <= $lastDayOfMonth) {
    $formattedDate = date('d M Y', strtotime($currentDate));
    if (isset($dailyValues[$currentDate])) {
        $dates[] = $formattedDate;
        $values[] = $dailyValues[$currentDate];
    } else {
        $dates[] = $formattedDate;
        $values[] = 0;
    }
    $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
}

echo "<script>
    \"use strict\";
    
    var series = {
        \"monthDataSeries1\": {
            \"prices\": " . json_encode($values) . ",
            \"dates\": " . json_encode($dates) . "
        }
    };
</script>";

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






<!-- Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Atenção</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Você precisa concluir o cadastro para ativar sua conta.
            </div>
            <div class="modal-footer">
                <a href="../enviar-doc" class="btn btn-success">Enviar Documentos</a>

            </div>
        </div>
    </div>
</div>

<!-- Adicione o JS do Bootstrap se ainda não estiver incluído -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>










<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Start::page-header -->
        <div class="d-flex align-items-center justify-content-between my-4 page-header-breadcrumb flex-wrap gap-2">
            <div>
                <p class="fw-medium fs-20 mb-0">Olá, <?php echo htmlspecialchars($nome); ?></p>
                <p class="fs-13 text-muted mb-0">Vamos tornar o dia de hoje produtivo!</p>
            </div>





        </div>
        <!-- End::page-header -->



        <?php if ($status == 0): ?>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body p-0">
                            <div class="p-3 d-grid border-bottom border-block-end-dashed">

                                <h5 class="card-title">Ativação de Conta</h5>
                                <p class="card-text">Para ativar sua conta, é necessário o envio de documentos. Por favor, envie os documentos para análise.</p>
                                <a href="../enviar-doc" class="btn btn-success">Enviar Documentos</a>


                            </div>
                            <div class="p-3 task-navigation border-bottom border-block-end-dashed">
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>




            <?php if ($status == 5): ?>

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card custom-card">
                            <div class="card-body p-0">
                                <div class="p-3 d-grid border-bottom border-block-end-dashed">

                                    <h5 class="card-title">Sua conta está em Análise</h5>
                                    <p class="card-text">Nossa equipe está análisando seus documentos e logo vai entrar em contato</p>



                                </div>
                                <div class="p-3 task-navigation border-bottom border-block-end-dashed">
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>







                <!-- Start:: row-1 -->
                <div class="row">
                    <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
                        <div class="card custom-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <div>
                                            <span class="d-block mb-2">Saldo Disponivel</span>
                                            <h5 class="mb-4 fs-4">R$ <?php echo number_format($saldoliquido, 2, ',', '.'); ?></h5>
                                        </div>
                                        <span class="text-success me-2 fw-medium d-inline-block" style="font-s"></span><span class="text-muted">Disponivel para saque</span>
                                    </div>
                                    <div>
                                        <div class="main-card-icon success">
                                            <div class="avatar avatar-lg bg-success-transparent border border-success border-opacity-10">
                                                <div class="avatar avatar-sm svg-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
                                                        <rect width="256" height="256" fill="none"></rect>
                                                        <path d="M40,192a16,16,0,0,0,16,16H216a8,8,0,0,0,8-8V88a8,8,0,0,0-8-8H56A16,16,0,0,1,40,64Z" opacity="0.2"></path>
                                                        <path d="M40,64V192a16,16,0,0,0,16,16H216a8,8,0,0,0,8-8V88a8,8,0,0,0-8-8H56A16,16,0,0,1,40,64h0A16,16,0,0,1,56,48H192" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path>
                                                        <circle cx="180" cy="140" r="12"></circle>
                                                    </svg>
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
                                            <span class="d-block mb-2">PIX Pago</span>
                                            <h5 class="mb-4 fs-4">R$ <?php echo number_format($sumAmountPaidOut ?? 0, 2, ',', '.'); ?></h5>
                                        </div>
                                        <span class="text-success me-2 fw-medium d-inline-block"></span><span class="text-muted">Nesse Mês</span>
                                    </div>
                                    <div>
                                        <div class="main-card-icon success">
                                            <div class="avatar avatar-lg bg-success-transparent border border-success border-opacity-10">
                                                <div class="avatar avatar-sm svg-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256">
                                                        <path d="M200,168a48.05,48.05,0,0,1-48,48H136v16a8,8,0,0,1-16,0V216H104a48.05,48.05,0,0,1-48-48,8,8,0,0,1,16,0,32,32,0,0,0,32,32h48a32,32,0,0,0,0-64H112a48,48,0,0,1,0-96h8V24a8,8,0,0,1,16,0V40h8a48.05,48.05,0,0,1,48,48,8,8,0,0,1-16,0,32,32,0,0,0-32-32H112a32,32,0,0,0,0,64h40A48.05,48.05,0,0,1,200,168Z"></path>
                                                    </svg>
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
                                            <span class="d-block mb-2">PIX Gerados</span>
                                            <h5 class="mb-4 fs-4"> <?php echo htmlspecialchars($totalRequests); ?></h5>
                                        </div>
                                        <span class="text-success me-2 fw-medium d-inline-block"></span><span class="text-muted">Nesse Mês</span>
                                    </div>
                                    <div>
                                        <div class="main-card-icon success">
                                            <div class="avatar avatar-lg bg-success-transparent border border-success border-opacity-10">
                                                <div class="avatar avatar-sm svg-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256">
                                                        <path d="M216,72H56a8,8,0,0,1,0-16H192a8,8,0,0,0,0-16H56A24,24,0,0,0,32,64V192a24,24,0,0,0,24,24H216a16,16,0,0,0,16-16V88A16,16,0,0,0,216,72Zm0,128H56a8,8,0,0,1-8-8V86.63A23.84,23.84,0,0,0,56,88H216Zm-48-60a12,12,0,1,1,12,12A12,12,0,0,1,168,140Z"></path>
                                                    </svg>
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
                                            <span class="d-block mb-2">Volume Transacionado</span>
                                            <h5 class="mb-4 fs-4">R$ <?php echo number_format($sumAmountPaidOut ?? 0, 2, ',', '.'); ?></h5>
                                        </div>
                                        <span class="text-danger me-2 fw-medium d-inline-block">
                                        </span><span class="text-muted">Nesse Mês</span>
                                    </div>
                                    <div>
                                        <div class="main-card-icon orange">
                                            <div class="avatar avatar-lg avatar-rounded bg-success-transparent svg-success">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256">
                                                    <path d="M224,200h-8V40a8,8,0,0,0-8-8H152a8,8,0,0,0-8,8V80H96a8,8,0,0,0-8,8v40H48a8,8,0,0,0-8,8v64H32a8,8,0,0,0,0,16H224a8,8,0,0,0,0-16ZM160,48h40V200H160ZM104,96h40V200H104ZM56,144H88v56H56Z">
                                                    </path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
                <!-- End:: row-1 -->

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">ESTATÍSTICAS DE VENDAS</div>
                            </div>
                            <div class="card-body">
                                <div id="area-basic"></div>
                            </div>
                        </div>
                    </div>
                </div>




                <!-- Start:: row-3 -->
                <!-- Start:: row-3 -->
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card custom-card overflow-hidden">
                            <div class="card-header justify-content-between">
                                <div class="card-title">
                                    TRANSAÇÕES RECENTES
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table text-nowrap">
                                        <thead>
                                            <tr>
                                                <th scope="col">Order ID</th>
                                                <th scope="col">Método de Pagamento</th>
                                                <th scope="col">Valor</th>
                                                <th scope="col">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $result_solicitacoes->fetch_assoc()): ?>
                                                <?php
                                                // Determinar o badge de status
                                                if ($row['status'] == 'PAID_OUT') {
                                                    $status_badge = "<span class='text-success'>Completed</span>";
                                                } elseif ($row['status'] == 'WAITING_FOR_APPROVAL') {
                                                    $status_badge = "<span class='text-info'>Pending</span>";
                                                } else {
                                                    $status_badge = $row['status'];
                                                }
                                                ?>
                                                <tr>
                                                    <td><a href="javascript:void(0)" class="fw-medium fs-13"><?php echo htmlspecialchars($row['id']); ?></a></td>
                                                    <td>
                                                        <div class="d-flex align-items-start gap-2">
                                                            <div>
                                                                <span class="avatar avatar-sm bg-success-transparent">
                                                                    <i class="ri-wallet-3-line fs-18"></i>
                                                                </span>
                                                            </div>
                                                            <div>
                                                                <span class="d-block fw-medium mb-1">PIX CASH IN</span>
                                                                <span class="d-block fs-11 text-muted">Online Transaction</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <span class="d-block fw-medium mb-1">R$ <?php echo number_format($row['amount'], 2); ?></span>
                                                            <span class="d-block fs-11 text-muted"><?php echo date('M d, Y', strtotime($row['real_data'])); ?></span>
                                                        </div>
                                                    </td>
                                                    <td><?php echo $status_badge; ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- End:: row-3 -->



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






    <script>
        // Função para gerar datas do início do mês até hoje
        function getDatesFromStartOfMonth() {
            const dates = [];
            const today = new Date();
            const year = today.getFullYear();
            const month = today.getMonth();
            const lastDay = new Date(year, month + 1, 0).getDate(); // Último dia do mês

            for (let day = 1; day <= lastDay; day++) {
                const date = new Date(year, month, day);
                const dayStr = String(date.getDate()).padStart(2, '0');
                const monthStr = String(date.getMonth() + 1).padStart(2, '0'); // Janeiro é 0
                dates.push(`${dayStr}/${monthStr}`);
            }
            return dates;
        }

        // Dados do PHP
        const labels = getDatesFromStartOfMonth();
        const data = {
            labels: labels,
            datasets: [{
                label: 'Movimentação',
                data: Array.from({
                    length: labels.length
                }, (_, i) => chartData.values[i] || 0), // Preencher com valores do PHP
                borderColor: 'rgba(0, 255, 0, 1)', // Verde limão para a linha
                backgroundColor: 'rgba(0, 255, 0, 0.2)', // Fundo translúcido verde limão
                fill: true,
                borderWidth: 2,
                tension: 0.4
            }]
        };

        // Configuração do gráfico
        const config = {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                scales: {
                    x: {
                        ticks: {
                            color: 'white'
                        },
                        grid: {
                            display: false
                        } // Remove o quadriculado de fundo do eixo x
                    },
                    y: {
                        ticks: {
                            color: 'white',
                            callback: function(value) {
                                return 'R$ ' + value; // Adiciona "R$" aos valores do eixo y
                            }
                        },
                        grid: {
                            display: false
                        } // Remove o quadriculado de fundo do eixo y
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: 'white'
                        }
                    }
                },
                layout: {
                    padding: 20
                }
            }
        };

        // Renderização do gráfico
        const ctx = document.getElementById('areaChart').getContext('2d');
        new Chart(ctx, config);
    </script>








    <!-- Internal Apex Area Charts JS -->
    <script src="../assets/js/apexcharts-area.js"></script>