<?php
session_start();
if (isset($_SESSION['email'])) {
  header("Location: ../home");
  exit();
}


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
    header("refresh:3;url=../home");
  } else {
    // Credenciais incorretas, exiba uma mensagem de erro
    $errorMessage = "Credenciais incorretas. Tente novamente.";
  }

  // Fechar a conexão
  $stmt->close();
  $conn->close();
}
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


        <form method="post" accept-charset="utf-8" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

          <div class="card custom-card my-4">
            <div class="card-body p-5">
              <p class="h4 mb-2 fw-semibold">Entrar</p>
              <p class="mb-4 text-muted fw-normal">Bem-vindo</p>
              <div class="row gy-3">
                <div class="col-xl-12">
                  <label for="signin-username" class="form-label text-default">E-mail</label>
                  <input type="email" id="email" name=email class="form-control form-control-lg" placeholder="Email" aria-label="Email">

                </div>
                <div class="col-xl-12 mb-2">
                  <label for="signin-password" class="form-label text-default d-block">Password

                </label>
                <div class="position-relative">
                    <input type="password" name="password" id="senha" class="form-control" placeholder="Password" aria-label="Password" required minlength="6">
                    <a href="javascript:void(0);" class="show-password-button text-muted" onclick="togglePassword('senha', this)" id="button-addon2">
                        <i class="ri-eye-off-line align-middle"></i>
                    </a>
                  </div>
                  <div class="mt-2">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                      <label class="form-check-label text-muted fw-normal fs-12" for="defaultCheck1">
                        Continuar conectado?
                      </label>
                    </div>



                    <?php
                    if (!empty($errorMessage)) {
                      echo '<span class="login-error" style="color:red">' . $errorMessage . '</span>';
                    }
                    if (!empty($successMessage)) {
                      echo '<p class="login-success">' . $successMessage . '</p>';
                    }
                    ?>




                  </div>
                </div>
              </div>




              <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary">Entrar</button>
              </div>


        </form>


        <div class="text-center">
          <p class="text-muted mt-3 mb-0">Não tem uma conta? <a href="../register" class="text-primary">Inscrever-se</a></p>
        </div>
      </div>
    </div>
  </div>
  </div>
  </div>
<script>
function togglePassword(inputId, element) {
    var input = document.getElementById(inputId);
    var icon = element.querySelector("i");

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("ri-eye-off-line");
        icon.classList.add("ri-eye-line"); // Ícone de olho aberto
    } else {
        input.type = "password";
        icon.classList.remove("ri-eye-line");
        icon.classList.add("ri-eye-off-line"); // Ícone de olho fechado
    }
}
</script>
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