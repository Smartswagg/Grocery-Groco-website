<?php

@include 'config.php';

session_start();

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = 'guest_' . session_id();
}
// No forced login redirect

if(isset($_POST['add_to_wishlist'])){

   $pid = $_POST['pid'];
   $pid = filter_var($pid, FILTER_SANITIZE_STRING);
   $p_name = $_POST['p_name'];
   $p_name = filter_var($p_name, FILTER_SANITIZE_STRING);
   $p_price = $_POST['p_price'];
   $p_price = filter_var($p_price, FILTER_SANITIZE_STRING);
   $p_image = $_POST['p_image'];
   $p_image = filter_var($p_image, FILTER_SANITIZE_STRING);

   $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
   $check_wishlist_numbers->execute([$p_name, $user_id]);

   $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
   $check_cart_numbers->execute([$p_name, $user_id]);

   if($check_wishlist_numbers->rowCount() > 0){
      $message[] = 'already added to wishlist!';
   }elseif($check_cart_numbers->rowCount() > 0){
      $message[] = 'already added to cart!';
   }else{
      $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
      $insert_wishlist->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
      $message[] = 'added to wishlist!';
   }

}

if(isset($_POST['add_to_cart'])){

   // Check stock before adding to cart
   $pid = $_POST['pid'];
   $pid = filter_var($pid, FILTER_SANITIZE_STRING);
   $p_qty = $_POST['p_qty'];
   $p_qty = filter_var($p_qty, FILTER_SANITIZE_STRING);
   $get_stock = $conn->prepare("SELECT quantity FROM `products` WHERE id = ?");
   $get_stock->execute([$pid]);
   $product = $get_stock->fetch(PDO::FETCH_ASSOC);
   if($product && $p_qty > $product['quantity']){
      $message[] = 'Cannot add more than available stock ('.$product['quantity'].')!';
   } else {

   $pid = $_POST['pid'];
   $pid = filter_var($pid, FILTER_SANITIZE_STRING);
   $p_name = $_POST['p_name'];
   $p_name = filter_var($p_name, FILTER_SANITIZE_STRING);
   $p_price = $_POST['p_price'];
   $p_price = filter_var($p_price, FILTER_SANITIZE_STRING);
   $p_image = $_POST['p_image'];
   $p_image = filter_var($p_image, FILTER_SANITIZE_STRING);
   $p_qty = $_POST['p_qty'];
   $p_qty = filter_var($p_qty, FILTER_SANITIZE_STRING);

   $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
   $check_cart_numbers->execute([$p_name, $user_id]);

   if($check_cart_numbers->rowCount() > 0){
      $message[] = 'already added to cart!';
   }else{

      $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
      $check_wishlist_numbers->execute([$p_name, $user_id]);

      if($check_wishlist_numbers->rowCount() > 0){
         $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE name = ? AND user_id = ?");
         $delete_wishlist->execute([$p_name, $user_id]);
      }

      $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
      $insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
      $message[] = 'added to cart!';
   }
}

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>shop</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php include 'header.php'; ?>
<!-- Removed sidebar: now only top header navigation is present -->

<section class="p-category">

   <a href="category.php?category=fruits">fruits</a>
   <a href="category.php?category=vegetables">vegetables</a>
   <a href="category.php?category=fish">fish</a>
   <a href="category.php?category=meat">meat</a>

</section>

<section class="products">

   <h1 class="title">latest products</h1>

   <div class="box-container">

   <?php
      $select_products = $conn->prepare("SELECT * FROM `products` WHERE status = 'approved'");
      $select_products->execute();
      if($select_products->rowCount() > 0){
         while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <form action="" class="box" method="POST">
      <div class="price">₹<span><?= $fetch_products['price']; ?></span>/-</div>
      <a href="view_page.php?pid=<?= $fetch_products['id']; ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
      <div class="name"><?= $fetch_products['name']; ?></div>
      <?php
        // Fetch seller company name
        $seller_id = $fetch_products['seller_id'];
        $company_name = '';
        if ($seller_id) {
           $seller_stmt = $conn->prepare("SELECT company_name FROM users WHERE id = ?");
           $seller_stmt->execute([$seller_id]);
           $seller = $seller_stmt->fetch(PDO::FETCH_ASSOC);
           $company_name = $seller['company_name'] ?? '';
        }
        // Fetch average rating and total reviews for this product and seller
        $avg_stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE product_id = ? AND seller_id = ?");
        $avg_stmt->execute([$fetch_products['id'], $seller_id]);
        $avg = $avg_stmt->fetch(PDO::FETCH_ASSOC);
        $avg_rating = $avg['avg_rating'] ? round($avg['avg_rating'], 1) : 0;
        $total_reviews = $avg['total_reviews'];
      ?>
      <?php if($company_name): ?>
        <div class="company-name" style="font-size:1.3rem;color:#555;margin-bottom:0.5rem;">Sold by: <strong><?= htmlspecialchars($company_name) ?></strong></div>
      <?php endif; ?>
      <div class="star-rating" style="margin:0.5rem 0;">
        <?php
          $fullStars = floor($avg_rating);
          $halfStar = ($avg_rating - $fullStars) >= 0.5;
          for($i=1; $i<=5; $i++) {
            if($i <= $fullStars) {
              echo '<i class="fas fa-star" style="color:#f7b731;"></i>';
            } elseif($halfStar && $i == $fullStars+1) {
              echo '<i class="fas fa-star-half-alt" style="color:#f7b731;"></i>';
            } else {
              echo '<i class="far fa-star" style="color:#f7b731;"></i>';
            }
          }
          if($total_reviews) echo " <span style='font-size:1.1rem;color:#555;'>($total_reviews)</span>";
        ?>
      </div>
      <div class="stock">Stock: <?= $fetch_products['quantity']; ?></div>
      <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
      <input type="hidden" name="p_name" value="<?= $fetch_products['name']; ?>">
      <input type="hidden" name="p_price" value="<?= $fetch_products['price']; ?>">
      <input type="hidden" name="p_image" value="<?= $fetch_products['image']; ?>">
      <input type="number" min="1" value="1" name="p_qty" class="qty">
      <input type="submit" value="add to wishlist" class="option-btn" name="add_to_wishlist">
      <input type="submit" value="add to cart" class="btn" name="add_to_cart">
   </form>
   <?php
      }
   }else{
      echo '<p class="empty">no products added yet!</p>';
   }
   ?>

   </div>

</section>








<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>