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
    --kp-primary: #FF6B35;
    --kp-secondary: #F7931E;
    --kp-accent: #FFD23F;
    --kp-dark: #2C1810;
    --kp-darker: #1A0F08;
    --kp-white: #ffffff;
    --kp-muted: #E8E8E8;
    --kp-glass: rgba(255, 255, 255, 0.1);
    --kp-glass-border: rgba(255, 255, 255, 0.2);
  }

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  }

  /* Flash Messages */
  .kp-message {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-radius: 16px;
    background: linear-gradient(135deg, var(--kp-primary), var(--kp-secondary));
    color: white;
    box-shadow: 0 20px 40px rgba(255, 107, 53, 0.3);
    border: 1px solid var(--kp-glass-border);
    font-weight: 500;
    backdrop-filter: blur(10px);
    animation: slideIn 0.3s ease-out;
  }

  @keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
  }

  .kp-message i {
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.2s ease;
  }

  .kp-message i:hover {
    opacity: 1;
  }

  /* Header */
  .header {
    position: sticky !important;
    top: 0 !important;
    z-index: 9999 !important;
    background: linear-gradient(135deg, #8B4513 0%, #D2691E 50%, #CD853F 100%) !important;
    backdrop-filter: blur(20px) !important;
    border-bottom: 1px solid var(--kp-glass-border) !important;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1) !important;
    width: 100% !important;
    min-height: 80px !important;
  }

  .header::before {
    content: '' !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%) !important;
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
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    font-family: 'Orbitron', monospace;
    font-weight: 800;
    font-size: 1.8rem;
    color: white !important;
    letter-spacing: 0.5px;
    transition: transform 0.3s ease;
  }

  .logo:hover {
    transform: scale(1.05);
  }

  .logo-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 24px rgba(255, 107, 53, 0.4);
    border: 2px solid rgba(255, 255, 255, 0.2);
    overflow: hidden;
    background: linear-gradient(135deg, var(--kp-primary), var(--kp-secondary));
    flex-shrink: 0;
  }

  .logo-icon img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center;
    padding: 4px;
    border-radius: 8px;
  }

  /* Hide the fallback icon when image loads */
  .logo-icon:has(img)::before {
    display: none;
  }

  /* Fallback icon when no image is provided */
  .logo-icon::before {
    content: '🏪';
    font-size: 24px;
    display: block;
  }

  /* Navigation */
  .navbar {
    display: flex;
    gap: 32px;
    align-items: center;
  }

  .navbar a {
    position: relative;
    text-decoration: none;
    color: white;
    font-weight: 600;
    font-size: 1rem;
    padding: 12px 16px;
    border-radius: 12px;
    transition: all 0.3s ease;
    text-transform: capitalize;
  }

  .navbar a::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: var(--kp-glass);
    border-radius: 12px;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: -1;
  }

  .navbar a:hover::before,
  .navbar a.active::before {
    opacity: 1;
  }

  .navbar a.active {
    color: var(--kp-accent);
    font-weight: 700;
  }

  .navbar a:hover {
    color: var(--kp-accent);
    transform: translateY(-2px);
  }

  /* Icons Section */
  .icons {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .icon-btn {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: var(--kp-glass);
    border: 1px solid var(--kp-glass-border);
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
    backdrop-filter: blur(10px);
  }

  .icon-btn:hover {
    background: linear-gradient(135deg, var(--kp-primary), var(--kp-secondary));
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(255, 107, 53, 0.4);
  }

  .icon-btn i {
    font-size: 18px;
  }

  /* Badge */
  .badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: linear-gradient(135deg, var(--kp-accent), #FFB000);
    color: var(--kp-darker);
    font-size: 12px;
    font-weight: 700;
    padding: 4px 8px;
    border-radius: 20px;
    min-width: 20px;
    text-align: center;
    border: 2px solid white;
    box-shadow: 0 4px 12px rgba(255, 210, 63, 0.4);
  }

  /* Profile Dropdown */
  .profile-dropdown {
    position: absolute;
    top: calc(100% + 12px);
    right: 24px;
    width: 320px;
    background: rgba(44, 24, 16, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid var(--kp-glass-border);
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
  }

  .profile-dropdown.active {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
  }

  .profile-info {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
  }

  .profile-avatar {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    object-fit: cover;
    border: 3px solid var(--kp-glass-border);
  }

  .profile-name {
    color: white;
    font-size: 1.2rem;
    font-weight: 700;
    margin: 0;
  }

  .profile-actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .profile-btn {
    display: block;
    width: 100%;
    padding: 12px 16px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    text-align: center;
    transition: all 0.3s ease;
  }

  .profile-btn.primary {
    background: linear-gradient(135deg, var(--kp-primary), var(--kp-secondary));
    color: white;
    box-shadow: 0 8px 20px rgba(255, 107, 53, 0.3);
  }

  .profile-btn.secondary {
    background: var(--kp-glass);
    color: white;
    border: 1px solid var(--kp-glass-border);
  }

  .profile-btn.danger {
    background: rgba(220, 53, 69, 0.2);
    color: #ff6b6b;
    border: 1px solid rgba(220, 53, 69, 0.3);
  }

  .profile-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
  }

  .auth-buttons {
    display: flex;
    gap: 12px;
  }

  .auth-buttons .profile-btn {
    flex: 1;
  }

  /* Mobile Menu Button */
  .mobile-menu-btn {
    display: none;
  }

  /* Responsive Design */
  @media (max-width: 992px) {
    .navbar {
      position: absolute;
      top: 100%;
      left: 24px;
      right: 24px;
      flex-direction: column;
      gap: 8px;
      background: rgba(44, 24, 16, 0.95);
      backdrop-filter: blur(20px);
      border: 1px solid var(--kp-glass-border);
      border-radius: 20px;
      padding: 20px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      opacity: 0;
      visibility: hidden;
      transform: translateY(-10px);
      transition: all 0.3s ease;
    }

    .navbar.active {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }

    .navbar a {
      width: 100%;
      text-align: center;
      padding: 16px;
    }

    .mobile-menu-btn {
      display: flex;
    }

    .logo {
      font-size: 1.5rem;
    }

    .logo-icon {
      width: 40px;
      height: 40px;
    }

    .icons {
      gap: 12px;
    }

    .icon-btn {
      width: 44px;
      height: 44px;
    }
  }

  /* Additional override styles to ensure header background works */
  header.header,
  .header,
  header {
    background: linear-gradient(135deg, #8B4513 0%, #D2691E 50%, #CD853F 100%) !important;
    position: sticky !important;
    top: 0 !important;
    z-index: 9999 !important;
  }
  
  /* Ensure no other styles override the header */
  body .header {
    background: linear-gradient(135deg, #8B4513 0%, #D2691E 50%, #CD853F 100%) !important;
  }
    .header-container {
      padding: 0 16px;
      height: 70px;
    }

    .profile-dropdown {
      right: 16px;
      width: calc(100vw - 32px);
      max-width: 300px;
    }

    .logo {
      font-size: 1.3rem;
    }
  }
</style>

<header class="header">
  <div class="header-container">
    <!-- Brand Logo -->
    <a href="admin_page.php" class="logo">
      <div class="logo-icon">
        <!-- Replace with your actual logo path -->
        <img src="images/logo.jpeg" alt="Kandu Pinnawala Logo" 
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
      <?php
        } else {
      ?>
          <!-- Guest user -->
          <div class="profile-info">
            <div class="profile-avatar" style="background: linear-gradient(135deg, var(--kp-primary), var(--kp-secondary)); display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-user" style="color: white; font-size: 24px;"></i>
            </div>
            <p class="profile-name">Guest User</p>
          </div>
          
          <div class="profile-actions">
            <div class="auth-buttons">
              <a href="login.php" class="profile-btn secondary">Login</a>
              <a href="register.php" class="profile-btn primary">Register</a>
            </div>
          </div>
      <?php
        }
      ?>
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
        setTimeout(function() {
          message.remove();
        }, 300);
      }
    }, 5000);
  });
});
</script>