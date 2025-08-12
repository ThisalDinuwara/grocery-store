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
   <title>shop</title>

   <!-- Tailwind CDN (optional for live preview) -->
   <script src="https://cdn.tailwindcss.com"></script>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<section class="p-category">

   <a href="category.php?category=wood">Wood</a>
   <a href="category.php?category=clothes">clothes</a>
   <a href="category.php?category=wallarts">wallarts</a>
   <a href="category.php?category=brass">brass</a>

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
            $select_products = $conn->prepare("SELECT * FROM `products` ORDER BY id DESC LIMIT 6");
            $select_products->execute();
            if($select_products->rowCount() > 0){
               while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
         ?>
         <form action="" method="POST" class="group">
            <div class="bg-white rounded-3xl overflow-hidden shadow-lg border border-gray-100 relative transition duration-300 hover:-translate-y-1 hover:shadow-2xl">
               <!-- Price Badge -->
               <div class="absolute top-4 left-4 bg-gradient-to-r from-orange-500 to-red-600 text-white px-4 py-2 rounded-full font-bold text-sm z-10">
                  Rs <?= htmlspecialchars($fetch_products['price']); ?>/-
               </div>
               
               <!-- View Button -->
               <a href="view_page.php?pid=<?= (int)$fetch_products['id']; ?>" 
                  class="absolute top-4 right-4 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-gray-700 hover:bg-orange-500 hover:text-white transition-all duration-300 z-10">
                  <i class="fas fa-eye"></i>
               </a>

               <!-- Product Image -->
               <div class="aspect-square bg-gray-50 overflow-hidden">
                  <img src="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>" 
                       alt="<?= htmlspecialchars($fetch_products['name']); ?>" 
                       class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
               </div>

               <!-- Product Info -->
               <div class="p-6">
                  <h3 class="text-xl font-bold text-gray-900 mb-3"><?= htmlspecialchars($fetch_products['name']); ?></h3>
                  
                  <!-- Hidden Inputs -->
                  <input type="hidden" name="pid" value="<?= (int)$fetch_products['id']; ?>">
                  <input type="hidden" name="p_name" value="<?= htmlspecialchars($fetch_products['name']); ?>">
                  <input type="hidden" name="p_price" value="<?= htmlspecialchars($fetch_products['price']); ?>">
                  <input type="hidden" name="p_image" value="<?= htmlspecialchars($fetch_products['image']); ?>">
                  
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
   </div>
</section>








<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>