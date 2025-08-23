<?php
// admin_update_product.php (safe + debugable)
@include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
   header('location:login.php');
   exit;
}

/* ---------------------------
   Config
----------------------------*/
const SHOW_DEBUG = false; // set to true to see SQL errors in the blue messages box

$message = [];

/* ---------------------------
   Helpers (no deprecated filters)
----------------------------*/
function clean_text(?string $v): string {
  $v = trim((string)$v);
  return filter_var($v, FILTER_SANITIZE_SPECIAL_CHARS);
}
function clean_float($v): float {
  if ($v === null || $v === '') return -1;
  $v = filter_var($v, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
  return is_numeric($v) ? (float)$v : -1;
}
function clean_int($v): int {
  return (int) filter_var($v ?? 0, FILTER_SANITIZE_NUMBER_INT);
}

/* ---------------------------
   Ensure DB schema (adds columns if missing)
----------------------------*/
try {
  // quantity
  $q = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'quantity'");
  $q->execute();
  if (!(bool)$q->fetchColumn()) {
    $conn->exec("ALTER TABLE `products` ADD COLUMN `quantity` INT NOT NULL DEFAULT 0 AFTER `price`");
  }

  // category_id (optional but used)
  $q = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                       WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'category_id'");
  $q->execute();
  if (!(bool)$q->fetchColumn()) {
    $conn->exec("ALTER TABLE `products` ADD COLUMN `category_id` INT NULL AFTER `category`");
  }
} catch (Exception $e) {
  $message[] = 'Note: could not verify/prepare DB schema' . (SHOW_DEBUG ? (': ' . htmlspecialchars($e->getMessage())) : '.');
}

/* ---------- load categories for the dropdown ---------- */
$all_categories = [];
try {
  $cats_stmt = $conn->prepare("SELECT id, name FROM categories ORDER BY name");
  $cats_stmt->execute();
  $rows = $cats_stmt->fetchAll(PDO::FETCH_ASSOC);
  $all_categories = is_array($rows) ? $rows : [];
  if (!is_array($rows)) {
    if (SHOW_DEBUG) $message[] = 'Categories fetch returned non-array.';
  }
} catch (Exception $e) {
  $message[] = 'Could not load categories' . (SHOW_DEBUG ? (': ' . htmlspecialchars($e->getMessage())) : '.');
  $all_categories = [];
}

/* ===============================
   UPDATE PRODUCT (with quantity + category_id)
================================= */
if (isset($_POST['update_product'])) {
   $pid         = clean_int($_POST['pid'] ?? 0);
   $name        = clean_text($_POST['name'] ?? '');
   $price       = clean_float($_POST['price'] ?? '');
   $category_id = clean_int($_POST['category_id'] ?? 0);
   $quantity    = max(0, clean_int($_POST['quantity'] ?? 0));
   $details     = clean_text($_POST['details'] ?? '');

   if ($pid <= 0)           { $message[] = 'Invalid product id.'; }
   if ($name === '')        { $message[] = 'Name is required.'; }
   if ($price < 0)          { $message[] = 'Enter a valid price (decimals allowed).'; }
   if ($category_id <= 0)   { $message[] = 'Select a valid category.'; }
   if ($details === '')     { $message[] = 'Details are required.'; }

   // derive category name to keep legacy `category` in sync
   $category_name = '';
   if ($category_id > 0) {
     try {
       $findCat = $conn->prepare("SELECT name FROM categories WHERE id = ? LIMIT 1");
       $findCat->execute([$category_id]);
       $rowCat = $findCat->fetch(PDO::FETCH_ASSOC);
       $category_name = $rowCat['name'] ?? '';
       if ($category_name === '' && SHOW_DEBUG) $message[] = 'Category id not found when syncing name.';
     } catch (Exception $e) {
       if (SHOW_DEBUG) $message[] = 'Load category name failed: ' . htmlspecialchars($e->getMessage());
     }
   }

   if (empty($message)) {
     try {
      $sql = "UPDATE `products`
              SET name = ?, category_id = ?, category = ?, details = ?, price = ?, quantity = ?
              WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->execute([$name, $category_id, $category_name, $details, $price, $quantity, $pid]);
      $message[] = 'Product updated successfully!';
     } catch (Exception $e) {
      $message[] = 'Update failed' . (SHOW_DEBUG ? (': ' . htmlspecialchars($e->getMessage())) : '.');
     }
   }

   // Image update (optional)
   if (isset($_FILES['image']) && !empty($_FILES['image']['name']) && $pid > 0) {
      $image          = clean_text($_FILES['image']['name']);
      $image_size     = (int)($_FILES['image']['size'] ?? 0);
      $image_tmp_name = $_FILES['image']['tmp_name'] ?? '';
      $image_folder   = 'uploaded_img/'.$image;
      $old_image      = $_POST['old_image'] ?? '';

      if ($image_size > 2000000) {
         $message[] = 'Image size is too large!';
      } else {
        try {
         $update_image = $conn->prepare("UPDATE `products` SET image = ? WHERE id = ?");
         $update_image->execute([$image, $pid]);

         @move_uploaded_file($image_tmp_name, $image_folder);
         $old_path = 'uploaded_img/'.$old_image;
         if ($old_image && is_file($old_path)) {
           @unlink($old_path);
         }
         $message[] = 'Image updated successfully!';
        } catch (Exception $e) {
         $message[] = 'Image update failed' . (SHOW_DEBUG ? (': ' . htmlspecialchars($e->getMessage())) : '.');
        }
      }
   }
}

/* ===============================
   Load product to edit
================================= */
$update_id = isset($_GET['update']) ? (int)$_GET['update'] : 0;
$fetch_products = null;
if ($update_id > 0) {
  try {
   $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
   $select_products->execute([$update_id]);
   if ($select_products->rowCount() > 0) {
      $fetch_products = $select_products->fetch(PDO::FETCH_ASSOC);
   } else {
      $message[] = 'No products found!';
   }
  } catch (Exception $e) {
    $message[] = 'Failed to load product' . (SHOW_DEBUG ? (': ' . htmlspecialchars($e->getMessage())) : '.');
  }
} else {
   $message[] = 'No products found!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8" />
   <meta http-equiv="X-UA-Compatible" content="IE=edge" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <title>Admin • Update Product</title>

   <!-- Tailwind CSS -->
   <script src="https://cdn.tailwindcss.com"></script>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

   <style>
     .line-clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
   </style>
</head>
<body class="bg-gray-50">
<?php include 'admin_header.php'; ?>

<!-- Main Content wrapper to match sidebar offset -->
<div class="ml-64 pt-16 min-h-screen">
  <div class="p-6">

    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-6 text-white mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold">Update Product</h1>
          <p class="text-blue-100"><?= date('l, F j, Y'); ?></p>
        </div>
        <div class="flex gap-2">
          <a href="admin_products.php" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded transition">
            <i class="fas fa-arrow-left"></i> Back to Products
          </a>
          <a href="admin_page.php" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded transition">
            <i class="fas fa-home"></i> Dashboard
          </a>
        </div>
      </div>
    </div>

    <!-- Messages -->
    <?php if(!empty($message)): ?>
      <div class="mb-4 space-y-2">
        <?php foreach($message as $msg): ?>
          <div class="flex items-center gap-2 bg-blue-50 text-blue-800 border border-blue-200 rounded px-4 py-2">
            <i class="fas fa-info-circle"></i>
            <span><?= htmlspecialchars($msg); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if($fetch_products): ?>
    <!-- Update Card -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="mb-6">
        <h3 class="text-lg font-semibold">
          Editing: <span class="font-bold"><?= htmlspecialchars($fetch_products['name']); ?></span>
        </h3>
        <p class="text-sm text-gray-500">
          Product ID #<?= (int)$fetch_products['id']; ?>
        </p>
      </div>

      <form action="" method="post" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="old_image" value="<?= htmlspecialchars($fetch_products['image']); ?>">
        <input type="hidden" name="pid" value="<?= (int)$fetch_products['id']; ?>">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Preview & Image -->
          <div class="lg:col-span-1">
            <div class="border rounded-lg overflow-hidden bg-gray-50">
              <div class="aspect-[4/3] w-full bg-gray-100 flex items-center justify-center overflow-hidden">
                <?php if(!empty($fetch_products['image'])): ?>
                  <img id="previewImg" src="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>" alt="Product image" class="w-full h-full object-cover">
                <?php else: ?>
                  <img id="previewImg" src="" alt="" class="hidden w-full h-full object-cover">
                  <i id="placeholderIcon" class="fas fa-image text-4xl text-gray-400"></i>
                <?php endif; ?>
              </div>
              <div class="p-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Change Image</label>
                <input type="file" name="image" accept="image/jpg, image/jpeg, image/png"
                       class="w-full border rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                       onchange="previewFile(this)" />
                <p class="text-xs text-gray-500 mt-2">JPG/PNG up to 2MB.</p>
              </div>
            </div>
          </div>

          <!-- Fields -->
          <div class="lg:col-span-2 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm text-gray-600 mb-1">Product Name</label>
                <input type="text" name="name" required placeholder="Enter product name"
                       value="<?= htmlspecialchars($fetch_products['name']); ?>"
                       class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>

              <div>
                <label class="block text-sm text-gray-600 mb-1">Price (supports decimals)</label>
                <input type="number" name="price" step="0.01" min="0" required placeholder="Enter price (e.g. 199.99)"
                       value="<?= htmlspecialchars($fetch_products['price']); ?>"
                       class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm text-gray-600 mb-1">Category</label>
                <select name="category_id" required
                        class="w-full border rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <?php
                    $current_cat_id = (int)($fetch_products['category_id'] ?? 0);

                    if (is_array($all_categories) && count($all_categories) > 0) {
                        if ($current_cat_id > 0) {
                            foreach ($all_categories as $c) {
                                if ((int)$c['id'] === $current_cat_id) {
                                    echo '<option value="'.(int)$c['id'].'" selected>'
                                         .htmlspecialchars($c['name']).' (current)</option>';
                                    echo '<option disabled>──────────</option>';
                                    break;
                                }
                            }
                        } else {
                            echo '<option value="" disabled selected>Select category</option>';
                        }

                        foreach ($all_categories as $c) {
                            $cid = (int)$c['id'];
                            if ($cid === $current_cat_id) continue;
                            echo '<option value="'.$cid.'">'.htmlspecialchars($c['name']).'</option>';
                        }
                    } else {
                        echo '<option value="" disabled selected>No categories found</option>';
                    }
                  ?>
                </select>
              </div>

              <!-- Quantity -->
              <div>
                <label class="block text-sm text-gray-600 mb-1">Quantity in stock</label>
                <input type="number" name="quantity" min="0" step="1" required placeholder="e.g. 10"
                       value="<?= htmlspecialchars((string)($fetch_products['quantity'] ?? 0)); ?>"
                       class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
            </div>

            <div>
              <label class="block text-sm text-gray-600 mb-1">Details</label>
              <textarea name="details" required rows="6" placeholder="Enter product details"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($fetch_products['details']); ?></textarea>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap items-center gap-3 pt-2">
          <button type="submit" name="update_product"
                  class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded transition">
            <i class="fas fa-save"></i> Update Product
          </button>
          <a href="admin_products.php"
             class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium px-4 py-2 rounded transition">
            <i class="fas fa-arrow-left"></i> Go Back
          </a>
        </div>
      </form>
    </div>
    <?php endif; ?>

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
function previewFile(input){
  const file = input.files && input.files[0];
  const img  = document.getElementById('previewImg');
  const ico  = document.getElementById('placeholderIcon');
  if(file){
    const reader = new FileReader();
    reader.onload = e => {
      img.src = e.target.result;
      img.classList.remove('hidden');
      if(ico) ico.classList.add('hidden');
    };
    reader.readAsDataURL(file);
  }
}
</script>

<script src="js/script.js"></script>
</body>
</html>
