<?php
session_start();


function validateForm($input)
{
  $input = trim($input);
  $input = stripslashes($input);
  return $input;
}

include '../conectarbanco.php';

$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);


if ($conn->connect_error) {
  die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $user_id = validateForm($_POST["user_id"]);
  $nome = validateForm($_POST["fullName"]);
  $email = validateForm($_POST["email"]);
  $senha = validateForm($_POST["password"]);
  $telefone = validateForm($_POST["telefone"]);


  if (emailExists($email, $conn)) {
    $aviso = "O e-mail já está sendo usado.";
  } elseif (userIdExists($user_id, $conn)) {
    $aviso = "Usuário não está disponível!";
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


    $insertQuery = "INSERT INTO users (id, user_id, nome, email, senha, telefone, saldo, data_cadastro, status, permission, cliente_id, taxa_cash_in, taxa_cash_out) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    if (!$stmt) {
      die("Erro na preparação da consulta: " . $conn->error);
    }

    $stmt->bind_param("issssisssssii", $nextId, $user_id, $nome, $email, $senhaHash, $telefone, $saldo, $dataCadastroFormatada, $status, $permission, $clienteId, $taxa_cash_in, $taxa_cash_out);

    if ($stmt->execute()) {

      $_SESSION['email'] = $email;


      $webhookUrl = getWebhookUrl($conn);


      if ($webhookUrl !== false) {
        sendToWebhook($webhookUrl, $nome, $email, $telefone);
      }


      $aviso = "Cadastro concluído com sucesso";


      header("refresh:3;url=https://web.uranopay.com/");
    } else {
      $aviso = "Erro " . $stmt->error;
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
  $result = file_get_contents($url, false, $context);

  if ($result === FALSE) {
    die('Erro ao enviar dados para o webhook');
  }
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../img/logo-favicon.png">
  <link rel="icon" type="image/png" href="../img/logo-favicon.png">
  <title>URANO PAY - REGISTRAR
  </title>
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
                <form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="POST" onsubmit="return validateForm();">

                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">Usuário</label>
                    <input type="text" id="nome" name="user_id" class="form-control" required>
                  </div>
                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">Nome</label>
                    <input type="text" id="fullName" name="fullName" class="form-control" required>
                  </div>
                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">Telefone</label>
                    <input type="number" id="telefone" name="telefone" class="form-control" required>
                  </div>
                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                  </div>
                  <div class="input-group input-group-outline mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" id="senha" class="form-control" required minlength="8">
                  </div>

                  <?php if (!empty($aviso)): ?>
                    <div class="aviso-visible" id="aviso">
                      <button class="close-btn" onclick="document.getElementById('aviso').style.display='none';">&times;</button>
                      <?php echo $aviso; ?>
                    </div>
                  <?php endif; ?>

                  <div class="text-center">
                    <button type="submit" class="btn bg-gradient-primary w-100 my-4 mb-2">Registrar</button>
                  </div>
                  <p class="mt-4 text-sm text-center">
                    Já tem uma conta?
                    <a href="../login/" class="text-primary text-gradient font-weight-bold">Login</a>
                  </p>
                </form>

                <script>
                  function validateForm() {
                    var nome = document.getElementById('nome').value;
                    var fullName = document.getElementById('fullName').value;
                    var telefone = document.getElementById('telefone').value;
                    var email = document.getElementById('email').value;
                    var senha = document.getElementById('senha').value;


                    if (!nome || !fullName || !telefone || !email || !senha) {
                      alert("Por favor, preencha todos os campos obrigatórios.");
                      return false;
                    }

                    if (senha.length < 8) {
                      alert("A senha deve ter pelo menos 8 caracteres.");
                      return false;
                    }

                    return true;
                  }
                </script>

                <script>
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
      <footer class="footer position-absolute bottom-2 py-2 w-100">
        <div class="container">
          <div class="row align-items-center justify-content-lg-between">
            <div class="col-12 col-md-6 my-auto">
              <div class="copyright text-center text-sm text-white text-lg-start">
                © <script>
                  document.write(new Date().getFullYear())
                </script>,
                made with by
                <a href="#" class="font-weight-bold text-white">URANO PAY</a>
              </div>
            </div>

          </div>
        </div>
      </footer>
    </div>
  </main>
  <!--   Core JS Files   -->
  <script src="assets-login/js/core/popper.min.js"></script>
  <script src="assets-login/js/core/bootstrap.min.js"></script>
  <script src="assets-login/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="assets-login/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>

</body>

</html>