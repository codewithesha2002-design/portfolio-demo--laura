<?php
require_once __DIR__ . '/db.php';

// load composer autoload (run `composer require razorpay/razorpay` in the project root first)
require_once __DIR__ . '/../vendor/autoload.php';

use Razorpay\Api\Api;

// Razorpay API credentials (get these from dashboard, use test keys for development)
$key_id     = 'YOUR_KEY_ID';
$key_secret = 'YOUR_KEY_SECRET';

$api = new Api($key_id, $key_secret);

// expected POST parameter: amount in rupees (integer or decimal)
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid amount']);
    exit;
}

// Razorpay expects amount in paise
$amountPaise = intval(round($amount * 100));
$receipt = 'rcpt_' . uniqid();

$orderData = [
    'receipt'         => $receipt,
    'amount'          => $amountPaise,
    'currency'        => 'INR',
    'payment_capture' => 1  // Auto capture
];

try {
    $order = $api->order->create($orderData);
    // store a record in our database so we can verify later
    $stmt = $conn->prepare("INSERT INTO payments (order_id, amount, currency, status, receipt) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $order['id'],
        $amountPaise,
        'INR',
        'created',
        $receipt
    ]);

    header('Content-Type: application/json');
    echo json_encode($order);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
