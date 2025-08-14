<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
};

if(isset($_POST['add_to_wishlist'])){

   $pid = $_POST['pid'];
   $pid = filter_var($pid, FILTER_SANITIZE_STRING);
   $p_name = $_POST['p_name'];
   $p_name = filter_var($p_name, FILTER_SANITIZE_STRING);
   $p_price = $_POST['p_price'];
   $p_price = filter_var($p_price, FILTER_SANITIZE_STRING);
   $p_image = $_POST['p_image'];
   $p_image = filter_var($p_image, FILTER_SANITIZE_STRING);

   $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
   $check_wishlist_numbers->execute([$p_name, $user_id]);

   $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
   $check_cart_numbers->execute([$p_name, $user_id]);

   if($check_wishlist_numbers->rowCount() > 0){
      $message[] = 'already added to wishlist!';
   }elseif($check_cart_numbers->rowCount() > 0){
      $message[] = 'already added to cart!';
   }else{
      $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
      $insert_wishlist->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
      $message[] = 'added to wishlist!';
   }

}

if(isset($_POST['add_to_cart'])){

   $pid = $_POST['pid'];
   $pid = filter_var($pid, FILTER_SANITIZE_STRING);
   $p_name = $_POST['p_name'];
   $p_name = filter_var($p_name, FILTER_SANITIZE_STRING);
   $p_price = $_POST['p_price'];
   $p_price = filter_var($p_price, FILTER_SANITIZE_STRING);
   $p_image = $_POST['p_image'];
   $p_image = filter_var($p_image, FILTER_SANITIZE_STRING);
   $p_qty = $_POST['p_qty'];
   $p_qty = filter_var($p_qty, FILTER_SANITIZE_STRING);

   $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
   $check_cart_numbers->execute([$p_name, $user_id]);

   if($check_cart_numbers->rowCount() > 0){
      $message[] = 'already added to cart!';
   }else{

      $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
      $check_wishlist_numbers->execute([$p_name, $user_id]);

      if($check_wishlist_numbers->rowCount() > 0){
         $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE name = ? AND user_id = ?");
         $delete_wishlist->execute([$p_name, $user_id]);
      }

      $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
      $insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
      $message[] = 'added to cart!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>quick view</title>

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
                  gaming: ['Orbitron', 'monospace'],
                  inter: ['Inter', 'sans-serif']
               }
            }
         }
      }
   </script>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Your CSS (kept) -->
   <link rel="stylesheet" href="css/style.css">

   <!-- Home.php look & feel -->
   <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   <style>
      *{margin:0;padding:0;box-sizing:border-box}
      body{
         font-family:'Inter',sans-serif;
         background:linear-gradient(135deg,#1B0F0A 0%,#3E2723 50%,#5D4037 100%);
         color:#fff; overflow-x:hidden;
      }
      .hero-bg{
         background:
           radial-gradient(circle at 20% 80%, rgba(139,69,19,.35) 0%, transparent 55%),
           radial-gradient(circle at 80% 20%, rgba(210,180,140,.35) 0%, transparent 55%),
           radial-gradient(circle at 40% 40%, rgba(160,82,45,.35) 0%, transparent 55%);
      }
      .neon-glow{box-shadow:0 0 20px rgba(139,69,19,.5),0 0 40px rgba(160,82,45,.3),0 0 60px rgba(210,180,140,.2)}
      .glass-effect{background:rgba(255,255,255,.08);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.18)}
      .hover-glow:hover{transform:translateY(-5px);box-shadow:0 10px 25px rgba(139,69,19,.35);transition:.3s ease}
      .gradient-text{background:linear-gradient(45deg,#8B4513,#A0522D,#D2B48C);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

      .product-card{
         background:linear-gradient(180deg,rgba(62,39,35,.92),rgba(62,39,35,.84));
         border:1px solid rgba(210,180,140,.28);
         border-radius:22px;
         overflow:hidden;
         transition:transform .35s ease, box-shadow .35s ease, border-color .35s ease;
      }
      .product-card:hover{transform:translateY(-8px);border-color:rgba(210,180,140,.55);box-shadow:0 22px 48px rgba(160,82,45,.35)}
      .price-badge{
         font-size:1.05rem;letter-spacing:.3px;padding:.6rem 1rem;
         border:1px solid rgba(255,255,255,.18)
      }
      .product-title{font-weight:800;letter-spacing:.2px;color:#FFF7EE;text-shadow:0 1px 0 rgba(0,0,0,.35);line-height:1.25}
      .qty{background:rgba(255,255,255,.08)}
      .qty:focus{outline:none;box-shadow:0 0 0 3px rgba(210,180,140,.35)}
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<!-- Hero / Header -->
<section class="relative min-h-[35vh] md:min-h-[45vh] flex items-center justify-center overflow-hidden hero-bg">
  <div class="absolute top-10 left-10 w-72 h-72 bg-gradient-to-r from-[rgba(139,69,19,0.22)] to-[rgba(210,180,140,0.22)] rounded-full blur-3xl"></div>
  <div class="absolute bottom-10 right-10 w-64 h-64 bg-gradient-to-r from-[rgba(160,82,45,0.22)] to-[rgba(139,69,19,0.22)] rounded-full blur-3xl"></div>

  <div class="container mx-auto px-6 lg:px-12 relative z-10 text-center">
     <h1 class="text-5xl lg:text-7xl font-bold leading-tight mb-4">
       <span class="gradient-text font-gaming">QUICK</span> <span class="text-white">VIEW</span>
     </h1>
     <div class="h-1 w-28 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] rounded-full mx-auto mb-6"></div>
     <p class="text-lg md:text-xl text-gray-200 max-w-3xl mx-auto">
       See the details you need and add to cart instantly.
     </p>
  </div>
</section>

<!-- Quick View (keeps all your PHP exactly the same) -->
<section id="quick-view" class="py-16">
  <div class="container mx-auto px-6 lg:px-12">

    <?php
      $pid = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
      $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
      $select_products->execute([$pid]);

      if($select_products->rowCount() > 0){
        $fetch_products = $select_products->fetch(PDO::FETCH_ASSOC);
    ?>
    <form action="" method="POST" class="group max-w-4xl mx-auto">
      <div class="product-card relative neon-glow">
        <!-- Top bar: price & back -->
        <div class="px-4 py-3 flex items-center justify-between">
          <div class="inline-flex items-center gap-2 price-badge rounded-full bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white">
            <i class="fas fa-tag"></i>
            <span class="font-semibold">Rs <?= htmlspecialchars($fetch_products['price']); ?>/-</span>
          </div>

          <a href="shop.php"
             class="w-10 h-10 glass-effect rounded-full flex items-center justify-center text-white hover-glow"
             aria-label="Back to shop">
            <i class="fas fa-times"></i>
          </a>
        </div>

        <!-- Image -->
        <div class="aspect-square overflow-hidden">
          <img src="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>"
               alt="<?= htmlspecialchars($fetch_products['name']); ?>"
               class="w-full h-full object-cover"
               onerror="this.src='uploaded_img/placeholder.png';">
        </div>

        <!-- Info -->
        <div class="p-6 md:p-8">
          <h3 class="product-title text-2xl mb-3">
            <?= htmlspecialchars($fetch_products['name']); ?>
          </h3>

          <?php if(!empty($fetch_products['details'])): ?>
          <p class="text-gray-200 mb-6 leading-relaxed">
            <?= nl2br(htmlspecialchars($fetch_products['details'])); ?>
          </p>
          <?php endif; ?>

          <!-- Hidden Inputs -->
          <input type="hidden" name="pid" value="<?= (int)$fetch_products['id']; ?>">
          <input type="hidden" name="p_name" value="<?= htmlspecialchars($fetch_products['name']); ?>">
          <input type="hidden" name="p_price" value="<?= htmlspecialchars($fetch_products['price']); ?>">
          <input type="hidden" name="p_image" value="<?= htmlspecialchars($fetch_products['image']); ?>">

          <!-- Quantity -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-200 mb-2">Quantity</label>
            <input type="number" min="1" value="1" name="p_qty"
                   class="qty w-32 px-4 py-3 glass-effect rounded-xl text-white text-center">
          </div>

          <!-- Actions -->
          <div class="grid sm:grid-cols-2 gap-3">
            <button type="submit" name="add_to_cart"
                    class="w-full bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white py-4 rounded-xl font-semibold hover-glow transition">
              <i class="fas fa-shopping-cart mr-2"></i>
              Add to Cart
            </button>

            <button type="submit" name="add_to_wishlist"
                    class="w-full glass-effect text-white py-4 rounded-xl font-semibold hover-glow">
              <i class="fas fa-heart mr-2"></i>
              Add to Wishlist
            </button>
          </div>
        </div>
      </div>
    </form>
    <?php
      } else {
        echo '<div class="text-center">
                <div class="glass-effect p-12 rounded-3xl max-w-md mx-auto">
                  <i class="fas fa-box-open text-6xl" style="color:#CD853F"></i>
                  <p class="text-2xl text-gray-200 font-medium mt-4">No products found!</p>
                  <a href="shop.php"
                     class="mt-6 inline-flex items-center justify-center bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white px-6 py-3 rounded-xl font-semibold hover-glow transition">
                    <i class="fas fa-store mr-2"></i> Back to Shop
                  </a>
                </div>
              </div>';
      }
    ?>
  </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
