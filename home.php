<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if(!$user_id){
   header('location:login.php');
   exit;
}

/* ===========================================
   Helpers + Safety
=========================================== */
$message = [];

function clean_int($v, $min = 0, $max = PHP_INT_MAX){
   $n = (int)filter_var($v, FILTER_SANITIZE_NUMBER_INT);
   if($n < $min) $n = $min;
   if($n > $max) $n = $max;
   return $n;
}
function clean_money($v){
   $v = filter_var($v, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
   return is_numeric($v) ? (float)$v : 0.0;
}
function clean_text($v){
   $v = trim((string)($v ?? ''));
   return filter_var($v, FILTER_SANITIZE_SPECIAL_CHARS);
}

/* ===========================================
   Ensure table for admin alerts (low stock)
=========================================== */
try{
   $conn->exec("
      CREATE TABLE IF NOT EXISTS `admin_alerts`(
         `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
         `alert_type` VARCHAR(32) NOT NULL,
         `product_id` INT UNSIGNED NOT NULL,
         `product_name` VARCHAR(255) NOT NULL,
         `current_qty` INT NOT NULL DEFAULT 0,
         `threshold` INT NOT NULL DEFAULT 10,
         `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
         `resolved` TINYINT(1) NOT NULL DEFAULT 0,
         PRIMARY KEY (`id`),
         KEY `idx_alert_type` (`alert_type`),
         KEY `idx_product_id` (`product_id`),
         KEY `idx_resolved` (`resolved`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
   ");
}catch(Throwable $e){ /* non-fatal */ }

function create_low_stock_alert_safe(PDO $conn, int $pid, string $pname, int $qty, int $threshold = 10){
   if($qty > $threshold) return;
   $sel = $conn->prepare("SELECT id FROM admin_alerts WHERE alert_type='LOW_STOCK' AND product_id=? AND resolved=0 LIMIT 1");
   $sel->execute([$pid]);
   if(!$sel->fetch()){
      $ins = $conn->prepare("INSERT INTO admin_alerts (alert_type, product_id, product_name, current_qty, threshold) VALUES ('LOW_STOCK',?,?,?,?)");
      $ins->execute([$pid, $pname, $qty, $threshold]);
   }else{
      $upd = $conn->prepare("UPDATE admin_alerts SET current_qty = LEAST(current_qty, ?) WHERE alert_type='LOW_STOCK' AND product_id=? AND resolved=0");
      $upd->execute([$qty, $pid]);
   }
}

/* ===========================================
   Wishlist
=========================================== */
if(isset($_POST['add_to_wishlist'])){
   $pid     = clean_int($_POST['pid'] ?? 0, 1);
   $p_name  = clean_text($_POST['p_name'] ?? '');
   $p_price = clean_money($_POST['p_price'] ?? 0);
   $p_image = clean_text($_POST['p_image'] ?? '');

   $check_wishlist = $conn->prepare("SELECT id FROM wishlist WHERE pid = ? AND user_id = ?");
   $check_wishlist->execute([$pid, $user_id]);

   $check_cart = $conn->prepare("SELECT id FROM cart WHERE pid = ? AND user_id = ?");
   $check_cart->execute([$pid, $user_id]);

   if($check_wishlist->rowCount() > 0){
      $message[] = 'Already in wishlist.';
   }elseif($check_cart->rowCount() > 0){
      $message[] = 'Already in cart.';
   }else{
      $ins = $conn->prepare("INSERT INTO wishlist(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
      $ins->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
      $message[] = 'Added to wishlist.';
   }
}

/* ===========================================
   ADD TO CART — stock-safe with alerts
=========================================== */
if(isset($_POST['add_to_cart'])){
   $pid     = clean_int($_POST['pid'] ?? 0, 1);
   $p_name  = clean_text($_POST['p_name'] ?? '');
   $p_price = clean_money($_POST['p_price'] ?? 0);
   $p_image = clean_text($_POST['p_image'] ?? '');
   $p_qty   = clean_int($_POST['p_qty'] ?? 1, 1, 100000);

   try{
      $conn->beginTransaction();

      $q = $conn->prepare("SELECT id, name, price, quantity, image FROM products WHERE id = ? FOR UPDATE");
      $q->execute([$pid]);
      $product = $q->fetch(PDO::FETCH_ASSOC);

      if(!$product){
         $conn->rollBack();
         $message[] = 'Product not found.';
      }else{
         $available = (int)$product['quantity'];
         $safeName  = (string)$product['name'];
         $safeImg   = (string)$product['image'];
         $unitPrice = (float)$product['price'];

         if($available <= 0){
            $conn->rollBack();
            $message[] = 'Unavailable right now.';
         }elseif($p_qty > $available){
            $conn->rollBack();
            $message[] = 'Only '.$available.' left in stock.';
         }else{
            $delw = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND pid = ?");
            $delw->execute([$user_id, $pid]);

            $chk = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND pid = ? FOR UPDATE");
            $chk->execute([$user_id, $pid]);

            if($row = $chk->fetch(PDO::FETCH_ASSOC)){
               $newQty = (int)$row['quantity'] + $p_qty;
               $upc = $conn->prepare("UPDATE cart SET quantity = ?, price = ? WHERE id = ?");
               $upc->execute([$newQty, $p_price > 0 ? $p_price : $unitPrice, $row['id']]);
            }else{
               $ins = $conn->prepare("INSERT INTO cart(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
               $ins->execute([$user_id, $pid, $safeName, ($p_price > 0 ? $p_price : $unitPrice), $p_qty, $safeImg]);
            }

            $upd = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $upd->execute([$p_qty, $pid]);

            $rq = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
            $rq->execute([$pid]);
            $left = (int)$rq->fetchColumn();

            create_low_stock_alert_safe($conn, $pid, $safeName, $left, 10);

            $conn->commit();
            $message[] = 'Added to cart.';
         }
      }
   }catch(Throwable $e){
      if($conn->inTransaction()) $conn->rollBack();
      $message[] = 'Could not add to cart. Please try again.';
   }
}

/* ===========================================
   Customer Review backend (same as before)
=========================================== */
if (
   $_SERVER['REQUEST_METHOD'] === 'POST' &&
   isset($_POST['review_title'], $_POST['review_email'], $_POST['review_message']) &&
   !isset($_POST['add_to_cart']) && !isset($_POST['add_to_wishlist'])
) {
   $rev_name   = clean_text($_POST['review_name']  ?? '');
   $rev_email  = trim(filter_var($_POST['review_email'] ?? '', FILTER_SANITIZE_EMAIL));
   $rev_order  = clean_text($_POST['review_order'] ?? '');
   $rev_title  = clean_text($_POST['review_title'] ?? '');
   $rev_msg    = clean_text($_POST['review_message'] ?? '');
   $rev_rating = clean_int($_POST['review_rating'] ?? 0, 0, 5);

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
         $conn->exec("
           CREATE TABLE IF NOT EXISTS `reviews`(
             `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
             `user_id` INT UNSIGNED NULL,
             `name` VARCHAR(120) NOT NULL,
             `email` VARCHAR(160) NOT NULL,
             `order_id` VARCHAR(64) NULL,
             `title` VARCHAR(180) NOT NULL,
             `rating` TINYINT NOT NULL,
             `message` TEXT NOT NULL,
             `image_path` VARCHAR(255) NULL,
             `status` ENUM('approved','pending','rejected') NOT NULL DEFAULT 'approved',
             `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
             PRIMARY KEY (`id`)
           ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
         ");

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
   <meta charset="UTF-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <title>Kandu Pinnawala - Premium Sri Lankan Handicrafts</title>

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
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

   <!-- Google Fonts -->
   <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

   <style>
      * { margin: 0; padding: 0; box-sizing: border-box; }
      body {
         font-family: 'Inter', sans-serif;
         background: #ffffff;
         color: #0b0b0b;
         overflow-x: hidden;
      }
      .neon-glow { box-shadow: 0 0 20px rgba(139,69,19,.18), 0 0 40px rgba(160,82,45,.12), 0 0 60px rgba(210,180,140,.08); }
      .glass-effect { background: rgba(0,0,0,.03); backdrop-filter: blur(10px); border: 1px solid rgba(0,0,0,.12); }
      .hover-glow:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,.08); transition: all .3s ease; }
      .floating-animation { animation: floating 3s ease-in-out infinite; }
      @keyframes floating { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
      .gradient-text { background: linear-gradient(45deg, #8B4513, #A0522D, #D2B48C); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
      .cyber-border { position:relative; border:2px solid transparent; background: linear-gradient(135deg, rgba(139,69,19,.08), rgba(160,82,45,.08)) border-box; -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0); -webkit-mask-composite: exclude; }
      .product-card{ background:#fff; border:1px solid rgba(0,0,0,.12); border-radius:22px; backdrop-filter: blur(16px); transition: transform .4s ease, box-shadow .4s ease, border-color .4s ease; }
      .product-card:hover{ transform: translateY(-10px) scale(1.02); border-color: rgba(0,0,0,.18); box-shadow: 0 22px 48px rgba(0,0,0,.06); }
      .product-card .aspect-square{ position:relative; border-radius:18px; border:1px solid rgba(0,0,0,.08); overflow:hidden; background: radial-gradient(600px 120px at 20% 0%, rgba(210,180,140,.10), transparent 60%); }
      .product-card img{ transition: transform .6s ease; }
      .group:hover .product-card img{ transform: scale(1.07); }
      .price-badge{ font-size:1.05rem; letter-spacing:.3px; padding:.6rem 1rem; border:1px solid rgba(0,0,0,.12); box-shadow: 0 6px 18px rgba(0,0,0,.06); background:#fff; color:#111; }
      .product-title{ font-weight:800; letter-spacing:.2px; color:#111827; line-height:1.25; }
      .product-card .qty{ background:#fff; color:#111; border:1px solid rgba(0,0,0,.12); }
      .product-card .qty:focus{ outline:none; box-shadow:0 0 0 3px rgba(210,180,140,.25); }
      .promo-badge{ position:absolute; top:6px; right:6px; z-index:10; padding:.5rem .75rem; border-radius:9999px; font-weight:800; background:linear-gradient(135deg,#22c55e,#86efac); color:#0f172a; border:1px solid rgba(0,0,0,.08); box-shadow:0 10px 25px rgba(16,185,129,.18); letter-spacing:.2px; font-size:.85rem; }
      .old-price{ color:#6b7280; opacity:.95; text-decoration: line-through; font-weight:600; }
      .deal-row{ display:flex; align-items:center; gap:.75rem; flex-wrap:wrap; }
      .ai-chat-widget { position:fixed; bottom:30px; right:30px; width:60px; height:60px; background:linear-gradient(135deg,#8B4513,#D2B48C); border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 10px 25px rgba(0,0,0,.15); animation:pulse 2s infinite; z-index:1000; }
      @keyframes pulse { 0%{transform:scale(1)} 50%{transform:scale(1.1)} 100%{transform:scale(1)} }
      .hero-bg { background: radial-gradient(circle at 20% 80%, rgba(139,69,19,.08) 0%, transparent 55%), radial-gradient(circle at 80% 20%, rgba(210,180,140,.08) 0%, transparent 55%), radial-gradient(circle at 40% 40%, rgba(160,82,45,.08) 0%, transparent 55%); }
      .category-icon { width:80px; height:80px; background:linear-gradient(135deg, rgba(139,69,19,.10), rgba(160,82,45,.10)); border-radius:20px; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; transition:all .3s ease; }
      .category-icon:hover { transform: rotateY(180deg); background: linear-gradient(135deg, #8B4513, #D2B48C); }
      /* Light theme overrides for former dark text utilities */
      .text-white, .text-gray-100, .text-gray-200, .text-gray-300, .text-gray-400 { color:#0b0b0b !important; }
      .placeholder-gray-400::placeholder, .placeholder-gray-300::placeholder { color:#6b7280 !important; }
   </style>
</head>
<body>

<?php include 'header.php'; ?>

<?php if(!empty($message)): ?>
   <div class="fixed top-5 right-5 space-y-2 z-50">
      <?php foreach($message as $m): ?>
      <div class="message rounded-xl px-4 py-2 bg-white border border-gray-200 shadow-sm"><?php echo htmlspecialchars($m); ?></div>
      <?php endforeach; ?>
   </div>
<?php endif; ?>

<!-- Hero -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden hero-bg">
   <div class="absolute top-10 left-10 w-96 h-96 bg-gradient-to-r from-[rgba(139,69,19,0.08)] to-[rgba(210,180,140,0.08)] rounded-full blur-3xl floating-animation"></div>
   <div class="absolute bottom-10 right-10 w-80 h-80 bg-gradient-to-r from-[rgba(160,82,45,0.08)] to-[rgba(139,69,19,0.08)] rounded-full blur-3xl floating-animation" style="animation-delay: 1s;"></div>

   <div class="container mx-auto px-6 lg:px-12 grid lg:grid-cols-2 gap-16 items-center relative z-10">
      <div class="space-y-8">
         <div class="space-y-6">
            <h1 class="text-6xl lg:text-8xl font-bold leading-tight">
               <span class="gradient-text font-gaming">KANDU</span><br>
               <span>PINNAWALA</span>
            </h1>
            <div class="h-1 w-32 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] rounded-full"></div>
            <p class="text-xl leading-relaxed max-w-2xl">
               Discover traditional Sri Lankan handicrafts — heritage meets innovation.
            </p>
         </div>

         <div class="flex flex-col sm:flex-row gap-6">
            <a href="#promotions" class="group bg-gradient-to-r from-[#8B4513] to-[#D2B48C] px-8 py-4 rounded-full font-semibold text-lg hover-glow neon-glow" style="color:#fff;">
               <i class="fas fa-rocket mr-2"></i> EXPLORE NOW
            </a>
            <a href="#products" class="glass-effect px-8 py-4 rounded-full font-semibold text-lg hover-glow border border-[rgba(0,0,0,0.12)]">
               <i class="fas fa-play mr-2"></i> WATCH DEMO
            </a>
         </div>

         <div class="flex space-x-8 pt-8">
            <div class="text-center"><div class="text-3xl font-bold gradient-text">1000+</div><div class="text-sm text-gray-600">Products</div></div>
            <div class="text-center"><div class="text-3xl font-bold gradient-text">500+</div><div class="text-sm text-gray-600">Happy Customers</div></div>
            <div class="text-center"><div class="text-3xl font-bold gradient-text">50+</div><div class="text-sm text-gray-600">Artisans</div></div>
         </div>
      </div>

      <div class="relative">
         <div class="glass-effect p-8 rounded-3xl neon-glow">
            <div class="aspect-square rounded-2xl overflow-hidden">
               <img src="images/new.jpg" alt="Sri Lankan Handicrafts" class="w-full h-full object-cover">
            </div>
            <div class="absolute -top-4 -right-4 w-20 h-20 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] rounded-2xl flex items-center justify-center floating-animation" style="color:#fff;">
               <i class="fas fa-star text-2xl"></i>
            </div>
            <div class="absolute -bottom-4 -left-4 w-16 h-16 bg-gradient-to-r from-[#A0522D] to-[#8B4513] rounded-xl flex items-center justify-center floating-animation" style="animation-delay:.5s;color:#fff;">
               <i class="fas fa-heart text-xl"></i>
            </div>
         </div>
      </div>
   </div>
</section>

<!-- Categories -->
<section class="py-20 relative" id="categories">
   <div class="container mx-auto px-6 lg:px-12">
      <div class="text-center mb-16">
         <h2 class="text-5xl lg:text-6xl font-bold mb-6"><span class="gradient-text font-gaming">CATEGORIES</span></h2>
         <div class="h-1 w-24 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] rounded-full mx-auto mb-6"></div>
         <p class="text-xl text-gray-700 max-w-3xl mx-auto">Choose your favorite craft category.</p>
      </div>

      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
         <div class="glass-effect p-8 rounded-3xl text-center hover-glow cyber-border">
            <div class="category-icon"><i class="fas fa-tree text-3xl" style="color:#CD853F"></i></div>
            <h3 class="text-2xl font-bold mb-4 gradient-text">WOOD</h3>
            <p class="text-gray-700 mb-6 leading-relaxed">Handcrafted wooden masterpieces.</p>
            <a href="category.php?category=wood" class="inline-flex items-center font-semibold transition-colors" style="color:#A0522D"><i class="fas fa-arrow-right mr-2"></i>EXPLORE</a>
         </div>

         <div class="glass-effect p-8 rounded-3xl text-center hover-glow cyber-border">
            <div class="category-icon"><i class="fas fa-tshirt text-3xl" style="color:#deb887"></i></div>
            <h3 class="text-2xl font-bold mb-4 gradient-text">CLOTHES</h3>
            <p class="text-gray-700 mb-6 leading-relaxed">Traditional garments with heritage.</p>
            <a href="category.php?category=clothes" class="inline-flex items-center font-semibold transition-colors" style="color:#A0522D"><i class="fas fa-arrow-right mr-2"></i>EXPLORE</a>
         </div>

         <div class="glass-effect p-8 rounded-3xl text-center hover-glow cyber-border">
            <div class="category-icon"><i class="fas fa-palette text-3xl" style="color:#A0522D"></i></div>
            <h3 class="text-2xl font-bold mb-4 gradient-text">WALL ARTS</h3>
            <p class="text-gray-700 mb-6 leading-relaxed">Decor steeped in tradition.</p>
            <a href="category.php?category=wallarts" class="inline-flex items-center font-semibold transition-colors" style="color:#A0522D"><i class="fas fa-arrow-right mr-2"></i>EXPLORE</a>
         </div>

         <div class="glass-effect p-8 rounded-3xl text-center hover-glow cyber-border">
            <div class="category-icon"><i class="fas fa-medal text-3xl" style="color:#E0AA3E"></i></div>
            <h3 class="text-2xl font-bold mb-4 gradient-text">BRASS</h3>
            <p class="text-gray-700 mb-6 leading-relaxed">Exquisite brass artifacts.</p>
            <a href="category.php?category=brass" class="inline-flex items-center font-semibold transition-colors" style="color:#A0522D"><i class="fas fa-arrow-right mr-2"></i>EXPLORE</a>
         </div>
      </div>
   </div>
</section>

<!-- PROMOTIONS -->
<section id="promotions" class="py-20 relative">
  <div class="container mx-auto px-6 lg:px-12">
    <div class="text-center mb-16">
      <h2 class="text-5xl lg:text-6xl font-bold mb-6">
        <span class="gradient-text font-gaming">PROMOTIONS</span>
      </h2>
      <div class="h-1 w-24 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] rounded-full mx-auto mb-6"></div>
      <p class="text-xl text-gray-700 max-w-3xl mx-auto">Admin-managed deals.</p>
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

              $qty = (int)($promo['quantity'] ?? 0);
              $low = $qty > 0 && $qty <= 10;
              $unavailable = $qty <= 0;
      ?>
      <form action="" method="POST" class="group">
        <div class="product-card p-6 relative h-full flex flex-col">
          <div class="promo-badge"><?= $badgeText; ?> · SAVE <?= $save; ?>%</div>

          <div class="absolute top-6 left-6 text-white px-4 py-2 rounded-full font-bold text-sm z-10 neon-glow price-badge" style="background:linear-gradient(135deg,#8B4513,#D2B48C); color:#fff;">
            Rs <?= number_format($nowPrice, 2); ?>
          </div>

          <div class="absolute top-6 right-20 flex flex-col gap-2 z-10">
            <button type="submit" name="add_to_wishlist" class="w-11 h-11 glass-effect rounded-full flex items-center justify-center hover:text-black hover:bg-white transition-all">
              <i class="fas fa-heart"></i>
            </button>
            <a href="view_page.php?pid=<?= $promo['id']; ?>" class="w-11 h-11 glass-effect rounded-full flex items-center justify-center hover:text-black hover:bg-white transition-all">
              <i class="fas fa-eye"></i>
            </a>
          </div>

          <div class="aspect-square rounded-2xl overflow-hidden mb-6">
            <img src="uploaded_img/<?= htmlspecialchars($promo['image']); ?>" alt="<?= htmlspecialchars($promo['name']); ?>" class="w-full h-full object-cover">
          </div>

          <div class="space-y-4 mt-auto">
            <h3 class="text-xl product-title"><?= htmlspecialchars($promo['name']); ?></h3>

            <div class="deal-row">
              <span class="old-price">Was Rs <?= number_format($was, 2); ?></span>
              <span class="text-sm px-2 py-1 rounded-md glass-effect border border-black/10">Now Rs <?= number_format($nowPrice, 2); ?></span>
            </div>

            <?php if($unavailable): ?>
              <div class="text-sm px-2 py-1 rounded-md bg-red-100 border border-red-300 text-red-700 font-semibold inline-block">
                Unavailable
              </div>
            <?php elseif($low): ?>
              <div class="text-sm px-2 py-1 rounded-md bg-yellow-100 border border-yellow-300 text-yellow-800 font-semibold inline-block">
                Only <?= $qty; ?> left
              </div>
            <?php endif; ?>

            <input type="hidden" name="pid" value="<?= (int)$promo['id']; ?>">
            <input type="hidden" name="p_name" value="<?= htmlspecialchars($promo['name']); ?>">
            <input type="hidden" name="p_price" value="<?= $nowPrice; ?>">
            <input type="hidden" name="p_image" value="<?= htmlspecialchars($promo['image']); ?>">

            <div class="flex items-center gap-3">
              <label class="text-sm font-medium">QTY:</label>
              <input type="number" min="1" <?= $unavailable ? 'value="0"' : 'value="1"' ?> name="p_qty" <?= $unavailable ? 'disabled' : 'max="'.$qty.'"' ?> class="qty w-24 px-3 py-2 rounded-lg text-center focus:ring-2 focus:ring-[rgb(139,69,19)] transition-all">
            </div>

            <button type="submit" name="add_to_cart"
              <?= $unavailable
                    ? 'disabled aria-disabled="true" class="w-full bg-gray-200 cursor-not-allowed text-gray-500 py-3.5 rounded-xl font-semibold"'
                    : 'class="w-full bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white py-3.5 rounded-xl font-semibold hover-glow neon-glow transition-all duration-300 transform hover:scale-[1.02]" style="color:#fff;"' ?>>
              <i class="fas fa-shopping-cart mr-2"></i> <?= $unavailable ? 'UNAVAILABLE' : 'ADD TO CART'; ?>
            </button>
          </div>
        </div>
      </form>
      <?php
            }
          }
        } else {
          echo '<div class="col-span-full text-center py-16">
                  <div class="glass-effect p-12 rounded-3xl max-w-md mx-auto">
                    <i class="fas fa-tags text-6xl" style="color:#22c55e"></i>
                    <p class="text-2xl text-gray-700 font-medium">No promotions right now. Check back soon!</p>
                  </div>
                </div>';
        }
      ?>
    </div>

    <div class="text-center mt-12">
      <a href="shop.php" class="inline-flex items-center bg-gradient-to-r from-[#5D4037] to-[#4E342E] glass-effect px-8 py-4 rounded-full font-semibold hover-glow transition-all duration-300 transform hover:scale-105" style="color:#fff;">
        <i class="fas fa-store mr-3"></i> VIEW MORE DEALS <i class="fas fa-arrow-right ml-3"></i>
      </a>
    </div>
  </div>
</section>

<!-- FEATURED -->
<section id="products" class="py-20 relative">
   <div class="container mx-auto px-6 lg:px-12">
      <div class="text-center mb-16">
         <h2 class="text-5xl lg:text-6xl font-bold mb-6"><span class="gradient-text font-gaming">FEATURED</span></h2>
         <div class="h-1 w-24 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] rounded-full mx-auto mb-6"></div>
         <p class="text-xl text-gray-700 max-w-3xl mx-auto">Premium selection of authentic Sri Lankan handicrafts.</p>
      </div>

      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
         <?php
            $select_products = $conn->prepare("SELECT * FROM products ORDER BY id DESC LIMIT 6");
            $select_products->execute();
            if($select_products->rowCount() > 0){
               while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){
                  $qty = (int)($fetch_products['quantity'] ?? 0);
                  $low = $qty > 0 && $qty <= 10;
                  $unavailable = $qty <= 0;
         ?>
         <form action="" method="POST" class="group">
            <div class="product-card p-6 relative h-full flex flex-col">
               <div class="absolute top-6 left-6 px-4 py-2 rounded-full font-bold text-sm z-10 neon-glow price-badge" style="background:linear-gradient(135deg,#8B4513,#D2B48C); color:#fff;">
                  Rs <?= number_format((float)$fetch_products['price'], 2); ?>
               </div>

               <div class="absolute top-6 right-6 flex flex-col gap-2 z-10">
                  <button type="submit" name="add_to_wishlist" class="w-11 h-11 glass-effect rounded-full flex items-center justify-center hover:bg-white transition-all">
                     <i class="fas fa-heart"></i>
                  </button>
                  <a href="view_page.php?pid=<?= (int)$fetch_products['id']; ?>" class="w-11 h-11 glass-effect rounded-full flex items-center justify-center hover:bg-white transition-all">
                     <i class="fas fa-eye"></i>
                  </a>
               </div>

               <div class="aspect-square rounded-2xl overflow-hidden mb-6">
                  <img src="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>" alt="<?= htmlspecialchars($fetch_products['name']); ?>" class="w-full h-full object-cover">
               </div>

               <div class="space-y-4 mt-auto">
                  <h3 class="text-xl product-title"><?= htmlspecialchars($fetch_products['name']); ?></h3>

                  <?php if($unavailable): ?>
                    <div class="text-sm px-2 py-1 rounded-md bg-red-100 border border-red-300 text-red-700 font-semibold inline-block">
                      Unavailable
                    </div>
                  <?php elseif($low): ?>
                    <div class="text-sm px-2 py-1 rounded-md bg-yellow-100 border border-yellow-300 text-yellow-800 font-semibold inline-block">
                      Only <?= $qty; ?> left
                    </div>
                  <?php endif; ?>

                  <input type="hidden" name="pid" value="<?= (int)$fetch_products['id']; ?>">
                  <input type="hidden" name="p_name" value="<?= htmlspecialchars($fetch_products['name']); ?>">
                  <input type="hidden" name="p_price" value="<?= (float)$fetch_products['price']; ?>">
                  <input type="hidden" name="p_image" value="<?= htmlspecialchars($fetch_products['image']); ?>">

                  <div class="flex items-center gap-3">
                     <label class="text-sm font-medium">QTY:</label>
                     <input type="number" min="1" <?= $unavailable ? 'value="0"' : 'value="1"' ?> name="p_qty" <?= $unavailable ? 'disabled' : 'max="'.$qty.'"' ?> class="qty w-24 px-3 py-2 rounded-lg text-center focus:ring-2 focus:ring-[rgb(139,69,19)] transition-all">
                  </div>

                  <button type="submit" name="add_to_cart"
                    <?= $unavailable
                          ? 'disabled aria-disabled="true" class="w-full bg-gray-200 cursor-not-allowed text-gray-500 py-3.5 rounded-xl font-semibold"'
                          : 'class="w-full bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white py-3.5 rounded-xl font-semibold hover-glow neon-glow transition-all duration-300 transform hover:scale-[1.02]" style="color:#fff;"' ?>>
                     <i class="fas fa-shopping-cart mr-2"></i> <?= $unavailable ? 'UNAVAILABLE' : 'ADD TO CART'; ?>
                  </button>
               </div>
            </div>
         </form>
         <?php
               }
            }else{
               echo '<div class="col-span-full text-center py-16">
                     <div class="glass-effect p-12 rounded-3xl max-w-md mx-auto">
                        <i class="fas fa-box-open text-6xl" style="color:#CD853F"></i>
                        <p class="text-2xl text-gray-700 font-medium">No products available yet!</p>
                     </div>
                  </div>';
            }
         ?>
      </div>

      <div class="text-center mt-12">
         <a href="shop.php" class="inline-flex items-center bg-gradient-to-r from-[#5D4037] to-[#4E342E] glass-effect px-8 py-4 rounded-full font-semibold hover-glow transition-all duration-300 transform hover:scale-105" style="color:#fff;">
            <i class="fas fa-store mr-3"></i> VIEW ALL PRODUCTS <i class="fas fa-arrow-right ml-3"></i>
         </a>
      </div>
   </div>
</section>

<!-- AI Assistant (Advanced) -->
<div class="ai-chat-widget" onclick="toggleChat()" title="Ask Kandu Assistant">
  <i class="fas fa-robot text-xl" style="color:#fff;"></i>
</div>

<div id="chatInterface"
     class="fixed bottom-24 right-6 w-[380px] h-[520px] glass-effect rounded-3xl p-0 overflow-hidden transform translate-y-full opacity-0 transition-all duration-300 z-50"
     style="display:none;">
  <!-- Header -->
  <div class="px-5 py-4 border-b border-[rgba(0,0,0,0.12)] flex items-center justify-between bg-white">
    <div class="flex items-center gap-2">
      <div class="w-9 h-9 rounded-full bg-gradient-to-r from-[#8B4513] to-[#D2B48C] flex items-center justify-center">
        <i class="fas fa-robot text-white text-sm"></i>
      </div>
      <div>
        <div class="font-semibold">Kandu Assistant</div>
        <div class="text-xs text-gray-500">Ask about products, orders, reservations</div>
      </div>
    </div>
    <button onclick="toggleChat()" class="text-gray-500 hover:text-black">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <!-- Tabs -->
  <div class="px-5 pt-3 bg-white">
    <div class="flex gap-2">
      <button id="tabChat" class="kp-tab kp-tab-active">Chat</button>
      <button id="tabFAQ" class="kp-tab">FAQs</button>
      <button id="tabActions" class="kp-tab">Quick Actions</button>
    </div>
  </div>

  <!-- Body -->
  <div class="p-5 bg-white h-[360px] overflow-y-auto space-y-3" id="chatBody">
    <div class="kp-bot">
      Hi! I can help you <strong>find products</strong>, check <strong>order status</strong>, or make a <strong>reservation</strong>.
    </div>
    <div class="kp-tip">Try: “Search wooden mask”, “Track order KP-2025-00123”, or “Reserve a table for 4 on Friday 7pm”.</div>
  </div>

  <!-- Input -->
  <div class="p-4 bg-white border-t border-[rgba(0,0,0,0.12)]">
    <div class="flex gap-2">
      <input type="text" id="chatInput" class="flex-1 px-4 py-2 rounded-xl border border-[rgba(0,0,0,0.12)] focus:outline-none"
             placeholder="Type a message...">
      <button id="chatSend" class="px-4 py-2 rounded-xl bg-gradient-to-r from-[#8B4513] to-[#D2B48C]" style="color:#fff;">
        <i class="fas fa-paper-plane"></i>
      </button>
    </div>
    <div class="flex flex-wrap gap-2 mt-2">
      <button class="kp-chip" onclick="kpSuggest('Search wooden mask')">Search wooden mask</button>
      <button class="kp-chip" onclick="kpSuggest('Track order KP-2025-00123')">Track order</button>
      <button class="kp-chip" onclick="kpSuggest('Show low stock items')">Low stock items</button>
      <button class="kp-chip" onclick="kpSuggest('Reserve table for 2 tonight 7pm')">Make reservation</button>
    </div>
  </div>
</div>

<style>
  .kp-tab { padding:.4rem .8rem; border:1px solid rgba(0,0,0,.12); border-radius:9999px; font-size:.85rem; background:#fff; }
  .kp-tab-active { background:linear-gradient(135deg,#8B4513,#D2B48C); color:#fff; border-color:transparent; }
  .kp-bot { background:#f5f5f5; border:1px solid rgba(0,0,0,.12); padding:.6rem .8rem; border-radius:10px; }
  .kp-user { background:#fff; border:1px solid rgba(0,0,0,.12); padding:.6rem .8rem; border-radius:10px; text-align:right; }
  .kp-tip  { font-size:.8rem; color:#6b7280; }
  .kp-chip{ font-size:.8rem; padding:.35rem .7rem; border:1px solid rgba(0,0,0,.12); border-radius:9999px; background:#fff; }
</style>

<?php include 'about.php'; ?>

<!-- Reviews -->
<section id="customer-reviews" class="py-20 relative">
  <div class="container mx-auto px-6 lg:px-12">
    <div class="text-center mb-12">
      <h2 class="text-4xl lg:text-5xl font-bold mb-4">What Customers Say</h2>
      <p class="text-lg text-gray-700 max-w-2xl mx-auto">Latest verified feedback.</p>
      <div class="h-1 w-24 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] rounded-full mx-auto mt-6"></div>
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
        <div class="rounded-3xl overflow-hidden shadow-lg border border-black/10 bg-white p-6">
          <div class="flex items-center gap-4 mb-3">
            <div class="w-12 h-12 rounded-full bg-gradient-to-r from-[#8B4513] to-[#D2B48C] flex items-center justify-center" style="color:#fff;">
              <?php echo strtoupper(substr($rName,0,1)); ?>
            </div>
            <div>
              <h3 class="text-lg font-bold leading-tight"><?php echo $rName; ?></h3>
              <div class="text-base" style="color:#E0AA3E">
                <?php for($i=1;$i<=5;$i++){ echo $i <= $rRate ? '<i class="fas fa-star mr-0.5"></i>' : '<i class="far fa-star mr-0.5"></i>'; } ?>
              </div>
            </div>
          </div>

          <h4 class="font-semibold mb-2"><?php echo $rTitle; ?></h4>
          <p class="text-gray-700 leading-relaxed mb-3"><?php echo $rMsg; ?></p>

          <?php if (!empty($rImg)): ?>
            <div class="rounded-xl overflow-hidden border border-black/10">
              <img src="<?php echo htmlspecialchars($rImg); ?>" alt="review image" class="w-full h-40 object-cover">
            </div>
          <?php endif; ?>
        </div>
      <?php
            endwhile;
          else:
            echo '<div class="col-span-full text-center text-gray-700">No customer reviews yet.</div>';
          endif;
        } catch (Exception $e) {
          echo '<div class="col-span-full text-center text-red-600">Failed to load reviews.</div>';
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
      <p class="text-lg text-gray-700 max-w-2xl mx-auto">Share your experience with Kandu Pinnawala.</p>
      <div class="h-1 w-24 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] rounded-full mx-auto mt-6"></div>
    </div>

    <form action="" method="POST" enctype="multipart/form-data" class="max-w-4xl mx-auto">
      <div class="glass-effect rounded-3xl p-8 md:p-10 shadow-xl">
        <div class="grid md:grid-cols-2 gap-6">
          <div><label class="block text-sm font-semibold mb-2">Full Name</label><input type="text" name="review_name" required class="w-full px-4 py-3 rounded-xl bg-white border border-black/10 focus:outline-none focus:ring-2 focus:ring-[rgba(139,69,19,0.7)]" placeholder="e.g., Nimal Perera"></div>
          <div><label class="block text-sm font-semibold mb-2">Email</label><input type="email" name="review_email" required class="w-full px-4 py-3 rounded-xl bg-white border border-black/10 focus:outline-none focus:ring-2 focus:ring-[rgba(139,69,19,0.7)]" placeholder="you@example.com"></div>
          <div><label class="block text-sm font-semibold mb-2">Order ID (optional)</label><input type="text" name="review_order" class="w-full px-4 py-3 rounded-xl bg-white border border-black/10 focus:outline-none focus:ring-2 focus:ring-[rgba(139,69,19,0.7)]" placeholder="e.g., KP-2025-00123"></div>
          <div><label class="block text-sm font-semibold mb-2">Review Title</label><input type="text" name="review_title" required class="w-full px-4 py-3 rounded-xl bg-white border border-black/10 focus:outline-none focus:ring-2 focus:ring-[rgba(139,69,19,0.7)]" placeholder="e.g., Beautiful craftsmanship!"></div>
        </div>

        <div class="mt-6">
          <label class="block text-sm font-semibold mb-2">Rating</label>
          <div class="flex items-center gap-2" id="ratingStars" data-selected="0">
            <button type="button" aria-label="1 star" data-value="1" class="star-btn w-10 h-10 rounded-full bg-white border border-black/10 flex items-center justify-center hover:bg-gray-50"><i class="fa-solid fa-star"></i></button>
            <button type="button" aria-label="2 stars" data-value="2" class="star-btn w-10 h-10 rounded-full bg-white border border-black/10 flex items-center justify-center hover:bg-gray-50"><i class="fa-solid fa-star"></i></button>
            <button type="button" aria-label="3 stars" data-value="3" class="star-btn w-10 h-10 rounded-full bg-white border border-black/10 flex items-center justify-center hover:bg-gray-50"><i class="fa-solid fa-star"></i></button>
            <button type="button" aria-label="4 stars" data-value="4" class="star-btn w-10 h-10 rounded-full bg-white border border-black/10 flex items-center justify-center hover:bg-gray-50"><i class="fa-solid fa-star"></i></button>
            <button type="button" aria-label="5 stars" data-value="5" class="star-btn w-10 h-10 rounded-full bg-white border border-black/10 flex items-center justify-center hover:bg-gray-50"><i class="fa-solid fa-star"></i></button>
          </div>
          <input type="hidden" name="review_rating" id="review_rating" value="0">
        </div>

        <div class="mt-6">
          <label class="block text-sm font-semibold mb-2">Your Review</label>
          <textarea name="review_message" rows="5" required class="w-full px-4 py-3 rounded-xl bg-white border border-black/10 focus:outline-none focus:ring-2 focus:ring-[rgba(139,69,19,0.7)]" placeholder="Tell us about quality, delivery, and your experience."></textarea>
          <div class="flex items-center justify-between mt-2 text-xs text-gray-600"><span>Be respectful. Keep it helpful.</span><span id="charCount">0/1000</span></div>
        </div>

        <div class="mt-6 grid md:grid-cols-2 gap-6">
          <div><label class="block text-sm font-semibold mb-2">Add a photo (optional)</label><input type="file" name="review_image" accept="image/*" class="w-full file:mr-4 file:rounded-lg file:border-0 file:px-4 file:py-2 file:bg-gradient-to-r file:from-[#8B4513] file:to-[#D2B48C] file:text-white file:cursor-pointer rounded-xl bg-white border border-black/10"></div>
          <div class="flex items-center gap-2 mt-8 md:mt-0"><input id="agree" type="checkbox" required class="w-4 h-4 rounded border-black/20 bg-white"><label for="agree" class="text-sm text-gray-700">I agree to display my review.</label></div>
        </div>

        <div class="mt-8 flex items-center gap-4">
          <button type="submit" class="inline-flex items-center bg-gradient-to-r from-[#8B4513] to-[#D2B48C] px-8 py-3 rounded-xl font-semibold hover:shadow-xl hover:shadow-[rgba(139,69,19,0.15)] transition" style="color:#fff;">
            <i class="fa-solid fa-paper-plane mr-2"></i> Submit Review
          </button>
          <span class="text-xs text-gray-600">*This form is UI only.</span>
        </div>
      </div>
    </form>
  </div>
</section>

<?php include 'footer.php'; ?>

<script>
   function toggleChat() {
      const chatInterface = document.getElementById('chatInterface');
      if (chatInterface.style.display === 'none') {
         chatInterface.style.display = 'block';
         setTimeout(() => { chatInterface.classList.remove('translate-y-full','opacity-0'); }, 10);
      } else {
         chatInterface.classList.add('translate-y-full','opacity-0');
         setTimeout(() => { chatInterface.style.display = 'none'; }, 300);
      }
   }
   function kpSuggest(text){ const i=document.getElementById('chatInput'); i.value=text; i.focus(); }

   // Tabs
   const tabChat = document.getElementById('tabChat');
   const tabFAQ  = document.getElementById('tabFAQ');
   const tabAct  = document.getElementById('tabActions');
   const chatBody= document.getElementById('chatBody');
   function setActiveTab(btn){
     [tabChat,tabFAQ,tabAct].forEach(b=>b.classList.remove('kp-tab-active'));
     btn.classList.add('kp-tab-active');
   }
   tabFAQ?.addEventListener('click', async ()=>{
     setActiveTab(tabFAQ);
     chatBody.innerHTML = '<div class="kp-bot">Loading FAQs…</div>';
     const res = await fetch('ai_assistant.php', { method:'POST', headers:{'Content-Type':'application/json'},
                   body: JSON.stringify({ action:'faq' })});
     const data = await res.json();
     chatBody.innerHTML = data.html || '<div class="kp-bot">No FAQs yet.</div>';
   });
   tabAct?.addEventListener('click', async ()=>{
     setActiveTab(tabAct);
     chatBody.innerHTML = `
       <div class="kp-bot"><strong>Quick Actions</strong></div>
       <div class="mt-2 grid gap-2">
         <button class="kp-chip" onclick="kpRun('low_stock')">Show low stock items</button>
         <button class="kp-chip" onclick="kpRun('top_products')">Top products</button>
         <button class="kp-chip" onclick="kpRun('latest_deals')">Latest deals</button>
       </div>`;
   });
   tabChat?.addEventListener('click', ()=>{
     setActiveTab(tabChat);
     chatBody.innerHTML = `
       <div class="kp-bot">Hi! Ask me about products, orders, or reservations.</div>
       <div class="kp-tip">e.g. “Search wooden mask”, “Track order KP-2025-00123”.</div>`;
   });

   async function kpRun(action, payload={}){
     const res = await fetch('ai_assistant.php', {
       method:'POST', headers:{'Content-Type':'application/json'},
       body: JSON.stringify({ action, ...payload })
     });
     const data = await res.json();
     chatBody.insertAdjacentHTML('beforeend', `<div class="kp-bot">${data.html || data.message || 'Done.'}</div>`);
     chatBody.scrollTop = chatBody.scrollHeight;
   }

   const sendBtn = document.getElementById('chatSend');
   const input   = document.getElementById('chatInput');
   function appendUser(msg){ chatBody.insertAdjacentHTML('beforeend', `<div class="kp-user">${escapeHtml(msg)}</div>`); chatBody.scrollTop = chatBody.scrollHeight; }
   function appendBot(html){ chatBody.insertAdjacentHTML('beforeend', `<div class="kp-bot">${html}</div>`); chatBody.scrollTop = chatBody.scrollHeight; }
   function escapeHtml(s){ const d=document.createElement('div'); d.innerText=s; return d.innerHTML; }

   function detectIntent(text){
     const t = text.toLowerCase();
     const orderIdMatch = t.match(/(?:order|kp-)\s*([a-z0-9\-]+)/i);
     if(t.includes('track') || t.includes('status')) return { action:'order_status', order_id: orderIdMatch ? orderIdMatch[1] : text.trim() };
     if(t.includes('low stock')) return { action:'low_stock' };
     if(t.includes('reserve') || t.includes('reservation') || t.includes('book table')) return { action:'reservation_parse', text };
     if(t.startsWith('search ') || t.includes('find ') || t.includes('show ')) return { action:'search_products', q: text.replace(/^search\s+/i,'').trim() };
     return { action:'faq_query', q: text };
   }

   async function sendChat(){
     const text = (input.value || '').trim();
     if(!text) return;
     appendUser(text);
     input.value = '';
     const intent = detectIntent(text);
     const res = await fetch('ai_assistant.php', {
       method:'POST', headers:{'Content-Type':'application/json'},
       body: JSON.stringify(intent)
     });
     const data = await res.json();
     appendBot(data.html || data.message || 'I’m not sure yet, but I’m learning!');
   }
   sendBtn?.addEventListener('click', sendChat);
   input?.addEventListener('keydown', (e)=>{ if(e.key==='Enter') sendChat(); });

   // Smooth anchors
   document.querySelectorAll('a[href^="#"]').forEach(a=>{
      a.addEventListener('click',e=>{ e.preventDefault(); document.querySelector(a.getAttribute('href')).scrollIntoView({behavior:'smooth'}); });
   });

   // Rating widget + char counter
   (function(){
      const c = document.getElementById('ratingStars'); const h = document.getElementById('review_rating');
      if(!c||!h) return; const stars=[...c.querySelectorAll('.star-btn')];
      const active='linear-gradient(135deg, #8B4513, #D2B48C)'; const inactive='#fff';
      function paint(n){ stars.forEach((b,i)=>{ b.style.background=(i<n)?active:inactive; b.style.borderColor='rgba(0,0,0,.12)'; b.querySelector('i').style.color=(i<n)?'#fff':'#999'; }); }
      stars.forEach(b=>{ b.addEventListener('click',()=>{ const v=Number(b.dataset.value||0); h.value=v; c.dataset.selected=v; paint(v); });
                         b.addEventListener('mouseenter',()=>paint(Number(b.dataset.value||0)));
                         b.addEventListener('mouseleave',()=>paint(Number(c.dataset.selected||0)));});
      paint(0);
   })();
   (function(){
      const ta=document.querySelector('textarea[name="review_message"]'); const cc=document.getElementById('charCount'); if(!ta||!cc) return; const max=1000;
      ta.addEventListener('input',()=>{ const len=ta.value.length; cc.textContent = `${len}/${max}`; if(len>max){ ta.value=ta.value.slice(0,max); } });
   })();
</script>
<script src="js/script.js"></script>
</body>
</html>
