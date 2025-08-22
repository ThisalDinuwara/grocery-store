<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
   exit;
};

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

if(isset($_GET['delete'])){

   $delete_id = $_GET['delete'];
   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE id = ?");
   $delete_wishlist_item->execute([$delete_id]);
   header('location:wishlist.php');
   exit;
}

if(isset($_GET['delete_all'])){

   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
   $delete_wishlist_item->execute([$user_id]);
   header('location:wishlist.php');
   exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>wishlist</title>

   <!-- Tailwind CDN -->
   <script src="https://cdn.tailwindcss.com"></script>
   <script>
      tailwind.config = {
         theme: {
            extend: {
               colors: {
                  /* Light theme palette (warm off-white) */
                  primary:  '#B77B3D',   // warm brown
                  secondary:'#D4A373',   // golden beige
                  accent:   '#8C6239',   // deep brown (icons)
                  ink:      '#2E1B0E',   // main text
                  soft:     '#6B4E2E'    // subtle text
               },
               fontFamily: {
                  gaming: ['Orbitron', 'monospace'],
                  inter:  ['Inter', 'sans-serif']
               }
            }
         }
      }
   </script>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <!-- Google Fonts -->
   <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

   <!-- Your existing site CSS (kept) -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      *{margin:0;padding:0;box-sizing:border-box}
      body{
         font-family:'Inter',sans-serif;
         background:linear-gradient(135deg,#FFFDF9 0%,#F7F3ED 50%,#EFE8DE 100%);
         color:#2E1B0E; /* ink */
         overflow-x:hidden;
      }

      .neon-glow{box-shadow:0 0 20px rgba(183,123,61,.18),0 0 40px rgba(212,163,115,.18),0 0 60px rgba(140,98,57,.12)}
      .glass-effect{background:rgba(255,255,255,.92);backdrop-filter:blur(10px);border:1px solid rgba(183,123,61,.22)}
      .hover-glow:hover{transform:translateY(-4px);box-shadow:0 12px 28px rgba(183,123,61,.18);transition:.3s ease}
      .floating-animation{animation:floating 3s ease-in-out infinite}
      @keyframes floating{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
      .gradient-text{background:linear-gradient(45deg,#B77B3D,#D4A373);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
      .hero-bg{
         background:
           radial-gradient(circle at 20% 80%, rgba(183,123,61,.18) 0%, transparent 55%),
           radial-gradient(circle at 80% 20%, rgba(212,163,115,.18) 0%, transparent 55%),
           radial-gradient(circle at 40% 40%, rgba(140,98,57,.18) 0%, transparent 55%);
      }

      .text-base{font-size:1.125rem!important}
      .text-lg{font-size:1.25rem!important}
      .text-xl{font-size:1.375rem!important}
      p,label,input,button,a,li{font-size:1.12rem}

      /* Product card — light */
      .product-card{
         background:linear-gradient(180deg,rgba(255,255,255,.98),rgba(252,247,238,.94));
         border:1px solid rgba(183,123,61,.26);
         border-radius:22px; backdrop-filter:blur(10px);
         transition:transform .4s ease, box-shadow .4s ease, border-color .4s ease; position:relative;overflow:hidden
      }
      .product-card:hover{transform:translateY(-6px) scale(1.01);border-color:rgba(183,123,61,.5);box-shadow:0 22px 48px rgba(183,123,61,.2)}
      .product-card .aspect-square{
         position:relative;border-radius:18px;border:1px solid rgba(183,123,61,.24);overflow:hidden;
         background:radial-gradient(600px 120px at 20% 0%, rgba(212,163,115,.18), transparent 60%)
      }
      .product-card img{transition:transform .6s ease}
      .group:hover .product-card img{transform:scale(1.06)}
      .price-badge{
         font-size:1.05rem;letter-spacing:.3px;padding:.6rem 1rem;border:1px solid rgba(183,123,61,.25);
         box-shadow:0 6px 18px rgba(183,123,61,.22)
      }
      .product-title{font-weight:800;letter-spacing:.2px;color:#2E1B0E;line-height:1.25}
      .product-card label{color:#6B4E2E;font-weight:600}
      .product-card .qty{background:#fff;color:#2E1B0E;border:1px solid rgba(183,123,61,.26)}
      .product-card .qty:focus{outline:none;box-shadow:0 0 0 3px rgba(183,123,61,.22)}
      .chip{display:inline-flex;align-items:center;gap:.5rem;border-radius:9999px;padding:.4rem .75rem}
      .icon-accent{color:#8C6239}
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<!-- Hero -->
<section class="relative min-h-[45vh] md:min-h-[55vh] flex items-center justify-center overflow-hidden hero-bg">
   <div class="absolute top-10 left-10 w-72 h-72 bg-gradient-to-r from-[rgba(183,123,61,0.18)] to-[rgba(212,163,115,0.18)] rounded-full blur-3xl floating-animation"></div>
   <div class="absolute bottom-10 right-10 w-64 h-64 bg-gradient-to-r from-[rgba(212,163,115,0.18)] to-[rgba(140,98,57,0.18)] rounded-full blur-3xl floating-animation" style="animation-delay:1s"></div>

   <div class="container mx-auto px-6 lg:px-12 grid lg:grid-cols-2 gap-10 items-center relative z-10">
      <div class="space-y-6">
         <h1 class="text-5xl lg:text-7xl font-bold leading-tight">
            <span class="gradient-text font-gaming">YOUR</span><br>
            <span class="text-ink">WISHLIST</span>
         </h1>
         <div class="h-1 w-28 bg-gradient-to-r from-[#B77B3D] to-[#D4A373] rounded-full"></div>
         <p class="text-xl text-soft/80 max-w-2xl">All the handicrafts you love, saved in one place. Move them to cart whenever you’re ready.</p>
         <div class="flex flex-wrap gap-3">
           <span class="chip glass-effect"><i class="fa-regular fa-heart icon-accent"></i> Saved</span>
           <span class="chip glass-effect"><i class="fa-solid fa-truck-fast icon-accent"></i> Fast Delivery</span>
           <span class="chip glass-effect"><i class="fa-solid fa-shield-heart icon-accent"></i> Secure Checkout</span>
         </div>
      </div>

      <div class="relative">
         <div class="glass-effect p-6 md:p-8 rounded-3xl neon-glow">
            <div class="aspect-square rounded-2xl overflow-hidden">
               <img src="images/new.jpg" alt="Wishlist" class="w-full h-full object-cover">
            </div>
            <div class="absolute -top-4 -right-4 w-16 h-16 bg-gradient-to-r from-[#B77B3D] to-[#D4A373] text-white rounded-2xl flex items-center justify-center floating-animation">
               <i class="fas fa-star text-xl"></i>
            </div>
            <div class="absolute -bottom-4 -left-4 w-14 h-14 bg-gradient-to-r from-[#D4A373] to-[#B77B3D] text-white rounded-xl flex items-center justify-center floating-animation" style="animation-delay:.5s">
               <i class="fas fa-heart text-lg"></i>
            </div>
         </div>
      </div>
   </div>
</section>

<!-- Wishlist Grid (functional) -->
<section class="py-16 relative">
  <div class="container mx-auto px-6 lg:px-12">

    <div class="text-center mb-12">
      <h2 class="text-4xl lg:text-5xl font-bold mb-4">
        <span class="gradient-text font-gaming">SAVED ITEMS</span>
      </h2>
      <div class="h-1 w-24 bg-gradient-to-r from-[#B77B3D] to-[#D4A373] rounded-full mx-auto"></div>
      <p class="text-lg text-soft/80 mt-6 max-w-3xl mx-auto">
        Review your favorites. You can move items to cart, adjust quantity, or remove them any time.
      </p>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
      <?php
         $grand_total = 0;
         $select_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
         $select_wishlist->execute([$user_id]);
         if($select_wishlist->rowCount() > 0){
            while($fetch_wishlist = $select_wishlist->fetch(PDO::FETCH_ASSOC)){ 
      ?>
      <form action="" method="POST" class="group">
         <div class="product-card p-6 relative h-full flex flex-col">
            <!-- Price badge -->
            <div class="absolute top-6 left-6 price-badge bg-gradient-to-r from-[#B77B3D] to-[#D4A373] text-white rounded-full font-bold text-sm z-10 neon-glow px-4 py-2">
               Rs <?= htmlspecialchars($fetch_wishlist['price']); ?>/-
            </div>

            <!-- Remove + View -->
            <div class="absolute top-6 right-6 flex flex-col gap-2 z-10">
               <a href="wishlist.php?delete=<?= $fetch_wishlist['id']; ?>" onclick="return confirm('delete this from wishlist?');"
                  class="w-11 h-11 glass-effect rounded-full flex items-center justify-center hover:text-white hover:bg-gradient-to-r hover:from-[#B77B3D] hover:to-[#D4A373] transition"
                  title="Remove"><i class="fas fa-times icon-accent"></i></a>
               <a href="view_page.php?pid=<?= $fetch_wishlist['pid']; ?>"
                  class="w-11 h-11 glass-effect rounded-full flex items-center justify-center hover:text-white hover:bg-gradient-to-r hover:from-[#B77B3D] hover:to-[#D4A373] transition"
                  title="View"><i class="fas fa-eye icon-accent"></i></a>
            </div>

            <!-- Image -->
            <div class="aspect-square rounded-2xl overflow-hidden mb-6">
               <img src="uploaded_img/<?= htmlspecialchars($fetch_wishlist['image']); ?>" alt="<?= htmlspecialchars($fetch_wishlist['name']); ?>" class="w-full h-full object-cover">
            </div>

            <!-- Info -->
            <div class="space-y-4 mt-auto">
               <h3 class="product-title text-xl"><?= htmlspecialchars($fetch_wishlist['name']); ?></h3>

               <!-- Hidden inputs -->
               <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_wishlist['pid']); ?>">
               <input type="hidden" name="p_name" value="<?= htmlspecialchars($fetch_wishlist['name']); ?>">
               <input type="hidden" name="p_price" value="<?= htmlspecialchars($fetch_wishlist['price']); ?>">
               <input type="hidden" name="p_image" value="<?= htmlspecialchars($fetch_wishlist['image']); ?>">

               <!-- Quantity -->
               <div class="flex items-center gap-3">
                  <label class="text-sm font-medium text-soft">QTY:</label>
                  <input type="number" min="1" value="1" class="qty w-24 px-3 py-2 glass-effect rounded-lg text-ink text-center focus:ring-2 focus:ring-[rgb(183,123,61)]" name="p_qty">
               </div>

               <!-- Add to cart -->
               <input type="submit" value="add to cart" name="add_to_cart"
                      class="w-full cursor-pointer bg-gradient-to-r from-[#B77B3D] to-[#D4A373] text-white py-3.5 rounded-xl font-semibold hover-glow neon-glow transition-all duration-300 transform hover:scale-[1.02]">
            </div>
         </div>
      </form>
      <?php
         $grand_total += (float)$fetch_wishlist['price'];
         }
      }else{
         echo '<div class="col-span-full text-center">
                 <div class="glass-effect p-12 rounded-3xl max-w-md mx-auto">
                    <i class="fas fa-heart-broken text-6xl" style="color:#8C6239"></i>
                    <p class="text-2xl text-soft font-medium mt-4">your wishlist is empty</p>
                 </div>
               </div>';
      }
      ?>
    </div>

    <!-- Totals / actions -->
    <div class="mt-12">
      <div class="glass-effect rounded-3xl p-6 md:p-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
          <p class="text-xl">
            grand total :
            <span class="font-extrabold gradient-text">Rs <?= number_format((float)$grand_total, 2); ?>/-</span>
          </p>
          <div class="flex flex-wrap gap-4">
            <a href="shop.php" class="inline-flex items-center bg-gradient-to-r from-[#8C7B6A] to-[#A9927D] glass-effect text-ink px-6 py-3 rounded-full font-semibold hover-glow transition">
              <i class="fas fa-store mr-2 icon-accent"></i> continue shopping
            </a>
            <a href="wishlist.php?delete_all" class="inline-flex items-center px-6 py-3 rounded-full font-semibold border border-red-300 text-red-700 hover:bg-red-100 transition <?= ($grand_total > 0)?'':'pointer-events-none opacity-50'; ?>">
              <i class="fas fa-trash mr-2"></i> delete all
            </a>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
