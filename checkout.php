<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
};

if(isset($_POST['order'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $method = $_POST['method'];
   $method = filter_var($method, FILTER_SANITIZE_STRING);
   $address = 'flat no. '. $_POST['flat'] .' '. $_POST['street'] .' '. $_POST['city'] .' '. $_POST['state'] .' '. $_POST['country'] .' - '. $_POST['pin_code'];
   $address = filter_var($address, FILTER_SANITIZE_STRING);
   $placed_on = date('d-M-Y');

   $cart_total = 0;
   $cart_products[] = '';

   $cart_query = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $cart_query->execute([$user_id]);
   if($cart_query->rowCount() > 0){
      while($cart_item = $cart_query->fetch(PDO::FETCH_ASSOC)){
         $cart_products[] = $cart_item['name'].' ( '.$cart_item['quantity'].' )';
         $sub_total = ($cart_item['price'] * $cart_item['quantity']);
         $cart_total += $sub_total;
      };
   };

   $total_products = implode(', ', $cart_products);

   $order_query = $conn->prepare("SELECT * FROM `orders` WHERE name = ? AND number = ? AND email = ? AND method = ? AND address = ? AND total_products = ? AND total_price = ?");
   $order_query->execute([$name, $number, $email, $method, $address, $total_products, $cart_total]);

   if($cart_total == 0){
      $message[] = 'your cart is empty';
   }elseif($order_query->rowCount() > 0){
      $message[] = 'order placed already!';
   }else{
      $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, placed_on) VALUES(?,?,?,?,?,?,?,?,?)");
      $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $cart_total, $placed_on]);
      $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
      $delete_cart->execute([$user_id]);
      $message[] = 'order placed successfully!';
   }

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>checkout</title>

   <!-- Tailwind -->
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
             gaming: ['Orbitron','monospace'],
             inter: ['Inter','sans-serif']
           }
         }
       }
     }
   </script>

   <!-- Icons + your css -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

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
      .glass-effect{background:rgba(255,255,255,.08);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.18)}
      .hover-glow:hover{transform:translateY(-4px);box-shadow:0 10px 25px rgba(139,69,19,.35);transition:.3s ease}
      .gradient-text{background:linear-gradient(45deg,#8B4513,#A0522D,#D2B48C);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
      .divider{height:1px;background:linear-gradient(90deg,rgba(139,69,19,.45),rgba(210,180,140,.45))}
      .chip{display:inline-flex;align-items:center;gap:.5rem;border-radius:9999px;padding:.35rem .75rem}
      /* Inputs on dark glass */
      .input-lite{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);color:#fff}
      .input-lite::placeholder{color:#d9cbb9}
      .input-lite:focus{outline:none;box-shadow:0 0 0 3px rgba(210,180,140,.35)}
      /* Cards */
      .card-dark{border-radius:22px}
   </style>
</head>
<body>

<?php include 'header.php'; ?>

<?php
// Collect cart items first
$cart_grand_total = 0;
$select_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
$select_cart_items->execute([$user_id]);
$cart_items = $select_cart_items->fetchAll(PDO::FETCH_ASSOC);
foreach ($cart_items as $ci) {
  $cart_grand_total += ((float)$ci['price'] * (int)$ci['quantity']);
}
$cart_count = count($cart_items);
?>

<!-- Header / Hero -->
<section class="relative min-h-[30vh] md:min-h-[36vh] flex items-center justify-center overflow-hidden hero-bg">
  <div class="absolute top-10 left-10 w-72 h-72 bg-gradient-to-r from-[rgba(139,69,19,0.22)] to-[rgba(210,180,140,0.22)] rounded-full blur-3xl"></div>
  <div class="absolute bottom-10 right-10 w-64 h-64 bg-gradient-to-r from-[rgba(160,82,45,0.22)] to-[rgba(139,69,19,0.22)] rounded-full blur-3xl"></div>
  <div class="container mx-auto px-6 lg:px-12 text-center relative z-10">
     <h1 class="text-5xl lg:text-7xl font-bold leading-tight">
       <span class="gradient-text font-gaming">CHECK</span><span class="text-white">OUT</span>
     </h1>
     <div class="h-1 w-28 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] rounded-full mx-auto mt-4"></div>
     <p class="text-lg md:text-xl text-gray-200 max-w-3xl mx-auto mt-6">Review your cart and place your order securely.</p>
  </div>
</section>

<!-- Checkout -->
<section id="checkout" class="py-16">
  <div class="container mx-auto px-6 lg:px-12">
    <div class="grid md:grid-cols-2 gap-8">

      <!-- Cart Summary -->
      <div class="glass-effect neon-glow card-dark overflow-hidden">
        <div class="px-6 py-4 flex items-center justify-between">
          <span class="px-3 py-1.5 rounded-full bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white text-sm font-bold">
            <i class="fas fa-shopping-bag mr-2"></i>Cart Summary
          </span>
          <span class="px-3 py-1.5 rounded-full bg-white/10 text-white text-xs font-semibold">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-300 mr-2"></span><?= $cart_count; ?> item(s)
          </span>
        </div>
        <div class="divider"></div>

        <div class="p-6">
          <?php if($cart_count > 0): ?>
            <ul class="divide-y divide-white/10">
              <?php foreach($cart_items as $ci): $line_total=((float)$ci['price']*(int)$ci['quantity']); ?>
              <li class="py-4 flex items-start justify-between gap-4">
                <div>
                  <p class="font-semibold text-white"><?= htmlspecialchars($ci['name']); ?></p>
                  <p class="text-sm text-[#EADDCB]">Rs <?= number_format((float)$ci['price'],2); ?>/- × <?= (int)$ci['quantity']; ?></p>
                </div>
                <div class="font-semibold text-white">Rs <?= number_format($line_total,2); ?>/-</div>
              </li>
              <?php endforeach; ?>
            </ul>

            <div class="mt-6 rounded-2xl bg-white/10 p-4 space-y-2 border border-white/15">
              <div class="flex items-center justify-between text-[#EADDCB]">
                <span>Subtotal</span><span>Rs <?= number_format($cart_grand_total, 2); ?>/-</span>
              </div>
              <div class="flex items-center justify-between text-[#EADDCB]">
                <span>Shipping</span><span>FREE</span>
              </div>
              <div class="pt-3 border-t border-white/10 flex items-center justify-between">
                <span class="text-lg font-bold text-white">Grand Total</span>
                <span class="text-lg font-extrabold text-white">Rs <?= number_format($cart_grand_total, 2); ?>/-</span>
              </div>
            </div>

            <a href="cart.php" class="mt-5 inline-flex items-center justify-center w-full rounded-xl glass-effect py-3 font-semibold hover-glow">
              <i class="fas fa-pen mr-2"></i> Edit Cart
            </a>
          <?php else: ?>
            <div class="text-center py-12">
              <i class="fas fa-box-open text-6xl text-white/40 mb-4"></i>
              <p class="text-2xl text-white/80 font-medium">Your cart is empty!</p>
              <a href="shop.php" class="mt-6 inline-flex items-center justify-center bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white px-6 py-3 rounded-xl font-semibold hover-glow">
                <i class="fas fa-store mr-2"></i> Shop Now
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Place Order -->
      <div class="glass-effect neon-glow card-dark overflow-hidden">
        <div class="px-6 py-4 flex items-center justify-between">
          <span class="px-3 py-1.5 rounded-full bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white text-sm font-bold">
            <i class="fas fa-clipboard-check mr-2"></i>Place Your Order
          </span>
          <span class="px-3 py-1.5 rounded-full bg-white/10 text-white text-xs font-semibold">
            <i class="fas fa-shield-alt mr-1"></i> Secure
          </span>
        </div>
        <div class="divider"></div>

        <form action="" method="POST" class="p-6">
          <div class="grid sm:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-[#EADDCB] mb-2">Your Name</label>
              <input type="text" name="name" placeholder="Enter your name" class="w-full px-4 py-3 rounded-xl input-lite" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-[#EADDCB] mb-2">Your Number</label>
              <input type="number" name="number" placeholder="Enter your number" class="w-full px-4 py-3 rounded-xl input-lite" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-[#EADDCB] mb-2">Your Email</label>
              <input type="email" name="email" placeholder="Enter your email" class="w-full px-4 py-3 rounded-xl input-lite" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-[#EADDCB] mb-2">Payment Method</label>
              <select name="method" class="w-full px-4 py-3 rounded-xl input-lite" required>
                <option value="cash on delivery">Cash on Delivery</option>
                <option value="credit card">Credit Card</option>
                <option value="paytm">Paytm</option>
                <option value="paypal">PayPal</option>
              </select>
            </div>
            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-[#EADDCB] mb-2">Address Line 01</label>
              <input type="text" name="flat" placeholder="e.g. Flat number" class="w-full px-4 py-3 rounded-xl input-lite" required>
            </div>
            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-[#EADDCB] mb-2">Address Line 02</label>
              <input type="text" name="street" placeholder="e.g. Street name" class="w-full px-4 py-3 rounded-xl input-lite" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-[#EADDCB] mb-2">City</label>
              <input type="text" name="city" placeholder="e.g. Colombo" class="w-full px-4 py-3 rounded-xl input-lite" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-[#EADDCB] mb-2">State/Province</label>
              <input type="text" name="state" placeholder="e.g. Western" class="w-full px-4 py-3 rounded-xl input-lite" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-[#EADDCB] mb-2">Country</label>
              <input type="text" name="country" placeholder="e.g. Sri Lanka" class="w-full px-4 py-3 rounded-xl input-lite" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-[#EADDCB] mb-2">PIN Code</label>
              <input type="number" min="0" name="pin_code" placeholder="e.g. 123456" class="w-full px-4 py-3 rounded-xl input-lite" required>
            </div>
          </div>

          <?php $disabled = ($cart_grand_total <= 0); ?>
          <button type="submit" name="order" <?= $disabled ? 'disabled' : '' ?>
            class="mt-6 w-full bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white py-4 rounded-xl font-semibold hover-glow <?= $disabled ? 'opacity-50 cursor-not-allowed' : '' ?>">
            <i class="fas fa-lock mr-2"></i> Place Order
          </button>
          <?php if($disabled): ?>
            <p class="mt-3 text-sm text-[#EADDCB] text-center">Add items to your cart to place an order.</p>
          <?php endif; ?>
        </form>
      </div>

    </div>
  </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
