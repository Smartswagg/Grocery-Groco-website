<?php
@include 'config.php';
session_start();

if(!isset($_SESSION['seller_id'])){
   header('location:seller_login.php');
   exit();
}

$user_id = $_SESSION['seller_id'];

if(isset($_POST['send'])){
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $msg = $_POST['msg'];
   $msg = filter_var($msg, FILTER_SANITIZE_STRING);

   $select_message = $conn->prepare("SELECT * FROM `message` WHERE name = ? AND email = ? AND number = ? AND message = ? AND user_id = ?");
   $select_message->execute([$name, $email, $number, $msg, $user_id]);

   if($select_message->rowCount() > 0){
      $message[] = 'You have already sent this message!';
   }else{
      $insert_message = $conn->prepare("INSERT INTO `message`(user_id, name, email, number, message) VALUES(?,?,?,?,?)");
      $insert_message->execute([$user_id, $name, $email, $number, $msg]);
      $message[] = 'Message sent to admin successfully!';
   }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Contact Admin</title>
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'seller_header.php'; ?>
<section class="contact">
   <h1 class="title">Contact Admin</h1>
   <form action="" method="POST">
      <input type="text" name="name" class="box" required placeholder="enter your name">
      <input type="email" name="email" class="box" required placeholder="enter your email">
      <input type="number" name="number" min="0" class="box" required placeholder="enter your number">
      <textarea name="msg" class="box" required placeholder="enter your message" cols="30" rows="10"></textarea>
      <input type="submit" value="Send Message" class="btn" name="send">
   </form>
   <?php
   if(isset($message)){
      foreach($message as $msg){
         echo '<p class="msg">'.$msg.'</p>';
      }
   }
   ?>
</section>
</body>
</html>
