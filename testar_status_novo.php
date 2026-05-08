<?php
header('Content-Type: text/html; charset=utf-8');

// USE O NOVO ID GERADO
$transactionId = 'b587f598c17f4833ace3bcc0427ef1fa';

$publicKey = 'gestaopay_live_qiaI7Hjs8K4SOtNFvOIWbcubyIYrc3ER';
$secretKey = 'sk_live_ozYa0bRqjTe2J5s6vy8xYAA8WQhkjQt5';
$auth = base64_encode($publicKey . ':' . $secretKey);

echo "<h2>Consultando transação: " . $transactionId . "</h2>";

$ch = curl_init("https://api.gestaopayments.com/v1/payment-transaction/info/id?id={$transactionId}");
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

echo "<h3>HTTP Code: " . $httpCode . "</h3>";

echo "<h3>Resposta COMPLETA da API:</h3>";
echo "<pre style='background:#f0f0f0; padding:15px; overflow:auto; max-height:500px;'>";
print_r(json_decode($response, true));
echo "</pre>";

$data = json_decode($response, true);
if ($data) {
    $responseData = isset($data['data']) ? $data['data'] : $data;
    $status = $responseData['status'] ?? 'NAO_ENCONTRADO';
    $pixStatus = $responseData['pix']['status'] ?? '';
    echo "<h2 style='color: " . ($status === 'PAID' ? 'green' : 'orange') . "'>STATUS: " . $status . "</h2>";
    echo "<h3>Status do PIX: " . $pixStatus . "</h3>";
}
?>