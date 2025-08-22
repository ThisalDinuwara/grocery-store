<?php
// register.php
@require_once 'config.php'; // must create a PDO instance in $conn
session_start();

/* ---------- Helpers (PHP 8.1+ safe) ---------- */
function t($v){ return trim((string)$v); }
function is_valid_email($v){ return (bool)filter_var($v, FILTER_VALIDATE_EMAIL); }
function is_valid_contact($v){ return $v === '' || preg_match('/^[0-9+\-\s]{6,20}$/', $v); }
function has_column(PDO $pdo, $table, $col){
  $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
  $stmt->execute([$col]);
  return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
}

$messages = [];

/* ---------- Handle form ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

  $name    = t($_POST['name']  ?? '');
  $email   = t($_POST['email'] ?? '');
  $pass    = $_POST['pass']    ?? '';
  $cpass   = $_POST['cpass']   ?? '';
  $contact = t($_POST['contact'] ?? '');

  // Basic validation
  if ($name === '' || $email === '' || $pass === '' || $cpass === '') {
    $messages[] = 'Please fill in all required fields.';
  } elseif (!is_valid_email($email)) {
    $messages[] = 'Please enter a valid email address.';
  } elseif (strlen($pass) < 6) {
    $messages[] = 'Password must be at least 6 characters.';
  } elseif ($pass !== $cpass) {
    $messages[] = 'Confirm password not matched!';
  } elseif (!is_valid_contact($contact)) {
    $messages[] = 'Contact number looks invalid.';
  } else {
    try {
      // Email uniqueness
      $chk = $conn->prepare("SELECT id FROM `users` WHERE email = ? LIMIT 1");
      $chk->execute([$email]);
      if ($chk->fetch()) {
        $messages[] = 'User email already exists!';
      } else {

        // Prepare upload (optional)
        $imageNameToStore = null;
        if (!empty($_FILES['image']['name'])) {
          $orig   = $_FILES['image']['name'];
          $size   = (int)($_FILES['image']['size'] ?? 0);
          $tmp    = $_FILES['image']['tmp_name'] ?? '';
          $ext    = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
          $allow  = ['jpg','jpeg','png','webp'];

          if (!in_array($ext, $allow, true)) {
            $messages[] = 'Image must be JPG, JPEG, PNG, or WEBP.';
          } elseif ($size > 2 * 1024 * 1024) {
            $messages[] = 'Image size is too large (max 2MB).';
          } elseif (!is_uploaded_file($tmp)) {
            $messages[] = 'Image upload failed.';
          } else {
            // Safe unique filename
            $imageNameToStore = 'u_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
          }
        }

        if (empty($messages)) {
          // Build dynamic INSERT based on existing columns
          $cols = ['name','email','password'];
          $vals = [$name, $email, password_hash($pass, PASSWORD_DEFAULT)];

          if (has_column($conn, 'users', 'contact')) {
            $cols[] = 'contact'; $vals[] = $contact;
          }
          if (has_column($conn, 'users', 'image') && $imageNameToStore !== null) {
            $cols[] = 'image';   $vals[] = $imageNameToStore;
          }

          $colSql  = '`' . implode('`,`', $cols) . '`';
          $qmarks  = rtrim(str_repeat('?,', count($cols)), ',');
          $sql     = "INSERT INTO `users` ($colSql) VALUES ($qmarks)";
          $ins     = $conn->prepare($sql);
          $ins->execute($vals);

          // Move image after DB write
          if ($imageNameToStore !== null) {
            @mkdir(__DIR__ . '/uploaded_img', 0775, true);
            move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/uploaded_img/' . $imageNameToStore);
          }

          $messages[] = 'Registered successfully!';
          header('Location: login.php');
          exit;
        }
      }
    } catch (Throwable $e) {
      // Log $e->getMessage() server-side if needed
      $messages[] = 'Registration error. Please try again.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — Kandu Pinnawala</title>

  <!-- Tailwind (theme) -->
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
          fontFamily: { inter:['Inter','sans-serif'], gaming:['Orbitron','monospace'] }
        }
      }
    }
  </script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    *{box-sizing:border-box}
    body{
      font-family:'Inter',sans-serif; line-height:1.6; color:#3E2723;
      background:linear-gradient(135deg,#F9F9F6 0%, #F2EFEA 50%, #EAE5DD 100%);
      min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem;
      overflow-x:hidden;
    }
    /* Soft spotlight like orders/cart */
    .hero-bg{position:fixed; inset:0; z-index:-1;
      background:
        radial-gradient(900px 340px at 50% 30%, rgba(234,226,214,.65) 0%, transparent 60%),
        radial-gradient(800px 320px at 15% 70%, rgba(245,240,232,.5) 0%, transparent 65%),
        radial-gradient(900px 340px at 85% 70%, rgba(255,255,255,.5) 0%, transparent 65%);
    }
    /* Glass card + soft shadow */
    .glass{background:rgba(255,255,255,.68); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px);
      border:1px solid rgba(140,120,100,.18); border-radius:22px;}
    .shadow-soft{ box-shadow:0 6px 18px rgba(120,100,80,.12), 0 12px 36px rgba(120,100,80,.10); }

    /* Title */
    .gradtxt{background:linear-gradient(45deg,#C89F6D,#A67B5B,#7B5E42); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent}
    .heading{ font-family:'Orbitron',monospace; font-weight:800; letter-spacing:-0.5px; }

    /* Inputs / buttons */
    .input-lite{
      width:100%; padding:.95rem 1.05rem; border-radius:14px; background:#fff;
      border:1px solid #ddd; color:#2a1e1a;
      transition:border-color .2s, box-shadow .2s;
    }
    .input-lite::placeholder{color:#8b776a}
    .input-lite:focus{outline:none; border-color:#C89F6D; box-shadow:0 0 0 3px rgba(200,159,109,.25)}

    .btn-primary{
      display:inline-flex; align-items:center; justify-content:center; width:100%;
      background:linear-gradient(135deg,#C89F6D,#7B5E42); color:#fff; padding:.95rem 1rem;
      border:none; border-radius:14px; font-weight:600;
      box-shadow:0 8px 18px rgba(120,100,80,.18);
      transition:transform .15s, box-shadow .2s, opacity .2s;
    }
    .btn-primary:hover{ transform:translateY(-2px); box-shadow:0 12px 22px rgba(120,100,80,.22); }

    /* Flash message (light) */
    .message{
      position:fixed; top:20px; left:50%; transform:translateX(-50%);
      background:rgba(245,243,238,.96);
      color:#3E2723; border:1px solid rgba(140,120,100,.25);
      padding:.85rem 1rem; border-radius:12px; z-index:50;
    }
    .message i{cursor:pointer; margin-left:.75rem}
  </style>
</head>
<body>
<div class="hero-bg"></div>

<?php if (!empty($messages)): foreach($messages as $m): ?>
  <div class="message shadow-soft">
    <span><?= htmlspecialchars($m) ?></span>
    <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
  </div>
<?php endforeach; endif; ?>

<section class="w-full px-6">
  <div class="max-w-xl mx-auto glass shadow-soft p-8 md:p-10">
    <div class="text-center mb-8">
      <h1 class="heading text-4xl md:text-5xl leading-tight">
        <span class="gradtxt">REGISTER</span>
      </h1>
      <div class="h-1 w-24 bg-gradient-to-r from-[#C89F6D] to-[#7B5E42] rounded-full mx-auto mt-4"></div>
      <p class="text-[#6a584d] mt-4">Create your account to start shopping.</p>
    </div>

    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-[#6a584d] mb-2">Full Name</label>
        <input type="text" name="name" class="input-lite" placeholder="enter your name" required>
      </div>

      <div>
        <label class="block text-sm font-medium text-[#6a584d] mb-2">Email</label>
        <input type="email" name="email" class="input-lite" placeholder="enter your email" required>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-[#6a584d] mb-2">Password</label>
          <input type="password" name="pass" class="input-lite" placeholder="enter your password" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-[#6a584d] mb-2">Confirm Password</label>
          <input type="password" name="cpass" class="input-lite" placeholder="confirm your password" required>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-[#6a584d] mb-2">Contact (optional)</label>
        <input type="text" name="contact" class="input-lite" placeholder="enter your contact">
      </div>

      <div>
        <label class="block text-sm font-medium text-[#6a584d] mb-2">Profile Image (optional)</label>
        <input type="file" name="image" accept="image/jpg, image/jpeg, image/png, image/webp"
               class="block w-full text-sm text-[#3E2723] file:mr-4 file:rounded-lg file:border-0 file:px-4 file:py-2
                      file:bg-gradient-to-r file:from-[#C89F6D] file:to-[#7B5E42] file:text-white
                      rounded-xl border border-neutral-200 bg-white">
      </div>

      <button type="submit" name="submit" class="btn-primary mt-2">
        register now
      </button>

      <p class="text-center text-[#6a584d]">already have an account?
        <a href="login.php" class="underline decoration-[#C89F6D] decoration-2 underline-offset-4 hover:text-[#7B5E42]">login now</a>
      </p>
    </form>
  </div>
</section>
</body>
</html>
