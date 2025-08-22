<?php

@include 'config.php';
session_start();

/* ========= PHP 8.1+ safe sanitizers ========= */
function san_text($v){ return trim(filter_var($v ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)); } // names/titles/etc.
function san_int($v){  return (int) filter_var($v ?? '', FILTER_SANITIZE_NUMBER_INT); }        // ids/qty
function san_float($v){ return (float) filter_var($v ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION); } // prices
function san_email($v){ return trim(filter_var($v ?? '', FILTER_SANITIZE_EMAIL)); }             // emails

$user_id = $_SESSION['user_id'];
if(!isset($user_id)){
   header('location:login.php');
   exit;
}

/* ---------------------------
   Wishlist
----------------------------*/
if(isset($_POST['add_to_wishlist'])){
   $pid     = san_int($_POST['pid'] ?? '');
   $p_name  = san_text($_POST['p_name'] ?? '');
   $p_price = san_float($_POST['p_price'] ?? '');
   $p_image = san_text($_POST['p_image'] ?? '');

   $check_wishlist_numbers = $conn->prepare("SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
   $check_wishlist_numbers->execute([$p_name, $user_id]);

   $check_cart_numbers = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check_cart_numbers->execute([$p_name, $user_id]);

   if($check_wishlist_numbers->rowCount() > 0){
      $message[] = 'already added to wishlist!';
   }elseif($check_cart_numbers->rowCount() > 0){
      $message[] = 'already added to cart!';
   }else{
      $insert_wishlist = $conn->prepare("INSERT INTO wishlist(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
      $insert_wishlist->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
      $message[] = 'added to wishlist!';
   }
}

/* ---------------------------
   Cart
----------------------------*/
if(isset($_POST['add_to_cart'])){
   $pid     = san_int($_POST['pid'] ?? '');
   $p_name  = san_text($_POST['p_name'] ?? '');
   $p_price = san_float($_POST['p_price'] ?? '');
   $p_image = san_text($_POST['p_image'] ?? '');
   $p_qty   = max(1, san_int($_POST['p_qty'] ?? 1));

   $check_cart_numbers = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check_cart_numbers->execute([$p_name, $user_id]);

   if($check_cart_numbers->rowCount() > 0){
      $message[] = 'already added to cart!';
   }else{
      $check_wishlist_numbers = $conn->prepare("SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
      $check_wishlist_numbers->execute([$p_name, $user_id]);

      if($check_wishlist_numbers->rowCount() > 0){
         $delete_wishlist = $conn->prepare("DELETE FROM wishlist WHERE name = ? AND user_id = ?");
         $delete_wishlist->execute([$p_name, $user_id]);
      }

      $insert_cart = $conn->prepare("INSERT INTO cart(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
      $insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
      $message[] = 'added to cart!';
   }
}

/* -------------------------------------------
   Customer Review backend (drop-in)
--------------------------------------------*/
if (
   $_SERVER['REQUEST_METHOD'] === 'POST' &&
   isset($_POST['review_title'], $_POST['review_email'], $_POST['review_message'])
) {
   $rev_name   = san_text($_POST['review_name']  ?? '');
   $rev_email  = san_email($_POST['review_email'] ?? '');
   $rev_order  = san_text($_POST['review_order'] ?? '');
   $rev_title  = san_text($_POST['review_title'] ?? '');
   $rev_msg    = san_text($_POST['review_message'] ?? '');
   $rev_rating = san_int($_POST['review_rating'] ?? 0);

   $errors = [];
   if ($rev_name === '')   { $errors[] = 'Name is required.'; }
   if (!filter_var($rev_email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Valid email is required.'; }
   if ($rev_title === '')  { $errors[] = 'Title is required.'; }
   if ($rev_msg === '')    { $errors[] = 'Review message is required.'; }
   if ($rev_rating < 1 || $rev_rating > 5) { $errors[] = 'Rating must be between 1 and 5.'; }

   $image_path = null;
   if (isset($_FILES['review_image']) && $_FILES['review_image']['error'] !== UPLOAD_ERR_NO_FILE) {
      if ($_FILES['review_image']['error'] === UPLOAD_ERR_OK) {
         $tmp  = $_FILES['review_image']['tmp_name'];
         $size = (int)$_FILES['review_image']['size'];
         if ($size > 3 * 1024 * 1024) {
            $errors[] = 'Image must be smaller than 3MB.';
         } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $tmp);
            finfo_close($finfo);
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (!isset($allowed[$mime])) {
               $errors[] = 'Only JPG, PNG, or WEBP images are allowed.';
            } else {
               $ext = $allowed[$mime];
               $safeBase = bin2hex(random_bytes(8));
               $newName = $safeBase . '_' . time() . '.' . $ext;
               $destDir = __DIR__ . '/uploaded_reviews';
               if (!is_dir($destDir)) { @mkdir($destDir, 0755, true); }
               $dest = $destDir . '/' . $newName;
               if (move_uploaded_file($tmp, $dest)) {
                  $image_path = 'uploaded_reviews/' . $newName;
               } else {
                  $errors[] = 'Failed to save uploaded image.';
               }
            }
         }
      } else {
         $errors[] = 'Upload error. Please try again.';
      }
   }

   if (empty($errors)) {
      try {
         $sql = "INSERT INTO reviews (user_id, name, email, order_id, title, rating, message, image_path, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'approved')";
         $stmt = $conn->prepare($sql);
         $stmt->execute([
            $user_id ?? null,
            $rev_name,
            $rev_email,
            $rev_order !== '' ? $rev_order : null,
            $rev_title,
            $rev_rating,
            $rev_msg,
            $image_path
         ]);
         $message[] = 'Thank you! Your review has been submitted.';
      } catch (Exception $e) {
         $message[] = 'Could not save review. Please try again.';
      }
   } else {
      $message[] = implode(' ', $errors);
   }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Kandu Pinnawala - Premium Sri Lankan Handicrafts</title>

   <!-- Tailwind CDN -->
   <script src="https://cdn.tailwindcss.com"></script>
   <script>
      tailwind.config = {
         theme: {
            extend: {
               colors: {
                  primary:  '#FF7F00',
                  secondary:'#FF4500',
                  accent:   '#FFA500',
                  dark:     '#1A0F00',
                  darker:   '#0D0500'
               },
               fontFamily: {
                  'gaming': ['Orbitron', 'monospace']
               }
            }
         }
      }
   </script>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

   <!-- Google Fonts -->
   <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

   <style>
      * { margin: 0; padding: 0; box-sizing: border-box; }

      /* ====== Coffee/Brown text overrides (requested) ====== */
      :root{
        --ink:#2F241F;     /* deep brown/black */
        --cocoa:#3E2723;   /* coffee brown */
      }
      body {
         font-family: 'Inter', sans-serif;
         background: linear-gradient(135deg, #0D0500 0%, #1A0F00 50%, #251200 100%);
         color: var(--ink);
         overflow-x: hidden;
      }
      /* Force previously white/gray texts to brown/black without changing layout */
      .text-white, .text-gray-50, .text-gray-100, .text-gray-200, .text-gray-300, .text-gray-400, .text-gray-500{
        color: var(--cocoa) !important;
      }
      p, label, input, button, a, li, span, small { color: var(--cocoa); }
      h1, h2, h3, h4, h5, h6 { color: var(--cocoa); }

      /* Keep special gradient texts as-is */
      .gradient-text { background: linear-gradient(45deg, #FF7F00, #FF4500, #FFA500); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

      /* Cards & effects remain the same (no layout changes) */
      .neon-glow { box-shadow: 0 0 20px rgba(255,127,0,.5), 0 0 40px rgba(255,69,0,.3), 0 0 60px rgba(255,165,0,.2); }
      .glass-effect { background: rgba(255,255,255,.08); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,.18); }
      .hover-glow:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(255,127,0,.35); transition: all .3s ease; }
      .floating-animation { animation: floating 3s ease-in-out infinite; }
      @keyframes floating { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
      .cyber-border { position:relative; border:2px solid transparent; background: linear-gradient(135deg, rgba(255,127,0,.22), rgba(255,165,0,.22)) border-box; -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0); -webkit-mask-composite: exclude; }
      .message { position:fixed; top:20px; right:20px; background:linear-gradient(135deg,#FF7F00,#FF4500); color:#111; padding:15px 20px; border-radius:10px; border:1px solid rgba(255,255,255,.2); z-index:1000; }
      .ai-chat-widget { position:fixed; bottom:30px; right:30px; width:60px; height:60px; background:linear-gradient(135deg,#FF7F00,#FFA500); border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 10px 25px rgba(255,127,0,.4); animation:pulse 2s infinite; z-index:1000; }
      @keyframes pulse { 0%{transform:scale(1)} 50%{transform:scale(1.1)} 100%{transform:scale(1)} }
      .hero-bg { background: radial-gradient(circle at 20% 80%, rgba(255,127,0,.28) 0%, transparent 55%), radial-gradient(circle at 80% 20%, rgba(255,165,0,.28) 0%, transparent 55%), radial-gradient(circle at 40% 40%, rgba(255,69,0,.28) 0%, transparent 55%); }
      .category-icon { width:80px; height:80px; background:linear-gradient(135deg, rgba(255,127,0,.25), rgba(255,165,0,.25)); border-radius:20px; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; transition:all .3s ease; }
      .category-icon:hover { transform: rotateY(180deg); background: linear-gradient(135deg, #FF7F00, #FFA500); }
      .text-base{font-size:1.125rem!important;}
      .text-lg{font-size:1.25rem!important;}
      .text-xl{font-size:1.375rem!important;}
      p, label, input, button, a, li { font-size:1.12rem; }
      .product-card{ background: linear-gradient(180deg, rgba(26,15,0,.92), rgba(26,15,0,.84)); border:1px solid rgba(255,200,140,.28); border-radius:22px; backdrop-filter: blur(16px); transition: transform .4s ease, box-shadow .4s ease, border-color .4s ease; }
      .product-card:hover{ transform: translateY(-10px) scale(1.02); border-color: rgba(255,200,140,.6); box-shadow: 0 22px 48px rgba(255,127,0,.35); }
      .product-card .aspect-square{ position:relative; border-radius:18px; border:1px solid rgba(255,200,140,.25); overflow:hidden; background: radial-gradient(600px 120px at 20% 0%, rgba(255,165,0,.18), transparent 60%); }
      .product-card img{ transition: transform .6s ease; }
      .group:hover .product-card img{ transform: scale(1.07); }
      .price-badge{ font-size:1.05rem; letter-spacing:.3px; padding:.6rem 1rem; border:1px solid rgba(255,255,255,.18); box-shadow: 0 6px 18px rgba(255,165,0,.25); background: linear-gradient(135deg,#FF7F00,#FF4500); color:#111; }
      .product-title{ font-weight:800; letter-spacing:.2px; color:var(--cocoa); text-shadow: none; line-height:1.25; }
      .product-card label{ color:var(--cocoa); font-weight:600; }
      .product-card .qty{ background: rgba(255,255,255,.08); color:var(--cocoa); }
      .product-card .qty::placeholder{ color:#8b6f64; }
      .product-card .qty:focus{ outline:none; box-shadow:0 0 0 3px rgba(255,127,0,.35); }
      .product-card .glass-effect{ border-color: rgba(255,255,255,.25); color:var(--cocoa); }
      .promo-badge{ position:absolute; top:6px; right:6px; z-index:10; padding:.5rem .75rem; border-radius:9999px; font-weight:800; background:linear-gradient(135deg,#FF7F00,#FFA500); color:#111; border:1px solid rgba(255,255,255,.25); box-shadow:0 10px 25px rgba(255,127,0,.25); letter-spacing:.2px; font-size:.85rem; }
      .old-price{ color:#5b4a43; opacity:1; text-decoration: line-through; font-weight:700; }
      .deal-row{ display:flex; align-items:center; gap:.75rem; flex-wrap:wrap; }

      /* === Welcome Modal (first-visit only) === */
      #welcomeModal{
        position:fixed; inset:0; display:none; align-items:center; justify-content:center;
        background:rgba(0,0,0,.55); backdrop-filter:blur(6px); z-index:10000;
      }
      .wm-card{
        width:min(520px,92vw);
        border-radius:24px; border:1px solid rgba(255,255,255,.22);
        background: radial-gradient(120% 120% at 10% 0%, rgba(255,127,0,.18), rgba(255,69,0,.12) 40%, rgba(13,5,0,.95) 75%);
        box-shadow:0 24px 60px rgba(255,127,0,.25), inset 0 0 0 1px rgba(255,255,255,.08);
        transform:scale(.9) translateY(20px); opacity:0;
      }
      .wm-card.show{ animation: wmPop .55s cubic-bezier(.2,.8,.2,1) forwards; }
      @keyframes wmPop{
        0%{opacity:0; transform:scale(.9) translateY(20px)}
        60%{opacity:1; transform:scale(1.02) translateY(0)}
        100%{opacity:1; transform:scale(1) translateY(0)}
      }
      .wm-shine{
        position:absolute; inset:-2px; border-radius:24px; pointer-events:none;
        background:conic-gradient(from 0deg, rgba(255,127,0,.0), rgba(255,165,0,.25), rgba(255,127,0,.0));
        filter:blur(18px); opacity:.35; animation: wmSpin 6s linear infinite;
      }
      @keyframes wmSpin{ to{ transform:rotate(360deg) } }
      .wm-close{
        position:absolute; top:10px; right:10px;
        width:38px; height:38px; border-radius:12px;
        border:1px solid rgba(255,255,255,.15);
        background:rgba(255,255,255,.08); color:var(--cocoa);
      }
      .wm-close:hover{ background:linear-gradient(135deg,#FF7F00,#FF4500); color:#111; }
   </style>
</head>
<body>

<?php include 'header.php'; ?>

<!-- Hero -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden hero-bg">
   <div class="absolute top-10 left-10 w-96 h-96 rounded-full blur-3xl floating-animation opacity-30" style="background:linear-gradient(135deg,#FF7F00,#FF4500)"></div>
   <div class="absolute bottom-10 right-10 w-80 h-80 rounded-full blur-3xl floating-animation opacity-30" style="animation-delay:1s;background:linear-gradient(135deg,#FFA500,#FF7F00)"></div>

   <div class="container mx-auto px-6 lg:px-12 grid lg:grid-cols-2 gap-16 items-center relative z-10">
      <div class="space-y-8">
         <div class="space-y-6">
            <h1 class="text-6xl lg:text-8xl font-bold leading-tight">
               <span class="gradient-text font-gaming">KANDU</span><br>
               <span class="">PINNAWALA</span>
            </h1>
            <div class="h-1 w-40 rounded-full" style="background:linear-gradient(90deg,#FF7F00,#FFA500)"></div>
            <p class="text-xl leading-relaxed max-w-2xl">
               Discover the ultimate collection of traditional Sri Lankan handicrafts. Where heritage meets innovation in a warm, orange aesthetic.
            </p>
         </div>

         <div class="flex flex-col sm:flex-row gap-6">
            <a href="#promotions" class="group text-black px-8 py-4 rounded-full font-semibold text-lg hover-glow neon-glow" style="background:linear-gradient(90deg,#FF7F00,#FF4500)">
               <i class="fas fa-rocket mr-2"></i> EXPLORE NOW
               <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
            </a>
            <a href="#products" class="glass-effect px-8 py-4 rounded-full font-semibold text-lg hover-glow border border-white/30">
               <i class="fas fa-play mr-2"></i> WATCH DEMO
            </a>
         </div>

         <div class="flex space-x-8 pt-8">
            <div class="text-center"><div class="text-3xl font-bold gradient-text">1000+</div><div class=" text-sm">Products</div></div>
            <div class="text-center"><div class="text-3xl font-bold gradient-text">500+</div><div class=" text-sm">Happy Customers</div></div>
            <div class="text-center"><div class="text-3xl font-bold gradient-text">50+</div><div class=" text-sm">Artisans</div></div>
         </div>
      </div>

      <div class="relative">
         <div class="glass-effect p-8 rounded-3xl neon-glow">
            <div class="aspect-square rounded-2xl overflow-hidden">
               <img src="images/new.jpg" alt="Sri Lankan Handicrafts" class="w-full h-full object-cover">
            </div>
            <div class="absolute -top-4 -right-4 w-20 h-20 rounded-2xl flex items-center justify-center floating-animation" style="background:linear-gradient(135deg,#FF7F00,#FF4500)"><i class="fas fa-star text-2xl" style="color:#111"></i></div>
            <div class="absolute -bottom-4 -left-4 w-16 h-16 rounded-xl flex items-center justify-center floating-animation" style="animation-delay:.5s;background:linear-gradient(135deg,#FFA500,#FF7F00)"><i class="fas fa-heart text-xl" style="color:#111"></i></div>
         </div>
      </div>
   </div>
</section>

<!-- Categories -->
<section class="py-20 relative">
   <div class="container mx-auto px-6 lg:px-12">
      <div class="text-center mb-16">
         <h2 class="text-5xl lg:text-6xl font-bold mb-6"><span class="gradient-text font-gaming">CATEGORIES</span></h2>
         <div class="h-1 w-24 rounded-full mx-auto mb-6" style="background:linear-gradient(90deg,#FF7F00,#FFA500)"></div>
         <p class="text-xl max-w-3xl mx-auto">Choose your craft category and dive into traditional Sri Lankan artistry</p>
      </div>

      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
         <div class="glass-effect p-8 rounded-3xl text-center hover-glow cyber-border">
            <div class="category-icon"><i class="fas fa-tree text-3xl" style="color:#FFA500"></i></div>
            <h3 class="text-2xl font-bold mb-4 gradient-text">WOOD</h3>
            <p class=" mb-6 leading-relaxed">Handcrafted wooden masterpieces showcasing traditional artistry</p>
            <a href="category.php?category=wood" class="inline-flex items-center font-semibold" style="color:#FFA500"><i class="fas fa-arrow-right mr-2"></i>EXPLORE</a>
         </div>

         <div class="glass-effect p-8 rounded-3xl text-center hover-glow cyber-border">
            <div class="category-icon"><i class="fas fa-tshirt text-3xl" style="color:#FF7F00"></i></div>
            <h3 class="text-2xl font-bold mb-4 gradient-text">CLOTHES</h3>
            <p class=" mb-6 leading-relaxed">Traditional garments woven with cultural heritage</p>
            <a href="category.php?category=clothes" class="inline-flex items-center font-semibold" style="color:#FFA500"><i class="fas fa-arrow-right mr-2"></i>EXPLORE</a>
         </div>

         <div class="glass-effect p-8 rounded-3xl text-center hover-glow cyber-border">
            <div class="category-icon"><i class="fas fa-palette text-3xl" style="color:#FF4500"></i></div>
            <h3 class="text-2xl font-bold mb-4 gradient-text">WALL ARTS</h3>
            <p class=" mb-6 leading-relaxed">Beautiful decorations reflecting artistic traditions</p>
            <a href="category.php?category=wallarts" class="inline-flex items-center font-semibold" style="color:#FFA500"><i class="fas fa-arrow-right mr-2"></i>EXPLORE</a>
         </div>

         <div class="glass-effect p-8 rounded-3xl text-center hover-glow cyber-border">
            <div class="category-icon"><i class="fas fa-medal text-3xl" style="color:#FFD8A8"></i></div>
            <h3 class="text-2xl font-bold mb-4 gradient-text">BRASS</h3>
            <p class=" mb-6 leading-relaxed">Exquisite brass items by skilled traditional artisans</p>
            <a href="category.php?category=brass" class="inline-flex items-center font-semibold" style="color:#FFA500"><i class="fas fa-arrow-right mr-2"></i>EXPLORE</a>
         </div>
      </div>
   </div>
</section>

<!-- Promotions -->
<section id="promotions" class="py-20 relative">
  <div class="container mx-auto px-6 lg:px-12">
    <div class="text-center mb-16">
      <h2 class="text-5xl lg:text-6xl font-bold mb-6">
        <span class="gradient-text font-gaming">PROMOTIONS</span>
      </h2>
      <div class="h-1 w-24 rounded-full mx-auto mb-6" style="background:linear-gradient(90deg,#FF7F00,#FFA500)"></div>
      <p class="text-xl max-w-3xl mx-auto">Today’s hand-picked deals — managed from your Admin panel.</p>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
      <?php
        $now = date('Y-m-d H:i:s');
        $sql = "
          SELECT p.*, pr.id AS promo_id, pr.promo_price, pr.discount_percent, pr.label
          FROM promotions pr
          JOIN products p ON p.id = pr.product_id
          WHERE pr.active = 1
            AND (pr.starts_at IS NULL OR pr.starts_at <= ?)
            AND (pr.ends_at   IS NULL OR pr.ends_at   >= ?)
          ORDER BY pr.created_at DESC
          LIMIT 6
        ";
        $select_promos = $conn->prepare($sql);
        $select_promos->execute([$now, $now]);

        if($select_promos->rowCount() > 0){
          while($promo = $select_promos->fetch(PDO::FETCH_ASSOC)){
            $basePrice  = (float)$promo['price'];
            $promoPrice = null;

            if (!empty($promo['promo_price'])) {
              $promoPrice = (float)$promo['promo_price'];
            } elseif (!empty($promo['discount_percent'])) {
              $promoPrice = max(0, $basePrice * (1 - ((float)$promo['discount_percent'] / 100)));
            }

            if ($promoPrice !== null && $promoPrice < $basePrice) {
              $was = $basePrice;
              $nowPrice = $promoPrice;
              $save = $was > 0 ? round((($was - $nowPrice) / $was) * 100) : 0;
              $badgeText = !empty($promo['label']) ? htmlspecialchars($promo['label']) : 'Limited Offer';
      ?>
      <form action="" method="POST" class="group">
        <div class="product-card p-6 relative h-full flex flex-col">
          <div class="promo-badge"><?= $badgeText; ?> · SAVE <?= $save; ?>%</div>

          <div class="absolute top-6 left-6 text-black px-4 py-2 rounded-full font-bold text-sm z-10 neon-glow price-badge">
            Rs <?= number_format($nowPrice, 2); ?>
          </div>

          <div class="absolute top-6 right-20 flex flex-col gap-2 z-10">
            <button type="submit" name="add_to_wishlist" class="w-11 h-11 glass-effect rounded-full flex items-center justify-center hover:text-black transition-all"
              onmouseover="this.style.background='linear-gradient(90deg,#FF7F00,#FF4500)';this.style.color='#111';"
              onmouseout="this.style.background='';this.style.color='';">
              <i class="fas fa-heart"></i>
            </button>
            <a href="view_page.php?pid=<?= $promo['id']; ?>" class="w-11 h-11 glass-effect rounded-full flex items-center justify-center hover:text-black transition-all"
              onmouseover="this.style.background='linear-gradient(90deg,#FF7F00,#FF4500)';this.style.color='#111';"
              onmouseout="this.style.background='';this.style.color='';">
              <i class="fas fa-eye"></i>
            </a>
          </div>

          <div class="aspect-square rounded-2xl overflow-hidden mb-6">
            <img src="uploaded_img/<?= $promo['image']; ?>" alt="<?= htmlspecialchars($promo['name']); ?>" class="w-full h-full object-cover">
          </div>

          <div class="space-y-4 mt-auto">
            <h3 class="text-xl product-title"><?= htmlspecialchars($promo['name']); ?></h3>

            <div class="deal-row">
              <span class="old-price">Was Rs <?= number_format($was, 2); ?></span>
              <span class="text-sm px-2 py-1 rounded-md glass-effect border border-white/20">Now Rs <?= number_format($nowPrice, 2); ?></span>
            </div>

            <input type="hidden" name="pid" value="<?= $promo['id']; ?>">
            <input type="hidden" name="p_name" value="<?= htmlspecialchars($promo['name']); ?>">
            <input type="hidden" name="p_price" value="<?= $nowPrice; ?>">
            <input type="hidden" name="p_image" value="<?= htmlspecialchars($promo['image']); ?>">

            <div class="flex items-center gap-3">
              <label class="text-sm font-medium">QTY:</label>
              <input type="number" min="1" value="1" name="p_qty" class="qty w-24 px-3 py-2 glass-effect rounded-lg text-center focus:ring-2" style="--tw-ring-color: rgba(255,127,0,.7)">
            </div>

            <button type="submit" name="add_to_cart" class="w-full text-black py-3.5 rounded-xl font-semibold hover-glow neon-glow transition-all duration-300 transform hover:scale-[1.02]" style="background:linear-gradient(90deg,#FF7F00,#FF4500)">
              <i class="fas fa-shopping-cart mr-2"></i> ADD TO CART
            </button>
          </div>
        </div>
      </form>
      <?php
            } // end valid promo
          } // end while
        } else {
          echo '<div class="col-span-full text-center py-16">
                  <div class="glass-effect p-12 rounded-3xl max-w-md mx-auto">
                    <i class="fas fa-tags text-6xl" style="color:#FFA500"></i>
                    <p class="text-2xl font-medium">No promotions right now. Check back soon!</p>
                  </div>
                </div>';
        }
      ?>
    </div>

    <div class="text-center mt-12">
      <a href="shop.php" class="inline-flex items-center glass-effect px-8 py-4 rounded-full font-semibold text-lg hover-glow transition-all duration-300 transform hover:scale-105" style="background:linear-gradient(90deg,#FF7F00,#FFA500)">
        <i class="fas fa-store mr-3"></i> VIEW MORE DEALS <i class="fas fa-arrow-right ml-3"></i>
      </a>
    </div>
  </div>
</section>

<!-- Featured -->
<section id="products" class="py-20 relative">
   <div class="container mx-auto px-6 lg:px-12">
      <div class="text-center mb-16">
         <h2 class="text-5xl lg:text-6xl font-bold mb-6"><span class="gradient-text font-gaming">FEATURED</span></h2>
         <div class="h-1 w-24 rounded-full mx-auto mb-6" style="background:linear-gradient(90deg,#FF7F00,#FFA500)"></div>
         <p class="text-xl max-w-3xl mx-auto">Discover our premium collection of authentic Sri Lankan handicrafts</p>
      </div>

      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
         <?php
            $select_products = $conn->prepare("SELECT * FROM products LIMIT 6");
            $select_products->execute();
            if($select_products->rowCount() > 0){
               while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){
         ?>
         <form action="" method="POST" class="group">
            <div class="product-card p-6 relative h-full flex flex-col">
               <div class="absolute top-6 left-6 text-black px-4 py-2 rounded-full font-bold text-sm z-10 neon-glow price-badge">
                  Rs <?= $fetch_products['price']; ?>
               </div>

               <div class="absolute top-6 right-6 flex flex-col gap-2 z-10">
                  <button type="submit" name="add_to_wishlist" class="w-11 h-11 glass-effect rounded-full flex items-center justify-center transition-all"
                     onmouseover="this.style.background='linear-gradient(90deg,#FF7F00,#FF4500)';this.style.color='#111';"
                     onmouseout="this.style.background='';this.style.color='';">
                     <i class="fas fa-heart"></i>
                  </button>
                  <a href="view_page.php?pid=<?= $fetch_products['id']; ?>" class="w-11 h-11 glass-effect rounded-full flex items-center justify-center transition-all"
                     onmouseover="this.style.background='linear-gradient(90deg,#FF7F00,#FF4500)';this.style.color='#111';"
                     onmouseout="this.style.background='';this.style.color='';">
                     <i class="fas fa-eye"></i>
                  </a>
               </div>

               <div class="aspect-square rounded-2xl overflow-hidden mb-6">
                  <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="<?= htmlspecialchars($fetch_products['name']); ?>" class="w-full h-full object-cover">
               </div>

               <div class="space-y-4 mt-auto">
                  <h3 class="text-xl product-title"><?= htmlspecialchars($fetch_products['name']); ?></h3>

                  <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
                  <input type="hidden" name="p_name" value="<?= htmlspecialchars($fetch_products['name']); ?>">
                  <input type="hidden" name="p_price" value="<?= $fetch_products['price']; ?>">
                  <input type="hidden" name="p_image" value="<?= htmlspecialchars($fetch_products['image']); ?>">

                  <div class="flex items-center gap-3">
                     <label class="text-sm font-medium">QTY:</label>
                     <input type="number" min="1" value="1" name="p_qty" class="qty w-24 px-3 py-2 glass-effect rounded-lg text-center focus:ring-2" style="--tw-ring-color: rgba(255,127,0,.7)">
                  </div>

                  <button type="submit" name="add_to_cart" class="w-full text-black py-3.5 rounded-xl font-semibold hover-glow neon-glow transition-all duration-300 transform hover:scale-[1.02]" style="background:linear-gradient(90deg,#FF7F00,#FF4500)">
                     <i class="fas fa-shopping-cart mr-2"></i> ADD TO CART
                  </button>
               </div>
            </div>
         </form>
         <?php
               }
            }else{
               echo '<div class="col-span-full text-center py-16">
                     <div class="glass-effect p-12 rounded-3xl max-w-md mx-auto">
                        <i class="fas fa-box-open text-6xl" style="color:#FFA500)"></i>
                        <p class="text-2xl font-medium">No products available yet!</p>
                     </div>
                  </div>';
            }
         ?>
      </div>

      <div class="text-center mt-12">
         <a href="shop.php" class="inline-flex items-center glass-effect px-8 py-4 rounded-full font-semibold text-lg hover-glow transition-all duration-300 transform hover:scale-105" style="background:linear-gradient(90deg,#FF7F00,#FFA500)">
            <i class="fas fa-store mr-3"></i> VIEW ALL PRODUCTS <i class="fas fa-arrow-right ml-3"></i>
         </a>
      </div>
   </div>
</section>

<!-- Stats -->
<section class="py-20 relative">
   <div class="container mx-auto px-6 lg:px-12">
      <div class="glass-effect rounded-3xl p-12">
         <div class="grid md:grid-cols-4 gap-8 text-center">
            <div><div class="text-5xl font-bold gradient-text mb-2">1000+</div><div class="">Premium Products</div></div>
            <div><div class="text-5xl font-bold gradient-text mb-2">500+</div><div class="">Happy Customers</div></div>
            <div><div class="text-5xl font-bold gradient-text mb-2">50+</div><div class="">Master Artisans</div></div>
            <div><div class="text-5xl font-bold gradient-text mb-2">24/7</div><div class="">Customer Support</div></div>
         </div>
      </div>
   </div>
</section>

<!-- AI Chat Widget -->
<div class="ai-chat-widget" onclick="toggleChat()"><i class="fas fa-robot text-2xl" style="color:#111"></i></div>

<!-- Chat Interface (enhanced) -->
<div id="chatInterface" class="fixed bottom-20 right-8 w-80 h-[28rem] glass-effect rounded-3xl p-6 transform translate-y-full opacity-0 transition-all duration-300 z-50" style="display:none;">
   <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg font-bold gradient-text">AI Assistant</h3>
      <div class="flex items-center gap-2">
        <button id="aiClear" class="text-xs px-2 py-1 rounded-md border border-white/20 glass-effect">/clear</button>
        <button onclick="toggleChat()" class=" hover:opacity-70"><i class="fas fa-times"></i></button>
      </div>
   </div>

   <div class="h-56 overflow-y-auto mb-3 space-y-3" id="chatMessages" aria-live="polite">
      <div class="p-3 rounded-lg" style="background:rgba(255,127,0,.15)">
        <p class="text-sm">Hello! I can help with product search, order hints, returns & more. Try: “find wooden mask”, “order KP-2025-00123”, or “return policy”.</p>
      </div>
   </div>

   <!-- smart suggestions -->
   <div id="chatSuggestions" class="flex flex-wrap gap-2 mb-3">
     <button class="text-xs px-3 py-1 rounded-full border glass-effect" data-suggest="show promotions">Show promotions</button>
     <button class="text-xs px-3 py-1 rounded-full border glass-effect" data-suggest="find masks">Find masks</button>
     <button class="text-xs px-3 py-1 rounded-full border glass-effect" data-suggest="order status KP-2025-00001">Order status</button>
     <button class="text-xs px-3 py-1 rounded-full border glass-effect" data-suggest="contact support">Contact support</button>
   </div>

   <div class="flex gap-2">
      <input type="text" id="chatInput" placeholder="Type a message… (/help)" class="flex-1 px-4 py-2 glass-effect rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2" style="--tw-ring-color: rgba(255,127,0,.7)">
      <button id="chatSend" class="px-4 py-2 rounded-lg hover-glow text-black" style="background:linear-gradient(90deg,#FF7F00,#FF4500)"><i class="fas fa-paper-plane"></i></button>
   </div>
</div>

<?php include 'about.php'; ?>

<!-- Reviews -->
<section id="customer-reviews" class="py-20 relative">
  <div class="container mx-auto px-6 lg:px-12">
    <div class="text-center mb-12">
      <h2 class="text-4xl lg:text-5xl font-bold mb-4">What Customers Say</h2>
      <p class="text-lg max-w-2xl mx-auto">Latest verified feedback from our community.</p>
      <div class="h-1 w-24 rounded-full mx-auto mt-6" style="background:linear-gradient(90deg,#FF7F00,#FFA500)"></div>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
      <?php
        try {
          $revQ = $conn->prepare("SELECT name, title, rating, message, image_path, created_at FROM reviews WHERE status='approved' ORDER BY created_at DESC LIMIT 6");
          $revQ->execute();
          if ($revQ->rowCount() > 0):
            while ($r = $revQ->fetch(PDO::FETCH_ASSOC)):
              $rName  = htmlspecialchars($r['name']);
              $rTitle = htmlspecialchars($r['title']);
              $rMsg   = htmlspecialchars($r['message']);
              $rRate  = (int)$r['rating'];
              $rImg   = $r['image_path'];
      ?>
        <div class="rounded-3xl overflow-hidden shadow-lg border border-white/20 bg-white/10 backdrop-blur-xl p-6">
          <div class="flex items-center gap-4 mb-3">
            <div class="w-12 h-12 rounded-full text-black font-bold flex items-center justify-center" style="background:linear-gradient(135deg,#FF7F00,#FF4500)"><?php echo strtoupper(substr($rName,0,1)); ?></div>
            <div>
              <h3 class="text-lg font-bold leading-tight"><?php echo $rName; ?></h3>
              <div class="text-base" style="color:#FFA500">
                <?php for($i=1;$i<=5;$i++){ echo $i <= $rRate ? '<i class="fas fa-star mr-0.5"></i>' : '<i class="far fa-star mr-0.5"></i>'; } ?>
              </div>
            </div>
          </div>

          <h4 class="font-semibold mb-2"><?php echo $rTitle; ?></h4>
          <p class="leading-relaxed mb-3"><?php echo $rMsg; ?></p>

          <?php if (!empty($rImg)): ?>
            <div class="rounded-xl overflow-hidden border border-white/10">
              <img src="<?php echo htmlspecialchars($rImg); ?>" alt="review image" class="w-full h-40 object-cover">
            </div>
          <?php endif; ?>
        </div>
      <?php
            endwhile;
          else:
            echo '<div class="col-span-full text-center">No customer reviews yet. Be the first to leave one!</div>';
          endif;
        } catch (Exception $e) {
          echo '<div class="col-span-full text-center" style="color:#b71c1c">Failed to load reviews.</div>';
        }
      ?>
    </div>
  </div>
</section>

<!-- Review Form -->
<section id="leave-review" class="py-20 relative">
  <div class="container mx-auto px-6 lg:px-12">
    <div class="text-center mb-12">
      <h2 class="text-4xl lg:text-5xl font-bold mb-4">Leave a Review</h2>
      <p class="text-lg max-w-2xl mx-auto">Share your experience with Kandu Pinnawala. Your feedback helps us and other customers!</p>
      <div class="h-1 w-24 rounded-full mx-auto mt-6" style="background:linear-gradient(90deg,#FF7F00,#FFA500)"></div>
    </div>

    <form action="" method="POST" enctype="multipart/form-data" class="max-w-4xl mx-auto">
      <div class="glass-effect rounded-3xl p-8 md:p-10 shadow-xl border border-white/20">
        <div class="grid md:grid-cols-2 gap-6">
          <div><label class="block text-sm font-semibold mb-2">Full Name</label><input type="text" name="review_name" required class="w-full px-4 py-3 rounded-xl bg-white/10 placeholder-gray-400 border border-white/20 focus:outline-none focus:ring-2" style="--tw-ring-color: rgba(255,127,0,.7)" placeholder="e.g., Nimal Perera"></div>
          <div><label class="block text-sm font-semibold mb-2">Email</label><input type="email" name="review_email" required class="w-full px-4 py-3 rounded-xl bg-white/10 placeholder-gray-400 border border-white/20 focus:outline-none focus:ring-2" style="--tw-ring-color: rgba(255,127,0,.7)" placeholder="you@example.com"></div>
          <div><label class="block text-sm font-semibold mb-2">Order ID (optional)</label><input type="text" name="review_order" class="w-full px-4 py-3 rounded-xl bg-white/10 placeholder-gray-400 border border-white/20 focus:outline-none focus:ring-2" style="--tw-ring-color: rgba(255,127,0,.7)" placeholder="e.g., KP-2025-00123"></div>
          <div><label class="block text-sm font-semibold mb-2">Review Title</label><input type="text" name="review_title" required class="w-full px-4 py-3 rounded-xl bg-white/10 placeholder-gray-400 border border-white/20 focus:outline-none focus:ring-2" style="--tw-ring-color: rgba(255,127,0,.7)" placeholder="e.g., Beautiful craftsmanship!"></div>
        </div>

        <div class="mt-6">
          <label class="block text-sm font-semibold mb-2">Rating</label>
          <div class="flex items-center gap-2" id="ratingStars" data-selected="0">
            <button type="button" aria-label="1 star" data-value="1" class="star-btn w-10 h-10 rounded-full bg-white/10 border border-white/20 flex items-center justify-center hover:bg-white/20"><i class="fa-solid fa-star"></i></button>
            <button type="button" aria-label="2 stars" data-value="2" class="star-btn w-10 h-10 rounded-full bg-white/10 border border-white/20 flex items-center justify-center hover:bg-white/20"><i class="fa-solid fa-star"></i></button>
            <button type="button" aria-label="3 stars" data-value="3" class="star-btn w-10 h-10 rounded-full bg-white/10 border border-white/20 flex items-center justify-center hover:bg-white/20"><i class="fa-solid fa-star"></i></button>
            <button type="button" aria-label="4 stars" data-value="4" class="star-btn w-10 h-10 rounded-full bg-white/10 border border-white/20 flex items-center justify-center hover:bg-white/20"><i class="fa-solid fa-star"></i></button>
            <button type="button" aria-label="5 stars" data-value="5" class="star-btn w-10 h-10 rounded-full bg-white/10 border border-white/20 flex items-center justify-center hover:bg-white/20"><i class="fa-solid fa-star"></i></button>
          </div>
          <input type="hidden" name="review_rating" id="review_rating" value="0">
        </div>

        <div class="mt-6">
          <label class="block text-sm font-semibold mb-2">Your Review</label>
          <textarea name="review_message" rows="5" required class="w-full px-4 py-3 rounded-xl bg-white/10 placeholder-gray-400 border border-white/20 focus:outline-none focus:ring-2" style="--tw-ring-color: rgba(255,127,0,.7)" placeholder="Tell us about the product quality, delivery, and your experience."></textarea>
          <div class="flex items-center justify-between mt-2 text-xs"><span>Be respectful. Keep it helpful for other shoppers.</span><span id="charCount">0/1000</span></div>
        </div>

        <div class="mt-6 grid md:grid-cols-2 gap-6">
          <div><label class="block text-sm font-semibold mb-2">Add a photo (optional)</label><input type="file" name="review_image" accept="image/*" class="w-full file:mr-4 file:rounded-lg file:border-0 file:px-4 file:py-2 file:text-black file:cursor-pointer rounded-xl bg-white/10 border border-white/20"
            onfocus="this.style.boxShadow='0 0 0 2px rgba(255,127,0,.7)'" onblur="this.style.boxShadow='none'"></div>
          <div class="flex items-center gap-2 mt-8 md:mt-0"><input id="agree" type="checkbox" required class="w-4 h-4 rounded border-white/30 bg-white/10"><label for="agree" class="text-sm">I agree to have my review displayed on the website.</label></div>
        </div>

        <div class="mt-8 flex items-center gap-4">
          <button type="submit" class="inline-flex items-center text-black px-9 py-3 rounded-xl font-semibold hover:shadow-xl transition" style="background:linear-gradient(90deg,#FF7F00,#FF4500); box-shadow:0 12px 28px rgba(255,127,0,.22);">
            <i class="fa-solid fa-paper-plane mr-2"></i> Submit Review
          </button>
          <span class="text-xs">*Backend included above for saving reviews.</span>
        </div>
      </div>
    </form>
  </div>
</section>

<!-- Welcome Modal (added) -->
<div id="welcomeModal" aria-hidden="true">
  <div class="wm-card glass-effect p-8 md:p-10 relative">
    <div class="wm-shine"></div>

    <button id="wmClose" class="wm-close flex items-center justify-center">
      <i class="fa-solid fa-xmark"></i>
    </button>

    <div class="text-center space-y-4">
      <div class="text-5xl font-extrabold gradient-text tracking-wide">WELCOME</div>
      <div class="text-2xl font-gaming">TO <span class="gradient-text">KANDU PINNAWALA</span></div>
      <p class=" max-w-md mx-auto">
        Traditional Sri Lankan handicrafts — crafted with love.
      </p>

      <button id="wmStart"
        class="mt-4 inline-flex items-center justify-center text-black px-7 py-3 rounded-xl font-semibold hover-glow neon-glow transition"
        style="background:linear-gradient(90deg,#FF7F00,#FF4500)">
        <i class="fa-solid fa-rocket mr-2"></i> Start exploring
      </button>

      <label class="mt-3 flex items-center justify-center gap-2 text-xs select-none">
        <input id="wmDontShow" type="checkbox"
               class="w-4 h-4 rounded border-white/30 bg-white/10">
        Don’t show again
      </label>
    </div>

    <div class="pointer-events-none absolute -top-4 -left-4 w-14 h-14 rounded-xl floating-animation"
         style="background:linear-gradient(135deg,#FF7F00,#FF4500); opacity:.75"></div>
    <div class="pointer-events-none absolute -bottom-5 -right-6 w-16 h-16 rounded-xl floating-animation"
         style="animation-delay:.6s;background:linear-gradient(135deg,#FFA500,#FF7F00); opacity:.75"></div>
  </div>
</div>

<?php include 'footer.php'; ?>

<script>
   function toggleChat() {
      const chatInterface = document.getElementById('chatInterface');
      if (chatInterface.style.display === 'none') {
         chatInterface.style.display = 'block';
         setTimeout(() => { chatInterface.classList.remove('translate-y-full','opacity-0'); }, 10);
         // load history when opened
         KPAI.loadHistory();
      } else {
         chatInterface.classList.add('translate-y-full','opacity-0');
         setTimeout(() => { chatInterface.style.display = 'none'; }, 300);
      }
   }

   /* ============================
      ADVANCED AI ASSISTANT (no backend, intelligent on-page)
      - Keeps chat history in localStorage
      - Understands intents: help, clear, promotions, category search, product keyword search, order hints, policies, contact, open hours, payment methods
      - Extracts product names from current page to answer "do you have X?"
      - Suggestion chips
   ============================ */
   const KPAI = (function(){
     const storeKey = 'kp_ai_chat_history';
     const elMsgs = () => document.getElementById('chatMessages');
     const elInput = () => document.getElementById('chatInput');

     function addUserMessage(text){
       const div = document.createElement('div');
       div.className = 'text-right';
       div.innerHTML = '<div class="inline-block p-3 rounded-lg max-w-xs text-black" style="background:linear-gradient(90deg,#FF7F00,#FF4500)"><p class="text-sm">'+escapeHtml(text)+'</p></div>';
       elMsgs().appendChild(div);
       elMsgs().scrollTop = elMsgs().scrollHeight;
       saveToHistory({role:'user', text});
     }

     function addBotMessage(text){
       const div = document.createElement('div');
       div.className = 'p-3 rounded-lg';
       div.style.background = 'rgba(255,127,0,.15)';
       div.innerHTML = '<p class="text-sm">'+text+'</p>';
       elMsgs().appendChild(div);
       elMsgs().scrollTop = elMsgs().scrollHeight;
       saveToHistory({role:'assistant', text});
     }

     function typeBot(text, delay=18){
       // typing effect
       const container = document.createElement('div');
       container.className = 'p-3 rounded-lg';
       container.style.background = 'rgba(255,127,0,.15)';
       const p = document.createElement('p');
       p.className = 'text-sm';
       container.appendChild(p);
       elMsgs().appendChild(container);
       elMsgs().scrollTop = elMsgs().scrollHeight;

       let i=0;
       const id = setInterval(()=>{
         p.innerHTML = escapeHtml(text.slice(0, i)) + (i<text.length ? '<span class="opacity-60">▌</span>' : '');
         elMsgs().scrollTop = elMsgs().scrollHeight;
         if(++i > text.length){ clearInterval(id); saveToHistory({role:'assistant', text}); }
       }, delay);
     }

     function escapeHtml(str){
       return str.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
     }

     function saveToHistory(msg){
       try{
         const arr = JSON.parse(localStorage.getItem(storeKey) || '[]');
         arr.push({t:Date.now(), ...msg});
         localStorage.setItem(storeKey, JSON.stringify(arr));
       }catch(e){}
     }

     function loadHistory(){
       try{
         const arr = JSON.parse(localStorage.getItem(storeKey) || '[]');
         if(!arr.length) return;
         elMsgs().innerHTML = ''; // clear default
         for(const m of arr){
           if(m.role==='user') addUserMessage(m.text);
           else addBotMessage(escapeHtml(m.text));
         }
       }catch(e){}
     }

     function clearHistory(){
       localStorage.removeItem(storeKey);
       elMsgs().innerHTML = '';
       addBotMessage('History cleared. How can I help? Try “find masks” or “return policy”.');
     }

     function getVisibleProductNames(){
       const nodes = document.querySelectorAll('#products .product-card h3, #promotions .product-card h3');
       const names = [];
       nodes.forEach(n=> names.push((n.textContent||'').trim()));
       return names;
     }

     function intentRouter(text){
       const q = text.trim().toLowerCase();

       // commands
       if(q === '/help'){
         return "Commands: <br>• <b>/help</b> – this help <br>• <b>/clear</b> – clear chat <br><br>Try questions like:<br>• “find wooden mask”<br>• “show promotions”<br>• “order status KP-2025-00001”<br>• “return policy” / “payment methods” / “contact support”.";
       }
       if(q === '/clear'){ clearHistory(); return null; }

       // promotions
       if(q.includes('promotion') || q.includes('deal') || q.includes('discount')){
         return 'Opening promotions for you… <br><a href="#promotions" class="underline">Jump to Promotions</a>';
       }

       // categories
       const categories = [
         {key:'wood', url:'category.php?category=wood', words:['wood','wooden','mask','masks']},
         {key:'clothes', url:'category.php?category=clothes', words:['cloth','clothes','garment','saree','shirt']},
         {key:'wallarts', url:'category.php?category=wallarts', words:['wall','art','painting','decor']},
         {key:'brass', url:'category.php?category=brass', words:['brass','metal','statue']},
       ];
       for(const c of categories){
         if(c.words.some(w=> q.includes(w))){
           return `I think you’re looking for <b>${c.key.toUpperCase()}</b>. <a href="${c.url}" class="underline">Open ${c.key}</a> or <a href="#products" class="underline">scroll to featured</a>.`;
         }
       }

       // product keyword search using names from page
       const names = getVisibleProductNames();
       if(names.length){
         const hits = names.filter(n => n.toLowerCase().includes(q));
         if(!hits.length){
           // pick fuzzy hits by splitting
           const words = q.split(/\s+/).filter(Boolean);
           const maybe = names.filter(n => words.some(w => n.toLowerCase().includes(w)));
           if(maybe.length){
             return "I didn’t find an exact match, but these look close:<br>• " + maybe.slice(0,5).join('<br>• ') + '<br><br><a href="shop.php" class="underline">Open full shop</a>';
           }
         }else{
           return "I found these in our featured list:<br>• " + hits.slice(0,6).join('<br>• ') + '<br><br><a href="shop.php" class="underline">See more in shop</a>';
         }
       }

       // order status pattern (client-side hint only)
       const m = q.match(/(?:order\s*status|order)\s*(kp-\d{4}-\d{3,})/i);
       if(m){
         const id = m[1].toUpperCase();
         return `For <b>${id}</b>: please check your email for updates or contact support with this ID. If you have an account, visit <a href="orders.php?id=${encodeURIComponent(id)}" class="underline">your orders</a>.`;
       }

       // policies & FAQs
       if(q.includes('return')) {
         return "Returns: Items can be returned within <b>14 days</b> if unused and in original packaging. Keep your receipt/order ID. For help, message us via <a href=\"contact.php\" class=\"underline\">Contact</a>.";
       }
       if(q.includes('refund')){
         return "Refunds are processed to your original payment method within <b>5–7 business days</b> after we receive and inspect the item.";
       }
       if(q.includes('ship') || q.includes('delivery')){
         return "Shipping: Island-wide delivery in <b>2–5 business days</b>. International shipping available on request. Free delivery for orders over <b>Rs 15,000</b> (Sri Lanka).";
       }
       if(q.includes('pay') || q.includes('payment') || q.includes('method')){
         return "Payment methods: Cash on Delivery (selected areas), Credit/Debit cards, and bank transfer. All transactions are secured.";
       }
       if(q.includes('hour') || q.includes('open') || q.includes('time')){
         return "Store hours: Online shop is open 24/7. Support: <b>Mon–Fri 9:00–18:00</b> (IST).";
       }
       if(q.includes('contact') || q.includes('support') || q.includes('help')){
         return "You can reach us via <a href=\"contact.php\" class=\"underline\">the Contact page</a> or email <b>support@kandupinnawala.lk</b>. We usually reply within a business day.";
       }

       // default
       return "I can help with promotions, category search, order hints, shipping, returns, and payments. Try: <b>“find wooden mask”</b> or <b>“return policy”</b>.";
     }

     function handleSend(){
       const input = elInput();
       const text = (input.value || '').trim();
       if(!text) return;
       addUserMessage(text);
       input.value = '';

       // small delay to mimic thinking
       setTimeout(()=>{
         const ans = intentRouter(text);
         if(ans) typeBot(ans);
       }, 500);
     }

     function wire(){
       document.getElementById('chatSend').addEventListener('click', handleSend);
       elInput().addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ e.preventDefault(); handleSend(); }});
       document.getElementById('aiClear').addEventListener('click', ()=>{ addUserMessage('/clear'); clearHistory(); });

       // suggestion chips
       document.querySelectorAll('#chatSuggestions [data-suggest]').forEach(btn=>{
         btn.addEventListener('click', ()=>{
           elInput().value = btn.getAttribute('data-suggest');
           handleSend();
         });
       });
     }

     return { wire, loadHistory };
   })();

   // Smooth anchor scroll
   document.querySelectorAll('a[href^="#"]').forEach(a=>{
      a.addEventListener('click',e=>{ e.preventDefault(); document.querySelector(a.getAttribute('href')).scrollIntoView({behavior:'smooth'}); });
   });

   // Rating stars & review char count
   (function(){
      const c = document.getElementById('ratingStars'); const h = document.getElementById('review_rating');
      if(!c||!h) return; const stars=[...c.querySelectorAll('.star-btn')];
      const active='linear-gradient(135deg, #FF7F00, #FF4500)'; const inactive='rgba(255,255,255,0.08)';
      function paint(n){ stars.forEach((b,i)=>{ b.style.background=(i<n)?active:inactive; b.style.borderColor=(i<n)?'rgba(255,205,150,0.6)':'rgba(255,255,255,0.2)'; b.querySelector('i').style.color=(i<n)?'#111':'#6d4c41'; }); }
      stars.forEach(b=>{
        b.addEventListener('click',()=>{ const v=Number(b.dataset.value||0); h.value=v; c.dataset.selected=v; paint(v); });
        b.addEventListener('mouseenter',()=>paint(Number(b.dataset.value||0)));
        b.addEventListener('mouseleave',()=>paint(Number(c.dataset.selected||0)));
      });
      paint(0);
   })();
   (function(){
      const ta=document.querySelector('textarea[name="review_message"]');
      const cc=document.getElementById('charCount'); if(!ta||!cc) return; const max=1000;
      ta.addEventListener('input',()=>{ const len=ta.value.length; cc.textContent = `${len}/${max}`; if(len>max){ ta.value=ta.value.slice(0,max); } });
   })();

   // Welcome Modal logic (respect "Don't show again")
   (function(){
    const modal = document.getElementById('welcomeModal');
    if(!modal) return;
    const card  = modal.querySelector('.wm-card');
    const btnX  = document.getElementById('wmClose');
    const btnGo = document.getElementById('wmStart');
    const cbDont = document.getElementById('wmDontShow');
    const KEY = 'kp_welcome_hidden';

    function openModal(){
      if(localStorage.getItem(KEY)==='1') return;
      modal.style.display = 'flex';
      requestAnimationFrame(()=> card.classList.add('show'));
      modal.setAttribute('aria-hidden','false');
    }
    function closeModal(){
      card.classList.remove('show');
      setTimeout(()=>{
        modal.style.display='none';
        modal.setAttribute('aria-hidden','true');
      }, 240);
      if(cbDont && cbDont.checked){ localStorage.setItem(KEY, '1'); }
    }

    [btnX, btnGo].forEach(b=> b && b.addEventListener('click', closeModal));
    modal.addEventListener('click', (e)=>{ if(e.target===modal) closeModal(); });
    document.addEventListener('keydown', (e)=>{ if(e.key==='Escape' && modal.style.display==='flex') closeModal(); });

    window.addEventListener('load', ()=> setTimeout(openModal, 600));
   })();

   // Wire AI after DOM ready
   window.addEventListener('load', KPAI.wire);
</script>

<script src="js/script.js"></script>
</body>
</html>
