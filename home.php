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

   <!-- Tailwind CDN (optional for live preview) -->
   <script src="https://cdn.tailwindcss.com"></script>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="home-bg bg-cover bg-center">
   <section class="home flex items-center min-h-[60vh]">
      <div class="content w-[50rem]">
         <h3 class="text-3xl text-black uppercase mt-4" style="color: white; margin:5px;">Discover the Beauty of Traditional Crafts</h3>
         <p class="text-lg text-gray-600 py-4 leading-7" style="color: wheat;">Explore our exquisite collection of handmade treasures, where every piece tells a story of Sri Lankan heritage and craftsmanship passed down through generations.</p>
         <a href="about.php" class="btn inline-block w-auto">about us</a>
      </div>
   </section>
</div>

<section class="home-category pb-0">
   <h1 class="title text-4xl uppercase text-center mb-8">shop by category</h1>
   <div class="box-container grid gap-6 justify-center">
      <div class="box p-6 rounded shadow bg-white text-center">
         <img src="images/woods.png" alt="">
         <h3 class="text-xl uppercase text-black py-2">wood</h3>
         <p class="text-sm text-gray-600 leading-6">Fresh and seasonal organic fruits from trusted growers.</p>
         <a href="category.php?category=fruits" class="btn">wood</a>
      </div>
      <div class="box p-6 rounded shadow bg-white text-center">
         <img src="images/clothes.png" alt="">
         <h3 class="text-xl uppercase text-black py-2">clothes</h3>
         <p class="text-sm text-gray-600 leading-6">High-quality, ethically sourced meat products.</p>
         <a href="category.php?category=meat" class="btn">clothes</a>
      </div>
      <div class="box p-6 rounded shadow bg-white text-center">
         <img src="images/wallarts.png" alt="">
         <h3 class="text-xl uppercase text-black py-2">wallarts</h3>
         <p class="text-sm text-gray-600 leading-6">Locally grown vegetables with natural freshness.</p>
         <a href="category.php?category=vegitables" class="btn">wallarts</a>
      </div>
      <div class="box p-6 rounded shadow bg-white text-center">
         <img src="images/brass.png" alt="">
         <h3 class="text-xl uppercase text-black py-2">brass</h3>
         <p class="text-sm text-gray-600 leading-6">Catch of the day, fresh from local fishermen.</p>
         <a href="category.php?category=fish" class="btn">brass</a>
      </div>
   </div>
</section>

<section class="products mt-10">
   <h1 class="title text-4xl uppercase text-center mb-8">latest products</h1>
   <div class="box-container grid gap-6 justify-center">
      <?php
         $select_products = $conn->prepare("SELECT * FROM products LIMIT 6");
         $select_products->execute();
         if($select_products->rowCount() > 0){
            while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
      ?>
      <form action="" class="box p-6 rounded shadow bg-white text-center relative" method="POST">
         <div class="price absolute top-4 left-4 bg-red-600 text-white px-4 py-2 rounded text-lg">Rs<span><?= $fetch_products['price']; ?></span>/-</div>
         <a href="view_page.php?pid=<?= $fetch_products['id']; ?>" class="fas fa-eye absolute top-4 right-4 p-2 border rounded text-black bg-white hover:bg-black hover:text-white"></a>
         <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="" class="w-full mb-4">
         <div class="name text-xl text-black py-2"><?= $fetch_products['name']; ?></div>
         <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
         <input type="hidden" name="p_name" value="<?= $fetch_products['name']; ?>">
         <input type="hidden" name="p_price" value="<?= $fetch_products['price']; ?>">
         <input type="hidden" name="p_image" value="<?= $fetch_products['image']; ?>">
         <input type="number" min="1" value="1" name="p_qty" class="qty mt-2 block w-full p-3 text-lg border rounded">
        <!-- <input type="submit" value="add to wishlist" class="option-btn bg-yellow-500 text-white hover:bg-black rounded mt-2 p-3 text-lg w-full" name="add_to_wishlist">-->
         <input type="submit" value="add to cart" class="btn bg-green-600 text-white hover:bg-black rounded mt-4 p-3 text-lg w-full" name="add_to_cart">
      </form>
      <?php
         }
      }else{
         echo '<p class="empty text-center text-red-500 text-xl">no products added yet!</p>';
      }
      ?>
   </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
