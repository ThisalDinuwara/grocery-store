<?php

@include 'config.php';

session_start();

if(isset($_POST['submit'])){

   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = md5($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   $sql = "SELECT * FROM `users` WHERE email = ? AND password = ?";
   $stmt = $conn->prepare($sql);
   $stmt->execute([$email, $pass]);
   $rowCount = $stmt->rowCount();  

   $row = $stmt->fetch(PDO::FETCH_ASSOC);

   if($rowCount > 0){

      if($row['user_type'] == 'admin'){

         $_SESSION['admin_id'] = $row['id'];
         header('location:admin_page.php');

      }elseif($row['user_type'] == 'user'){

         $_SESSION['user_id'] = $row['id'];
         header('location:home.php');

      }else{
         $message[] = 'no user found!';
      }

   }else{
      $message[] = 'incorrect email or password!';
   }

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8" />
   <meta http-equiv="X-UA-Compatible" content="IE=edge" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <title>login</title>

   <!-- Tailwind for theme -->
   <script src="https://cdn.tailwindcss.com"></script>
   <script>
     tailwind.config = {
       theme: {
         extend: {
           colors: {
             primary: '#8B4513',
             secondary: '#A0522D',
             accent: '#D2B48C',
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
   <!-- Keep your existing CSS (optional) -->
   <link rel="stylesheet" href="css/components.css">

   <style>
     *{box-sizing:border-box}
     body{
       font-family:'Inter',sans-serif;
       background:linear-gradient(135deg,#1B0F0A 0%,#3E2723 50%,#5D4037 100%);
       color:#fff; min-height:100vh; display:flex; align-items:center; justify-content:center;
       padding: 2rem;
     }
     .bg-blobs{
       position:fixed; inset:0; z-index:-1;
       background:
         radial-gradient(circle at 20% 80%, rgba(139,69,19,.35) 0%, transparent 55%),
         radial-gradient(circle at 80% 20%, rgba(210,180,140,.35) 0%, transparent 55%),
         radial-gradient(circle at 40% 40%, rgba(160,82,45,.35) 0%, transparent 55%);
     }
     .glass{background:rgba(255,255,255,.08);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.18)}
     .neon{box-shadow:0 0 20px rgba(139,69,19,.5),0 0 40px rgba(160,82,45,.3),0 0 60px rgba(210,180,140,.2)}
     .headline-grad{background:linear-gradient(45deg,#8B4513,#A0522D,#D2B48C);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
     .input-lite{
       width:100%; padding:.9rem 1rem; border-radius:14px;
       background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.18); color:#fff;
     }
     .input-lite::placeholder{color:#EADDCB}
     .input-lite:focus{outline:none; box-shadow:0 0 0 3px rgba(210,180,140,.35)}
     /* Make PHP flash messages readable on dark bg */
     .message{
       position:fixed; top:20px; left:50%; transform:translateX(-50%);
       background:rgba(139,69,19,.92); color:#fff; border:1px solid rgba(255,255,255,.25);
       padding:.85rem 1rem; border-radius:12px; z-index:50;
     }
     .message i{cursor:pointer; margin-left:.75rem}
     a.link{color:#EADDCB; text-decoration:underline; text-decoration-color:#D2B48C; text-underline-offset:4px}
     a.link:hover{color:#fff}
   </style>
</head>
<body>

<div class="bg-blobs"></div>

<?php
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message neon glass">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<section class="w-full max-w-md mx-auto">
  <div class="glass neon rounded-3xl p-8 md:p-10">
    <!-- Header -->
    <div class="text-center mb-8">
      <h1 class="text-4xl md:text-5xl font-extrabold leading-tight">
        <span class="headline-grad font-gaming">LOGIN</span>
      </h1>
      <div class="h-1 w-24 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] rounded-full mx-auto mt-4"></div>
      <p class="text-[#EADDCB] mt-4">Welcome back! Sign in to continue.</p>
    </div>

    <!-- Form (names unchanged) -->
    <form action="" method="POST" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-[#EADDCB] mb-2">Email</label>
        <input type="email" name="email" class="input-lite" placeholder="enter your email" required>
      </div>

      <div>
        <label class="block text-sm font-medium text-[#EADDCB] mb-2">Password</label>
        <input type="password" name="pass" class="input-lite" placeholder="enter your password" required>
      </div>

      <button type="submit" name="submit"
        class="w-full mt-2 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white py-3.5 rounded-xl font-semibold hover:opacity-95 transition">
        login now
      </button>

      <p class="text-center text-[#EADDCB]">don't have an account?
        <a href="register.php" class="link">register now</a>
      </p>
    </form>
  </div>
</section>

</body>
</html>
