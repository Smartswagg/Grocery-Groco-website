<?php

@include 'config.php';

session_start();

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = 'guest_' . session_id();
}
// No forced login redirect

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>about</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<section class="about">

   <div class="row">

      <div class="box">
         <img src="images/about-img-1.png" alt="">
         <h3>why choose us?</h3>
         <p>Every product meets strict USDA Organic (or equivalent) standards—no synthetic pesticides, GMOs, antibiotics, or artificial additives.</p>
         <a href="contact.php" class="btn">contact us</a>
      </div>

      <div class="box">
         <img src="images/about-img-2.png" alt="">
         <h3>what we provide?</h3>
         <p>From crisp apples to heirloom tomatoes, our produce is hand-picked at peak ripeness, free of synthetic pesticides and GMOs.</p>
         <a href="shop.php" class="btn">our shop</a>
      </div>

   </div>

</section>

<section class="reviews">

   <h1 class="title">clients reivews</h1>

   <div class="box-container">

      <div class="box">
         <img src="images/pic-1.png" alt="">
         <p>“Switching to organic groceries here has been life-changing for my family. My kids used to have constant allergies, but since we started buying their organic fruits and snacks, their symptoms have improved dramatically. The subscription box saves me hours each week—everything arrives fresh and ready to go. Worth every penny!”</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>john deo</h3>
      </div>

      <div class="box">
         <img src="images/pic-2.png" alt="">
         <p>“As someone who tracks every macro, I’m blown away by the quality of their organic meats and plant-based proteins. The grass-fed beef tastes unreal, and I’ve noticed better muscle recovery since switching. Plus, their carbon-neutral delivery aligns with my values. 10/10 recommend!”</p>

</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>john deo</h3>
      </div>

      <div class="box">
         <img src="images/pic-3.png" alt="">
         <p>“We’ve struggled with high blood pressure for years, but their organic meal kits helped us eat cleaner without the hassle. The portions are perfect, and the flavors are so vibrant—no more bland ‘health food.’ Carlos even loves the kale salads now! Thank you for making aging healthier feel delicious.”</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>john deo</h3>
      </div>

      <div class="box">
         <img src="images/pic-4.png" alt="">
         <p>“I’m on a tight budget, but their ‘ugly produce’ discount program lets me afford organic veggies while reducing waste. The seasonal boxes are packed with quirky, tasty stuff I’d never pick myself. Plus, their compostable packaging? Chef’s kiss. 🌱”</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>john deo</h3>
      </div>

      <div class="box">
         <img src="images/pic-5.png" alt="">
         <p>“As a professional chef, I’m picky about ingredients. Their organic herbs and heirloom produce are next-level—my followers rave about the colors and flavors in my dishes. Sourcing from them supports small farms, which I love highlighting in my content. A win for taste AND ethics!”

</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>john deo</h3>
      </div>

      <div class="box">
         <img src="images/pic-6.png" alt="">
         <p>“I thought organic = expensive until I found their frozen organic berries and bulk grains. Now I eat cleaner without breaking the bank. The team even helped me customize a budget-friendly plan. The peanut butter oats recipe they shared? My weekday breakfast staple!”

</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>john deo</h3>
      </div>

   </div>

</section>









<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>