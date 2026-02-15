<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $host;
$rootDir = $baseUrl . '/login/';
$logo = $rootDir . "assets/img/logo2.png";
$bg = $rootDir . "assets/img/background.jpg";
?>

<?php
session_start();
if (isset($_SESSION['email'])) {
  header("Location: /home");
  exit();
}

// Função para validar os dados do formulário
function validateForm($input)
{
  $input = trim($input);
  $input = stripslashes($input);
  $input = htmlspecialchars($input);
  return $input;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $data = json_decode(file_get_contents("php://input"), true);

  $email = validateForm($data["email"] ?? "");
  $senha = validateForm($data["password"] ?? "");

  if (empty($email) || empty($senha)) {
    echo json_encode(["status" => "error", "message" => "Preencha todos os campos."]);
    exit;
  }

  include '../conectarbanco.php';

  $conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

  if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Erro na conexão com o banco de dados."]);
    exit;
  }

  $sql = "SELECT senha, status FROM users WHERE email = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->bind_result($hash, $status);
  $stmt->fetch();

  if ($status === 'pendente') {
    echo json_encode(["status" => "warning", "message" => "Conta em análise, aguarde."]);
  } elseif ($status === 'rejeitado') {
    echo json_encode(["status" => "warning", "message" => "Conta rejeitada, entre em contato com o suporte."]);
  } elseif ($hash && password_verify($senha, $hash)) {
    $_SESSION["email"] = $email;
    echo json_encode(["status" => "success", "message" => "Login efetuado com sucesso!"]);
  } else {
    echo json_encode(["status" => "warning", "message" => "Credenciais incorretas."]);
  }

  $stmt->close();
  $conn->close();
  exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>Login</title>
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
          <div class="card-title text-uppercase text-center py-3">Acessar</div>
          <form id="loginForm">
            <div class="form-group">
              <label for="email" class="sr-only">Email</label>
              <div class="position-relative has-icon-right">
                <input autofocus type="email" id="email" name="email" class="form-control input-shadow" placeholder="Entre com seu email">
                <div class="form-control-position">
                  <i class="fa-solid fa-envelope"></i>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="password" class="sr-only">Senha</label>
              <div class="position-relative has-icon-right">
                <input type="password" id="password" name="password" class="form-control input-shadow" placeholder="Entre com sua senha">
                <div class="form-control-position">
                  <i class="fa-solid fa-lock"></i>
                </div>
              </div>
            </div>

            <button type="submit" class="btn btn-success btn-block">Acessar</button>
          </form>
        </div>
      </div>
      <div class="card-footer text-center py-3">
        <p class="text-warning mb-0">Ainda não tem uma conta? <a href="/register"> Cadastrar-me</a></p>
      </div>
    </div>
  </div>

  <!-- Bootstrap core JavaScript-->
  <script src="<?= $rootDir ?>assets/js/jquery.min.js"></script>
  <script src="<?= $rootDir ?>assets/js/popper.min.js"></script>
  <script src="<?= $rootDir ?>assets/js/bootstrap.min.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const form = document.getElementById("loginForm");

      form.addEventListener("submit", async function(event) {
        event.preventDefault();

        const email = document.getElementById("email").value;
        const password = document.getElementById("password").value;

        if (!email || !password) {
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
              email,
              password
            }),
          });

          const result = await response.json();

          if (response.ok) {
            if (result.status === "success") {
              showToast("success", result.message);
              setTimeout(() => {
                window.location.href = "../home";
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