<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

@include 'config.php';

session_start();

$seller_id = $_SESSION['seller_id'] ?? null;

if(!isset($seller_id)){
   header('location:seller_login.php');
   exit;
};

$update_id = $_GET['update'] ?? null;

// Fetch Product Details (Verify Ownership)
$product_data = null;
if($update_id){
    $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ? AND seller_id = ?");
    $select_products->execute([$update_id, $seller_id]);
    if($select_products->rowCount() > 0){
        $product_data = $select_products->fetch(PDO::FETCH_ASSOC);
    } else {
        // Product not found or doesn't belong to seller
        $message[] = 'Product not found or permission denied.';
        // Optionally redirect
        // header('location:seller_products.php');
        // exit;
    }
} else {
    $message[] = 'No product ID specified for update.';
    // Optionally redirect
    // header('location:seller_products.php');
    // exit;
}

// Update Product Logic
if(isset($_POST['update_product'])){

   $pid = filter_input(INPUT_POST, 'pid', FILTER_VALIDATE_INT);
   $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
   $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_FULL_SPECIAL_CHARS); // Consider VALIDATE_FLOAT
   $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
   $details = filter_input(INPUT_POST, 'details', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
   $old_image = filter_input(INPUT_POST, 'old_image', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

   // Basic validation
   $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
   if (!$pid || !$name || $price === false || !$category || !$details || $quantity === false || $quantity < 0) {
        $message[] = 'Please fill in all required fields correctly, including a valid quantity.';
   } else {
        // Update basic product info (verify ownership again in WHERE clause)
        $update_product = $conn->prepare("UPDATE `products` SET name = ?, category = ?, details = ?, price = ?, quantity = ? WHERE id = ? AND seller_id = ?");
        $update_product->execute([$name, $category, $details, $price, $quantity, $pid, $seller_id]);

        if ($update_product->rowCount() > 0) {
             $message[] = 'Product details updated successfully!';
             // Re-fetch data after update to show latest info on the form
             $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ? AND seller_id = ?");
             $select_products->execute([$pid, $seller_id]);
             $product_data = $select_products->fetch(PDO::FETCH_ASSOC);
        } else {
            // Check if it failed because the product doesn't exist/belong to seller, or no changes were made
             $check_exists = $conn->prepare("SELECT id FROM `products` WHERE id = ? AND seller_id = ?");
             $check_exists->execute([$pid, $seller_id]);
             if($check_exists->rowCount() == 0){
                 $message[] = 'Error: Product not found or permission denied.';
             } else {
                $message[] = 'No changes detected in product details.';
             }
        }

        // Handle Image Update
        $image = $_FILES['image']['name'] ?? '';
        $image = filter_var($image, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $image_size = $_FILES['image']['size'] ?? 0;
        $image_tmp_name = $_FILES['image']['tmp_name'] ?? '';
        $image_folder = 'uploaded_img/'.$image;

        if(!empty($image)){
            if($image_size > 2000000){ // 2MB limit
                $message[] = 'Image size is too large (max 2MB)!';
            } else {
                $update_image = $conn->prepare("UPDATE `products` SET image = ? WHERE id = ? AND seller_id = ?");
                $update_image->execute([$image, $pid, $seller_id]);

                if($update_image->rowCount() > 0){
                    if (!empty($image_tmp_name) && move_uploaded_file($image_tmp_name, $image_folder)) {
                         // Delete old image only if new one is successfully uploaded
                         $old_image_path = 'uploaded_img/'.$old_image;
                         if(file_exists($old_image_path) && $old_image != $image){ // Avoid deleting if it's the same file
                             unlink($old_image_path);
                         }
                         $message[] = 'Image updated successfully!';
                         // Update image name in fetched data
                         $product_data['image'] = $image;
                    } else {
                        $message[] = 'Failed to upload new image.';
                    }
                } else {
                     $message[] = 'Failed to update image record in database.';
                }
            }
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
   <title>Update Product</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link (using admin style for now) -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>

<?php include 'seller_header.php'; ?>

<section class="update-product">

   <h1 class="title">update product</h1>

   <?php if ($product_data): ?>
       <form action="" method="post" enctype="multipart/form-data">
          <!-- Hidden fields for product ID and old image name -->
          <input type="hidden" name="pid" value="<?= htmlspecialchars($product_data['id'] ?? ''); ?>">
          <input type="hidden" name="old_image" value="<?= htmlspecialchars($product_data['image'] ?? ''); ?>">

          <?php
             // Display current image
             $current_image_path = 'uploaded_img/' . htmlspecialchars($product_data['image'] ?? '');
             $display_image = file_exists($current_image_path) ? $current_image_path : 'images/default-product.png'; // Default if missing
          ?>
          <img src="<?= $display_image; ?>" alt="Current product image">

          <!-- Form fields pre-filled with current data -->
          <label>Product Name:</label>
          <input type="text" name="name" placeholder="enter product name" required class="box" value="<?= htmlspecialchars($product_data['name'] ?? ''); ?>">

          <label>Price:</lab
          el>
          <input type="number" name="price" min="0" step="0.01" placeholder="enter product price" required class="box" value="<?= htmlspecialchars($product_data['price'] ?? ''); ?>">

          <label>Category:</label>
          <select name="category" class="box" required>
             <!-- Make the current category selected -->
             <option value="vegetables" <?= ($product_data['category'] == 'vegetables') ? 'selected' : ''; ?>>vegetables</option>
             <option value="fruits" <?= ($product_data['category'] == 'fruits') ? 'selected' : ''; ?>>fruits</option>
             <option value="meat" <?= ($product_data['category'] == 'meat') ? 'selected' : ''; ?>>meat</option>
             <option value="fish" <?= ($product_data['category'] == 'fish') ? 'selected' : ''; ?>>fish</option>
             <!-- Add other categories if needed, ensuring the current one is selected -->
          </select>

          <label>Details:</label>
          <textarea name="details" required placeholder="enter product details" class="box" cols="30" rows="10"><?= htmlspecialchars($product_data['details'] ?? ''); ?></textarea>

          <label>Quantity in Stock:</label>
          <input type="number" name="quantity" min="0" required class="box" value="<?= htmlspecialchars($product_data['quantity'] ?? ''); ?>">

          <label>Update Image (optional):</label>
          <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png">

          <div class="flex-btn">
             <input type="submit" class="btn" value="update product" name="update_product">
             <a href="seller_products.php" class="option-btn">go back</a>
          </div>
       </form>
   <?php else: ?>
       <?php
          // Display message if product wasn't loaded (already set above)
          if(isset($message)){
             foreach($message as $msg){
                echo '<div class="message"><span>'.$msg.'</span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
             }
          }
       ?>
       <p class="empty">Could not load product details. <a href="seller_products.php" class="option-btn">Go back</a></p>
   <?php endif; ?>

</section>

<script src="js/admin_script.js"></script> <!-- Using admin script for now -->

</body>
</html>
