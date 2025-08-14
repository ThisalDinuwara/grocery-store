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

   <!-- Tailwind CDN -->
   <script src="https://cdn.tailwindcss.com"></script>
   <script>
      tailwind.config = {
         theme: {
            extend: {
               colors: {
                  primary: '#8B4513',   // Saddle Brown
                  secondary: '#A0522D', // Sienna
                  accent: '#D2B48C',    // Tan
                  dark: '#3E2723',      // Dark Brown
                  darker: '#1B0F0A'     // Deep Brown
               },
               fontFamily: {
                  gaming: ['Orbitron', 'monospace']
               }
            }
         }
      }
   </script>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Your site CSS (kept) -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      *{margin:0;padding:0;box-sizing:border-box}
      body{
         font-family:'Inter',sans-serif;
         background:linear-gradient(135deg,#1B0F0A 0%,#3E2723 50%,#5D4037 100%);
         color:#fff; overflow-x:hidden;
      }

      /* Shared effects (match home.php) */
      .neon-glow{box-shadow:0 0 20px rgba(139,69,19,.5),0 0 40px rgba(160,82,45,.3),0 0 60px rgba(210,180,140,.2)}
      .glass-effect{background:rgba(255,255,255,.08);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.18)}
      .hover-glow:hover{transform:translateY(-5px);box-shadow:0 10px 25px rgba(139,69,19,.35);transition:.3s ease}
      .gradient-text{background:linear-gradient(45deg,#8B4513,#A0522D,#D2B48C);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
      .hero-bg{
         background:
           radial-gradient(circle at 20% 80%, rgba(139,69,19,.35) 0%, transparent 55%),
           radial-gradient(circle at 80% 20%, rgba(210,180,140,.35) 0%, transparent 55%),
           radial-gradient(circle at 40% 40%, rgba(160,82,45,.35) 0%, transparent 55%);
      }

      /* Card design for orders */
      .order-card{
         background:linear-gradient(180deg,rgba(62,39,35,.92),rgba(62,39,35,.84));
         border:1px solid rgba(210,180,140,.28);
         border-radius:22px;
         overflow:hidden;
         transition:transform .35s ease, box-shadow .35s ease, border-color .35s ease;
      }
      .order-card:hover{
         transform:translateY(-8px);
         border-color:rgba(210,180,140,.55);
         box-shadow:0 22px 48px rgba(160,82,45,.35);
      }

      .badge-price{
         font-size:.95rem;
         padding:.5rem .9rem;
         border:1px solid rgba(255,255,255,.18);
      }

      .pill{
         border:1px solid rgba(255,255,255,.18);
         background:rgba(255,255,255,.08);
         color:#FCEBD0;
      }

      .muted{color:#d8c9b8}
      .muted-2{color:#cbbba9}
      .chip{display:inline-flex;align-items:center;gap:.5rem;border-radius:9999px;padding:.4rem .75rem}
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<!-- Hero (matches the home aesthetic) -->
<section class="relative min-h-[40vh] md:min-h-[50vh] flex items-center justify-center overflow-hidden hero-bg">
  <div class="absolute top-10 left-10 w-72 h-72 bg-gradient-to-r from-[rgba(139,69,19,0.22)] to-[rgba(210,180,140,0.22)] rounded-full blur-3xl"></div>
  <div class="absolute bottom-10 right-10 w-64 h-64 bg-gradient-to-r from-[rgba(160,82,45,0.22)] to-[rgba(139,69,19,0.22)] rounded-full blur-3xl"></div>

  <div class="container mx-auto px-6 lg:px-12 relative z-10 text-center">
     <h1 class="text-5xl lg:text-7xl font-bold leading-tight mb-4">
       <span class="gradient-text font-gaming">YOUR</span> <span class="text-white">ORDERS</span>
     </h1>
     <div class="h-1 w-28 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] rounded-full mx-auto mb-6"></div>
     <p class="text-lg md:text-xl text-gray-200 max-w-3xl mx-auto">
       Track purchases, check payment status, and review delivery details—all in one place.
     </p>
     <div class="mt-6 flex items-center justify-center gap-3">
       <span class="chip glass-effect"><i class="fa-solid fa-shield-check"></i> Secure Orders</span>
       <span class="chip glass-effect"><i class="fa-solid fa-truck-fast"></i> Fast Shipping</span>
       <span class="chip glass-effect"><i class="fa-solid fa-headset"></i> 24/7 Support</span>
     </div>
  </div>
</section>

<!-- Orders Grid (PHP unchanged, just themed UI) -->
<section id="orders" class="py-16">
  <div class="container mx-auto px-6 lg:px-12">

    <div class="text-center mb-12">
      <h2 class="text-4xl lg:text-5xl font-bold mb-4">
        <span class="gradient-text font-gaming">PLACED ORDERS</span>
      </h2>
      <div class="h-1 w-24 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] rounded-full mx-auto"></div>
      <p class="text-lg text-gray-200 mt-6 max-w-3xl mx-auto">
        Review details of your recent purchases and payment confirmation.
      </p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
      <?php
        $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? ORDER BY id DESC");
        $select_orders->execute([$user_id]);

        if($select_orders->rowCount() > 0){
          while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){

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
        <div class="order-card relative">
          <!-- Top bar with price + status -->
          <div class="px-4 py-3 flex items-center justify-between">
            <div class="inline-flex items-center gap-2 badge-price rounded-full bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white">
              <i class="fas fa-receipt"></i>
              <span class="font-semibold">Total: Rs <?= htmlspecialchars($fetch_orders['total_price']); ?>/-</span>
            </div>
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold border <?= $statusClass; ?>">
              <span class="w-2 h-2 rounded-full <?= $dotClass; ?>"></span>
              <?= htmlspecialchars($fetch_orders['payment_status']); ?>
            </span>
          </div>

          <!-- Body -->
          <div class="p-6">
            <h3 class="text-xl font-bold text-white mb-1">
              <?php if(isset($fetch_orders['id'])): ?>
                Order #<?= (int)$fetch_orders['id']; ?>
              <?php else: ?>
                Order Details
              <?php endif; ?>
            </h3>
            <p class="text-sm muted mb-4">Placed on <?= htmlspecialchars($fetch_orders['placed_on']); ?></p>

            <div class="space-y-3">
              <div class="flex items-start gap-3">
                <i class="fas fa-user text-accent mt-1"></i>
                <div>
                  <p class="text-[11px] uppercase tracking-wide muted-2">Name</p>
                  <p class="font-medium text-white"><?= htmlspecialchars($fetch_orders['name']); ?></p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <i class="fas fa-envelope text-accent mt-1"></i>
                <div>
                  <p class="text-[11px] uppercase tracking-wide muted-2">Email</p>
                  <p class="font-medium text-white"><?= htmlspecialchars($fetch_orders['email']); ?></p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <i class="fas fa-phone text-accent mt-1"></i>
                <div>
                  <p class="text-[11px] uppercase tracking-wide muted-2">Number</p>
                  <p class="font-medium text-white"><?= htmlspecialchars($fetch_orders['number']); ?></p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <i class="fas fa-location-dot text-accent mt-1"></i>
                <div>
                  <p class="text-[11px] uppercase tracking-wide muted-2">Address</p>
                  <p class="font-medium text-white"><?= nl2br(htmlspecialchars($fetch_orders['address'])); ?></p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <i class="fas fa-credit-card text-accent mt-1"></i>
                <div>
                  <p class="text-[11px] uppercase tracking-wide muted-2">Payment Method</p>
                  <p class="font-medium text-white"><?= htmlspecialchars($fetch_orders['method']); ?></p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <i class="fas fa-boxes-stacked text-accent mt-1"></i>
                <div class="flex-1">
                  <p class="text-[11px] uppercase tracking-wide muted-2">Items</p>
                  <div class="mt-1 p-3 rounded-xl glass-effect text-sm leading-relaxed max-h-32 overflow-auto">
                    <?= htmlspecialchars($fetch_orders['total_products']); ?>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action -->
            <a href="shop.php"
               class="mt-6 inline-flex items-center justify-center w-full bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white py-3 rounded-xl font-semibold hover-glow neon-glow transition duration-300">
              <i class="fas fa-store mr-2"></i> Shop More
            </a>
          </div>
        </div>
      </div>
      <?php
          }
        } else {
          echo '
            <div class="col-span-full text-center">
              <div class="glass-effect p-12 rounded-3xl max-w-md mx-auto">
                <i class="fas fa-box-open text-6xl" style="color:#CD853F"></i>
                <p class="text-2xl text-gray-200 font-medium mt-4">No orders placed yet!</p>
                <a href="shop.php"
                   class="mt-6 inline-flex items-center justify-center bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white px-6 py-3 rounded-xl font-semibold hover-glow neon-glow transition">
                  <i class="fas fa-store mr-2"></i> Start Shopping
                </a>
              </div>
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
