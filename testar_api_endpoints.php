<?php
header('Content-Type: text/html; charset=utf-8');

$transactionId = 'b587f598c17f4833ace3bcc0427ef1fa';

$publicKey = 'gestaopay_live_qiaI7Hjs8K4SOtNFvOIWbcubyIYrc3ER';
$secretKey = 'sk_live_ozYa0bRqjTe2J5s6vy8xYAA8WQhkjQt5';
$auth = base64_encode($publicKey . ':' . $secretKey);

echo "<h2>Testando diferentes endpoints para consultar transação: $transactionId</h2>";

// Endpoints possíveis
$endpoints = [
    "https://api.gestaopayments.com/v1/payment-transaction/info/id?id={$transactionId}",
    "https://api.gestaopayments.com/v1/payment-transaction/{$transactionId}",
    "https://api.gestaopayments.com/v1/payment-transaction/info/{$transactionId}",
    "https://api.gestaopayments.com/v1/transaction/{$transactionId}",
    "https://api.gestaopayments.com/v1/payment/{$transactionId}",
    "https://api.gestaopayments.com/v1/order/{$transactionId}"
];

foreach ($endpoints as $url) {
    echo "<h3>Testando: $url</h3>";
    
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
    
    echo "HTTP Code: $httpCode<br>";
    
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        echo "<strong style='color:green'>✅ ENCONTRADO! Resposta:</strong><br>";
        echo "<pre style='background:#e8f5e9; padding:10px; overflow:auto; max-height:300px;'>";
        print_r($data);
        echo "</pre>";
        echo "<hr>";
    } else {
        echo "Resposta: " . substr($response, 0, 200) . "<br>";
        echo "<hr>";
    }
}
?>