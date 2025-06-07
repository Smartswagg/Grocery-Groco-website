<?php
@include 'config.php';
session_start();

if(!isset($_SESSION['payment_data']) || !isset($_SESSION['current_order_id'])) {
    header('Location: checkout.php');
    exit();
}

$payment_data = $_SESSION['payment_data'];
$order_id = $_SESSION['current_order_id'];
$method = $_GET['method'] ?? '';

if($method != 'paytm' && $method != 'phonepe') {
    header('Location: checkout.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Payment</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .payment-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            text-align: center;
        }
        .payment-status {
            margin: 20px 0;
            padding: 20px;
            border-radius: 5px;
            background: #f8f9fa;
        }
        .payment-qr {
            margin: 20px 0;
        }
        .payment-qr img {
            max-width: 200px;
            height: auto;
        }
        .payment-instructions {
            margin: 20px 0;
            text-align: left;
        }
        .payment-instructions ol {
            margin-left: 20px;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <h2>Complete Your Payment</h2>
        
        <div class="payment-status">
            <h3>Order #<?= $order_id ?></h3>
            <p>Amount: ₹<?= $payment_data['data']['amount'] ?? $payment_data['data']['TXN_AMOUNT'] ?></p>
        </div>

        <?php if($method == 'paytm'): ?>
            <div class="payment-qr">
                <img src="images/paytm-qr.png" alt="Paytm QR Code">
            </div>
            <div class="payment-instructions">
                <h4>To complete your payment:</h4>
                <ol>
                    <li>Open your Paytm app</li>
                    <li>Scan the QR code above</li>
                    <li>Enter the amount shown</li>
                    <li>Complete the payment</li>
                </ol>
            </div>
            <form id="paytmForm" action="<?= $payment_data['url'] ?>" method="POST">
                <?php foreach($payment_data['data'] as $key => $value): ?>
                    <input type="hidden" name="<?= $key ?>" value="<?= $value ?>">
                <?php endforeach; ?>
            </form>
            <script>
                // Auto-submit form after 5 seconds
                setTimeout(function() {
                    document.getElementById('paytmForm').submit();
                }, 5000);
            </script>
        <?php else: ?>
            <div class="payment-qr">
                <img src="images/phonepe-qr.png" alt="PhonePe QR Code">
            </div>
            <div class="payment-instructions">
                <h4>To complete your payment:</h4>
                <ol>
                    <li>Open your PhonePe app</li>
                    <li>Scan the QR code above</li>
                    <li>Enter the amount shown</li>
                    <li>Complete the payment</li>
                </ol>
            </div>
            <form id="phonepeForm" action="<?= $payment_data['url'] ?>" method="POST">
                <input type="hidden" name="data" value="<?= base64_encode(json_encode($payment_data['data'])) ?>">
                <input type="hidden" name="x-verify" value="<?= $payment_data['x_verify'] ?>">
            </form>
            <script>
                // Auto-submit form after 5 seconds
                setTimeout(function() {
                    document.getElementById('phonepeForm').submit();
                }, 5000);
            </script>
        <?php endif; ?>

        <div class="payment-status">
            <p>Please complete the payment within 10 minutes</p>
            <p>Your order will be confirmed once the payment is successful</p>
        </div>
    </div>

    <script>
        // Check payment status every 10 seconds
        setInterval(function() {
            fetch('check_payment_status.php?order_id=<?= $order_id ?>')
                .then(response => response.json())
                .then(data => {
                    if(data.status === 'success') {
                        window.location.href = 'order_success.php?order_id=<?= $order_id ?>';
                    }
                });
        }, 10000);
    </script>
</body>
</html> 