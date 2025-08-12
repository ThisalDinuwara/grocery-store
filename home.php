<?php

@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];
  if(!isset($user_id)){
   header('location:login.php');
};

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

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Home</title>

   <!-- Tailwind CDN -->
   <script src="https://cdn.tailwindcss.com"></script>
   <script>
      tailwind.config = {
         theme: {
            extend: {
               colors: {
                  primary: '#ff6b35',
                  secondary: '#1a1a1a',
                  accent: '#f8f9fa'
               }
            }
         }
      }
   </script>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      .gradient-bg {
         background: linear-gradient(135deg, #667eea 0%, #351059ff 100%);
      }
      .card-hover {
         transition: all 0.3s ease;
      }
      .card-hover:hover {
         transform: translateY(-8px);
         box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
      }
      .blob-bg {
         background: linear-gradient(45deg, #ff6b35, #f7931e);
         border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
         animation: blob 7s ease-in-out infinite;
      }
      @keyframes blob {
         0%, 100% { border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; }
         25% { border-radius: 58% 42% 75% 25% / 76% 46% 54% 24%; }
         50% { border-radius: 50% 50% 33% 67% / 55% 27% 73% 45%; }
         75% { border-radius: 33% 67% 58% 42% / 63% 68% 32% 37%; }
      }
   </style>
</head>
<body class="bg-gray-50">

<?php include 'header.php'; ?>

<!-- Hero Section -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden bg-gradient-to-br from-indigo-50 via-white to-cyan-50">
   <!-- Animated Background Elements -->
   <div class="absolute top-20 left-10 w-72 h-72 blob-bg opacity-10"></div>
   <div class="absolute bottom-20 right-10 w-96 h-96 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full opacity-10 blur-3xl"></div>
   
   <div class="container mx-auto px-6 lg:px-12 grid lg:grid-cols-2 gap-12 items-center relative z-10">
      <!-- Content -->
      <div class="space-y-8">
         <div class="space-y-4">
            <h1 class="text-5xl lg:text-7xl font-bold text-gray-900 leading-tight">
               Discover the 
               <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-red-600">Beauty</span>
               of Traditional Crafts
            </h1>
            <p class="text-xl text-gray-600 leading-relaxed max-w-2xl">
               Explore our exquisite collection of handmade treasures, where every piece tells a story of Sri Lankan heritage and craftsmanship passed down through generations.
            </p>
         </div>
         
         <div class="flex flex-col sm:flex-row gap-4">
            <a href="about.php" class="group bg-gradient-to-r from-orange-500 to-red-600 text-white px-8 py-4 rounded-full font-semibold text-lg hover:shadow-2xl hover:shadow-orange-500/25 transition-all duration-300 transform hover:scale-105 text-center">
               About Us
               <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
            </a>
            <a href="#products" class="border-2 border-gray-300 text-gray-700 px-8 py-4 rounded-full font-semibold text-lg hover:border-orange-500 hover:text-orange-500 transition-all duration-300 text-center">
               Shop Now
            </a>
         </div>
      </div>

      <!-- Hero Image Placeholder -->
      <!-- Hero Image -->
<div class="relative">
   <div class="w-full h-96 lg:h-[500px] rounded-3xl overflow-hidden">
      <img src="images/new.jpg" 
           alt="Sri Lankan Traditional Handicrafts" 
           class="w-full h-full object-cover">
   </div>
</div>

   </div>
</section>

<!-- Categories Section -->
<section class="py-20 bg-white">
   <div class="container mx-auto px-6 lg:px-12">
      <div class="text-center mb-16">
         <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Shop by Category</h2>
         <p class="text-xl text-gray-600 max-w-3xl mx-auto">Discover our carefully curated collections of traditional Sri Lankan crafts</p>
      </div>

      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
         <div class="group card-hover bg-white rounded-3xl p-8 shadow-lg border border-gray-100 text-center">
            <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl flex items-center justify-center">
               <img src="images/woods.png" alt="Wood" class="w-12 h-12 object-contain">
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-3">Wood</h3>
            <p class="text-gray-600 mb-6 leading-relaxed">Handcrafted wooden items showcasing traditional artistry and natural beauty.</p>
            <a href="category.php?category=fruits" class="inline-flex items-center text-orange-500 font-semibold hover:text-orange-600 transition-colors">
               Explore Wood
               <i class="fas fa-arrow-right ml-2 text-sm"></i>
            </a>
         </div>

         <div class="group card-hover bg-white rounded-3xl p-8 shadow-lg border border-gray-100 text-center">
            <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-purple-400 to-pink-500 rounded-2xl flex items-center justify-center">
               <img src="images/clothes.png" alt="Clothes" class="w-12 h-12 object-contain">
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-3">Clothes</h3>
            <p class="text-gray-600 mb-6 leading-relaxed">Traditional garments and textiles woven with cultural heritage.</p>
            <a href="category.php?category=meat" class="inline-flex items-center text-orange-500 font-semibold hover:text-orange-600 transition-colors">
               Explore Clothes
               <i class="fas fa-arrow-right ml-2 text-sm"></i>
            </a>
         </div>

         <div class="group card-hover bg-white rounded-3xl p-8 shadow-lg border border-gray-100 text-center">
            <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-green-400 to-blue-500 rounded-2xl flex items-center justify-center">
               <img src="images/wallarts.png" alt="Wall Arts" class="w-12 h-12 object-contain">
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-3">Wall Arts</h3>
            <p class="text-gray-600 mb-6 leading-relaxed">Beautiful wall decorations reflecting Sri Lankan artistic traditions.</p>
            <a href="category.php?category=vegitables" class="inline-flex items-center text-orange-500 font-semibold hover:text-orange-600 transition-colors">
               Explore Arts
               <i class="fas fa-arrow-right ml-2 text-sm"></i>
            </a>
         </div>

         <div class="group card-hover bg-white rounded-3xl p-8 shadow-lg border border-gray-100 text-center">
            <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-yellow-400 to-red-500 rounded-2xl flex items-center justify-center">
               <img src="images/brass.png" alt="Brass" class="w-12 h-12 object-contain">
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-3">Brass</h3>
            <p class="text-gray-600 mb-6 leading-relaxed">Exquisite brass items crafted by skilled traditional artisans.</p>
            <a href="category.php?category=fish" class="inline-flex items-center text-orange-500 font-semibold hover:text-orange-600 transition-colors">
               Explore Brass
               <i class="fas fa-arrow-right ml-2 text-sm"></i>
            </a>
         </div>
      </div>
   </div>
</section>

<!-- Products Section -->
<section id="products" class="py-20 bg-gray-50">
   <div class="container mx-auto px-6 lg:px-12">
      <div class="text-center mb-16">
         <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Latest Products</h2>
         <p class="text-xl text-gray-600 max-w-3xl mx-auto">Discover our newest collection of authentic Sri Lankan handicrafts</p>
      </div>

      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
         <?php
            $select_products = $conn->prepare("SELECT * FROM products LIMIT 6");
            $select_products->execute();
            if($select_products->rowCount() > 0){
               while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
         ?>
         <form action="" method="POST" class="group">
            <div class="card-hover bg-white rounded-3xl overflow-hidden shadow-lg border border-gray-100 relative">
               <!-- Price Badge -->
               <div class="absolute top-4 left-4 bg-gradient-to-r from-orange-500 to-red-600 text-white px-4 py-2 rounded-full font-bold text-sm z-10">
                  Rs <?= $fetch_products['price']; ?>/-
               </div>
               
               <!-- View Button -->
               <a href="view_page.php?pid=<?= $fetch_products['id']; ?>" 
                  class="absolute top-4 right-4 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-gray-700 hover:bg-orange-500 hover:text-white transition-all duration-300 z-10">
                  <i class="fas fa-eye"></i>
               </a>

               <!-- Product Image -->
               <div class="aspect-square bg-gray-50 overflow-hidden">
                  <img src="uploaded_img/<?= $fetch_products['image']; ?>" 
                       alt="<?= $fetch_products['name']; ?>" 
                       class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
               </div>

               <!-- Product Info -->
               <div class="p-6">
                  <h3 class="text-xl font-bold text-gray-900 mb-3"><?= $fetch_products['name']; ?></h3>
                  
                  <!-- Hidden Inputs -->
                  <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
                  <input type="hidden" name="p_name" value="<?= $fetch_products['name']; ?>">
                  <input type="hidden" name="p_price" value="<?= $fetch_products['price']; ?>">
                  <input type="hidden" name="p_image" value="<?= $fetch_products['image']; ?>">
                  
                  <!-- Quantity Input -->
                  <div class="mb-4">
                     <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                     <input type="number" min="1" value="1" name="p_qty" 
                            class="qty w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none transition-all">
                  </div>

                  <!-- Add to Cart Button -->
                  <button type="submit" name="add_to_cart" 
                          class="w-full bg-gradient-to-r from-orange-500 to-red-600 text-white py-4 rounded-xl font-semibold hover:shadow-xl hover:shadow-orange-500/25 transition-all duration-300 transform hover:scale-[1.02]">
                     <i class="fas fa-shopping-cart mr-2"></i>
                     Add to Cart
                  </button>
               </div>
            </div>
         </form>
         <?php
            }
         }else{
            echo '<div class="col-span-full text-center py-16">
                     <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                     <p class="text-2xl text-gray-500 font-medium">No products available yet!</p>
                  </div>';
         }
         ?>
      </div>

      <!-- View All Products Button -->
      <div class="text-center mt-12">
         <a href="shop.php" class="inline-flex items-center bg-gradient-to-r from-gray-800 to-gray-900 text-white px-8 py-4 rounded-full font-semibold text-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
            View All Products
            <i class="fas fa-arrow-right ml-2"></i>
         </a>
      </div>
   </div>
</section>

<?php include 'about.php'; ?>
<?php include 'footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>