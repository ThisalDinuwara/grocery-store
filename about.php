<?php 
@include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
   <title>Home - Kandu Pinnawala</title>

   <!-- Tailwind -->
   <script src="https://cdn.tailwindcss.com"></script>
   <script>
      tailwind.config = {
         theme: {
            extend: {
               colors: {
                  primary: '#8B4513',
                  secondary: '#A0522D',
                  accent: '#D2B48C',
                  dark: '#3E2723',
                  darker: '#1B0F0A'
               },
               fontFamily: {
                  'gaming': ['Orbitron', 'monospace']
               }
            }
         }
      }
   </script>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Font overrides -->
   <style>
      body { font-size: 1.25rem; line-height: 1.8; }
      h1 { font-size: 3rem !important; }
      h2 { font-size: 2.75rem !important; }
      h3 { font-size: 2rem !important; }
      p, li, a, button { font-size: 1.25rem !important; }
      #reviews h2 { font-size: 3rem !important; }
   </style>

   <!-- Floating masks -->
   <style>
      .floating-bg { position: absolute; inset:0; width:100%; height:100%; overflow:hidden; z-index:0; pointer-events:none; }
      .floating-mask { position:absolute; opacity:.5; animation: floatRotate 20s ease-in-out infinite; }
      @keyframes floatRotate {
         0%{transform:translateY(0) rotate(0deg)}
         50%{transform:translateY(-30px) rotate(180deg)}
         100%{transform:translateY(0) rotate(360deg)}
      }
   </style>
</head>
<body class="min-h-screen text-white" style="background: linear-gradient(135deg,#1B0F0A,#3E2723,#5D4037);">

<!-- Floating Background -->
<div class="relative w-full min-h-[100vh] overflow-hidden">
   <div class="floating-bg">
      <?php for($m=1;$m<=25;$m++): ?>
         <img src="images/mask<?= $m%2==0?8:7; ?>.png" class="floating-mask w-20" style="top:<?= rand(5,85); ?>%;left:<?= rand(5,90); ?>%;animation-delay:<?= rand(0,6); ?>s;" alt="mask">
      <?php endfor; ?>
   </div>
   <div class="absolute inset-0 z-[5] bg-gradient-to-b from-[rgba(27,15,10,0.85)] via-[rgba(62,39,35,0.7)] to-[rgba(27,15,10,0.2)]"></div>

   <!-- About Section -->
   <section class="relative z-10 py-20">
      <div class="container mx-auto px-6 lg:px-12">
         <div class="text-center mb-12">
            <div class="inline-flex items-center gap-2 mb-5 px-4 py-1.5 rounded-full bg-white/10 border border-white/20 shadow">
               <span class="w-2 h-2 rounded-full bg-gradient-to-r from-primary to-accent"></span>
               <span class="text-sm font-semibold text-gray-200">Handcrafted in Sri Lanka</span>
            </div>
            <h2 class="font-extrabold">
               <span class="px-6 py-3 rounded-2xl bg-white/10 border border-white/20">About Kandu Pinnawala</span>
            </h2>
            <span class="block h-1 w-28 mx-auto mt-4 rounded-full bg-gradient-to-r from-primary to-accent"></span>
            <p class="mt-5 text-xl text-gray-300">Craft, culture, and passion — handmade in Sri Lanka</p>
         </div>

         <div class="grid md:grid-cols-2 gap-8">
            <!-- Why choose us -->
            <div class="group relative rounded-3xl overflow-hidden shadow-lg border border-white/20 bg-white/10 backdrop-blur-xl p-6">
               <img src="images/about.png" alt="Why choose us" class="w-full rounded-lg mb-4 object-contain">
               <h3 class="font-bold mb-3">Why choose us?</h3>
               <p class="text-gray-200/90">Direct artisan partnerships, fair pricing, authentic designs, and premium materials.</p>
               <ul class="mt-4 space-y-2 text-gray-200/90">
                  <li><i class="fas fa-check text-accent mr-2"></i>Authentic craftsmanship</li>
                  <li><i class="fas fa-check text-accent mr-2"></i>Fair to artisans</li>
                  <li><i class="fas fa-check text-accent mr-2"></i>Premium materials</li>
                  <li><i class="fas fa-check text-accent mr-2"></i>Unique designs</li>
               </ul>
               <a href="contact.php" class="mt-6 inline-flex items-center bg-gradient-to-r from-primary to-accent px-6 py-3 rounded-xl font-semibold hover:shadow-xl">
                  <i class="fas fa-envelope mr-2"></i> Contact Us
               </a>
            </div>

            <!-- What we provide -->
            <div class="group relative rounded-3xl overflow-hidden shadow-lg border border-white/20 bg-white/10 backdrop-blur-xl p-6">
               <img src="images/cart.png" alt="What we provide" class="w-full rounded-lg mb-4 object-contain">
               <h3 class="font-bold mb-3">What we provide?</h3>
               <p class="text-gray-200/90">Curated masks, wood carvings, batik textiles & custom orders — every piece tells a story.</p>
               <ul class="mt-4 space-y-2 text-gray-200/90">
                  <li><i class="fas fa-check text-accent mr-2"></i>Devil masks</li>
                  <li><i class="fas fa-check text-accent mr-2"></i>Wood carvings</li>
                  <li><i class="fas fa-check text-accent mr-2"></i>Batik textiles</li>
                  <li><i class="fas fa-check text-accent mr-2"></i>Custom pieces</li>
               </ul>
               <a href="shop.php" class="mt-6 inline-flex items-center bg-gradient-to-r from-primary to-accent px-6 py-3 rounded-xl font-semibold hover:shadow-xl">
                  <i class="fas fa-store mr-2"></i> Our Shop
               </a>
            </div>
         </div>
      </div>
   </section>
</div>

<!-- Reviews -->
<section id="reviews" class="py-20">
   <div class="container mx-auto px-6 lg:px-12">
      <div class="text-center mb-16">
         <h2 class="font-bold mb-4">Client Reviews</h2>
         <p class="text-xl text-gray-300">What our customers say about Kandu Pinnawala</p>
      </div>

      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
         <?php for($i=1; $i<=6; $i++): ?>
         <div class="rounded-3xl shadow-lg border border-white/15 bg-white/10 backdrop-blur-xl p-6">
            <div class="flex items-center gap-4 mb-4">
               <img src="images/pic-<?= $i; ?>.png" class="w-12 h-12 rounded-full border border-white/20" alt="Reviewer">
               <div>
                  <h3 class="leading-tight">John Deo</h3>
                  <div class="text-xs text-yellow-400">
                     <i class="fas fa-star"></i>
                     <i class="fas fa-star"></i>
                     <i class="fas fa-star"></i>
                     <i class="fas fa-star"></i>
                     <i class="fas fa-star-half-alt"></i>
                  </div>
               </div>
            </div>
            <p>“Lorem ipsum dolor sit amet, consectetur adipisicing elit. Eos minima eveniet dolorum possimus.”</p>
         </div>
         <?php endfor; ?>
      </div>
   </div>
</section>

<script src="js/script.js"></script>
</body>
</html>
