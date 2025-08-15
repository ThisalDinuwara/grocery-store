<?php

@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];
if(!isset($user_id)){
   header('location:login.php');
}

if(isset($_POST['add_to_wishlist'])){
   $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
   $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
   $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
   $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);

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

if(isset($_POST['add_to_cart'])){
   $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
   $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
   $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
   $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);
   $p_qty = filter_var($_POST['p_qty'], FILTER_SANITIZE_STRING);

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
   $rev_name   = trim(filter_var($_POST['review_name']  ?? '', FILTER_SANITIZE_STRING));
   $rev_email  = trim(filter_var($_POST['review_email'] ?? '', FILTER_SANITIZE_EMAIL));
   $rev_order  = trim(filter_var($_POST['review_order'] ?? '', FILTER_SANITIZE_STRING));
   $rev_title  = trim(filter_var($_POST['review_title'] ?? '', FILTER_SANITIZE_STRING));
   $rev_msg    = trim(filter_var($_POST['review_message'] ?? '', FILTER_SANITIZE_STRING));
   $rev_rating = (int)$_POST['review_rating'] ?? 0;

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
   <meta charset="UTF-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <title>Kandu Pinnawala - Premium Sri Lankan Handicrafts</title>

   <!-- Tailwind CDN -->
   <script src="https://cdn.tailwindcss.com"></script>
   <script>
      tailwind.config = {
         theme: {
            extend: {
               colors: {
                  primary:  '#FF7F00', // vivid orange
                  secondary:'#FF4500', // orange-red
                  accent:   '#FFA500', // classic orange
                  dark:     '#1A1200', // deep warm black
                  darker:   '#0D0900'  // darkest
               },
               fontFamily: { gaming: ['Orbitron','monospace'] }
            }
         }
      }
   </script>

   <!-- Icons + Fonts -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

   <style>
      * { margin:0; padding:0; box-sizing:border-box; }

      /* ===== Larger, fixed typography ===== */
      html { font-size: 17px; } /* 1rem ≈ 17px */
      h1 { font-size: 3.3rem; line-height: 1.2; }  /* ~56px */
      h2 { font-size: 2.8rem; line-height: 1.25; } /* ~48px */
      h3 { font-size: 2.3rem; line-height: 1.3; }  /* ~40px */
      h4 { font-size: 1.7rem; line-height: 1.35;} /* ~28px */
      p, label, input, button, a, li { font-size: 1.25rem; line-height:1.65; } /* ~20px */

      body{
         font-family: 'Inter', sans-serif;
         background: linear-gradient(135deg, #0D0900 0%, #1A1200 50%, #221700 100%);
         color:#fff;
         overflow-x:hidden;
      }

      .neon-glow {
         box-shadow: 0 0 20px rgba(255,127,0,.35), 0 0 40px rgba(255,69,0,.28), 0 0 60px rgba(255,165,0,.22);
      }
      .glass-effect { background: rgba(255,255,255,.06); backdrop-filter: blur(10px); border:1px solid rgba(255,255,255,.15); }
      .hover-glow:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(255,127,0,.35); transition: all .3s ease; }

      .floating-animation { animation: floating 3s ease-in-out infinite; }
      @keyframes floating { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }

      .gradient-text{
         background: linear-gradient(45deg, #FF7F00, #FF4500, #FFA500);
         -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
      }

      .cyber-border{
         position:relative; border:2px solid transparent;
         background: linear-gradient(135deg, rgba(255,127,0,.22), rgba(255,165,0,.22)) border-box;
         -webkit-mask:linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
         -webkit-mask-composite: xor; mask-composite: exclude;
      }

      .message{
         position:fixed; top:20px; right:20px; z-index:1000;
         background: rgba(255,127,0,.9); color:#1A1200;
         padding:15px 20px; border-radius:10px; border:1px solid rgba(255,255,255,.2);
         animation: slideIn .3s ease;
      }
      @keyframes slideIn { from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }

      .ai-chat-widget{
         position:fixed; bottom:30px; right:30px; width:60px; height:60px; z-index:1000;
         background: linear-gradient(135deg, #FF7F00, #FF4500); color:#1A1200;
         border-radius:50%; display:flex; align-items:center; justify-content:center;
         cursor:pointer; box-shadow:0 10px 25px rgba(255,127,0,.4); animation:pulse 2s infinite;
      }
      @keyframes pulse { 0%{transform:scale(1)} 50%{transform:scale(1.1)} 100%{transform:scale(1)} }

      .hero-bg{
         background:
           radial-gradient(circle at 20% 80%, rgba(255,127,0,.25) 0%, transparent 55%),
           radial-gradient(circle at 80% 20%, rgba(255,69,0,.25) 0%, transparent 55%),
           radial-gradient(circle at 40% 40%, rgba(255,165,0,.25) 0%, transparent 55%);
      }

      .category-icon{
         width:80px; height:80px; border-radius:20px;
         background: linear-gradient(135deg, rgba(255,127,0,.18), rgba(255,165,0,.18));
         display:flex; align-items:center; justify-content:center;
         margin:0 auto 20px; transition:all .3s ease;
      }
      .category-icon:hover{ transform:rotateY(180deg); background: linear-gradient(135deg, #FF7F00, #FFA500); }

      /* ===== Product grid ===== */
      .product-card{
         background: linear-gradient(180deg, rgba(26,18,0,.95), rgba(26,18,0,.86));
         border:1px solid rgba(255,205,150,.28);
         border-radius:22px; backdrop-filter: blur(16px);
         transition: transform .4s ease, box-shadow .4s ease, border-color .4s ease;
      }
      .product-card:hover{
         transform: translateY(-10px) scale(1.02);
         border-color: rgba(255,205,150,.58);
         box-shadow: 0 22px 48px rgba(255,127,0,.28);
      }
      .product-card .aspect-square{
         position:relative; border-radius:18px; overflow:hidden;
         border:1px solid rgba(255,205,150,.25);
         background: radial-gradient(600px 120px at 20% 0%, rgba(255,165,0,.18), transparent 60%);
      }
      .product-card img{ transition: transform .6s ease; }
      .group:hover .product-card img{ transform: scale(1.07); }

      .price-badge{
         font-size:1.05rem; letter-spacing:.3px; padding:.6rem 1rem; color:#1A1200;
         border:1px solid rgba(255,255,255,.18);
         box-shadow: 0 6px 18px rgba(255,127,0,.25);
         background: linear-gradient(90deg, #FF7F00, #FF4500);
      }

      .product-title{ font-weight:800; letter-spacing:.2px; line-height:1.25; color:#FFF3E0; text-shadow:0 1px 0 rgba(0,0,0,.35); }
      .product-card label{ color:#FFE4C4; font-weight:600; }
      .product-card .qty{ background: rgba(255,255,255,.08); }
      .product-card .qty:focus{ outline:none; box-shadow:0 0 0 3px rgba(255,127,0,.35); }
   </style>
</head>
<body>

<?php include 'header.php'; ?>

<!-- Hero Section -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden hero-bg">
   <div class="absolute top-10 left-10 w-96 h-96 rounded-full blur-3xl floating-animation opacity-30" style="background:linear-gradient(135deg,#FF7F00,#FF4500)"></div>
   <div class="absolute bottom-10 right-10 w-80 h-80 rounded-full blur-3xl floating-animation opacity-30" style="animation-delay:1s;background:linear-gradient(135deg,#FFA500,#FF7F00)"></div>

   <div class="container mx-auto px-6 lg:px-12 grid lg:grid-cols-2 gap-16 items-center relative z-10">
      <!-- Content -->
      <div class="space-y-8">
         <div class="space-y-6">
            <h1 class="text-6xl lg:text-8xl font-bold leading-tight">
               <span class="gradient-text font-gaming">KANDU</span><br>
               <span class="text-white">PINNAWALA</span>
            </h1>
            <div class="h-1 w-40 rounded-full" style="background:linear-gradient(90deg,#FF7F00,#FF4500)"></div>
            <p class="text-xl text-gray-200 leading-relaxed max-w-2xl">
               Discover the ultimate collection of traditional Sri Lankan handicrafts — now in a warm, modern orange theme.
            </p>
         </div>

         <div class="flex flex-col sm:flex-row gap-6">
            <button class="group text-black px-9 py-4 rounded-full font-semibold text-lg hover-glow neon-glow" style="background:linear-gradient(90deg,#FF7F00,#FF4500)">
               <i class="fas fa-rocket mr-2"></i> EXPLORE NOW
               <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
            </button>
            <button class="glass-effect text-white px-9 py-4 rounded-full font-semibold text-lg hover-glow border border-white/30">
               <i class="fas fa-play mr-2"></i> WATCH DEMO
            </button>
         </div>

         <!-- Stats -->
         <div class="flex space-x-8 pt-6">
            <div class="text-center"><div class="text-4xl font-bold gradient-text">1000+</div><div class="text-gray-300 text-sm">Products</div></div>
            <div class="text-center"><div class="text-4xl font-bold gradient-text">500+</div><div class="text-gray-300 text-sm">Happy Customers</div></div>
            <div class="text-center"><div class="text-4xl font-bold gradient-text">50+</div><div class="text-gray-300 text-sm">Artisans</div></div>
         </div>
      </div>

      <!-- Hero Visual -->
      <div class="relative">
         <div class="glass-effect p-8 rounded-3xl neon-glow">
            <div class="aspect-square rounded-2xl overflow-hidden">
               <img src="images/new.jpg" alt="Sri Lankan Handicrafts" class="w-full h-full object-cover">
            </div>
            <div class="absolute -top-4 -right-4 w-20 h-20 rounded-2xl flex items-center justify-center floating-animation"
                 style="background:linear-gradient(135deg,#FF7F00,#FF4500)"><i class="fas fa-star text-2xl text-black"></i></div>
            <div class="absolute -bottom-4 -left-4 w-16 h-16 rounded-xl flex items-center justify-center floating-animation" style="animation-delay:.5s;background:linear-gradient(135deg,#FFA500,#FF7F00)"><i class="fas fa-heart text-xl text-black"></i></div>
         </div>
      </div>
   </div>
</section>

<!-- Categories -->
<section class="py-20 relative">
   <div class="container mx-auto px-6 lg:px-12">
      <div class="text-center mb-16">
         <h2 class="font-bold mb-6"><span class="gradient-text font-gaming">CATEGORIES</span></h2>
         <div class="h-1 w-28 rounded-full mx-auto mb-6" style="background:linear-gradient(90deg,#FF7F00,#FF4500)"></div>
         <p class="text-xl text-gray-200 max-w-3xl mx-auto">Choose your craft category and dive into traditional Sri Lankan artistry</p>
      </div>

      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
         <div class="glass-effect p-8 rounded-3xl text-center hover-glow cyber-border">
            <div class="category-icon"><i class="fas fa-tree text-3xl" style="color:#FFA500"></i></div>
            <h3 class="text-2xl font-bold mb-4 gradient-text">WOOD</h3>
            <p class="text-gray-200 mb-6 leading-relaxed">Handcrafted wooden masterpieces showcasing traditional artistry</p>
            <a href="category.php?category=wood" class="inline-flex items-center font-semibold" style="color:#FFA500"><i class="fas fa-arrow-right mr-2"></i>EXPLORE</a>
         </div>

         <div class="glass-effect p-8 rounded-3xl text-center hover-glow cyber-border">
            <div class="category-icon"><i class="fas fa-tshirt text-3xl" style="color:#FF7F00"></i></div>
            <h3 class="text-2xl font-bold mb-4 gradient-text">CLOTHES</h3>
            <p class="text-gray-200 mb-6 leading-relaxed">Traditional garments woven with cultural heritage</p>
            <a href="category.php?category=clothes" class="inline-flex items-center font-semibold" style="color:#FFA500"><i class="fas fa-arrow-right mr-2"></i>EXPLORE</a>
         </div>

         <div class="glass-effect p-8 rounded-3xl text-center hover-glow cyber-border">
            <div class="category-icon"><i class="fas fa-palette text-3xl" style="color:#FF4500"></i></div>
            <h3 class="text-2xl font-bold mb-4 gradient-text">WALL ARTS</h3>
            <p class="text-gray-200 mb-6 leading-relaxed">Beautiful decorations reflecting artistic traditions</p>
            <a href="category.php?category=wallarts" class="inline-flex items-center font-semibold" style="color:#FFA500"><i class="fas fa-arrow-right mr-2"></i>EXPLORE</a>
         </div>

         <div class="glass-effect p-8 rounded-3xl text-center hover-glow cyber-border">
            <div class="category-icon"><i class="fas fa-medal text-3xl" style="color:#FFD8A8"></i></div>
            <h3 class="text-2xl font-bold mb-4 gradient-text">BRASS</h3>
            <p class="text-gray-200 mb-6 leading-relaxed">Exquisite brass items by skilled traditional artisans</p>
            <a href="category.php?category=brass" class="inline-flex items-center font-semibold" style="color:#FFA500"><i class="fas fa-arrow-right mr-2"></i>EXPLORE</a>
         </div>
      </div>
   </div>
</section>

<!-- Featured Products -->
<section id="products" class="py-20 relative">
   <div class="container mx-auto px-6 lg:px-12">
      <div class="text-center mb-16">
         <h2 class="font-bold mb-6"><span class="gradient-text font-gaming">FEATURED</span></h2>
         <div class="h-1 w-28 rounded-full mx-auto mb-6" style="background:linear-gradient(90deg,#FF7F00,#FF4500)"></div>
         <p class="text-xl text-gray-200 max-w-3xl mx-auto">Discover our premium collection of authentic Sri Lankan handicrafts</p>
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
               <div class="price-badge absolute top-6 left-6 z-10">Rs <?= $fetch_products['price']; ?></div>

               <div class="absolute top-6 right-6 flex flex-col gap-2 z-10">
                  <button type="submit" name="add_to_wishlist" class="w-11 h-11 glass-effect rounded-full flex items-center justify-center hover:text-black transition-all"
                          onmouseover="this.style.background='linear-gradient(90deg,#FF7F00,#FF4500)';this.style.color='#000';"
                          onmouseout="this.style.background='';this.style.color='';">
                     <i class="fas fa-heart"></i>
                  </button>
                  <a href="view_page.php?pid=<?= $fetch_products['id']; ?>" class="w-11 h-11 glass-effect rounded-full flex items-center justify-center hover:text-black transition-all"
                     onmouseover="this.style.background='linear-gradient(90deg,#FF7F00,#FF4500)';this.style.color='#000';"
                     onmouseout="this.style.background='';this.style.color='';">
                     <i class="fas fa-eye"></i>
                  </a>
               </div>

               <div class="aspect-square rounded-2xl overflow-hidden mb-6">
                  <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="<?= $fetch_products['name']; ?>" class="w-full h-full object-cover">
               </div>

               <div class="space-y-4 mt-auto">
                  <h3 class="text-xl product-title"><?= $fetch_products['name']; ?></h3>

                  <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
                  <input type="hidden" name="p_name" value="<?= $fetch_products['name']; ?>">
                  <input type="hidden" name="p_price" value="<?= $fetch_products['price']; ?>">
                  <input type="hidden" name="p_image" value="<?= $fetch_products['image']; ?>">

                  <div class="flex items-center gap-3">
                     <label class="text-sm font-medium">QTY:</label>
                     <input type="number" min="1" value="1" name="p_qty" class="qty w-28 px-3 py-2 glass-effect rounded-lg text-white text-center focus:ring-2" style="--tw-ring-color: rgba(255,127,0,.7)">
                  </div>

                  <button type="submit" name="add_to_cart" class="w-full text-black py-3.5 rounded-xl font-semibold hover-glow neon-glow transition-all duration-300 transform hover:scale-[1.02]" style="background:linear-gradient(90deg,#FF7F00,#FF4500)">
                     <i class="fas fa-shopping-cart mr-2"></i> ADD TO CART
                  </button>
               </div>
            </div>
         </form>
         <?php
               }
            } else {
               echo '<div class="col-span-full text-center py-16">
                        <div class="glass-effect p-12 rounded-3xl max-w-md mx-auto">
                           <i class="fas fa-box-open text-6xl" style="color:#FFA500"></i>
                           <p class="text-2xl text-gray-200 font-medium">No products available yet!</p>
                        </div>
                     </div>';
            }
         ?>
      </div>

      <div class="text-center mt-12">
         <a href="shop.php" class="inline-flex items-center glass-effect text-white px-9 py-4 rounded-full font-semibold text-lg hover-glow transition-all duration-300 transform hover:scale-105" style="background:linear-gradient(90deg,#FFA500,#FF7F00)">
            <i class="fas fa-store mr-3"></i> VIEW ALL PRODUCTS
            <i class="fas fa-arrow-right ml-3"></i>
         </a>
      </div>
   </div>
</section>

<!-- Stats -->
<section class="py-20 relative">
   <div class="container mx-auto px-6 lg:px-12">
      <div class="glass-effect rounded-3xl p-12">
         <div class="grid md:grid-cols-4 gap-8 text-center">
            <div><div class="text-5xl font-bold gradient-text mb-2">1000+</div><div class="text-gray-300">Premium Products</div></div>
            <div><div class="text-5xl font-bold gradient-text mb-2">500+</div><div class="text-gray-300">Happy Customers</div></div>
            <div><div class="text-5xl font-bold gradient-text mb-2">50+</div><div class="text-gray-300">Master Artisans</div></div>
            <div><div class="text-5xl font-bold gradient-text mb-2">24/7</div><div class="text-gray-300">Customer Support</div></div>
         </div>
      </div>
   </div>
</section>

<!-- AI Chat Widget -->
<div class="ai-chat-widget" onclick="toggleChat()"><i class="fas fa-robot text-2xl"></i></div>

<!-- Chat Interface -->
<div id="chatInterface" class="fixed bottom-20 right-8 w-80 h-96 glass-effect rounded-3xl p-6 transform translate-y-full opacity-0 transition-all duration-300 z-50" style="display:none;">
   <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-bold gradient-text">AI Assistant</h3>
      <button onclick="toggleChat()" class="text-gray-300 hover:text-white"><i class="fas fa-times"></i></button>
   </div>
   <div class="h-64 overflow-y-auto mb-4 space-y-3" id="chatMessages">
      <div class="p-3 rounded-lg" style="background:rgba(255,127,0,.15)">
         <p class="text-sm text-gray-200">Hello! I'm here to help you find the perfect Sri Lankan handicraft. What are you looking for?</p>
      </div>
   </div>
   <div class="flex gap-2">
      <input type="text" id="chatInput" placeholder="Type your message..." class="flex-1 px-4 py-2 glass-effect rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2" style="--tw-ring-color: rgba(255,127,0,.7)">
      <button onclick="sendMessage()" class="px-4 py-2 rounded-lg hover-glow text-black" style="background:linear-gradient(90deg,#FF7F00,#FF4500)"><i class="fas fa-paper-plane"></i></button>
   </div>
</div>

<?php include 'about.php'; ?>

<!-- Customer Reviews -->
<section id="customer-reviews" class="py-20 relative">
  <div class="container mx-auto px-6 lg:px-12">
    <div class="text-center mb-12">
      <h2 class="text-4xl lg:text-5xl font-bold mb-4 text-white">What Customers Say</h2>
      <p class="text-lg text-gray-200 max-w-2xl mx-auto">Latest verified feedback from our community.</p>
      <div class="h-1 w-28 rounded-full mx-auto mt-6" style="background:linear-gradient(90deg,#FF7F00,#FF4500)"></div>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
      <?php
        try {
          $revQ = $conn->prepare("SELECT name, title, rating, message, image_path, created_at
                                  FROM reviews
                                  WHERE status='approved'
                                  ORDER BY created_at DESC
                                  LIMIT 6");
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
              <h3 class="text-lg font-bold text-white leading-tight"><?php echo $rName; ?></h3>
              <div class="text-base" style="color:#FFA500">
                <?php for($i=1;$i<=5;$i++){ echo $i <= $rRate ? '<i class="fas fa-star mr-0.5"></i>' : '<i class="far fa-star mr-0.5"></i>'; } ?>
              </div>
            </div>
          </div>

          <h4 class="text-white font-semibold mb-2"><?php echo $rTitle; ?></h4>
          <p class="text-gray-200 leading-relaxed mb-3"><?php echo $rMsg; ?></p>

          <?php if (!empty($rImg)): ?>
            <div class="rounded-xl overflow-hidden border border-white/10">
              <img src="<?php echo htmlspecialchars($rImg); ?>" alt="review image" class="w-full h-40 object-cover">
            </div>
          <?php endif; ?>
        </div>
      <?php
            endwhile;
          else:
            echo '<div class="col-span-full text-center text-gray-200">No customer reviews yet. Be the first to leave one!</div>';
          endif;
        } catch (Exception $e) {
          echo '<div class="col-span-full text-center text-red-200">Failed to load reviews.</div>';
        }
      ?>
    </div>
  </div>
</section>

<!-- Review Form -->
<section id="leave-review" class="py-20 relative">
  <div class="container mx-auto px-6 lg:px-12">
    <div class="text-center mb-12">
      <h2 class="text-4xl lg:text-5xl font-bold mb-4 text-white">Leave a Review</h2>
      <p class="text-lg text-gray-200 max-w-2xl mx-auto">Share your experience with Kandu Pinnawala. Your feedback helps us and other customers!</p>
      <div class="h-1 w-28 rounded-full mx-auto mt-6" style="background:linear-gradient(90deg,#FF7F00,#FF4500)"></div>
    </div>

    <form action="" method="POST" enctype="multipart/form-data" class="max-w-4xl mx-auto">
      <div class="glass-effect rounded-3xl p-8 md:p-10 shadow-xl border border-white/20">
        <div class="grid md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-semibold text-gray-200 mb-2">Full Name</label>
            <input type="text" name="review_name" required class="w-full px-4 py-3 rounded-xl bg-white/10 text-white placeholder-gray-400 border border-white/20 focus:outline-none focus:ring-2" style="--tw-ring-color: rgba(255,127,0,.7)" placeholder="e.g., Nimal Perera">
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-200 mb-2">Email</label>
            <input type="email" name="review_email" required class="w-full px-4 py-3 rounded-xl bg-white/10 text-white placeholder-gray-400 border border-white/20 focus:outline-none focus:ring-2" style="--tw-ring-color: rgba(255,127,0,.7)" placeholder="you@example.com">
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-200 mb-2">Order ID (optional)</label>
            <input type="text" name="review_order" class="w-full px-4 py-3 rounded-xl bg-white/10 text-white placeholder-gray-400 border border-white/20 focus:outline-none focus:ring-2" style="--tw-ring-color: rgba(255,127,0,.7)" placeholder="e.g., KP-2025-00123">
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-200 mb-2">Review Title</label>
            <input type="text" name="review_title" required class="w-full px-4 py-3 rounded-xl bg-white/10 text-white placeholder-gray-400 border border-white/20 focus:outline-none focus:ring-2" style="--tw-ring-color: rgba(255,127,0,.7)" placeholder="e.g., Beautiful craftsmanship!">
          </div>
        </div>

        <!-- Rating -->
        <div class="mt-6">
          <label class="block text-sm font-semibold text-gray-200 mb-2">Rating</label>
          <div class="flex items-center gap-2" id="ratingStars" data-selected="0">
            <?php for($i=1;$i<=5;$i++): ?>
              <button type="button" aria-label="<?php echo $i; ?> star" data-value="<?php echo $i; ?>" class="star-btn w-10 h-10 rounded-full bg-white/10 border border-white/20 flex items-center justify-center hover:bg-white/20"><i class="fa-solid fa-star"></i></button>
            <?php endfor; ?>
          </div>
          <input type="hidden" name="review_rating" id="review_rating" value="0">
        </div>

        <!-- Message -->
        <div class="mt-6">
          <label class="block text-sm font-semibold text-gray-200 mb-2">Your Review</label>
          <textarea name="review_message" rows="5" required class="w-full px-4 py-3 rounded-xl bg-white/10 text-white placeholder-gray-400 border border-white/20 focus:outline-none focus:ring-2" style="--tw-ring-color: rgba(255,127,0,.7)" placeholder="Tell us about the product quality, delivery, and your experience."></textarea>
          <div class="flex items-center justify-between mt-2 text-xs text-gray-300"><span>Be respectful. Keep it helpful for other shoppers.</span><span id="charCount">0/1000</span></div>
        </div>

        <!-- Image upload + agree -->
        <div class="mt-6 grid md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-semibold text-gray-200 mb-2">Add a photo (optional)</label>
            <input type="file" name="review_image" accept="image/*" class="w-full file:mr-4 file:rounded-lg file:border-0 file:px-4 file:py-2 file:text-black file:cursor-pointer rounded-xl bg-white/10 text-white border border-white/20"
                   onfocus="this.style.boxShadow='0 0 0 2px rgba(255,127,0,.7)'" onblur="this.style.boxShadow='none'">
          </div>
          <div class="flex items-center gap-2 mt-8 md:mt-0">
            <input id="agree" type="checkbox" required class="w-4 h-4 rounded border-white/30 bg-white/10">
            <label for="agree" class="text-sm text-gray-300">I agree to have my review displayed on the website.</label>
          </div>
        </div>

        <!-- Submit -->
        <div class="mt-8 flex items-center gap-4">
          <button type="submit" class="inline-flex items-center text-black px-9 py-3 rounded-xl font-semibold hover:shadow-xl transition" style="background:linear-gradient(90deg,#FF7F00,#FF4500); box-shadow:0 12px 28px rgba(255,127,0,.22);">
            <i class="fa-solid fa-paper-plane mr-2"></i> Submit Review
          </button>
          <span class="text-xs text-gray-300">*Backend included above for saving reviews.</span>
        </div>
      </div>
    </form>
  </div>
</section>

<?php include 'footer.php'; ?>

<script>
   function toggleChat(){
      const chatInterface = document.getElementById('chatInterface');
      if (chatInterface.style.display === 'none'){
         chatInterface.style.display = 'block';
         setTimeout(()=> chatInterface.classList.remove('translate-y-full','opacity-0'), 10);
      } else {
         chatInterface.classList.add('translate-y-full','opacity-0');
         setTimeout(()=> chatInterface.style.display = 'none', 300);
      }
   }

   function sendMessage(){
      const input = document.getElementById('chatInput');
      const messages = document.getElementById('chatMessages');
      if (input.value.trim()){
         messages.innerHTML += `<div class="text-right"><div class="inline-block p-3 rounded-lg max-w-xs text-black" style="background:linear-gradient(90deg,#FF7F00,#FF4500)"><p class="text-sm">${input.value}</p></div></div>`;
         setTimeout(()=>{
           messages.innerHTML += `<div class="p-3 rounded-lg" style="background:rgba(255,127,0,.15)"><p class="text-sm text-gray-200">Thank you! Our team will help you find the perfect handicraft.</p></div>`;
           messages.scrollTop = messages.scrollHeight;
         },800);
         input.value = '';
         messages.scrollTop = messages.scrollHeight;
      }
   }

   document.getElementById('chatInput').addEventListener('keypress', e=>{ if(e.key==='Enter'){ sendMessage(); } });

   // Smooth scroll for anchors
   document.querySelectorAll('a[href^="#"]').forEach(a=>{
      a.addEventListener('click', e=>{
         e.preventDefault();
         document.querySelector(a.getAttribute('href')).scrollIntoView({behavior:'smooth'});
      });
   });

   // Rating stars paint (orange)
   (function(){
      const container = document.getElementById('ratingStars');
      const hidden = document.getElementById('review_rating');
      if(!container || !hidden) return;
      const stars = Array.from(container.querySelectorAll('.star-btn'));
      const activeBg = 'linear-gradient(135deg,#FF7F00,#FF4500)';
      const inactiveBg = 'rgba(255,255,255,.08)';

      function paint(n){
         stars.forEach((btn,i)=>{
            btn.style.background = (i<n) ? activeBg : inactiveBg;
            btn.style.borderColor = (i<n) ? 'rgba(255,205,150,.6)' : 'rgba(255,255,255,.2)';
            btn.querySelector('i').style.color = (i<n) ? '#1A1200' : '#bbb';
         });
      }
      stars.forEach(btn=>{
         btn.addEventListener('click', ()=>{ const v = Number(btn.dataset.value||0); hidden.value = v; container.dataset.selected = v; paint(v); });
         btn.addEventListener('mouseenter', ()=> paint(Number(btn.dataset.value||0)));
         btn.addEventListener('mouseleave', ()=> paint(Number(container.dataset.selected||0)));
      });
      paint(0);
   })();

   // Character counter (max 1000)
   (function(){
      const ta = document.querySelector('textarea[name="review_message"]');
      const cc = document.getElementById('charCount');
      if(!ta||!cc) return;
      const max = 1000;
      ta.addEventListener('input', ()=>{
         const len = ta.value.length; cc.textContent = `${len}/${max}`;
         if(len>max){ ta.value = ta.value.slice(0,max); }
      });
   })();
</script>

<script src="js/script.js"></script>
</body>
</html>
