<?php
@include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'] ?? null;
if(!$admin_id){
   header('location:login.php');
   exit;
}

// Optional: hide deprecation notices locally
// error_reporting(E_ALL & ~E_DEPRECATED);

$messages = [];  // keep messages safely an array

/* -----------------------------------------
   Helper: does products.category exist?
----------------------------------------- */
$hasLegacyCategory = false;
try{
   $colChk = $conn->prepare("
      SELECT COUNT(*)
      FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'products'
        AND COLUMN_NAME = 'category'
   ");
   $colChk->execute();
   $hasLegacyCategory = ((int)$colChk->fetchColumn() > 0);
}catch(Exception $e){
   // ignore; default false
}

/* =========================================
   ADD PRODUCT  (with categories)
========================================= */
if(isset($_POST['add_product'])){
   $name        = trim(filter_var($_POST['name'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
   $price       = isset($_POST['price']) ? (float)$_POST['price'] : 0;
   $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
   $details     = trim(filter_var($_POST['details'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

   $image          = trim(filter_var($_FILES['image']['name'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
   $image_size     = $_FILES['image']['size'] ?? 0;
   $image_tmp_name = $_FILES['image']['tmp_name'] ?? '';
   $image_folder   = $image ? ('uploaded_img/'.$image) : '';

   $errs = [];
   if($name === ''){ $errs[] = 'Please enter a name.'; }
   if($price < 0){ $errs[] = 'Price must be >= 0.'; }
   if($category_id <= 0){
      $errs[] = 'Please choose a category.';
   }else{
      $catStmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
      $catStmt->execute([$category_id]);
      $category_name = $catStmt->fetchColumn();
      if(!$category_name){
         $errs[] = 'Selected category does not exist.';
      }
   }

   $select_products = $conn->prepare("SELECT 1 FROM products WHERE name = ? LIMIT 1");
   $select_products->execute([$name]);
   if($select_products->rowCount() > 0){
      $errs[] = 'Product name already exists!';
   }

   if(empty($errs)){
      try{
         if($hasLegacyCategory){
            $sql = "INSERT INTO products (name, category_id, category, details, price, image)
                    VALUES (?,?,?,?,?,?)";
            $ok  = $conn->prepare($sql)->execute([
               $name, $category_id, $category_name, $details, $price, $image
            ]);
         }else{
            $sql = "INSERT INTO products (name, category_id, details, price, image)
                    VALUES (?,?,?,?,?)";
            $ok  = $conn->prepare($sql)->execute([
               $name, $category_id, $details, $price, $image
            ]);
         }

         if($ok){
            if($image && $image_size > 2000000){
               $messages[] = 'Image size is too large!';
            }elseif($image){
               @move_uploaded_file($image_tmp_name, $image_folder);
               $messages[] = 'New product added!';
            }else{
               $messages[] = 'New product added (no image).';
            }
         }else{
            $messages[] = 'Failed to add product.';
         }
      }catch(PDOException $e){
         if(stripos($e->getMessage(), "Field 'category' doesn't have a default value") !== false){
            try{
               $sql = "INSERT INTO products (name, category_id, category, details, price, image)
                       VALUES (?,?,?,?,?,?)";
               $ok  = $conn->prepare($sql)->execute([
                  $name, $category_id, $category_name ?? '', $details, $price, $image
               ]);
               if($ok){
                  if($image && $image_size > 2000000){
                     $messages[] = 'Image size is too large!';
                  }elseif($image){
                     @move_uploaded_file($image_tmp_name, $image_folder);
                     $messages[] = 'New product added!';
                  }else{
                     $messages[] = 'New product added (no image).';
                  }
               }
            }catch(PDOException $e2){
               $messages[] = 'DB error: '.$e2->getMessage();
            }
         }else{
            $messages[] = 'DB error: '.$e->getMessage();
         }
      }
   }else{
      $messages[] = implode(' ', $errs);
   }
}

/* =========================================
   DELETE PRODUCT + cascade deletes
========================================= */
if(isset($_GET['delete'])){
   $delete_id = (int)($_GET['delete'] ?? 0);

   $select_delete_image = $conn->prepare("SELECT image FROM products WHERE id = ?");
   $select_delete_image->execute([$delete_id]);
   if($row = $select_delete_image->fetch(PDO::FETCH_ASSOC)){
      if(!empty($row['image'])){
         @unlink('uploaded_img/'.$row['image']);
      }
   }

   $conn->prepare("DELETE FROM products WHERE id=?")->execute([$delete_id]);
   $conn->prepare("DELETE FROM wishlist WHERE pid=?")->execute([$delete_id]);
   $conn->prepare("DELETE FROM cart WHERE pid=?")->execute([$delete_id]);
   $conn->prepare("DELETE FROM promotions WHERE product_id=?")->execute([$delete_id]);

   header('location:admin_products.php');
   exit;
}

/* =========================================
   PROMOTION CRUD
========================================= */
if(isset($_POST['save_promo'])){
   $promo_id         = (int)($_POST['promo_id'] ?? 0);
   $product_id       = (int)($_POST['product_id'] ?? 0);
   $promo_price      = ($_POST['promo_price']      !== '') ? (float)$_POST['promo_price']      : null;
   $discount_percent = ($_POST['discount_percent'] !== '') ? (float)$_POST['discount_percent'] : null;
   $label            = trim(filter_var($_POST['label'] ?? 'Limited Offer', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
   $starts_at        = trim($_POST['starts_at'] ?? '');
   $ends_at          = trim($_POST['ends_at']   ?? '');
   $active           = isset($_POST['active']) ? 1 : 0;

   $starts_at = $starts_at === '' ? null : $starts_at;
   $ends_at   = $ends_at   === '' ? null : $ends_at;

   $errs = [];
   if($product_id <= 0) $errs[] = 'Choose a valid product.';
   if($promo_price === null && $discount_percent === null) $errs[] = 'Set either Promo Price or Discount %.';
   if($promo_price !== null && $promo_price < 0) $errs[] = 'Promo price must be >= 0.';
   if($discount_percent !== null && ($discount_percent < 0 || $discount_percent > 95)) $errs[] = 'Discount % must be between 0 and 95.';

   if(empty($errs)){
      if($promo_id > 0){
         $sql = "UPDATE promotions
                 SET product_id=?, promo_price=?, discount_percent=?, label=?, starts_at=?, ends_at=?, active=?
                 WHERE id=?";
         $conn->prepare($sql)->execute([$product_id,$promo_price,$discount_percent,$label,$starts_at,$ends_at,$active,$promo_id]);
         $messages[] = 'Promotion updated.';
      }else{
         $chk = $conn->prepare("SELECT id FROM promotions WHERE product_id=? ORDER BY id DESC LIMIT 1");
         $chk->execute([$product_id]);
         if($row = $chk->fetch(PDO::FETCH_ASSOC)){
            $sql = "UPDATE promotions
                    SET promo_price=?, discount_percent=?, label=?, starts_at=?, ends_at=?, active=?
                    WHERE id=?";
            $conn->prepare($sql)->execute([$promo_price,$discount_percent,$label,$starts_at,$ends_at,$active,(int)$row['id']]);
            $messages[] = 'Promotion updated.';
         }else{
            $sql = "INSERT INTO promotions (product_id, promo_price, discount_percent, label, starts_at, ends_at, active)
                    VALUES (?,?,?,?,?,?,?)";
            $conn->prepare($sql)->execute([$product_id,$promo_price,$discount_percent,$label,$starts_at,$ends_at,$active]);
            $messages[] = 'Promotion created.';
         }
      }
   }else{
      $messages[] = implode(' ', $errs);
   }

   header('location:admin_products.php#p'.$product_id);
   exit;
}

if(isset($_GET['delete_promo'])){
   $del_pid     = (int)($_GET['pid'] ?? 0);
   $del_promoid = (int)($_GET['delete_promo'] ?? 0);
   $conn->prepare("DELETE FROM promotions WHERE id=?")->execute([$del_promoid]);
   $messages[] = 'Promotion deleted.';
   header('location:admin_products.php#p'.$del_pid);
   exit;
}

/* =========================================
   Load Categories for dropdowns/filters
========================================= */
$categories = [];
try{
   $cats_stmt = $conn->prepare("SELECT id, name FROM categories ORDER BY name");
   $cats_stmt->execute();
   $categories = $cats_stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}catch(Exception $e){
   $categories = [];
}

/* =========================================
   Search / Filter Products
========================================= */
$search_q      = isset($_GET['q'])   ? trim($_GET['q']) : '';
$filter_cat_id = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

$p_where  = [];
$p_params = [];

if($search_q !== ''){
   $like = "%{$search_q}%";
   $p_where[] = "(p.name LIKE ? OR p.details LIKE ? OR c.name LIKE ?)";
   array_push($p_params, $like, $like, $like);
}
if($filter_cat_id > 0){
   $p_where[] = "p.category_id = ?";
   $p_params[] = $filter_cat_id;
}
$where_sql = $p_where ? ('WHERE '.implode(' AND ', $p_where)) : '';

$stmt_count = $conn->prepare("SELECT COUNT(*) FROM products p LEFT JOIN categories c ON c.id = p.category_id $where_sql");
$stmt_count->execute($p_params);
$total_results = (int)$stmt_count->fetchColumn();

$show_products = $conn->prepare("
  SELECT p.*, c.name AS category_name
  FROM products p
  LEFT JOIN categories c ON c.id = p.category_id
  $where_sql
  ORDER BY p.id DESC
");
$show_products->execute($p_params);
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8" />
   <meta http-equiv="X-UA-Compatible" content="IE=edge" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <title>Admin • Products</title>
   <script src="https://cdn.tailwindcss.com"></script>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
   <style>
     .line-clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
     .line-clamp-3{display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
   </style>
</head>
<body class="bg-gray-50">
<?php include 'admin_header.php'; ?>

<div class="ml-64 pt-16 min-h-screen">
  <div class="p-6">

    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-6 text-white mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold">Products</h1>
          <p class="text-blue-100"><?= date('l, F j, Y'); ?></p>
        </div>
        <a href="admin_page.php" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded transition">
          <i class="fas fa-home"></i> Dashboard
        </a>
      </div>
    </div>

    <?php if(!empty($messages)): ?>
      <div class="mb-4 space-y-2">
        <?php foreach(($messages ?? []) as $msg): ?>
          <div class="flex items-center gap-2 bg-blue-50 text-blue-800 border border-blue-200 rounded px-4 py-2">
            <i class="fas fa-info-circle"></i>
            <span><?= htmlspecialchars($msg); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="#add-form" class="bg-blue-600 hover:bg-blue-700 text-white p-3 rounded text-center transition-colors">
          <i class="fas fa-plus mb-2 block"></i>
          <span class="text-sm">Add Product</span>
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

    <div id="add-form" class="bg-white rounded-lg shadow p-6 mb-8">
      <h3 class="text-lg font-semibold mb-4">Add New Product</h3>

      <?php if(empty($categories)): ?>
        <div class="mb-4 p-3 rounded bg-yellow-50 text-yellow-900 border border-yellow-200">
          No categories found. <a class="underline text-blue-700" href="admin_categories.php">Create a category</a> first.
        </div>
      <?php endif; ?>

      <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-4">
            <input type="text" name="name" required placeholder="Enter product name"
                   class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />

            <select name="category_id" required
                    class="w-full border rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="" disabled selected>Select category</option>
              <?php foreach(($categories ?? []) as $catRow): ?>
                <option value="<?= (int)$catRow['id']; ?>"><?= htmlspecialchars($catRow['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="space-y-4">
            <input type="number" step="0.01" min="0" inputmode="decimal" name="price" required placeholder="Enter product price (Rs)"
                   class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />

            <input type="file" name="image" accept="image/jpg, image/jpeg, image/png"
                   class="w-full border rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
        </div>

        <textarea name="details" required placeholder="Enter product details" rows="5"
                  class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>

        <button type="submit" name="add_product"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded transition">
          <i class="fas fa-save"></i> Add Product
        </button>
      </form>
    </div>

    <div class="bg-white rounded-lg shadow p-4 md:p-6 mb-4">
      <form method="GET" class="flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-between">
        <div>
          <h3 class="text-lg font-semibold">Products Added</h3>
          <?php if($search_q !== '' || $filter_cat_id > 0): ?>
            <p class="text-gray-500 text-sm">
              Showing <span class="font-medium"><?= $total_results; ?></span> result(s)
              <?php if($search_q !== ''): ?> for “<span class="font-medium"><?= htmlspecialchars($search_q); ?></span>”<?php endif; ?>
              <?php if($filter_cat_id > 0): ?>
                in <span class="font-medium">
                  <?php
                    $nm = array_values(array_filter($categories, fn($c)=> (int)$c['id'] === $filter_cat_id));
                    echo $nm ? htmlspecialchars($nm[0]['name']) : 'category';
                  ?>
                </span>
              <?php endif; ?>
              <a href="admin_products.php" class="text-blue-600 hover:underline ml-2">Reset</a>
            </p>
          <?php else: ?>
            <p class="text-gray-500 text-sm">Search, filter and manage products</p>
          <?php endif; ?>
        </div>
        <div class="flex flex-col sm:flex-row gap-2">
          <div class="relative">
            <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input
              type="text"
              name="q"
              value="<?= htmlspecialchars($search_q); ?>"
              placeholder="Search product, details, category…"
              class="w-full sm:w-80 pl-10 pr-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <select name="cat" class="w-full sm:w-52 py-2 px-3 border rounded bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="0">All categories</option>
            <?php foreach(($categories ?? []) as $catOpt): ?>
              <option value="<?= (int)$catOpt['id']; ?>" <?= $filter_cat_id===(int)$catOpt['id']?'selected':''; ?>>
                <?= htmlspecialchars($catOpt['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <button class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition">
            <i class="fas fa-filter"></i> Apply
          </button>
        </div>
      </form>
    </div>

    <div class="bg-white rounded-lg shadow">
      <div class="p-4 md:p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
          <?php
            if($show_products->rowCount() > 0){
              while($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)){
                $pid = (int)$fetch_products['id'];

                $promo = null;
                $getPromo = $conn->prepare("SELECT * FROM promotions WHERE product_id = ? ORDER BY id DESC LIMIT 1");
                $getPromo->execute([$pid]);
                if($getPromo->rowCount()){
                  $promo = $getPromo->fetch(PDO::FETCH_ASSOC);
                }

                $now   = date('Y-m-d H:i:s');
                $base  = (float)$fetch_products['price'];
                $final = null; $isLive = false;

                if($promo){
                  $inWindow = ($promo['starts_at'] === null || $promo['starts_at'] <= $now)
                           && ($promo['ends_at'] === null   || $promo['ends_at']   >= $now);
                  if((int)$promo['active'] === 1 && $inWindow){
                    if($promo['promo_price'] !== null && $promo['promo_price'] !== ''){
                      $final = (float)$promo['promo_price'];
                    }elseif($promo['discount_percent'] !== null && $promo['discount_percent'] !== ''){
                      $final = max(0, $base * (1 - ((float)$promo['discount_percent']/100)));
                    }
                    if($final !== null && $final < $base){ $isLive = true; }
                  }
                }
          ?>
          <span id="p<?= $pid; ?>" class="relative -top-20 block"></span>

          <div class="rounded-lg border hover:shadow-md transition bg-white overflow-hidden">
            <div class="aspect-[4/3] bg-gray-100 flex items-center justify-center overflow-hidden">
              <?php if(!empty($fetch_products['image'])): ?>
                <img src="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>" alt="" class="w-full h-full object-cover" />
              <?php else: ?>
                <i class="fas fa-image text-gray-400 text-4xl"></i>
              <?php endif; ?>
            </div>

            <div class="p-4 space-y-2">
              <div class="flex items-start justify-between gap-2">
                <h4 class="font-semibold line-clamp-2"><?= htmlspecialchars($fetch_products['name']); ?></h4>
                <span class="text-xs px-2 py-1 rounded bg-indigo-100 text-indigo-700">
                  <?= htmlspecialchars($fetch_products['category_name'] ?? 'Uncategorized'); ?>
                </span>
              </div>

              <div class="text-sm text-gray-600 line-clamp-3" title="<?= htmlspecialchars($fetch_products['details']); ?>">
                <?= htmlspecialchars($fetch_products['details']); ?>
              </div>

              <div class="flex items-center gap-2 pt-1">
                <?php if($isLive): ?>
                  <span class="text-sm text-gray-500 line-through">Rs <?= number_format($base,2); ?></span>
                  <span class="text-green-700 font-semibold">Rs <?= number_format($final,2); ?></span>
                  <?php if($final > 0 && $base > 0): ?>
                    <span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-700">
                      SAVE <?= round((($base-$final)/$base)*100); ?>%
                    </span>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="text-gray-800 font-semibold">Rs <?= number_format((float)$fetch_products['price'],2); ?></span>
                <?php endif; ?>
              </div>

              <?php if($isLive): ?>
                <div class="text-xs px-2 py-1 rounded bg-emerald-100 text-emerald-800 inline-flex items-center gap-1">
                  <i class="fas fa-bolt"></i> LIVE PROMO
                </div>
              <?php elseif($promo): ?>
                <div class="text-xs px-2 py-1 rounded bg-yellow-100 text-yellow-800 inline-flex items-center gap-1">
                  <i class="fas fa-circle-info"></i> Promo exists (not live)
                </div>
              <?php else: ?>
                <div class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-700 inline-flex items-center gap-1">
                  <i class="fas fa-tag"></i> No promotion
                </div>
              <?php endif; ?>

              <div class="flex items-center gap-2 pt-2">
                <a href="admin_update_product.php?update=<?= $pid; ?>" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-2 rounded transition">
                  <i class="fas fa-pen"></i> update
                </a>
                <a href="admin_products.php?delete=<?= $pid; ?>" onclick="return confirm('delete this product?');" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-3 py-2 rounded transition">
                  <i class="fas fa-trash"></i> delete
                </a>
              </div>
            </div>

            <div class="p-4 bg-gray-50 border-t">
              <form method="POST" action="" class="space-y-3">
                <input type="hidden" name="product_id" value="<?= $pid; ?>">
                <input type="hidden" name="promo_id" value="<?= $promo ? (int)$promo['id'] : 0; ?>">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs text-gray-600 mb-1">Promo Price (optional)</label>
                    <input type="number" step="0.01" name="promo_price" value="<?= $promo && $promo['promo_price'] !== null ? htmlspecialchars($promo['promo_price']) : ''; ?>" placeholder="e.g. 2499.00" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div>
                    <label class="block text-xs text-gray-600 mb-1">Discount % (optional)</label>
                    <input type="number" step="0.01" name="discount_percent" value="<?= $promo && $promo['discount_percent'] !== null ? htmlspecialchars($promo['discount_percent']) : ''; ?>" placeholder="e.g. 20" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs text-gray-600 mb-1">Label</label>
                    <input type="text" name="label" value="<?= $promo ? htmlspecialchars($promo['label']) : 'Limited Offer'; ?>" maxlength="60" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div class="flex items-center gap-2 pt-6 sm:pt-6">
                    <input type="checkbox" id="active_<?= $pid; ?>" name="active" class="w-4 h-4" <?= $promo ? ((int)$promo['active']===1 ? 'checked' : '') : 'checked'; ?> />
                    <label for="active_<?= $pid; ?>" class="text-sm text-gray-700">Active</label>
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs text-gray-600 mb-1">Starts At (YYYY-MM-DD HH:MM:SS)</label>
                    <input type="text" name="starts_at" value="<?= $promo && !empty($promo['starts_at']) ? htmlspecialchars($promo['starts_at']) : ''; ?>" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div>
                    <label class="block text-xs text-gray-600 mb-1">Ends At (YYYY-MM-DD HH:MM:SS)</label>
                    <input type="text" name="ends_at" value="<?= $promo && !empty($promo['ends_at']) ? htmlspecialchars($promo['ends_at']) : ''; ?>" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                </div>

                <div class="flex items-center flex-wrap gap-2">
                  <button type="submit" name="save_promo" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-3 py-2 rounded transition">
                    <i class="fas fa-badge-percent"></i> Save Promotion
                  </button>

                  <?php if($promo): ?>
                    <a href="admin_products.php?delete_promo=<?= (int)$promo['id']; ?>&pid=<?= $pid; ?>" onclick="return confirm('Delete this promotion?');" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-3 py-2 rounded transition">
                      <i class="fas fa-trash"></i> Delete Promotion
                    </a>
                  <?php endif; ?>
                </div>
              </form>
            </div>
          </div>
          <?php
              }
            }else{
              echo '<div class="col-span-full text-center text-gray-500 py-8">No products found.</div>';
            }
          ?>
        </div>
      </div>
    </div>

  </div>
</div>

<script src="js/script.js"></script>
</body>
</html>
