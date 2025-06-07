<?php
@include 'config.php';
session_start();
if (!isset($message) || !is_array($message)) {
    $message = [];
}
if(!isset($_SESSION['user_id'])){
   header('location:user_login.php');
   exit();
}
$user_id = $_SESSION['user_id'];
// Handle add address
if(isset($_POST['add_address'])){
   $flat = filter_var($_POST['flat'], FILTER_SANITIZE_STRING);
   $street = filter_var($_POST['street'], FILTER_SANITIZE_STRING);
   $city = filter_var($_POST['city'], FILTER_SANITIZE_STRING);
   $state = filter_var($_POST['state'], FILTER_SANITIZE_STRING);
   $country = filter_var($_POST['country'], FILTER_SANITIZE_STRING);
   $pin_code = filter_var($_POST['pin_code'], FILTER_SANITIZE_STRING);
   $check = $conn->prepare("SELECT * FROM addresses WHERE user_id = ? AND flat = ? AND street = ? AND city = ? AND state = ? AND country = ? AND pin_code = ?");
   $check->execute([$user_id, $flat, $street, $city, $state, $country, $pin_code]);
   if ($check->rowCount() > 0) {
      $message[] = 'This address already exists!';
   } else {
      $stmt = $conn->prepare("INSERT INTO addresses (user_id, flat, street, city, state, country, pin_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $stmt->execute([$user_id, $flat, $street, $city, $state, $country, $pin_code]);
      $message[] = 'Address added successfully!';
   }
}
// Handle delete address
if(isset($_GET['delete'])){
   $id = intval($_GET['delete']);
   $stmt = $conn->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
   $stmt->execute([$id, $user_id]);
   header('Location: user_addresses.php');
   exit();
}
// Fetch addresses
$stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ?");
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!is_array($addresses)) $addresses = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Manage Addresses</title>
   <link rel="stylesheet" href="css/components.css">
</head>
<body>
<?php include 'header.php'; ?>
<section class="form-container">
   <form action="" method="POST">
      <h3>Add New Address</h3>
      <input type="text" name="flat" class="box" placeholder="Flat/House No." required>
      <input type="text" name="street" class="box" placeholder="Street" required>
      <input type="text" name="city" class="box" placeholder="City" required>
      <input type="text" name="state" class="box" placeholder="State" required>
      <input type="text" name="country" class="box" placeholder="Country" required>
      <input type="text" name="pin_code" class="box" placeholder="Pin Code" required>
      <input type="submit" name="add_address" value="Add Address" class="btn">
   </form>
   <?php
   if (!isset($message) || !is_array($message)) $message = [];
   foreach($message as $msg){
      echo '<p class="msg">'.$msg.'</p>';
   }
   ?>
</section>
<section class="display-orders">
   <h3>Your Saved Addresses</h3>
   <?php if(is_array($addresses) && count($addresses) > 0): ?>
      <?php foreach($addresses as $address): ?>
         <div class="box">
            <p><span class="address-label">Address:</span> <?= htmlspecialchars($address['flat']) ?>, <?= htmlspecialchars($address['street']) ?>, <?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['state']) ?>, <?= htmlspecialchars($address['country']) ?> - <?= htmlspecialchars($address['pin_code']) ?></p>
            <a href="user_addresses.php?delete=<?= $address['id'] ?>" class="delete-btn" onclick="return confirm('Delete this address?');">Delete</a>
         </div>
      <?php endforeach; ?>
   <?php else: ?>
      <p class="empty">No addresses saved yet.</p>
   <?php endif; ?>
</section>
<?php include 'footer.php'; ?>
</body>
</html> 