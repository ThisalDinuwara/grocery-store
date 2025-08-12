<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>orders</title>
<!-- Tailwind CDN (optional for live preview) -->
   <script src="https://cdn.tailwindcss.com"></script>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<!-- Placed Orders (matches product grid UI) -->
<section id="orders" class="py-20 bg-gray-50">
  <div class="container mx-auto px-6 lg:px-12">
    <!-- Header like products section -->
    <div class="text-center mb-16">
      <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Placed Orders</h2>
      <p class="text-xl text-gray-600 max-w-3xl mx-auto">Track your recent purchases and payment status</p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
      <?php
        $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? ORDER BY id DESC");
        $select_orders->execute([$user_id]);

        if($select_orders->rowCount() > 0){
          while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){

            // Build a status pill style
            $status = strtolower($fetch_orders['payment_status']);
            if(in_array($status, ['paid','completed','success'])) {
              $statusClass = 'bg-green-100 text-green-700 border-green-200';
              $dotClass    = 'bg-green-500';
            } elseif(in_array($status, ['pending','processing'])) {
              $statusClass = 'bg-yellow-100 text-yellow-700 border-yellow-200';
              $dotClass    = 'bg-yellow-500';
            } elseif(in_array($status, ['cancelled','canceled','failed'])) {
              $statusClass = 'bg-red-100 text-red-700 border-red-200';
              $dotClass    = 'bg-red-500';
            } else {
              $statusClass = 'bg-gray-100 text-gray-700 border-gray-200';
              $dotClass    = 'bg-gray-400';
            }
      ?>
      <div class="group">
        <div class="card-hover bg-white rounded-3xl overflow-hidden shadow-lg border border-gray-100 relative">
          <!-- Total (badge) -->
          <div class="absolute top-4 left-4 bg-gradient-to-r from-orange-500 to-red-600 text-white px-4 py-2 rounded-full font-bold text-sm z-10">
            Total: Rs <?= htmlspecialchars($fetch_orders['total_price']); ?>/-
          </div>

          <!-- Payment status pill -->
          <div class="absolute top-4 right-4 z-10">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold border <?= $statusClass; ?>">
              <span class="w-2 h-2 rounded-full <?= $dotClass; ?>"></span>
              <?= htmlspecialchars($fetch_orders['payment_status']); ?>
            </span>
          </div>

          <!-- Order body -->
          <div class="p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-1">
              <?php if(isset($fetch_orders['id'])): ?>
                Order #<?= (int)$fetch_orders['id']; ?>
              <?php else: ?>
                Order Details
              <?php endif; ?>
            </h3>
            <p class="text-sm text-gray-500 mb-4">Placed on <?= htmlspecialchars($fetch_orders['placed_on']); ?></p>

            <div class="space-y-3 text-gray-700">
              <div class="flex items-start gap-3">
                <i class="fas fa-user text-orange-500 mt-1"></i>
                <div>
                  <p class="text-xs uppercase tracking-wide text-gray-500">Name</p>
                  <p class="font-medium"><?= htmlspecialchars($fetch_orders['name']); ?></p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <i class="fas fa-envelope text-orange-500 mt-1"></i>
                <div>
                  <p class="text-xs uppercase tracking-wide text-gray-500">Email</p>
                  <p class="font-medium"><?= htmlspecialchars($fetch_orders['email']); ?></p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <i class="fas fa-phone text-orange-500 mt-1"></i>
                <div>
                  <p class="text-xs uppercase tracking-wide text-gray-500">Number</p>
                  <p class="font-medium"><?= htmlspecialchars($fetch_orders['number']); ?></p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <i class="fas fa-location-dot text-orange-500 mt-1"></i>
                <div>
                  <p class="text-xs uppercase tracking-wide text-gray-500">Address</p>
                  <p class="font-medium"><?= nl2br(htmlspecialchars($fetch_orders['address'])); ?></p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <i class="fas fa-credit-card text-orange-500 mt-1"></i>
                <div>
                  <p class="text-xs uppercase tracking-wide text-gray-500">Payment Method</p>
                  <p class="font-medium"><?= htmlspecialchars($fetch_orders['method']); ?></p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <i class="fas fa-boxes-stacked text-orange-500 mt-1"></i>
                <div class="flex-1">
                  <p class="text-xs uppercase tracking-wide text-gray-500">Items</p>
                  <div class="mt-1 p-3 rounded-xl bg-gray-50 text-sm leading-relaxed max-h-28 overflow-auto">
                    <?= htmlspecialchars($fetch_orders['total_products']); ?>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action -->
            <a href="shop.php" class="mt-6 inline-flex items-center justify-center w-full bg-gradient-to-r from-orange-500 to-red-600 text-white py-3 rounded-xl font-semibold hover:shadow-xl hover:shadow-orange-500/25 transition duration-300">
              <i class="fas fa-store mr-2"></i> Shop More
            </a>
          </div>
        </div>
      </div>
      <?php
          } // while
        } else {
          echo '
            <div class="col-span-full text-center py-16">
              <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
              <p class="text-2xl text-gray-500 font-medium">No orders placed yet!</p>
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