<?php

@include 'config.php';

session_start();

// Handle admin approval actions BEFORE any output
if(isset($_POST['approve_product']) && isset($_POST['product_id'])){
   $pid = intval($_POST['product_id']);
   $stmt = $conn->prepare("UPDATE products SET status = 'approved' WHERE id = ?");
   $stmt->execute([$pid]);
   header('Location: admin_products.php');
   exit;
}
if(isset($_POST['reject_product']) && isset($_POST['product_id'])){
   $pid = intval($_POST['product_id']);
   $stmt = $conn->prepare("UPDATE products SET status = 'rejected' WHERE id = ?");
   $stmt->execute([$pid]);
   header('Location: admin_products.php');
   exit;
}

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
};

if(isset($_POST['add_product'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $price = $_POST['price'];
   $price = filter_var($price, FILTER_SANITIZE_STRING);
   $category = $_POST['category'];
   $category = filter_var($category, FILTER_SANITIZE_STRING);
   $details = $_POST['details'];
   $details = filter_var($details, FILTER_SANITIZE_STRING);

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;

   $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
   $select_products->execute([$name]);

   if($select_products->rowCount() > 0){
      $message[] = 'product name already exist!';
   }else{

      $insert_products = $conn->prepare("INSERT INTO `products`(name, category, details, price, image) VALUES(?,?,?,?,?)");
      $insert_products->execute([$name, $category, $details, $price, $image]);

      if($insert_products){
         if($image_size > 2000000){
            $message[] = 'image size is too large!';
         }else{
            move_uploaded_file($image_tmp_name, $image_folder);
            $message[] = 'new product added!';
         }

      }

   }

};

if(isset($_GET['delete'])){

   $delete_id = $_GET['delete'];
   $select_delete_image = $conn->prepare("SELECT image FROM `products` WHERE id = ?");
   $select_delete_image->execute([$delete_id]);
   $fetch_delete_image = $select_delete_image->fetch(PDO::FETCH_ASSOC);
   unlink('uploaded_img/'.$fetch_delete_image['image']);
   $delete_products = $conn->prepare("DELETE FROM `products` WHERE id = ?");
   $delete_products->execute([$delete_id]);
   $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE pid = ?");
   $delete_wishlist->execute([$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
   $delete_cart->execute([$delete_id]);
   header('location:admin_products.php');


}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>products</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="show-products">

   <h1 class="title">products added</h1>

   <form action="" method="GET" class="filter-form">
      <div class="filter-group">
         <input type="text" name="product_name" placeholder="Search by product name" value="<?= isset($_GET['product_name']) ? htmlspecialchars($_GET['product_name']) : '' ?>">
         <select name="category">
            <option value="">All Categories</option>
            <?php
            $categories = $conn->query("SELECT DISTINCT category FROM products ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
            foreach($categories as $cat) {
               $selected = (isset($_GET['category']) && $_GET['category'] === $cat) ? 'selected' : '';
               echo "<option value='".htmlspecialchars($cat)."' $selected>".htmlspecialchars($cat)."</option>";
            }
            ?>
         </select>
         <input type="text" name="seller" placeholder="Search by seller/company" value="<?= isset($_GET['seller']) ? htmlspecialchars($_GET['seller']) : '' ?>">
         <button type="submit" class="btn">Filter</button>
         <?php if(isset($_GET['product_name']) || isset($_GET['category']) || isset($_GET['seller'])): ?>
            <a href="admin_products.php" class="btn" style="background:#e74c3c;">Clear Filters</a>
         <?php endif; ?>
      </div>
   </form>

   <div class="box-container">

   <?php
      $where_conditions = [];
      $params = [];

      if(isset($_GET['product_name']) && !empty($_GET['product_name'])) {
         $where_conditions[] = "p.name LIKE ?";
         $params[] = "%".$_GET['product_name']."%";
      }

      if(isset($_GET['category']) && !empty($_GET['category'])) {
         $where_conditions[] = "p.category = ?";
         $params[] = $_GET['category'];
      }

      if(isset($_GET['seller']) && !empty($_GET['seller'])) {
         $where_conditions[] = "(u.name LIKE ? OR u.company_name LIKE ?)";
         $params[] = "%".$_GET['seller']."%";
         $params[] = "%".$_GET['seller']."%";
      }

      $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

      $show_products = $conn->prepare("SELECT p.*, u.name as seller_name, u.company_name 
         FROM `products` p 
         LEFT JOIN users u ON p.seller_id = u.id 
         $where_clause 
         ORDER BY p.id DESC");
      $show_products->execute($params);
      if($show_products->rowCount() > 0){
         while($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)){  
   ?>
   <div class="box">
      <div class="price">₹<?= $fetch_products['price']; ?>/-</div>
      <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
      <div class="name"><?= $fetch_products['name']; ?></div>
      <?php
         // Fetch seller info
         $seller_stmt = $conn->prepare("SELECT name, company_name FROM users WHERE id = ?");
         $seller_stmt->execute([$fetch_products['seller_id']]);
         $seller = $seller_stmt->fetch(PDO::FETCH_ASSOC);
         $seller_name = $seller['name'] ?? 'Unknown';
         $company_name = $seller['company_name'] ?? 'Unknown';
         // Fetch average rating and total reviews
         $avg_stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE product_id = ? AND seller_id = ?");
         $avg_stmt->execute([$fetch_products['id'], $fetch_products['seller_id']]);
         $avg = $avg_stmt->fetch(PDO::FETCH_ASSOC);
         $avg_rating = $avg['avg_rating'] ? round($avg['avg_rating'], 1) : 0;
         $total_reviews = $avg['total_reviews'];
      ?>
      <div class="cat">Seller: <strong><?= htmlspecialchars($seller_name) ?></strong></div>
      <div class="cat">Company: <strong><?= htmlspecialchars($company_name) ?></strong></div>
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
      <div class="cat"><?= $fetch_products['category']; ?></div>
      <div class="details"><?= $fetch_products['details']; ?></div>
      <div class="flex-btn">
         <a href="admin_products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('delete this product?');">delete</a>
      </div>
   </div>
   <?php
      }
   }else{
      echo '<p class="empty">now products added yet!</p>';
   }
   ?>

   </div>

</section>

<section class="approve-products">
   <h1 class="title">Pending Product Approvals</h1>

   <form action="" method="GET" class="filter-form">
      <div class="filter-group">
         <input type="text" name="pending_product_name" placeholder="Search by product name" value="<?= isset($_GET['pending_product_name']) ? htmlspecialchars($_GET['pending_product_name']) : '' ?>">
         <select name="pending_category">
            <option value="">All Categories</option>
            <?php
            $categories = $conn->query("SELECT DISTINCT category FROM products WHERE status = 'pending' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
            foreach($categories as $cat) {
               $selected = (isset($_GET['pending_category']) && $_GET['pending_category'] === $cat) ? 'selected' : '';
               echo "<option value='".htmlspecialchars($cat)."' $selected>".htmlspecialchars($cat)."</option>";
            }
            ?>
         </select>
         <input type="text" name="pending_seller" placeholder="Search by seller/company" value="<?= isset($_GET['pending_seller']) ? htmlspecialchars($_GET['pending_seller']) : '' ?>">
         <button type="submit" class="btn">Filter</button>
         <?php if(isset($_GET['pending_product_name']) || isset($_GET['pending_category']) || isset($_GET['pending_seller'])): ?>
            <a href="admin_products.php" class="btn" style="background:#e74c3c;">Clear Filters</a>
         <?php endif; ?>
      </div>
   </form>

   <div class="box-container">
   <?php
      $where_conditions = ["p.status = 'pending'"];
      $params = [];

      if(isset($_GET['pending_product_name']) && !empty($_GET['pending_product_name'])) {
         $where_conditions[] = "p.name LIKE ?";
         $params[] = "%".$_GET['pending_product_name']."%";
      }

      if(isset($_GET['pending_category']) && !empty($_GET['pending_category'])) {
         $where_conditions[] = "p.category = ?";
         $params[] = $_GET['pending_category'];
      }

      if(isset($_GET['pending_seller']) && !empty($_GET['pending_seller'])) {
         $where_conditions[] = "(u.name LIKE ? OR u.company_name LIKE ?)";
         $params[] = "%".$_GET['pending_seller']."%";
         $params[] = "%".$_GET['pending_seller']."%";
      }

      $where_clause = "WHERE " . implode(" AND ", $where_conditions);

      $pending_products = $conn->prepare("SELECT p.*, u.name AS seller_name, u.company_name 
         FROM `products` p 
         JOIN users u ON p.seller_id = u.id 
         $where_clause 
         ORDER BY p.id DESC");
      $pending_products->execute($params);
      if($pending_products->rowCount() > 0){
         while($prod = $pending_products->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box">
      <div class="price">₹<?= htmlspecialchars($prod['price']); ?>/-</div>
      <img src="uploaded_img/<?= htmlspecialchars($prod['image']); ?>" alt="<?= htmlspecialchars($prod['name']); ?>">
      <div class="name"><?= htmlspecialchars($prod['name']); ?></div>
      <div class="cat">Category: <?= htmlspecialchars($prod['category']); ?></div>
      <div class="details">Details: <?= nl2br(htmlspecialchars($prod['details'])); ?></div>
      <div class="cat">Seller: <strong><?= htmlspecialchars($prod['seller_name']); ?></strong></div>
      <div class="cat">Company: <strong><?= htmlspecialchars($prod['company_name']); ?></strong></div>
      <form action="" method="POST" style="margin-top:1rem;">
         <input type="hidden" name="product_id" value="<?= $prod['id']; ?>">
         <button type="submit" name="approve_product" class="btn" style="background:#27ae60;">Approve</button>
         <button type="submit" name="reject_product" class="delete-btn" style="background:#e74c3c;">Reject</button>
      </form>
   </div>
   <?php
         }
      } else {
         echo '<p class="empty">No pending products for approval.</p>';
      }
   ?>
   </div>
</section>

<script src="js/script.js"></script>

</body>
</html>