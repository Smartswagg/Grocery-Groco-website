<?php

if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}

?>

<header class="header">

   <div class="flex">

      <a href="home.php" class="logo">Sagnik<span>.</span></a>

      <nav class="navbar">
         <a href="home.php">home</a>
         <a href="shop.php">shop</a>
         <a href="orders.php">orders</a>
         <a href="about.php">about</a>
         <a href="contact.php">contact</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
         <a href="/Groco/Groco/grocery%20store/search_page.php" class="fas fa-search" title="Search"></a>
         <?php
            $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $count_cart_items->execute([$user_id]);
            $count_wishlist_items = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
            $count_wishlist_items->execute([$user_id]);
            $current_page = basename($_SERVER['PHP_SELF']);
            $show_counts = $current_page !== 'user_addresses.php';
         ?>
         <?php if($show_counts): ?>
            <a href="wishlist.php"><i class="fas fa-heart"></i><span>(<?= $count_wishlist_items->rowCount(); ?>)</span></a>
            <a href="cart.php"><i class="fas fa-shopping-cart"></i><span>(<?= $count_cart_items->rowCount(); ?>)</span></a>
         <?php endif; ?>
      </div>

      <div class="profile">
         <?php
            $is_logged_in = isset($_SESSION['user_id']);
            $fetch_profile = false;
            if($is_logged_in){
                $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
                $select_profile->execute([$_SESSION['user_id']]);
                $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
            }
         ?>
         <?php if($is_logged_in && $fetch_profile): ?>
            <img src="uploaded_img/<?= htmlspecialchars($fetch_profile['image']); ?>" alt="">
            <p><?= htmlspecialchars($fetch_profile['name']); ?></p>
            <a href="user_profile_update.php" class="btn">Update Profile</a>
            <a href="logout.php" class="delete-btn">Logout</a>
         <?php else: ?>
            <div class="flex-btn">
               <a href="user_login.php" class="option-btn">Customer Login</a>
               <a href="seller_login.php" class="option-btn">Seller Login</a>
               <a href="admin_login.php" class="option-btn">Admin Login</a>
            </div>
         <?php endif; ?>
      </div>

   </div>

</header>