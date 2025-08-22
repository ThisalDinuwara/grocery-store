<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
   exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Orders - Kandu Pinnawala</title>

   <!-- Tailwind CDN -->
   <script src="https://cdn.tailwindcss.com"></script>
   <script>
      tailwind.config = {
         theme: {
            extend: {
               colors: {
                  primary:   '#B77B3D',  // warm brown
                  secondary: '#D4A373',  // golden beige
                  accent:    '#8C6239',  // deep brown
                  ink:       '#2E1B0E',  // main text
                  soft:      '#5C3A24',  // subtle text
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

      /* ===== Light Theme Base ===== */
      body{
         font-family:'Inter',sans-serif;
         background:linear-gradient(135deg,#FFFDF9 0%, #F7F3ED 50%, #EFE8DE 100%);
         color:#2E1B0E; /* ink */
         overflow-x:hidden;
      }

      /* Subtle utilities */
      .glass-effect{background:rgba(255,255,255,.85);backdrop-filter:blur(10px);border:1px solid rgba(183,123,61,.22)}
      .hover-elevate:hover{transform:translateY(-4px);box-shadow:0 12px 24px rgba(183,123,61,.18);transition:.3s ease}
      .gradient-text{background:linear-gradient(45deg,#B77B3D,#D4A373);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

      .hero-bg{
         background:
           radial-gradient(circle at 20% 80%, rgba(183,123,61,.18) 0%, transparent 55%),
           radial-gradient(circle at 80% 20%, rgba(212,163,115,.18) 0%, transparent 55%),
           radial-gradient(circle at 40% 40%, rgba(140,98,57,.18) 0%, transparent 55%);
      }

      /* ===== Order Card (light) ===== */
      .order-card{
         background:linear-gradient(180deg,rgba(255,255,255,.96),rgba(250,245,235,.92));
         border:1px solid rgba(183,123,61,.26);
         border-radius:22px;
         overflow:hidden;
         transition:transform .3s ease, box-shadow .3s ease, border-color .3s ease;
      }
      .order-card:hover{
         transform:translateY(-6px);
         border-color:rgba(183,123,61,.5);
         box-shadow:0 22px 48px rgba(183,123,61,.2);
      }

      .badge-price{
         font-size:.95rem;
         padding:.5rem .9rem;
         border:1px solid rgba(183,123,61,.25);
         border-radius:9999px;
         background:linear-gradient(135deg,#B77B3D,#D4A373);
         color:#fff;
      }

      .chip{
         display:inline-flex;align-items:center;gap:.5rem;border-radius:9999px;padding:.5rem .85rem;
         border:1px solid rgba(183,123,61,.22);
         background:#fff;color:#6B4E2E;
      }

      .muted{color:#6B4E2E}
      .muted-2{color:#8A6A49}

      /* Buttons */
      .btn-primary{
         background:linear-gradient(135deg,#B77B3D,#D4A373);
         color:#fff;font-weight:700;border:none;
         border-radius:14px;padding:.9rem 1rem;
         transition:.25s; box-shadow:0 12px 28px rgba(183,123,61,.2);
      }
      .btn-primary:hover{transform:translateY(-2px)}

      /* Status pills (use Tailwind utility colors for readability on light bg) */
      .pill{
         display:inline-flex;align-items:center;gap:.5rem;
         padding:.4rem .7rem;border-radius:9999px;font-weight:700;font-size:.8rem;
         border-width:1px;border-style:solid;
      }
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<!-- Hero (light aesthetic) -->
<section class="relative min-h-[40vh] md:min-h-[50vh] flex items-center justify-center overflow-hidden hero-bg">
  <div class="absolute top-10 left-10 w-72 h-72 bg-gradient-to-r from-[rgba(183,123,61,0.18)] to-[rgba(212,163,115,0.18)] rounded-full blur-3xl"></div>
  <div class="absolute bottom-10 right-10 w-64 h-64 bg-gradient-to-r from-[rgba(212,163,115,0.18)] to-[rgba(140,98,57,0.18)] rounded-full blur-3xl"></div>

  <div class="container mx-auto px-6 lg:px-12 relative z-10 text-center">
     <h1 class="text-5xl lg:text-7xl font-bold leading-tight mb-4">
       <span class="gradient-text font-gaming">YOUR</span> <span class="">ORDERS</span>
     </h1>
     <div class="h-1 w-28 bg-gradient-to-r from-[#B77B3D] to-[#D4A373] rounded-full mx-auto mb-6"></div>
     <p class="text-lg md:text-xl muted max-w-3xl mx-auto">
       Track purchases, check payment status, and review delivery details—all in one place.
     </p>
     <div class="mt-6 flex items-center justify-center gap-3">
       <span class="chip"><i class="fa-solid fa-shield-check"></i> Secure Orders</span>
       <span class="chip"><i class="fa-solid fa-truck-fast"></i> Fast Shipping</span>
       <span class="chip"><i class="fa-solid fa-headset"></i> 24/7 Support</span>
     </div>
  </div>
</section>

<!-- Orders Grid -->
<section id="orders" class="py-16">
  <div class="container mx-auto px-6 lg:px-12">

    <div class="text-center mb-12">
      <h2 class="text-4xl lg:text-5xl font-bold mb-4">
        <span class="gradient-text font-gaming">PLACED ORDERS</span>
      </h2>
      <div class="h-1 w-24 bg-gradient-to-r from-[#B77B3D] to-[#D4A373] rounded-full mx-auto"></div>
      <p class="text-lg muted mt-6 max-w-3xl mx-auto">
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
              $statusClass = 'bg-green-100 text-green-800 border-green-200';
              $dotClass    = 'bg-green-500';
            } elseif(in_array($status, ['pending','processing'])) {
              $statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-200';
              $dotClass    = 'bg-yellow-500';
            } elseif(in_array($status, ['cancelled','canceled','failed'])) {
              $statusClass = 'bg-red-100 text-red-800 border-red-200';
              $dotClass    = 'bg-red-500';
            } else {
              $statusClass = 'bg-gray-100 text-gray-800 border-gray-200';
              $dotClass    = 'bg-gray-400';
            }
      ?>
      <div class="group">
        <div class="order-card relative">
          <!-- Top bar with price + status -->
          <div class="px-4 py-3 flex items-center justify-between">
            <div class="inline-flex items-center gap-2 badge-price">
              <i class="fas fa-receipt"></i>
              <span class="font-semibold">Total: Rs <?= htmlspecialchars($fetch_orders['total_price']); ?>/-</span>
            </div>
            <span class="pill <?= $statusClass; ?>">
              <span class="w-2 h-2 rounded-full <?= $dotClass; ?>"></span>
              <?= htmlspecialchars($fetch_orders['payment_status']); ?>
            </span>
          </div>

          <!-- Body -->
          <div class="p-6">
            <h3 class="text-xl font-extrabold mb-1 text-[color:var(--tw-colors-ink,#2E1B0E)]">
              <?php if(isset($fetch_orders['id'])): ?>
                Order #<?= (int)$fetch_orders['id']; ?>
              <?php else: ?>
                Order Details
              <?php endif; ?>
            </h3>
            <p class="text-sm muted-2 mb-4">Placed on <?= htmlspecialchars($fetch_orders['placed_on']); ?></p>

            <div class="space-y-3">
              <div class="flex items-start gap-3">
                <i class="fas fa-user text-[color:#8C6239] mt-1"></i>
                <div>
                  <p class="text-[11px] uppercase tracking-wide muted-2">Name</p>
                  <p class="font-semibold"><?= htmlspecialchars($fetch_orders['name']); ?></p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <i class="fas fa-envelope text-[color:#8C6239] mt-1"></i>
                <div>
                  <p class="text-[11px] uppercase tracking-wide muted-2">Email</p>
                  <p class="font-medium"><?= htmlspecialchars($fetch_orders['email']); ?></p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <i class="fas fa-phone text-[color:#8C6239] mt-1"></i>
                <div>
                  <p class="text-[11px] uppercase tracking-wide muted-2">Number</p>
                  <p class="font-medium"><?= htmlspecialchars($fetch_orders['number']); ?></p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <i class="fas fa-location-dot text-[color:#8C6239] mt-1"></i>
                <div>
                  <p class="text-[11px] uppercase tracking-wide muted-2">Address</p>
                  <p class="font-medium"><?= nl2br(htmlspecialchars($fetch_orders['address'])); ?></p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <i class="fas fa-credit-card text-[color:#8C6239] mt-1"></i>
                <div>
                  <p class="text-[11px] uppercase tracking-wide muted-2">Payment Method</p>
                  <p class="font-medium"><?= htmlspecialchars($fetch_orders['method']); ?></p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <i class="fas fa-boxes-stacked text-[color:#8C6239] mt-1"></i>
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
               class="mt-6 inline-flex items-center justify-center w-full btn-primary hover-elevate">
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
                <i class="fas fa-box-open text-6xl" style="color:#8C6239"></i>
                <p class="text-2xl muted font-medium mt-4">No orders placed yet!</p>
                <a href="shop.php"
                   class="mt-6 inline-flex items-center justify-center btn-primary hover-elevate px-6 py-3">
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
