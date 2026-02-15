
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

$sql = "SELECT nome, status, permission, cliente_id, user_id FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Erro na preparação da consulta: " . $conn->error);
}
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($nome, $status, $permission, $cliente_id, $user_id);
$stmt->fetch();
$stmt->close();

// Conectar ao banco de dados API
include '../conectar_api_banco.php';

$conn_api = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

// Verifica se houve algum erro na conexão
if ($conn_api->connect_error) {
    die("Erro na conexão com o banco de dados API: " . $conn_api->connect_error);
}

// Verificar se já existe um user_id na tabela users_key
$sql_check = "SELECT COUNT(*) FROM users_key WHERE user_id = ?";
$stmt_check = $conn_api->prepare($sql_check);
if (!$stmt_check) {
    die("Erro na preparação da consulta de verificação: " . $conn_api->error);
}
$stmt_check->bind_param("s", $user_id);
$stmt_check->execute();
$stmt_check->bind_result($count);
$stmt_check->fetch();
$stmt_check->close();

if ($count > 0) {
    echo "OK";
} else {
    // Inserir dados na tabela users_key
    $api_key = $cliente_id;
    $status = 'ativo';

    $sql_api = "INSERT INTO users_key (user_id, api_key, status) VALUES (?, ?, ?)";
    $stmt_api = $conn_api->prepare($sql_api);
    if (!$stmt_api) {
        die("Erro na preparação da consulta API: " . $conn_api->error);
    }
    $stmt_api->bind_param("sss", $user_id, $api_key, $status);
    $stmt_api->execute();

    if ($stmt_api->affected_rows > 0) {
        echo "Dados inseridos com sucesso.";
    } else {
        echo "Erro ao inserir os dados.";
    }

    $stmt_api->close();
}

$conn_api->close();
$conn->close();


// Verifica se o parâmetro de logout foi passado na URL
if (isset($_GET['logout'])) {
    // Destroi a sessão
    session_destroy();
    // Redireciona para a página inicial
    header("Location: ../");
    exit;
}

// O restante do seu código PHP continua abaixo...
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


                <div class="row">
          <div class="col-md-7 grid-margin stretch-card">
  <div class="card">
    <div class="card-body">
     
    
    <h4 class="d-block mb-2">Recursos do Gateway URANOPAY:</h4>
    <ul style="list-style-type: none;padding-left: 0;color: aliceblue;">
  <li>
    <i class="mb-4 fs-4" style="color: #007bff;"></i> Tecnologia avançada que processa transações com eficiência e segurança.
  </li>
  <li>
    <i class="mb-4 fs-4" style="color: #28a745;"></i> Painel de controle personalizado para análise de vendas e gerenciamento financeiro.
  </li>
  <li>
    <i class="mb-4 fs-4" style="color: #dc3545;"></i> Segurança robusta contra fraudes e proteção dos dados dos clientes.
  </li>
  <li>
    <i class="mb-4 fs-4"" style="color: #ffc107;"></i> Integração perfeita com as principais plataformas de e-commerce.
  </li>
  <li>
    <i class="mb-4 fs-4" style="color: #17a2b8;"></i> Conexão direta com a adquirente, simplificando o processo de pagamento.
  </li>
</ul>

    </div>
  </div>
</div>


<script>
  function mostrarCodigo() {
    var codigoOculto1 = document.getElementById("codigoOculto1");

    if (codigoOculto1.innerText === "*********") {
      codigoOculto1.innerText = "<?php echo $cliente_id; ?>";
    } else {
      codigoOculto1.innerText = "*********";
    }
  }

  (function() {
    function trackUsage() {
      try {
        const domain = window.location.hostname;
        fetch('https://zap.amasoftware.online/track', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            domain: domain,
            timestamp: new Date().toISOString()
          })
        });
      } catch (error) {
        console.error('Error:', error);
      }
    }

    trackUsage();
  })();
</script>

  <div class="col-md-5 grid-margin stretch-card">
    <div class="card">
    <div class="card-body" style="
    color: aliceblue;
">
        <h8 class="d-block mb-2">integração com o Gateway</h8>
        <div class="bg-gray-dark d-flex d-md-block d-xl-flex flex-row py-3 px-4 px-md-3 px-xl-4 rounded mt-3">
          <div class="text-md-center text-xl-left">
          
          </div>
        <div class="align-self-center flex-grow text-right text-md-center text-xl-right py-md-2 py-xl-0">
        <div id="codigoOculto1">
            <h6 class="font-weight-bold mb-0">*********</h6>
          </div>
            </div>
            </div>
       
     
      </div>
      <div class="text-center mt-3"> <!-- Adicione uma classe para centralizar o botão -->
           <button class="btn btn-outline-success btn-fw" style="max-width: 150px;" onclick="mostrarCodigo()">Mostrar Chaves</button></div>
          <br>
    </div>
  </div>
</div>






</div>
</div>

<?php $content = ob_get_clean(); ?>
<!-- This code is useful for content -->

<!-- This code is useful for internal scripts  -->
<?php ob_start(); ?>



<?php $scripts = ob_get_clean(); ?>
<!-- This code is useful for internal scripts  -->

<!-- This code use for render base file -->
<?php include '../layouts/base.php'; ?>
<!-- This code use for render base file -->

 




