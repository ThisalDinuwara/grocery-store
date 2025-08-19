<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
   header('location:login.php');
   exit;
}

/* Always initialize messages so header/footer foreach is safe */
$message = [];

/* ---------- helpers (no deprecated filters) ---------- */
function clean_text($v, $max = 255){
   $v = trim((string)$v);
   $v = strip_tags($v);
   if (mb_strlen($v) > $max) $v = mb_substr($v, 0, $max);
   return $v;
}
function clean_int($v, $min = 0){
   $n = (int)$v;
   if ($n < $min) $n = $min;
   return $n;
}
function clean_float($v, $min = 0.0){
   $n = (float)$v;
   if ($n < $min) $n = $min;
   return $n;
}

/* ---------- add to wishlist ---------- */
if (isset($_POST['add_to_wishlist'])) {
   $pid     = clean_int($_POST['pid'] ?? 0, 1);
   $p_name  = clean_text($_POST['p_name'] ?? '', 190);
   $p_price = clean_float($_POST['p_price'] ?? 0, 0);
   $p_image = clean_text($_POST['p_image'] ?? '', 190);

   if ($pid && $p_name !== '') {
      $check_wishlist = $conn->prepare("SELECT 1 FROM `wishlist` WHERE name = ? AND user_id = ?");
      $check_wishlist->execute([$p_name, $user_id]);

      $check_cart = $conn->prepare("SELECT 1 FROM `cart` WHERE name = ? AND user_id = ?");
      $check_cart->execute([$p_name, $user_id]);

      if ($check_wishlist->rowCount() > 0) {
         $message[] = 'already added to wishlist!';
      } elseif ($check_cart->rowCount() > 0) {
         $message[] = 'already added to cart!';
      } else {
         $insert = $conn->prepare("INSERT INTO `wishlist`(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
         $insert->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
         $message[] = 'added to wishlist!';
      }
   } else {
      $message[] = 'Invalid product.';
   }
}

/* ---------- add to cart ---------- */
if (isset($_POST['add_to_cart'])) {
   $pid     = clean_int($_POST['pid'] ?? 0, 1);
   $p_name  = clean_text($_POST['p_name'] ?? '', 190);
   $p_price = clean_float($_POST['p_price'] ?? 0, 0);
   $p_image = clean_text($_POST['p_image'] ?? '', 190);
   $p_qty   = clean_int($_POST['p_qty'] ?? 1, 1);

   if ($pid && $p_name !== '') {
      $check_cart = $conn->prepare("SELECT 1 FROM `cart` WHERE name = ? AND user_id = ?");
      $check_cart->execute([$p_name, $user_id]);

      if ($check_cart->rowCount() > 0) {
         $message[] = 'already added to cart!';
      } else {
         // remove from wishlist if it exists
         $check_wishlist = $conn->prepare("SELECT 1 FROM `wishlist` WHERE name = ? AND user_id = ?");
         $check_wishlist->execute([$p_name, $user_id]);
         if ($check_wishlist->rowCount() > 0) {
            $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE name = ? AND user_id = ?");
            $delete_wishlist->execute([$p_name, $user_id]);
         }

         $insert_cart = $conn->prepare(
            "INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)"
         );
         $insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
         $message[] = 'added to cart!';
      }
   } else {
      $message[] = 'Invalid product.';
   }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>search page</title>

   <!-- Tailwind CDN -->
   <script src="https://cdn.tailwindcss.com"></script>
   <script>
      tailwind.config = {
         theme: {
            extend: {
               colors: { primary:'#8B4513', secondary:'#A0522D', accent:'#D2B48C', dark:'#3E2723', darker:'#1B0F0A' },
               fontFamily: { gaming:['Orbitron','monospace'] }
            }
         }
      }
   </script>

   <!-- Font Awesome + Fonts + Site CSS -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="css/style.css">

   <style>
      *{margin:0;padding:0;box-sizing:border-box}
      body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#1B0F0A 0%,#3E2723 50%,#5D4037 100%);color:#fff;overflow-x:hidden}
      .neon-glow{box-shadow:0 0 20px rgba(139,69,19,.5),0 0 40px rgba(160,82,45,.3),0 0 60px rgba(210,180,140,.2)}
      .glass-effect{background:rgba(255,255,255,.08);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.18)}
      .hover-glow:hover{transform:translateY(-5px);box-shadow:0 10px 25px rgba(139,69,19,.35);transition:.3s ease}
      .floating-animation{animation:floating 3s ease-in-out infinite}
      @keyframes floating{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
      .gradient-text{background:linear-gradient(45deg,#8B4513,#A0522D,#D2B48C);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
      .hero-bg{
         background:
            radial-gradient(circle at 20% 80%, rgba(139,69,19,.35) 0%, transparent 55%),
            radial-gradient(circle at 80% 20%, rgba(210,180,140,.35) 0%, transparent 55%),
            radial-gradient(circle at 40% 40%, rgba(160,82,45,.35) 0%, transparent 55%);
      }
      .category-icon{width:80px;height:80px;border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;background:linear-gradient(135deg, rgba(139,69,19,.25), rgba(160,82,45,.25));transition:.3s}
      .category-icon:hover{transform:rotateY(180deg);background:linear-gradient(135deg,#8B4513,#D2B48C)}
      .text-base{font-size:1.125rem!important}.text-lg{font-size:1.25rem!important}.text-xl{font-size:1.375rem!important}
      p,label,input,button,a,li{font-size:1.12rem}
      .product-card{background:linear-gradient(180deg,rgba(62,39,35,.92),rgba(62,39,35,.8));border:1px solid rgba(210,180,140,.28);border-radius:22px;backdrop-filter:blur(16px);transition:transform .4s, box-shadow .4s, border-color .4s;position:relative;overflow:hidden}
      .product-card:hover{transform:translateY(-10px) scale(1.02);border-color:rgba(210,180,140,.6);box-shadow:0 22px 48px rgba(160,82,45,.35)}
      .product-card .aspect-square{position:relative;border-radius:18px;border:1px solid rgba(210,180,140,.25);overflow:hidden;background:radial-gradient(600px 120px at 20% 0%, rgba(210,180,140,.18), transparent 60%)}
      .product-card img{transition:transform .6s}.group:hover .product-card img{transform:scale(1.07)}
      .price-badge{font-size:1.05rem;letter-spacing:.3px;padding:.6rem 1rem;border:1px solid rgba(255,255,255,.18);box-shadow:0 6px 18px rgba(210,180,140,.25)}
      .product-title{font-weight:800;letter-spacing:.2px;color:#FFF7EE;text-shadow:0 1px 0 rgba(0,0,0,.35);line-height:1.25}
      .product-card label{color:#F0E6DA;font-weight:600}
      .product-card .qty{background:rgba(255,255,255,.08)}
      .product-card .qty:focus{outline:none;box-shadow:0 0 0 3px rgba(210,180,140,.35)}
      .product-card .glass-effect{border-color:rgba(255,255,255,.25);color:#E2C9A8}
   </style>
</head>
<body>

<?php include 'header.php'; ?>

<!-- HERO -->
<section class="relative min-h-[60vh] md:min-h-[70vh] flex items-center justify-center overflow-hidden hero-bg">
   <div class="absolute top-10 left-10 w-80 h-80 bg-gradient-to-r from-[rgba(139,69,19,0.2)] to-[rgba(210,180,140,0.2)] rounded-full blur-3xl floating-animation"></div>
   <div class="absolute bottom-10 right-10 w-72 h-72 bg-gradient-to-r from-[rgba(160,82,45,0.2)] to-[rgba(139,69,19,0.2)] rounded-full blur-3xl floating-animation" style="animation-delay:1s"></div>

   <div class="container mx-auto px-6 lg:px-12 grid lg:grid-cols-2 gap-12 items-center relative z-10">
      <div class="space-y-7">
         <h1 class="text-5xl lg:text-7xl font-bold leading-tight">
            <span class="gradient-text font-gaming">SEARCH</span><br>
            <span class="text-white">OUR COLLECTION</span>
         </h1>
         <div class="h-1 w-28 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] rounded-full"></div>
         <p class="text-xl text-gray-300 max-w-2xl">Find authentic Sri Lankan handicrafts by name, category, or details.</p>

         <form action="" method="POST" class="flex flex-col sm:flex-row gap-3 max-w-xl">
            <input
               type="text"
               class="flex-1 px-5 py-3 rounded-xl glass-effect placeholder:text-gray-300 focus:ring-2 focus:ring-[rgb(139,69,19)] outline-none"
               name="search_box"
               placeholder="Type to search…"
               value="<?= isset($_POST['search_box']) ? htmlspecialchars($_POST['search_box']) : '' ?>"
            >
            <button type="submit" name="search_btn"
                    class="px-6 py-3 rounded-xl font-semibold text-white bg-gradient-to-r from-[#8B4513] to-[#D2B48C] hover-glow neon-glow">
               <i class="fas fa-search mr-2"></i> Search
            </button>
         </form>
      </div>

      <div class="relative">
         <div class="glass-effect p-6 md:p-8 rounded-3xl neon-glow">
            <div class="aspect-square rounded-2xl overflow-hidden">
               <img src="images/new.jpg" alt="Sri Lankan Handicrafts" class="w-full h-full object-cover">
            </div>
            <div class="absolute -top-4 -right-4 w-16 h-16 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] rounded-2xl flex items-center justify-center floating-animation">
               <i class="fas fa-star text-xl"></i>
            </div>
            <div class="absolute -bottom-4 -left-4 w-14 h-14 bg-gradient-to-r from-[#A0522D] to-[#8B4513] rounded-xl flex items-center justify-center floating-animation" style="animation-delay:.5s">
               <i class="fas fa-heart text-lg"></i>
            </div>
         </div>
      </div>
   </div>
</section>

<!-- CATEGORIES -->
<section class="py-16">
   <div class="container mx-auto px-6 lg:px-12">
      <div class="text-center mb-12">
         <h2 class="text-4xl lg:text-5xl font-bold mb-4"><span class="gradient-text font-gaming">CATEGORIES</span></h2>
         <div class="h-1 w-24 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] rounded-full mx-auto"></div>
         <p class="text-lg text-gray-300 mt-6 max-w-3xl mx-auto">Browse by craft type and discover pieces you’ll love.</p>
      </div>

      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
         <a href="category.php?category=wood" class="glass-effect p-8 rounded-3xl text-center hover-glow">
            <div class="category-icon"><i class="fas fa-tree text-3xl text-[#CD853F]"></i></div>
            <h3 class="text-2xl font-bold mb-2 gradient-text">WOOD</h3>
            <p class="text-gray-300">Handcrafted wooden masterpieces.</p>
         </a>
         <a href="category.php?category=clothes" class="glass-effect p-8 rounded-3xl text-center hover-glow">
            <div class="category-icon"><i class="fas fa-tshirt text-3xl text-[#deb887]"></i></div>
            <h3 class="text-2xl font-bold mb-2 gradient-text">CLOTHES</h3>
            <p class="text-gray-300">Traditional garments with heritage.</p>
         </a>
         <a href="category.php?category=wallarts" class="glass-effect p-8 rounded-3xl text-center hover-glow">
            <div class="category-icon"><i class="fas fa-palette text-3xl text-[#A0522D]"></i></div>
            <h3 class="text-2xl font-bold mb-2 gradient-text">WALL ARTS</h3>
            <p class="text-gray-300">Decor that tells a story.</p>
         </a>
         <a href="category.php?category=brass" class="glass-effect p-8 rounded-3xl text-center hover-glow">
            <div class="category-icon"><i class="fas fa-medal text-3xl text-[#FFD166]"></i></div>
            <h3 class="text-2xl font-bold mb-2 gradient-text">BRASS</h3>
            <p class="text-gray-300">Exquisite brass craftsmanship.</p>
         </a>
      </div>
   </div>
</section>

<!-- RESULTS -->
<section class="py-10 relative">
  <div class="container mx-auto px-6 lg:px-12">
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
      <?php
        if (isset($_POST['search_btn'])) {
          $search_box = clean_text($_POST['search_box'] ?? '', 200);

          $like = "%{$search_box}%";
          $select_products = $conn->prepare(
            "SELECT * FROM `products`
             WHERE name LIKE ? OR category LIKE ? OR details LIKE ?
             ORDER BY id DESC"
          );
          $select_products->execute([$like, $like, $like]);

          if ($select_products->rowCount() > 0) {
            while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
      ?>
      <form action="" method="POST" class="group">
        <div class="product-card p-6 h-full flex flex-col">
          <div class="absolute top-6 left-6 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white rounded-full font-bold text-sm z-10 neon-glow price-badge">
            Rs <?= htmlspecialchars($fetch_products['price']); ?>/-
          </div>

          <a href="view_page.php?pid=<?= (int)$fetch_products['id']; ?>"
             class="absolute top-6 right-6 w-11 h-11 glass-effect rounded-full flex items-center justify-center hover:text-white hover:bg-gradient-to-r hover:from-[#8B4513] hover:to-[#D2B48C] transition">
            <i class="fas fa-eye"></i>
          </a>

          <div class="aspect-square rounded-2xl overflow-hidden mb-6">
            <img src="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>"
                 alt="<?= htmlspecialchars($fetch_products['name']); ?>"
                 class="w-full h-full object-cover"
                 onerror="this.src='uploaded_img/placeholder.png';">
          </div>

          <div class="space-y-4 mt-auto">
            <h3 class="text-xl product-title"><?= htmlspecialchars($fetch_products['name']); ?></h3>

            <!-- Hidden inputs -->
            <input type="hidden" name="pid"    value="<?= (int)$fetch_products['id']; ?>">
            <input type="hidden" name="p_name" value="<?= htmlspecialchars($fetch_products['name']); ?>">
            <input type="hidden" name="p_price"value="<?= (float)$fetch_products['price']; ?>">
            <input type="hidden" name="p_image"value="<?= htmlspecialchars($fetch_products['image']); ?>">

            <div class="flex items-center gap-3">
              <label class="text-sm font-medium">QTY:</label>
              <input type="number" min="1" value="1" name="p_qty"
                     class="qty w-24 px-3 py-2 glass-effect rounded-lg text-white text-center focus:ring-2 focus:ring-[rgb(139,69,19)] transition-all">
            </div>

            <button type="submit" name="add_to_cart"
                    class="w-full bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white py-3.5 rounded-xl font-semibold hover-glow neon-glow transition-all duration-300 transform hover:scale-[1.02]">
              <i class="fas fa-shopping-cart mr-2"></i> ADD TO CART
            </button>
          </div>
        </div>
      </form>
      <?php
            }
          } else {
            echo '
              <div class="col-span-full text-center py-16 glass-effect rounded-3xl">
                <i class="fas fa-box-open text-6xl" style="color:#CD853F"></i>
                <p class="text-2xl text-gray-200 font-medium mt-4">No products available yet!</p>
              </div>';
          }
        } else {
          echo '
            <div class="col-span-full text-center py-16 glass-effect rounded-3xl">
              <i class="fas fa-search text-6xl" style="color:#CD853F"></i>
              <p class="text-2xl text-gray-200 font-medium mt-4">Start by searching for a product…</p>
            </div>';
        }
      ?>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>

<script>
   document.querySelectorAll('a[href^="#"]').forEach(a=>{
      a.addEventListener('click',e=>{
         e.preventDefault();
         const el = document.querySelector(a.getAttribute('href'));
         if (el) el.scrollIntoView({behavior:'smooth'});
      });
   });
</script>
<script src="js/script.js"></script>
</body>
</html>
