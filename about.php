<?php

@include 'config.php';

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>about</title>

   <!-- Tailwind CDN -->
   <script src="https://cdn.tailwindcss.com"></script>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">

   <!-- Devil Mask Animation Styles -->
   <style>
      .floating-bg {
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         z-index: 0;
         overflow: hidden;
         pointer-events: none;
      }

      .floating-mask {
         position: absolute;
         width: 100px;
         opacity: 0.12;
         animation: floatRotate 18s ease-in-out infinite;
      }

      .floating-mask:nth-child(1) { top: 10%; left: 5%; animation-delay: 0s; }
      .floating-mask:nth-child(2) { top: 30%; left: 25%; animation-delay: 4s; }
      .floating-mask:nth-child(3) { top: 60%; left: 70%; animation-delay: 2s; }
      .floating-mask:nth-child(4) { top: 80%; left: 45%; animation-delay: 6s; }
      .floating-mask:nth-child(5) { top: 40%; left: 80%; animation-delay: 1s; }

      @keyframes floatRotate {
         0%   { transform: translateY(0px) rotate(0deg); }
         50%  { transform: translateY(-30px) rotate(180deg); }
         100% { transform: translateY(0px) rotate(360deg); }
      }
   </style>
</head>
<body class="bg-gray-100">

<!-- ======================== -->
<!-- WRAPPER FOR ABOUT SECTION -->
<!-- ======================== -->
<div class="relative w-full h-[100vh] overflow-hidden">
   <!-- Floating devil mask background (covers whole visible area) -->
   <div class="floating-bg">
      <img src="images/devil-mask1.png" class="floating-mask" alt="mask">
      <img src="images/devil-mask2.png" class="floating-mask" alt="mask">
      <img src="images/devil-mask1.png" class="floating-mask" alt="mask">
      <img src="images/devil-mask2.png" class="floating-mask" alt="mask">
      <img src="images/devil-mask1.png" class="floating-mask" alt="mask">
   </div>

   <!-- ABOUT CONTENT -->
   <section class="about relative z-10 py-12 px-6 h-full flex items-center">
      <div class="row grid md:grid-cols-2 gap-8 w-full">
         <div class="box bg-white p-6 rounded shadow">
            <img src="images/about.png" alt="" class="mb-4">
            <h3 class="text-xl font-semibold mb-2">Why choose us?</h3>
            <p class="text-gray-700 mb-4">Lorem, ipsum dolor sit amet consectetur adipisicing elit...</p>
            <a href="contact.php" class="btn bg-red-500 text-white px-4 py-2 rounded">Contact Us</a>
         </div>

         <div class="box bg-white p-6 rounded shadow">
            <img src="images/cart.png" alt="" class="mb-4">
            <h3 class="text-xl font-semibold mb-2">What we provide?</h3>
            <p class="text-gray-700 mb-4">Lorem, ipsum dolor sit amet consectetur adipisicing elit...</p>
            <a href="shop.php" class="btn bg-red-500 text-white px-4 py-2 rounded">Our Shop</a>
         </div>
      </div>
   </section>
</div>

<!-- ======================== -->
<!-- CLIENT REVIEWS BELOW (No animation) -->
<!-- ======================== -->
<section class="reviews py-12 px-6 bg-white bg-opacity-90 relative z-10">
   <h1 class="title text-2xl font-bold text-center mb-8">Client Reviews</h1>
   <div class="box-container grid md:grid-cols-3 gap-6">
      <?php for($i=1; $i<=6; $i++): ?>
      <div class="box p-6 rounded shadow bg-gray-50">
         <img src="images/pic-<?php echo $i; ?>.png" alt="" class="mb-4">
         <p class="text-gray-700 mb-3">Lorem ipsum dolor sit amet consectetur adipisicing elit...</p>
         <div class="stars text-yellow-500 mb-2">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3 class="font-semibold">John Deo</h3>
      </div>
      <?php endfor; ?>
   </div>
</section>

<script src="js/script.js"></script>
</body>
</html>
