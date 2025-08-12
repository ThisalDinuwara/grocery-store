<?php
@include 'config.php';

$user_id = $_SESSION['user_id'] ?? null;
if(!isset($user_id)){
   header('location:login.php');
   exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8" />
   <meta http-equiv="X-UA-Compatible" content="IE=edge" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <title>About - Kandu Pinnawala</title>

   <!-- Tailwind CDN -->
   <script src="https://cdn.tailwindcss.com"></script>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">

   <!-- Devil Mask Animation Styles (unchanged) -->
   <style>
      .floating-bg {
         position: absolute; top: 0; left: 0; width: 100%; height: 100%;
         z-index: 0; overflow: hidden; pointer-events: none;
      }
      .floating-mask { position: absolute; width: 100px; opacity: .5; animation: floatRotate 20s ease-in-out infinite; }

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
      .floating-mask:nth-child(10){ top: 24%; left: 82%; animation-delay: .5s; width: 75px; }

      /* Row 3 - Middle */
      .floating-mask:nth-child(11){ top: 38%; left: 10%; animation-delay: 2.5s; width: 90px; }
      .floating-mask:nth-child(12){ top: 35%; left: 30%; animation-delay: 4.5s; width: 85px; }
      .floating-mask:nth-child(13){ top: 40%; left: 50%; animation-delay: 1.2s; width: 100px; }
      .floating-mask:nth-child(14){ top: 37%; left: 70%; animation-delay: 5.5s; width: 80px; }
      .floating-mask:nth-child(15){ top: 42%; left: 88%; animation-delay: 3.2s; width: 95px; }

      /* Row 4 - Lower Middle */
      .floating-mask:nth-child(16){ top: 58%; left: 3%; animation-delay: 6.5s; width: 85px; }
      .floating-mask:nth-child(17){ top: 55%; left: 25%; animation-delay: 2.8s; width: 90px; }
      .floating-mask:nth-child(18){ top: 60%; left: 45%; animation-delay: 4.8s; width: 75px; }
      .floating-mask:nth-child(19){ top: 57%; left: 67%; animation-delay: 1.8s; width: 95px; }
      .floating-mask:nth-child(20){ top: 62%; left: 85%; animation-delay: 5.8s; width: 80px; }

      /* Row 5 - Bottom */
      .floating-mask:nth-child(21){ top: 78%; left: 12%; animation-delay: 3.8s; width: 90px; }
      .floating-mask:nth-child(22){ top: 75%; left: 32%; animation-delay: 6.2s; width: 85px; }
      .floating-mask:nth-child(23){ top: 80%; left: 52%; animation-delay: 2.2s; width: 95px; }
      .floating-mask:nth-child(24){ top: 77%; left: 72%; animation-delay: 4.2s; width: 80px; }
      .floating-mask:nth-child(25){ top: 82%; left: 90%; animation-delay: 1.5s; width: 75px; }

      @keyframes floatRotate {
         0%{transform:translateY(0) rotate(0)}
         50%{transform:translateY(-30px) rotate(180deg)}
         100%{transform:translateY(0) rotate(360deg)}
      }
   </style>
</head>
<body class="bg-gray-50">

<!-- ======================== -->
<!-- ABOUT SECTION (modern glass, header pops out) -->
<!-- ======================== -->
<div class="relative w-full min-h-[100vh] overflow-hidden">
   <!-- Floating devil mask background -->
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

   <!-- Soft readability scrim behind header -->
   <div class="absolute inset-0 z-[5] bg-gradient-to-b from-white/85 via-white/70 to-white/20"></div>

   <!-- ABOUT content -->
   <section class="relative z-10 py-20">
      <div class="container mx-auto px-6 lg:px-12 w-full">

         <!-- Pop-out header -->
         <div class="text-center mb-12 relative z-10">
            <!-- Accent pill -->
            <div class="inline-flex items-center gap-2 mb-5 px-4 py-1.5 rounded-full bg-white/85 backdrop-blur border border-white/60 shadow">
               <span class="w-2 h-2 rounded-full bg-gradient-to-r from-orange-500 to-red-600"></span>
               <span class="text-sm font-semibold text-gray-700">Handcrafted in Sri Lanka</span>
            </div>

            <!-- Title pill -->
            <h2 class="text-4xl lg:text-6xl font-extrabold tracking-tight leading-tight">
               <span class="inline-block px-6 py-3 rounded-2xl bg-white/90 backdrop-blur-xl border border-white/60 shadow-2xl shadow-orange-500/10">
                 About Kandu Pinnawala
               </span>
            </h2>

            <!-- Gradient underline -->
            <span class="block h-1 w-28 mx-auto mt-4 rounded-full bg-gradient-to-r from-orange-500 to-red-600"></span>

            <p class="mt-5 text-xl text-gray-700/90 max-w-3xl mx-auto">
               Craft, culture, and passion — handmade in Sri Lanka
            </p>
         </div>

         <!-- Glass cards -->
         <div class="grid md:grid-cols-2 gap-8">
            <!-- Card 1 -->
            <div class="group rounded-3xl overflow-hidden shadow-lg border border-white/40 bg-white/60 backdrop-blur-xl transition hover:shadow-2xl">
               <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-orange-500 to-red-600"></div>
               <div class="aspect-[4/3] bg-gray-50/60 overflow-hidden">
                  <img src="images/about.png" alt="Why choose us"
                       class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105"
                       onerror="this.src='images/about.png'">
               </div>
               <div class="p-6">
                  <h3 class="text-xl font-bold text-gray-900 mb-3">Why choose us?</h3>
                  <p class="text-gray-700/90">Direct artisan partnerships, fair pricing, authentic designs, and premium materials.</p>
                  <ul class="mt-5 grid sm:grid-cols-2 gap-3 text-gray-700/90">
                     <li class="flex items-center gap-3"><i class="fas fa-check text-green-600"></i> Authentic craftsmanship</li>
                     <li class="flex items-center gap-3"><i class="fas fa-check text-green-600"></i> Fair to artisans</li>
                     <li class="flex items-center gap-3"><i class="fas fa-check text-green-600"></i> Premium materials</li>
                     <li class="flex items-center gap-3"><i class="fas fa-check text-green-600"></i> Unique designs</li>
                  </ul>
                  <a href="contact.php"
                     class="mt-6 inline-flex items-center bg-gradient-to-r from-orange-500 to-red-600 text-white px-6 py-3 rounded-xl font-semibold hover:shadow-xl hover:shadow-orange-500/25 transition">
                     <i class="fas fa-envelope mr-2"></i> Contact Us
                  </a>
               </div>
            </div>

            <!-- Card 2 -->
            <div class="group rounded-3xl overflow-hidden shadow-lg border border-white/40 bg-white/60 backdrop-blur-xl transition hover:shadow-2xl">
               <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-orange-500 to-red-600"></div>
               <div class="aspect-[4/3] bg-gray-50/60 overflow-hidden">
                  <img src="images/cart.png" alt="What we provide"
                       class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105"
                       onerror="this.src='images/cart.png'">
               </div>
               <div class="p-6">
                  <h3 class="text-xl font-bold text-gray-900 mb-3">What we provide?</h3>
                  <p class="text-gray-700/90">Curated masks, wood carvings, batik textiles & custom orders — every piece tells a story.</p>
                  <ul class="mt-5 grid sm:grid-cols-2 gap-3 text-gray-700/90">
                     <li class="flex items-center gap-3"><i class="fas fa-check text-green-600"></i> Devil masks</li>
                     <li class="flex items-center gap-3"><i class="fas fa-check text-green-600"></i> Wood carvings</li>
                     <li class="flex items-center gap-3"><i class="fas fa-check text-green-600"></i> Batik textiles</li>
                     <li class="flex items-center gap-3"><i class="fas fa-check text-green-600"></i> Custom pieces</li>
                  </ul>
                  <a href="shop.php"
                     class="mt-6 inline-flex items-center bg-gradient-to-r from-orange-500 to-red-600 text-white px-6 py-3 rounded-xl font-semibold hover:shadow-xl hover:shadow-orange-500/25 transition">
                     <i class="fas fa-store mr-2"></i> Our Shop
                  </a>
               </div>
            </div>
         </div>
      </div>
   </section>
</div>

<!-- ======================== -->
<!-- REVIEWS (smaller avatars, matching cards) -->
<!-- ======================== -->
<section id="reviews" class="py-20 bg-white">
   <div class="container mx-auto px-6 lg:px-12">
      <div class="text-center mb-16">
         <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Client Reviews</h2>
         <p class="text-xl text-gray-600 max-w-3xl mx-auto">What our customers say about Kandu Pinnawala</p>
      </div>

      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
         <?php for($i=1; $i<=6; $i++): ?>
         <div class="bg-white rounded-3xl overflow-hidden shadow-lg border border-gray-100 p-6">
            <div class="flex items-center gap-4 mb-4">
               <!-- Smaller circular avatar -->
               <img src="images/pic-<?= $i; ?>.png" alt="Reviewer"
                    class="w-12 h-12 rounded-full object-cover border border-gray-200"
                    onerror="this.src='images/pic-1.png'">
               <div>
                  <h3 class="text-base font-bold text-gray-900 leading-tight">John Deo</h3>
                  <div class="text-yellow-500 text-xs">
                     <i class="fas fa-star mr-0.5"></i>
                     <i class="fas fa-star mr-0.5"></i>
                     <i class="fas fa-star mr-0.5"></i>
                     <i class="fas fa-star mr-0.5"></i>
                     <i class="fas fa-star-half-alt"></i>
                  </div>
               </div>
            </div>

            <p class="text-gray-600 leading-relaxed">
               “Lorem ipsum dolor sit amet, consectetur adipisicing elit. Eos minima eveniet dolorum possimus.”
            </p>
         </div>
         <?php endfor; ?>
      </div>
   </div>
</section>

<script src="js/script.js"></script>
</body>
</html>
