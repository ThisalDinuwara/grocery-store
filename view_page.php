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
   <title>Quick View — Kandu Pinnawala</title>

   <!-- Tailwind CDN -->
   <script src="https://cdn.tailwindcss.com"></script>
   <script>
      tailwind.config = {
         theme: {
            extend: {
               colors: {
                  'orange-primary': '#FF6B35',
                  'orange-light': '#FF8C42', 
                  'orange-accent': '#FFA366',
                  'brown-dark': '#3E2723',
                  'brown-medium': '#5D4037',
                  'brown-light': '#8D6E63',
                  'cream': '#F5F5DC',
                  'warm-bg': '#2C1810'
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

   <!-- Google Fonts -->
   <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   
   <style>
      * {
         margin: 0; 
         padding: 0; 
         box-sizing: border-box;
      }

      body {
         font-family: 'Inter', sans-serif !important;
         background: linear-gradient(135deg, #2C1810 0%, #3E2723 50%, #5D4037 100%) !important;
         color: #F5F5DC !important;
         overflow-x: hidden;
         min-height: 100vh;
      }

      .hero-bg {
         background: 
           radial-gradient(circle at 20% 80%, rgba(255, 107, 53, 0.15) 0%, transparent 60%),
           radial-gradient(circle at 80% 20%, rgba(255, 140, 66, 0.12) 0%, transparent 60%),
           radial-gradient(circle at 40% 40%, rgba(255, 163, 102, 0.1) 0%, transparent 60%),
           linear-gradient(135deg, rgba(44, 24, 16, 0.9), rgba(62, 39, 35, 0.8));
      }

      .orange-glow {
         box-shadow: 
           0 0 20px rgba(255, 107, 53, 0.3),
           0 0 40px rgba(255, 140, 66, 0.2),
           0 0 60px rgba(255, 163, 102, 0.1);
      }

      .glass-effect {
         background: rgba(245, 245, 220, 0.08);
         backdrop-filter: blur(12px);
         border: 1px solid rgba(255, 107, 53, 0.2);
      }

      .hover-glow:hover {
         transform: translateY(-8px);
         box-shadow: 
           0 15px 35px rgba(255, 107, 53, 0.25),
           0 5px 15px rgba(255, 140, 66, 0.15);
         transition: all 0.4s ease;
      }

      /* Orange gradient for QUICK text */
      .gradient-text {
         background: linear-gradient(45deg, #FF6B35, #FF8C42, #FFA366) !important;
         -webkit-background-clip: text !important;
         -webkit-text-fill-color: transparent !important;
         background-clip: text !important;
         color: #FF6B35 !important;
         font-weight: 900 !important;
         text-shadow: 0 0 30px rgba(255, 107, 53, 0.5);
      }

      .product-card {
         background: linear-gradient(145deg, 
           rgba(62, 39, 35, 0.95), 
           rgba(93, 64, 55, 0.9),
           rgba(141, 110, 99, 0.85)
         );
         border: 2px solid rgba(255, 107, 53, 0.3);
         border-radius: 24px;
         overflow: hidden;
         transition: all 0.4s ease;
         position: relative;
      }

      .product-card::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         right: 0;
         bottom: 0;
         background: linear-gradient(135deg, 
           rgba(255, 107, 53, 0.05), 
           transparent 50%, 
           rgba(255, 163, 102, 0.03)
         );
         pointer-events: none;
      }

      .product-card:hover {
         transform: translateY(-12px);
         border-color: rgba(255, 107, 53, 0.6);
         box-shadow: 
           0 25px 50px rgba(255, 107, 53, 0.2),
           0 10px 30px rgba(93, 64, 55, 0.3);
      }

      .price-badge {
         background: linear-gradient(135deg, #FF6B35, #FF8C42) !important;
         color: white !important;
         font-size: 1.1rem;
         font-weight: 700;
         letter-spacing: 0.5px;
         padding: 0.8rem 1.2rem;
         border: 2px solid rgba(255, 255, 255, 0.2);
         box-shadow: 0 8px 25px rgba(255, 107, 53, 0.3);
      }

      .product-title {
         font-weight: 800 !important;
         color: #F5F5DC !important;
         text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
         line-height: 1.3;
         letter-spacing: 0.3px;
      }

      .qty-input {
         background: rgba(245, 245, 220, 0.1) !important;
         color: #F5F5DC !important;
         border: 2px solid rgba(255, 107, 53, 0.3);
         transition: all 0.3s ease;
      }

      .qty-input:focus {
         outline: none;
         border-color: #FF6B35;
         box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.2);
         background: rgba(245, 245, 220, 0.15) !important;
      }

      .btn-primary {
         background: linear-gradient(135deg, #FF6B35, #FF8C42) !important;
         color: white !important;
         font-weight: 700;
         border: none;
         transition: all 0.3s ease;
         box-shadow: 0 8px 25px rgba(255, 107, 53, 0.3);
      }

      .btn-primary:hover {
         background: linear-gradient(135deg, #FF8C42, #FFA366) !important;
         transform: translateY(-2px);
         box-shadow: 0 12px 35px rgba(255, 107, 53, 0.4);
      }

      .btn-secondary {
         background: rgba(245, 245, 220, 0.1) !important;
         color: #F5F5DC !important;
         border: 2px solid rgba(255, 107, 53, 0.4);
         font-weight: 600;
         transition: all 0.3s ease;
      }

      .btn-secondary:hover {
         background: rgba(255, 107, 53, 0.2) !important;
         border-color: #FF6B35;
         color: white !important;
      }

      /* Text colors */
      h1, h2, h3, h4, h5, h6, p, span, label, div {
         color: #F5F5DC !important;
      }

      .text-description {
         color: rgba(245, 245, 220, 0.9) !important;
         line-height: 1.6;
      }

      /* Message styling */
      .message {
         position: fixed;
         top: 20px;
         right: 20px;
         background: linear-gradient(135deg, #FF6B35, #FF8C42);
         color: white !important;
         padding: 16px 24px;
         border-radius: 12px;
         border: 2px solid rgba(255, 255, 255, 0.2);
         z-index: 1000;
         animation: slideIn 0.4s ease;
         box-shadow: 0 10px 30px rgba(255, 107, 53, 0.3);
         font-weight: 600;
      }

      @keyframes slideIn {
         from { transform: translateX(100%); opacity: 0; }
         to { transform: translateX(0); opacity: 1; }
      }

      /* Close button styling */
      .close-btn {
         color: #F5F5DC !important;
         transition: all 0.3s ease;
      }

      .close-btn:hover {
         color: #FF6B35 !important;
         transform: scale(1.1);
      }

      /* Floating animation */
      .floating {
         animation: floating 6s ease-in-out infinite;
      }

      @keyframes floating {
         0%, 100% { transform: translateY(0px) rotate(0deg); }
         33% { transform: translateY(-10px) rotate(1deg); }
         66% { transform: translateY(5px) rotate(-1deg); }
      }

      /* Image hover effect */
      .product-image {
         transition: all 0.4s ease;
      }

      .product-card:hover .product-image {
         transform: scale(1.05);
      }

      /* Input placeholder */
      input::placeholder {
         color: rgba(245, 245, 220, 0.6) !important;
      }

      /* Enhanced no-products styling */
      .no-products {
         background: rgba(245, 245, 220, 0.05);
         border: 2px solid rgba(255, 107, 53, 0.2);
      }
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<!-- Hero Section -->
<section class="relative min-h-[40vh] md:min-h-[50vh] flex items-center justify-center overflow-hidden hero-bg">
  <!-- Floating background elements -->
  <div class="absolute top-16 left-16 w-80 h-80 bg-gradient-to-r from-orange-primary/20 to-orange-light/15 rounded-full blur-3xl floating"></div>
  <div class="absolute bottom-16 right-16 w-72 h-72 bg-gradient-to-r from-orange-accent/15 to-orange-primary/10 rounded-full blur-3xl floating" style="animation-delay: 2s;"></div>
  <div class="absolute top-1/2 left-1/3 w-64 h-64 bg-gradient-to-r from-orange-light/10 to-orange-accent/8 rounded-full blur-3xl floating" style="animation-delay: 4s;"></div>

  <div class="container mx-auto px-6 lg:px-12 relative z-10 text-center">
    <h1 class="text-6xl lg:text-8xl font-bold leading-tight mb-6">
      <span class="gradient-text font-gaming">QUICK</span> 
      <span style="color: #F5F5DC !important; font-weight: 300;">VIEW</span>
    </h1>
    <div class="h-2 w-32 bg-gradient-to-r from-orange-primary to-orange-accent rounded-full mx-auto mb-8 orange-glow"></div>
    <p class="text-xl md:text-2xl max-w-4xl mx-auto leading-relaxed" style="color: rgba(245, 245, 220, 0.9) !important;">
      Explore traditional Sri Lankan handicrafts with detailed views and instant shopping options
    </p>
  </div>
</section>

<!-- Quick View Section -->
<section id="quick-view" class="py-20">
  <div class="container mx-auto px-6 lg:px-12">

    <?php
      $pid = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
      $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
      $select_products->execute([$pid]);

      if($select_products->rowCount() > 0){
        $fetch_products = $select_products->fetch(PDO::FETCH_ASSOC);
    ?>
    <form action="" method="POST" class="group max-w-5xl mx-auto">
      <div class="product-card relative orange-glow">
        <!-- Header with price and close -->
        <div class="px-6 py-4 flex items-center justify-between">
          <div class="price-badge rounded-full">
            <i class="fas fa-tag mr-2"></i>
            <span>Rs <?= htmlspecialchars($fetch_products['price']); ?>/-</span>
          </div>

          <a href="shop.php" class="close-btn w-12 h-12 glass-effect rounded-full flex items-center justify-center hover-glow" aria-label="Back to shop">
            <i class="fas fa-times text-xl"></i>
          </a>
        </div>

        <!-- Product Image -->
        <div class="aspect-square overflow-hidden mx-6 rounded-2xl border-2 border-orange-primary/30">
          <img src="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>"
               alt="<?= htmlspecialchars($fetch_products['name']); ?>"
               class="product-image w-full h-full object-cover"
               onerror="this.src='uploaded_img/placeholder.png';">
        </div>

        <!-- Product Information -->
        <div class="p-8 md:p-10">
          <h3 class="product-title text-3xl md:text-4xl mb-4">
            <?= htmlspecialchars($fetch_products['name']); ?>
          </h3>

          <?php if(!empty($fetch_products['details'])): ?>
          <p class="text-description text-lg mb-8 leading-relaxed">
            <?= nl2br(htmlspecialchars($fetch_products['details'])); ?>
          </p>
          <?php endif; ?>

          <!-- Hidden Form Inputs -->
          <input type="hidden" name="pid" value="<?= (int)$fetch_products['id']; ?>">
          <input type="hidden" name="p_name" value="<?= htmlspecialchars($fetch_products['name']); ?>">
          <input type="hidden" name="p_price" value="<?= htmlspecialchars($fetch_products['price']); ?>">
          <input type="hidden" name="p_image" value="<?= htmlspecialchars($fetch_products['image']); ?>">

          <!-- Quantity Selector -->
          <div class="mb-8">
            <label class="block text-lg font-semibold mb-3" style="color: #F5F5DC !important;">
              <i class="fas fa-sort-numeric-up mr-2 text-orange-primary"></i>
              Quantity
            </label>
            <input type="number" min="1" value="1" name="p_qty"
                   class="qty-input w-40 px-5 py-4 rounded-xl text-center text-lg font-semibold">
          </div>

          <!-- Action Buttons -->
          <div class="grid md:grid-cols-2 gap-4">
            <button type="submit" name="add_to_cart"
                    class="btn-primary w-full py-5 rounded-xl text-lg font-bold">
              <i class="fas fa-shopping-cart mr-3"></i>
              Add to Cart
            </button>

            <button type="submit" name="add_to_wishlist"
                    class="btn-secondary w-full py-5 rounded-xl text-lg font-bold">
              <i class="fas fa-heart mr-3"></i>
              Add to Wishlist
            </button>
          </div>
        </div>
      </div>
    </form>
    <?php
      } else {
        echo '<div class="text-center">
                <div class="no-products glass-effect p-16 rounded-3xl max-w-lg mx-auto">
                  <i class="fas fa-box-open text-8xl mb-6" style="color: #FF6B35;"></i>
                  <h3 class="text-3xl font-bold mb-4" style="color: #F5F5DC !important;">No Product Found</h3>
                  <p class="text-lg mb-8" style="color: rgba(245, 245, 220, 0.8) !important;">The requested product could not be found in our collection.</p>
                  <a href="shop.php"
                     class="btn-primary inline-flex items-center justify-center px-8 py-4 rounded-xl text-lg font-bold">
                    <i class="fas fa-store mr-3"></i>
                    Browse Our Collection
                  </a>
                </div>
              </div>';
      }
    ?>
  </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

<!-- Display Messages -->
<?php
if(isset($message)){
   foreach($message as $msg){
      echo '<div class="message">
              <i class="fas fa-info-circle mr-3"></i>
              <span>'.ucfirst($msg).'</span>
            </div>';
   }
}
?>

<!-- Add auto-hide for messages -->
<script>
document.addEventListener('DOMContentLoaded', function() {
   const messages = document.querySelectorAll('.message');
   messages.forEach(function(message) {
      setTimeout(function() {
         message.style.opacity = '0';
         message.style.transform = 'translateX(100%)';
         setTimeout(function() {
            message.remove();
         }, 300);
      }, 4000);
   });
});
</script>

</body>
</html>