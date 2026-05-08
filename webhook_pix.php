<?php
$input = json_decode(file_get_contents('php://input'), true);

if ($input) {
    // Resposta pode vir dentro de "data"
    $responseData = isset($input['data']) ? $input['data'] : $input;
    $transactionId = $responseData['id'] ?? null;
    $status = $responseData['status'] ?? null;
    
    $log = date('Y-m-d H:i:s') . " - Transação: $transactionId - Status: $status\n";
    file_put_contents('webhook_log.txt', $log, FILE_APPEND);
    
    if ($transactionId && ($status === 'PAID' || $status === 'APPROVED' || $status === 'COMPLETED')) {
        file_put_contents('pagamentos_confirmados.txt', date('Y-m-d H:i:s') . " - Pagamento confirmado: $transactionId\n", FILE_APPEND);
    }
}

http_response_code(200);
echo 'OK';
?>