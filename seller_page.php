<?php

@include 'config.php';

session_start();

$seller_id = $_SESSION['seller_id'] ?? null;

if(!isset($seller_id)){
   header('location:seller_login.php');
   exit; // Stop script execution
}

// You can fetch seller-specific data here later if needed
// For now, it's just the basic dashboard structure

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Seller Dashboard</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom admin css file link, assuming sellers use similar styles -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>

<?php @include 'seller_header.php'; ?>

<section class="dashboard">

   <h1 class="title">dashboard</h1>

   <div class="box-container">

      <div class="box">
         <h3>Welcome to Seller Panel!</h3>

         <a href="seller_dashboard.php" class="btn">View Dashboard</a>
      </div>

      <div class="box">
         <?php
            // Example: Count seller's products
            $select_products = $conn->prepare("SELECT COUNT(*) as total_products FROM `products` WHERE seller_id = ?");
            $select_products->execute([$seller_id]);
            $fetch_products = $select_products->fetch(PDO::FETCH_ASSOC);
            $total_products = $fetch_products['total_products'] ?? 0;
         ?>
         <h3><?= $total_products; ?></h3>
         <p>products added</p>
         <a href="seller_products.php" class="btn">see products</a>
      </div>

      <!-- Add more boxes for orders, reviews, etc. later -->

   </div>

</section>


<script src="js/admin_script.js"></script> 
<!-- Consider creating a separate seller_script.js if needed -->

</body>
</html>
