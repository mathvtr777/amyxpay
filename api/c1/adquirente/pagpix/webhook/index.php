<?php
date_default_timezone_set('America/Sao_Paulo');

$jsonData = file_get_contents('php://input');

if ($jsonData) {
    $currentDateTime = date('d/m/Y H:i:s');
    $dataToSave = "Data e Hora: " . $currentDateTime . "\n";
    $dataToSave .= "JSON Recebido: " . $jsonData . "\n";
    $dataToSave .= "--------------------\n";
    $filePath = 'dados_cashout.txt';
    file_put_contents($filePath, $dataToSave, FILE_APPEND);
    echo json_encode(['status' => 'success', 'message' => 'Dados armazenados com sucesso']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Nenhum dado recebido']);
}
