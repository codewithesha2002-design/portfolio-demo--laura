<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Razorpay\Api\Api;

// use the same API keys used to create the order
$key_id     = 'YOUR_KEY_ID';
$key_secret = 'YOUR_KEY_SECRET';
$api = new Api($key_id, $key_secret);

// Razorpay sends back order_id, payment_id and signature
$input = json_decode(file_get_contents('php://input'), true);
$order_id   = isset($input['order_id']) ? $input['order_id'] : '';
$payment_id = isset($input['payment_id']) ? $input['payment_id'] : '';
signature   = isset($input['signature']) ? $input['signature'] : '';

if (!$order_id || !$payment_id || !$signature) {
    http_response_code(400);
    echo json_encode(['status' => 'missing fields']);
    exit;
}

// verify signature
$generated_signature = hash_hmac('sha256', $order_id . '|' . $payment_id, $key_secret);

if ($generated_signature === $signature) {
    // fetch payment info if you need additional details (method, email, etc.)
    try {
        $payment = $api->payment->fetch($payment_id);
        $stmt = $conn->prepare("UPDATE payments SET payment_id=?, status=?, method=? WHERE order_id=?");
        $stmt->execute([
            $payment_id,
            'paid',
            $payment['method'],
            $order_id
        ]);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok']);
    } catch (Exception $e) {
        // if fetching payment fails log it
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    // signature mismatch, mark order as failed
    $stmt = $conn->prepare("UPDATE payments SET status=? WHERE order_id=?");
    $stmt->execute(['failed', $order_id]);
    http_response_code(400);
    echo json_encode(['status' => 'invalid signature']);
}
