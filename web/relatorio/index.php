

<?php
session_start();

// Verifica se o parâmetro de logout foi passado na URL
if (isset($_GET['logout'])) {
    // Destroi a sessão
    session_destroy();
    // Redireciona para a página inicial
    header("Location: ../");
    exit;
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
  // Redirecionar imediatamente para a página ../home se o status for 0
  header("Location: ../home");
  exit;
}

// Verificar o status do usuário
if ($status == 5) {
    // Redirecionar imediatamente para a página ../home se o status for 0
    header("Location: ../home");
    exit;
  }


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
$sql = "SELECT user_id, nome, status, permission, transacoes_aproved FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_id, $nome, $status, $permission, $transacoes_aproved);
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

// Número de registros por página
$limit = 10;

// Página atual
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Atualizar a consulta SQL para buscar as solicitações com limite e deslocamento
$sql_solicitacoes = "SELECT id, externalreference, amount, client_name, client_document, client_email, real_data, status, paymentcode 
                     FROM solicitacoes 
                     WHERE user_id = ? 
                     ORDER BY id DESC 
                     LIMIT ? OFFSET ?";
$stmt_solicitacoes = $conn->prepare($sql_solicitacoes);
$stmt_solicitacoes->bind_param("sii", $user_id2, $limit, $offset); // 's' para string, 'i' para inteiro
$stmt_solicitacoes->execute();
$result_solicitacoes = $stmt_solicitacoes->get_result();

// Contar o total de registros para a paginação
$sql_count = "SELECT COUNT(*) as total FROM solicitacoes WHERE user_id = ?";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("s", $user_id2);
$stmt_count->execute();
$total_result = $stmt_count->get_result()->fetch_assoc();
$total_records = $total_result['total'];
$total_pages = ceil($total_records / $limit);

// Fechar a conexão
$stmt_solicitacoes->close();
$stmt_count->close();
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



                    <style>
.pagination {
    display: flex;
    justify-content: center;
    padding: 10px 0;
}

.pagination-link {
    display: inline-block;
    padding: 8px 8px;
    margin: 0 4px;
    text-decoration: none;
    color: #007bff;
    border: 1px solid #007bff;
    border-radius: 4px;
    transition: background-color 0.3s, color 0.3s;
}

.pagination-link:hover {
    background-color: #007bff;
    color: white;
}

.pagination-link.active {
    background-color: #007bff;
    color: white;
    border: 1px solid #007bff;
}

.pagination-link.disabled {
    color: #6c757d;
    border: 1px solid #6c757d;
    cursor: not-allowed;
}
</style>



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
                                <th scope="col">Depósito Líquido</th>
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
                                    <td>
                                        <div>
                                            <span class="d-block fw-medium mb-1">R$ <?php echo number_format($row['net_deposit'], 2); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo $status_badge; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Paginação -->
                <div class="pagination">
                    <a href="?page=<?php echo $page - 1; ?>" class="pagination-link <?php if ($page == 1) echo 'disabled'; ?>" aria-label="Previous">&laquo; Previous</a>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="pagination-link <?php if ($i == $page) echo 'active'; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <a href="?page=<?php echo $page + 1; ?>" class="pagination-link <?php if ($page == $total_pages) echo 'disabled'; ?>" aria-label="Next">Next &raquo;</a>
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

 




