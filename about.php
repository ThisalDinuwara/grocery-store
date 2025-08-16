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
   <link rel="stylesheet" href="css/style.css">

   <!-- Font size overrides -->
   <style>
      body {
         font-size: 1.25rem; /* ~20px */
         line-height: 1.8;
      }
      h1 { font-size: 3rem !important; }
      h2 { font-size: 2.75rem !important; }
      h3 { font-size: 2rem !important; }
      h4, h5, h6 { font-size: 1.5rem !important; }
      p, li { font-size: 1.25rem !important; }
      .text-sm { font-size: 1.125rem !important; }
      .text-xs { font-size: 1rem !important; }
      a, button { font-size: 1.25rem !important; }
      #reviews h2 { font-size: 3rem !important; }
      #reviews p { font-size: 1.25rem !important; }
   </style>

   <!-- Floating masks -->
   <style>
      .floating-bg {
         position: absolute; top: 0; left: 0; width: 100%; height: 100%;
         z-index: 0; overflow: hidden; pointer-events: none;
      }
      .floating-mask { position: absolute; width: 100px; opacity: .5; animation: floatRotate 20s ease-in-out infinite; }
      .floating-mask:nth-child(1) { top: 2%; left: 8%; animation-delay: 0s; width: 80px; }
      .floating-mask:nth-child(2) { top: 5%; left: 25%; animation-delay: 2s; width: 90px; }
      .floating-mask:nth-child(3) { top: 3%; left: 45%; animation-delay: 4s; width: 85px; }
      .floating-mask:nth-child(4) { top: 7%; left: 65%; animation-delay: 1s; width: 95px; }
      .floating-mask:nth-child(5) { top: 4%; left: 85%; animation-delay: 3s; width: 75px; }
      .floating-mask:nth-child(6) { top: 20%; left: 5%; animation-delay: 5s; width: 85px; }
      .floating-mask:nth-child(7) { top: 18%; left: 22%; animation-delay: 1.5s; width: 90px; }
      .floating-mask:nth-child(8) { top: 22%; left: 42%; animation-delay: 3.5s; width: 80px; }
      .floating-mask:nth-child(9) { top: 19%; left: 62%; animation-delay: 6s; width: 95px; }
      .floating-mask:nth-child(10){ top: 24%; left: 82%; animation-delay: .5s; width: 75px; }
      .floating-mask:nth-child(11){ top: 38%; left: 10%; animation-delay: 2.5s; width: 90px; }
      .floating-mask:nth-child(12){ top: 35%; left: 30%; animation-delay: 4.5s; width: 85px; }
      .floating-mask:nth-child(13){ top: 40%; left: 50%; animation-delay: 1.2s; width: 100px; }
      .floating-mask:nth-child(14){ top: 37%; left: 70%; animation-delay: 5.5s; width: 80px; }
      .floating-mask:nth-child(15){ top: 42%; left: 88%; animation-delay: 3.2s; width: 95px; }
      .floating-mask:nth-child(16){ top: 58%; left: 3%; animation-delay: 6.5s; width: 85px; }
      .floating-mask:nth-child(17){ top: 55%; left: 25%; animation-delay: 2.8s; width: 90px; }
      .floating-mask:nth-child(18){ top: 60%; left: 45%; animation-delay: 4.8s; width: 75px; }
      .floating-mask:nth-child(19){ top: 57%; left: 67%; animation-delay: 1.8s; width: 95px; }
      .floating-mask:nth-child(20){ top: 62%; left: 85%; animation-delay: 5.8s; width: 80px; }
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

<body class="min-h-screen text-white" style="background: linear-gradient(135deg, #1B0F0A 0%, #3E2723 50%, #5D4037 100%);">

<div class="relative w-full min-h-[100vh] overflow-hidden">
   <div class="floating-bg">
      <?php for($m=1;$m<=25;$m++): ?>
         <img src="images/mask<?= $m%2==0?8:7; ?>.png" class="floating-mask" alt="mask">
      <?php endfor; ?>
   </div>
   <div class="absolute inset-0 z-[5] bg-gradient-to-b from-[rgba(27,15,10,0.85)] via-[rgba(62,39,35,0.7)] to-[rgba(27,15,10,0.2)]"></div>

   <section class="relative z-10 py-20">
      <div class="container mx-auto px-6 lg:px-12 w-full">
         <div class="text-center mb-12 relative z-10">
            <div class="inline-flex items-center gap-2 mb-5 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur border border-white/20 shadow">
               <span class="w-2 h-2 rounded-full bg-gradient-to-r from-[#8B4513] to-[#D2B48C]"></span>
               <span class="text-sm font-semibold text-gray-200">Handcrafted in Sri Lanka</span>
            </div>
            <h2 class="font-extrabold tracking-tight leading-tight">
               <span class="inline-block px-6 py-3 rounded-2xl bg-white/10 text-white backdrop-blur-xl border border-white/20 shadow-2xl">
                 About Kandu Pinnawala
               </span>
            </h2>
            <span class="block h-1 w-28 mx-auto mt-4 rounded-full bg-gradient-to-r from-[#8B4513] to-[#D2B48C]"></span>
            <p class="mt-5 text-xl text-gray-300 max-w-3xl mx-auto">
               Craft, culture, and passion — handmade in Sri Lanka
            </p>
         </div>

         <div class="grid md:grid-cols-2 gap-8">
            <div class="group relative rounded-3xl overflow-hidden shadow-lg border border-white/20 bg-white/10 backdrop-blur-xl transition hover:shadow-2xl hover:border-white/30">
               <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#8B4513] to-[#D2B48C]"></div>
               <div class="aspect-[4/3] bg-[rgba(67,40,24,0.15)] overflow-hidden">
                  <img src="images/about.png" alt="Why choose us"
                       class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105"
                       onerror="this.src='images/about.png'">
               </div>
               <div class="p-6">
                  <h3 class="font-bold text-white mb-3">Why choose us?</h3>
                  <p class="text-gray-200/90">Direct artisan partnerships, fair pricing, authentic designs, and premium materials.</p>
                  <ul class="mt-5 grid sm:grid-cols-2 gap-3 text-gray-200/90">
                     <li class="flex items-center gap-3"><i class="fas fa-check" style="color:#D2B48C"></i> Authentic craftsmanship</li>
                     <li class="flex items-center gap-3"><i class="fas fa-check" style="color:#D2B48C"></i> Fair to artisans</li>
                     <li class="flex items-center gap-3"><i class="fas fa-check" style="color:#D2B48C"></i> Premium materials</li>
                     <li class="flex items-center gap-3"><i class="fas fa-check" style="color:#D2B48C"></i> Unique designs</li>
                  </ul>
                  <a href="contact.php"
                     class="mt-6 inline-flex items-center bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white px-6 py-3 rounded-xl font-semibold hover:shadow-xl hover:shadow-[rgba(139,69,19,0.25)] transition">
                     <i class="fas fa-envelope mr-2"></i> Contact Us
                  </a>
               </div>
            </div>

            <div class="group relative rounded-3xl overflow-hidden shadow-lg border border-white/20 bg-white/10 backdrop-blur-xl transition hover:shadow-2xl hover:border-white/30">
               <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#8B4513] to-[#D2B48C]"></div>
               <div class="aspect-[4/3] bg-[rgba(67,40,24,0.15)] overflow-hidden">
                  <img src="images/cart.png" alt="What we provide"
                       class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105"
                       onerror="this.src='images/cart.png'">
               </div>
               <div class="p-6">
                  <h3 class="font-bold text-white mb-3">What we provide?</h3>
                  <p class="text-gray-200/90">Curated masks, wood carvings, batik textiles & custom orders — every piece tells a story.</p>
                  <ul class="mt-5 grid sm:grid-cols-2 gap-3 text-gray-200/90">
                     <li class="flex items-center gap-3"><i class="fas fa-check" style="color:#D2B48C"></i> Devil masks</li>
                     <li class="flex items-center gap-3"><i class="fas fa-check" style="color:#D2B48C"></i> Wood carvings</li>
                     <li class="flex items-center gap-3"><i class="fas fa-check" style="color:#D2B48C"></i> Batik textiles</li>
                     <li class="flex items-center gap-3"><i class="fas fa-check" style="color:#D2B48C"></i> Custom pieces</li>
                  </ul>
                  <a href="shop.php"
                     class="mt-6 inline-flex items-center bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white px-6 py-3 rounded-xl font-semibold hover:shadow-xl hover:shadow-[rgba(139,69,19,0.25)] transition">
                     <i class="fas fa-store mr-2"></i> Our Shop
                  </a>
               </div>
            </div>
         </div>
      </div>
   </section>
</div>

<section id="reviews" class="py-20">
   <div class="container mx-auto px-6 lg:px-12">
      <div class="text-center mb-16">
         <h2 class="font-bold text-white mb-4">Client Reviews</h2>
         <p class="text-xl text-gray-300 max-w-3xl mx-auto">What our customers say about Kandu Pinnawala</p>
      </div>

      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
         <?php for($i=1; $i<=6; $i++): ?>
         <div class="rounded-3xl overflow-hidden shadow-lg border border-white/15 bg-white/10 backdrop-blur-xl p-6">
            <div class="flex items-center gap-4 mb-4">
               <img src="images/pic-<?= $i; ?>.png" alt="Reviewer"
                    class="w-12 h-12 rounded-full object-cover border border-white/20"
                    onerror="this.src='images/pic-1.png'">
               <div>
                  <h3 class="leading-tight">John Deo</h3>
                  <div class="text-xs" style="color:#FFD166">
                     <i class="fas fa-star mr-0.5"></i>
                     <i class="fas fa-star mr-0.5"></i>
                     <i class="fas fa-star mr-0.5"></i>
                     <i class="fas fa-star mr-0.5"></i>
                     <i class="fas fa-star-half-alt"></i>
                  </div>
               </div>
            </div>
            <p class="leading-relaxed">
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
