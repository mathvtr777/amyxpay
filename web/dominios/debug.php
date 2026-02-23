<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Diagnóstico Domínios</h2>";

// Test 1: conectarbanco
echo "1. Carregando conectarbanco.php... ";
$f = __DIR__ . '/../conectarbanco.php';
if (file_exists($f)) {
    include_once $f;
    echo "<span style='color:green'>OK</span><br>";
    echo "Host: " . htmlspecialchars($config['db_host'] ?? 'N/A') . "<br>";
}
else {
    echo "<span style='color:red'>ARQUIVO NÃO ENCONTRADO: $f</span><br>";
}

// Test 2: DB connect
echo "2. Conectando MySQL... ";
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error) {
    echo "<span style='color:red'>ERRO: " . htmlspecialchars($conn->connect_error) . "</span><br>";
}
else {
    echo "<span style='color:green'>OK</span><br>";
}

// Test 3: DomainService
echo "3. Carregando DomainService.php... ";
$ds = __DIR__ . '/../app/Services/DomainService.php';
if (file_exists($ds)) {
    require_once $ds;
    echo "<span style='color:green'>OK</span><br>";
}
else {
    echo "<span style='color:red'>ARQUIVO NÃO ENCONTRADO: $ds</span><br>";
}

// Test 4: Session
echo "4. Session... ";
session_start();
echo(isset($_SESSION['email']) ? "<span style='color:green'>Logado como: " . htmlspecialchars($_SESSION['email']) . "</span><br>" : "<span style='color:orange'>NÃO LOGADO (sessão vazia)</span><br>");

// Test 5: base_new.php
echo "5. Verificando base_new.php... ";
$bf = __DIR__ . '/../layouts/base_new.php';
echo file_exists($bf) ? "<span style='color:green'>OK</span><br>" : "<span style='color:red'>NÃO ENCONTRADO</span><br>";

// Test 6: sidebar_new.php
echo "6. Verificando sidebar_new.php... ";
$sf = __DIR__ . '/../layouts/components/sidebar_new.php';
echo file_exists($sf) ? "<span style='color:green'>OK</span><br>" : "<span style='color:red'>NÃO ENCONTRADO</span><br>";

// Test 7: users table
echo "7. Verificando coluna users... ";
$res = $conn->query("DESCRIBE users");
$cols = [];
while ($row = $res->fetch_assoc()) {
    $cols[] = $row['Field'];
}
echo "Colunas: " . implode(', ', $cols) . "<br>";

echo "<br><b>PHP Version:</b> " . PHP_VERSION . "<br>";
echo "<b>__DIR__:</b> " . __DIR__;
?>
