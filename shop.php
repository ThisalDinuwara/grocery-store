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
                  primary:'#FF7F00', secondary:'#FF4500', accent:'#FFA500', dark:'#1A0F00', darker:'#0D0500'
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
      body{background:linear-gradient(135deg,#0D0500 0%,#1A0F00 50%,#251200 100%);color:#FFF7EE;overflow-x:hidden}
      .text-base{font-size:1.20rem!important}.text-lg{font-size:1.35rem!important}.text-xl{font-size:1.50rem!important}
      p,label,input,button,a,li{font-size:1.20rem}
      .p-category{display:flex;flex-wrap:wrap;gap:.75rem;justify-content:center;padding:1.25rem 1rem;margin-top:1rem}
      .p-category a{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);color:#FFE8CF;padding:.6rem 1rem;border-radius:999px;transition:.25s;text-decoration:none}
      .p-category a:hover{background:linear-gradient(135deg,#FF7F00,#FFA500);color:#111;transform:translateY(-2px);box-shadow:0 10px 22px rgba(255,165,0,.25)}
      .product-card{background:linear-gradient(180deg,rgba(26,15,0,.92),rgba(26,15,0,.84));border:1px solid rgba(255,200,140,.28);border-radius:22px;backdrop-filter:blur(16px);overflow:hidden;position:relative;transition:transform .35s,box-shadow .35s,border-color .35s}
      .product-card:hover{transform:translateY(-8px) scale(1.015);border-color:rgba(255,200,140,.55);box-shadow:0 22px 48px rgba(255,127,0,.35)}
      .thumb{border-radius:18px;border:1px solid rgba(255,200,140,.25);overflow:hidden;background:radial-gradient(600px 120px at 20% 0%,rgba(255,165,0,.18),transparent 60%)}
      .product-card img{transition:transform .6s}.group:hover .product-card img{transform:scale(1.07)}
      .price-badge{background:linear-gradient(135deg,#FF7F00,#FF4500);color:#111;padding:.6rem 1rem;border-radius:999px;font-weight:800;font-size:1.05rem;border:1px solid rgba(255,255,255,.18);box-shadow:0 10px 24px rgba(255,165,0,.28)}
      .chip{width:44px;height:44px;display:flex;align-items:center;justify-content:center;border-radius:999px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.22);color:#E8D7BF;transition:.25s}
      .chip:hover{background:linear-gradient(135deg,#FF7F00,#FFA500);color:#111;transform:translateY(-2px)}
      .product-title{color:#FFF7EE;font-weight:800;letter-spacing:.2px;line-height:1.25;text-shadow:0 1px 0 rgba(0,0,0,.35);font-size:1.25rem}
      .product-meta{color:#FFE8CF}
      .qty{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.22);color:#fff}
      .qty:focus{outline:none;box-shadow:0 0 0 3px rgba(255,127,0,.35)}
      .btn-cart{background:linear-gradient(135deg,#FF7F00,#FF4500);color:#111;font-weight:800;letter-spacing:.2px;padding:.95rem 1rem;border-radius:14px;transition:.25s;box-shadow:0 12px 28px rgba(255,127,0,.25)}
      .btn-cart:hover{transform:translateY(-2px) scale(1.01)}
      .section-title{color:#FFF3E4}.section-sub{color:#E8DAC8}
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
                     <input type="number" min="1" value="1" name="p_qty" class="qty w-24 px-3 py-2 rounded-lg text-center transition-all">
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
                        <div class="mx-auto inline-flex items-center justify-center w-20 h-20 rounded-full" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.2);">
                           <i class="fas fa-box-open text-3xl" style="color:#FFE8CF"></i>
                        </div>
                        <p class="mt-6 text-2xl" style="color:#FFE8CF;">No products available yet!</p>
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
