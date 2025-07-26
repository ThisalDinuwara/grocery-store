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
         opacity: 0.5;
         animation: floatRotate 20s ease-in-out infinite;
      }

      /* Row 1 - Top */
      .floating-mask:nth-child(1) { top: 2%; left: 8%; animation-delay: 0s; width: 80px; }
      .floating-mask:nth-child(2) { top: 5%; left: 25%; animation-delay: 2s; width: 90px; }
      .floating-mask:nth-child(3) { top: 3%; left: 45%; animation-delay: 4s; width: 85px; }
      .floating-mask:nth-child(4) { top: 7%; left: 65%; animation-delay: 1s; width: 95px; }
      .floating-mask:nth-child(5) { top: 4%; left: 85%; animation-delay: 3s; width: 75px; }

      /* Row 2 - Upper Middle */
      .floating-mask:nth-child(6) { top: 20%; left: 5%; animation-delay: 5s; width: 85px; }
      .floating-mask:nth-child(7) { top: 18%; left: 22%; animation-delay: 1.5s; width: 90px; }
      .floating-mask:nth-child(8) { top: 22%; left: 42%; animation-delay: 3.5s; width: 80px; }
      .floating-mask:nth-child(9) { top: 19%; left: 62%; animation-delay: 6s; width: 95px; }
      .floating-mask:nth-child(10) { top: 24%; left: 82%; animation-delay: 0.5s; width: 75px; }

      /* Row 3 - Middle */
      .floating-mask:nth-child(11) { top: 38%; left: 10%; animation-delay: 2.5s; width: 90px; }
      .floating-mask:nth-child(12) { top: 35%; left: 30%; animation-delay: 4.5s; width: 85px; }
      .floating-mask:nth-child(13) { top: 40%; left: 50%; animation-delay: 1.2s; width: 100px; }
      .floating-mask:nth-child(14) { top: 37%; left: 70%; animation-delay: 5.5s; width: 80px; }
      .floating-mask:nth-child(15) { top: 42%; left: 88%; animation-delay: 3.2s; width: 95px; }

      /* Row 4 - Lower Middle */
      .floating-mask:nth-child(16) { top: 58%; left: 3%; animation-delay: 6.5s; width: 85px; }
      .floating-mask:nth-child(17) { top: 55%; left: 25%; animation-delay: 2.8s; width: 90px; }
      .floating-mask:nth-child(18) { top: 60%; left: 45%; animation-delay: 4.8s; width: 75px; }
      .floating-mask:nth-child(19) { top: 57%; left: 67%; animation-delay: 1.8s; width: 95px; }
      .floating-mask:nth-child(20) { top: 62%; left: 85%; animation-delay: 5.8s; width: 80px; }

      /* Row 5 - Bottom */
      .floating-mask:nth-child(21) { top: 78%; left: 12%; animation-delay: 3.8s; width: 90px; }
      .floating-mask:nth-child(22) { top: 75%; left: 32%; animation-delay: 6.2s; width: 85px; }
      .floating-mask:nth-child(23) { top: 80%; left: 52%; animation-delay: 2.2s; width: 95px; }
      .floating-mask:nth-child(24) { top: 77%; left: 72%; animation-delay: 4.2s; width: 80px; }
      .floating-mask:nth-child(25) { top: 82%; left: 90%; animation-delay: 1.5s; width: 75px; }

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
      <img src="images/mask7.png" class="floating-mask" alt="mask">
      <img src="images/mask8.png" class="floating-mask" alt="mask">
      <img src="images/mask7.png" class="floating-mask" alt="mask">
      <img src="images/mask8.png" class="floating-mask" alt="mask">
      <img src="images/mask7.png" class="floating-mask" alt="mask">
      <img src="images/mask8.png" class="floating-mask" alt="mask">
      <img src="images/mask7.png" class="floating-mask" alt="mask">
       <img src="images/mask8.png" class="floating-mask" alt="mask">
      <img src="images/mask7.png" class="floating-mask" alt="mask">
      <img src="images/mask8.png" class="floating-mask" alt="mask">
      <img src="images/mask7.png" class="floating-mask" alt="mask">
      <img src="images/mask8.png" class="floating-mask" alt="mask">
      <img src="images/mask7.png" class="floating-mask" alt="mask">
      <img src="images/mask8.png" class="floating-mask" alt="mask">
      <img src="images/mask7.png" class="floating-mask" alt="mask">
      <img src="images/mask8.png" class="floating-mask" alt="mask">
      <img src="images/mask7.png" class="floating-mask" alt="mask">
      <img src="images/mask8.png" class="floating-mask" alt="mask">
      <img src="images/mask7.png" class="floating-mask" alt="mask">
      <img src="images/mask8.png" class="floating-mask" alt="mask">
      <img src="images/mask7.png" class="floating-mask" alt="mask">
      <img src="images/mask8.png" class="floating-mask" alt="mask">
      <img src="images/mask7.png" class="floating-mask" alt="mask">
      <img src="images/mask8.png" class="floating-mask" alt="mask">
      <img src="images/mask7.png" class="floating-mask" alt="mask">
      
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
<br>
<br>

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