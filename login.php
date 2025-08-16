<?php

@include 'config.php';

session_start();

if (isset($_POST['submit'])) {

  $email = $_POST['email'];
  $email = filter_var($email, FILTER_SANITIZE_STRING);
  $pass = md5($_POST['pass']);
  $pass = filter_var($pass, FILTER_SANITIZE_STRING);

  $sql = "SELECT * FROM `users` WHERE email = ? AND password = ?";
  $stmt = $conn->prepare($sql);
  $stmt->execute([$email, $pass]);
  $rowCount = $stmt->rowCount();

  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($rowCount > 0) {

    if ($row['user_type'] == 'admin') {

      $_SESSION['admin_id'] = $row['id'];
      header('location:admin_page.php');
    } elseif ($row['user_type'] == 'user') {

      $_SESSION['user_id'] = $row['id'];
      header('location:home.php');
    } else {
      $message[] = 'no user found!';
    }
  } else {
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
            inter: ['Inter', 'sans-serif'],
            gaming: ['Orbitron', 'monospace']
          }
        }
      }
    }
  </script>

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
  <link rel="stylesheet" href="css/components.css">

  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #1B0F0A 0%, #3E2723 50%, #5D4037 100%);
      color: #fff;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }

    .bg-blobs {
      position: fixed;
      inset: 0;
      z-index: -1;
      background:
        radial-gradient(circle at 20% 80%, rgba(139, 69, 19, .35) 0%, transparent 55%),
        radial-gradient(circle at 80% 20%, rgba(210, 180, 140, .35) 0%, transparent 55%),
        radial-gradient(circle at 40% 40%, rgba(160, 82, 45, .35) 0%, transparent 55%);
    }

    .glass {
      background: rgba(255, 255, 255, .08);
      backdrop-filter: blur(14px);
      border: 1px solid rgba(255, 255, 255, .18)
    }

    .neon {
      box-shadow: 0 0 25px rgba(139, 69, 19, .55),
        0 0 50px rgba(160, 82, 45, .35),
        0 0 80px rgba(210, 180, 140, .25)
    }

    .headline-grad {
      background: linear-gradient(45deg,
          #f0eae5ff,
          #e4d9d4ff,
          #e7d2bdff
        );
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;

      text-shadow:
        0 2px 4px rgba(0, 0, 0, 0.6),
        0 0 8px rgba(166, 124, 82, 0.4),
        0 0 14px rgba(112, 54, 28, 0.25);
    }


    .input-lite {
      width: 100%;
      padding: 1.2rem 1.4rem;
      font-size: 1.1rem;
      border-radius: 16px;
      background: rgba(255, 255, 255, .08);
      border: 1px solid rgba(255, 255, 255, .18);
      color: #fff;
    }

    .input-lite::placeholder {
      color: #EADDCB;
      font-size: 1rem;
    }

    .input-lite:focus {
      outline: none;
      box-shadow: 0 0 0 4px rgba(210, 180, 140, .4)
    }

    .message {
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      background: rgba(139, 69, 19, .92);
      color: #fff;
      border: 1px solid rgba(255, 255, 255, .25);
      padding: 1rem 1.2rem;
      font-size: 1.05rem;
      border-radius: 14px;
      z-index: 50;
    }

    .message i {
      cursor: pointer;
      margin-left: .75rem
    }

    a.link {
      color: #EADDCB;
      text-decoration: underline;
      text-decoration-color: #D2B48C;
      text-underline-offset: 4px
    }

    a.link:hover {
      color: #fff
    }
  </style>
</head>

<body>

  <div class="bg-blobs"></div>

  <?php
  if (isset($message)) {
    foreach ($message as $message) {
      echo '
      <div class="message neon glass">
         <span>' . $message . '</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
    }
  }
  ?>

  <section class="w-full max-w-2xl mx-auto scale-105 md:scale-110">
    <div class="glass neon rounded-3xl p-10 md:p-14">
      <div class="text-center mb-10">
        <h1 class="text-5xl md:text-6xl font-extrabold leading-tight">
          <span class="headline-grad font-gaming tracking-wide">LOGIN</span>
        </h1>
        <div class="h-1 w-32 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] rounded-full mx-auto mt-6"></div>
        <p class="text-[#EADDCB] mt-6 text-lg md:text-xl">Welcome back! Sign in to continue.</p>
      </div>

      <form action="" method="POST" class="space-y-6">
        <div>
          <label class="block text-base md:text-lg font-medium text-[#EADDCB] mb-3">Email</label>
          <input type="email" name="email" class="input-lite" placeholder="Enter your email" required>
        </div>

        <div>
          <label class="block text-base md:text-lg font-medium text-[#EADDCB] mb-3">Password</label>
          <input type="password" name="pass" class="input-lite" placeholder="Enter your password" required>
        </div>

        <button type="submit" name="submit"
          class="w-full mt-4 text-lg md:text-xl bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white py-4 rounded-xl font-semibold hover:opacity-95 transition">
          Login Now
        </button>

        <p class="text-center text-[#EADDCB] text-lg md:text-xl mt-4">Don't have an account?
          <a href="register.php" class="link">Register Now</a>
        </p>
      </form>
    </div>
  </section>

</body>

</html>