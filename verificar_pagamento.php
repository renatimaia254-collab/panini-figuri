<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$transactionId = $_GET['transaction_id'] ?? '';
if (!$transactionId) {
    echo json_encode(['status' => 'ERROR', 'message' => 'ID não informado']);
    exit;
}

$publicKey = 'gestaopay_live_qiaI7Hjs8K4SOtNFvOIWbcubyIYrc3ER';
$secretKey = 'sk_live_ozYa0bRqjTe2J5s6vy8xYAA8WQhkjQt5';
$auth = base64_encode($publicKey . ':' . $secretKey);

// ENDPOINT CORRETO: /info/{id} (sem ?id=)
$url = "https://api.gestaopayments.com/v1/payment-transaction/info/{$transactionId}";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Basic ' . $auth
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Salvar log
file_put_contents('status_log.txt', date('Y-m-d H:i:s') . " - ID: $transactionId - HTTP: $httpCode - Response: " . substr($response, 0, 300) . "\n", FILE_APPEND);

if ($httpCode == 200) {
    $data = json_decode($response, true);
    $responseData = isset($data['data']) ? $data['data'] : $data;
    $status = strtoupper($responseData['status'] ?? 'UNKNOWN');
    
    if ($status === 'PAID' || $status === 'APPROVED' || $status === 'COMPLETED') {
        echo json_encode(['status' => 'PAID', 'raw_status' => $status, 'transaction_id' => $transactionId]);
    } else if ($status === 'PENDING' || $status === 'WAITING') {
        echo json_encode(['status' => 'PENDING', 'raw_status' => $status, 'transaction_id' => $transactionId]);
    } else if ($status === 'REFUNDED' || $status === 'CANCELLED' || $status === 'REFUSED') {
        echo json_encode(['status' => 'CANCELLED', 'raw_status' => $status, 'transaction_id' => $transactionId]);
    } else {
        echo json_encode(['status' => 'PENDING', 'raw_status' => $status, 'transaction_id' => $transactionId]);
    }
} else if ($httpCode == 404) {
    echo json_encode([
        'status' => 'PENDING',
        'code' => $httpCode,
        'message' => 'Transação ainda não processada'
    ]);
} else {
    echo json_encode([
        'status' => 'ERROR',
        'code' => $httpCode,
        'message' => 'Erro ao consultar API'
    ]);
}
?>