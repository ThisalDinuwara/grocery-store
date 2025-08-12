<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
};

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
   $delete_cart_item->execute([$delete_id]);
   header('location:cart.php');
}

if(isset($_GET['delete_all'])){
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
   $delete_cart_item->execute([$user_id]);
   header('location:cart.php');
}

if(isset($_POST['update_qty'])){
   $cart_id = $_POST['cart_id'];
   $p_qty = $_POST['p_qty'];
   $p_qty = filter_var($p_qty, FILTER_SANITIZE_STRING);
   $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
   $update_qty->execute([$p_qty, $cart_id]);
   $message[] = 'cart quantity updated';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>shopping cart</title>

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
// Fetch all items first
$grand_total = 0;
$select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
$select_cart->execute([$user_id]);
$cart_items = $select_cart->fetchAll(PDO::FETCH_ASSOC);
?>
<section id="cart" class="py-20 bg-gray-50">
  <div class="container mx-auto px-6 lg:px-12">
    <!-- Header (matches products section) -->
    <div class="text-center mb-16">
      <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Shopping Cart</h2>
      <p class="text-xl text-gray-600 max-w-3xl mx-auto">Review items, update quantities, or proceed to checkout</p>
    </div>

    <?php if(count($cart_items) > 0): ?>
    <div class="grid lg:grid-cols-3 gap-8">
      <!-- Items List -->
      <div class="lg:col-span-2 space-y-6">
        <?php foreach($cart_items as $fetch_cart): 
              $sub_total = ((float)$fetch_cart['price'] * (int)$fetch_cart['quantity']);
              $grand_total += $sub_total;
        ?>
        <form action="" method="POST"
              class="group bg-white rounded-3xl overflow-hidden shadow-lg border border-gray-100">
          <!-- Card header -->
          <div class="bg-gradient-to-r from-orange-500 to-red-600 px-4 py-3 flex items-center justify-between">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white text-gray-900 text-sm font-bold">
              <i class="fas fa-tag text-orange-500"></i>
              Rs <?= number_format((float)$fetch_cart['price'], 2); ?>/-
            </div>
            <div class="flex items-center gap-2">
              <a href="view_page.php?pid=<?= (int)$fetch_cart['pid']; ?>"
                 class="w-9 h-9 bg-white/90 rounded-full flex items-center justify-center text-gray-700 hover:bg-orange-500 hover:text-white transition"
                 title="View item">
                <i class="fas fa-eye"></i>
              </a>
              <a href="cart.php?delete=<?= (int)$fetch_cart['id']; ?>" onclick="return confirm('Delete this from cart?');"
                 class="w-9 h-9 bg-white/90 rounded-full flex items-center justify-center text-gray-700 hover:bg-red-500 hover:text-white transition"
                 title="Remove">
                <i class="fas fa-times"></i>
              </a>
            </div>
          </div>

          <!-- Card body -->
          <div class="p-6">
            <div class="flex flex-col sm:flex-row gap-6">
              <div class="w-full sm:w-40 h-40 bg-gray-50 rounded-2xl overflow-hidden flex-shrink-0">
                <img src="uploaded_img/<?= htmlspecialchars($fetch_cart['image']); ?>"
                     alt="<?= htmlspecialchars($fetch_cart['name']); ?>"
                     class="w-full h-full object-cover"
                     onerror="this.src='uploaded_img/placeholder.png'">
              </div>

              <div class="flex-1">
                <h3 class="text-xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($fetch_cart['name']); ?></h3>

                <input type="hidden" name="cart_id" value="<?= (int)$fetch_cart['id']; ?>">

                <div class="grid sm:grid-cols-2 gap-4 items-end">
                  <!-- Qty control -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                    <div class="flex items-center gap-2">
                      <input type="number" min="1" name="p_qty" value="<?= (int)$fetch_cart['quantity']; ?>"
                             class="w-28 px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none">
                      <button type="submit" name="update_qty"
                              class="px-4 py-3 rounded-xl font-semibold border border-gray-200 bg-white hover:bg-gray-50 transition">
                        Update
                      </button>
                    </div>
                  </div>

                  <!-- Subtotal -->
                  <div class="sm:text-right">
                    <p class="text-sm text-gray-500 mb-1">Sub Total</p>
                    <p class="text-2xl font-extrabold text-gray-900">
                      Rs <?= number_format($sub_total, 2); ?>/-
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
        <?php endforeach; ?>
      </div>

      <!-- Summary -->
      <div class="bg-white rounded-3xl overflow-hidden shadow-lg border border-gray-100 h-max">
        <div class="bg-gradient-to-r from-orange-500 to-red-600 px-4 py-3">
          <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white text-gray-900 text-sm font-bold">
            <i class="fas fa-receipt text-orange-500"></i> Order Summary
          </div>
        </div>

        <div class="p-6">
          <div class="rounded-2xl bg-gray-50 p-4 space-y-2">
            <div class="flex items-center justify-between text-gray-700">
              <span>Items</span><span><?= count($cart_items); ?></span>
            </div>
            <div class="flex items-center justify-between text-gray-700">
              <span>Subtotal</span><span>Rs <?= number_format($grand_total, 2); ?>/-</span>
            </div>
            <div class="flex items-center justify-between text-gray-700">
              <span>Shipping</span><span>FREE</span>
            </div>
            <div class="pt-3 border-t border-gray-200 flex items-center justify-between">
              <span class="text-lg font-bold text-gray-900">Grand Total</span>
              <span class="text-lg font-extrabold text-gray-900">Rs <?= number_format($grand_total, 2); ?>/-</span>
            </div>
          </div>

          <div class="mt-6 grid gap-3">
            <a href="shop.php"
               class="text-center rounded-xl border border-gray-200 py-3 font-semibold text-gray-700 hover:bg-gray-50 transition">
              Continue Shopping
            </a>

            <a href="cart.php?delete_all"
               class="text-center rounded-xl border border-red-200 text-red-600 py-3 font-semibold hover:bg-red-50 transition <?= ($grand_total > 0)?'':'pointer-events-none opacity-50'; ?>">
              Delete All
            </a>

            <a href="checkout.php"
               class="text-center bg-gradient-to-r from-orange-500 to-red-600 text-white py-4 rounded-xl font-semibold hover:shadow-xl hover:shadow-orange-500/25 transition <?= ($grand_total > 0)?'':'pointer-events-none opacity-50'; ?>">
              Proceed to Checkout
            </a>
          </div>
        </div>
      </div>
    </div>

    <?php else: ?>
      <div class="text-center py-16 bg-white rounded-3xl border border-gray-100 shadow-lg">
        <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
        <p class="text-2xl text-gray-500 font-medium">Your cart is empty</p>
        <a href="shop.php"
           class="mt-6 inline-flex items-center justify-center bg-gradient-to-r from-orange-500 to-red-600 text-white px-6 py-3 rounded-xl font-semibold hover:shadow-xl hover:shadow-orange-500/25 transition">
          <i class="fas fa-store mr-2"></i> Shop Now
        </a>
      </div>
    <?php endif; ?>
  </div>
</section>









<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>