<?php
header('Content-Type: application/json');

$publicKey = 'gestaopay_live_qiaI7Hjs8K4SOtNFvOIWbcubyIYrc3ER';
$secretKey = 'sk_live_ozYa0bRqjTe2J5s6vy8xYAA8WQhkjQt5';
$auth = base64_encode($publicKey . ':' . $secretKey);

$payload = [
    'amount' => 1000,
    'payment_method' => 'pix',
    'postback_url' => 'http://localhost/panini/webhook_pix.php',
    'customer' => [
        'name' => 'Cliente Teste',
        'email' => 'teste@email.com',
        'document' => ['type' => 'cpf', 'number' => '12345678909'],
        'phone' => '11999999999'
    ],
    'items' => [['title' => 'Produto Teste', 'unit_price' => 1000, 'quantity' => 1, 'tangible' => true]],
    'pix' => ['expires_in_days' => 1],
    'request' => ['amount' => 1000, 'payment_method' => 'pix'],
    'metadata' => ['teste' => 'ok']
];

$ch = curl_init('https://api.gestaopayments.com/v1/payment-transaction/create');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Basic ' . $auth
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP: " . $httpCode . "\n";
echo "Resposta: " . $response;
?>