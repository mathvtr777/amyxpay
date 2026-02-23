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
    }
    elseif ($status === 'rejeitado') {
        $errorMessage = "Conta rejeitada, entre em contato com o suporte.";
    }
    elseif ($hash && password_verify($senha, $hash)) {
        // Credenciais corretas, armazene o email na sessão para uso posterior
        $_SESSION["email"] = $email;
        $successMessage = "Login efetuado com sucesso!";
        header("refresh:3;url=../home");
    }
    else {
        // Credenciais incorretas, exiba uma mensagem de erro
        $errorMessage = "Credenciais incorretas. Tente novamente.";
    }

    // Fechar a conexão
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html class="dark" lang="pt-BR"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Login - Zyro Pay</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
<script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#8c2bee", // Purple from Zyro
                        "background-light": "#F9FAFB",
                        "background-dark": "#0F1115",
                        "surface-dark": "#1C1F26",
                    },
                    fontFamily: {
                        display: ["Inter", "sans-serif"],
                    },
                    borderRadius: {
                        DEFAULT: "0.75rem",
                    },
                },
            },
        };
    </script>
<!-- Three.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .login-error {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: block;
        }
        .login-success {
            color: #10b981;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: block;
        }
        /* 3D Logo container */
        #logo3d-container {
            width: 320px;
            height: 220px;
            position: relative;
            margin: 0 auto 1.5rem;
            cursor: default;
        }
        #logo3d-container canvas {
            width: 100% !important;
            height: 100% !important;
            display: block;
        }
        .logo3d-scanline {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, transparent 50%, rgba(124,58,237,0.03) 51%, transparent 51%);
            background-size: 100% 4px;
            pointer-events: none;
            z-index: 2;
            border-radius: 12px;
        }
    </style>

</head>
<body class="bg-background-light dark:bg-background-dark min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-md">
<div id="logo3d-wrap" style="display:flex;justify-content:center;margin-bottom:1.5rem">
    <div id="logo3d-container">
        <div class="logo3d-scanline"></div>
    </div>
</div>
<div class="bg-white dark:bg-surface-dark p-8 md:p-10 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800">
<h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">Iniciar sessão</h1>

<?php
if (!empty($errorMessage)) {
    echo '<div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/20"><span class="login-error">' . $errorMessage . '</span></div>';
}
if (!empty($successMessage)) {
    echo '<div class="mb-4 p-3 rounded-lg bg-green-500/10 border border-green-500/20"><span class="login-success">' . $successMessage . '</span></div>';
}
?>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-6">
<div class="space-y-2">
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="email">
                        E-mail
                    </label>
<div class="relative">
<input required class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-600" id="email" name="email" placeholder="seu@email.com" type="email" value="<?php echo htmlspecialchars($email); ?>"/>
</div>
</div>
<div class="space-y-2">
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="password">
                        Senha
                    </label>
<div class="relative">
<input required class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-600" id="password" name="password" placeholder="••••••••" type="password"/>
<button class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" type="button" onclick="togglePassword()">
<span class="material-symbols-outlined text-xl" id="eye-icon">visibility_off</span>
</button>
</div>
</div>
<div class="flex justify-end">
<a class="text-sm font-medium text-primary hover:opacity-80 transition-opacity" href="#">
                        Esqueci a senha
                    </a>
</div>
<button class="w-full py-4 bg-primary hover:bg-opacity-90 text-white font-bold rounded-xl shadow-lg shadow-primary/20 transition-all active:scale-[0.98] flex justify-center items-center" type="submit">
                    Entrar
                </button>
</form>
<div class="mt-8 text-center">
<p class="text-sm text-gray-500 dark:text-gray-400">
                    Ainda não tem conta? 
                    <a class="text-primary font-semibold hover:underline" href="../register">Crie agora</a>
</p>
</div>
</div>
<div class="mt-8 flex justify-center items-center gap-6 opacity-40 grayscale hover:grayscale-0 transition-all duration-500">
<div class="flex items-center gap-1">
<span class="material-symbols-outlined text-gray-600 dark:text-gray-400 text-sm">lock</span>
<span class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-widest">Ambiente Seguro</span>
</div>
<div class="flex items-center gap-1">
<span class="material-symbols-outlined text-gray-600 dark:text-gray-400 text-sm">verified_user</span>
<span class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-widest">PCI Compliance</span>
</div>
</div>
</div>
<div class="fixed bottom-4 right-4">
<button class="p-3 bg-white dark:bg-surface-dark rounded-full shadow-lg border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-white" onclick="document.documentElement.classList.toggle('dark')">
<span class="material-symbols-outlined block dark:hidden">dark_mode</span>
<span class="material-symbols-outlined hidden dark:block">light_mode</span>
</button>
</div>

<script>
function togglePassword() {
    var input = document.getElementById("password");
    var icon = document.getElementById("eye-icon");
    if (input.type === "password") {
        input.type = "text";
        icon.textContent = "visibility";
    } else {
        input.type = "password";
        icon.textContent = "visibility_off";
    }
}

// ── Three.js 3D Logo ──────────────────────────────────
(function() {
    if (typeof THREE === 'undefined') return;
    var container = document.getElementById('logo3d-container');
    if (!container) return;

    var W = 320, H = 220;
    var scene = new THREE.Scene();
    var camera = new THREE.PerspectiveCamera(50, W / H, 0.1, 100);
    camera.position.z = 4;

    var renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(W, H);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    container.appendChild(renderer.domElement);

    var clock = new THREE.Clock();
    var logoMesh = null;
    var mouseX = 0, mouseY = 0;

    var ambientLight = new THREE.AmbientLight(0xffffff, 0.9);
    scene.add(ambientLight);
    var pointLight = new THREE.PointLight(0xa855f7, 2, 20);
    pointLight.position.set(3, 3, 3);
    scene.add(pointLight);

    // Glow texture
    function makeGlow() {
        var c = document.createElement('canvas');
        c.width = c.height = 256;
        var ctx = c.getContext('2d');
        var g = ctx.createRadialGradient(128,128,0,128,128,128);
        g.addColorStop(0,   'rgba(168,85,247,1)');
        g.addColorStop(0.5, 'rgba(124,58,237,0.3)');
        g.addColorStop(1,   'rgba(0,0,0,0)');
        ctx.fillStyle = g;
        ctx.fillRect(0,0,256,256);
        return new THREE.CanvasTexture(c);
    }

    // Load logo image
    var loader = new THREE.TextureLoader();
    loader.load('https://i.imgur.com/C5tqLgx.png', function(tex) {
        var geo = new THREE.PlaneGeometry(3, 2);
        var mat = new THREE.MeshBasicMaterial({ map: tex, transparent: true, side: THREE.DoubleSide });
        logoMesh = new THREE.Mesh(geo, mat);
        scene.add(logoMesh);

        var glowGeo = new THREE.PlaneGeometry(5, 5);
        var glowMat = new THREE.MeshBasicMaterial({
            map: makeGlow(),
            transparent: true,
            blending: THREE.AdditiveBlending,
            opacity: 0.28,
            depthWrite: false
        });
        var glow = new THREE.Mesh(glowGeo, glowMat);
        glow.position.z = -0.01;
        logoMesh.add(glow);
    }, undefined, function() {
        // Fallback: draw ZYRO PAY text on canvas
        var c = document.createElement('canvas');
        c.width = 512; c.height = 256;
        var ctx = c.getContext('2d');
        ctx.fillStyle = 'transparent';
        ctx.clearRect(0,0,512,256);
        ctx.font = 'bold 72px Inter,Arial,sans-serif';
        ctx.textAlign = 'center';
        ctx.fillStyle = '#ffffff';
        ctx.fillText('ZYRO', 150, 160);
        ctx.fillStyle = '#a855f7';
        ctx.fillText('PAY', 380, 160);
        var tex = new THREE.CanvasTexture(c);
        var geo = new THREE.PlaneGeometry(3, 1.5);
        var mat = new THREE.MeshBasicMaterial({ map: tex, transparent: true });
        logoMesh = new THREE.Mesh(geo, mat);
        scene.add(logoMesh);
    });

    document.addEventListener('mousemove', function(e) {
        mouseX = (e.clientX / window.innerWidth - 0.5) * 2;
        mouseY = (e.clientY / window.innerHeight - 0.5) * 2;
    });

    function animate() {
        requestAnimationFrame(animate);
        var t = clock.getElapsedTime();
        if (logoMesh) {
            logoMesh.rotation.y += 0.012;
            logoMesh.rotation.x += (mouseY * 0.3 - logoMesh.rotation.x) * 0.05;
            logoMesh.position.y = Math.sin(t) * 0.12;
        }
        renderer.render(scene, camera);
    }
    animate();
})();
</script>

</body></html>