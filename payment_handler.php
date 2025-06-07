<?php
@include 'config.php';

class PaymentHandler {
    private $conn;
    private $user_id;
    private $order_id;
    private $amount;

    public function __construct($conn, $user_id, $order_id, $amount) {
        $this->conn = $conn;
        $this->user_id = $user_id;
        $this->order_id = $order_id;
        $this->amount = $amount;
    }

    public function verifyPayment($payment_id, $payment_method) {
        // For cash on delivery, we don't need to verify payment
        return true;
    }
}
?> 