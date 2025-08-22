<?php
// login.php
@require_once 'config.php';
session_start();

/* ----------------------------
   Helpers (PHP 8.1+ safe)
---------------------------- */
function clean_text($v){ return trim((string)$v); }
function valid_email($v){ return filter_var($v, FILTER_VALIDATE_EMAIL); }

/* ----------------------------
   Handle login
---------------------------- */
$message = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Allow email OR username in the same field
  $identifier = clean_text($_POST['email'] ?? '');
  $password   = $_POST['pass'] ?? '';

  if ($identifier === '' || $password === '') {
    $message[] = 'Please fill in all fields';
  } else {

    try {
      // If it's an email, match by email; otherwise also try by username (name)
      $isEmail = (bool) valid_email($identifier);

      if ($isEmail) {
        $stmt = $conn->prepare("SELECT id, name, email, password, user_type FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$identifier]);
      } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, user_type FROM users WHERE name = ? OR email = ? LIMIT 1");
        $stmt->execute([$identifier, $identifier]);
      }

      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$row) {
        $message[] = 'incorrect email or password!';
      } else {
        $stored = $row['password'] ?? '';

        // Prefer modern hashes
        $ok = false;
        if (preg_match('/^\$2y\$/', $stored) || str_starts_with($stored, '$argon2')) {
          // bcrypt or argon2
          $ok = password_verify($password, $stored);
        } else {
          // Legacy MD5 fallback (compare md5 of entered password to stored hash)
          $ok = (md5($password) === $stored);
        }

        if ($ok) {
          if (($row['user_type'] ?? '') === 'admin') {
            $_SESSION['admin_id'] = $row['id'];
            header('Location: admin_page.php');
            exit;
          } elseif (($row['user_type'] ?? '') === 'user') {
            $_SESSION['user_id'] = $row['id'];
            header('Location: home.php');
            exit;
          } else {
            $message[] = 'no user found!';
          }
        } else {
          $message[] = 'incorrect email or password!';
        }
      }

    } catch (Throwable $e) {
      // Hide details from UI; log if you have a logger
      $message[] = 'Login error. Please try again.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login — Kandu Pinnawala</title>

  <!-- Tailwind for theme -->
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
            offwhite2: '#EDE9E3'
          },
          fontFamily: {
            inter: ['Inter','sans-serif'],
            gaming: ['Orbitron','monospace']
          }
        }
      }
    }
  </script>

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    *{box-sizing:border-box}

    /* ===== Off-white site theme (matches orders/cart/register) ===== */
    body{
      font-family:'Inter',sans-serif; line-height:1.6; color:#3E2723;
      background:linear-gradient(135deg,#F9F9F6 0%,#F2EFEA 50%,#EAE5DD 100%);
      min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem;
      overflow-x:hidden;
    }
    /* Hero mist / soft spotlight */
    .hero-mist{position:fixed; inset:0; z-index:-1;
      background:
        radial-gradient(900px 340px at 50% 30%, rgba(234,226,214,.65) 0%, transparent 60%),
        radial-gradient(800px 320px at 15% 70%, rgba(245,240,232,.5) 0%, transparent 65%),
        radial-gradient(900px 340px at 85% 70%, rgba(255,255,255,.5) 0%, transparent 65%);
    }
    /* Glass card + soft shadow */
    .glass{
      background:rgba(255,255,255,.68);
      -webkit-backdrop-filter:blur(14px); backdrop-filter:blur(14px);
      border:1px solid rgba(140,120,100,.18); border-radius:22px;
    }
    .shadow-soft{ box-shadow:0 6px 18px rgba(120,100,80,.12), 0 12px 36px rgba(120,100,80,.10); }

    /* Title styles */
    .heading{ font-family:'Orbitron',monospace; font-weight:800; letter-spacing:-.5px; }
    .gradtext{
      background:linear-gradient(45deg,#C89F6D,#A67B5B,#7B5E42);
      -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;
    }

    /* Inputs / buttons (light theme) */
    .input-lite{
      width:100%; padding:1rem 1.15rem; border-radius:16px; background:#fff;
      border:1px solid #ddd; color:#2a1e1a; font-size:1rem;
      transition:border-color .2s, box-shadow .2s;
    }
    .input-lite::placeholder{color:#8b776a}
    .input-lite:focus{outline:none; border-color:#C89F6D; box-shadow:0 0 0 4px rgba(200,159,109,.22)}

    .btn-primary{
      display:inline-flex; align-items:center; justify-content:center; width:100%;
      background:linear-gradient(135deg,#C89F6D,#7B5E42); color:#fff; padding:1rem 1.15rem;
      border:none; border-radius:14px; font-weight:600;
      box-shadow:0 8px 18px rgba(120,100,80,.18);
      transition:transform .15s, box-shadow .2s, opacity .2s;
    }
    .btn-primary:hover{ transform:translateY(-2px); box-shadow:0 12px 22px rgba(120,100,80,.22); }

    /* Flash message (light) */
    .message{
      position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:50;
      background:rgba(245,243,238,.96); color:#3E2723;
      border:1px solid rgba(140,120,100,.25); padding:0.9rem 1.1rem; border-radius:12px;
    }
    .message i{ cursor:pointer; margin-left:.75rem }
    a.link{ color:#6a584d; text-decoration:underline; text-decoration-color:#C89F6D; text-underline-offset:4px }
    a.link:hover{ color:#7B5E42 }
  </style>
</head>
<body>
  <div class="hero-mist"></div>

  <?php if (!empty($message)): foreach ($message as $m): ?>
    <div class="message shadow-soft">
      <span><?= htmlspecialchars($m) ?></span>
      <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
    </div>
  <?php endforeach; endif; ?>

  <section class="w-full max-w-2xl mx-auto">
    <div class="glass shadow-soft rounded-3xl p-8 md:p-12">
      <div class="text-center mb-8">
        <h1 class="heading text-4xl md:text-5xl leading-tight">
          <span class="gradtext">LOGIN</span>
        </h1>
        <div class="h-1 w-28 bg-gradient-to-r from-[#C89F6D] to-[#7B5E42] rounded-full mx-auto mt-4"></div>
        <p class="text-[#6a584d] mt-4 text-base md:text-lg">Welcome back! Sign in to continue.</p>
      </div>

      <form action="" method="POST" class="space-y-5">
        <div>
          <label class="block text-sm md:text-base font-medium text-[#6a584d] mb-2">Email or Username</label>
          <input type="text" name="email" class="input-lite" placeholder="Enter email or username" required>
        </div>

        <div>
          <label class="block text-sm md:text-base font-medium text-[#6a584d] mb-2">Password</label>
          <input type="password" name="pass" class="input-lite" placeholder="Enter your password" required>
        </div>

        <button type="submit" name="submit" class="btn-primary mt-2">
          Login Now
        </button>

        <p class="text-center text-[#6a584d] text-base md:text-lg mt-2">
          Don't have an account? <a href="register.php" class="link">Register Now</a>
        </p>
      </form>
    </div>
  </section>
</body>
</html>
