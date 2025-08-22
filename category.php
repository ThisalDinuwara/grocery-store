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

/* =========================================================
   CATEGORY RESOLUTION (after normalization)
========================================================= */
$category_param = isset($_GET['category']) ? trim($_GET['category']) : '';
$category_param = filter_var($category_param, FILTER_SANITIZE_STRING);

$category_id = null;
$category_label = '';

try {
   if ($category_param !== '') {
      if (ctype_digit($category_param)) {
         $cid = (int)$category_param;
         $stmt = $conn->prepare("SELECT name FROM `categories` WHERE id = ? LIMIT 1");
         $stmt->execute([$cid]);
         $name = $stmt->fetchColumn();
         if ($name) {
            $category_id = $cid;
            $category_label = $name;
         }
      } else {
         $needle = strtolower(preg_replace('/\s+/', '', $category_param));
         $stmt = $conn->prepare("SELECT id, name FROM `categories` WHERE LOWER(REPLACE(name,' ','')) = ? LIMIT 1");
         $stmt->execute([$needle]);
         if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $category_id = (int)$row['id'];
            $category_label = $row['name'];
         }
      }
   }
} catch (Exception $e) {
   // fall back silently
}

/* =========================================================
   Category list for search suggestions (datalist)
========================================================= */
$category_names = [];
try {
   $cq = $conn->query("SELECT name FROM `categories` ORDER BY name ASC");
   if ($cq) { $category_names = $cq->fetchAll(PDO::FETCH_COLUMN); }
} catch (Exception $e) {
   // ignore
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Category - Kandu Pinnawala</title>

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
              soft:      '#6B4E2E',  // subtle text
            },
            fontFamily: { gaming: ['Orbitron','monospace'] }
          }
        }
      }
   </script>

   <!-- font awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- site css -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      *{box-sizing:border-box}

      /* ===== Light Theme Base ===== */
      body{
         font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans";
         background: linear-gradient(135deg,#FFFDF9 0%,#F7F3ED 50%,#EFE8DE 100%);
         color:#2E1B0E; /* ink */
         overflow-x:hidden;
      }

      .hero-bg{
        background:
          radial-gradient(circle at 20% 80%, rgba(183,123,61,.18) 0%, transparent 55%),
          radial-gradient(circle at 80% 20%, rgba(212,163,115,.18) 0%, transparent 55%),
          radial-gradient(circle at 40% 40%, rgba(140,98,57,.18) 0%, transparent 55%);
      }

      /* Light glass + accents */
      .glass-effect{ background:rgba(255,255,255,.92); backdrop-filter:blur(10px); border:1px solid rgba(183,123,61,.22) }
      .neon-glow{ box-shadow:0 0 20px rgba(183,123,61,.18),0 0 40px rgba(212,163,115,.18),0 0 60px rgba(140,98,57,.12) }
      .hover-glow:hover{ transform:translateY(-4px); box-shadow:0 12px 28px rgba(183,123,61,.18); transition:all .3s ease }
      .gradient-text{ background: linear-gradient(45deg,#B77B3D,#D4A373); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text }
      .floating-animation{ animation:floating 3s ease-in-out infinite }
      @keyframes floating{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}

      /* Product card on light */
      .product-card{
        background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(252,247,238,.94));
        border:1px solid rgba(183,123,61,.26);
        border-radius:22px; backdrop-filter: blur(10px);
        transition: transform .3s ease, box-shadow .3s ease, border-color .3s ease;
        position:relative; overflow:hidden;
      }
      .product-card:hover{ transform: translateY(-6px); border-color: rgba(183,123,61,.5); box-shadow:0 22px 48px rgba(183,123,61,.2) }
      .product-card .aspect-square{
        border-radius:18px; overflow:hidden; border:1px solid rgba(183,123,61,.24);
        background: radial-gradient(600px 120px at 20% 0%, rgba(212,163,115,.18), transparent 60%);
      }
      .product-card img{ transition: transform .6s ease }
      .group:hover .product-card img{ transform: scale(1.06) }

      .price-badge{
        font-size:1.05rem; padding:.55rem 1rem; border:1px solid rgba(183,123,61,.25);
        background: linear-gradient(135deg,#B77B3D,#D4A373); color:#fff; border-radius:9999px;
        box-shadow:0 6px 18px rgba(183,123,61,.22)
      }
      .product-title{ color:#2E1B0E; font-weight:800; letter-spacing:.2px; line-height:1.25 }
      .qty{
        background: #fff; color:#2E1B0E; border:1px solid rgba(183,123,61,.26)
      }
      .qty:focus{ outline:none; box-shadow:0 0 0 3px rgba(183,123,61,.22) }

      .btn-grad{ background: linear-gradient(135deg,#B77B3D,#D4A373); color:#fff; }
      .muted{ color:#6B4E2E }
      .muted-2{ color:#8A6A49 }
      .icon-accent{ color:#8C6239 }
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<section class="relative py-16 md:py-20 hero-bg overflow-hidden">
  <div class="absolute top-10 left-10 w-72 h-72 bg-gradient-to-r from-[rgba(183,123,61,0.18)] to-[rgba(212,163,115,0.18)] rounded-full blur-3xl floating-animation"></div>
  <div class="absolute bottom-10 right-10 w-64 h-64 bg-gradient-to-r from-[rgba(212,163,115,0.18)] to-[rgba(140,98,57,0.18)] rounded-full blur-3xl floating-animation" style="animation-delay:1s"></div>

  <div class="container mx-auto px-6 lg:px-12 relative z-10">
    <div class="glass-effect rounded-3xl p-8 md:p-10 neon-glow">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
          <h1 class="text-4xl md:text-5xl font-bold mb-2">
            <span class="gradient-text font-gaming">CATEGORY</span>
          </h1>
          <p class="muted">
            <?= $category_label ? htmlspecialchars($category_label) : 'All Products'; ?>
          </p>
        </div>

        <!-- Category Search (uses ?category=...) -->
        <form action="" method="get" class="w-full md:w-auto">
          <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative">
              <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 icon-accent"></i>
              <input
                type="text"
                name="category"
                list="categoryList"
                value="<?= htmlspecialchars($category_param); ?>"
                placeholder="Search category (e.g., Wood, Brass)…"
                class="w-full sm:w-80 pl-10 pr-3 py-2 rounded-xl glass-effect focus:outline-none focus:ring-2 focus:ring-[rgba(183,123,61,0.45)]"
              />
              <datalist id="categoryList">
                <?php foreach($category_names as $cn): ?>
                  <option value="<?= htmlspecialchars($cn); ?>"></option>
                <?php endforeach; ?>
              </datalist>
            </div>
            <div class="flex gap-2">
              <button class="inline-flex items-center gap-2 px-5 py-2 rounded-xl btn-grad hover-glow">
                <i class="fas fa-search"></i> Search
              </button>
              <a href="category.php" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl glass-effect hover-glow">
                <i class="fas fa-rotate-left icon-accent"></i> Reset
              </a>
            </div>
          </div>
        </form>

      </div>
    </div>
  </div>
</section>

<section id="products" class="py-16">
  <div class="container mx-auto px-6 lg:px-12">
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
      <?php
        // Fetch products using new schema (products.category_id)
        if ($category_id !== null) {
           $select_products = $conn->prepare("SELECT * FROM `products` WHERE category_id = ? ORDER BY id DESC");
           $select_products->execute([$category_id]);
        } else {
           $select_products = $conn->prepare("SELECT * FROM `products` ORDER BY id DESC");
           $select_products->execute();
        }

        if($select_products->rowCount() > 0){
          while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){
      ?>
      <form action="" method="POST" class="group">
        <div class="product-card p-6 relative h-full flex flex-col">
          <!-- Price badge -->
          <div class="absolute top-6 left-6 price-badge z-10">
            Rs <?= htmlspecialchars($fetch_products['price']); ?>/-
          </div>

          <!-- Quick actions -->
          <div class="absolute top-6 right-6 flex gap-2 z-10">
            <button type="submit" name="add_to_wishlist" title="Add to wishlist"
                    class="w-11 h-11 glass-effect rounded-full flex items-center justify-center hover:text-white hover:bg-gradient-to-r hover:from-[#B77B3D] hover:to-[#D4A373] transition"
                    aria-label="Add to wishlist">
              <i class="fas fa-heart icon-accent"></i>
            </button>
            <a href="view_page.php?pid=<?= (int)$fetch_products['id']; ?>"
               class="w-11 h-11 glass-effect rounded-full flex items-center justify-center hover:text-white hover:bg-gradient-to-r hover:from-[#B77B3D] hover:to-[#D4A373] transition"
               aria-label="View">
               <i class="fas fa-eye icon-accent"></i>
            </a>
          </div>

          <!-- Image -->
          <div class="aspect-square mb-6">
            <img src="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>"
                 alt="<?= htmlspecialchars($fetch_products['name']); ?>"
                 class="w-full h-full object-cover">
          </div>

          <!-- Info -->
          <div class="space-y-4 mt-auto">
            <h3 class="product-title text-xl"><?= htmlspecialchars($fetch_products['name']); ?></h3>

            <!-- Hidden inputs -->
            <input type="hidden" name="pid" value="<?= (int)$fetch_products['id']; ?>">
            <input type="hidden" name="p_name" value="<?= htmlspecialchars($fetch_products['name']); ?>">
            <input type="hidden" name="p_price" value="<?= htmlspecialchars($fetch_products['price']); ?>">
            <input type="hidden" name="p_image" value="<?= htmlspecialchars($fetch_products['image']); ?>">

            <!-- Qty -->
            <div class="flex items-center gap-3">
              <label class="text-sm font-semibold muted">QTY:</label>
              <input type="number" min="1" value="1" name="p_qty"
                     class="qty w-24 px-3 py-2 rounded-lg text-center">
            </div>

            <!-- Add to cart -->
            <button type="submit" name="add_to_cart"
                    class="w-full btn-grad py-3.5 rounded-xl font-semibold hover-glow transition">
              <i class="fas fa-shopping-cart mr-2"></i> Add to Cart
            </button>
          </div>
        </div>
      </form>
      <?php
          }
        } else {
          echo '<div class="col-span-full">
                  <div class="glass-effect p-12 rounded-3xl text-center">
                    <i class="fas fa-box-open text-5xl icon-accent"></i>
                    <p class="mt-4 text-xl muted">No products available!</p>
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
