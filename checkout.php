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
<!-- Tailwind CDN (optional for live preview) -->
   <script src="https://cdn.tailwindcss.com"></script>


   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

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

<!-- Checkout (modern, no overlapping text) -->
<section id="checkout" class="py-20 bg-gray-50">
  <div class="container mx-auto px-6 lg:px-12">
    <!-- Header -->
    <div class="text-center mb-16">
      <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Checkout</h2>
      <p class="text-xl text-gray-600 max-w-3xl mx-auto">Review your cart and place your order securely</p>
    </div>

    <div class="grid md:grid-cols-2 gap-8">
      <!-- Cart Summary -->
      <div class="rounded-3xl overflow-hidden shadow-lg border border-gray-100 bg-white">
        <!-- Gradient header bar (inside the card) -->
        <div class="bg-gradient-to-r from-orange-500 to-red-600 px-4 py-3">
          <div class="flex items-center justify-between">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white text-gray-900 text-sm font-bold">
              <i class="fas fa-shopping-bag text-orange-500"></i> Cart Summary
            </span>
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/20 text-white text-xs font-semibold">
              <span class="w-2 h-2 rounded-full bg-emerald-300"></span> <?= $cart_count; ?> item(s)
            </span>
          </div>
        </div>

        <div class="p-6">
          <?php if($cart_count > 0): ?>
            <ul class="divide-y divide-gray-100">
              <?php foreach($cart_items as $ci): $line_total=((float)$ci['price']*(int)$ci['quantity']); ?>
              <li class="py-4 flex items-start justify-between gap-4">
                <div>
                  <p class="font-semibold text-gray-900"><?= htmlspecialchars($ci['name']); ?></p>
                  <p class="text-sm text-gray-500">Rs <?= number_format((float)$ci['price'],2); ?>/- × <?= (int)$ci['quantity']; ?></p>
                </div>
                <div class="font-semibold text-gray-900">Rs <?= number_format($line_total,2); ?>/-</div>
              </li>
              <?php endforeach; ?>
            </ul>

            <div class="mt-6 rounded-2xl bg-gray-50 p-4 space-y-2">
              <div class="flex items-center justify-between text-gray-700">
                <span>Subtotal</span><span>Rs <?= number_format($cart_grand_total, 2); ?>/-</span>
              </div>
              <div class="flex items-center justify-between text-gray-700">
                <span>Shipping</span><span>FREE</span>
              </div>
              <div class="pt-3 border-t border-gray-200 flex items-center justify-between">
                <span class="text-lg font-bold text-gray-900">Grand Total</span>
                <span class="text-lg font-extrabold text-gray-900">Rs <?= number_format($cart_grand_total, 2); ?>/-</span>
              </div>
            </div>

            <a href="cart.php" class="mt-5 inline-flex items-center justify-center w-full rounded-xl border border-gray-200 py-3 font-semibold text-gray-700 hover:bg-gray-50 transition">
              <i class="fas fa-pen mr-2"></i> Edit Cart
            </a>
          <?php else: ?>
            <div class="text-center py-12">
              <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
              <p class="text-2xl text-gray-500 font-medium">Your cart is empty!</p>
              <a href="shop.php" class="mt-6 inline-flex items-center justify-center bg-gradient-to-r from-orange-500 to-red-600 text-white px-6 py-3 rounded-xl font-semibold hover:shadow-xl hover:shadow-orange-500/25 transition">
                <i class="fas fa-store mr-2"></i> Shop Now
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Place Order -->
      <div class="rounded-3xl overflow-hidden shadow-lg border border-gray-100 bg-white">
        <!-- Gradient header bar -->
        <div class="bg-gradient-to-r from-orange-500 to-red-600 px-4 py-3">
          <div class="flex items-center justify-between">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white text-gray-900 text-sm font-bold">
              <i class="fas fa-clipboard-check text-orange-500"></i> Place Your Order
            </span>
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/20 text-white text-xs font-semibold">
              <i class="fas fa-shield-alt"></i> Secure
            </span>
          </div>
        </div>

        <form action="" method="POST" class="p-6">
          <div class="grid sm:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Your Name</label>
              <input type="text" name="name" placeholder="Enter your name" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Your Number</label>
              <input type="number" name="number" placeholder="Enter your number" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Your Email</label>
              <input type="email" name="email" placeholder="Enter your email" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
              <select name="method" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none" required>
                <option value="cash on delivery">Cash on Delivery</option>
                <option value="credit card">Credit Card</option>
                <option value="paytm">Paytm</option>
                <option value="paypal">PayPal</option>
              </select>
            </div>
            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Address Line 01</label>
              <input type="text" name="flat" placeholder="e.g. Flat number" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none" required>
            </div>
            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Address Line 02</label>
              <input type="text" name="street" placeholder="e.g. Street name" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
              <input type="text" name="city" placeholder="e.g. Colombo" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">State/Province</label>
              <input type="text" name="state" placeholder="e.g. Western" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
              <input type="text" name="country" placeholder="e.g. Sri Lanka" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">PIN Code</label>
              <input type="number" min="0" name="pin_code" placeholder="e.g. 123456" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none" required>
            </div>
          </div>

          <?php $disabled = ($cart_grand_total <= 0); ?>
          <button type="submit" name="order" <?= $disabled ? 'disabled' : '' ?>
            class="mt-6 w-full bg-gradient-to-r from-orange-500 to-red-600 text-white py-4 rounded-xl font-semibold hover:shadow-xl hover:shadow-orange-500/25 transition duration-300 transform hover:scale-[1.02] <?= $disabled ? 'opacity-50 cursor-not-allowed' : '' ?>">
            <i class="fas fa-lock mr-2"></i> Place Order
          </button>
          <?php if($disabled): ?>
            <p class="mt-3 text-sm text-gray-500 text-center">Add items to your cart to place an order.</p>
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