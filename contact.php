<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
};

if(isset($_POST['send'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $msg = $_POST['msg'];
   $msg = filter_var($msg, FILTER_SANITIZE_STRING);

   $select_message = $conn->prepare("SELECT * FROM `message` WHERE name = ? AND email = ? AND number = ? AND message = ?");
   $select_message->execute([$name, $email, $number, $msg]);

   if($select_message->rowCount() > 0){
      $message[] = 'already sent message!';
   }else{
      $insert_message = $conn->prepare("INSERT INTO `message`(user_id, name, email, number, message) VALUES(?,?,?,?,?)");
      $insert_message->execute([$user_id, $name, $email, $number, $msg]);
      $message[] = 'sent message successfully!';
   }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>contact</title>

   <!-- Tailwind -->
   <script src="https://cdn.tailwindcss.com"></script>
   <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              primary: '#8B4513', secondary: '#A0522D', accent: '#D2B48C', dark: '#3E2723', darker: '#1B0F0A'
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
      body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#1B0F0A 0%,#3E2723 50%,#5D4037 100%);color:#fff;overflow-x:hidden}
      .hero-bg{
        background:
          radial-gradient(circle at 20% 80%, rgba(139,69,19,.35) 0%, transparent 55%),
          radial-gradient(circle at 80% 20%, rgba(210,180,140,.35) 0%, transparent 55%),
          radial-gradient(circle at 40% 40%, rgba(160,82,45,.35) 0%, transparent 55%);
      }
      .neon-glow{box-shadow:0 0 20px rgba(139,69,19,.5),0 0 40px rgba(160,82,45,.3),0 0 60px rgba(210,180,140,.2)}
      .glass-effect{background:rgba(255,255,255,.08);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.18)}
      .hover-glow:hover{transform:translateY(-5px);box-shadow:0 10px 25px rgba(139,69,19,.35);transition:.3s ease}
      .gradient-text{background:linear-gradient(45deg,#8B4513,#A0522D,#D2B48C);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
      .divider{height:1px;background:linear-gradient(90deg,rgba(139,69,19,.45),rgba(210,180,140,.45))}
      .input-lite{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);color:#fff}
      .input-lite::placeholder{color:#cbbfb3}
      .input-lite:focus{outline:none;box-shadow:0 0 0 3px rgba(210,180,140,.35)}

      /* ---- FIX: force readable text inside the left card ---- */
      .force-white, .force-white *{color:#ffffff !important}
      .force-muted{color:#EADDCB !important}
      .force-icon{color:#FFFFFF !important}
      .force-link a{color:#ffffff !important}
   </style>
</head>
<body>

<?php include 'header.php'; ?>

<!-- Hero Header -->
<section class="relative min-h-[32vh] md:min-h-[40vh] flex items-center justify-center overflow-hidden hero-bg">
  <div class="absolute top-10 left-10 w-72 h-72 bg-gradient-to-r from-[rgba(139,69,19,0.22)] to-[rgba(210,180,140,0.22)] rounded-full blur-3xl"></div>
  <div class="absolute bottom-10 right-10 w-64 h-64 bg-gradient-to-r from-[rgba(160,82,45,0.22)] to-[rgba(139,69,19,0.22)] rounded-full blur-3xl"></div>
  <div class="container mx-auto px-6 lg:px-12 relative z-10 text-center">
     <h1 class="text-5xl lg:text-7xl font-bold leading-tight mb-4">
       <span class="gradient-text font-gaming">CONTACT</span> <span class="text-white">US</span>
     </h1>
     <div class="h-1 w-28 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] rounded-full mx-auto mb-4"></div>
     <p class="text-lg md:text-xl text-gray-200 max-w-3xl mx-auto">We’d love to hear from you. Send us a message and we’ll reply soon.</p>
  </div>
</section>

<!-- Contact Section -->
<section id="contact" class="py-16 relative">
  <div class="container mx-auto px-6 lg:px-12">
    <div class="grid md:grid-cols-2 gap-8">
      <!-- LEFT: Info Card -->
      <div class="glass-effect neon-glow rounded-3xl overflow-hidden force-white force-link">
        <div class="px-6 py-4 flex justify-center">
          <span class="px-3 py-1 rounded-full bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white text-sm font-bold">
            We reply within 24h
          </span>
        </div>
        <div class="divider"></div>

        <div class="p-6 lg:p-8">
          <h3 class="text-xl font-bold mb-6 text-center">Contact Information</h3>

          <ul class="space-y-4">
            <li class="flex items-center gap-3">
              <span class="w-10 h-10 rounded-xl glass-effect flex items-center justify-center">
                <i class="fas fa-map-marker-alt force-icon"></i>
              </span>
              <span class="force-muted">Kandu Pinnawala, Sri Lanka</span>
            </li>
            <li class="flex items-center gap-3">
              <span class="w-10 h-10 rounded-xl glass-effect flex items-center justify-center">
                <i class="fas fa-envelope force-icon"></i>
              </span>
              <span class="force-muted">support@kandupinnawala.lk</span>
            </li>
            <li class="flex items-center gap-3">
              <span class="w-10 h-10 rounded-xl glass-effect flex items-center justify-center">
                <i class="fas fa-phone force-icon"></i>
              </span>
              <span class="force-muted">+94 77 123 4567</span>
            </li>
            <li class="flex items-center gap-3">
              <span class="w-10 h-10 rounded-xl glass-effect flex items-center justify-center">
                <i class="fas fa-clock force-icon"></i>
              </span>
              <span class="force-muted">Mon–Sat, 9:00–18:00</span>
            </li>
          </ul>

          <div class="mt-8 grid grid-cols-3 gap-3">
            <a href="tel:+94771234567" class="text-center rounded-xl glass-effect py-3 font-medium hover-glow">Call</a>
            <a href="https://wa.me/94771234567" target="_blank" class="text-center rounded-xl glass-effect py-3 font-medium hover-glow">WhatsApp</a>
            <a href="mailto:support@kandupinnawala.lk" class="text-center rounded-xl glass-effect py-3 font-medium hover-glow">Email</a>
          </div>

          <div class="mt-6 flex items-center gap-3 justify-center">
            <a href="#" class="w-10 h-10 rounded-full glass-effect flex items-center justify-center hover-glow" aria-label="Facebook">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="w-10 h-10 rounded-full glass-effect flex items-center justify-center hover-glow" aria-label="Instagram">
              <i class="fab fa-instagram"></i>
            </a>
            <a href="#" class="w-10 h-10 rounded-full glass-effect flex items-center justify-center hover-glow" aria-label="Twitter">
              <i class="fab fa-twitter"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- RIGHT: Form Card -->
      <div class="glass-effect neon-glow rounded-3xl overflow-hidden">
        <div class="px-6 py-4 flex justify-center">
          <span class="px-3 py-1 rounded-full bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white text-sm font-bold">
            Send Message
          </span>
        </div>
        <div class="divider"></div>

        <form action="" method="POST" class="p-6 lg:p-8">
          <div class="relative mb-5">
            <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none"></i>
            <input type="text" name="name" required placeholder="Your Name" class="w-full pl-11 pr-4 py-3 rounded-xl input-lite" />
          </div>

          <div class="grid sm:grid-cols-2 gap-5">
            <div class="relative">
              <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none"></i>
              <input type="email" name="email" required placeholder="Email" class="w-full pl-11 pr-4 py-3 rounded-xl input-lite" />
            </div>

            <div class="relative">
              <i class="fas fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none"></i>
              <input type="number" name="number" min="0" required placeholder="Phone Number" class="w-full pl-11 pr-4 py-3 rounded-xl input-lite" />
            </div>
          </div>

          <div class="relative mt-5">
            <i class="fas fa-comment-dots absolute left-4 top-4 text-gray-300 pointer-events-none"></i>
            <textarea name="msg" rows="6" required placeholder="Message" class="w-full pl-11 pr-4 py-3 rounded-xl input-lite resize-none"></textarea>
          </div>

          <button type="submit" name="send" class="mt-6 w-full bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white py-4 rounded-xl font-semibold hover-glow transition">
            <i class="fas fa-paper-plane mr-2"></i> Send Message
          </button>

          <p class="mt-3 text-sm text-gray-200">By sending, you agree to our privacy policy.</p>
        </form>
      </div>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
