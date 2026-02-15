<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $host;
$rootDir = $baseUrl . '/register/';
$logo = $rootDir . "assets/img/logo2.png";
$bg = $rootDir . "assets/img/background.jpg";
?>


<?php
session_start();
if (isset($_SESSION['email']) && !isset($_SESSION['user_id'])) {
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
require_once '../security/GoogleAuthenticator.php'; // Inclua manualmente a biblioteca.

$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

$ga = new PHPGangsta_GoogleAuthenticator();
$secret = $_SESSION['2fa_secret']; // Gere a chave secreta para o 2FA.

$user_id = $_SESSION['user_id'] ?? null;
$email = $_SESSION['email'] ?? null;

$qrCodeUrl = $ga->getQRCodeGoogleUrl('URANOPAY', $secret, $email); // Gere a URL do QR Code.

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verify_2fa'])) {
  $inputCode = $_POST['2fa_code'] ?? null; // Código inserido pelo usuário.
  //$secret = $_SESSION['2fa_secret'] ?? null;


  if ($secret === null || $inputCode === null) {
    responseJson("error", "Dados inválidos. Tente novamente.");
  }

  $checkResult = $ga->verifyCode($secret, $inputCode, 2); // Valida o código com tolerância de 2 minutos.

  if ($checkResult) {
    responseJson("success", "2FA configurado com sucesso!");
    // Salve a chave secreta no banco de dados para o usuário.
    save2FASecret($secret, $user_id, $conn); // Substitua `1` pelo ID real do usuário.
    // Remover variáveis de sessão
    unset($_SESSION['user_id']);
    unset($_SESSION['2fa_secret']);

    // Destruir a sessão
    session_destroy();

    // Limpar o cookie da sessão
    if (isset($_COOKIE[session_name()])) {
      setcookie(session_name(), '', time() - 3600, '/'); // Expira o cookie da sessão
    }
    // Iniciar uma nova sessão, se necessário
    session_start();
    $_SESSION['email'] = $email;
    exit;
  } else {
    responseJson("error", "Código inválido. Tente novamente.");
  }
}

/**
 * Função para salvar o segredo 2FA no banco de dados.
 */
function save2FASecret(string $secret, int $user_id, mysqli $conn): void
{
  ini_set('display_errors', 1);
  error_reporting(E_ALL);


  $query = "UPDATE users SET 2fa_active = ? WHERE user_id = ?";
  $stmt = $conn->prepare($query);

  if (!$stmt) {
    throw new RuntimeException("Erro ao preparar a consulta: {$conn->error}");
  }

  $active = 1;
  $stmt->bind_param("ii", $active, $user_id);
  $stmt->execute();
  $stmt->close();
}

/**
 * Função para retornar uma resposta JSON.
 */
function responseJson(string $status, string $message): void
{
  echo json_encode(["status" => $status, "message" => $message], JSON_THROW_ON_ERROR);
  exit;
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>2FA</title>
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
          <div class="card-title text-uppercase text-center py-3">Autenticação de dois fatores</div>
          <p>Escaneie o QR Code no Google Authenticator:</p>
          <div class="card-title text-uppercase text-center py-3">
            <img src="<?= $qrCodeUrl ?>" alt="QR Code">
          </div>
          <p class="text-center">Ou insira este código manualmente no app: <strong class="text-center"><?= $secret ?></strong></p>
          <hr>
          <p class="text-center">Depois, insira o código gerado no campo abaixo:</p>
          <input type="text" id="2fa_code" class="form-control" placeholder="Digite o código do Google Authenticator">
          <button type="button" class="btn btn-success mt-3 w-full text-center" id="verify2FA" style="width: 100%;">Verificar</button>
        </div>
      </div>

    </div>
  </div>



  <script>
    document.getElementById("verify2FA").addEventListener("click", async function() {
      const code = document.getElementById("2fa_code").value;

      if (!code) {
        showToast("warning", "Por favor, insira o código.");
        return;
      }

      const response = await fetch("<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `verify_2fa=true&2fa_code=${code}`
      });

      const result = await response.json();

      if (result.status === "success") {
        showToast("success", result.message);
        sessionStorage.removeItem('2fa_code');
        sessionStorage.removeItem('user_id');
        setTimeout(() => {
          window.location.href = "/home";
        }, 2000)
      } else {
        showToast("warning", result.message);
      }
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

  <!-- Bootstrap core JavaScript-->
  <script src="<?= $rootDir ?>assets/js/jquery.min.js"></script>
  <script src="<?= $rootDir ?>assets/js/popper.min.js"></script>
  <script src="<?= $rootDir ?>assets/js/bootstrap.min.js"></script>

</body>

</html>