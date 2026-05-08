<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
    exit;
}

$publicKey = 'gestaopay_live_qiaI7Hjs8K4SOtNFvOIWbcubyIYrc3ER';
$secretKey = 'sk_live_ozYa0bRqjTe2J5s6vy8xYAA8WQhkjQt5';
$auth = base64_encode($publicKey . ':' . $secretKey);

$totalCents = intval(floatval($input['total']) * 100);
$orderId = 'PED_' . date('YmdHis') . rand(100, 999);
$customerName = $input['customer']['name'];
$customerEmail = $input['customer']['email'];
$customerPhone = preg_replace('/\D/', '', $input['customer']['phone']);
$customerCpf = preg_replace('/\D/', '', $input['customer']['document']);

if (strlen($customerCpf) !== 11) {
    echo json_encode(['success' => false, 'error' => 'CPF inválido']);
    exit;
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$postbackUrl = $protocol . $host . '/webhook_pix.php';

$items = [];
foreach ($input['items'] as $item) {
    $items[] = [
        'title' => $item['name'],
        'name' => $item['name'],
        'quantity' => (int)$item['quantity'],
        'unit_price' => (int)$item['unit_price']
    ];
}

$payload = [
    'amount' => $totalCents,
    'payment_method' => 'pix',
    'postback_url' => $postbackUrl,
    'customer' => [
        'name' => $customerName,
        'email' => $customerEmail,
        'document' => [
            'type' => 'cpf',
            'number' => $customerCpf
        ],
        'phone' => $customerPhone
    ],
    'items' => $items,
    'metadata' => [
        'order_id' => $orderId,
        'customer_name' => $customerName
    ],
    'pix' => [
        'expires_in_days' => 1
    ]
];

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
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 201 || $httpCode === 200) {
    $data = json_decode($response, true);
    $responseData = isset($data['data']) ? $data['data'] : $data;
    $transactionId = $responseData['id'] ?? null;
    $qrCode = $responseData['pix']['qr_code'] ?? null;
    
    echo json_encode([
        'success' => true,
        'qr_code' => $qrCode,
        'pix_code' => $qrCode,
        'transaction_id' => $transactionId,
        'order_id' => $orderId
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao gerar PIX. HTTP: ' . $httpCode
    ]);
}
?>