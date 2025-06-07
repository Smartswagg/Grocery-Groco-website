<?php
@include 'config.php';
session_start();
if(isset($_POST['submit'])){
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = md5($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $sql = "SELECT * FROM `users` WHERE email = ? AND password = ? AND user_type = 'admin'";
   $stmt = $conn->prepare($sql);
   $stmt->execute([$email, $pass]);
   $rowCount = $stmt->rowCount();  
   $row = $stmt->fetch(PDO::FETCH_ASSOC);
   if($rowCount > 0){
      $_SESSION['admin_id'] = $row['id'];
      header('location:admin_page.php');
   }else{
      $login_error = true;
   }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Adminatration Login</title>
   <link rel="stylesheet" href="css/components.css">
</head>
<body>
<?php if (!empty($login_error)): ?>
<script>
   alert("Wrong email address or password. Please try again.");
</script>
<?php endif; ?>
   <section class="form-container">
   <form action="" method="post">
      <h3>Admin Login</h3>
      <input type="email" name="email" class="box" required placeholder="enter your email">
      <input type="password" name="pass" class="box" required placeholder="enter your password">
      <input type="submit" name="submit" value="Login Now" class="form-btn">
   </form>
</section>
</body>
</html>
