<?php
// test_payment.php - chama process_payment.php diretamente e mostra a resposta real
header("Content-Type: text/html; charset=utf-8");

require_once __DIR__ . "/../../conectarbanco.php";
$conn = new mysqli($config["db_host"], $config["db_user"], $config["db_pass"], $config["db_name"]);

// Pega o checkout_id da URL
$cid = intval($_GET["checkout_id"] ?? 0);
if (!$cid) {
    // Lista checkouts disponíveis
    $res = $conn->query("SELECT id, name_produto, user_provider_id, email FROM checkout_build ORDER BY id DESC LIMIT 10");
    echo "<h2>Checkouts disponíveis:</h2><ul>";
    while ($r = $res->fetch_assoc()) {
        echo "<li><a href='?checkout_id={$r["id"]}'>{$r["id"]} - {$r["name_produto"]} (provider_id: {$r["user_provider_id"]}, email: {$r["email"]})</a></li>";
    }
    echo "</ul><p>Clique num checkout para testar.</p>";
    $conn->close();
    exit;
}

// Testa o processo de pagamento via cURL interno
$url = "http://localhost/uranoPAY/web/checkout/process_payment.php";
$payload = json_encode([
    "checkout_id" => $cid,
    "name" => "Teste Usuario",
    "document" => "12345678901",
    "amount" => 5.00
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

echo "<h2>Teste - Checkout ID: $cid</h2>";
echo "<p><b>HTTP Code:</b> $httpCode</p>";
if ($curlErr)
    echo "<p style='color:red'><b>cURL Error:</b> $curlErr</p>";
echo "<p><b>Resposta RAW:</b></p>";
echo "<pre style='background:#f4f4f4;padding:10px;word-wrap:break-word'>" . htmlspecialchars($response) . "</pre>";

$decoded = json_decode($response, true);
if ($decoded) {
    echo "<p><b>JSON Decodificado:</b></p>";
    echo "<pre style='background:#eaf7ea;padding:10px'>" . print_r($decoded, true) . "</pre>";
}

// provider info
$pr = $conn->query("SELECT cb.*, up.provider_name, up.api_key, up.api_token FROM checkout_build cb LEFT JOIN user_providers up ON cb.user_provider_id=up.id WHERE cb.id=$cid");
$prow = $pr ? $pr->fetch_assoc() : null;
echo "<h3>Dados do provedor vinculado:</h3>";
echo "<pre>" . ($prow ? print_r(["provider_name" => $prow["provider_name"], "api_key_length" => strlen($prow["api_key"] ?? ''), "api_token_length" => strlen($prow["api_token"] ?? '')], true) : "NENHUM PROVEDOR VINCULADO") . "</pre>";

$conn->close();
?>