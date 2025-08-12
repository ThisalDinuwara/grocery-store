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
      <!-- Tailwind CDN (optional for live preview) -->
   <script src="https://cdn.tailwindcss.com"></script>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/components.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<!-- Update Profile (Glass UI to match product cards) -->
<section id="profile-update" class="py-20 bg-gray-50">
  <div class="container mx-auto px-6 lg:px-12">
    <div class="text-center mb-16">
      <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Update Profile</h2>
      <p class="text-xl text-gray-600 max-w-3xl mx-auto">Manage your account details and avatar</p>
    </div>

    <form action="" method="POST" enctype="multipart/form-data" class="group max-w-4xl mx-auto">
      <div class="relative rounded-3xl overflow-hidden shadow-lg border border-white/40 bg-white/30 backdrop-blur-xl transition duration-300 hover:shadow-2xl">
        <!-- Decorative gradient strip (subtle, like cards) -->
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-orange-500 to-red-600"></div>

        <!-- Optional back button (matches grid style) -->
        <a href="home.php"
           class="absolute top-4 right-4 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-gray-700 hover:bg-orange-500 hover:text-white transition-all duration-300 z-10"
           aria-label="Back">
          <i class="fas fa-arrow-left"></i>
        </a>

        <!-- Content -->
        <div class="p-8 lg:p-10">
          <div class="grid md:grid-cols-2 gap-8">
            <!-- Avatar + Upload -->
            <div>
              <div class="aspect-square bg-gray-50/60 rounded-2xl overflow-hidden border border-white/50 mb-6">
                <img
                  id="avatarPreview"
                  src="uploaded_img/<?= htmlspecialchars($fetch_profile['image'] ?? ''); ?>"
                  alt="Profile picture"
                  class="w-full h-full object-cover"
                  onerror="this.src='uploaded_img/placeholder.png';"
                >
              </div>

              <label class="block text-sm font-medium text-gray-700 mb-2">Update Picture</label>
              <input
                type="file"
                name="image"
                accept="image/jpg, image/jpeg, image/png"
                class="block w-full text-sm text-gray-700 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:bg-gradient-to-r file:from-orange-500 file:to-red-600 file:text-white hover:file:opacity-90 rounded-xl border border-gray-200 bg-white/60 backdrop-blur placeholder:text-gray-400"
                onchange="previewAvatar(this)"
              >
              <input type="hidden" name="old_image" value="<?= htmlspecialchars($fetch_profile['image'] ?? ''); ?>">
            </div>

            <!-- Fields -->
            <div>
              <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                <input
                  type="text"
                  name="name"
                  value="<?= htmlspecialchars($fetch_profile['name'] ?? ''); ?>"
                  placeholder="Update username"
                  required
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/60 backdrop-blur focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none"
                >
              </div>

              <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input
                  type="email"
                  name="email"
                  value="<?= htmlspecialchars($fetch_profile['email'] ?? ''); ?>"
                  placeholder="Update email"
                  required
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/60 backdrop-blur focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none"
                >
              </div>

              <!-- Passwords (keep original names) -->
              <input type="hidden" name="old_pass" value="<?= htmlspecialchars($fetch_profile['password'] ?? ''); ?>">

              <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">Old Password</label>
                <input
                  type="password"
                  name="update_pass"
                  placeholder="Enter previous password"
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/60 backdrop-blur focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none"
                >
              </div>

              <div class="grid md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                  <input
                    type="password"
                    name="new_pass"
                    placeholder="Enter new password"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/60 backdrop-blur focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none"
                  >
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                  <input
                    type="password"
                    name="confirm_pass"
                    placeholder="Confirm new password"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/60 backdrop-blur focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none"
                  >
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-8 grid md:grid-cols-2 gap-4">
            <input
              type="submit"
              class="cursor-pointer w-full bg-gradient-to-r from-orange-500 to-red-600 text-white py-4 rounded-xl font-semibold hover:shadow-xl hover:shadow-orange-500/25 transition-all duration-300 transform hover:scale-[1.02]"
              value="Update Profile"
              name="update_profile"
            >
            <a href="home.php"
               class="w-full text-center bg-white/70 text-gray-700 border border-gray-200 py-4 rounded-xl font-semibold hover:bg-white transition-all">
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