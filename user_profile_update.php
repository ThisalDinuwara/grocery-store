<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
};

if(isset($_POST['update_profile'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);

   $update_profile = $conn->prepare("UPDATE `users` SET name = ?, email = ? WHERE id = ?");
   $update_profile->execute([$name, $email, $user_id]);

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;
   $old_image = $_POST['old_image'];

   if(!empty($image)){
      if($image_size > 2000000){
         $message[] = 'image size is too large!';
      }else{
         $update_image = $conn->prepare("UPDATE `users` SET image = ? WHERE id = ?");
         $update_image->execute([$image, $user_id]);
         if($update_image){
            move_uploaded_file($image_tmp_name, $image_folder);
            unlink('uploaded_img/'.$old_image);
            $message[] = 'image updated successfully!';
         };
      };
   };

   $old_pass = $_POST['old_pass'];
   $update_pass = md5($_POST['update_pass']);
   $update_pass = filter_var($update_pass, FILTER_SANITIZE_STRING);
   $new_pass = md5($_POST['new_pass']);
   $new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING);
   $confirm_pass = md5($_POST['confirm_pass']);
   $confirm_pass = filter_var($confirm_pass, FILTER_SANITIZE_STRING);

   if(!empty($update_pass) AND !empty($new_pass) AND !empty($confirm_pass)){
      if($update_pass != $old_pass){
         $message[] = 'old password not matched!';
      }elseif($new_pass != $confirm_pass){
         $message[] = 'confirm password not matched!';
      }else{
         $update_pass_query = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
         $update_pass_query->execute([$confirm_pass, $user_id]);
         $message[] = 'password updated successfully!';
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
   <title>update user profile</title>

   <!-- Tailwind CDN -->
   <script src="https://cdn.tailwindcss.com"></script>
   <script>
      tailwind.config = {
         theme: {
            extend: {
               colors: {
                  primary: '#8B4513',   // Saddle Brown
                  secondary: '#A0522D', // Sienna
                  accent: '#D2B48C',    // Tan
                  dark: '#3E2723',      // Dark Brown
                  darker: '#1B0F0A'     // Deep Brown
               },
               fontFamily: {
                  gaming: ['Orbitron', 'monospace'],
                  inter: ['Inter', 'sans-serif']
               }
            }
         }
      }
   </script>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Google Fonts to match home.php -->
   <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

   <!-- your site css (kept) -->
   <link rel="stylesheet" href="css/components.css">

   <style>
      *{margin:0;padding:0;box-sizing:border-box}
      body{
         font-family:'Inter',sans-serif;
         background:linear-gradient(135deg,#1B0F0A 0%,#3E2723 50%,#5D4037 100%);
         color:#fff; overflow-x:hidden;
      }
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

      /* card look */
      .card{
         border-radius:22px;
         border:1px solid rgba(210,180,140,.28);
         background:linear-gradient(180deg,rgba(62,39,35,.92),rgba(62,39,35,.84));
      }
      .divider{height:1px;background:linear-gradient(90deg,rgba(139,69,19,.45),rgba(210,180,140,.45))}
      .input-lite{
         background:rgba(255,255,255,.08);
         border:1px solid rgba(255,255,255,.18);
         color:#fff;
      }
      .input-lite::placeholder{color:#cbbfb3}
      .input-lite:focus{outline:none;box-shadow:0 0 0 3px rgba(210,180,140,.35)}
      .file-lite::-webkit-file-upload-button{cursor:pointer}
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<!-- Small Hero Header (matches home.php aesthetic) -->
<section class="relative min-h-[32vh] md:min-h-[40vh] flex items-center justify-center overflow-hidden hero-bg">
  <div class="absolute top-10 left-10 w-72 h-72 bg-gradient-to-r from-[rgba(139,69,19,0.22)] to-[rgba(210,180,140,0.22)] rounded-full blur-3xl"></div>
  <div class="absolute bottom-10 right-10 w-64 h-64 bg-gradient-to-r from-[rgba(160,82,45,0.22)] to-[rgba(139,69,19,0.22)] rounded-full blur-3xl"></div>
  <div class="container mx-auto px-6 lg:px-12 relative z-10 text-center">
     <h1 class="text-5xl lg:text-7xl font-bold leading-tight mb-4">
       <span class="gradient-text font-gaming">ACCOUNT</span> <span class="text-white">SETTINGS</span>
     </h1>
     <div class="h-1 w-28 bg-gradient-to-r from-[#8B4513] to-[#D2B48C] rounded-full mx-auto mb-4"></div>
     <p class="text-lg md:text-xl text-gray-200 max-w-3xl mx-auto">Manage your profile, avatar, and password securely.</p>
  </div>
</section>

<!-- Update Profile (Glass/Neon card like product cards) -->
<section id="profile-update" class="py-16">
  <div class="container mx-auto px-6 lg:px-12">
    <form action="" method="POST" enctype="multipart/form-data" class="max-w-4xl mx-auto">
      <div class="card neon-glow overflow-hidden">
        <!-- top bar -->
        <div class="px-6 py-4 flex items-center justify-between">
          <div class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white px-4 py-2 text-sm font-semibold">
            <i class="fas fa-user-gear"></i> <span>Update Profile</span>
          </div>
          <a href="home.php" class="w-10 h-10 glass-effect rounded-full flex items-center justify-center text-white hover-glow" aria-label="Back">
            <i class="fas fa-arrow-left"></i>
          </a>
        </div>
        <div class="divider"></div>

        <!-- content -->
        <div class="p-8 lg:p-10">
          <div class="grid md:grid-cols-2 gap-8">
            <!-- Avatar + Upload -->
            <div>
              <div class="aspect-square rounded-2xl overflow-hidden border border-white/20 mb-6 glass-effect">
                <img
                  id="avatarPreview"
                  src="uploaded_img/<?= htmlspecialchars($fetch_profile['image'] ?? ''); ?>"
                  alt="Profile picture"
                  class="w-full h-full object-cover"
                  onerror="this.src='uploaded_img/placeholder.png';"
                >
              </div>

              <label class="block text-sm font-medium text-gray-200 mb-2">Update Picture</label>
              <input
                type="file"
                name="image"
                accept="image/jpg, image/jpeg, image/png"
                class="block w-full text-sm text-white file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:bg-gradient-to-r file:from-[#8B4513] file:to-[#D2B48C] file:text-white hover:file:opacity-90 rounded-xl input-lite file-lite"
                onchange="previewAvatar(this)"
              >
              <input type="hidden" name="old_image" value="<?= htmlspecialchars($fetch_profile['image'] ?? ''); ?>">
            </div>

            <!-- Fields -->
            <div>
              <div class="mb-5">
                <label class="block text-sm font-medium text-gray-200 mb-2">Username</label>
                <input
                  type="text"
                  name="name"
                  value="<?= htmlspecialchars($fetch_profile['name'] ?? ''); ?>"
                  placeholder="Update username"
                  required
                  class="w-full px-4 py-3 rounded-xl input-lite"
                >
              </div>

              <div class="mb-5">
                <label class="block text-sm font-medium text-gray-200 mb-2">Email</label>
                <input
                  type="email"
                  name="email"
                  value="<?= htmlspecialchars($fetch_profile['email'] ?? ''); ?>"
                  placeholder="Update email"
                  required
                  class="w-full px-4 py-3 rounded-xl input-lite"
                >
              </div>

              <!-- Passwords (original names kept exactly) -->
              <input type="hidden" name="old_pass" value="<?= htmlspecialchars($fetch_profile['password'] ?? ''); ?>">

              <div class="mb-5">
                <label class="block text-sm font-medium text-gray-200 mb-2">Old Password</label>
                <input
                  type="password"
                  name="update_pass"
                  placeholder="Enter previous password"
                  class="w-full px-4 py-3 rounded-xl input-lite"
                >
              </div>

              <div class="grid md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-200 mb-2">New Password</label>
                  <input
                    type="password"
                    name="new_pass"
                    placeholder="Enter new password"
                    class="w-full px-4 py-3 rounded-xl input-lite"
                  >
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-200 mb-2">Confirm Password</label>
                  <input
                    type="password"
                    name="confirm_pass"
                    placeholder="Confirm new password"
                    class="w-full px-4 py-3 rounded-xl input-lite"
                  >
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-8 grid md:grid-cols-2 gap-4">
            <input
              type="submit"
              class="cursor-pointer w-full bg-gradient-to-r from-[#8B4513] to-[#D2B48C] text-white py-4 rounded-xl font-semibold hover-glow transition"
              value="Update Profile"
              name="update_profile"
            >
            <a href="home.php"
               class="w-full text-center glass-effect text-white py-4 rounded-xl font-semibold hover-glow">
              Go Back
            </a>
          </div>
        </div>
      </div>
    </form>
  </div>
</section>

<script>
  // Live preview for avatar
  function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
    reader.readAsDataURL(input.files[0]);
  }
</script>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
