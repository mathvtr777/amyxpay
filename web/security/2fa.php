<?php

declare(strict_types=1);

session_start();

require_once 'GoogleAuthenticator.php'; // Inclua manualmente a biblioteca.

$ga = new PHPGangsta_GoogleAuthenticator();
$secret = $ga->createSecret(); // Gere a chave secreta para o 2FA.
$_SESSION['2fa_secret'] = $secret; // Salve a chave secreta na sessão temporariamente.

$qrCodeUrl = $ga->getQRCodeGoogleUrl('SeuApp', $secret); // Gere a URL do QR Code.

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verify_2fa'])) {
    $inputCode = $_POST['2fa_code'] ?? null; // Código inserido pelo usuário.
    $secret = $_SESSION['2fa_secret'] ?? null;
    $user_id = $_POST['user_id'] ?? null;


    if ($secret === null || $inputCode === null || $user_id === null) {
        responseJson("error", "Dados inválidos. Tente novamente.");
    }

    $checkResult = $ga->verifyCode($secret, $inputCode, 2); // Valida o código com tolerância de 2 minutos.

    if ($checkResult) {
        responseJson("success", "2FA configurado com sucesso!");
        // Salve a chave secreta no banco de dados para o usuário.
        save2FASecret($secret, $user_id, $conn); // Substitua `1` pelo ID real do usuário.
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
    $query = "UPDATE users SET 2fa_secret = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new RuntimeException("Erro ao preparar a consulta: {$conn->error}");
    }

    $stmt->bind_param("si", $secret, $user_id);
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
