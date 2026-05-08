<?php
header('Content-Type: text/html; charset=utf-8');

$publicKey = 'gestaopay_live_qiaI7Hjs8K4SOtNFvOIWbcubyIYrc3ER';
$secretKey = 'sk_live_ozYa0bRqjTe2J5s6vy8xYAA8WQhkjQt5';
$auth = base64_encode($publicKey . ':' . $secretKey);

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$postbackUrl = $protocol . $host . '/webhook_pix.php';

$orderId = 'PED_' . date('YmdHis') . rand(100, 999);

$payload = [
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
            'title' => 'Produto Teste',
            'name' => 'Produto Teste',
            'quantity' => 1,
            'unit_price' => 100
        ]
    ],
    'metadata' => [
        'order_id' => $orderId,
        'test' => true
    ],
    'pix' => [
        'expires_in_days' => 1
    ]
];

echo "<h2>=== GERANDO NOVO PIX ===</h2>";
echo "<pre>";
echo "Payload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init('https://api.gestaopayments.com/v1/payment-transaction/create');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Basic ' . $auth
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "Resposta: " . $response . "\n";

$data = json_decode($response, true);
if ($data && isset($data['data'])) {
    echo "\n\n✅ PIX GERADO COM SUCESSO!\n";
    echo "ID da transação: " . $data['data']['id'] . "\n";
    echo "QR Code: " . ($data['data']['pix']['qr_code'] ?? 'N/A') . "\n";
    echo "\n🔑 GUARDE ESTE ID: " . $data['data']['id'];
} else if ($data && isset($data['errors'])) {
    echo "\n\n❌ ERROS:\n";
    print_r($data['errors']);
}
echo "</pre>";
?>