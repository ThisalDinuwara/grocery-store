<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
   header('location:login.php');
   exit;
}

/* always initialize so header/footer foreach won't warn */
$message = [];

/* ---------- helpers ---------- */
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
   $pid    = clean_int($_POST['pid'] ?? 0, 1);
   $p_name = clean_text($_POST['p_name'] ?? '', 190);
   $p_price= clean_float($_POST['p_price'] ?? 0, 0);
   $p_image= clean_text($_POST['p_image'] ?? '', 190);

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
   $pid    = clean_int($_POST['pid'] ?? 0, 1);
   $p_name = clean_text($_POST['p_name'] ?? '', 190);
   $p_price= clean_float($_POST['p_price'] ?? 0, 0);
   $p_image= clean_text($_POST['p_image'] ?? '', 190);
   $p_qty  = clean_int($_POST['p_qty'] ?? 1, 1);

   if ($pid && $p_name !== '') {
      $check_cart = $conn->prepare("SELECT 1 FROM `cart` WHERE name = ? AND user_id = ?");
      $check_cart->execute([$p_name, $user_id]);

      if ($check_cart->rowCount() > 0) {
         $message[] = 'already added to cart!';
      } else {
         // remove from wishlist if present
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
   <title>Shop - Kandu Pinnawala</title>

   <!-- Tailwind CDN -->
   <script src="https://cdn.tailwindcss.com"></script>
   <script>
      tailwind.config = {
         theme: {
            extend: {
               colors: {
                  primary:'#B77B3D',   // warm brown
                  secondary:'#D4A373', // golden beige
                  accent:'#8C6239',    // deep brown
                  ink:'#2E1B0E',       // text
                  soft:'#5C3A24',      // subtle text
               },
               fontFamily: { gaming: ['Orbitron','monospace'] }
            }
         }
      }
   </script>

   <!-- Icons & base CSS -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

   <style>
      /* ===== Light Theme (off-white gradient base) ===== */
      body{
         background: linear-gradient(135deg,#FFFDF9 0%, #F7F3ED 50%, #EFE8DE 100%);
         color: #2E1B0E; /* ink */
         overflow-x:hidden;
      }

      .text-base{font-size:1.20rem!important}
      .text-lg{font-size:1.35rem!important}
      .text-xl{font-size:1.50rem!important}
      p,label,input,button,a,li{font-size:1.20rem}

      /* Category chips */
      .p-category{
         display:flex;flex-wrap:wrap;gap:.75rem;justify-content:center;
         padding:1.25rem 1rem;margin-top:1rem
      }
      .p-category a{
         background: rgba(255,255,255,.85);
         border:1px solid rgba(183,123,61,.25); /* brown border */
         color:#6B4E2E;
         padding:.6rem 1rem;border-radius:999px;
         transition:.25s;text-decoration:none
      }
      .p-category a:hover{
         background: linear-gradient(135deg,#B77B3D,#D4A373);
         color:#fff; transform:translateY(-2px);
         box-shadow:0 10px 22px rgba(183,123,61,.22)
      }

      /* Product card */
      .product-card{
         background: linear-gradient(180deg, rgba(255,255,255,.95), rgba(250,245,235,.9));
         border:1px solid rgba(183,123,61,.28);
         border-radius:22px; backdrop-filter: blur(10px);
         overflow:hidden; position:relative;
         transition:transform .35s, box-shadow .35s, border-color .35s
      }
      .product-card:hover{
         transform:translateY(-8px) scale(1.015);
         border-color: rgba(183,123,61,.55);
         box-shadow:0 22px 48px rgba(183,123,61,.22)
      }

      .thumb{
         border-radius:18px;
         border:1px solid rgba(183,123,61,.25);
         overflow:hidden;
         background: radial-gradient(600px 120px at 20% 0%, rgba(212,163,115,.18), transparent 60%);
      }
      .product-card img{ transition: transform .6s }
      .group:hover .product-card img{ transform: scale(1.07) }

      /* Price badge */
      .price-badge{
         background: linear-gradient(135deg,#B77B3D,#D4A373);
         color:#fff;
         padding:.6rem 1rem;border-radius:999px;
         font-weight:800;font-size:1.05rem;
         border:1px solid rgba(183,123,61,.25);
         box-shadow:0 10px 24px rgba(183,123,61,.22)
      }

      /* Icon chips (view/heart) */
      .chip{
         width:44px;height:44px;display:flex;align-items:center;justify-content:center;
         border-radius:999px;
         background:#ffffff;
         border:1px solid rgba(183,123,61,.25);
         color:#6B4E2E;
         transition:.25s
      }
      .chip:hover{
         background: linear-gradient(135deg,#B77B3D,#D4A373);
         color:#fff; transform:translateY(-2px)
      }

      .product-title{
         color:#3D2B1F;
         font-weight:800; letter-spacing:.2px; line-height:1.25;
         font-size:1.25rem
      }
      .product-meta{ color:#5C3A24 }

      .qty{
         background:#fff;
         border:1px solid rgba(183,123,61,.3);
         color:#2E1B0E;
         border-radius:10px;
      }
      .qty:focus{
         outline:none;
         box-shadow:0 0 0 3px rgba(183,123,61,.28)
      }

      .btn-cart{
         background: linear-gradient(135deg,#B77B3D,#D4A373);
         color:#fff; font-weight:800; letter-spacing:.2px;
         padding:.95rem 1rem; border-radius:14px; transition:.25s;
         box-shadow:0 12px 28px rgba(183,123,61,.22)
      }
      .btn-cart:hover{ transform:translateY(-2px) scale(1.01) }

      .section-title{ color:#3D2B1F }
      .section-sub{ color:#6B4E2E }
   </style>
</head>
<body>

<?php include 'header.php'; ?>

<section class="p-category">
   <a href="category.php?category=wood">Wood</a>
   <a href="category.php?category=clothes">clothes</a>
   <a href="category.php?category=wallarts">wallarts</a>
   <a href="category.php?category=brass">brass</a>
</section>

<section id="products" class="py-20">
   <div class="container mx-auto px-6 lg:px-12">
      <div class="text-center mb-16">
         <h2 class="text-4xl lg:text-5xl font-bold section-title mb-4">Latest Products</h2>
         <p class="text-xl section-sub max-w-3xl mx-auto">Discover our newest collection of authentic Sri Lankan handicrafts</p>
      </div>

      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
         <?php
            $select_products = $conn->prepare("SELECT * FROM `products` ORDER BY id DESC LIMIT 6");
            $select_products->execute();
            if ($select_products->rowCount() > 0):
               while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)):
         ?>
         <form action="" method="POST" class="group">
            <div class="product-card p-6 relative">
               <div class="absolute top-6 left-6 price-badge z-10">
                  Rs <?= htmlspecialchars($fetch_products['price']); ?>/-
               </div>

               <div class="absolute top-6 right-6 flex flex-col gap-2 z-10">
                  <a href="view_page.php?pid=<?= (int)$fetch_products['id']; ?>" class="chip" title="View">
                     <i class="fas fa-eye"></i>
                  </a>
                  <button type="submit" name="add_to_wishlist" class="chip" title="Add to Wishlist">
                     <i class="fas fa-heart"></i>
                  </button>
               </div>

               <div class="thumb aspect-square mb-6">
                  <img
                    src="uploaded_img<?= (strpos($fetch_products['image'],'/')===0?'':'/') . htmlspecialchars($fetch_products['image']); ?>"
                    alt="<?= htmlspecialchars($fetch_products['name']); ?>"
                    class="w-full h-full object-cover">
               </div>

               <div class="space-y-4">
                  <h3 class="product-title"><?= htmlspecialchars($fetch_products['name']); ?></h3>

                  <!-- Hidden Inputs -->
                  <input type="hidden" name="pid" value="<?= (int)$fetch_products['id']; ?>">
                  <input type="hidden" name="p_name" value="<?= htmlspecialchars($fetch_products['name']); ?>">
                  <input type="hidden" name="p_price" value="<?= (float)$fetch_products['price']; ?>">
                  <input type="hidden" name="p_image" value="<?= htmlspecialchars($fetch_products['image']); ?>">

                  <div class="flex items-center gap-3">
                     <label class="product-meta font-semibold">QTY:</label>
                     <input type="number" min="1" value="1" name="p_qty" class="qty w-24 px-3 py-2 text-center transition-all">
                  </div>

                  <button type="submit" name="add_to_cart" class="btn-cart w-full">
                     <i class="fas fa-shopping-cart mr-2"></i> Add to Cart
                  </button>
               </div>
            </div>
         </form>
         <?php
               endwhile;
            else:
               echo '<div class="col-span-full text-center py-16">
                        <div class="mx-auto inline-flex items-center justify-center w-20 h-20 rounded-full" style="background:#fff;border:1px solid rgba(183,123,61,.25);">
                           <i class="fas fa-box-open text-3xl" style="color:#6B4E2E"></i>
                        </div>
                        <p class="mt-6 text-2xl" style="color:#6B4E2E;">No products available yet!</p>
                     </div>';
            endif;
         ?>
      </div>
   </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
