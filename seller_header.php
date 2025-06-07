<?php

@include 'config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$seller_id = $_SESSION['seller_id'] ?? null;

if (!isset($seller_id)) {
   // If seller_id is not set, redirect to seller login
   header('location:seller_login.php');
   exit; // Stop script execution after redirection
}


if(isset($message)){
   foreach($message as $msg){ // Renamed variable to avoid conflict
      echo '
      <div class="message">
         <span>'.$msg.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}

?>

<header class="header">

   <div class="flex">

      <a href="seller_page.php" class="logo">Seller<span>Panel</span></a>

      <nav class="navbar">
         <a href="seller_page.php">home</a>
         <a href="seller_products.php">products</a>
         <a href="seller_message_admin.php">Contact Admin</a>
         <a href="compare_products.php">Compare Products</a>
         <!-- <a href="seller_orders.php">orders</a> -->  <!-- Add later if needed -->
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php
            // Ensure $conn is available
            if (isset($conn) && $seller_id) {
               try {
                  $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ? AND user_type = 'seller'");
                  $select_profile->execute([$seller_id]);
                  if($select_profile->rowCount() > 0){
                     $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
                     // Check if profile image exists, provide a default if not
                     $profile_img = (!empty($fetch_profile['image']) && file_exists('uploaded_img/'.$fetch_profile['image'])) ? 'uploaded_img/'.$fetch_profile['image'] : 'images/default-avatar.png'; // Adjust default image path if needed
         ?>
                     <img src="<?= htmlspecialchars($profile_img); ?>" alt="">
                     <p><?= htmlspecialchars($fetch_profile['name']); ?></p>
         <?php
                  } else {
                      // Handle case where seller profile not found or not a seller anymore
                      echo "<p>Profile not found.</p>";
                  }
               } catch (PDOException $e) {
                  echo "<p>Error fetching profile: " . $e->getMessage() . "</p>"; // Log error properly in production
               }
            } else {
                // Handle case where connection is not available or seller_id is missing
                echo "<p>Unable to load profile.</p>";
            }
         ?>
         <a href="seller_update_profile.php">Update Profile</a>
         <a href="logout.php" class="delete-btn">Logout</a>
      </div>

   </div>

</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userBtn = document.getElementById('user-btn');
    const profile = document.querySelector('.profile');
    if (userBtn && profile) {
        userBtn.addEventListener('click', function() {
            profile.classList.toggle('active');
        });
    }
});
</script>
<style>
.profile { display: none; }
.profile.active { display: block; }
</style>
