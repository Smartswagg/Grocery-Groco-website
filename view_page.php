<?php

@include 'config.php';

session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Handle review submission
if(isset($_POST['submit_review']) && isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
   $product_id = isset($_GET['pid']) ? $_GET['pid'] : null;
   // Fetch seller_id for this product
   $seller_id = null;
   if ($product_id) {
      $stmt = $conn->prepare("SELECT seller_id FROM products WHERE id = ?");
      $stmt->execute([$product_id]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      $seller_id = $row ? $row['seller_id'] : null;
   }
   $rating = intval($_POST['rating']);
   $review_text = trim($_POST['review_text']);
   // Prevent duplicate review by same user for this product+seller
   $dup_stmt = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ? AND seller_id = ?");
   $dup_stmt->execute([$user_id, $product_id, $seller_id]);
   if($dup_stmt->rowCount() > 0){
      $message[] = 'You have already reviewed this product from this seller!';
   } else {
      $ins_stmt = $conn->prepare("INSERT INTO reviews (user_id, product_id, seller_id, rating, review_text) VALUES (?, ?, ?, ?, ?)");
      $ins_stmt->execute([$user_id, $product_id, $seller_id, $rating, $review_text]);
      $message[] = 'Review submitted!';
      header("Location: view_page.php?pid=$product_id");
      exit;
   }
}
// Handle review deletion
if(isset($_POST['delete_review']) && isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
    $product_id = isset($_GET['pid']) ? $_GET['pid'] : null;
    // Fetch seller_id for this product
    $seller_id = null;
    if ($product_id) {
      $stmt = $conn->prepare("SELECT seller_id FROM products WHERE id = ?");
      $stmt->execute([$product_id]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      $seller_id = $row ? $row['seller_id'] : null;
    }
    $del_stmt = $conn->prepare("DELETE FROM reviews WHERE user_id = ? AND product_id = ? AND seller_id = ?");
    $del_stmt->execute([$user_id, $product_id, $seller_id]);
    $message[] = 'Review deleted!';
    header("Location: view_page.php?pid=$product_id");
    exit;
}

if(isset($_POST['add_to_wishlist']) && $user_id){

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

if(isset($_POST['add_to_cart']) && $user_id){

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
      // Insert into cart
      $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
      $insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
      $message[] = 'added to cart!';
   }
}

}

$pid = $_GET['pid'];
$select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
$select_products->execute([$pid]);
if($select_products->rowCount() > 0){
   $fetch_product = $select_products->fetch(PDO::FETCH_ASSOC);
   $product_id = $fetch_product['id'];
   $seller_id = $fetch_product['seller_id'];
   // Fetch seller info
   $seller_stmt = $conn->prepare("SELECT name, company_name FROM users WHERE id = ?");
   $seller_stmt->execute([$seller_id]);
   $seller = $seller_stmt->fetch(PDO::FETCH_ASSOC);
   // Fetch reviews for this product and seller
   $review_stmt = $conn->prepare("SELECT r.*, u.name AS user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? AND r.seller_id = ? ORDER BY r.created_at DESC");
   $review_stmt->execute([$product_id, $seller_id]);
   $reviews = $review_stmt->fetchAll(PDO::FETCH_ASSOC);
   // Calculate average rating
   $avg_stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE product_id = ? AND seller_id = ?");
   $avg_stmt->execute([$product_id, $seller_id]);
   $avg = $avg_stmt->fetch(PDO::FETCH_ASSOC);
   $avg_rating = $avg['avg_rating'] ? round($avg['avg_rating'], 2) : 'No ratings yet';
   $total_reviews = $avg['total'];
   // Check if user can review: must have a completed order for this product from this seller
   $can_review = false;
   if(isset($_SESSION['user_id'])) {
      $order_check = $conn->prepare("SELECT * FROM orders WHERE user_id = ? AND seller_id = ? AND status = 'delivered' AND total_products LIKE ?");
      $order_check->execute([$_SESSION['user_id'], $seller_id, "%{$fetch_product['name']}%"]);
      if($order_check->rowCount() > 0){
         $can_review = true;
      }
   }
   $user_review = null;
   if(isset($_SESSION['user_id'])) {
       $user_review_stmt = $conn->prepare("SELECT * FROM reviews WHERE user_id = ? AND product_id = ? AND seller_id = ?");
       $user_review_stmt->execute([$_SESSION['user_id'], $product_id, $seller_id]);
       $user_review = $user_review_stmt->fetch(PDO::FETCH_ASSOC);
   }
   ?>
   <form action="" class="box" method="POST">
      <div class="price" style="font-size:2.2rem;font-weight:bold;">₹<span><?= $fetch_product['price']; ?></span>/-</div>
      <img src="uploaded_img/<?= $fetch_product['image']; ?>" alt="" style="max-width:170px;max-height:170px;width:170px;height:170px;object-fit:contain;display:block;margin:0 auto 0.5rem auto;border-radius:1rem;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
      <div class="name" style="font-size:2rem;font-weight:bold; margin-top:0.7rem;"><?= $fetch_product['name']; ?></div>
      <p><span style="font-weight:bold;">Category:</span> <?= htmlspecialchars($fetch_product['category']); ?></p>
      <p>Sold by: <strong><?= htmlspecialchars($seller['company_name'] ?? 'Unknown'); ?></strong></p>
      <div class="stock">Stock: <?= $fetch_product['quantity']; ?></div>
      <div class="details"><?= $fetch_product['details']; ?></div>
      <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
      <input type="hidden" name="p_name" value="<?= $fetch_product['name']; ?>">
      <input type="hidden" name="p_price" value="<?= $fetch_product['price']; ?>">
      <input type="hidden" name="p_image" value="<?= $fetch_product['image']; ?>">
      <input type="number" min="1" value="1" name="p_qty" class="qty">
      <input type="submit" value="add to wishlist" class="option-btn" name="add_to_wishlist">
      <input type="submit" value="add to cart" class="btn" name="add_to_cart">
   </form>
   <?php
} else {
   echo '<p class="empty">no products added yet!</p>';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>quick view</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<section class="quick-view">

   <h1 class="title">quick view</h1>

</section>

<section class="product-reviews">
   <h2>Reviews for this Seller's Product</h2>
   <p>Average Rating: <strong><?= $avg_rating ?></strong> (<?= $total_reviews ?> reviews)</p>
   <?php if(isset($_SESSION['user_id']) && $can_review): ?>
       <?php if($user_review && !empty($user_review['review_text'])): ?>
           <div class="review-box" style="border:1px solid #ccc;padding:1rem;margin-bottom:1rem;background:#f9f9f9;position:relative;min-height:110px;max-width:600px;width:100%;word-break:break-word;">
               <strong>You - <?= $user_review['rating'] ?>★</strong><br>
               <span><?= htmlspecialchars($user_review['review_text']) ?></span><br>
               <small><?= $user_review['created_at'] ?></small>
               <form action="" method="POST" style="margin-top:1rem;background:transparent;box-shadow:none;">
                   <input type="hidden" name="delete_review" value="1">
                   <button type="submit" class="delete-btn" style="padding:0.5rem 1.2rem;font-size:1rem;min-width:unset;width:auto;box-shadow:none;">Delete Review</button>
               </form>
           </div>
       <?php else: ?>
           <form action="" method="POST" style="margin-bottom:2rem;">
               <label>Leave a review:</label><br>
               <select name="rating" required>
                   <option value="">Rating</option>
                   <?php for($i=5;$i>=1;$i--): ?><option value="<?= $i ?>"><?= $i ?> Star<?= $i>1?'s':'' ?></option><?php endfor; ?>
               </select>
               <textarea name="review_text" placeholder="Write your review..." required style="width:300px;height:60px;"></textarea>
               <input type="submit" name="submit_review" value="Submit Review" class="btn">
           </form>
       <?php endif; ?>
   <?php elseif(isset($_SESSION['user_id'])): ?>
       <p style="color:#888;">You can only review this product after completing a purchase from this seller.</p>
   <?php endif; ?>
   <?php
   $has_other_reviews = false;
   foreach($reviews as $rev):
       if($user_review && $rev['user_id'] == $_SESSION['user_id']) continue; // skip own review
       $has_other_reviews = true;
   ?>
       <div class="review-box" style="border:1px solid #ccc;padding:1rem;margin-bottom:1rem;">
           <strong><?= htmlspecialchars($rev['user_name']) ?> - <?= $rev['rating'] ?>★</strong><br>
           <span><?= htmlspecialchars($rev['review_text']) ?></span><br>
           <small><?= $rev['created_at'] ?></small>
       </div>
   <?php endforeach; ?>
   <?php if(!$user_review && !$has_other_reviews): ?>
       <p>No reviews yet for this seller's product.</p>
   <?php endif; ?>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>