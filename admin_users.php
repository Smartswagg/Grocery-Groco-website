<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
};

if(isset($_GET['delete'])){

   $delete_id = $_GET['delete'];
   $delete_users = $conn->prepare("DELETE FROM `users` WHERE id = ?");
   $delete_users->execute([$delete_id]);
   header('location:admin_users.php');

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>users</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php include 'admin_header.php'; ?>

<?php
$type = $_GET['type'] ?? '';
if ($type === 'user') {
    $page_title = 'Customers accounts';
    $select_users = $conn->prepare("SELECT * FROM `users` WHERE user_type = 'user'");
    $select_users->execute();
} elseif ($type === 'seller') {
    $page_title = 'Seller Accounts';
    $select_users = $conn->prepare("SELECT * FROM `users` WHERE user_type = 'seller'");
    $select_users->execute();
} else {
    $page_title = 'All Accounts';
    $select_users = $conn->prepare("SELECT * FROM `users`");
    $select_users->execute();
}
?>

<section class="user-accounts">

   <h1 class="title"><?= htmlspecialchars($page_title) ?></h1>

   <div class="box-container">

      <?php
         while($fetch_users = $select_users->fetch(PDO::FETCH_ASSOC)){
      ?>
      <div class="box" style="<?php if($fetch_users['id'] == $admin_id){ echo 'display:none'; }; ?>">
         <img src="uploaded_img/<?= $fetch_users['image']; ?>" alt="">
         <p> customer id : <span><?= $fetch_users['id']; ?></span></p>
         <p> customer name : <span><?= $fetch_users['name']; ?></span></p>
         <p> email : <span><?= $fetch_users['email']; ?></span></p>
         <a href="admin_users.php?delete=<?= $fetch_users['id']; ?>" onclick="return confirm('delete this customer?');" class="delete-btn">delete</a>
      </div>
      <?php
      }
      ?>
   </div>

</section>













<script src="js/script.js"></script>

</body>
</html>