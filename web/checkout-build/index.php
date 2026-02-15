


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

$sql = "SELECT user_id, nome, status, permission, saldo, transacoes_aproved, cliente_id FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_id, $nome, $status, $permission, $saldo, $transacoes_aproved, $cliente_id);
$stmt->fetch();

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Pegar o valor do produto enviado pelo formulário
    $valor_checkout = $_POST['valor_checkout'];

    // Remover o ponto de milhar, caso exista
    $valor_checkout = str_replace('.', '', $valor_checkout);

    // Substituir a vírgula por ponto
    $valor_checkout = str_replace(',', '.', $valor_checkout);

    // Validar se o valor é um número válido
    if (!is_numeric($valor_checkout)) {
        echo "Valor inválido. Por favor, insira um valor numérico com ponto.";
        exit; // Parar o script caso o valor não seja válido
    }

    // Caso o valor seja válido, você pode continuar com a lógica do processamento
    echo "Valor válido: " . $valor_checkout;
}

// Armazenar os dados na sessão
$_SESSION['user_id'] = $user_id;
$_SESSION['cliente_id'] = $cliente_id; // Adiciona cliente_id à sessão

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

// Consulta para obter os dados da tabela checkout_build onde a coluna email é igual ao e-mail da sessão
$sql = "SELECT id, name_produto, valor, referencia, logo_produto, obrigado_page, key_gateway, ativo, url_checkout 
        FROM checkout_build 
        WHERE email = ?";

// Preparar e executar a consulta
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();

// Obter o resultado
$result = $stmt->get_result();
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


                  

                    <!-- Start::row-1 -->
<div class="row">
    <div class="col-xl-3">
        <div class="card custom-card">
            <div class="card-body p-0">
                <div class="p-3 d-grid border-bottom border-block-end-dashed">
                    <button class="btn btn-primary d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#addtask">
                        <i class="ri-add-circle-line fs-16 align-middle me-1"></i>Criar novo Checkout
                    </button>
                    <div class="modal fade" id="addtask" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title" id="mail-ComposeLabel">Novo Checkout</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form id="checkoutForm" method="POST" action="insert_checkout.php" enctype="multipart/form-data">
                                    <div class="modal-body px-4">
                                        <div class="row gy-2">
                                            <div class="col-xl-12">
                                                <label for="produto_name" class="form-label">Nome do Produto</label>
                                                <input type="text" class="form-control" id="produto_name" name="produto_name" placeholder="Produto" required>
                                            </div>
                                        <div class="col-xl-12">
                                            <label for="valor_checkout" class="form-label">Valor</label>
                                            <input type="text" class="form-control" id="valor_checkout" name="valor_checkout" placeholder="Valor" required oninput="this.value = this.value.replace(',', '.').replace(/(\d)(?=(\d{3})+\.)/g, '$1.')" />
                                        </div>
                                            <div class="col-xl-12">
                                                <label for="obrigado_page" class="form-label">Página de Obrigado</label>
                                                <input type="text" class="form-control" id="obrigado_page" name="obrigado_page" placeholder="URL da Página de Obrigado" required>
                                            </div>
                        
                                            <div class="col-xl-8">
                                                <label for="formFile" class="form-label">Foto do Produto</label>
                                                <input class="form-control" type="file" id="formFile" name="formFile" required>
                                            </div>

                                        
                                            <div class="col-xl-12">
    <label for="bannerFile" class="form-label">Banner do Produto</label>
    <input class="form-control" type="file" id="bannerFile" name="bannerFile" required>
    <div class="alert alert-info mt-4">
                  
                    <p>Por favor, faça o upload de um banner nas dimensões recomendadas: 1200x400 pixels.</p>
              
                      </div>

</div>

                                            <div class="col-xl-4">
                                                <label for="status" class="form-label">Status</label>
                                                <select class="form-control" id="status" name="status" required>
                                                    <option value="1">Ativo</option>
                                             
                                                </select>
                                            </div>
                                            
                                            <!-- Campo oculto para cliente_id -->
                                            <input type="hidden" id="cliente_id" name="cliente_id" value="<?php echo $_SESSION['cliente_id']; ?>">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Create</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-3 task-navigation border-bottom border-block-end-dashed">
                </div>
            </div>
        </div>
    </div>





                            <div class="col-xl-9">
    <div class="card custom-card">
        <div class="card-header justify-content-between">
            <div class="card-title">
                Produtos
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table text-nowrap table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">Produto</th>
                            <th scope="col">Status</th>
                            <th scope="col">Valor</th>
                            <th scope="col">Link Checkout</th>
                            <th scope="col">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            // Itera sobre os resultados e exibe cada linha na tabela
                            while($row = $result->fetch_assoc()) {
                                $statusBadge = $row['ativo'] ? 'bg-success-transparent' : 'bg-light text-dark';
                                $statusText = $row['ativo'] ? 'Active' : 'Inactive';
                                // Supondo que $row['url_checkout'] contenha a URL original
$url_checkout = $row['url_checkout'];

// Cria o URL para o segundo botão, substituindo 'v1' por 'v2'
$url_checkout_v2 = str_replace('v1', 'v2', $url_checkout);
                                echo "<tr>";
                                echo "<th scope='row'>";
                                echo "<div class='d-flex align-items-center'>";
                                echo "<span class='avatar avatar-xs me-2 online avatar-rounded'>";
                                echo "<img src='{$row['logo_produto']}' alt='img'>";
                                echo "</span>{$row['name_produto']}";
                                echo "</div>";
                                echo "</th>";
                                echo "<td><span class='badge {$statusBadge}'>{$statusText}</span></td>";
                                echo "<td>RS " . number_format($row['valor'], 2, ',', '.') . "</td>";
                                echo "<td>";
                                // Adiciona um botão que redireciona para a URL do checkout em uma nova guia
                                echo "
                                <a href='{$url_checkout}' class='btn btn-primary' target='_blank'>DIGITAL</a>
                                <a href='{$url_checkout_v2}' class='btn btn-primary' target='_blank'>FISICO</a>
                            "; echo "</td>";
                                echo "<td>";
                                echo "<div class='hstack gap-2 flex-wrap'>";
                                echo "<a href='#' class='btn btn-info btn-edit' data-bs-toggle='modal' data-bs-target='#editModal' data-id='{$row['id']}' data-name='{$row['name_produto']}' data-value='{$row['valor']}' data-thank-you-page='{$row['obrigado_page']}' data-status='{$row['ativo']}'><i class='ri-edit-line'></i></a>";
                                echo "<a href='deletar_produto.php?id={$row['id']}' class='btn btn-danger btn-delete' onclick='return confirm(\"Você tem certeza que deseja excluir este produto?\")'><i class='ri-delete-bin-5-line'></i></a>";
                                echo "</div>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>Nenhum produto encontrado</td></tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



                        </div>
                        <!--End::row-1 -->



         
<!-- Modal para Editar Checkout -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="editModalLabel">Editar Checkout</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm" method="POST" action="update_checkout.php">

                <div class="modal-body px-4">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="row gy-2">
                        <div class="col-xl-12">
                            <label for="edit_valor_checkout" class="form-label">Valor</label>
                            <input type="text" class="form-control" id="edit_valor_checkout" name="valor_checkout" placeholder="Valor">
                        </div>
                        <div class="col-xl-12">
                            <label for="edit_obrigado_page" class="form-label">Página de Obrigado</label>
                            <input type="text" class="form-control" id="edit_obrigado_page" name="obrigado_page" placeholder="URL da Página de Obrigado">
                        </div>
                    
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Seleciona todos os botões de edição
    const editButtons = document.querySelectorAll('.btn-edit');

    // Adiciona um listener de evento para cada botão
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Obtém os dados do atributo data-*
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const value = this.getAttribute('data-value');
            const thankYouPage = this.getAttribute('data-thank-you-page');
            const status = this.getAttribute('data-status');

            // Preenche os campos do modal com os dados
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_valor_checkout').value = value;
            document.getElementById('edit_obrigado_page').value = thankYouPage;

            // Aqui você pode adicionar lógica para preencher outros campos se necessário
        });
    });
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

 

    <!-- Dragula JS -->
    <script src="<?php echo $baseUrl; ?>/assets/libs/dragula/dragula.min.js"></script>
        
        <!-- Internal To-Do-List JS -->
        <script src="<?php echo $baseUrl; ?>/assets/js/todolist.js"></script>







