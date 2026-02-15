<?php
session_start();

// Função para validar os dados do formulário
function validateForm($input)
{
  $input = trim($input);
  $input = stripslashes($input);
  return $input;
}

include '../conectarbanco.php';

$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

// Verifica se houve algum erro na conexão
if ($conn->connect_error) {
  die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Validar e obter os dados do formulário
  $user_id = validateForm($_POST["user_id"]);
  $nome = validateForm($_POST["fullName"]);
  $email = validateForm($_POST["email"]);
  $senha = validateForm($_POST["password"]);
  $telefone = validateForm($_POST["telefone"]);

  // Verificar se o e-mail já existe
  if (emailExists($email, $conn)) {
    $aviso = "O e-mail já está sendo usado.";
  } elseif (userIdExists($user_id, $conn)) {
    $aviso = "Usuário não está disponível!";
  } else {
    // Criptografar a senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // Obter o próximo ID aleatório de 8 dígitos
    $nextId = generateRandomId(8);
    $clienteId = generateRandomString(24);

    $saldo = 0;
    $status = 0;
    $permission = 1;

    // Obter a data e hora atual no fuso horário de São Paulo
    $dataCadastro = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    $dataCadastroFormatada = $dataCadastro->format('Y-m-d H:i:s');

    // Obter os valores padrão de taxa_cash_in e taxa_cash_out da tabela app
    $taxaPadroes = getTaxaPadroes($conn);
    if ($taxaPadroes === false) {
      die("Erro ao obter taxas padrão.");
    }
    $taxa_cash_in = $taxaPadroes['taxa_cash_in_padrao'];
    $taxa_cash_out = $taxaPadroes['taxa_cash_out_padrao'];

    // Atualizar a consulta para incluir o nome, telefone, taxa_cash_in e taxa_cash_out
    $insertQuery = "INSERT INTO users (id, user_id, nome, email, senha, telefone, saldo, data_cadastro, status, permission, cliente_id, taxa_cash_in, taxa_cash_out) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    if (!$stmt) {
      die("Erro na preparação da consulta: " . $conn->error);
    }

    $stmt->bind_param("issssisssssii", $nextId, $user_id, $nome, $email, $senhaHash, $telefone, $saldo, $dataCadastroFormatada, $status, $permission, $clienteId, $taxa_cash_in, $taxa_cash_out);

    if ($stmt->execute()) {
      // Definir o email como uma variável de sessão
      $_SESSION['email'] = $email;

      // Obter a URL do webhook da tabela app
      $webhookUrl = getWebhookUrl($conn);

      // Enviar dados para o webhook se a URL foi encontrada
      if ($webhookUrl !== false) {
        sendToWebhook($webhookUrl, $nome, $email, $telefone);
      }

      // Mensagem de aviso
      $aviso = "Cadastro concluído com sucesso";

      // Redirecionar para a página inicial
      header("refresh:3;url=https://web.vibrationpay.app/");
    } else {
      $aviso = "Erro " . $stmt->error;
    }
    $stmt->close();
  }
}

// Função para verificar se um e-mail já existe na tabela
function emailExists($email, $conn)
{
  $checkEmailQuery = "SELECT email FROM users WHERE email = ?";
  $checkEmailStmt = $conn->prepare($checkEmailQuery);
  if (!$checkEmailStmt) {
    die("Erro na preparação da consulta de verificação de e-mail: " . $conn->error);
  }

  $checkEmailStmt->bind_param("s", $email);
  $checkEmailStmt->execute();
  $checkEmailStmt->store_result();
  $exists = $checkEmailStmt->num_rows > 0;
  $checkEmailStmt->close();
  return $exists;
}

// Função para verificar se um user_id já existe na tabela
function userIdExists($user_id, $conn)
{
  $checkUserIdQuery = "SELECT user_id FROM users WHERE user_id = ?";
  $checkUserIdStmt = $conn->prepare($checkUserIdQuery);
  if (!$checkUserIdStmt) {
    die("Erro na preparação da consulta de verificação de user_id: " . $conn->error);
  }

  $checkUserIdStmt->bind_param("s", $user_id);
  $checkUserIdStmt->execute();
  $checkUserIdStmt->store_result();
  $exists = $checkUserIdStmt->num_rows > 0;
  $checkUserIdStmt->close();
  return $exists;
}

function generateRandomId($length)
{
  $characters = '0123456789';
  $randomId = '';

  for ($i = 0; $i < $length; $i++) {
    $randomId .= $characters[random_int(0, strlen($characters) - 1)];
  }

  return $randomId;
}

function generateRandomString($length)
{
  return bin2hex(random_bytes($length / 2));
}

// Função para obter a URL do webhook da tabela app
function getWebhookUrl($conn)
{
  $query = "SELECT sms_url_cadastro_pendente FROM app LIMIT 1";
  $result = $conn->query($query);

  if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    return $row['sms_url_cadastro_pendente'];
  } else {
    return false;
  }
}

// Função para obter os valores padrão de taxa_cash_in e taxa_cash_out da tabela app
function getTaxaPadroes($conn)
{
  $query = "SELECT taxa_cash_in_padrao, taxa_cash_out_padrao FROM app LIMIT 1";
  $result = $conn->query($query);

  if ($result && $result->num_rows > 0) {
    return $result->fetch_assoc();
  } else {
    return false;
  }
}

// Função para enviar dados ao webhook
function sendToWebhook($url, $name, $email, $phone)
{
  $data = array(
    'event' => 'novo',
    'name' => $name,
    'email' => $email,
    'phone' => $phone
  );

  $options = array(
    'http' => array(
      'header'  => "Content-Type: application/json\r\n",
      'method'  => 'POST',
      'content' => json_encode($data),
    ),
  );

  $context  = stream_context_create($options);
  $result = file_get_contents($url, false, $context);

  if ($result === FALSE) {
    die('Erro ao enviar dados para o webhook');
  }
}

$conn->close();
?>



<!-- Este código gera o URL base do site combinando o protocolo, o nome de domínio e o caminho do diretório -->
<?php
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/../';
?>
<!-- This code generates the base URL for the website by combining the protocol, domain name, and directory path -->

<!-- This code is useful for internal styles  -->
<?php ob_start(); ?>


<?php $styles = ob_get_clean(); ?>
<!-- This code is useful for internal styles  -->

<!-- This code is useful for content -->
<?php ob_start(); ?>

<?php ob_start(); ?>

<body class="authentication-background">
  <?php $body = ob_get_clean(); ?>

  <div class="container">
    <div class="row justify-content-center align-items-center authentication authentication-basic h-100">
      <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-6 col-sm-8 col-12">
        <div class="my-5 d-flex justify-content-center">
          <a href="index.php">
            <img src="../img/URANOPAY-branca.png" alt="logo" class="desktop-dark">
          </a>
        </div>


        <form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="POST">

          <div class="card custom-card my-4">
            <div class="card-body p-5">
              <p class="h4 mb-2 fw-semibold">Entrar</p>
              <p class="mb-4 text-muted fw-normal">Bem-vindo</p>
              <div class="row gy-3">

                <div class="col-xl-12">
                  <label for="signin-username" class="form-label text-default">Usuário</label>
                  <input type="text" id="nome" name="user_id" class="form-control" placeholder="User" required>
                </div>

                <div class="col-xl-12">
                  <label for="signin-username" class="form-label text-default">Nome</label>
                  <input type="text" id="fullName" name="fullName" class="form-control" placeholder="Nome" aria-label="Name" required>
                </div>

                <div class="col-xl-12">
                  <label for="signin-username" class="form-label text-default">Telefone</label>
                  <input type="number" id="telefone" name="telefone" class="form-control" placeholder="Telefone" aria-label="telefone">
                </div>


                <div class="col-xl-12">
                  <label for="signin-username" class="form-label text-default">E-mail</label>
                  <input type="email" id="email" name="email" class="form-control" placeholder="Email" aria-label="Email">
                </div>

                <div class="col-xl-12 mb-2">
                  <label for="signin-password" class="form-label text-default d-block">Password

                  </label>
                  <div class="position-relative">
                    <input type="password" name="password" id="senha" class="form-control" placeholder="Password" aria-label="Password" required minlength="6">
                    <a href="javascript:void(0);" class="show-password-button text-muted" onclick="createpassword('signin-password',this)" id="button-addon2"><i class="ri-eye-off-line align-middle"></i></a>
                  </div>
                  <div class="mt-2">





                    <?php if (!empty($aviso)): ?>
                      <div class="aviso-visible" id="aviso">
                        <button class="close-btn" onclick="document.getElementById('aviso').style.display='none';">&times;</button>
                        <?php echo $aviso; ?>
                      </div>
                    <?php endif; ?>


                  </div>
                </div>
              </div>




              <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary">Entrar</button>
              </div>


        </form>


        <div class="text-center">
          <p class="text-muted mt-3 mb-0">já tem uma conta? <a href="../login" class="text-primary">Inscrever-se</a></p>
        </div>
      </div>
    </div>
  </div>
  </div>
  </div>

  <?php $content = ob_get_clean(); ?>
  <!-- This code is useful for content -->

  <!-- This code is useful for internal scripts  -->
  <?php ob_start(); ?>

  <!-- Show Password JS -->
  <script src="<?php echo $baseUrl; ?>/assets/js/show-password.js"></script>

  <?php $scripts = ob_get_clean(); ?>
  <!-- This code is useful for internal scripts  -->

  <!-- This code use for render base file -->
  <?php include dirname(__FILE__) . '/../layouts/error-base.php'; ?>
  <!-- This code use for render base file -->