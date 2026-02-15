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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <style>
    body {
      background-color: #19123B;
    }

    .card {
      border: none;
      border-top: 5px solid rgb(176, 106, 252);
      background: #212042;
      color: #57557A;
    }

    p {
      font-weight: 600;
      font-size: 15px;
    }

    .fab {
      display: flex;
      justify-content: center;
      align-items: center;
      border: none;
      background: #2A284D;
      height: 40px;
      width: 90px;
    }

    .fab:hover {
      cursor: pointer;
    }

    .fa-twitter {
      color: #56ABEC;
    }

    .fa-facebook {
      color: #1775F1;
    }

    .fa-google {
      color: #CB5048;
    }

    .division {
      float: none;
      position: relative;
      margin: 30px auto 20px;
      text-align: center;
      width: 100%;
      box-sizing: border-box;
    }

    .division .line {
      border-top: 1.5px solid #57557A;
      ;
      position: absolute;
      top: 13px;
      width: 85%;
    }

    .line.l {
      left: 52px;
    }

    .line.r {
      right: 45px;

    }

    .division span {
      font-weight: 600;
      font-size: 14px;
    }

    .myform {
      padding: 0 25px 0 33px;
    }

    .form-control {
      border: 1px solid #57557A;
      border-radius: 3px;
      background: #212042;
      margin-bottom: 20px;
      letter-spacing: 1px;

    }

    .form-control:focus {
      border: 1px solid #57557A;
      border-radius: 3px;
      box-shadow: none;
      background: #212042;
      color: #fff;
      letter-spacing: 1px;
    }

    .bn {
      text-decoration: underline;
    }

    .bn:hover {
      cursor: pointer;
    }

    .form-check-input {
      margin-top: 8px !important;
    }

    .btn-primary {
      background: linear-gradient(135deg, rgba(176, 106, 252, 1) 39%, rgba(116, 17, 255, 1) 101%);
      border: none;
      border-radius: 50px;
    }

    .btn-primary:focus {
      box-shadow: none;
      border: none;
    }

    small {
      color: #F2CEFF;
    }

    .far.fa-user {
      font-size: 13px;
    }

    @media(min-width: 767px) {
      .bn {
        text-align: right;
      }
    }

    @media(max-width: 767px) {
      .form-check {
        text-align: center;
      }

      .bn {
        text-align: center;
        align-items: center;
      }
    }

    @media(max-width: 450px) {
      .fab {
        width: 100%;
        height: 100%;
      }

      .division .line {
        width: 50%;
      }
    }
  </style>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
</head>

<body>
  <div class="container">
    <div class="row d-flex justify-content-center mt-5">
      <div class="col-12 col-md-8 col-lg-6 col-xl-5">
        <div class="card py-3 px-2">
          <p class="text-center mb-3 mt-2">SE CONNECTER AVEC</p>
          <div class="row mx-auto ">
            <div class="col-4">
              <i class="fab fa-twitter"></i>
            </div>
            <div class="col-4">
              <i class="fab fa-facebook"></i>
            </div>
            <div class="col-4">
              <i class="fab fa-google"></i>
            </div>
          </div>
          <div class="division">
            <div class="row">
              <div class="col-3">
                <div class="line l"></div>
              </div>
              <div class="col-6"><span>OU AVEC MON EMAIL</span></div>
              <div class="col-3">
                <div class="line r"></div>
              </div>
            </div>
          </div>
          <form class="myform">
            <div class="form-group">
              <input type="email" class="form-control" placeholder="Email">
            </div>
            <div class="form-group">
              <input type="password" class="form-control" placeholder="Mot de passe">
            </div>
            <div class="row">
              <div class="col-md-6 col-12">
                <div class="form-group form-check">
                  <input type="checkbox" class="form-check-input" id="exampleCheck1">
                  <label class="form-check-label" for="exampleCheck1">Rester connecte</label>
                </div>
              </div>
              <div class="col-md-6 col-12 bn">Mot se passe oublie</div>
            </div>
            <div class="form-group mt-3">
              <button type="button" class="btn btn-block btn-primary btn-lg"><small><i class="far fa-user pr-2"></i>Se connecter</small></button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>

</html>