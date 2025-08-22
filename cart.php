<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if(!$user_id){
   header('location:login.php');
   exit;
}

$message = [];

/* ---- delete single ---- */
if(isset($_GET['delete'])){
   $delete_id = (int)$_GET['delete'];
   $stmt = $conn->prepare("DELETE FROM `cart` WHERE id = ? AND user_id = ?");
   $stmt->execute([$delete_id, $user_id]);
   header('location:cart.php');
   exit;
}

/* ---- delete all ---- */
if(isset($_GET['delete_all'])){
   $stmt = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
   $stmt->execute([$user_id]);
   header('location:cart.php');
   exit;
}

/* ---- update qty ---- */
if(isset($_POST['update_qty'])){
   $cart_id = (int)($_POST['cart_id'] ?? 0);
   $p_qty   = (int)($_POST['p_qty'] ?? 1);
   if($p_qty < 1) $p_qty = 1;

   $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ? AND user_id = ?");
   $update_qty->execute([$p_qty, $cart_id, $user_id]);
   $message[] = 'Cart quantity updated';
}

/* ---- fetch items ---- */
$grand_total = 0.0;
$select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? ORDER BY id DESC");
$select_cart->execute([$user_id]);
$cart_items = $select_cart->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8" />
   <meta http-equiv="X-UA-Compatible" content="IE=edge" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <title>Shopping Cart — Kandu Pinnawala</title>

   <!-- Tailwind -->
   <script src="https://cdn.tailwindcss.com"></script>
   <script>
     tailwind.config = {
       theme: {
         extend: {
           colors: {
             primary:   '#7B5E42',  // Cocoa Brown
             secondary: '#A67B5B',  // Soft Brown
             accent:    '#C89F6D',  // Warm Tan
             dark:      '#3E2723',  // Deep Brown
             offwhite:  '#F5F3EE',
             offwhite2: '#EDE9E3',
           },
           fontFamily: { gaming:['Orbitron','monospace'], inter:['Inter','sans-serif'] }
         }
       }
     }
   </script>

   <!-- Fonts & Icons -->
   <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

   <style>
     /* ===== THEME (same as orders.php) ===== */
     *{box-sizing:border-box}
     body{
       font-family:'Inter',sans-serif; line-height:1.6; color:#3E2723;
       background:linear-gradient(135deg,#F9F9F6 0%,#F2EFEA 50%,#EAE5DD 100%);
       overflow-x:hidden;
     }
     /* Hero mist / soft spotlight */
     .hero-bg{
       background:
         radial-gradient(900px 340px at 50% 30%, rgba(234,226,214,.65) 0%, transparent 60%),
         radial-gradient(800px 320px at 15% 70%, rgba(245,240,232,.5) 0%, transparent 65%),
         radial-gradient(900px 340px at 85% 70%, rgba(255,255,255,.5) 0%, transparent 65%);
     }
     /* Cards */
     .glass-effect{
       background:rgba(255,255,255,.68);
       -webkit-backdrop-filter:blur(10px);
       backdrop-filter:blur(10px);
       border:1px solid rgba(140,120,100,.18);
       border-radius:20px;
     }
     .shadow-soft{ box-shadow:0 6px 18px rgba(120,100,80,.12), 0 12px 36px rgba(120,100,80,.10); }
     .allow-overflow{ overflow:visible!important; }

     /* Headings & accents */
     .heading{ font-family:'Orbitron',monospace; font-weight:800; letter-spacing:-0.5px; }
     .subhead{ color:#6a584d; }
     .gradient-text{
       background:linear-gradient(45deg,#C89F6D,#A67B5B,#7B5E42);
       -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;
     }

     /* Badges */
     .badge{
       display:inline-flex; align-items:center; gap:.5rem;
       white-space:nowrap; line-height:1.2; padding:.42rem .9rem; border-radius:9999px;
       border:1px solid rgba(120,100,80,.22);
       background:linear-gradient(135deg,#E9E4DC,#D7CFC4);
       font-family:'Orbitron',monospace; font-weight:700; font-size:.85rem;
       color:#3E2723; letter-spacing:.35px;
     }

     /* Inputs & buttons on light theme */
     .input{
       background:#fff; border:1px solid #ddd; border-radius:12px; padding:.75rem 1rem;
       outline:none; transition:box-shadow .2s, border-color .2s;
     }
     .input:focus{ border-color:#C89F6D; box-shadow:0 0 0 3px rgba(200,159,109,.25); }

     .btn{
       display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
       border-radius:12px; padding:.75rem 1rem; font-weight:600;
       transition:transform .15s ease, box-shadow .2s ease, background .2s ease;
     }
     .btn-ghost{ background:rgba(255,255,255,.7); border:1px solid rgba(140,120,100,.18); }
     .btn-ghost:hover{ transform:translateY(-2px); box-shadow:0 8px 16px rgba(120,100,80,.14); }
     .btn-primary{
       color:#fff; background:linear-gradient(135deg,#C89F6D,#7B5E42);
       box-shadow:0 8px 18px rgba(120,100,80,.18);
     }
     .btn-primary:hover{ transform:translateY(-2px); box-shadow:0 12px 22px rgba(120,100,80,.22); }

     /* Flash message */
     .message{
       position:fixed; top:20px; right:20px; z-index:1000;
       background:rgba(245,243,238,.96);
       -webkit-backdrop-filter:blur(10px); backdrop-filter:blur(10px);
       color:#3E2723; padding:12px 16px; border-radius:12px;
       border:1px solid rgba(140,120,100,.25); animation:slideIn .25s ease;
     }
     @keyframes slideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}
   </style>
</head>
<body>

<?php if(!empty($message)): foreach($message as $m): ?>
  <div class="message"><?= htmlspecialchars($m); ?></div>
<?php endforeach; endif; ?>

<?php include 'header.php'; ?>

<section class="relative min-h-screen flex items-start justify-center overflow-hidden hero-bg py-16">
  <div class="container mx-auto px-6 lg:px-12 grid lg:grid-cols-3 gap-8 items-start relative z-10 w-full">

    <!-- Title -->
    <div class="lg:col-span-3 text-center mb-6">
      <h2 class="heading text-4xl lg:text-5xl">
        <span class="gradient-text">YOUR</span> <span class="heading">CART</span>
      </h2>
      <p class="mt-3 text-lg subhead">Review items, update quantities, or proceed to checkout.</p>

      <!-- Mini feature chips (optional; match orders hero chips) -->
      <div class="mt-4 flex items-center justify-center gap-3 flex-wrap">
        <span class="badge"><i class="fa-solid fa-shield-halved"></i> Secure Checkout</span>
        <span class="badge"><i class="fa-solid fa-truck-fast"></i> Fast Shipping</span>
        <span class="badge"><i class="fa-solid fa-headset"></i> 24/7 Support</span>
      </div>
    </div>

    <?php if(count($cart_items) > 0): ?>
      <!-- Items -->
      <div class="lg:col-span-2 space-y-6">
        <?php foreach($cart_items as $row):
              $name  = htmlspecialchars($row['name']);
              $img   = htmlspecialchars($row['image']);
              $pid   = (int)$row['pid'];
              $id    = (int)$row['id'];
              $price = (float)$row['price'];
              $qty   = (int)$row['quantity'];
              $sub   = $price * $qty;
              $grand_total += $sub;
        ?>
        <form action="" method="POST" class="glass-effect shadow-soft allow-overflow">
          <!-- Top bar with price badge + actions -->
          <div class="px-4 py-3 flex items-center justify-between">
            <span class="badge"><i class="fa-solid fa-tag"></i> Rs <?= number_format($price,2); ?>/-</span>
            <div class="flex items-center gap-2">
              <a href="view_page.php?pid=<?= $pid; ?>" class="btn btn-ghost w-10 h-10 rounded-full" title="View item">
                <i class="fa-solid fa-eye"></i>
              </a>
              <a href="cart.php?delete=<?= $id; ?>" onclick="return confirm('Delete this from cart?');" class="btn btn-ghost w-10 h-10 rounded-full" title="Remove">
                <i class="fa-solid fa-xmark"></i>
              </a>
            </div>
          </div>

          <!-- Body -->
          <div class="p-6 pt-3">
            <div class="flex flex-col sm:flex-row gap-6">
              <div class="w-full sm:w-40 h-40 rounded-2xl overflow-hidden flex-shrink-0 border border-neutral-200 bg-white">
                <img src="uploaded_img/<?= $img; ?>" alt="<?= $name; ?>" class="w-full h-full object-cover" onerror="this.src='uploaded_img/placeholder.png'">
              </div>

              <div class="flex-1">
                <h3 class="text-xl font-semibold mb-2"><?= $name; ?></h3>
                <input type="hidden" name="cart_id" value="<?= $id; ?>">

                <div class="grid sm:grid-cols-2 gap-4 items-end">
                  <div>
                    <label class="block text-sm font-medium mb-2">Quantity</label>
                    <div class="flex items-center gap-2">
                      <input type="number" min="1" name="p_qty" value="<?= $qty; ?>" class="input w-28">
                      <button type="submit" name="update_qty" class="btn btn-ghost">Update</button>
                    </div>
                  </div>

                  <div class="sm:text-right">
                    <p class="text-sm text-neutral-600 mb-1">Sub Total</p>
                    <p class="heading text-2xl">Rs <?= number_format($sub,2); ?>/-</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
        <?php endforeach; ?>
      </div>

      <!-- Summary -->
      <aside class="glass-effect shadow-soft allow-overflow h-max">
        <div class="px-4 py-3 flex items-center justify-between">
          <span class="badge"><i class="fa-solid fa-receipt"></i> Order Summary</span>
        </div>
        <div class="p-6">
          <div class="rounded-2xl bg-white p-4 space-y-2 border border-neutral-200">
            <div class="flex items-center justify-between text-neutral-700">
              <span>Items</span><span><?= count($cart_items); ?></span>
            </div>
            <div class="flex items-center justify-between text-neutral-700">
              <span>Subtotal</span><span>Rs <?= number_format($grand_total,2); ?>/-</span>
            </div>
            <div class="flex items-center justify-between text-neutral-700">
              <span>Shipping</span><span>FREE</span>
            </div>
            <div class="pt-3 border-top border-neutral-200 flex items-center justify-between" style="border-top:1px solid #eee;">
              <span class="heading text-lg">Grand Total</span>
              <span class="heading text-lg">Rs <?= number_format($grand_total,2); ?>/-</span>
            </div>
          </div>

          <div class="mt-6 grid gap-3">
            <a href="shop.php" class="btn btn-ghost text-center">Continue Shopping</a>
            <a href="cart.php?delete_all" class="btn btn-ghost text-center text-red-700 border border-red-300 hover:bg-red-50 <?= ($grand_total > 0)?'':'pointer-events-none opacity-50'; ?>">Delete All</a>
            <a href="checkout.php" class="btn btn-primary text-center py-4 <?= ($grand_total > 0)?'':'pointer-events-none opacity-50'; ?>">Proceed to Checkout</a>
          </div>
        </div>
      </aside>
    <?php else: ?>
      <!-- Empty state -->
      <div class="lg:col-span-3 text-center py-16 glass-effect shadow-soft">
        <i class="fa-solid fa-box-open text-6xl text-neutral-400 mb-4"></i>
        <p class="heading text-2xl">Your cart is empty</p>
        <a href="shop.php" class="btn btn-primary mt-6 px-6 py-3">
          <i class="fa-solid fa-store mr-2"></i> Shop Now
        </a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include 'footer.php'; ?>

<script>
  // Auto-hide flash messages
  document.querySelectorAll('.message').forEach(el=>{
    setTimeout(()=>{
      el.style.transform='translateX(100%)';
      el.style.opacity='0';
      setTimeout(()=>el.remove(),250);
    }, 3000);
  });
</script>
<script src="js/script.js"></script>
</body>
</html>
