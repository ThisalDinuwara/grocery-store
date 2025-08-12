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

   
      <!-- Tailwind CDN (optional for live preview) -->
   <script src="https://cdn.tailwindcss.com"></script>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<!-- Contact Section (Modern, centered header bars, no overlap) -->
<section id="contact" class="py-20 bg-gray-50 relative">
  <!-- soft decorative blobs -->
  <div class="pointer-events-none absolute inset-0 -z-10">
    <div class="absolute -top-24 -left-20 w-72 h-72 rounded-full bg-orange-200/40 blur-3xl"></div>
    <div class="absolute -bottom-24 -right-20 w-72 h-72 rounded-full bg-red-200/40 blur-3xl"></div>
  </div>

  <div class="container mx-auto px-6 lg:px-12">
    <!-- Section header -->
    <div class="text-center mb-16">
      <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Get in Touch</h2>
      <p class="text-xl text-gray-600 max-w-3xl mx-auto">
        We’d love to hear from you. Send us a message and we’ll reply soon.
      </p>
    </div>

    <div class="grid md:grid-cols-2 gap-8">
      <!-- Info Card -->
      <div class="rounded-3xl overflow-hidden shadow-lg border border-gray-100 bg-white">
        <!-- gradient top bar with centered pill -->
        <div class="bg-gradient-to-r from-orange-500 to-red-600 p-2 flex justify-center">
          <span class="px-3 py-1 rounded-full bg-white text-sm font-bold text-gray-800">
            We reply within 24h
          </span>
        </div>

        <div class="p-6 lg:p-8">
          <h3 class="text-xl font-bold text-gray-900 mb-6 text-center">Contact Information</h3>

          <ul class="space-y-4 text-gray-700">
            <li class="flex items-center gap-3">
              <span class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center">
                <i class="fas fa-map-marker-alt text-orange-600"></i>
              </span>
              Kandu Pinnawala, Sri Lanka
            </li>
            <li class="flex items-center gap-3">
              <span class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center">
                <i class="fas fa-envelope text-orange-600"></i>
              </span>
              support@kandupinnawala.lk
            </li>
            <li class="flex items-center gap-3">
              <span class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center">
                <i class="fas fa-phone text-orange-600"></i>
              </span>
              +94 77 123 4567
            </li>
            <li class="flex items-center gap-3">
              <span class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center">
                <i class="fas fa-clock text-orange-600"></i>
              </span>
              Mon–Sat, 9:00–18:00
            </li>
          </ul>

          <div class="mt-8 grid grid-cols-3 gap-3">
            <a href="tel:+94771234567" class="text-center rounded-xl border border-gray-200 bg-white py-3 font-medium hover:bg-gray-50 transition">Call</a>
            <a href="https://wa.me/94771234567" target="_blank" class="text-center rounded-xl border border-gray-200 bg-white py-3 font-medium hover:bg-gray-50 transition">WhatsApp</a>
            <a href="mailto:support@kandupinnawala.lk" class="text-center rounded-xl border border-gray-200 bg-white py-3 font-medium hover:bg-gray-50 transition">Email</a>
          </div>

          <div class="mt-6 flex items-center gap-3 text-gray-600 justify-center">
            <a href="#" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition" aria-label="Facebook">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition" aria-label="Instagram">
              <i class="fab fa-instagram"></i>
            </a>
            <a href="#" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition" aria-label="Twitter">
              <i class="fab fa-twitter"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- Form Card -->
      <div class="rounded-3xl overflow-hidden shadow-lg border border-gray-100 bg-white">
        <!-- gradient top bar with centered pill -->
        <div class="bg-gradient-to-r from-orange-500 to-red-600 p-2 flex justify-center">
          <span class="px-3 py-1 rounded-full bg-white text-sm font-bold text-gray-800">
            Send Message
          </span>
        </div>

        <form action="" method="POST" class="p-6 lg:p-8">
          <!-- Name -->
          <div class="relative mb-5">
            <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
            <input
              type="text" name="name" required placeholder="Your Name"
              class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 bg-white outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
            />
          </div>

          <!-- Email & Phone -->
          <div class="grid sm:grid-cols-2 gap-5">
            <div class="relative">
              <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
              <input
                type="email" name="email" required placeholder="Email"
                class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 bg-white outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
              />
            </div>

            <div class="relative">
              <i class="fas fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
              <input
                type="number" name="number" min="0" required placeholder="Phone Number"
                class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 bg-white outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
              />
            </div>
          </div>

          <!-- Message -->
          <div class="relative mt-5">
            <i class="fas fa-comment-dots absolute left-4 top-4 text-gray-400 pointer-events-none"></i>
            <textarea
              name="msg" rows="6" required placeholder="Message"
              class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 bg-white outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none"
            ></textarea>
          </div>

          <!-- Submit -->
          <button type="submit" name="send"
                  class="mt-6 w-full bg-gradient-to-r from-orange-500 to-red-600 text-white py-4 rounded-xl font-semibold
                         hover:shadow-xl hover:shadow-orange-500/25 transition duration-300 transform hover:scale-[1.02]">
            <i class="fas fa-paper-plane mr-2"></i>
            Send Message
          </button>

          <p class="mt-3 text-sm text-gray-500">By sending, you agree to our privacy policy.</p>
        </form>
      </div>
    </div>
  </div>
</section>









<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>