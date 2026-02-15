<?php
session_start();
if (isset($_SESSION['email'])) {
  header("Location: ../home");
  exit();
}
?>


<?php
session_start();

// Inicializa as variáveis
$email = $senha = "";
$emailErr = $senhaErr = "";
$errorMessage = $successMessage = "";

// Função para validar os dados do formulário
function validateForm($input)
{
  $input = trim($input);
  $input = stripslashes($input);
  $input = htmlspecialchars($input);
  return $input;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Validar e obter os dados do formulário
  $email = validateForm($_POST["email"]);
  $senha = validateForm($_POST["password"]);

  include '../conectarbanco.php';

  $conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

  // Verifica se houve algum erro na conexão
  if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
  }

  // Consulta SQL para verificar as credenciais e status
  $sql = "SELECT senha, status FROM users WHERE email = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->bind_result($hash, $status);
  $stmt->fetch();

  if ($status === 'pendente') {
    $errorMessage = "Conta em análise, aguarde.";
  } elseif ($status === 'rejeitado') {
    $errorMessage = "Conta rejeitada, entre em contato com o suporte.";
  } elseif ($hash && password_verify($senha, $hash)) {
    // Credenciais corretas, armazene o email na sessão para uso posterior
    $_SESSION["email"] = $email;
    $successMessage = "Login efetuado com sucesso!";
    header("refresh:2;url=../home");
  } else {
    // Credenciais incorretas, exiba uma mensagem de erro
    $errorMessage = "Credenciais incorretas.";
  }

  // Fechar a conexão
  $stmt->close();
  $conn->close();
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../img/logo-favicon.png">
  <link rel="icon" type="image/png" href="../img/logo-favicon.png">
  <title>URANO PAY - LOGIN</title>
  <!--     Fonts and icons     -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
  <!-- Nucleo Icons -->
  <link href="assets-login/css/nucleo-icons.css" rel="stylesheet" />
  <link href="assets-login/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
  <!-- CSS Files -->
  <link id="pagestyle" href="assets-login/css/material-dashboard.css?v=3.0.0" rel="stylesheet" />
  <!-- Bootstrap CSS for modal -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-gray-200">
  <div class="container position-sticky z-index-sticky top-0">
    <div class="row">
      <div class="col-12">

      </div>
    </div>
  </div>
  <main class="main-content  mt-0">
    <div class="page-header align-items-start min-vh-100" style="background-image: url('https://images.unsplash.com/photo-1497294815431-9365093b7331?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1950&q=80');">
      <span class="mask bg-gradient-dark opacity-6"></span>
      <div class="container my-auto">
        <div class="row">
          <div class="col-lg-4 col-md-8 col-12 mx-auto">
            <div class="card z-index-0 fadeIn3 fadeInBottom">
              <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-primary shadow-primary border-radius-lg py-3 pe-1">
                  <img src="../img/URANOPAY-branca.png" alt="Texto alternativo" class="img-fluid d-block mx-auto mt-2 mb-0" style="max-width: 200px;">

                </div>
              </div>
              <div class="card-body">
                <form method="post" accept-charset="utf-8" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" onsubmit="return validateForm()">
                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                  </div>
                  <div class="input-group input-group-outline mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" id="senha" class="form-control" required>
                  </div>
                  <div class="form-check form-switch d-flex align-items-center mb-3">
                    <input class="form-check-input" type="checkbox" id="rememberMe">
                    <label class="form-check-label mb-0 ms-2" for="rememberMe">Lembre-se</label>
                  </div>

                  <?php
                  if (!empty($errorMessage)) {
                    echo '<span class="login-error" style="color:red">' . $errorMessage . '</span>';
                  }
                  if (!empty($successMessage)) {
                    echo '<p class="login-success" style="color:green">' . $successMessage . '</p>';
                  }
                  ?>

                  <div class="text-center">
                    <button type="submit" class="btn bg-gradient-primary w-100 my-4 mb-2">Login</button>
                  </div>
                  <p class="mt-4 text-sm text-center">
                    Não tem uma conta?
                    <a href="../registrar/" class="text-primary text-gradient font-weight-bold">Inscrever-se</a>
                  </p>
                </form>

                <script>
                  function validateForm() {
                    const email = document.getElementById("email").value;
                    const senha = document.getElementById("senha").value;

                    if (email === "" || senha === "") {
                      const modal = new bootstrap.Modal(document.getElementById('errorModal'));
                      modal.show();
                      return false;
                    }
                    return true;
                  }

                  document.addEventListener('DOMContentLoaded', function() {
                    const inputs = document.querySelectorAll('.form-control');

                    inputs.forEach(input => {
                      if (input.value) {
                        input.parentElement.classList.add('is-filled');
                      }

                      input.addEventListener('focus', () => {
                        input.parentElement.classList.add('is-filled');
                      });

                      input.addEventListener('blur', () => {
                        if (!input.value) {
                          input.parentElement.classList.remove('is-filled');
                        }
                      });
                    });
                  });
                </script>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>



    <!-- Modal com fundo preto -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white">
          <div class="modal-header border-0">

            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Por favor, preencha todos os campos antes de continuar.
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
          </div>
        </div>
      </div>
    </div>




    <!-- Script to trigger modal and set message -->
    <script>
      function showModal(message) {
        document.getElementById('modalMessage').innerText = message;
        var modal = new bootstrap.Modal(document.getElementById('errorModal'));
        modal.show();
      }
    </script>


    <footer class="footer position-absolute bottom-2 py-2 w-100">
      <div class="container">
        <div class="row align-items-center justify-content-lg-between">
          <div class="col-12 col-md-6 my-auto">
            <div class="copyright text-center text-sm text-white text-lg-start">
              © <script>
                document.write(new Date().getFullYear())
              </script>
              Desenvolvido por
              <a href="#" class="font-weight-bold text-white">URANOPAY</a>
            </div>
          </div>
        </div>
      </div>
    </footer>
  </main>

  <!--   Core JS Files   -->
  <script src="assets-login/js/core/popper.min.js"></script>
  <script src="assets-login/js/core/bootstrap.min.js"></script>
  <script src="assets-login/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="assets-login/js/plugins/smooth-scrollbar.min.js"></script>

  <!-- Bootstrap JS for modal -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>