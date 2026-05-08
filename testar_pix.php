<?php
header('Content-Type: application/json');

$publicKey = 'gestaopay_live_qiaI7Hjs8K4SOtNFvOIWbcubyIYrc3ER';
$secretKey = 'sk_live_ozYa0bRqjTe2J5s6vy8xYAA8WQhkjQt5';
$auth = base64_encode($publicKey . ':' . $secretKey);

// PEGAR DOMÍNIO REAL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
if (strpos($host, 'localhost') !== false) {
    $postbackUrl = 'https://SEU_DOMINIO_AQUI.com/webhook_pix.php'; // ALTERE!
} else {
    $postbackUrl = $protocol . $host . '/webhook_pix.php';
}

// Dados de teste CORRIGIDOS
$testPayload = [
    'amount' => 100,
    'payment_method' => 'pix',
    'postback_url' => $postbackUrl,
    'customer' => [
        'name' => 'Cliente Teste',
        'email' => 'teste@email.com',
        'document' => [
            'type' => 'cpf',
            'number' => '12345678909'
        ],
        'phone' => '11999999999'
    ],
    'items' => [
        [
            'title' => 'Produto Teste',  // CAMPO OBRIGATÓRIO
            'name' => 'Produto Teste',
            'quantity' => 1,
            'unit_price' => 100
        ]
    ],
    'metadata' => [  // CAMPO OBRIGATÓRIO
        'test' => true,
        'order_id' => 'TEST_' . date('YmdHis')
    ],
    'pix' => [
        'expires_in_days' => 1
    ]
];

echo "=== TESTE API GESTAOPAY ===\n\n";
echo "URL: https://api.gestaopayments.com/v1/payment-transaction/create\n";
echo "Postback URL: " . $postbackUrl . "\n\n";
echo "Payload: " . json_encode($testPayload, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init('https://api.gestaopayments.com/v1/payment-transaction/create');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Basic ' . $auth
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testPayload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "Response: " . $response . "\n";

if ($curlError) {
    echo "CURL Error: " . $curlError . "\n";
}

curl_close($ch);
?>