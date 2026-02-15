<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $host;
$rootDir = $baseUrl . '/register/';
$logo = $rootDir . "assets/img/logo2.png";
$bg = $rootDir . "assets/img/background.jpg";
?>


<?php
session_start();
if (isset($_SESSION['email'])) {
  header("Location: /home");
  exit();
}

function validateForm($input)
{
  $input = trim($input);
  $input = stripslashes($input);
  return $input;
}

include '../conectarbanco.php';
require_once '../security/GoogleAuthenticator.php';

$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);


if ($conn->connect_error) {
  die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $input = file_get_contents("php://input");
  $data = json_decode($input, true);

  $user_id = isset($data["user_id"]) ? validateForm($data["user_id"]) : null;
  $nome = isset($data["fullName"]) ? validateForm($data["fullName"]) : null;
  $email = isset($data["email"]) ? validateForm($data["email"]) : null;
  $senha = isset($data["password"]) ? validateForm($data["password"]) : null;
  $telefone = isset($data["telefone"]) ? validateForm($data["telefone"]) : null;

  if (emailExists($email, $conn)) {
    echo json_encode(["status" => "warning", "message" => "O e-mail já está sendo usado."]);
    return;
  } elseif (userIdExists($user_id, $conn)) {
    echo json_encode(["status" => "warning", "message" => "Usuário não está disponível!"]);
    return;
  } else {

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);


    $nextId = generateRandomId(8);
    $clienteId = generateRandomString(24);

    $saldo = 0;
    $status = 0;
    $permission = 1;


    $dataCadastro = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    $dataCadastroFormatada = $dataCadastro->format('Y-m-d H:i:s');


    $taxaPadroes = getTaxaPadroes($conn);
    if ($taxaPadroes === false) {
      die("Erro ao obter taxas padrão.");
    }
    $taxa_cash_in = $taxaPadroes['taxa_cash_in_padrao'];
    $taxa_cash_out = $taxaPadroes['taxa_cash_out_padrao'];

    $ga = new PHPGangsta_GoogleAuthenticator();
    $secret = $ga->createSecret();
    $_SESSION['2fa_secret'] = $secret;

    $insertQuery = "INSERT INTO users (id, user_id, nome, email, senha, telefone, saldo, data_cadastro, status, permission, cliente_id, taxa_cash_in, taxa_cash_out,2fa_secret) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    if (!$stmt) {
      die("Erro na preparação da consulta: " . $conn->error);
    }

    $stmt->bind_param("issssisssssiis", $nextId, $user_id, $nome, $email, $senhaHash, $telefone, $saldo, $dataCadastroFormatada, $status, $permission, $clienteId, $taxa_cash_in, $taxa_cash_out, $secret);

    if ($stmt->execute()) {

      $_SESSION['email'] = $email;


      $webhookUrl = getWebhookUrl($conn);


      if ($webhookUrl !== false) {
        sendToWebhook($webhookUrl, $nome, $email, $telefone);
      }
      $_SESSION['user_id'] = $user_id;
      $_SESSION['email'] = $email;

      echo json_encode(["status" => "success", "message" => "Cadastro concluído com sucesso"]);
      return;
    } else {
      echo json_encode(["status" => "warning", "message" => "Erro ao cadastrar. Tente novamente."]);
      return;
    }
    $stmt->close();
  }
}


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
  $result = true/* file_get_contents($url, false, $context) */;

  if ($result === FALSE) {
    die('Erro ao enviar dados para o webhook');
  }
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>Registrar-me</title>
  <!-- loader-->
  <link href="<?= $rootDir ?>assets/css/pace.min.css" rel="stylesheet" />
  <script src="<?= $rootDir ?>assets/js/pace.min.js"></script>
  <!--favicon-->
  <link rel="icon" href="<?= $rootDir ?>assets/images/favicon.ico" type="image/x-icon">
  <!-- Bootstrap core CSS-->
  <link href="<?= $rootDir ?>assets/css/bootstrap.min.css" rel="stylesheet" />
  <!-- animate CSS-->
  <link href="<?= $rootDir ?>assets/css/animate.css" rel="stylesheet" type="text/css" />
  <!-- Icons CSS-->
  <link href="<?= $rootDir ?>assets/css/icons.css" rel="stylesheet" type="text/css" />
  <!-- Custom Style-->
  <link href="<?= $rootDir ?>assets/css/app-style.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .card-body,
    .card-footer {
      background: rgba(0, 0, 0, 0.68);
    }

    .swal2-popup {
      background: rgba(0, 0, 0, 0.68);
      height: 80px;
      display: flex;
      align-items: center;
    }

    .swal2-title {
      color: white;
    }

    .swal2-timer-progress-bar {
      background: #abffaf;
    }
  </style>
</head>

<body style="display: flex;align-items:center;justify-content:center;width:100vw;height:100vh;background: url('<?= $bg ?>')" class="bg-theme bg-theme7">

  <div id="wrapper">
    <div class="card card-authentication1 mx-auto my-5">
      <div class="card-body">
        <div class="card-content p-2">
          <div class="text-center">
            <img width="auto" height="60px" src="<?= $logo ?>" alt="logo icon">
          </div>
          <div class="card-title text-uppercase text-center py-3">Registrar-me</div>
          <form id="registerForm">

            <div class="form-group">
              <label for="nome" class="sr-only">Usuário</label>
              <div class="position-relative has-icon-right">
                <input autofocus type="text" id="nome" name="user_id" class="form-control input-shadow" placeholder="Digite um usuário" required>
                <div class="form-control-position">
                  <i class="fa-solid fa-circle-user"></i>

                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="fullName" class="sr-only">Nome</label>
              <div class="position-relative has-icon-right">
                <input type="text" id="fullName" name="fullName" class="form-control input-shadow" placeholder="Digite seu nome completo" required>
                <div class="form-control-position">
                  <i class="fa-solid fa-user"></i>
                </div>
              </div>
            </div>
            <div class="form-group">
               <label for="telefone" class="sr-only">Telefone</label>
              <div class="position-relative has-icon-right">
                <input type="tel" id="telefone" name="telefone" class="form-control input-shadow" placeholder="Digite seu telefone" required>
                <div class="form-control-position">
                  <i class="fa-solid fa-phone"></i>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="email" class="sr-only">Email</label>
              <div class="position-relative has-icon-right">
                <input type="text" id="email" name="email" class="form-control input-shadow" placeholder="Entre com seu email" required>
                <div class="form-control-position">
                  <i class="fa-solid fa-envelope"></i>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="password" class="sr-only">Senha</label>
              <div class="position-relative has-icon-right">
                <input type="password" id="password" name="password" class="form-control input-shadow" placeholder="Entre com sua senha" required>
                <div class="form-control-position">
                  <i class="fa-solid fa-lock"></i>
                </div>
              </div>
            </div>

            <button type="submit" class="btn btn-success btn-block">Registrar-me</button>
          </form>
        </div>
      </div>
      <div class="card-footer text-center py-3">
        <p class="text-warning mb-0">Já tem uma conta? <a href="/login"> Acessar</a></p>
      </div>
    </div>
  </div>



  <!-- Bootstrap core JavaScript-->
  <script src="<?= $rootDir ?>assets/js/jquery.min.js"></script>
  <script src="<?= $rootDir ?>assets/js/popper.min.js"></script>
  <script src="<?= $rootDir ?>assets/js/bootstrap.min.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const form = document.getElementById("registerForm");

      form.addEventListener("submit", async function(event) {
        event.preventDefault();

        const user_id = document.getElementById("nome").value;
        const fullName = document.getElementById("fullName").value;
        const telefone = document.getElementById("telefone").value;
        const email = document.getElementById("email").value;
        const password = document.getElementById("password").value;

        if (!nome || !fullName || !telefone || !email || !password) {
          showToast("warning", "Preencha todos os campos.");
          return;
        }

        try {
          const response = await fetch("<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify({
              user_id,
              fullName,
              telefone,
              email,
              password
            }),
          });

          const result = await response.json();

          if (response.ok) {
            if (result.status === "success") {
              showToast("success", result.message);
              setTimeout(() => {
                window.location.href = "/register/2fa.php";
              }, 2000);
            } else {
              showToast("warning", result.message || "Algo deu errado.");
            }
          } else {
            showToast("error", "Erro ao processar o login.");
          }
        } catch (error) {
          showToast("error", "Erro na comunicação com o servidor.");
        }
      });
    });


    function showToast(type, message) {

      var toastMixin = Swal.mixin({
        toast: true,
        icon: type,
        title: message,
        animation: false,
        position: 'top-right',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.addEventListener('mouseenter', Swal.stopTimer)
          toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
      });
      toastMixin.fire({
        title: message,
        icon: type
      });
    }
  </script>
</body>

</html>