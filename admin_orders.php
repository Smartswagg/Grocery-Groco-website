<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
};

if(isset($_POST['update_order'])){

   $order_id = $_POST['order_id'];
   $update_status = $_POST['update_status'];
   $update_status = strtolower(trim($update_status));
   if ($update_status === '') {
      $message[] = 'Please select a valid status.';
   } else {
      if ($update_status === 'delivered') {
         $update_orders = $conn->prepare("UPDATE `orders` SET status = ?, status_updated_at = NOW() WHERE id = ?");
         $update_orders->execute([$update_status, $order_id]);
      } else {
         $update_orders = $conn->prepare("UPDATE `orders` SET status = ? WHERE id = ?");
         $update_orders->execute([$update_status, $order_id]);
      }
      $message[] = 'Order status has been updated!';
   }

};

if(isset($_GET['delete'])){

   $delete_id = $_GET['delete'];
   $delete_orders = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
   $delete_orders->execute([$delete_id]);
   header('location:admin_orders.php');

}

$type = $_GET['type'] ?? '';
if ($type === 'completed') {
    $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE status = 'delivered'");
    $select_orders->execute();
    $section_title = 'completed orders';
} elseif ($type === 'pending') {
    $where = [];
    $params = [];
    if (isset($_GET['status']) && $_GET['status'] !== '' && $_GET['status'] !== 'all') {
        $where[] = "status = ?";
        $params[] = $_GET['status'];
    } else {
        $where[] = "status != 'delivered'";
    }
    if (!empty($_GET['product_name'])) {
        $where[] = "total_products LIKE ?";
        $params[] = "%" . $_GET['product_name'] . "%";
    }
    if (!empty($_GET['user_name'])) {
        $where[] = "name LIKE ?";
        $params[] = "%" . $_GET['user_name'] . "%";
    }
    $where_clause = $where ? ("WHERE " . implode(' AND ', $where)) : '';
    $select_orders = $conn->prepare("SELECT * FROM `orders` $where_clause");
    $select_orders->execute($params);
    $section_title = 'pending orders';
} else {
    $where = [];
    $params = [];
    if (!empty($_GET['product_name'])) {
        $where[] = "total_products LIKE ?";
        $params[] = "%" . $_GET['product_name'] . "%";
    }
    if (!empty($_GET['user_name'])) {
        $where[] = "name LIKE ?";
        $params[] = "%" . $_GET['user_name'] . "%";
    }
    if (isset($_GET['status']) && $_GET['status'] !== '' && $_GET['status'] !== 'all') {
        $where[] = "status = ?";
        $params[] = $_GET['status'];
    }
    $where_clause = $where ? ("WHERE " . implode(' AND ', $where)) : '';
    $select_orders = $conn->prepare("SELECT * FROM `orders` $where_clause");
    $select_orders->execute($params);
    $section_title = 'all orders';
}

function update_order_status($conn, $order_id, $new_status) {
   $order_id = mysqli_real_escape_string($conn, $order_id);
   $new_status = mysqli_real_escape_string($conn, $new_status);
   
   // Get current order status
   $current_status_query = "SELECT status FROM orders WHERE id = '$order_id'";
   $current_status_result = mysqli_query($conn, $current_status_query);
   $current_status_row = mysqli_fetch_assoc($current_status_result);
   $current_status = $current_status_row['status'];
   
   // Validate status transition
   if (!is_valid_status_transition($current_status, $new_status)) {
      return false;
   }
   
   // Update order status
   $update_query = "UPDATE orders SET status = '$new_status', status_updated_at = NOW() WHERE id = '$order_id'";
   return mysqli_query($conn, $update_query);
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
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="placed-orders">

   <h1 class="title"><?= ucfirst($section_title) ?></h1>

   <div class="box-container">

      <?php if ($type === 'pending'): ?>
      <form action="" method="GET" class="filter-form" style="margin-bottom:2rem;">
         <input type="hidden" name="type" value="pending">
         <div class="filter-group">
            <input type="text" name="product_name" placeholder="Search by product name" value="<?= isset($_GET['product_name']) ? htmlspecialchars($_GET['product_name']) : '' ?>">
            <input type="text" name="user_name" placeholder="Search by user name" value="<?= isset($_GET['user_name']) ? htmlspecialchars($_GET['user_name']) : '' ?>">
            <select name="status">
               <option value="">All Status (except delivered)</option>
               <option value="pending" <?= (isset($_GET['status']) && $_GET['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
               <option value="processed" <?= (isset($_GET['status']) && $_GET['status'] === 'processed') ? 'selected' : '' ?>>Processed</option>
               <option value="shipped" <?= (isset($_GET['status']) && $_GET['status'] === 'shipped') ? 'selected' : '' ?>>Shipped</option>
               <option value="out for delivery" <?= (isset($_GET['status']) && $_GET['status'] === 'out for delivery') ? 'selected' : '' ?>>Out for Delivery</option>
               <option value="delivered" <?= (isset($_GET['status']) && $_GET['status'] === 'delivered') ? 'selected' : '' ?>>Delivered</option>
               <option value="cancelled" <?= (isset($_GET['status']) && $_GET['status'] === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
            </select>
            <button type="submit" class="btn">Filter</button>
            <?php if(isset($_GET['product_name']) || isset($_GET['user_name']) || isset($_GET['status'])): ?>
               <a href="admin_orders.php?type=pending" class="btn" style="background:#e74c3c;">Clear Filters</a>
            <?php endif; ?>
         </div>
      </form>
      <?php endif; ?>

      <?php if ($type === '' || $type === null): ?>
      <form action="" method="GET" class="filter-form" style="margin-bottom:2rem;">
         <div class="filter-group">
            <input type="text" name="product_name" placeholder="Search by product name" value="<?= isset($_GET['product_name']) ? htmlspecialchars($_GET['product_name']) : '' ?>">
            <input type="text" name="user_name" placeholder="Search by user name" value="<?= isset($_GET['user_name']) ? htmlspecialchars($_GET['user_name']) : '' ?>">
            <select name="status">
               <option value="">All Statuses</option>
               <option value="pending" <?= (isset($_GET['status']) && $_GET['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
               <option value="processed" <?= (isset($_GET['status']) && $_GET['status'] === 'processed') ? 'selected' : '' ?>>Processed</option>
               <option value="shipped" <?= (isset($_GET['status']) && $_GET['status'] === 'shipped') ? 'selected' : '' ?>>Shipped</option>
               <option value="out for delivery" <?= (isset($_GET['status']) && $_GET['status'] === 'out for delivery') ? 'selected' : '' ?>>Out for Delivery</option>
               <option value="delivered" <?= (isset($_GET['status']) && $_GET['status'] === 'delivered') ? 'selected' : '' ?>>Delivered</option>
               <option value="cancelled" <?= (isset($_GET['status']) && $_GET['status'] === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
            </select>
            <button type="submit" class="btn">Filter</button>
            <?php if(isset($_GET['product_name']) || isset($_GET['user_name']) || isset($_GET['status'])): ?>
               <a href="admin_orders.php" class="btn" style="background:#e74c3c;">Clear Filters</a>
            <?php endif; ?>
         </div>
      </form>
      <?php endif; ?>

      <?php
         if($select_orders->rowCount() > 0){
            while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){
               $is_delivered_and_locked = false;
               if ($fetch_orders['status'] === 'delivered' || $fetch_orders['status'] === 'cancelled') {
                  $is_delivered_and_locked = true;
               }
      ?>
      <div class="box">
         <p> user id : <span><?= $fetch_orders['user_id']; ?></span> </p>
         <p> placed on : <span><?= $fetch_orders['placed_on']; ?></span> </p>
         <p> name : <span><?= $fetch_orders['name']; ?></span> </p>
         <p> email : <span><?= $fetch_orders['email']; ?></span> </p>
         <p> number : <span><?= $fetch_orders['number']; ?></span> </p>
         <p> address : <span><?= $fetch_orders['address']; ?></span> </p>
         <p> total products : <span><?= $fetch_orders['total_products']; ?></span> </p>
         <p> total price : <span>₹<?= $fetch_orders['total_price']; ?>/-</span> </p>
         <p> payment method : <span><?= $fetch_orders['method']; ?></span> </p>
         <?php if ($fetch_orders['method'] === 'debitcard' && !empty($fetch_orders['bank_name'])): ?>
         <p> bank name : <span><?= htmlspecialchars($fetch_orders['bank_name']); ?></span> </p>
         <?php endif; ?>
         <form action="" method="POST">
            <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
            <select name="update_status" class="drop-down" required <?= $is_delivered_and_locked ? 'disabled style="background:#eee;color:#aaa;pointer-events:none;"' : '' ?>>
               <option value="" disabled <?= empty($fetch_orders['status']) ? 'selected' : '' ?>>Select status</option>
               <option value="pending" <?= $fetch_orders['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
               <option value="processed" <?= $fetch_orders['status'] == 'processed' ? 'selected' : '' ?>>Processed</option>
               <option value="shipped" <?= $fetch_orders['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
               <option value="out for delivery" <?= $fetch_orders['status'] == 'out for delivery' ? 'selected' : '' ?>>Out for Delivery</option>
               <option value="delivered" <?= $fetch_orders['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
               <option value="cancelled" <?= $fetch_orders['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            <div class="flex-btn">
               <input type="submit" name="update_order" class="option-btn" value="update" <?= $is_delivered_and_locked ? 'disabled style="background:#ccc;color:#888;cursor:not-allowed;"' : '' ?>>
               <a href="admin_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('delete this order?');">delete</a>
            </div>
         </form>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">No orders found for this view.</p>';
      }
      ?>

   </div>

</section>












<script src="js/script.js"></script>

</body>
</html>