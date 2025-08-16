<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit;
}

/* =========================================
   ADD PRODUCT (original)
========================================= */
if(isset($_POST['add_product'])){

   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
   $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
   $details = filter_var($_POST['details'], FILTER_SANITIZE_STRING);

   $image = filter_var($_FILES['image']['name'], FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;

   $select_products = $conn->prepare("SELECT * FROM products WHERE name = ?");
   $select_products->execute([$name]);

   if($select_products->rowCount() > 0){
      $message[] = 'product name already exist!';
   }else{

      $insert_products = $conn->prepare("INSERT INTO products(name, category, details, price, image) VALUES(?,?,?,?,?)");
      $insert_products->execute([$name, $category, $details, $price, $image]);

      if($insert_products){
         if($image_size > 2000000){
            $message[] = 'image size is too large!';
         }else{
            move_uploaded_file($image_tmp_name, $image_folder);
            $message[] = 'new product added!';
         }
      }
   }
}

/* =========================================
   DELETE PRODUCT (original) + delete promos
========================================= */
if(isset($_GET['delete'])){

   $delete_id = (int)$_GET['delete'];

   // delete image
   $select_delete_image = $conn->prepare("SELECT image FROM products WHERE id = ?");
   $select_delete_image->execute([$delete_id]);
   if($select_delete_image->rowCount()){
      $fetch_delete_image = $select_delete_image->fetch(PDO::FETCH_ASSOC);
      @unlink('uploaded_img/'.$fetch_delete_image['image']);
   }

   // delete product
   $delete_products = $conn->prepare("DELETE FROM products WHERE id = ?");
   $delete_products->execute([$delete_id]);

   // cascade deletes
   $delete_wishlist = $conn->prepare("DELETE FROM wishlist WHERE pid = ?");
   $delete_wishlist->execute([$delete_id]);

   $delete_cart = $conn->prepare("DELETE FROM cart WHERE pid = ?");
   $delete_cart->execute([$delete_id]);

   // NEW: remove promotions tied to this product (in case FK not set)
   $delete_promos = $conn->prepare("DELETE FROM promotions WHERE product_id = ?");
   $delete_promos->execute([$delete_id]);

   header('location:admin_products.php');
   exit;
}

/* =========================================
   PROMOTION: Create/Update (CRUD)
   - One promotion per product (latest wins)
========================================= */
if(isset($_POST['save_promo'])){
   // Fields
   $promo_id = isset($_POST['promo_id']) ? (int)$_POST['promo_id'] : 0;
   $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

   // Accept either promo_price OR discount_percent (one or both)
   $promo_price = isset($_POST['promo_price']) && $_POST['promo_price'] !== '' ? (float)$_POST['promo_price'] : null;
   $discount_percent = isset($_POST['discount_percent']) && $_POST['discount_percent'] !== '' ? (float)$_POST['discount_percent'] : null;

   $label = isset($_POST['label']) ? trim(filter_var($_POST['label'], FILTER_SANITIZE_STRING)) : 'Limited Offer';
   $starts_at = isset($_POST['starts_at']) ? trim($_POST['starts_at']) : '';
   $ends_at   = isset($_POST['ends_at'])   ? trim($_POST['ends_at'])   : '';
   $active    = isset($_POST['active']) ? 1 : 0;

   // Normalize empty to NULL
   $starts_at = ($starts_at === '') ? null : $starts_at;
   $ends_at   = ($ends_at === '')   ? null : $ends_at;

   // Basic validation
   $errs = [];
   if($product_id <= 0){ $errs[] = 'Choose a valid product.'; }
   if($promo_price === null && $discount_percent === null){
      $errs[] = 'Set either Promo Price or Discount %.';
   }
   if($promo_price !== null && $promo_price < 0){
      $errs[] = 'Promo price must be >= 0.';
   }
   if($discount_percent !== null && ($discount_percent < 0 || $discount_percent > 95)){
      $errs[] = 'Discount % must be between 0 and 95.';
   }

   if(empty($errs)){
      if($promo_id > 0){
         // UPDATE
         $sql = "UPDATE promotions
                 SET product_id=?, promo_price=?, discount_percent=?, label=?, starts_at=?, ends_at=?, active=?
                 WHERE id=?";
         $stmt = $conn->prepare($sql);
         $stmt->execute([$product_id, $promo_price, $discount_percent, $label, $starts_at, $ends_at, $active, $promo_id]);
         $message[] = 'Promotion updated.';
      }else{
         // If you want to enforce one-per-product, you can upsert:
         // Try to find existing promo for product
         $chk = $conn->prepare("SELECT id FROM promotions WHERE product_id=? ORDER BY id DESC LIMIT 1");
         $chk->execute([$product_id]);
         if($chk->rowCount()){
            $row = $chk->fetch(PDO::FETCH_ASSOC);
            $sql = "UPDATE promotions
                    SET promo_price=?, discount_percent=?, label=?, starts_at=?, ends_at=?, active=?
                    WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$promo_price, $discount_percent, $label, $starts_at, $ends_at, $active, (int)$row['id']]);
            $message[] = 'Promotion updated.';
         }else{
            // INSERT
            $sql = "INSERT INTO promotions (product_id, promo_price, discount_percent, label, starts_at, ends_at, active)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$product_id, $promo_price, $discount_percent, $label, $starts_at, $ends_at, $active]);
            $message[] = 'Promotion created.';
         }
      }
   }else{
      $message[] = implode(' ', $errs);
   }

   header('location:admin_products.php#p'.$product_id);
   exit;
}

/* =========================================
   PROMOTION: Delete (CRUD)
========================================= */
if(isset($_GET['delete_promo'])){
   $del_pid = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
   $del_promoid = (int)$_GET['delete_promo'];
   $stmt = $conn->prepare("DELETE FROM promotions WHERE id = ?");
   $stmt->execute([$del_promoid]);
   $message[] = 'Promotion deleted.';
   header('location:admin_products.php#p'.$del_pid);
   exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>products</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

   <style>
     /* Small tweaks to fit promo editor nicely inside product boxes */
     .promo-wrap{margin-top:12px; padding:12px; border:1px dashed #aaa; border-radius:10px; background:#fafafa;}
     .promo-row{display:grid; grid-template-columns:repeat(2,1fr); gap:10px; margin-top:8px;}
     .promo-row .box{width:100%;}
     .pill{display:inline-block; padding:4px 8px; border-radius:20px; background:#eef6ff; color:#0a3a66; font-size:12px; margin-top:6px;}
     .now-price{font-weight:700; color:#0a7a34;}
     .old-price{text-decoration:line-through; color:#888;}
     .promo-actions{display:flex; gap:8px; margin-top:10px; align-items:center;}
     .btn-secondary{background:#ddd; color:#333; padding:8px 12px; border-radius:6px;}
     .btn-danger{background:#ff4444; color:#fff; padding:8px 12px; border-radius:6px;}
     .anchor-spacer{position:relative; top:-80px; visibility:hidden;}
   </style>
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="add-products">

   <h1 class="title">add new product</h1>

   <form action="" method="POST" enctype="multipart/form-data">
      <div class="flex">
         <div class="inputBox">
           <input type="text" name="name" class="box" required placeholder="enter product name">
           <select name="category" class="box" required>
              <option value="" selected disabled>select category</option>
              <option value="Wood">Wood</option>
              <option value="Clothes">Clothes</option>
              <option value="Wall">Wall decorations</option>
              <option value="Brass">Brass</option>
           </select>
         </div>
         <div class="inputBox">
           <input type="price" min="0" name="price" class="box" required placeholder="enter product price">
           <input type="file" name="image" required class="box" accept="image/jpg, image/jpeg, image/png">
         </div>
      </div>
      <textarea name="details" class="box" required placeholder="enter product details" cols="30" rows="10"></textarea>
      <input type="submit" class="btn" value="add product" name="add_product">
   </form>

</section>

<section class="show-products">

   <h1 class="title">products added</h1>

   <div class="box-container">

   <?php
      $show_products = $conn->prepare("SELECT * FROM products ORDER BY id DESC");
      $show_products->execute();
      if($show_products->rowCount() > 0){
         while($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)){
            $pid = (int)$fetch_products['id'];

            // Load current promo (if any) for this product (take most recent)
            $promo = null;
            $getPromo = $conn->prepare("SELECT * FROM promotions WHERE product_id = ? ORDER BY id DESC LIMIT 1");
            $getPromo->execute([$pid]);
            if($getPromo->rowCount()){
               $promo = $getPromo->fetch(PDO::FETCH_ASSOC);
            }

            // Compute final price preview (if promo active + within dates)
            $now = date('Y-m-d H:i:s');
            $base = (float)$fetch_products['price'];
            $final = null;
            $isLive = false;
            if($promo){
               $inWindow =
                  ($promo['starts_at'] === null || $promo['starts_at'] <= $now) &&
                  ($promo['ends_at'] === null   || $promo['ends_at']   >= $now);
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
   <span id="p<?= $pid; ?>" class="anchor-spacer"></span>
   <div class="box">
      <div class="price">$<?= htmlspecialchars($fetch_products['price']); ?>/-</div>
      <img src="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>" alt="">
      <div class="name"><?= htmlspecialchars($fetch_products['name']); ?></div>
      <div class="cat"><?= htmlspecialchars($fetch_products['category']); ?></div>
      <div class="details"><?= htmlspecialchars($fetch_products['details']); ?></div>

      <?php if($isLive): ?>
        <div class="pill">
          LIVE PROMO:
          <span class="old-price">$<?= number_format($base,2); ?></span>
          → <span class="now-price">$<?= number_format($final,2); ?></span>
          <?php if($final > 0 && $base > 0): ?>
            (SAVE <?= round((($base-$final)/$base)*100); ?>%)
          <?php endif; ?>
        </div>
      <?php elseif($promo): ?>
        <div class="pill">Promo exists but not currently live (inactive or out of window).</div>
      <?php else: ?>
        <div class="pill" style="background:#fff4e5;color:#663c00;">No promotion configured.</div>
      <?php endif; ?>

      <!-- Promotion editor -->
      <div class="promo-wrap">
        <form method="POST" action="">
          <input type="hidden" name="product_id" value="<?= $pid; ?>">
          <input type="hidden" name="promo_id" value="<?= $promo ? (int)$promo['id'] : 0; ?>">

          <div class="promo-row">
            <div>
              <label>Promo Price (optional)</label>
              <input type="number" step="0.01" name="promo_price" class="box"
                     value="<?= $promo && $promo['promo_price'] !== null ? htmlspecialchars($promo['promo_price']) : ''; ?>"
                     placeholder="e.g. 2499.00">
            </div>
            <div>
              <label>Discount % (optional)</label>
              <input type="number" step="0.01" name="discount_percent" class="box"
                     value="<?= $promo && $promo['discount_percent'] !== null ? htmlspecialchars($promo['discount_percent']) : ''; ?>"
                     placeholder="e.g. 20">
            </div>
          </div>

          <div class="promo-row">
            <div>
              <label>Label</label>
              <input type="text" name="label" class="box"
                     value="<?= $promo ? htmlspecialchars($promo['label']) : 'Limited Offer'; ?>"
                     maxlength="60">
            </div>
            <div class="flex" style="align-items:center; gap:10px; margin-top:24px;">
              <input type="checkbox" id="active_<?= $pid; ?>" name="active"
                     <?= $promo ? ((int)$promo['active']===1 ? 'checked' : '') : 'checked'; ?>>
              <label for="active_<?= $pid; ?>">Active</label>
            </div>
          </div>

          <div class="promo-row">
            <div>
              <label>Starts At (YYYY-MM-DD HH:MM:SS)</label>
              <input type="text" name="starts_at" class="box"
                     value="<?= $promo && !empty($promo['starts_at']) ? htmlspecialchars($promo['starts_at']) : ''; ?>">
            </div>
            <div>
              <label>Ends At (YYYY-MM-DD HH:MM:SS)</label>
              <input type="text" name="ends_at" class="box"
                     value="<?= $promo && !empty($promo['ends_at']) ? htmlspecialchars($promo['ends_at']) : ''; ?>">
            </div>
          </div>

          <div class="promo-actions">
            <button type="submit" name="save_promo" class="btn">Save Promotion</button>
            <?php if($promo): ?>
              <a href="admin_products.php?delete_promo=<?= (int)$promo['id']; ?>&pid=<?= $pid; ?>"
                 class="btn-danger" onclick="return confirm('Delete this promotion?');">
                 Delete Promotion
              </a>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <div class="flex-btn" style="margin-top:12px;">
         <a href="admin_update_product.php?update=<?= $pid; ?>" class="option-btn">update</a>
         <a href="admin_products.php?delete=<?= $pid; ?>" class="delete-btn" onclick="return confirm('delete this product?');">delete</a>
      </div>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">now products added yet!</p>';
      }
   ?>

   </div>

</section>

<script src="js/script.js"></script>
</body>
</html><?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit;
}

/* =========================================
   ADD PRODUCT (original)
========================================= */
if(isset($_POST['add_product'])){

   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
   $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
   $details = filter_var($_POST['details'], FILTER_SANITIZE_STRING);

   $image = filter_var($_FILES['image']['name'], FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;

   $select_products = $conn->prepare("SELECT * FROM products WHERE name = ?");
   $select_products->execute([$name]);

   if($select_products->rowCount() > 0){
      $message[] = 'product name already exist!';
   }else{

      $insert_products = $conn->prepare("INSERT INTO products(name, category, details, price, image) VALUES(?,?,?,?,?)");
      $insert_products->execute([$name, $category, $details, $price, $image]);

      if($insert_products){
         if($image_size > 2000000){
            $message[] = 'image size is too large!';
         }else{
            move_uploaded_file($image_tmp_name, $image_folder);
            $message[] = 'new product added!';
         }
      }
   }
}

/* =========================================
   DELETE PRODUCT (original) + delete promos
========================================= */
if(isset($_GET['delete'])){

   $delete_id = (int)$_GET['delete'];

   // delete image
   $select_delete_image = $conn->prepare("SELECT image FROM products WHERE id = ?");
   $select_delete_image->execute([$delete_id]);
   if($select_delete_image->rowCount()){
      $fetch_delete_image = $select_delete_image->fetch(PDO::FETCH_ASSOC);
      @unlink('uploaded_img/'.$fetch_delete_image['image']);
   }

   // delete product
   $delete_products = $conn->prepare("DELETE FROM products WHERE id = ?");
   $delete_products->execute([$delete_id]);

   // cascade deletes
   $delete_wishlist = $conn->prepare("DELETE FROM wishlist WHERE pid = ?");
   $delete_wishlist->execute([$delete_id]);

   $delete_cart = $conn->prepare("DELETE FROM cart WHERE pid = ?");
   $delete_cart->execute([$delete_id]);

   // NEW: remove promotions tied to this product (in case FK not set)
   $delete_promos = $conn->prepare("DELETE FROM promotions WHERE product_id = ?");
   $delete_promos->execute([$delete_id]);

   header('location:admin_products.php');
   exit;
}

/* =========================================
   PROMOTION: Create/Update (CRUD)
   - One promotion per product (latest wins)
========================================= */
if(isset($_POST['save_promo'])){
   // Fields
   $promo_id = isset($_POST['promo_id']) ? (int)$_POST['promo_id'] : 0;
   $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

   // Accept either promo_price OR discount_percent (one or both)
   $promo_price = isset($_POST['promo_price']) && $_POST['promo_price'] !== '' ? (float)$_POST['promo_price'] : null;
   $discount_percent = isset($_POST['discount_percent']) && $_POST['discount_percent'] !== '' ? (float)$_POST['discount_percent'] : null;

   $label = isset($_POST['label']) ? trim(filter_var($_POST['label'], FILTER_SANITIZE_STRING)) : 'Limited Offer';
   $starts_at = isset($_POST['starts_at']) ? trim($_POST['starts_at']) : '';
   $ends_at   = isset($_POST['ends_at'])   ? trim($_POST['ends_at'])   : '';
   $active    = isset($_POST['active']) ? 1 : 0;

   // Normalize empty to NULL
   $starts_at = ($starts_at === '') ? null : $starts_at;
   $ends_at   = ($ends_at === '')   ? null : $ends_at;

   // Basic validation
   $errs = [];
   if($product_id <= 0){ $errs[] = 'Choose a valid product.'; }
   if($promo_price === null && $discount_percent === null){
      $errs[] = 'Set either Promo Price or Discount %.';
   }
   if($promo_price !== null && $promo_price < 0){
      $errs[] = 'Promo price must be >= 0.';
   }
   if($discount_percent !== null && ($discount_percent < 0 || $discount_percent > 95)){
      $errs[] = 'Discount % must be between 0 and 95.';
   }

   if(empty($errs)){
      if($promo_id > 0){
         // UPDATE
         $sql = "UPDATE promotions
                 SET product_id=?, promo_price=?, discount_percent=?, label=?, starts_at=?, ends_at=?, active=?
                 WHERE id=?";
         $stmt = $conn->prepare($sql);
         $stmt->execute([$product_id, $promo_price, $discount_percent, $label, $starts_at, $ends_at, $active, $promo_id]);
         $message[] = 'Promotion updated.';
      }else{
         // If you want to enforce one-per-product, you can upsert:
         // Try to find existing promo for product
         $chk = $conn->prepare("SELECT id FROM promotions WHERE product_id=? ORDER BY id DESC LIMIT 1");
         $chk->execute([$product_id]);
         if($chk->rowCount()){
            $row = $chk->fetch(PDO::FETCH_ASSOC);
            $sql = "UPDATE promotions
                    SET promo_price=?, discount_percent=?, label=?, starts_at=?, ends_at=?, active=?
                    WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$promo_price, $discount_percent, $label, $starts_at, $ends_at, $active, (int)$row['id']]);
            $message[] = 'Promotion updated.';
         }else{
            // INSERT
            $sql = "INSERT INTO promotions (product_id, promo_price, discount_percent, label, starts_at, ends_at, active)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$product_id, $promo_price, $discount_percent, $label, $starts_at, $ends_at, $active]);
            $message[] = 'Promotion created.';
         }
      }
   }else{
      $message[] = implode(' ', $errs);
   }

   header('location:admin_products.php#p'.$product_id);
   exit;
}

/* =========================================
   PROMOTION: Delete (CRUD)
========================================= */
if(isset($_GET['delete_promo'])){
   $del_pid = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
   $del_promoid = (int)$_GET['delete_promo'];
   $stmt = $conn->prepare("DELETE FROM promotions WHERE id = ?");
   $stmt->execute([$del_promoid]);
   $message[] = 'Promotion deleted.';
   header('location:admin_products.php#p'.$del_pid);
   exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>products</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

   <style>
     /* Small tweaks to fit promo editor nicely inside product boxes */
     .promo-wrap{margin-top:12px; padding:12px; border:1px dashed #aaa; border-radius:10px; background:#fafafa;}
     .promo-row{display:grid; grid-template-columns:repeat(2,1fr); gap:10px; margin-top:8px;}
     .promo-row .box{width:100%;}
     .pill{display:inline-block; padding:4px 8px; border-radius:20px; background:#eef6ff; color:#0a3a66; font-size:12px; margin-top:6px;}
     .now-price{font-weight:700; color:#0a7a34;}
     .old-price{text-decoration:line-through; color:#888;}
     .promo-actions{display:flex; gap:8px; margin-top:10px; align-items:center;}
     .btn-secondary{background:#ddd; color:#333; padding:8px 12px; border-radius:6px;}
     .btn-danger{background:#ff4444; color:#fff; padding:8px 12px; border-radius:6px;}
     .anchor-spacer{position:relative; top:-80px; visibility:hidden;}
   </style>
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="add-products">

   <h1 class="title">add new product</h1>

   <form action="" method="POST" enctype="multipart/form-data">
      <div class="flex">
         <div class="inputBox">
           <input type="text" name="name" class="box" required placeholder="enter product name">
           <select name="category" class="box" required>
              <option value="" selected disabled>select category</option>
              <option value="Wood">Wood</option>
              <option value="Clothes">Clothes</option>
              <option value="Wall">Wall decorations</option>
              <option value="Brass">Brass</option>
           </select>
         </div>
         <div class="inputBox">
           <input type="price" min="0" name="price" class="box" required placeholder="enter product price">
           <input type="file" name="image" required class="box" accept="image/jpg, image/jpeg, image/png">
         </div>
      </div>
      <textarea name="details" class="box" required placeholder="enter product details" cols="30" rows="10"></textarea>
      <input type="submit" class="btn" value="add product" name="add_product">
   </form>

</section>

<section class="show-products">

   <h1 class="title">products added</h1>

   <div class="box-container">

   <?php
      $show_products = $conn->prepare("SELECT * FROM products ORDER BY id DESC");
      $show_products->execute();
      if($show_products->rowCount() > 0){
         while($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)){
            $pid = (int)$fetch_products['id'];

            // Load current promo (if any) for this product (take most recent)
            $promo = null;
            $getPromo = $conn->prepare("SELECT * FROM promotions WHERE product_id = ? ORDER BY id DESC LIMIT 1");
            $getPromo->execute([$pid]);
            if($getPromo->rowCount()){
               $promo = $getPromo->fetch(PDO::FETCH_ASSOC);
            }

            // Compute final price preview (if promo active + within dates)
            $now = date('Y-m-d H:i:s');
            $base = (float)$fetch_products['price'];
            $final = null;
            $isLive = false;
            if($promo){
               $inWindow =
                  ($promo['starts_at'] === null || $promo['starts_at'] <= $now) &&
                  ($promo['ends_at'] === null   || $promo['ends_at']   >= $now);
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
   <span id="p<?= $pid; ?>" class="anchor-spacer"></span>
   <div class="box">
      <div class="price">$<?= htmlspecialchars($fetch_products['price']); ?>/-</div>
      <img src="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>" alt="">
      <div class="name"><?= htmlspecialchars($fetch_products['name']); ?></div>
      <div class="cat"><?= htmlspecialchars($fetch_products['category']); ?></div>
      <div class="details"><?= htmlspecialchars($fetch_products['details']); ?></div>

      <?php if($isLive): ?>
        <div class="pill">
          LIVE PROMO:
          <span class="old-price">$<?= number_format($base,2); ?></span>
          → <span class="now-price">$<?= number_format($final,2); ?></span>
          <?php if($final > 0 && $base > 0): ?>
            (SAVE <?= round((($base-$final)/$base)*100); ?>%)
          <?php endif; ?>
        </div>
      <?php elseif($promo): ?>
        <div class="pill">Promo exists but not currently live (inactive or out of window).</div>
      <?php else: ?>
        <div class="pill" style="background:#fff4e5;color:#663c00;">No promotion configured.</div>
      <?php endif; ?>

      <!-- Promotion editor -->
      <div class="promo-wrap">
        <form method="POST" action="">
          <input type="hidden" name="product_id" value="<?= $pid; ?>">
          <input type="hidden" name="promo_id" value="<?= $promo ? (int)$promo['id'] : 0; ?>">

          <div class="promo-row">
            <div>
              <label>Promo Price (optional)</label>
              <input type="number" step="0.01" name="promo_price" class="box"
                     value="<?= $promo && $promo['promo_price'] !== null ? htmlspecialchars($promo['promo_price']) : ''; ?>"
                     placeholder="e.g. 2499.00">
            </div>
            <div>
              <label>Discount % (optional)</label>
              <input type="number" step="0.01" name="discount_percent" class="box"
                     value="<?= $promo && $promo['discount_percent'] !== null ? htmlspecialchars($promo['discount_percent']) : ''; ?>"
                     placeholder="e.g. 20">
            </div>
          </div>

          <div class="promo-row">
            <div>
              <label>Label</label>
              <input type="text" name="label" class="box"
                     value="<?= $promo ? htmlspecialchars($promo['label']) : 'Limited Offer'; ?>"
                     maxlength="60">
            </div>
            <div class="flex" style="align-items:center; gap:10px; margin-top:24px;">
              <input type="checkbox" id="active_<?= $pid; ?>" name="active"
                     <?= $promo ? ((int)$promo['active']===1 ? 'checked' : '') : 'checked'; ?>>
              <label for="active_<?= $pid; ?>">Active</label>
            </div>
          </div>

          <div class="promo-row">
            <div>
              <label>Starts At (YYYY-MM-DD HH:MM:SS)</label>
              <input type="text" name="starts_at" class="box"
                     value="<?= $promo && !empty($promo['starts_at']) ? htmlspecialchars($promo['starts_at']) : ''; ?>">
            </div>
            <div>
              <label>Ends At (YYYY-MM-DD HH:MM:SS)</label>
              <input type="text" name="ends_at" class="box"
                     value="<?= $promo && !empty($promo['ends_at']) ? htmlspecialchars($promo['ends_at']) : ''; ?>">
            </div>
          </div>

          <div class="promo-actions">
            <button type="submit" name="save_promo" class="btn">Save Promotion</button>
            <?php if($promo): ?>
              <a href="admin_products.php?delete_promo=<?= (int)$promo['id']; ?>&pid=<?= $pid; ?>"
                 class="btn-danger" onclick="return confirm('Delete this promotion?');">
                 Delete Promotion
              </a>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <div class="flex-btn" style="margin-top:12px;">
         <a href="admin_update_product.php?update=<?= $pid; ?>" class="option-btn">update</a>
         <a href="admin_products.php?delete=<?= $pid; ?>" class="delete-btn" onclick="return confirm('delete this product?');">delete</a>
      </div>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">now products added yet!</p>';
      }
   ?>

   </div>

</section>

<script src="js/script.js"></script>
</body>
</html>