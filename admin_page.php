<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>admin page</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="dashboard">

   <h1 class="title">dashboard</h1>

   <div class="box-container">

      <div class="box">
      <?php
         $total_pendings = 0;
         $select_pendings = $conn->prepare("SELECT * FROM `orders` WHERE status != 'delivered'");
         $select_pendings->execute();
         while($fetch_pendings = $select_pendings->fetch(PDO::FETCH_ASSOC)){
            $total_pendings += $fetch_pendings['total_price'];
         };
      ?>
      <h3>₹<?= $total_pendings; ?>/-</h3>
      <p>total pendings</p>
      <a href="admin_orders.php?type=pending" class="btn">see orders</a>
      </div>

      <div class="box">
      <?php
         $total_completed = 0;
         $select_completed = $conn->prepare("SELECT * FROM `orders` WHERE status = ?");
         $select_completed->execute(['delivered']);
         $delivered_orders = $select_completed->fetchAll(PDO::FETCH_ASSOC);
         foreach($delivered_orders as $fetch_completed){
            $total_completed += $fetch_completed['total_price'];
         }
      ?>
      <h3>₹<?= $total_completed; ?>/-</h3>
      <p>completed orders</p>
      <a href="admin_orders.php?type=completed" class="btn">see orders</a>
      </div>

      <div class="box">
      <?php
         $select_orders = $conn->prepare("SELECT * FROM `orders`");
         $select_orders->execute();
         $number_of_orders = $select_orders->rowCount();
      ?>
      <h3><?= $number_of_orders; ?></h3>
      <p>orders placed</p>
      <a href="admin_orders.php" class="btn">see orders</a>
      </div>


      <div class="box">
      <?php
         $select_users = $conn->prepare("SELECT * FROM `users` WHERE user_type = ?");
         $select_users->execute(['user']);
         $number_of_users = $select_users->rowCount();
      ?>
      <h3><?= $number_of_users; ?></h3>
      <p>total customers</p>
      <a href="admin_users.php?type=user" class="btn">see accounts</a>
      </div>

      <div class="box">
      <?php
         $select_sellers = $conn->prepare("SELECT * FROM `users` WHERE user_type = ?");
         $select_sellers->execute(['seller']);
         $number_of_sellers = $select_sellers->rowCount();
      ?>
      <h3><?= $number_of_sellers; ?></h3>
      <p>total sellers</p>
      <a href="admin_users.php?type=seller" class="btn">see accounts</a>
      </div>

      <div class="box">
      <?php
         $select_accounts = $conn->prepare("SELECT * FROM `users` WHERE user_type = 'user' OR user_type = 'seller'");
         $select_accounts->execute();
         $number_of_accounts = $select_accounts->rowCount();
      ?>
      <h3><?= $number_of_accounts; ?></h3>
      <p>total accounts</p>
      <a href="admin_users.php" class="btn">see accounts</a>
      </div>

      <div class="box">
      <?php
         // Seller messages count
         $seller_count_stmt = $conn->prepare("SELECT COUNT(*) FROM `message` m JOIN users u ON m.user_id = u.id WHERE u.user_type = 'seller'");
         $seller_count_stmt->execute();
         $number_of_seller_messages = $seller_count_stmt->fetchColumn();
      ?>
      <h3><?= $number_of_seller_messages; ?></h3>
      <p>seller messages</p>
      <a href="admin_messages.php?type=seller" class="btn">see seller messages</a>
      </div>

      <div class="box">
      <?php
         // User messages count
         $user_count_stmt = $conn->prepare("SELECT COUNT(*) FROM `message` m JOIN users u ON m.user_id = u.id WHERE u.user_type = 'user'");
         $user_count_stmt->execute();
         $number_of_user_messages = $user_count_stmt->fetchColumn();
      ?>
      <h3><?= $number_of_user_messages; ?></h3>
      <p>customer messages</p>
      <a href="admin_messages.php?type=user" class="btn">see customer messages</a>
      </div>

      <div class="box">
      <?php
         $select_products = $conn->prepare("SELECT COUNT(*) FROM `products`");
         $select_products->execute();
         $number_of_products = $select_products->fetchColumn();
      ?>
      <h3><?= $number_of_products; ?></h3>
      <p>total products</p>
      <a href="admin_products.php" class="btn">See Products</a>
      </div>

   </div>

</section>













<script src="js/script.js"></script>

</body>
</html>