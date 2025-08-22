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

<style>
  :root{
    /* ===== Light theme palette (off‑white base) ===== */
    --kp-bg-1: #FFFDF9;   /* top */
    --kp-bg-2: #F7F3ED;   /* middle */
    --kp-bg-3: #EFE8DE;   /* bottom */

    --kp-primary:   #B77B3D; /* warm brown */
    --kp-secondary: #D4A373; /* golden beige */
    --kp-accent:    #8C6239; /* deeper brown accent */

    --kp-ink:       #2E1B0E; /* body text */
    --kp-ink-soft:  #5C3A24; /* subtle text */

    /* NEW: explicit link colors for perfect contrast */
    --kp-link:        #6B4E2E; /* nav/items default */
    --kp-link-hover:  #9C6A3A; /* hover/active */

    --kp-white: #ffffff;
    --kp-muted: #EAE3D9;

    /* “Glass” for light UI */
    --kp-glass: rgba(255,255,255,0.7);
    --kp-glass-border: rgba(183,123,61,0.25);

    /* Hovers & shadows */
    --kp-hover-bg: rgba(183,123,61,0.10);
    --kp-shadow: 0 10px 24px rgba(183,123,61,0.18);
  }

  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    color: var(--kp-ink);
    background: linear-gradient(135deg, var(--kp-bg-1) 0%, var(--kp-bg-2) 50%, var(--kp-bg-3) 100%);
  }

  /* Flash Messages (light on warm gradient) */
  .kp-message {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--kp-primary), var(--kp-secondary));
    color: #fff;
    box-shadow: var(--kp-shadow);
    border: 1px solid var(--kp-glass-border);
    font-weight: 600;
    backdrop-filter: blur(10px);
    animation: slideIn 0.3s ease-out;
  }
  @keyframes slideIn { from {transform: translateX(100%); opacity: 0;} to {transform: translateX(0); opacity: 1;} }
  .kp-message i { cursor: pointer; opacity: .9; transition: opacity .2s ease; }
  .kp-message i:hover { opacity: 1; }

  /* ===== Header (light) ===== */
  .header {
    position: sticky !important;
    top: 0 !important;
    z-index: 9999 !important;
    background: linear-gradient(135deg, #FFFDF9 0%, #F7F3ED 50%, #EFE8DE 100%) !important;
    backdrop-filter: blur(16px) !important;
    border-bottom: 1px solid var(--kp-glass-border) !important;
    box-shadow: 0 8px 22px rgba(0,0,0,0.06) !important;
    width: 100% !important;
    min-height: 80px !important;
  }
  .header::before {
    content: '' !important;
    position: absolute !important;
    inset: 0 !important;
    background: linear-gradient(135deg, rgba(255,255,255,.6) 0%, rgba(255,255,255,.35) 100%) !important;
    pointer-events: none !important;
    z-index: 1 !important;
  }

  .header-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 80px;
    position: relative;
    z-index: 2;
  }

  /* Brand Logo */
  .logo {
    display: flex; align-items: center; gap: 12px;
    text-decoration: none;
    font-family: 'Orbitron', monospace;
    font-weight: 800; font-size: 1.7rem;
    color: var(--kp-link) !important;  /* ensure readable logo text */
    letter-spacing: .5px;
    transition: transform .25s ease, color .2s ease;
  }
  .logo:hover { transform: scale(1.03); color: var(--kp-link-hover) !important; }

  .logo-icon {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid var(--kp-glass-border);
    background: linear-gradient(135deg, var(--kp-primary), var(--kp-secondary));
    box-shadow: var(--kp-shadow);
    overflow: hidden; flex-shrink: 0;
  }
  .logo-icon img { width: 100%; height: 100%; object-fit: contain; padding: 4px; border-radius: 8px; }
  .logo-icon:has(img)::before { display: none; }
  .logo-icon::before { content: '🏪'; font-size: 22px; color: #fff; }

  /* Navigation — explicit link colors for contrast */
  .navbar { display: flex; gap: 24px; align-items: center; }
  .navbar a {
    position: relative; text-decoration: none;
    color: var(--kp-link);
    font-weight: 600; font-size: 1rem;
    padding: 10px 14px; border-radius: 12px;
    transition: all .2s ease;
    text-transform: capitalize;
  }
  .navbar a::before {
    content: ''; position: absolute; inset: 0;
    background: var(--kp-hover-bg);
    border-radius: 12px; opacity: 0; transition: opacity .2s ease; z-index: -1;
  }
  .navbar a:hover::before, .navbar a.active::before { opacity: 1; }
  .navbar a:hover, .navbar a.active {
    color: var(--kp-link-hover);
  }

  /* Icons Section */
  .icons { display: flex; align-items: center; gap: 12px; }
  .icon-btn {
    position: relative; display: flex; align-items: center; justify-content: center;
    width: 46px; height: 46px; border-radius: 12px;
    background: var(--kp-glass);
    border: 1px solid var(--kp-glass-border);
    color: var(--kp-link);                 /* readable icon color */
    text-decoration: none; transition: all .2s ease;
    cursor: pointer; backdrop-filter: blur(10px);
  }
  .icon-btn:hover {
    background: linear-gradient(135deg, var(--kp-primary), var(--kp-secondary));
    color: #fff; transform: translateY(-2px); box-shadow: var(--kp-shadow);
  }
  .icon-btn i { font-size: 18px; }

  /* Badge */
  .badge {
    position: absolute; top: -8px; right: -8px;
    background: linear-gradient(135deg, var(--kp-secondary), #EBC58A);
    color: #442A17;
    font-size: 12px; font-weight: 800; padding: 4px 8px; border-radius: 20px;
    min-width: 20px; text-align: center; border: 2px solid var(--kp-white);
    box-shadow: 0 4px 12px rgba(212,163,115,.35);
  }

  /* Profile Dropdown (light) */
  .profile-dropdown {
    position: absolute; top: calc(100% + 12px); right: 24px; width: 320px;
    background: var(--kp-white);
    color: var(--kp-ink);                 /* readable dropdown text */
    backdrop-filter: blur(12px);
    border: 1px solid var(--kp-glass-border);
    border-radius: 18px; padding: 20px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.08);
    opacity: 0; visibility: hidden; transform: translateY(-10px);
    transition: all .2s ease;
  }
  .profile-dropdown.active { opacity: 1; visibility: visible; transform: translateY(0); }

  .profile-info { display: flex; align-items: center; gap: 16px; margin-bottom: 16px; }
  .profile-avatar { width: 60px; height: 60px; border-radius: 14px; object-fit: cover; border: 2px solid var(--kp-glass-border); background: #f9f6f1; }
  .profile-name { color: var(--kp-ink); font-size: 1.1rem; font-weight: 800; margin: 0; }

  .profile-actions { display: flex; flex-direction: column; gap: 10px; }
  .profile-btn {
    display: block; width: 100%; padding: 12px 14px; border-radius: 12px;
    text-decoration: none; font-weight: 700; text-align: center; transition: all .2s ease;
    border: 1px solid var(--kp-glass-border);
    color: var(--kp-link);                 /* fix: readable button text */
    background: var(--kp-glass);
  }
  .profile-btn.primary {
    background: linear-gradient(135deg, var(--kp-primary), var(--kp-secondary));
    color: #fff; box-shadow: var(--kp-shadow); border: none;
  }
  .profile-btn.secondary:hover { background: var(--kp-hover-bg); color: var(--kp-link-hover); }
  .profile-btn.danger { background: #FFF3F3; color: #C44242; border: 1px solid rgba(196,66,66,0.25); }
  .profile-btn:hover { transform: translateY(-1px); box-shadow: 0 12px 24px rgba(0,0,0,0.06); }

  .auth-buttons { display: flex; gap: 10px; }
  .auth-buttons .profile-btn { flex: 1; }

  /* Mobile */
  .mobile-menu-btn { display: none; }

  @media (max-width: 992px) {
    .navbar {
      position: absolute; top: 100%; left: 24px; right: 24px;
      flex-direction: column; gap: 8px;
      background: var(--kp-white);
      color: var(--kp-ink);
      backdrop-filter: blur(12px);
      border: 1px solid var(--kp-glass-border);
      border-radius: 18px; padding: 16px;
      box-shadow: 0 20px 50px rgba(0,0,0,0.06);
      opacity: 0; visibility: hidden; transform: translateY(-10px);
      transition: all .2s ease;
    }
    .navbar.active { opacity: 1; visibility: visible; transform: translateY(0); }
    .navbar a { width: 100%; text-align: center; padding: 14px; color: var(--kp-link); }
    .navbar a:hover, .navbar a.active { color: var(--kp-link-hover); }

    .mobile-menu-btn { display: flex; }
    .logo { font-size: 1.45rem; }
    .logo-icon { width: 42px; height: 42px; }
    .icons { gap: 10px; }
    .icon-btn { width: 44px; height: 44px; }

    .profile-dropdown { right: 16px; width: calc(100vw - 32px); max-width: 300px; }
    .header-container { padding: 0 16px; height: 70px; }
  }

  /* Ensure header stays light across overrides */
  header.header, .header, header {
    background: linear-gradient(135deg, #FFFDF9 0%, #F7F3ED 50%, #EFE8DE 100%) !important;
  }
</style>

<header class="header">
  <div class="header-container">
    <!-- Brand Logo -->
    <a href="admin_page.php" class="logo">
      <div class="logo-icon">
        <!-- Replace with your actual logo path -->
        <img src="images/Logo.png" alt="Kandu Pinnawala Logo"
             onerror="this.style.display='none'; this.parentElement.classList.add('no-logo');">
      </div>
      Kandu Pinnawala
    </a>

    <!-- Navigation -->
    <nav class="navbar" id="navbar" role="navigation" aria-label="Primary">
      <a href="home.php" class="<?php echo $current === 'home.php' ? 'active' : ''; ?>"
         <?php echo $current === 'home.php' ? 'aria-current="page"' : ''; ?>>Home</a>
      <a href="shop.php" class="<?php echo $current === 'shop.php' ? 'active' : ''; ?>"
         <?php echo $current === 'shop.php' ? 'aria-current="page"' : ''; ?>>Shop</a>
      <a href="orders.php" class="<?php echo $current === 'orders.php' ? 'active' : ''; ?>"
         <?php echo $current === 'orders.php' ? 'aria-current="page"' : ''; ?>>Orders</a>
      <a href="contact.php" class="<?php echo $current === 'contact.php' ? 'active' : ''; ?>"
         <?php echo $current === 'contact.php' ? 'aria-current="page"' : ''; ?>>Custom Orders</a>
    </nav>

    <!-- Icons Section -->
    <div class="icons">
      <!-- Mobile Menu Button -->
      <div class="icon-btn mobile-menu-btn" id="mobile-menu-btn" title="Menu" aria-label="Toggle menu">
        <i class="fas fa-bars"></i>
      </div>

      <!-- Search -->
      <a href="search_page.php" class="icon-btn" title="Search" aria-label="Search">
        <i class="fas fa-search"></i>
      </a>

      <!-- Wishlist -->
      <?php
        $count_wishlist_items = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
        $count_wishlist_items->execute([$user_id]);
        $wishlist_count = $count_wishlist_items->rowCount();
      ?>
      <a href="wishlist.php" class="icon-btn" title="Wishlist" aria-label="Wishlist">
        <i class="fas fa-heart"></i>
        <?php if($wishlist_count > 0): ?>
          <span class="badge"><?= $wishlist_count; ?></span>
        <?php endif; ?>
      </a>

      <!-- Cart -->
      <?php
        $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
        $count_cart_items->execute([$user_id]);
        $cart_count = $count_cart_items->rowCount();
      ?>
      <a href="cart.php" class="icon-btn" title="Cart" aria-label="Cart">
        <i class="fas fa-shopping-cart"></i>
        <?php if($cart_count > 0): ?>
          <span class="badge"><?= $cart_count; ?></span>
        <?php endif; ?>
      </a>

      <!-- User Profile -->
      <div class="icon-btn" id="profile-btn" title="Account" aria-label="Toggle account panel">
        <i class="fas fa-user"></i>
      </div>
    </div>

    <!-- Profile Dropdown -->
    <div class="profile-dropdown" id="profile-dropdown" aria-label="Account panel">
      <?php
        $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
        $select_profile->execute([$user_id]);

        if($select_profile->rowCount() > 0) {
          $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
      ?>
          <!-- Logged in user -->
          <div class="profile-info">
            <img src="uploaded_img/<?= htmlspecialchars($fetch_profile['image']); ?>"
                 alt="Profile" class="profile-avatar">
            <p class="profile-name"><?= htmlspecialchars($fetch_profile['name']); ?></p>
          </div>

          <div class="profile-actions">
            <a href="user_profile_update.php" class="profile-btn primary">Update Profile</a>
            <a href="logout.php" class="profile-btn danger">Logout</a>
          </div>
      <?php } else { ?>
          <!-- Guest user -->
          <div class="profile-info">
            <div class="profile-avatar" style="display:flex;align-items:center;justify-content:center;">
              <i class="fas fa-user" style="color: var(--kp-ink-soft); font-size: 22px;"></i>
            </div>
            <p class="profile-name">Guest User</p>
          </div>

          <div class="profile-actions">
            <div class="auth-buttons">
              <a href="login.php" class="profile-btn">Login</a>
              <a href="register.php" class="profile-btn primary">Register</a>
            </div>
          </div>
      <?php } ?>
    </div>
  </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const mobileMenuBtn = document.getElementById('mobile-menu-btn');
  const profileBtn = document.getElementById('profile-btn');
  const navbar = document.getElementById('navbar');
  const profileDropdown = document.getElementById('profile-dropdown');

  // Mobile menu toggle
  if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      navbar.classList.toggle('active');
      profileDropdown.classList.remove('active');
    });
  }

  // Profile dropdown toggle
  if (profileBtn) {
    profileBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      profileDropdown.classList.toggle('active');
      navbar.classList.remove('active');
    });
  }

  // Close dropdowns when clicking outside
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.header')) {
      navbar.classList.remove('active');
      profileDropdown.classList.remove('active');
    }
  });

  // Close dropdowns on escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      navbar.classList.remove('active');
      profileDropdown.classList.remove('active');
    }
  });

  // Auto-remove flash messages after 5 seconds
  const messages = document.querySelectorAll('.kp-message');
  messages.forEach(function(message) {
    setTimeout(function() {
      if (message.parentElement) {
        message.style.transform = 'translateX(100%)';
        message.style.opacity = '0';
        setTimeout(function() { message.remove(); }, 300);
      }
    }, 5000);
  });
});
</script>
