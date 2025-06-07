<?php

@include 'config.php';

session_start();

$seller_id = $_SESSION['seller_id'] ?? null;

if(!isset($seller_id)){
   header('location:seller_login.php');
   exit;
};

// Add Product Logic
if(isset($_POST['add_product'])){

   $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
   $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_STRING); // Consider FILTER_VALIDATE_FLOAT or INT
   $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
   $details = filter_input(INPUT_POST, 'details', FILTER_SANITIZE_STRING);

   $image = $_FILES['image']['name'] ?? '';
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'] ?? 0;
   $image_tmp_name = $_FILES['image']['tmp_name'] ?? '';
   $image_folder = 'uploaded_img/'.$image;

   // Check if product name already exists for this seller
   $select_products = $conn->prepare("SELECT id FROM `products` WHERE name = ? AND seller_id = ?");
   $select_products->execute([$name, $seller_id]);
   if($select_products->rowCount() > 0){
      $message[] = 'You have already added this product!';
   } elseif (empty($image)) {
      $message[] = 'Image is required!';
   } else {
      // Insert product with seller_id and status 'pending'
      $insert_products = $conn->prepare("INSERT INTO `products`(name, category, details, price, image, seller_id, status) VALUES(?,?,?,?,?,?,?)");
      $result = $insert_products->execute([$name, $category, $details, $price, $image, $seller_id, 'pending']);

      if($result){
         if($image_size > 2000000){ // 2MB limit
            $message[] = 'Image size is too large (max 2MB)!';
            // Optionally delete the DB record if image upload failed validation after insert
            $conn->prepare("DELETE FROM `products` WHERE name = ? AND seller_id = ?")->execute([$name, $seller_id]);
         } elseif (!empty($image_tmp_name) && move_uploaded_file($image_tmp_name, $image_folder)) {
            $message[] = 'New product added!';
         } else {
            $message[] = 'Failed to upload image!';
            // Optionally delete the DB record if image upload failed after insert
             $conn->prepare("DELETE FROM `products` WHERE name = ? AND seller_id = ?")->execute([$name, $seller_id]);
         }
      } else {
         $message[] = 'Failed to add product!';
      }
   }
};

// Delete Product Logic
if(isset($_GET['delete'])){

   $delete_id = $_GET['delete'];

   // Verify the seller owns this product before deleting
   $check_owner = $conn->prepare("SELECT image FROM `products` WHERE id = ? AND seller_id = ?");
   $check_owner->execute([$delete_id, $seller_id]);

   if($check_owner->rowCount() > 0){
      $fetch_delete_image = $check_owner->fetch(PDO::FETCH_ASSOC);
      $image_path = 'uploaded_img/'.$fetch_delete_image['image'];

      // Delete product record
      $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ? AND seller_id = ?");
      $delete_product->execute([$delete_id, $seller_id]);

      // Delete image file
      if (file_exists($image_path)) {
            unlink($image_path);
      }

      // Delete associated wishlist/cart items (optional but good practice)
      $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE pid = ?");
      $delete_wishlist->execute([$delete_id]);
      $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
      $delete_cart->execute([$delete_id]);

      $message[] = 'Product deleted successfully!';
      header('Location: seller_products.php'); // Redirect to refresh list and clear GET param
      exit;

   } else {
      $message[] = 'Product not found or you do not have permission to delete it.';
      header('Location: seller_products.php'); // Redirect
      exit;
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Seller Products</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link (using admin style for now) -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>

<?php include 'seller_header.php'; ?>

<section class="add-products">

   <h1 class="title">add new product</h1>

   <form action="" method="POST" enctype="multipart/form-data">
      <div class="flex">
         <div class="inputBox">
         <input type="text" name="name" class="box" required placeholder="enter product name">
         <select name="category" class="box" required>
            <option value="" selected disabled>select category</option>
               <option value="vegetables">vegetables</option>
               <option value="fruits">fruits</option>
               <option value="meat">meat</option>
               <option value="fish">fish</option>
               <!-- Add more categories as needed -->
         </select>
         </div>
         <div class="inputBox">
         <input type="number" min="0" step="0.01" name="price" class="box" required placeholder="enter product price">
         <input type="file" name="image" required class="box" accept="image/jpg, image/jpeg, image/png">
         </div>
      </div>
      <textarea name="details" class="box" required placeholder="enter product details" cols="30" rows="10"></textarea>
      <input type="submit" class="btn" value="add product" name="add_product">
   </form>

</section>

<section class="show-products">

   <h1 class="title">your added products</h1>

   <div class="box-container">


   <?php
      // Show only products belonging to this seller
      $show_products = $conn->prepare("SELECT * FROM `products` WHERE seller_id = ? ORDER BY id DESC");
      $show_products->execute([$seller_id]);
      if($show_products->rowCount() > 0){
         while($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)){  
            $image_path = 'uploaded_img/'.$fetch_products['image'];
            $display_image = file_exists($image_path) ? $image_path : 'images/default-product.png'; // Default image if missing
            // Fetch average rating and total reviews for this product and seller
            $avg_stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE product_id = ? AND seller_id = ?");
            $avg_stmt->execute([$fetch_products['id'], $seller_id]);
            $avg = $avg_stmt->fetch(PDO::FETCH_ASSOC);
            $avg_rating = $avg['avg_rating'] ? round($avg['avg_rating'], 1) : 0;
            $total_reviews = $avg['total_reviews'];
   ?>
   <div class="box">
      <div class="price">₹<?= htmlspecialchars($fetch_products['price']); ?>/-</div>
      <img src="<?= htmlspecialchars($display_image); ?>" alt="<?= htmlspecialchars($fetch_products['name']); ?>">
      <div class="name"><?= htmlspecialchars($fetch_products['name']); ?></div>
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
      <div class="cat">Status: <span style="font-weight:bold; color:<?= $fetch_products['status'] === 'approved' ? '#27ae60' : ($fetch_products['status'] === 'pending' ? '#f39c12' : '#e74c3c'); ?>;">
        <?= ucfirst($fetch_products['status']); ?>
      </span></div>
      <div class="details"><?= nl2br(htmlspecialchars($fetch_products['details'])); ?></div>
      <div class="flex-btn">
         <a href="seller_update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">update</a>
         <a href="seller_products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('Delete this product?');">delete</a>
      </div>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">You haven\'t added any products yet!</p>';
      }
   ?>

   </div>

</section>


<script src="js/admin_script.js"></script> <!-- Using admin script for now -->

</body>
</html>
