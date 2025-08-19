<?php
@include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'] ?? null;
if (!isset($admin_id)) {
  header('location:login.php');
  exit;
}

/* ---------------------------------
   Alerts bucket (avoid $message)
---------------------------------- */
$alerts = [];
if (isset($message)) { // normalize if an include sets $message
  if (is_array($message)) $alerts = array_merge($alerts, $message);
  elseif (is_string($message) && $message !== '') $alerts[] = $message;
  unset($message);
}

/* Load current profile for display & hidden fields */
$select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$select_profile->execute([$admin_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
if (!$fetch_profile) {
  $_SESSION['flash_msg'] = 'Profile not found.';
  header('location:admin_page.php');
  exit;
}

/* ===============================
   UPDATE PROFILE
================================= */
if (isset($_POST['update_profile'])) {

  // Basic fields (no FILTER_SANITIZE_STRING in PHP8)
  $name  = isset($_POST['name'])  ? trim($_POST['name'])  : '';
  $email = isset($_POST['email']) ? trim($_POST['email']) : '';

  $email_valid = filter_var($email, FILTER_VALIDATE_EMAIL);

  if ($name === '' || !$email_valid) {
    $alerts[] = 'Name and a valid email are required.';
  } else {
    // Ensure email uniqueness (excluding me)
    $dup = $conn->prepare("SELECT 1 FROM `users` WHERE email = ? AND id <> ? LIMIT 1");
    $dup->execute([$email, $admin_id]);
    if ($dup->rowCount() > 0) {
      $alerts[] = 'That email is already in use.';
    } else {
      $update_profile = $conn->prepare("UPDATE `users` SET name = ?, email = ? WHERE id = ?");
      if ($update_profile->execute([$name, $email, $admin_id])) {
        $alerts[] = 'Profile details updated!';
        $fetch_profile['name']  = $name;
        $fetch_profile['email'] = $email;
      } else {
        $alerts[] = 'Failed to update basic details.';
      }
    }
  }

  // Image upload (optional)
  if (!empty($_FILES['image']['name'])) {
    $img = $_FILES['image'];
    if (is_uploaded_file($img['tmp_name'])) {
      $ext = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));
      $allowed = ['jpg','jpeg','png','gif','webp'];
      if (!in_array($ext, $allowed, true)) {
        $alerts[] = 'Invalid image type. Allowed: jpg, jpeg, png, gif, webp.';
      } elseif ($img['size'] > 2 * 1024 * 1024) {
        $alerts[] = 'Image size is too large! (max 2MB)';
      } else {
        $newName = 'avatar_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $target  = __DIR__ . '/uploaded_img/' . $newName;
        if (@move_uploaded_file($img['tmp_name'], $target)) {
          $upd = $conn->prepare("UPDATE `users` SET image = ? WHERE id = ?");
          if ($upd->execute([$newName, $admin_id])) {
            // delete old file
            $old = $fetch_profile['image'] ?? '';
            if ($old) {
              $oldPath = __DIR__ . '/uploaded_img/' . $old;
              if (is_file($oldPath)) @unlink($oldPath);
            }
            $fetch_profile['image'] = $newName;
            $alerts[] = 'Profile photo updated!';
          } else {
            @unlink($target);
            $alerts[] = 'Failed to save photo in database.';
          }
        } else {
          $alerts[] = 'Failed to upload photo.';
        }
      }
    } else {
      $alerts[] = 'Invalid image upload.';
    }
  }

  // Password change (optional) — keeps legacy MD5 to avoid breaking existing login
  $curr_pass_raw    = $_POST['update_pass']  ?? '';
  $new_pass_raw     = $_POST['new_pass']     ?? '';
  $confirm_pass_raw = $_POST['confirm_pass'] ?? '';

  if ($curr_pass_raw !== '' || $new_pass_raw !== '' || $confirm_pass_raw !== '') {
    $stored_hash = $fetch_profile['password'] ?? ''; // MD5 in current schema
    $curr_hash   = md5($curr_pass_raw);

    if ($curr_hash !== $stored_hash) {
      $alerts[] = 'Old password not matched!';
    } elseif ($new_pass_raw === '') {
      $alerts[] = 'New password cannot be empty.';
    } elseif (md5($new_pass_raw) !== md5($confirm_pass_raw)) {
      $alerts[] = 'Confirm password not matched!';
    } else {
      $new_hash = md5($new_pass_raw); // NOTE: insecure; migrate to password_hash later
      $q = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
      if ($q->execute([$new_hash, $admin_id])) {
        $alerts[] = 'Password updated successfully!';
        $fetch_profile['password'] = $new_hash;
      } else {
        $alerts[] = 'Failed to update password.';
      }
    }
  }

  // Refresh profile from DB (in case other fields changed elsewhere)
  $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
  $select_profile->execute([$admin_id]);
  $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC) ?: $fetch_profile;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin • Update Profile</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

  <style>
    .line-clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
  </style>
</head>
<body class="bg-gray-50">
<?php include 'admin_header.php'; ?>

<div class="ml-64 pt-16 min-h-screen">
  <div class="p-6">

    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-6 text-white mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold">Update Profile</h1>
          <p class="text-blue-100"><?= htmlspecialchars(date('l, F j, Y')); ?></p>
        </div>
        <a href="admin_page.php" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded transition">
          <i class="fas fa-home"></i> Dashboard
        </a>
      </div>
    </div>

    <!-- Alerts -->
    <?php if (!empty($alerts)): ?>
      <div class="mb-6 space-y-2">
        <?php foreach ($alerts as $msg): ?>
          <div class="flex items-center gap-2 bg-blue-50 text-blue-800 border border-blue-200 rounded px-4 py-2">
            <i class="fas fa-info-circle"></i>
            <span><?= htmlspecialchars($msg) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Profile Overview + Form -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Profile Summary -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center gap-4">
            <div class="w-20 h-20 rounded-full bg-gray-100 overflow-hidden flex items-center justify-center">
              <?php
                $img = !empty($fetch_profile['image']) ? 'uploaded_img/'.$fetch_profile['image'] : '';
                if ($img && is_file(__DIR__.'/'.$img)):
              ?>
                <img src="<?= htmlspecialchars($img) ?>" alt="" class="w-full h-full object-cover">
              <?php else: ?>
                <i class="fas fa-user text-3xl text-gray-400"></i>
              <?php endif; ?>
            </div>
            <div>
              <h3 class="text-lg font-semibold"><?= htmlspecialchars($fetch_profile['name'] ?? 'Admin'); ?></h3>
              <p class="text-gray-600 text-sm"><?= htmlspecialchars($fetch_profile['email'] ?? ''); ?></p>
              <span class="inline-flex items-center gap-1 mt-2 text-xs px-2 py-1 rounded bg-indigo-100 text-indigo-700">
                <i class="fas fa-user-shield"></i> <?= htmlspecialchars($fetch_profile['user_type'] ?? 'admin'); ?>
              </span>
            </div>
          </div>
          <div class="mt-6 border-t pt-4 text-sm text-gray-600">
            <p><i class="fas fa-id-badge mr-2 text-gray-400"></i> User ID:
              <span class="font-medium"><?= (int)($fetch_profile['id'] ?? 0); ?></span>
            </p>
          </div>
        </div>
      </div>

      <!-- Edit Form -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold mb-4">Edit Profile</h3>

          <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm text-gray-600 mb-1">Username</label>
                <input type="text" name="name" value="<?= htmlspecialchars($fetch_profile['name'] ?? ''); ?>" required
                       class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
              <div>
                <label class="block text-sm text-gray-600 mb-1">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($fetch_profile['email'] ?? ''); ?>" required
                       class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm text-gray-600 mb-2">Update Profile Picture</label>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.gif,.webp"
                       class="w-full border rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-2">Max 2MB.</p>
              </div>
              <div class="flex items-center gap-4">
                <div class="w-20 h-20 rounded-full bg-gray-100 overflow-hidden flex items-center justify-center">
                  <?php if ($img && is_file(__DIR__.'/'.$img)): ?>
                    <img id="avatarPreview" src="<?= htmlspecialchars($img) ?>" alt="Preview" class="w-full h-full object-cover">
                  <?php else: ?>
                    <img id="avatarPreview" src="" alt="Preview" class="hidden w-full h-full object-cover">
                    <i id="avatarPlaceholder" class="fas fa-user text-3xl text-gray-400"></i>
                  <?php endif; ?>
                </div>
                <div class="text-sm text-gray-600">Live preview</div>
              </div>
            </div>

            <!-- Password Section -->
            <div class="border rounded-lg">
              <div class="px-4 py-3 border-b bg-gray-50 flex items-center gap-2">
                <i class="fas fa-lock text-gray-500"></i>
                <span class="font-medium text-gray-700">Change Password (optional)</span>
              </div>
              <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm text-gray-600 mb-1">Old Password</label>
                  <input type="password" name="update_pass" placeholder="Enter old password"
                         class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                  <label class="block text-sm text-gray-600 mb-1">New Password</label>
                  <input type="password" name="new_pass" placeholder="Enter new password"
                         class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                  <label class="block text-sm text-gray-600 mb-1">Confirm Password</label>
                  <input type="password" name="confirm_pass" placeholder="Confirm new password"
                         class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap items-center gap-3">
              <button type="submit" name="update_profile"
                      class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded transition">
                <i class="fas fa-save"></i> Update Profile
              </button>
              <a href="admin_page.php"
                 class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium px-4 py-2 rounded transition">
                <i class="fas fa-arrow-left"></i> Go Back
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow p-6 mt-6">
      <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="admin_products.php" class="bg-blue-600 hover:bg-blue-700 text-white p-3 rounded text-center transition-colors">
          <i class="fas fa-box mb-2 block"></i>
          <span class="text-sm">Manage Products</span>
        </a>
        <a href="admin_orders.php" class="bg-green-600 hover:bg-green-700 text-white p-3 rounded text-center transition-colors">
          <i class="fas fa-list mb-2 block"></i>
          <span class="text-sm">View Orders</span>
        </a>
        <a href="admin_users.php" class="bg-purple-600 hover:bg-purple-700 text-white p-3 rounded text-center transition-colors">
          <i class="fas fa-users mb-2 block"></i>
          <span class="text-sm">Manage Users</span>
        </a>
        <a href="admin_contacts.php" class="bg-orange-600 hover:bg-orange-700 text-white p-3 rounded text-center transition-colors">
          <i class="fas fa-envelope mb-2 block"></i>
          <span class="text-sm">Messages</span>
        </a>
      </div>
    </div>

  </div>
</div>

<script>
// simple live preview if you want it
document.querySelector('input[name="image"]')?.addEventListener('change', function(e){
  const file = this.files && this.files[0];
  const img  = document.getElementById('avatarPreview');
  const ph   = document.getElementById('avatarPlaceholder');
  if (!file) return;
  const r = new FileReader();
  r.onload = ev => {
    img.src = ev.target.result;
    img.classList.remove('hidden');
    if (ph) ph.classList.add('hidden');
  };
  r.readAsDataURL(file);
});
</script>

<script src="js/script.js"></script>
</body>
</html>
