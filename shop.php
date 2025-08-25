<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if(!$user_id){
   header('location:login.php');
   exit;
}

$message = $message ?? [];

/* =========================
   Add to WISHLIST (uses pid, trust server)
========================= */
if(isset($_POST['add_to_wishlist'])){
   $pid = (int)($_POST['pid'] ?? 0);

   $pstmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id = ? LIMIT 1");
   $pstmt->execute([$pid]);
   $prod = $pstmt->fetch(PDO::FETCH_ASSOC);

   if(!$prod){
      $message[] = 'Product not found.';
   }else{
      $check = $conn->prepare("SELECT 1 FROM wishlist WHERE pid = ? AND user_id = ? LIMIT 1");
      $check->execute([$pid, $user_id]);

      $check_cart = $conn->prepare("SELECT 1 FROM cart WHERE pid = ? AND user_id = ? LIMIT 1");
      $check_cart->execute([$pid, $user_id]);

      if($check->rowCount() > 0){
         $message[] = 'Already in wishlist!';
      }elseif($check_cart->rowCount() > 0){
         $message[] = 'Already in cart!';
      }else{
         $ins = $conn->prepare("INSERT INTO wishlist (user_id, pid, name, price, image) VALUES (?,?,?,?,?)");
         $ins->execute([$user_id, $prod['id'], $prod['name'], $prod['price'], $prod['image']]);
         $message[] = 'Added to wishlist!';
      }
   }
}

/* =========================
   Add to CART with stock lock & subtraction
========================= */
if(isset($_POST['add_to_cart'])){
   $pid   = (int)($_POST['pid'] ?? 0);
   $reqQ  = max(1, (int)($_POST['p_qty'] ?? 1));

   try{
      $conn->beginTransaction();

      $pstmt = $conn->prepare("SELECT id, name, price, image, quantity FROM products WHERE id = ? FOR UPDATE");
      $pstmt->execute([$pid]);
      $prod = $pstmt->fetch(PDO::FETCH_ASSOC);

      if(!$prod){
         $conn->rollBack();
         $message[] = 'Product not found.';
      }elseif((int)$prod['quantity'] <= 0){
         $conn->rollBack();
         $message[] = 'Out of stock.';
      }else{
         $avail = (int)$prod['quantity'];
         $addQty = min($reqQ, $avail);
         $newStock = $avail - $addQty;

         $csel = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND pid = ? FOR UPDATE");
         $csel->execute([$user_id, $pid]);
         if($row = $csel->fetch(PDO::FETCH_ASSOC)){
            $newCartQty = (int)$row['quantity'] + $addQty;
            $cupd = $conn->prepare("UPDATE cart SET quantity = ?, price = ?, name = ?, image = ? WHERE id = ?");
            $cupd->execute([$newCartQty, $prod['price'], $prod['name'], $prod['image'], $row['id']]);
         }else{
            $cins = $conn->prepare("INSERT INTO cart (user_id, pid, name, price, quantity, image) VALUES (?,?,?,?,?,?)");
            $cins->execute([$user_id, $prod['id'], $prod['name'], $prod['price'], $addQty, $prod['image']]);
         }

         $up = $conn->prepare("UPDATE products SET quantity = ? WHERE id = ?");
         $up->execute([$newStock, $pid]);

         $conn->commit();

         if($addQty < $reqQ){
            $message[] = "Only {$addQty} left; added {$addQty} to cart.";
         }else{
            $message[] = 'Added to cart!';
         }
      }
   }catch(Exception $e){
      if($conn->inTransaction()){ $conn->rollBack(); }
      $message[] = 'Could not add to cart. Please try again.';
   }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shop</title>

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
            },
            fontFamily: {
              'gaming': ['Orbitron', 'monospace'],
              'sans': ['Inter', 'system-ui', 'sans-serif']
            }
          }
        }
      }
   </script>

   <!-- Icons -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Your base CSS (kept) -->
   <link rel="stylesheet" href="css/style.css">

   <!-- Light Theme Overrides (match home page) -->
   <style>
      :root { --kp-text:#0b0b0b; --kp-border:rgba(0,0,0,.12); }
      *{box-sizing:border-box}
      body{ background:#ffffff !important; color:var(--kp-text) !important; font-family:'Inter',sans-serif; }

      /* Category chips */
      .p-category{display:flex;flex-wrap:wrap;gap:.75rem;justify-content:center;padding:1.5rem 1rem 0;}
      .p-category a{background:#fff;border:1px solid var(--kp-border);color:#111827;padding:.6rem 1rem;border-radius:999px;transition:.25s;text-decoration:none;}
      .p-category a:hover{background:linear-gradient(135deg,#8B4513,#D2B48C);color:#fff;transform:translateY(-2px);box-shadow:0 10px 22px rgba(0,0,0,.10);border-color:transparent;}

      /* Cards */
      #products{background:transparent;}
      .product-card{background:#fff;border:1px solid var(--kp-border);border-radius:22px;backdrop-filter: blur(10px);overflow:hidden;position:relative;transition: transform .35s ease, box-shadow .35s ease, border-color .35s ease;box-shadow: 0 8px 24px rgba(0,0,0,.05);}
      .product-card:hover{transform: translateY(-8px) scale(1.015);border-color: rgba(0,0,0,.18);box-shadow: 0 22px 48px rgba(0,0,0,.08);}
      .product-card .thumb{border-radius:18px;border:1px solid var(--kp-border);overflow:hidden;background:radial-gradient(600px 120px at 20% 0%, rgba(210,180,140,.10), transparent 60%);}
      .product-card img{transition: transform .6s ease;}
      .group:hover .product-card img{transform: scale(1.07);}

      .price-badge{background: linear-gradient(135deg,#8B4513,#D2B48C); color:white; padding:.6rem 1rem;border-radius:999px;font-weight:800;font-size:1.05rem;border:1px solid transparent;box-shadow: 0 10px 24px rgba(0,0,0,.12);}
      .chip{width:44px;height:44px;display:flex;align-items:center;justify-content:center;border-radius:999px;background:#fff;border:1px solid var(--kp-border);color:#111827;transition:.25s;}
      .chip:hover{background: linear-gradient(135deg,#8B4513,#D2B48C);color:#fff;transform: translateY(-2px);border-color:transparent;}
      .product-title{color:#111827;font-weight:800;letter-spacing:.2px;line-height:1.25;font-size:1.25rem;}
      .product-meta{color:#374151;}
      .qty{background:#fff;border:1px solid var(--kp-border);color:#111827;border-radius:12px;}
      .qty:focus{outline:none;box-shadow:0 0 0 3px rgba(210,180,140,.25);}
      .btn-cart{background: linear-gradient(135deg,#8B4513,#D2B48C); color:#fff;font-weight:700;letter-spacing:.2px;padding:.95rem 1rem;border-radius:14px;transition:.25s; box-shadow:0 12px 28px rgba(0,0,0,.12);}
      .btn-cart:hover{transform: translateY(-2px) scale(1.01);}
      .badge-stock{position:absolute;top:6px;right:6px}
      .oos-overlay{position:absolute;inset:0;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;font-weight:800;letter-spacing:.5px;color:#fff;border-radius:18px}

      .alert { background:#FFF7ED; color:#7C2D12; border:1px solid #FED7AA; padding:.6rem 1rem; border-radius:12px; }
      .section-title{color:#0b0b0b}
      .section-sub{color:#374151}
   </style>
</head>
<body>

<?php include 'header.php'; ?>

<!-- HERO for Kandu Pinnawala Shop -->
<section class="relative bg-gradient-to-r from-[#8B4513] via-[#A0522D] to-[#D2B48C] text-white">
  <div class="container mx-auto px-6 lg:px-12 py-16 lg:py-20 grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
    
    <!-- Left content -->
    <div class="space-y-7">
      <button class="bg-white text-[#8B4513] font-semibold px-6 py-2 rounded-full shadow-md hover:shadow-lg transition inline-flex items-center">
        <i class="fa-solid fa-store mr-2"></i> Explore Our Crafts
      </button>

      <h1 class="text-5xl lg:text-6xl font-extrabold leading-tight">
        Discover <span class="text-yellow-200">Authentic</span> Sri Lankan Handicrafts
      </h1>

      <p class="text-white/90 text-lg max-w-xl">
        From wood carvings to brass art, explore the timeless beauty of Sri Lankan craftsmanship at Kandu Pinnawala.
      </p>

      <!-- Feature bullets -->
      <div class="grid grid-cols-2 gap-5 text-sm font-medium">
        <div class="flex items-center gap-2">
          <span class="w-8 h-8 flex items-center justify-center bg-white/20 rounded-full">
            <i class="fa-solid fa-hand-sparkles"></i>
          </span>
          Handmade
        </div>
        <div class="flex items-center gap-2">
          <span class="w-8 h-8 flex items-center justify-center bg-white/20 rounded-full">
            <i class="fa-solid fa-leaf"></i>
          </span>
          Eco-Friendly
        </div>
        <div class="flex items-center gap-2">
          <span class="w-8 h-8 flex items-center justify-center bg-white/20 rounded-full">
            <i class="fa-solid fa-users"></i>
          </span>
          Local Artisans
        </div>
        <div class="flex items-center gap-2">
          <span class="w-8 h-8 flex items-center justify-center bg-white/20 rounded-full">
            <i class="fa-solid fa-gift"></i>
          </span>
          Perfect Gifts
        </div>
      </div>
    </div>

    <!-- Right image card -->
    <div class="relative">
      <div class="rounded-2xl overflow-hidden shadow-xl border-4 border-white/20">
        <img src="images/hero_handicraft.jpg" alt="Handicraft Display" class="w-full h-full object-cover">
      </div>

      <!-- Badge -->
      <div class="absolute -top-3 -right-3 bg-white text-[#8B4513] font-bold px-4 py-2 rounded-2xl shadow">
        1000+ <span class="font-normal text-gray-600 ml-1">Crafts</span>
      </div>

      <!-- Availability / Highlight -->
      <div class="absolute -bottom-4 left-6 bg-white text-gray-800 px-4 py-3 rounded-xl shadow-md flex items-center gap-3">
        <i class="fa-solid fa-star text-yellow-500"></i>
        <div>
          <p class="font-semibold leading-tight">Best Seller Collection</p>
          <p class="text-sm text-gray-500 leading-tight">Exclusive 2025 Handicrafts</p>
        </div>
      </div>
    </div>
  </div>
</section>


<section class="p-category">
   <a href="category.php?category=wood">Wood</a>
   <a href="category.php?category=clothes">Clothes</a>
   <a href="category.php?category=wallarts">Wall Arts</a>
   <a href="category.php?category=brass">Brass</a>
</section>

<section id="products" class="py-20">
   <div class="container mx-auto px-6 lg:px-12">
      <div class="text-center mb-16">
         <h2 class="text-4xl lg:text-5xl font-bold section-title mb-4">Latest Products</h2>
         <p class="text-xl section-sub max-w-3xl mx-auto">Discover our newest collection of authentic Sri Lankan handicrafts</p>
      </div>

      <?php if(!empty($message)): ?>
        <div class="max-w-3xl mx-auto mb-8 space-y-2">
          <?php foreach($message as $m): ?>
            <div class="alert"><?= htmlspecialchars($m) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
         <?php
            $select_products = $conn->prepare("SELECT * FROM `products` ORDER BY id DESC LIMIT 6");
            $select_products->execute();
            if($select_products->rowCount() > 0){
               while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){
                  $pid   = (int)$fetch_products['id'];
                  $price = (float)$fetch_products['price'];
                  $qty   = (int)($fetch_products['quantity'] ?? 0);
                  $inStock = $qty > 0;
         ?>
         <form action="" method="POST" class="group">
            <div class="product-card p-6 relative">
               <div class="absolute top-6 left-6 price-badge z-10">
                  Rs <?= number_format($price,2); ?>/-
               </div>

               <div class="badge-stock z-10">
                 <?php if(!$inStock): ?>
                   <span class="text-xs px-2 py-1 rounded bg-rose-100 text-rose-800 border border-rose-200">Out of stock</span>
                 <?php elseif($qty < 10): ?>
                   <span class="text-xs px-2 py-1 rounded bg-amber-100 text-amber-800 border border-amber-200">Only <?= $qty; ?> left</span>
                 <?php endif; ?>
               </div>

               <div class="absolute top-6 right-6 flex flex-col gap-2 z-10">
                  <a href="view_page.php?pid=<?= $pid; ?>" class="chip" title="View"><i class="fas fa-eye"></i></a>
                  <button type="submit" name="add_to_wishlist" class="chip" title="Add to Wishlist"><i class="fas fa-heart"></i></button>
               </div>

               <div class="thumb aspect-square mb-6 relative">
                  <img src="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>" alt="<?= htmlspecialchars($fetch_products['name']); ?>" class="w-full h-full object-cover">
                  <?php if(!$inStock): ?><div class="oos-overlay text-lg rounded">OUT OF STOCK</div><?php endif; ?>
               </div>

               <div class="space-y-4">
                  <h3 class="product-title"><?= htmlspecialchars($fetch_products['name']); ?></h3>
                  <input type="hidden" name="pid" value="<?= $pid; ?>">
                  <div class="flex items-center gap-3">
                     <label class="product-meta font-semibold">QTY:</label>
                     <input type="number" min="1" value="<?= $inStock ? 1 : 0; ?>" name="p_qty" class="qty w-24 px-3 py-2 text-center transition-all" <?= $inStock ? '' : 'disabled'; ?>>
                  </div>
                  <button type="submit" name="add_to_cart" class="btn-cart w-full" <?= $inStock ? '' : 'disabled style="opacity:.6;cursor:not-allowed"' ?>>
                     <i class="fas fa-shopping-cart mr-2"></i> <?= $inStock ? 'Add to Cart' : 'Unavailable' ?>
                  </button>
               </div>
            </div>
         </form>
         <?php
               }
            }else{
               echo '<div class="col-span-full text-center py-16">
                        <div class="mx-auto inline-flex items-center justify-center w-20 h-20 rounded-full bg-white border" style="border-color:var(--kp-border);">
                           <i class="fas fa-box-open text-3xl" style="color:#A0522D"></i>
                        </div>
                        <p class="mt-6 text-2xl" style="color:#374151;">No products available yet!</p>
                     </div>';
            }
         ?>
      </div>
   </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
