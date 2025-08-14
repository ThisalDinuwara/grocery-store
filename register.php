<?php

include 'config.php';

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = md5($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = md5($_POST['cpass']);
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);
   $contact = $_POST['contact'];
   $contact = filter_var($contact, FILTER_SANITIZE_STRING);

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;

   $select = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
   $select->execute([$email]);

   if($select->rowCount() > 0){
      $message[] = 'user email already exist!';
   }else{
      if($pass != $cpass){
         $message[] = 'confirm password not matched!';
      }else{
         $insert = $conn->prepare("INSERT INTO `users`(name, email, password, image, contact) VALUES(?,?,?,?,?)");
         $insert->execute([$name, $email, $pass, $image,$contact]);

         if($insert){
            if($image_size > 2000000){
               $message[] = 'image size is too large!';
            }else{
               move_uploaded_file($image_tmp_name, $image_folder);
               $message[] = 'registered successfully!';
               header('location:login.php');
            }
         }

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
   <title>register</title>

   <!-- Tailwind (for theme) -->
   <script src="https://cdn.tailwindcss.com"></script>
   <script>
     tailwind.config = {
       theme: {
         extend: {
           colors: {
             primary: '#8B4513',   // Saddle Brown
             secondary: '#A0522D', // Sienna
             accent: '#D2B48C',    // Tan
             dark: '#3E2723',
             darker: '#1B0F0A'
           },
           fontFamily: {
             inter: ['Inter','sans-serif'],
             gaming: ['Orbitron','monospace']
           }
         }
       }
     }
   </script>

   <!-- Icons -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <!-- Your existing css (kept) -->
   <link rel="stylesheet" href="css/components.css">

   <!-- Theme styles (no PHP changes) -->
   <style>
     *{box-sizing:border-box}
     body{
       font-family:'Inter',sans-serif;
       background:linear-gradient(135deg,#1B0F0A 0%,#3E2723 50%,#5D4037 100%);
       color:#fff; min-height:100vh; display:flex; align-items:center; justify-content:center;
     }
     .hero-bg{
       position:fixed; inset:0; z-index:-1;
       background:
         radial-gradient(circle at 20% 80%, rgba(139,69,19,.35) 0%, transparent 55%),
         radial-gradient(circle at 80% 20%, rgba(210,180,140,.35) 0%, transparent 55%),
         radial-gradient(circle at 40% 40%, rgba(160,82,45,.35) 0%, transparent 55%);
     }
     .glass-effect{background:rgba(255,255,255,.08);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.18)}
     .neon-glow{box-shadow:0 0 20px rgba(139,69,19,.5),0 0 40px rgba(160,82,45,.3),0 0 60px rgba(210,180,140,.2)}
     .gradient-text{background:linear-gradient(45deg,#8B4513,#A0522D,#D2B48C);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
     .input-lite{
       width:100%; padding:.9rem 1rem; border-radius:14px;
       background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.18); color:#fff;
     }
     .input-lite::placeholder{color:#EADDCB}
     .input-lite:focus{outline:none; box-shadow:0 0 0 3px rgba(210,180,140,.35)}
     .pill{display:inline-flex;align-items:center;gap:.5rem;padding:.35rem .75rem;border-radius:9999px}
     /* Make default .message readable on dark bg */
     .message{
       position:fixed; top:20px; left:50%; transform:translateX(-50%);
       background:rgba(139,69,19,.9); color:#fff; border:1px solid rgba(255,255,255,.2);
       padding:.8rem 1rem; border-radius:12px; z-index:50;
     }
     .message i{cursor:pointer; margin-left:.75rem}
   </style>
</head>
<body>

<div class="hero-bg"></div>

<?php
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message neon-glow glass-effect">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<section class="w-full px-6">
  <div class="max-w-xl mx-auto glass-effect neon-glow rounded-3xl p-8 md:p-10">
    <!-- Header -->
    <div class="text-center mb-8">
      <h1 class="text-4xl md:text-5xl font-extrabold leading-tight">
        <span class="gradient-text font-gaming">REGISTER</span>
      </h1>
      <div class="h-1 w-24 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] rounded-full mx-auto mt-4"></div>
      <p class="text-[#EADDCB] mt-4">Create your account to start shopping Sri Lankan handicrafts.</p>
    </div>

    <!-- Form (PHP names unchanged) -->
    <form action="" enctype="multipart/form-data" method="POST" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-[#EADDCB] mb-2">Full Name</label>
        <input type="text" name="name" class="input-lite" placeholder="enter your name" required>
      </div>

      <div>
        <label class="block text-sm font-medium text-[#EADDCB] mb-2">Email</label>
        <input type="email" name="email" class="input-lite" placeholder="enter your email" required>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-[#EADDCB] mb-2">Password</label>
          <input type="password" name="pass" class="input-lite" placeholder="enter your password" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-[#EADDCB] mb-2">Confirm Password</label>
          <input type="password" name="cpass" class="input-lite" placeholder="confirm your password" required>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-[#EADDCB] mb-2">Contact</label>
        <input type="text" name="contact" class="input-lite" placeholder="enter your contact" required>
      </div>

      <div>
        <label class="block text-sm font-medium text-[#EADDCB] mb-2">Profile Image</label>
        <input type="file" name="image" required accept="image/jpg, image/jpeg, image/png"
               class="block w-full text-sm text-white file:mr-4 file:rounded-lg file:border-0 file:px-4 file:py-2
                      file:bg-gradient-to-r file:from-[#8B4513] file:to-[#D2B48C] file:text-white
                      rounded-xl border border-white/20 bg-white/5">
      </div>

      <button type="submit" name="submit"
        class="w-full mt-2 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white py-3.5 rounded-xl font-semibold hover:opacity-95 transition">
        register now
      </button>

      <p class="text-center text-[#EADDCB]">already have an account?
        <a href="login.php" class="underline decoration-[#D2B48C] decoration-2 underline-offset-4 hover:text-white">
          login now
        </a>
      </p>
    </form>
  </div>
</section>

</body>
</html>
