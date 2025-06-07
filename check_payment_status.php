<?php
@include 'config.php';
@include 'payment_handler.php';

session_start();

header('Content-Type: application/json');

if(!isset($_GET['order_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Order ID not provided']);
    exit();
}

$order_id = $_GET['order_id'];

// Get order details
$order_query = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$order_query->execute([$order_id]);
$order = $order_query->fetch(PDO::FETCH_ASSOC);

if(!$order) {
    echo json_encode(['status' => 'error', 'message' => 'Order not found']);
    exit();
}

// Check payment status
$payment_handler = new PaymentHandler($conn, $order['user_id'], $order_id, $order['total_price']);
$payment_verified = $payment_handler->verifyPayment($order_id, $order['method']);

if($payment_verified) {
    // Update order status
    $update_order = $conn->prepare("UPDATE orders SET status = 'processed', payment_status = 'completed' WHERE id = ?");
    $update_order->execute([$order_id]);
    
    // Clear cart
    $delete_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $delete_cart->execute([$order['user_id']]);
    
    // Clear session data
    unset($_SESSION['payment_data']);
    unset($_SESSION['current_order_id']);
    
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'pending']);
}
?> 