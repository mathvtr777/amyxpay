<?php
// Definir o fuso horário para Brasília
date_default_timezone_set('America/Sao_Paulo');

// Função para registrar o log
function logRequest($message) {
    $logFile = 'logs.txt'; // Nome do arquivo de log
    $timestamp = date("Y-m-d H:i:s"); // Timestamp atual
    $logMessage = "[$timestamp] $message\n"; // Mensagem de log
    file_put_contents($logFile, $logMessage, FILE_APPEND); // Salvar no arquivo
}

// Chamar a URL desejada
$url = 'https://api.uranopay.com/cron/calcular_saldo_liquido_user.php'; // Substitua pela URL desejada
file_get_contents($url); // Faz a chamada sem registrar a resposta
logRequest("Chamou a URL: $url"); // Log da chamada

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs de Chamadas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #4CAF50;
        }
        .console {
            background: #282c34;
            color: #fff;
            padding: 15px;
            border-radius: 5px;
            overflow: auto;
            max-height: 400px;
            margin: 20px 0;
        }
        .log {
            padding: 5px;
            border-bottom: 1px solid #444;
        }
        .log:last-child {
            border-bottom: none;
        }
    </style>
    <script>
        // Função para atualizar a página e chamar a URL
        function updatePage() {
            setTimeout(() => {
                location.reload(); // Atualiza a página
            }, 3000); // Espera 2 segundos
        }

        // Chamadas a cada 30 segundos
        setInterval(() => {
            updatePage(); // Chama a função de atualização
        }, 30000); // 30 segundos
    </script>
</head>
<body>
    <h1>Logs de Chamadas</h1>
    <div class="console">
        <h2>Console de Logs</h2>
        <div id="logContainer">
            <?php
            // Ler e mostrar os logs
            if (file_exists('logs.txt')) {
                $logs = file('logs.txt'); // Ler todos os logs em um array
                $logs = array_reverse($logs); // Inverter a ordem dos logs
                foreach ($logs as $log) {
                    echo "<div class='log'>" . htmlspecialchars($log) . "</div>"; // Exibir cada log
                }
            } else {
                echo "<div class='log'>Nenhum log disponível.</div>";
            }
            ?>
        </div>
    </div>
</body>
</html>
