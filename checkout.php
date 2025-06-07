<?php

@include 'config.php';
@include 'payment_handler.php';

session_start();

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
} else {
    // If not logged in, redirect to user login page for checkout
    header('Location: user_login.php');
    exit;
}


if(isset($_POST['order'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $method = $_POST['method'];
   $method = filter_var($method, FILTER_SANITIZE_STRING);
   $address = 'flat no. '. $_POST['flat'] .' '. $_POST['street'] .' '. $_POST['city'] .' '. $_POST['state'] .' '. $_POST['country'] .' - '. $_POST['pin_code'];
   $address = filter_var($address, FILTER_SANITIZE_STRING);
   $placed_on = date('Y-m-d H:i:s');

   // Group cart items by seller_id
   $cart_query = $conn->prepare("SELECT c.*, p.seller_id FROM `cart` c JOIN `products` p ON c.pid = p.id WHERE c.user_id = ?");
   $cart_query->execute([$user_id]);
   $seller_cart = [];
   $cart_total = 0;
   while($cart_item = $cart_query->fetch(PDO::FETCH_ASSOC)){
      $seller_id = $cart_item['seller_id'];
      if (!isset($seller_cart[$seller_id])) {
        $seller_cart[$seller_id] = [
          'products' => [],
          'total' => 0
        ];
      }
      $seller_cart[$seller_id]['products'][] = $cart_item['name'].' ( '.$cart_item['quantity'].' )';
      $sub_total = ($cart_item['price'] * $cart_item['quantity']);
      $seller_cart[$seller_id]['total'] += $sub_total;
      $cart_total += $sub_total;
   }

   // Debit card validation
   if ($method === 'debitcard') {
       $card_number = $_POST['card_number'] ?? '';
       $expiry = $_POST['expiry'] ?? '';
       $cvv = $_POST['cvv'] ?? '';
       $bank_name = $_POST['bank_name'] ?? '';
       if ($bank_name === 'Other') {
           $bank_name = $_POST['other_bank_name'] ?? '';
       }
       // Remove spaces from card number
       $card_number = str_replace(' ', '', $card_number);

       // Basic validation
       if (!preg_match('/^[0-9]{16}$/', $card_number)) {
           $message[] = 'Invalid card number. Must be 16 digits.';
       }
       if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiry)) {
           $message[] = 'Invalid expiry date. Use MM/YY format.';
       }
       if (!preg_match('/^[0-9]{3,4}$/', $cvv)) {
           $message[] = 'Invalid CVV. Must be 3 or 4 digits.';
       }
   } else {
       $bank_name = NULL;
   }

   if($cart_total == 0){
      $message[] = 'your cart is empty';
   } elseif (empty($message)) {
      $order_placed = false;
      foreach ($seller_cart as $seller_id => $data) {
        $total_products = implode(', ', $data['products']);
        $total_price = $data['total'];
        // Check for duplicate order for this seller
        $order_query = $conn->prepare("SELECT * FROM `orders` WHERE name = ? AND number = ? AND email = ? AND method = ? AND address = ? AND total_products = ? AND total_price = ? AND seller_id = ? AND bank_name <=> ?");
        $order_query->execute([$name, $number, $email, $method, $address, $total_products, $total_price, $seller_id, $bank_name]);
        if($order_query->rowCount() > 0){
          $message[] = 'order for a seller placed already!';
        }else{
          $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, seller_id, name, number, email, method, address, total_products, total_price, placed_on, status, payment_status, bank_name) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)");
          $insert_order->execute([$user_id, $seller_id, $name, $number, $email, $method, $address, $total_products, $total_price, $placed_on, 'pending', 'pending', $bank_name]);
          $order_id = $conn->lastInsertId();
          $order_placed = true;
        }
      }
      // Reduce product stock for each item in the cart
      $cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $cart_items->execute([$user_id]);
      while($item = $cart_items->fetch(PDO::FETCH_ASSOC)){
        $update_stock = $conn->prepare("UPDATE `products` SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
        $update_stock->execute([$item['quantity'], $item['pid'], $item['quantity']]);
      }
      // Remove all cart items for this user after placing orders
      $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
      $delete_cart->execute([$user_id]);
      if ($order_placed) {
        $message[] = 'order placed successfully!';
      }
   }

}

// Fetch saved addresses for the user
$address_stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ?");
$address_stmt->execute([$user_id]);
$saved_addresses = $address_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's profile data (name, email, whatsapp_number)
$user_stmt = $conn->prepare("SELECT name, email, whatsapp_number FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);
$name = $user_data['name'] ?? '';
$email = $user_data['email'] ?? '';
$number = $user_data['whatsapp_number'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>checkout</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<section class="display-orders">

   <?php
      $cart_grand_total = 0;
      $select_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart_items->execute([$user_id]);
      if($select_cart_items->rowCount() > 0){
         while($fetch_cart_items = $select_cart_items->fetch(PDO::FETCH_ASSOC)){
            $cart_total_price = ($fetch_cart_items['price'] * $fetch_cart_items['quantity']);
            $cart_grand_total += $cart_total_price;
   ?>
   <p> <?= $fetch_cart_items['name']; ?> <span>(<?= '₹'.$fetch_cart_items['price'].'/- x '. $fetch_cart_items['quantity']; ?>)</span> </p>
   <?php
    }
   }else{
      echo '<p class="empty">your cart is empty!</p>';
   }
   ?>
   <div class="grand-total">grand total : <span>₹<?= $cart_grand_total; ?>/-</span></div>
</section>

<section class="checkout-orders">

   <form action="" method="POST" id="checkoutForm">

      <h3>place your order</h3>

      <div class="flex">
         <div class="inputBox">
            <span>your name :</span>
            <input type="text" name="name" placeholder="enter your name" class="box" required value="<?= htmlspecialchars($name) ?>">
         </div>
         <div class="inputBox">
            <span>your number :</span>
            <input type="number" name="number" placeholder="enter your number" class="box" required value="<?= htmlspecialchars($number) ?>">
         </div>
         <div class="inputBox">
            <span>your email :</span>
            <input type="email" name="email" placeholder="enter your email" class="box" required value="<?= htmlspecialchars($email) ?>">
         </div>
         <div class="inputBox">
            <span>payment method :</span>
            <select name="method" class="box" id="paymentMethodSelect" required>
               <option value="cash on delivery">cash on delivery</option>
               <option value="debitcard">Debit Card</option>
            </select>
         </div>
         <div class="inputBox" style="width:100%;">
            <span>Choose a saved address:</span>
            <select id="savedAddressSelect" class="box">
               <option value="">-- Select a saved address --</option>
               <?php foreach($saved_addresses as $addr): ?>
                  <option value='<?= json_encode($addr) ?>'><?= htmlspecialchars($addr['flat']) ?>, <?= htmlspecialchars($addr['street']) ?>, <?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['state']) ?>, <?= htmlspecialchars($addr['country']) ?> - <?= htmlspecialchars($addr['pin_code']) ?></option>
               <?php endforeach; ?>
            </select>
         </div>
         <div class="inputBox">
            <span>address line 01 :</span>
            <input type="text" name="flat" id="flat" placeholder="e.g. flat number" class="box" required>
         </div>
         <div class="inputBox">
            <span>address line 02 :</span>
            <input type="text" name="street" id="street" placeholder="e.g. street name" class="box" required>
         </div>
         <div class="inputBox">
            <span>city :</span>
            <input type="text" name="city" id="city" placeholder="e.g. mumbai" class="box" required>
         </div>
         <div class="inputBox">
            <span>state :</span>
            <input type="text" name="state" id="state" placeholder="e.g. maharashtra" class="box" required>
         </div>
         <div class="inputBox">
            <span>country :</span>
            <input type="text" name="country" id="country" placeholder="e.g. India" class="box" required>
         </div>
         <div class="inputBox">
            <span>pin code :</span>
            <input type="number" min="0" name="pin_code" id="pin_code" placeholder="e.g. 123456" class="box" required>
         </div>
      </div>

      <!-- Insert debit card form fields here, hidden by default -->
      <div id="debitCardFields" style="display:none; margin-top:1rem;">
         <div class="inputBox">
            <span>Card Number :</span>
            <input type="text" name="card_number" id="card_number" maxlength="19" placeholder="1234 5678 9012 3456" class="box">
         </div>
         <div class="inputBox">
            <span>Expiry Date :</span>
            <input type="text" name="expiry" id="expiry" maxlength="5" placeholder="MM/YY" class="box">
         </div>
         <div class="inputBox">
            <span>CVV :</span>
            <input type="password" name="cvv" id="cvv" maxlength="4" placeholder="CVV" class="box">
         </div>
      </div>

      <!-- Insert after debit card fields -->
      <div id="bankNameFields" style="display:none; margin-top:1rem;">
         <div class="inputBox">
            <span>Bank Name :</span>
            <select name="bank_name" id="bank_name" class="box">
               <option value="">-- Select Bank --</option>
               <option value="HDFC Bank">HDFC Bank</option>
               <option value="SBI">SBI</option>
               <option value="ICICI Bank">ICICI Bank</option>
               <option value="Axis Bank">Axis Bank</option>
               <option value="Kotak Bank">Kotak Bank</option>
               <option value="Canara Bank">Canara Bank</option>
               <option value="PNB">PNB</option>
               <option value="IDFC First Bank">IDFC First Bank</option>
               <option value="Bank of Baroda">Bank of Baroda</option>
               <option value="Union Bank">Union Bank</option>
               <option value="Yes Bank">Yes Bank</option>
               <option value="IndusInd Bank">IndusInd Bank</option>
               <option value="Other">Other</option>
            </select>
         </div>
         <div class="inputBox" id="otherBankBox" style="display:none;">
            <span>Enter Bank Name :</span>
            <input type="text" name="other_bank_name" id="other_bank_name" class="box" placeholder="Enter your bank name">
         </div>
      </div>

      <input type="submit" name="order" class="btn <?= ($cart_grand_total > 1)?'':'disabled'; ?>" value="place order">

   </form>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>
<script>
// Show/hide debit card fields and bank name fields based on payment method
const paymentSelect = document.getElementById('paymentMethodSelect');
const debitCardFields = document.getElementById('debitCardFields');
const bankNameFields = document.getElementById('bankNameFields');
const bankNameSelect = document.getElementById('bank_name');
const otherBankBox = document.getElementById('otherBankBox');
const otherBankInput = document.getElementById('other_bank_name');

paymentSelect.addEventListener('change', function() {
  if (this.value === 'debitcard') {
    debitCardFields.style.display = 'block';
    bankNameFields.style.display = 'block';
    document.getElementById('card_number').required = true;
    document.getElementById('expiry').required = true;
    document.getElementById('cvv').required = true;
    bankNameSelect.required = true;
  } else {
    debitCardFields.style.display = 'none';
    bankNameFields.style.display = 'none';
    document.getElementById('card_number').required = false;
    document.getElementById('expiry').required = false;
    document.getElementById('cvv').required = false;
    bankNameSelect.required = false;
    otherBankInput.required = false;
    otherBankBox.style.display = 'none';
  }
});

bankNameSelect.addEventListener('change', function() {
  if (this.value === 'Other') {
    otherBankBox.style.display = 'block';
    otherBankInput.required = true;
  } else {
    otherBankBox.style.display = 'none';
    otherBankInput.required = false;
  }
});

// On page load, trigger change event to set correct state
paymentSelect.dispatchEvent(new Event('change'));

// Saved address autofill (unchanged)
document.getElementById('savedAddressSelect').addEventListener('change', function() {
   var val = this.value;
   if(val) {
      var addr = JSON.parse(val);
      document.getElementById('flat').value = addr.flat;
      document.getElementById('street').value = addr.street;
      document.getElementById('city').value = addr.city;
      document.getElementById('state').value = addr.state;
      document.getElementById('country').value = addr.country;
      document.getElementById('pin_code').value = addr.pin_code;
   } else {
      document.getElementById('flat').value = '';
      document.getElementById('street').value = '';
      document.getElementById('city').value = '';
      document.getElementById('state').value = '';
      document.getElementById('country').value = '';
      document.getElementById('pin_code').value = '';
   }
});
</script>

</body>
</html>