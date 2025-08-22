<?php 
@include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
if(!$user_id){
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
            sand:   '#F7F4EF', // light
            sand2:  '#EFE9DF',
            sand3:  '#E6DFD3',
            cocoa:  '#3E2723', // headings / strong text
            ink:    '#2F241F', // body text
            soft:   '#6B5A51',
            tan:    '#C89F6D', // accent 1
            bronze: '#A77A47', // accent 2
          },
          fontFamily: { gaming: ['Orbitron','monospace'], inter: ['Inter','sans-serif'] },
          boxShadow: {
            soft: '0 16px 40px rgba(120,100,80,.12)',
            card: '0 10px 28px rgba(120,100,80,.10)',
          }
        }
      }
    }
  </script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

  <!-- Type scale (bigger, but responsive) -->
  <style>
    :root{
      --ink:#2F241F; --soft:#6B5A51; --tan:#C89F6D; --bronze:#A77A47;
    }
    html,body{ height:100% }
    body{ font-family:'Inter',sans-serif; color:var(--ink) }
    h1{ font-weight:900 }
    .lead{ color:var(--soft) }
    /* floating background */
    .floating-bg{ position:absolute; inset:0; overflow:hidden; z-index:0; pointer-events:none }
    .floating-mask{ position:absolute; opacity:.45; filter:blur(0.4px); animation:floatRotate 18s ease-in-out infinite }
    @keyframes floatRotate{
      0%{ transform:translateY(0) rotate(0deg) }
      50%{ transform:translateY(-26px) rotate(180deg) }
      100%{ transform:translateY(0) rotate(360deg) }
    }
    /* glass card on light */
    .glass{
      background: rgba(255,255,255,.82);
      backdrop-filter: blur(10px);
      border:1px solid rgba(140,120,100,.20);
      box-shadow: var(--shadow, 0 10px 28px rgba(120,100,80,.10));
    }
    /* gradient accent line */
    .accent-line{ height:6px; width:112px; border-radius:9999px; background:linear-gradient(90deg,#C89F6D,#A77A47) }
  </style>
</head>

<body class="min-h-screen bg-[radial-gradient(900px_340px_at_12%_-10%,rgba(200,159,109,.20),transparent_60%),radial-gradient(900px_340px_at_88%_110%,rgba(167,122,71,.16),transparent_60%),linear-gradient(180deg,#F7F4EF,#EFE9DF_48%,#E6DFD3_100%)]">

  <!-- Floating Background (masks) -->
  <div class="relative w-full min-h-[100vh] overflow-hidden">
    <div class="floating-bg">
      <?php for($m=1;$m<=25;$m++): ?>
        <img src="images/mask<?= $m%2==0?8:7; ?>.png"
             class="floating-mask w-16 sm:w-20"
             style="top:<?= rand(5,85); ?>%;left:<?= rand(5,90); ?>%;animation-delay:<?= rand(0,6); ?>s;"
             alt="Sri Lankan mask">
      <?php endfor; ?>
    </div>

    <!-- Hero / About Heading -->
    <section class="relative z-10 pt-16 md:pt-20 pb-10">
      <div class="container mx-auto px-6 lg:px-12 text-center">
        <div class="inline-flex items-center gap-2 mb-5 px-4 py-1.5 rounded-full glass">
          <span class="inline-block w-2 h-2 rounded-full bg-gradient-to-r from-tan to-bronze"></span>
          <span class="text-sm font-semibold text-[color:var(--soft)]">Handcrafted in Sri Lanka</span>
        </div>

        <h2 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight text-cocoa">
          About <span class="font-gaming bg-gradient-to-r from-tan to-bronze bg-clip-text text-transparent">Kandu Pinnawala</span>
        </h2>
        <div class="accent-line mx-auto mt-5"></div>
        <p class="lead mt-5 text-lg md:text-xl max-w-3xl mx-auto">
          Craft, culture, and passion — handmade in Sri Lanka.
        </p>
      </div>
    </section>

    <!-- About Cards -->
    <section class="relative z-10 pb-16">
      <div class="container mx-auto px-6 lg:px-12">
        <div class="grid md:grid-cols-2 gap-8">

          <!-- Why choose us -->
          <div class="group relative rounded-3xl overflow-hidden glass shadow-card p-6">
            <img src="images/about.png" alt="Why choose us" class="w-full rounded-xl mb-4 object-contain">
            <h3 class="text-2xl font-bold text-cocoa mb-2">Why choose us?</h3>
            <p class="lead">Direct artisan partnerships, fair pricing, authentic designs, and premium materials.</p>
            <ul class="mt-4 space-y-2 text-[color:var(--soft)]">
              <li><i class="fas fa-check text-[color:var(--tan)] mr-2"></i>Authentic craftsmanship</li>
              <li><i class="fas fa-check text-[color:var(--tan)] mr-2"></i>Fair to artisans</li>
              <li><i class="fas fa-check text-[color:var(--tan)] mr-2"></i>Premium materials</li>
              <li><i class="fas fa-check text-[color:var(--tan)] mr-2"></i>Unique designs</li>
            </ul>
            <a href="contact.php"
               class="mt-6 inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold text-white bg-gradient-to-r from-tan to-bronze hover:brightness-105 shadow-soft">
              <i class="fas fa-envelope"></i> Contact Us
            </a>
          </div>

          <!-- What we provide -->
          <div class="group relative rounded-3xl overflow-hidden glass shadow-card p-6">
            <img src="images/cart.png" alt="What we provide" class="w-full rounded-xl mb-4 object-contain">
            <h3 class="text-2xl font-bold text-cocoa mb-2">What we provide?</h3>
            <p class="lead">Curated masks, wood carvings, batik textiles & custom orders — every piece tells a story.</p>
            <ul class="mt-4 space-y-2 text-[color:var(--soft)]">
              <li><i class="fas fa-check text-[color:var(--tan)] mr-2"></i>Devil masks</li>
              <li><i class="fas fa-check text-[color:var(--tan)] mr-2"></i>Wood carvings</li>
              <li><i class="fas fa-check text-[color:var(--tan)] mr-2"></i>Batik textiles</li>
              <li><i class="fas fa-check text-[color:var(--tan)] mr-2"></i>Custom pieces</li>
            </ul>
            <a href="shop.php"
               class="mt-6 inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold text-white bg-gradient-to-r from-tan to-bronze hover:brightness-105 shadow-soft">
              <i class="fas fa-store"></i> Our Shop
            </a>
          </div>

        </div>
      </div>
    </section>
  </div>

  <!-- Reviews -->
  <section id="reviews" class="py-16 md:py-20">
    <div class="container mx-auto px-6 lg:px-12">
      <div class="text-center mb-12">
        <h2 class="text-4xl md:text-5xl font-bold text-cocoa mb-3">Client Reviews</h2>
        <p class="lead">What our customers say about Kandu Pinnawala</p>
      </div>

      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php for($i=1; $i<=6; $i++): ?>
          <div class="rounded-3xl glass p-6">
            <div class="flex items-center gap-4 mb-4">
              <img src="images/pic-<?= $i; ?>.png" class="w-12 h-12 rounded-full border border-[rgba(140,120,100,.25)]" alt="Reviewer">
              <div>
                <h3 class="leading-tight font-semibold text-cocoa">John Deo</h3>
                <div class="text-xs text-[color:var(--bronze)]">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star-half-alt"></i>
                </div>
              </div>
            </div>
            <p class="text-[color:var(--soft)]">“Lorem ipsum dolor sit amet, consectetur adipisicing elit. Eos minima eveniet dolorum possimus.”</p>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </section>

  <script src="js/script.js"></script>
</body>
</html>
