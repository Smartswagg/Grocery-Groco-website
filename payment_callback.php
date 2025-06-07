<?php
@include 'config.php';
@include 'payment_handler.php';

session_start();

if(isset($_POST)) {
    $payment_method = $_POST['payment_method'] ?? '';
    $payment_id = $_POST['payment_id'] ?? '';
    $order_id = $_POST['order_id'] ?? '';
    $status = $_POST['status'] ?? '';
    
    if($payment_method && $payment_id && $order_id) {
        $payment_handler = new PaymentHandler($conn, $_SESSION['user_id'], $order_id, 0);
        $payment_verified = $payment_handler->verifyPayment($payment_id, $payment_method);
        
        if($payment_verified) {
            // Update order status in database
            $update_order = $conn->prepare("UPDATE `orders` SET status = 'processed', payment_status = 'completed' WHERE id = ?");
            $update_order->execute([$order_id]);
            
            // Redirect to success page
            header('Location: order_success.php?order_id=' . $order_id);
            exit();
        } else {
            // Payment verification failed
            header('Location: payment_failed.php?order_id=' . $order_id);
            exit();
        }
    }
}

// If we reach here, something went wrong
header('Location: payment_failed.php');
exit();
?> 