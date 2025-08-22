<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
   header('location:login.php');
   exit;
}

/* always have an array so foreach in header/footer is safe */
$message = [];

if (isset($_POST['send'])) {
   // ---- sanitization (no deprecated filters) ----
   $name   = trim($_POST['name']   ?? '');
   $email  = trim($_POST['email']  ?? '');
   $number = preg_replace('/\D+/', '', $_POST['number'] ?? ''); // keep digits only
   $msg    = trim($_POST['msg']    ?? '');

   // strip tags but keep plain text
   $name = strip_tags($name);
   $msg  = strip_tags($msg);

   // ---- validation ----
   $errors = [];
   if ($name === '' || mb_strlen($name) > 120)  { $errors[] = 'Please enter a valid name.'; }
   if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) { $errors[] = 'Please enter a valid email.'; }
   if ($number === '' || mb_strlen($number) < 6 || mb_strlen($number) > 20) { $errors[] = 'Please enter a valid phone number.'; }
   if ($msg === '' || mb_strlen($msg) > 2000) { $errors[] = 'Please enter a valid message.'; }

   if (empty($errors)) {
      // check duplicates (same user + same content recently)
      $select = $conn->prepare(
         "SELECT id FROM `message`
          WHERE user_id = ? AND name = ? AND email = ? AND number = ? AND message = ?
          ORDER BY id DESC LIMIT 1"
      );
      $select->execute([$user_id, $name, $email, $number, $msg]);

      if ($select->rowCount() > 0) {
         $message[] = 'already sent message!';
      } else {
         $insert = $conn->prepare(
            "INSERT INTO `message`(user_id, name, email, number, message)
             VALUES(?,?,?,?,?)"
         );
         $insert->execute([$user_id, $name, $email, $number, $msg]);
         $message[] = 'sent message successfully!';
      }
   } else {
      // show all validation problems in one toast
      $message[] = implode(' ', $errors);
   }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Contact - Kandu Pinnawala</title>

   <!-- Tailwind -->
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
            fontFamily: { gaming: ['Orbitron','monospace'], inter: ['Inter','sans-serif'] }
          }
        }
      }
   </script>

   <!-- Icons + fonts + your css -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="css/style.css">

   <style>
      *{margin:0;padding:0;box-sizing:border-box}
      /* ===== Light Theme Base ===== */
      body{
        font-family:'Inter',sans-serif;
        background: linear-gradient(135deg,#FFFDF9 0%, #F7F3ED 50%, #EFE8DE 100%);
        color:#2E1B0E; /* ink */
        overflow-x:hidden;
      }
      .hero-bg{
        background:
          radial-gradient(circle at 20% 80%, rgba(183,123,61,.18) 0%, transparent 55%),
          radial-gradient(circle at 80% 20%, rgba(212,163,115,.18) 0%, transparent 55%),
          radial-gradient(circle at 40% 40%, rgba(140,98,57,.18) 0%, transparent 55%);
      }
      .glass-effect{
        background: rgba(255,255,255,.92);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(183,123,61,.22);
      }
      .hover-elevate:hover{transform:translateY(-4px);box-shadow:0 12px 24px rgba(183,123,61,.18);transition:.3s ease}
      .gradient-text{
        background:linear-gradient(45deg,#B77B3D,#D4A373);
        -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text
      }
      .divider{height:1px;background:linear-gradient(90deg,rgba(183,123,61,.35),rgba(212,163,115,.35))}
      .input-lite{
        background:#fff;
        border:1px solid rgba(183,123,61,.26);
        color:#2E1B0E;
      }
      .input-lite::placeholder{color:#7F6346}
      .input-lite:focus{outline:none;box-shadow:0 0 0 3px rgba(183,123,61,.25)}
      .muted{color:#6B4E2E}
      .muted-2{color:#8A6A49}
      .chip{
        display:inline-flex;align-items:center;gap:.5rem;border-radius:9999px;
        padding:.5rem .9rem;border:1px solid rgba(183,123,61,.22); background:#fff; color:#6B4E2E;
      }
      .pill{
        display:inline-flex;align-items:center;gap:.5rem;border-radius:9999px;
        padding:.45rem .85rem;border:1px solid rgba(183,123,61,.22); background:#fff; color:#6B4E2E; font-weight:700; font-size:.8rem
      }
      .btn-primary{
        background:linear-gradient(135deg,#B77B3D,#D4A373);
        color:#fff; font-weight:700; border:none;
        border-radius:14px; padding:1rem 1rem; transition:.25s;
        box-shadow:0 12px 28px rgba(183,123,61,.22)
      }
      .btn-primary:hover{transform:translateY(-2px)}
      .icon-accent{ color:#8C6239 }
   </style>
</head>
<body>

<?php include 'header.php'; ?>

<!-- Hero Header -->
<section class="relative min-h-[32vh] md:min-h-[40vh] flex items-center justify-center overflow-hidden hero-bg">
  <div class="absolute top-10 left-10 w-72 h-72 bg-gradient-to-r from-[rgba(183,123,61,0.18)] to-[rgba(212,163,115,0.18)] rounded-full blur-3xl"></div>
  <div class="absolute bottom-10 right-10 w-64 h-64 bg-gradient-to-r from-[rgba(212,163,115,0.18)] to-[rgba(140,98,57,0.18)] rounded-full blur-3xl"></div>
  <div class="container mx-auto px-6 lg:px-12 relative z-10 text-center">
     <h1 class="text-5xl lg:text-7xl font-bold leading-tight mb-4">
       <span class="gradient-text font-gaming">CONTACT</span> <span class="">US</span>
     </h1>
     <div class="h-1 w-28 bg-gradient-to-r from-[#B77B3D] to-[#D4A373] rounded-full mx-auto mb-4"></div>
     <p class="text-lg md:text-xl muted max-w-3xl mx-auto">We’d love to hear from you. Send us a message and we’ll reply soon.</p>
  </div>
</section>

<!-- Contact Section -->
<section id="contact" class="py-16 relative">
  <div class="container mx-auto px-6 lg:px-12">
    <div class="grid md:grid-cols-2 gap-8">
      <!-- LEFT: Info Card -->
      <div class="glass-effect rounded-3xl overflow-hidden hover-elevate">
        <div class="px-6 py-4 flex justify-center">
          <span class="pill">
            <i class="fa-regular fa-bolt icon-accent"></i>
            We reply within 24h
          </span>
        </div>
        <div class="divider"></div>

        <div class="p-6 lg:p-8">
          <h3 class="text-xl font-extrabold mb-6 text-center" style="color:#3D2B1F">Contact Information</h3>

          <ul class="space-y-4">
            <li class="flex items-center gap-3">
              <span class="w-10 h-10 rounded-xl glass-effect flex items-center justify-center">
                <i class="fas fa-map-marker-alt icon-accent"></i>
              </span>
              <span class="muted">Kandu Pinnawala, Sri Lanka</span>
            </li>
            <li class="flex items-center gap-3">
              <span class="w-10 h-10 rounded-xl glass-effect flex items-center justify-center">
                <i class="fas fa-envelope icon-accent"></i>
              </span>
              <span class="muted">support@kandupinnawala.lk</span>
            </li>
            <li class="flex items-center gap-3">
              <span class="w-10 h-10 rounded-xl glass-effect flex items-center justify-center">
                <i class="fas fa-phone icon-accent"></i>
              </span>
              <span class="muted">+94 77 123 4567</span>
            </li>
            <li class="flex items-center gap-3">
              <span class="w-10 h-10 rounded-xl glass-effect flex items-center justify-center">
                <i class="fas fa-clock icon-accent"></i>
              </span>
              <span class="muted">Mon–Sat, 9:00–18:00</span>
            </li>
          </ul>

          <div class="mt-8 grid grid-cols-3 gap-3">
            <a href="tel:+94771234567" class="text-center rounded-xl glass-effect py-3 font-semibold hover-elevate">Call</a>
            <a href="https://wa.me/94771234567" target="_blank" class="text-center rounded-xl glass-effect py-3 font-semibold hover-elevate">WhatsApp</a>
            <a href="mailto:support@kandupinnawala.lk" class="text-center rounded-xl glass-effect py-3 font-semibold hover-elevate">Email</a>
          </div>

          <div class="mt-6 flex items-center gap-3 justify-center">
            <a href="#" class="w-10 h-10 rounded-full glass-effect flex items-center justify-center hover-elevate" aria-label="Facebook">
              <i class="fab fa-facebook-f icon-accent"></i>
            </a>
            <a href="#" class="w-10 h-10 rounded-full glass-effect flex items-center justify-center hover-elevate" aria-label="Instagram">
              <i class="fab fa-instagram icon-accent"></i>
            </a>
            <a href="#" class="w-10 h-10 rounded-full glass-effect flex items-center justify-center hover-elevate" aria-label="Twitter">
              <i class="fab fa-twitter icon-accent"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- RIGHT: Form Card -->
      <div class="glass-effect rounded-3xl overflow-hidden hover-elevate">
        <div class="px-6 py-4 flex justify-center">
          <span class="pill">
            <i class="fa-regular fa-paper-plane icon-accent"></i>
            Send Message
          </span>
        </div>
        <div class="divider"></div>

        <form action="" method="POST" class="p-6 lg:p-8">
          <div class="relative mb-5">
            <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 icon-accent pointer-events-none"></i>
            <input type="text" name="name" required placeholder="Your Name" class="w-full pl-11 pr-4 py-3 rounded-xl input-lite" />
          </div>

          <div class="grid sm:grid-cols-2 gap-5">
            <div class="relative">
              <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 icon-accent pointer-events-none"></i>
              <input type="email" name="email" required placeholder="Email" class="w-full pl-11 pr-4 py-3 rounded-xl input-lite" />
            </div>

            <div class="relative">
              <i class="fas fa-phone absolute left-4 top-1/2 -translate-y-1/2 icon-accent pointer-events-none"></i>
              <input type="number" name="number" min="0" required placeholder="Phone Number" class="w-full pl-11 pr-4 py-3 rounded-xl input-lite" />
            </div>
          </div>

          <div class="relative mt-5">
            <i class="fas fa-comment-dots absolute left-4 top-4 icon-accent pointer-events-none"></i>
            <textarea name="msg" rows="6" required placeholder="Message" class="w-full pl-11 pr-4 py-3 rounded-xl input-lite resize-none"></textarea>
          </div>

          <button type="submit" name="send" class="mt-6 w-full btn-primary">
            <i class="fas fa-paper-plane mr-2"></i> Send Message
          </button>

          <p class="mt-3 text-sm muted-2">By sending, you agree to our privacy policy.</p>
        </form>
      </div>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
