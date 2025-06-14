<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php';
session_start();

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = md5($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = md5($_POST['cpass']);
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);
   $user_type = isset($_POST['user_type']) ? $_POST['user_type'] : 'user';
   $company_name = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;

   $select = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
   $select->execute([$email]);

   if($select->rowCount() > 0){
      $message[] = 'user email already exist!';
   }else{
      if($pass != $cpass){
         $message[] = 'confirm password not matched!';
      }else{
         if($user_type === 'seller' && empty($company_name)){
            $message[] = 'Company name is required for sellers!';
         } else {
            if($user_type === 'seller'){
               $insert = $conn->prepare("INSERT INTO `users`(name, email, password, image, user_type, company_name) VALUES(?,?,?,?,?,?)");
               $insert->execute([$name, $email, $pass, $image, $user_type, $company_name]);
            } else {
               $insert = $conn->prepare("INSERT INTO `users`(name, email, password, image, user_type) VALUES(?,?,?,?,?)");
               $insert->execute([$name, $email, $pass, $image, $user_type]);
            }

            if($insert){
               if($image_size > 2000000){
                  $message[] = 'image size is too large!';
               }else{
                  move_uploaded_file($image_tmp_name, $image_folder);
                  $message[] = 'registered successfully!';
                  // Set session and redirect based on user_type
                  $last_id = $conn->lastInsertId();
                  if($user_type === 'seller'){
                     $_SESSION['seller_id'] = $last_id;
                     header('Location: seller_page.php');
                     exit;
                  } else {
                     $_SESSION['user_id'] = $last_id;
                     header('Location: index.php');
                     exit;
                  }
               }
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
   <title>register</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/components.css">

</head>
<body>

<?php

if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}

?>
   
<section class="form-container">

   <form action="" enctype="multipart/form-data" method="POST">
      <h3>register now</h3>
      <input type="text" name="name" class="box" placeholder="enter your name" required>
      <input type="email" name="email" class="box" placeholder="enter your email" required>
      <input type="password" name="pass" class="box" placeholder="enter your password" required>
      <input type="password" name="cpass" class="box" placeholder="confirm your password" required>
      <input type="file" name="image" class="box" required accept="image/jpg, image/jpeg, image/png">
      <select name="user_type" class="box" id="user_type_select" required onchange="document.getElementById('company_name_field').style.display = this.value === 'seller' ? 'block' : 'none';">
         <option value="user">Customer</option>
         <option value="seller">Seller</option>
      </select>
      <input type="text" name="company_name" class="box" id="company_name_field" placeholder="Enter your company name" style="display:none;">
      <input type="submit" value="register now" class="btn" name="submit">
      <p>already have an account? <a href="user_login.php">login now</a></p>
   </form>

</section>


</body>
</html>