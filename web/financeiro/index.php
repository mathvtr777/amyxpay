




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

// Consultar o status do usuário e a taxa_cash_out pelo email
$sql = "SELECT status, taxa_cash_out FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($status, $taxa_cash_out);
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




$sqlSumDepositoLiquido = "SELECT SUM(deposito_liquido) AS sumDepositoLiquido FROM solicitacoes WHERE user_id = ? AND status = 'PAID_OUT'";
$stmtSumDepositoLiquido = $conn->prepare($sqlSumDepositoLiquido);
$stmtSumDepositoLiquido->bind_param("s", $user_id2);
$stmtSumDepositoLiquido->execute();
$stmtSumDepositoLiquido->bind_result($sumDepositoLiquido);
$stmtSumDepositoLiquido->fetch();
$stmtSumDepositoLiquido->close();


// Consultar a data real mais recente (se necessário)
$sqlRealDate = "SELECT MAX(real_data) AS realDate FROM solicitacoes WHERE user_id = ?";
$stmtRealDate = $conn->prepare($sqlRealDate);
$stmtRealDate->bind_param("i", $user_id);
$stmtRealDate->execute();
$stmtRealDate->bind_result($realDate);
$stmtRealDate->fetch();
$stmtRealDate->close();


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


// Fechar a conexão
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
                   



                     
                    </div>
                    <!-- End::page-header -->

                    <!-- Start:: row-1 -->
                    <div class="row">
                        <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                            <div class="card custom-card">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div>
                                            <div>
                                                <span class="d-block mb-2">DISPONIVEL + SALDO PENDENTE</span>
                                                <h5 class="mb-4 fs-4">R$  <?php echo number_format($saldoliquido, 2, ',', '.'); ?></h5>
                                            </div>
                                            <span class="text-success me-2 fw-medium d-inline-block"></span><span class="text-muted">SALDO TOTAL</span>
                                        </div>
                                        <div>
                                            <div class="main-card-icon success">
                                                <div class="avatar avatar-lg bg-success-transparent border border-success border-opacity-10">
                                                    <div class="avatar avatar-sm svg-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M40,192a16,16,0,0,0,16,16H216a8,8,0,0,0,8-8V88a8,8,0,0,0-8-8H56A16,16,0,0,1,40,64Z" opacity="0.2"></path><path d="M40,64V192a16,16,0,0,0,16,16H216a8,8,0,0,0,8-8V88a8,8,0,0,0-8-8H56A16,16,0,0,1,40,64h0A16,16,0,0,1,56,48H192" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path><circle cx="180" cy="140" r="12"></circle></svg>   </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                            <div class="card custom-card main-card">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div>
                                            <div>
                                                <span class="d-block mb-2">DISPONIVEL PARA SAQUE</span>
                                                <h5 class="mb-4 fs-4">R$ <?php echo number_format($saldoliquido, 2, ',', '.'); ?></h5>
                                            </div>
                                            <span class="text-success me-2 fw-medium d-inline-block"></span><span class="text-muted">SALDO DISPONIVEL</span>
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
                  
                        
                    </div>
                    <!-- End:: row-1 -->






                    
                    <?php
$saldoBaixo = $sumDepositoLiquido < 5;
?>

  

<?php
// Verifica se $sumDepositoLiquido está vazio ou não definido
if (empty($sumDepositoLiquido)) {
    $sumDepositoLiquido = 0;
}
?>




<!-- Start::row-1 -->
<div class="row">
    <div class="col-xl-6">
        <div class="card custom-card">
            <div class="card-body p-0">
                <div class="p-3 d-grid border-bottom border-block-end-dashed">
                    <button class="btn btn-primary d-flex align-items-center justify-content-center" 
                            data-bs-toggle="modal" 
                            data-bs-target="#addtask" 
                            data-saldo="<?php echo htmlspecialchars($saldoliquido); ?>">
                        <i class="ri-add-circle-line fs-16 align-middle me-1"></i> Solicitar saque
                    </button>
                    
                    <!-- Modal -->
                    <div class="modal fade" id="addtask" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title" id="mail-ComposeLabel">Novo Saque</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form id="saqueForm" method="POST" action="insert_retirada.php" enctype="multipart/form-data">
                                    <div class="modal-body px-4">
                                        <div class="row gy-2">

                                            <!-- Verificação de saldo baixo -->
                                            <?php if ($saldoBaixo): ?>
                                                <div class="alert alert-danger mt-4">
                                                    <strong>Saldo muito baixo para realizar um saque.</strong>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Verificação de saque em processamento -->
                                            <?php
                                            include '../conectarbanco.php';
                                                // Conectar ao banco de dados
                                            $conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

                                            // Verificar conexão
                                            if ($conn->connect_error) {
                                                die("Falha na conexão: " . $conn->connect_error);
                                            }
                                            // Consultar se já existe um saque em processamento
                                            $sqlCheck = "SELECT COUNT(*) as count FROM retiradas WHERE user_id = ? AND status = '0'";
                                            $stmtCheck = $conn->prepare($sqlCheck);
                                            $stmtCheck->bind_param("s", $user_id);
                                            $stmtCheck->execute();
                                            $stmtCheck->bind_result($count);
                                            $stmtCheck->fetch();
                                            $stmtCheck->close();

                                            if ($count > 0): ?>
                                                <div class="alert alert-warning mt-4">
                                                    <strong>Já existe um saque em processamento. Aguarde a conclusão.</strong>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Exibição do saldo disponível -->
                                            <div class="alert alert-info mt-4">
                                                <ul>
                                                    <li><strong>DISPONÍVEL PARA SAQUE:</strong> R$: <?php echo number_format($saldoliquido, 2, ',', '.'); ?></li>
                                                </ul>
                                            </div>

                                            <!-- Campo de valor -->
                                            <div class="col-xl-12">
                                                <label for="valor" class="form-label">Valor</label>
                                                <input type="number" step="0.01" class="form-control" id="valor" name="valor" placeholder="Valor" required>
                                                <div id="valorError" class="text-danger mt-2" style="display: none;">Saldo insuficiente para o valor solicitado.</div>
                                            </div>

                                            <div class="col-xl-12">
                                                <input type="hidden" id="tipo_chave" name="tipo_chave" value="CPF">
                                                <label class="form-label">Tipo de Chave</label>
                                                <input type="text" class="form-control" value="CPF" readonly>
                                            </div>

                                            <div class="col-xl-12">
                                                <label for="chave" class="form-label">Chave PIX:</label>
                                                <input type="text" class="form-control" id="chave" name="chave" placeholder="Chave" required>
                                            </div>

                                            <!-- Campo oculto para o ID do usuário -->
                                            <input type="hidden" id="user_id" name="user_id" value="<?php echo htmlspecialchars($email); ?>">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary" <?php echo ($count > 0) ? 'disabled' : ''; ?>>Solicitar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Explicação sobre taxas padrão -->
                <div class="alert alert-info mt-4">
                    <ul>
                        <li><strong>Taxa de saque:</strong> R$: <?php echo htmlspecialchars($taxa_cash_out); ?></li>
                        <li><strong>Limite Pessoa física:</strong> R$ 5.000,00 /mês</li>
                        <li><strong>Limite Pessoa jurídica:</strong> Sem limite</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::row-1 -->

<script>
document.getElementById('saqueForm').addEventListener('submit', function(event) {
    var saldo = <?php echo $saldoliquido; ?>; // Corrigido para usar PHP para obter o saldo
    var valor = parseFloat(document.getElementById('valor').value);
    var valorError = document.getElementById('valorError');
    
    // Verifica se o saldo é zero ou se o valor solicitado é maior que o saldo
    if (saldo <= 0) {
        valorError.textContent = 'Saldo insuficiente para realizar um saque.';
        valorError.style.display = 'block';
        event.preventDefault(); // Evita o envio do formulário
    } else if (valor > saldo) {
        valorError.textContent = 'Saldo insuficiente para o valor solicitado.';
        valorError.style.display = 'block';
        event.preventDefault(); // Evita o envio do formulário
    } else {
        valorError.style.display = 'none'; // Oculta a mensagem de erro se tudo estiver certo
    }
});
</script>








<?php


// Verificar se o e-mail está presente na sessão
if (!isset($_SESSION['email'])) {
    // Se o e-mail não estiver presente na sessão, redirecione para outra página
    header("Location: ../");
    exit; // Certifique-se de sair do script após o redirecionamento
}

include '../conectarbanco.php';

// Obter o e-mail da sessão
$email = $_SESSION['email'];

// Conectar ao banco de dados
$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

// Verifique a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Obter o user_id com base no e-mail da sessão
$sqlUserId = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$sqlUserId->bind_param('s', $email);
$sqlUserId->execute();
$resultUserId = $sqlUserId->get_result();

if ($resultUserId->num_rows == 0) {
    echo "Usuário não encontrado.";
    $sqlUserId->close();
    $conn->close();
    exit;
}

$userRow = $resultUserId->fetch_assoc();
$user_id = $userRow['user_id'];
$sqlUserId->close();

// Configurações de paginação
$limit = 10; // Número de registros por página
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Página atual
$offset = ($page - 1) * $limit;

// Consulta para obter o número total de registros com user_id igual ao e-mail da sessão
$totalResult = $conn->prepare("SELECT COUNT(*) AS total FROM retiradas WHERE user_id = ?");
$totalResult->bind_param('s', $user_id);
$totalResult->execute();
$totalRow = $totalResult->get_result()->fetch_assoc();
$totalRecords = $totalRow['total'];
$totalPages = ceil($totalRecords / $limit);

// Consulta para obter os registros com user_id igual ao e-mail da sessão
$sql = "SELECT id, referencia, valor, valor_liquido, tipo_chave, chave, status, data_solicitacao, data_pagamento, taxa_cash_out 
        FROM retiradas 
        WHERE user_id = ?
        ORDER BY data_solicitacao DESC 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $user_id, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- Start::row-2 -->
<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">
                    Retiradas
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table text-nowrap table-bordered">
                        <thead>
                            <tr>
                               
                                <th scope="col">Valor</th>
                                <th scope="col">Valor Líquido</th>
                                <th scope="col">Tipo de Chave</th>
                                <th scope="col">Chave</th>
                                <th scope="col">Status</th>
                                <th scope="col">Data de Solicitação</th>
                            
                              
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                // Itera sobre os resultados e exibe cada linha na tabela
                                while ($row = $result->fetch_assoc()) {
                                    $statusBadge = $row['status'] == '1' ? 'bg-success-transparent' : 'bg-light text-dark';
                                    $statusText = $row['status'] == '1' ? 'Pago' : 'Pendente';
                                    echo "<tr>";
                                  
                                    echo "<td>{$row['valor']}</td>";
                                    echo "<td>{$row['valor_liquido']}</td>";
                                    echo "<td>{$row['tipo_chave']}</td>";
                                    echo "<td>{$row['chave']}</td>";
                                    echo "<td><span class='badge {$statusBadge}'>{$statusText}</span></td>";
                                    echo "<td>{$row['data_solicitacao']}</td>";
                                  
                                   
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9'>Nenhum registro encontrado</td></tr>";
                            }
                            $stmt->close();
                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>
                <!-- Paginação -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
<!-- End::row-2 -->






                   

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






