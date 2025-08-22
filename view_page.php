<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if(!$user_id){
  header('location:login.php');
  exit;
}

/* ---------- Wishlist ---------- */
if(isset($_POST['add_to_wishlist'])){
  $pid     = filter_var($_POST['pid']     ?? '', FILTER_SANITIZE_NUMBER_INT);
  $p_name  = filter_var($_POST['p_name']  ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
  $p_price = filter_var($_POST['p_price'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
  $p_image = filter_var($_POST['p_image'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

  $check_wishlist_numbers = $conn->prepare("SELECT 1 FROM `wishlist` WHERE name = ? AND user_id = ?");
  $check_wishlist_numbers->execute([$p_name, $user_id]);

  $check_cart_numbers = $conn->prepare("SELECT 1 FROM `cart` WHERE name = ? AND user_id = ?");
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

/* ---------- Cart ---------- */
if(isset($_POST['add_to_cart'])){
  $pid     = filter_var($_POST['pid']     ?? '', FILTER_SANITIZE_NUMBER_INT);
  $p_name  = filter_var($_POST['p_name']  ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
  $p_price = filter_var($_POST['p_price'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
  $p_image = filter_var($_POST['p_image'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
  $p_qty   = max(1, (int)filter_var($_POST['p_qty'] ?? 1, FILTER_SANITIZE_NUMBER_INT));

  $check_cart_numbers = $conn->prepare("SELECT 1 FROM `cart` WHERE name = ? AND user_id = ?");
  $check_cart_numbers->execute([$p_name, $user_id]);

  if($check_cart_numbers->rowCount() > 0){
    $message[] = 'already added to cart!';
  }else{
    $check_wishlist_numbers = $conn->prepare("SELECT 1 FROM `wishlist` WHERE name = ? AND user_id = ?");
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
  <title>Quick View — Kandu Pinnawala</title>

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            sand:   '#F7F4EF',
            sand2:  '#EFE9DF',
            sand3:  '#E6DFD3',
            cocoa:  '#3E2723',
            tan:    '#C89F6D',   // accents
            bronze: '#A77A47',
          },
          fontFamily: {
            inter: ['Inter','sans-serif'],
            gaming:['Orbitron','monospace']
          },
          boxShadow: {
            soft: '0 10px 30px rgba(105, 80, 50, .12)',
            ring: '0 0 0 6px rgba(200,159,109,.22)'
          }
        }
      }
    }
  </script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root{
      --bg1:#F7F4EF; --bg2:#EFE9DF; --bg3:#E6DFD3;
      --ink:#2F241F; --ink-soft:#5f5149;
      --tan:#C89F6D; --bronze:#A77A47;
    }
    *{box-sizing:border-box}
    body{
      font-family:'Inter',sans-serif;
      color:var(--ink);
      background:
        radial-gradient(800px 300px at 15% 0%, rgba(200,159,109,.18), transparent 60%),
        radial-gradient(800px 300px at 85% 100%, rgba(167,122,71,.14), transparent 60%),
        linear-gradient(180deg,var(--bg1),var(--bg2) 45%, var(--bg3) 100%);
      min-height:100vh;
    }

    /* hero bubbles */
    .bubble{filter:blur(24px); opacity:.6; border-radius:9999px}
    .floating{animation:floating 6s ease-in-out infinite}
    @keyframes floating{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}

    /* glass card */
    .card{
      background:rgba(255,255,255,.75);
      backdrop-filter: blur(10px);
      border:1px solid rgba(120,100,80,.18);
      box-shadow:0 10px 30px rgba(105, 80, 50, .12);
      border-radius:22px;
    }

    .badge{
      background:linear-gradient(135deg,var(--tan),var(--bronze));
      color:#fff; border:1px solid rgba(255,255,255,.6);
      box-shadow:0 8px 22px rgba(167,122,71,.25);
    }

    .btn-primary{
      background:linear-gradient(135deg,var(--tan),var(--bronze));
      color:#fff; font-weight:800;
      box-shadow:0 12px 26px rgba(167,122,71,.25);
      transition:transform .18s ease, box-shadow .18s ease, filter .18s ease;
    }
    .btn-primary:hover{ transform:translateY(-1px); filter:brightness(1.03); }
    .btn-secondary{
      background:#fff; color:var(--ink);
      border:1px solid rgba(120,100,80,.25); font-weight:700;
    }
    .btn-secondary:hover{ box-shadow:0 10px 22px rgba(105,80,50,.12); }

    .qty{ border:1px solid rgba(120,100,80,.25); background:#fff; }
    .qty:focus{ outline:none; box-shadow:0 0 0 5px rgba(200,159,109,.22); }

    .message{
      position:fixed; top:20px; right:20px;
      background:linear-gradient(135deg,var(--tan),var(--bronze));
      color:#fff; padding:12px 16px; border-radius:14px;
      border:1px solid rgba(255,255,255,.6); z-index:1000;
      box-shadow:0 16px 40px rgba(167,122,71,.28); font-weight:700;
      animation:msg .28s ease-out;
    }
    @keyframes msg{ from{ transform:translateX(40px); opacity:0 } to{ transform:translateX(0); opacity:1 } }
  </style>
</head>
<body>

<?php include 'header.php'; ?>

<!-- Hero -->
<section class="relative overflow-hidden">
  <div class="absolute -top-10 -left-10 w-80 h-80 bg-[rgba(200,159,109,.22)] bubble floating"></div>
  <div class="absolute -bottom-16 -right-8 w-96 h-96 bg-[rgba(167,122,71,.18)] bubble floating" style="animation-delay:.8s"></div>

  <div class="container mx-auto px-6 lg:px-12 py-16 md:py-20 text-center relative">
    <h1 class="text-5xl md:text-6xl lg:text-7xl font-extrabold tracking-tight">
      <span class="font-gaming bg-gradient-to-r from-tan to-bronze bg-clip-text text-transparent">QUICK</span>
      <span class="text-cocoa">VIEW</span>
    </h1>
    <div class="h-[6px] w-32 rounded-full mx-auto mt-6 mb-4 bg-gradient-to-r from-tan to-bronze"></div>
    <p class="text-lg md:text-xl text-[color:var(--ink-soft)] max-w-3xl mx-auto">
      Explore traditional Sri Lankan handicrafts with detailed views and instant shopping options.
    </p>
  </div>
</section>

<section class="py-10 md:py-16">
  <div class="container mx-auto px-6 lg:px-12">
    <?php
      $pid = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
      $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
      $select_products->execute([$pid]);

      if($select_products->rowCount() > 0){
        $p = $select_products->fetch(PDO::FETCH_ASSOC);
    ?>
    <form action="" method="POST" class="max-w-5xl mx-auto">
      <div class="card overflow-hidden">
        <!-- Header -->
        <div class="px-5 md:px-7 py-4 flex items-center justify-between">
          <div class="badge inline-flex items-center gap-2 px-4 py-2 rounded-full font-bold text-sm md:text-base">
            <i class="fa-solid fa-tag"></i>
            Rs <?= number_format((float)$p['price'], 2) ?>/-
          </div>
          <a href="shop.php" class="w-10 h-10 rounded-full grid place-items-center border border-[rgba(120,100,80,.25)] bg-white hover:shadow-soft transition" aria-label="Back to shop">
            <i class="fa-solid fa-xmark text-[color:var(--ink)] text-lg"></i>
          </a>
        </div>

        <!-- Image -->
        <div class="px-5 md:px-7">
          <div class="rounded-2xl overflow-hidden border border-[rgba(120,100,80,.25)]">
            <img src="uploaded_img/<?= htmlspecialchars($p['image']) ?>"
                 alt="<?= htmlspecialchars($p['name']) ?>"
                 class="w-full h-auto block transition-transform duration-500 hover:scale-[1.02]"
                 onerror="this.src='uploaded_img/placeholder.png'">
          </div>
        </div>

        <!-- Content -->
        <div class="p-6 md:p-8">
          <h2 class="text-2xl md:text-3xl font-extrabold mb-3"><?= htmlspecialchars($p['name']) ?></h2>
          <?php if(!empty($p['details'])): ?>
            <p class="text-[color:var(--ink-soft)] leading-relaxed mb-6">
              <?= nl2br(htmlspecialchars($p['details'])) ?>
            </p>
          <?php endif; ?>

          <!-- Hidden inputs -->
          <input type="hidden" name="pid" value="<?= (int)$p['id'] ?>">
          <input type="hidden" name="p_name" value="<?= htmlspecialchars($p['name']) ?>">
          <input type="hidden" name="p_price" value="<?= htmlspecialchars($p['price']) ?>">
          <input type="hidden" name="p_image" value="<?= htmlspecialchars($p['image']) ?>">

          <div class="grid md:grid-cols-[180px_1fr] gap-4 md:gap-6 items-end">
            <div>
              <label class="block text-sm font-semibold mb-2 text-[color:var(--ink-soft)]">Quantity</label>
              <input type="number" min="1" value="1" name="p_qty" class="qty w-40 px-4 py-3 rounded-xl text-center font-semibold">
            </div>
            <div class="grid sm:grid-cols-2 gap-3">
              <button type="submit" name="add_to_cart" class="btn-primary rounded-xl py-3.5 text-base">
                <i class="fa-solid fa-cart-shopping mr-2"></i> Add to Cart
              </button>
              <button type="submit" name="add_to_wishlist" class="btn-secondary rounded-xl py-3.5 text-base">
                <i class="fa-solid fa-heart mr-2"></i> Add to Wishlist
              </button>
            </div>
          </div>
        </div>
      </div>
    </form>
    <?php } else { ?>
      <div class="max-w-lg mx-auto text-center card p-10">
        <i class="fa-solid fa-box-open text-6xl mb-4 text-[color:var(--bronze)]"></i>
        <h3 class="text-2xl font-extrabold mb-2">No Product Found</h3>
        <p class="text-[color:var(--ink-soft)] mb-6">The requested product could not be found in our collection.</p>
        <a href="shop.php" class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-xl">
          <i class="fa-solid fa-store"></i> Browse Our Collection
        </a>
      </div>
    <?php } ?>
  </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

<?php
if(!empty($message)){
  foreach($message as $m){
    echo '<div class="message"><i class="fa-solid fa-circle-info mr-2"></i>'.htmlspecialchars(ucfirst($m)).'</div>';
  }
}
?>

<script>
  // auto-hide toasts
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.message').forEach(el => {
      setTimeout(() => {
        el.style.opacity = '0';
        el.style.transform = 'translateX(16px)';
        setTimeout(() => el.remove(), 260);
      }, 3500);
    });
  });
</script>

</body>
</html>
