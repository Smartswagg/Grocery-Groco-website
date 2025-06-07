<?php

@include 'config.php';

session_start();

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = 'guest_' . session_id();
}
// No forced login redirect

function getStatusMessage($status) {
    $status = strtolower(trim($status));
    switch($status) {
        case 'pending': return 'Your order is awaiting processing.';
        case 'processed': return 'Your order is being prepared.';
        case 'shipped': return 'Your order is on the way.';
        case 'out for delivery': return 'Your order is out for delivery.';
        case 'delivered': return 'Your order has been delivered.';
        case 'cancelled': return 'Your order was cancelled.';
        default: return 'Order status unknown.';
    }
}
function getStatusStep($status) {
    $status = strtolower(trim($status));
    switch($status) {
        case 'pending': return 1;
        case 'processed': return 2;
        case 'shipped': return 3;
        case 'out for delivery': return 4;
        case 'delivered': return 5;
        case 'cancelled': return 0;
        default: return 0;
    }
}

if(isset($_POST['add_to_cart'])){
   $product_name = $_POST['product_name'];
   $product_price = $_POST['product_price'];
   $product_image = $_POST['product_image'];
   $product_quantity = $_POST['product_quantity'];
   $seller_id = $_POST['seller_id'];
   $seller_name = $_POST['seller_name'];
   $product_id = $_POST['product_id'];

   $check_cart_numbers = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');

   if(mysqli_num_rows($check_cart_numbers) > 0){
      $message[] = 'already added to cart!';
   }else{
      mysqli_query($conn, "INSERT INTO `cart`(user_id, seller_id, seller_name, product_id, name, price, quantity, image) VALUES('$user_id', '$seller_id', '$seller_name', '$product_id', '$product_name', '$product_price', '$product_quantity', '$product_image')") or die('query failed');
      $message[] = 'product added to cart!';
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>orders</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php include 'header.php'; ?>
<!-- Removed sidebar: now only top header navigation is present -->


<section class="placed-orders">

   <h1 class="title">placed orders</h1>

   <div class="box-container">

   <?php
      $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
      $select_orders->execute([$user_id]);
      if($select_orders->rowCount() > 0){
         while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <div class="box">
      <p> placed on : <span><?= $fetch_orders['placed_on']; ?></span> </p>
      <p> name : <span><?= $fetch_orders['name']; ?></span> </p>
      <p> number : <span><?= $fetch_orders['number']; ?></span> </p>
      <p> email : <span><?= $fetch_orders['email']; ?></span> </p>
      <p> address : <span><?= $fetch_orders['address']; ?></span> </p>
      <p> payment method : <span><?= $fetch_orders['method']; ?></span> </p>
      <p> your orders : <span><?= $fetch_orders['total_products']; ?></span> </p>
      <p> total price : <span>₹<?= $fetch_orders['total_price']; ?>/-</span> </p>
      <p> order status : <span style="color:<?php if(trim(strtolower($fetch_orders['status'])) == 'processed'){ echo 'orange'; } elseif(trim(strtolower($fetch_orders['status'])) == 'shipped'){ echo 'blue'; } elseif(trim(strtolower($fetch_orders['status'])) == 'delivered'){ echo 'green'; } elseif(trim(strtolower($fetch_orders['status'])) == 'out for delivery'){ echo 'red'; } else { echo 'red'; } ?>"><?= $fetch_orders['status']; ?></span> </p>
      <div class="order-status-progress">
        <?php $step = getStatusStep($fetch_orders['status']); ?>
        <?php if($fetch_orders['status'] !== 'cancelled'): ?>
        <div class="progress-bar">
          <div class="progress-step <?= $step >= 1 ? 'active' : '' ?>">Pending</div>
          <div class="progress-step <?= $step >= 2 ? 'active' : '' ?>">Processed</div>
          <div class="progress-step <?= $step >= 3 ? 'active' : '' ?>">Shipped</div>
          <div class="progress-step <?= $step >= 4 ? 'active' : '' ?>">Out for Delivery</div>
          <div class="progress-step <?= $step >= 5 ? 'active' : '' ?>">Delivered</div>
        </div>
        <?php else: ?>
          <div class="progress-bar cancelled"><div class="progress-step active">Cancelled</div></div>
        <?php endif; ?>
        <div class="status-message" style="margin-top:0.7rem;font-weight:bold;">
          <?= getStatusMessage($fetch_orders['status']); ?>
        </div>
      </div>
   </div>
   <?php
      }
   }else{
      echo '<p class="empty">no orders placed yet!</p>';
   }
   ?>

   </div>

</section>









<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>