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

$aviso = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $user_id = validateForm($_POST["user_id"]);
  $nome = validateForm($_POST["fullName"]);
  $email = validateForm($_POST["email"]);
  $senha = validateForm($_POST["password"]);
  $telefone = validateForm($_POST["telefone"]);

  if (emailExists($email, $conn)) {
    $aviso = "O e-mail já está sendo usado.";
  }
  elseif (userIdExists($user_id, $conn)) {
    $aviso = "Usuário não está disponível!";
  }
  else {

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

    $insertQuery = "INSERT INTO users (id, user_id, nome, email, senha, telefone, saldo, data_cadastro, status, permission, cliente_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    if (!$stmt) {
      die("Erro na preparação da consulta: " . $conn->error);
    }

    $stmt->bind_param("issssisssssii", $nextId, $user_id, $nome, $email, $senhaHash, $telefone, $dataCadastroFormatada, $status, $permission, $clienteId);

    if ($stmt->execute()) {
      $_SESSION['email'] = $email;

      $webhookUrl = getWebhookUrl($conn);
      if ($webhookUrl !== false) {
        sendToWebhook($webhookUrl, $nome, $email, $telefone);
      }

      $aviso = "Cadastro concluído com sucesso!";
      header("refresh:3;url=../");
    }
    else {
      $aviso = "Erro " . $stmt->error;
    }
    $stmt->close();
  }
}

function emailExists($email, $conn)
{
  $checkEmailQuery = "SELECT email FROM users WHERE email = ?";
  $checkEmailStmt = $conn->prepare($checkEmailQuery);
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
  }
  else {
    return false;
  }
}

function getTaxaPadroes($conn)
{
  $query = "SELECT taxa_cash_in_padrao_padrao FROM app LIMIT 1";
  $result = $conn->query($query);
  if ($result && $result->num_rows > 0) {
    return $result->fetch_assoc();
  }
  else {
    return false;
  }
}

function sendToWebhook($url, $name, $email, $phone)
{
  $data = array('event' => 'novo', 'name' => $name, 'email' => $email, 'phone' => $phone);
  $options = array(
    'http' => array(
      'header' => "Content-Type: application/json\r\n",
      'method' => 'POST',
      'content' => json_encode($data),
    ),
  );
  $context = stream_context_create($options);
  file_get_contents($url, false, $context);
}

$conn->close();
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Cadastro - Zyro Pay</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet"/>
<script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#7c3aed",
                        "background-light": "#f3f4f6",
                        "background-dark": "#0a0a0c",
                        "card-dark": "#18181b",
                    },
                    fontFamily: {
                        display: ["Inter", "sans-serif"],
                    },
                    borderRadius: {
                        DEFAULT: "12px",
                    },
                },
            },
        };
    </script>
<style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .bg-landscape {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), 
                        url('https://images.unsplash.com/photo-1475274047050-1d0c0975c63e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
        }
        .aviso-success { color: #10b981; }
        .aviso-error { color: #ef4444; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark min-h-screen flex flex-col justify-between overflow-x-hidden">
<div class="fixed inset-0 z-0 bg-landscape pointer-events-none"></div>
<main class="relative z-10 flex flex-grow items-center justify-center p-4">
<div class="w-full max-w-[480px]">
<div class="bg-white dark:bg-card-dark shadow-2xl rounded-3xl overflow-hidden transition-all duration-300">
<div class="bg-gradient-to-br from-gray-900 to-black p-10 flex justify-center items-center">
<div class="flex items-center space-x-2">
<span class="text-white text-4xl font-extrabold tracking-tighter uppercase">ZYRO</span>
<div class="bg-primary px-3 py-1 rounded-lg">
<span class="text-white text-2xl font-bold italic uppercase">PAY</span>
</div>
</div>
</div>
<div class="px-8 py-10">
<?php if (!empty($aviso)): ?>
    <div class="mb-6 p-4 rounded-xl <?php echo(strpos($aviso, 'sucesso') !== false) ? 'bg-green-500/10 border border-green-500/20 aviso-success' : 'bg-red-500/10 border border-red-500/20 aviso-error'; ?> text-sm font-medium">
        <?php echo $aviso; ?>
    </div>
<?php
endif; ?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-4" method="POST">
<div class="relative">
<span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
<span class="material-icons-outlined text-sm">person_outline</span>
</span>
<input required class="w-full pl-10 pr-4 py-3 border border-gray-200 dark:border-zinc-800 rounded-xl bg-transparent text-gray-800 dark:text-zinc-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all placeholder:text-gray-400" name="user_id" placeholder="Usuário" type="text" value="<?php echo htmlspecialchars($_POST['user_id'] ?? ''); ?>"/>
</div>
<div class="relative">
<span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
<span class="material-icons-outlined text-sm">badge</span>
</span>
<input required class="w-full pl-10 pr-4 py-3 border border-gray-200 dark:border-zinc-800 rounded-xl bg-transparent text-gray-800 dark:text-zinc-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all placeholder:text-gray-400" name="fullName" placeholder="Nome Completo" type="text" value="<?php echo htmlspecialchars($_POST['fullName'] ?? ''); ?>"/>
</div>
<div class="relative">
<span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
<span class="material-icons-outlined text-sm">phone</span>
</span>
<input required class="w-full pl-10 pr-4 py-3 border border-gray-200 dark:border-zinc-800 rounded-xl bg-transparent text-gray-800 dark:text-zinc-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all placeholder:text-gray-400" name="telefone" placeholder="Telefone" type="tel" value="<?php echo htmlspecialchars($_POST['telefone'] ?? ''); ?>"/>
</div>
<div class="relative">
<span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
<span class="material-icons-outlined text-sm">mail_outline</span>
</span>
<input required class="w-full pl-10 pr-4 py-3 border border-gray-200 dark:border-zinc-800 rounded-xl bg-transparent text-gray-800 dark:text-zinc-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all placeholder:text-gray-400" name="email" placeholder="Email" type="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"/>
</div>
<div class="relative">
<span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
<span class="material-icons-outlined text-sm">lock_outline</span>
</span>
<input required class="w-full pl-10 pr-4 py-3 border border-gray-200 dark:border-zinc-800 rounded-xl bg-transparent text-gray-800 dark:text-zinc-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all placeholder:text-gray-400" name="password" placeholder="Mínimo 8 caracteres" type="password" minlength="8"/>
</div>
<button class="w-full mt-6 py-4 bg-gradient-to-r from-primary to-purple-700 hover:from-purple-600 hover:to-primary text-white font-bold rounded-xl transition-all duration-300 transform active:scale-95 shadow-lg shadow-primary/25" type="submit">
                            REGISTRAR
                        </button>
</form>
<div class="mt-8 text-center">
<p class="text-sm text-gray-500 dark:text-zinc-400">
                            Já tem uma conta? 
                            <a class="text-primary font-semibold hover:underline" href="../login">Login</a>
</p>
</div>
</div>
</div>
</div>
</main>
<footer class="relative z-10 px-8 py-6 flex flex-col md:flex-row justify-between items-center text-white/50 text-sm">
<div class="mb-4 md:mb-0">
            © 2026, made with by <span class="font-bold text-white uppercase">ZYRO PAY</span>
</div>
<div class="flex space-x-6">
<a class="hover:text-white transition-colors" href="#">Termos</a>
<a class="hover:text-white transition-colors" href="#">Privacidade</a>
<a class="hover:text-white transition-colors" href="#">Suporte</a>
</div>
</footer>
<button class="fixed bottom-6 right-6 z-50 p-3 bg-white dark:bg-zinc-800 rounded-full shadow-xl transition-all active:scale-90" onclick="document.documentElement.classList.toggle('dark')">
<span class="material-icons-outlined text-gray-800 dark:text-yellow-400 block dark:hidden">dark_mode</span>
<span class="material-icons-outlined text-gray-800 dark:text-yellow-400 hidden dark:block">light_mode</span>
</button>

</body></html>