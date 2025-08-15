<?php
// Flash messages
if (isset($message)) {
  foreach ($message as $m) {
    echo '
    <div class="kp-message">
      <span>' . $m . '</span>
      <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
    </div>';
  }
}

// Helper: work out current file for active nav state
$current = basename($_SERVER['PHP_SELF'] ?? '');
?>
<!-- Orange Theme Header -->
<style>
  :root{
    --kp-primary:  #FF7F00;  /* vivid orange */
    --kp-secondary:#FF4500;  /* orange-red */
    --kp-accent:   #FFA500;  /* classic orange */
    --kp-dark:     #1A1200;  /* deep warm black */
    --kp-darker:   #0D0900;  /* darkest */
    --kp-white:    #ffffff;
    --kp-muted:    #cfcfcf;
  }

  /* Flash message */
  .kp-message{
    position:fixed; top:18px; right:18px; z-index:10000;
    display:flex; align-items:center; gap:.75rem;
    padding:.9rem 1.1rem; border-radius:12px;
    background: linear-gradient(90deg, var(--kp-primary), var(--kp-secondary));
    color:#111; box-shadow:0 10px 24px rgba(255,127,0,.28);
    border:1px solid rgba(255,255,255,.2);
    font: 500 1rem/1.3 "Inter", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  }
  .kp-message i{ cursor:pointer; opacity:.8; }
  .kp-message i:hover{ opacity:1; }

  /* Header base */
  .header{
    position:sticky; top:0; z-index:9999;
    background: rgba(255,255,255,.06);
    -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px);
    border-bottom:1px solid rgba(255,255,255,.15);
  }
  .header .flex{
    max-width:1200px; margin:0 auto; padding:16px 20px;
    display:flex; align-items:center; justify-content:space-between; gap:14px;
  }

  /* Brand (FORCE WHITE) */
  .header .logo{
    display:inline-flex; align-items:center; gap:.5rem;
    font-family: "Orbitron", monospace;
    font-weight:900; letter-spacing:.5px;
    font-size:1.6rem; text-decoration:none;
    color:#fff !important; /* <- force white brand text */
  }
  .header .logo span{
    display:inline-block; width:.65rem; height:.65rem; border-radius:50%;
    background: linear-gradient(135deg, var(--kp-primary), var(--kp-secondary));
    box-shadow:0 0 0 3px rgba(255,127,0,.18);
  }

  /* Navbar */
  .navbar{
    display:flex; gap:22px; align-items:center;
  }
  .navbar a{
    position:relative; text-decoration:none;
    color:var(--kp-white); opacity:.95;
    font: 600 1.05rem/1 "Inter", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    padding:10px 6px; transition: color .2s ease, opacity .2s ease;
  }
  .navbar a:hover{ color:var(--kp-accent); opacity:1; }
  .navbar a::after{
    content:""; position:absolute; left:0; bottom:4px; height:2px; width:0;
    background: linear-gradient(90deg, var(--kp-primary), var(--kp-secondary));
    transition: width .25s ease;
  }
  .navbar a:hover::after{ width:100%; }

  /* Active page indicator */
  .navbar a.active{
    color:var(--kp-accent);
  }
  .navbar a.active::after{
    width:100%;
  }

  /* Icons row */
  .icons{
    display:flex; align-items:center; gap:16px; color:var(--kp-white);
    font-size:1.2rem;
  }
  .icons a, .icons i{
    position:relative; display:inline-flex; align-items:center; justify-content:center;
    width:42px; height:42px; border-radius:50%;
    background: rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.15);
    text-decoration:none; color:var(--kp-white);
    transition: transform .2s ease, box-shadow .2s ease, background .2s ease, color .2s ease;
  }
  .icons a:hover, .icons i:hover{
    transform: translateY(-2px);
    background: linear-gradient(135deg, var(--kp-primary), var(--kp-secondary));
    color:#111;
    box-shadow:0 10px 20px rgba(255,127,0,.35);
  }

  /* Badges on wishlist/cart */
  .icons a span{
    position:absolute; top:-6px; right:-8px;
    background: linear-gradient(135deg, var(--kp-primary), var(--kp-secondary));
    color:#111; font:700 .75rem/1 "Inter", sans-serif;
    border-radius:999px; padding:.22rem .45rem;
    border:1px solid rgba(0,0,0,.1);
    box-shadow:0 4px 12px rgba(255,127,0,.28);
  }

  /* Profile dropdown panel */
  .profile{
    position:absolute; top:74px; right:20px; width:280px;
    background: rgba(0,0,0,.55);
    border:1px solid rgba(255,255,255,.15);
    -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px);
    border-radius:16px; padding:16px; display:none; z-index:1000;
    box-shadow:0 18px 40px rgba(0,0,0,.45);
  }
  .profile.active{ display:block; }
  .profile img{
    width:64px; height:64px; object-fit:cover; border-radius:50%;
    border:2px solid rgba(255,255,255,.25);
  }
  .profile p{
    margin-top:10px; font:700 1.05rem/1.2 "Inter", sans-serif; color:var(--kp-white);
  }

  /* Buttons inside profile */
  .profile .btn,
  .profile .option-btn,
  .profile .delete-btn{
    display:block; width:100%; text-align:center;
    margin-top:10px; padding:.75rem 1rem; border-radius:12px; text-decoration:none;
    font:700 1rem/1 "Inter", sans-serif;
    transition: transform .15s ease, box-shadow .2s ease, opacity .2s ease;
  }
  .profile .btn{
    background: linear-gradient(135deg, var(--kp-primary), var(--kp-secondary));
    color:#111; box-shadow:0 10px 18px rgba(255,127,0,.22);
  }
  .profile .option-btn{
    background: rgba(255,255,255,.08); color:var(--kp-white); border:1px solid rgba(255,255,255,.15);
  }
  .profile .delete-btn{
    background: rgba(255,69,0,.15); color:#ffd8cc; border:1px solid rgba(255,69,0,.35);
  }
  .profile .btn:hover,
  .profile .option-btn:hover,
  .profile .delete-btn:hover{ transform: translateY(-1px); opacity:.95; }

  .profile .flex-btn{
    display:flex; gap:10px; margin-top:10px;
  }

  /* Mobile menu toggle (hamburger) */
  #menu-btn{ display:none; cursor:pointer; }
  #user-btn{ cursor:pointer; }

  @media (max-width: 992px){
    .navbar{
      position:absolute; top:74px; left:0; right:0;
      flex-direction:column; gap:10px; padding:14px;
      background: rgba(0,0,0,.55);
      border:1px solid rgba(255,255,255,.15);
      -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px);
      display:none; margin:0 12px; border-radius:16px;
    }
    .navbar.active{ display:flex; }
    #menu-btn{ display:inline-flex; }
  }
</style>

<header class="header">
  <div class="flex">
    <!-- Brand -->
    <a href="admin_page.php" class="logo">Kandu Pinnawala<span></span></a>

    <!-- Nav -->
    <nav class="navbar" id="kp-navbar" role="navigation" aria-label="Primary">
      <a href="home.php"   class="<?php echo $current==='home.php'   ? 'active' : ''; ?>" <?php echo $current==='home.php'   ? 'aria-current="page"' : ''; ?>>home</a>
      <a href="shop.php"   class="<?php echo $current==='shop.php'   ? 'active' : ''; ?>" <?php echo $current==='shop.php'   ? 'aria-current="page"' : ''; ?>>shop</a>
      <a href="orders.php" class="<?php echo $current==='orders.php' ? 'active' : ''; ?>" <?php echo $current==='orders.php' ? 'aria-current="page"' : ''; ?>>orders</a>
      <!-- <a href="about.php" class="<?php // echo $current==='about.php' ? 'active' : ''; ?>">about</a> -->
      <a href="contact.php" class="<?php echo $current==='contact.php' ? 'active' : ''; ?>" <?php echo $current==='contact.php' ? 'aria-current="page"' : ''; ?>>custom orders</a>
    </nav>

    <!-- Icons -->
    <div class="icons">
      <i id="menu-btn" class="fas fa-bars" title="Menu" aria-label="Toggle menu"></i>
      <i id="user-btn" class="fas fa-user" title="Account" aria-label="Toggle account panel"></i>
      <a href="search_page.php" class="fas fa-search" title="Search" aria-label="Search"></a>
      <?php
        $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
        $count_cart_items->execute([$user_id]);
        $count_wishlist_items = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
        $count_wishlist_items->execute([$user_id]);
      ?>
      <a href="wishlist.php" title="Wishlist" aria-label="Wishlist">
        <i class="fas fa-heart"></i>
        <span>(<?= $count_wishlist_items->rowCount(); ?>)</span>
      </a>
      <a href="cart.php" title="Cart" aria-label="Cart">
        <i class="fas fa-shopping-cart"></i>
        <span>(<?= $count_cart_items->rowCount(); ?>)</span>
      </a>
    </div>

    <!-- Profile dropdown -->
    <div class="profile" id="kp-profile" aria-label="Account panel">
      <?php
        $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
        $select_profile->execute([$user_id]);
        $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
      ?>
      <img src="uploaded_img/<?= $fetch_profile['image']; ?>" alt="">
      <p><?= htmlspecialchars($fetch_profile['name']); ?></p>

      <a href="user_profile_update.php" class="btn">update profile</a>
      <a href="logout.php" class="delete-btn">logout</a>

      <div class="flex-btn">
        <a href="login.php" class="option-btn">login</a>
        <a href="register.php" class="option-btn">register</a>
      </div>
    </div>
  </div>
</header>

<!-- Minimal JS to toggle menu/profile -->
<script>
  (function(){
    const menuBtn = document.getElementById('menu-btn');
    const userBtn = document.getElementById('user-btn');
    const nav     = document.getElementById('kp-navbar');
    const profile = document.getElementById('kp-profile');

    if(menuBtn){
      menuBtn.addEventListener('click', ()=>{
        nav.classList.toggle('active');
        profile.classList.remove('active');
      });
    }
    if(userBtn){
      userBtn.addEventListener('click', ()=>{
        profile.classList.toggle('active');
        nav.classList.remove('active');
      });
    }

    // Close panels on outside click
    document.addEventListener('click', (e)=>{
      const withinHeader = e.target.closest('.header');
      if(!withinHeader){
        nav.classList.remove('active');
        profile.classList.remove('active');
      }
    });
  })();
</script>
