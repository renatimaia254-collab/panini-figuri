<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false]);
    exit;
}

$orderId = $input['order_id'] ?? '';
$action = $input['action'] ?? '';

if ($orderId && $action) {
    $logFile = 'clicks_log.txt';
    $logEntry = date('Y-m-d H:i:s') . " - Pedido: $orderId - Ação: $action\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    // Também salva no localStorage do servidor (arquivo JSON)
    $clicksFile = 'clicks.json';
    $clicks = [];
    if (file_exists($clicksFile)) {
        $clicks = json_decode(file_get_contents($clicksFile), true) ?: [];
    }
    $clicks[] = ['date' => date('Y-m-d H:i:s'), 'order_id' => $orderId, 'action' => $action];
    file_put_contents($clicksFile, json_encode($clicks));
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>