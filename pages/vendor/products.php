<?php
/* ===== BACKEND ===== */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';
$required_role = ROLE_VENDOR;
require_once '../../templates/layouts/auth_wrapper.php';

$db = Database::getInstance();
$user = current_user();

// Verify vendor status
$vendor = $db->prepareOne("SELECT id, verification_status FROM vendors WHERE user_id = ? LIMIT 1", 'i', $user['id']);
if (!$vendor || $vendor['verification_status'] !== VENDOR_APPROVED) {
    header('Location: ' . BASE_URL . '/pages/vendor/onboarding.php');
    exit;
}
$vid = (int)$vendor['id'];
$errors  = [];
$success = '';

// --- Handle Add/Edit/Delete ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === CSRF_TOKEN) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $pid        = (int)($_POST['product_id'] ?? 0);
        $name       = trim($_POST['name'] ?? '');
        $desc       = trim($_POST['description'] ?? '');
        $cat        = (int)($_POST['category_id'] ?? 0);
        $price      = (float)($_POST['price'] ?? 0);
        $disc_price = ($_POST['discount_price'] !== '' ? (float)$_POST['discount_price'] : null);
        $unit       = trim($_POST['unit'] ?? 'piece');
        $stock      = (int)($_POST['stock'] ?? 0);
        $featured   = (int)isset($_POST['is_featured']);
        $slug       = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name)) . '-' . time();

        if (!$name || !$cat || $price <= 0) {
            $errors[] = 'Name, category, and price are required.';
        }

        // --- File Upload Logic ---
        $uploaded_images = [];
        if (isset($_FILES['product_images']) && !empty($_FILES['product_images']['name'][0])) {
            $files = $_FILES['product_images'];
            $upload_dir = '../../assets/uploads/products/';
            
            // Ensure directory exists
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === 0) {
                    $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $new_name = 'prod_' . uniqid() . '_' . time() . '.' . $ext;
                        if (move_uploaded_file($files['tmp_name'][$i], $upload_dir . $new_name)) {
                            $uploaded_images[] = 'assets/uploads/products/' . $new_name;
                        }
                    } else {
                        $errors[] = "Invalid file type for: " . $files['name'][$i];
                    }
                }
            }
        }

        if (empty($errors)) {
            if ($action === 'add') {
                $images_json = json_encode($uploaded_images);
                $db->execute(
                    "INSERT INTO products (vendor_id, category_id, name, slug, description, price, discount_price, unit, stock, is_featured, is_active, images) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)",
                    'iisssddsiis', $vid, $cat, $name, $slug, $desc, $price, $disc_price, $unit, $stock, $featured, $images_json
                );
                $success = 'Product added successfully!';
            } else {
                // For edit: Get existing images if no new ones uploaded, or merge
                $existing = $db->prepareOne("SELECT images FROM products WHERE id = ? AND vendor_id = ?", 'ii', $pid, $vid);
                $existing_imgs = json_decode($existing['images'] ?? '[]', true);
                
                // If new images uploaded, we add them (or replace? Let's append for now)
                $final_images = !empty($uploaded_images) ? array_merge($existing_imgs, $uploaded_images) : $existing_imgs;
                $images_json = json_encode($final_images);

                $db->execute(
                    "UPDATE products SET category_id=?, name=?, description=?, price=?, discount_price=?, unit=?, stock=?, is_featured=?, images=? 
                     WHERE id=? AND vendor_id=?",
                    'issddsiisii', $cat, $name, $desc, $price, $disc_price, $unit, $stock, $featured, $images_json, $pid, $vid
                );
                $success = 'Product updated successfully!';
            }
        }
    } elseif ($action === 'toggle_status') {
        $pid = (int)$_POST['product_id'];
        $db->execute("UPDATE products SET is_active = 1 - is_active WHERE id = ? AND vendor_id = ?", 'ii', $pid, $vid);
        $success = 'Status updated.';
    } elseif ($action === 'delete') {
        $pid = (int)$_POST['product_id'];
        $db->execute("DELETE FROM products WHERE id = ? AND vendor_id = ?", 'ii', $pid, $vid);
        $success = 'Product deleted.';
    }
}

// --- Fetch Data ---
$categories = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC");
$page   = (int)($_GET['page'] ?? 1);
$per    = 10;
$offset = ($page - 1) * $per;

$cat_filter = (int)($_GET['cat'] ?? 0);
$q_filter   = trim($_GET['q'] ?? '');

$where = "p.vendor_id = $vid";
if ($cat_filter) $where .= " AND p.category_id = $cat_filter";
if ($q_filter)   $where .= " AND p.name LIKE '%" . $db->escape($q_filter) . "%'";

$total = (int)($db->queryOne("SELECT COUNT(*) AS c FROM products p WHERE $where")['c'] ?? 0);
$total_pages = max(1, ceil($total / $per));

$products = $db->query(
    "SELECT p.*, c.name AS cat_name 
     FROM products p 
     JOIN categories c ON c.id = p.category_id 
     WHERE $where 
     ORDER BY p.created_at DESC 
     LIMIT $per OFFSET $offset"
);

$edit_id = (int)($_GET['edit'] ?? 0);
$edit_product = $edit_id ? $db->prepareOne("SELECT * FROM products WHERE id = ? AND vendor_id = ?", 'ii', $edit_id, $vid) : null;

/* ===== FRONTEND ===== */
$page_title  = 'My Products';
$active_page = 'products.php';
$sidebar_role = 'vendor';
$body_class  = 'is-dashboard';
require_once '../../templates/layouts/header.php';
?>

<div class="dashboard-layout">
  <?php require_once '../../templates/layouts/sidebar.php'; ?>
  
  <main class="dashboard-main">
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-2xl fw-bold">🧺 My Products</h1>
        <p class="text-muted text-sm">Manage your inventory and pricing</p>
      </div>
      <button class="btn btn-primary" onclick="openModal('product-modal')">+ Add New Product</button>
    </div>

    <!-- Alert Messages -->
    <?php if ($success): ?>
      <div class="alert alert-success mb-6 p-4 rounded-md flex items-start gap-3">
        <span>✅</span> <div><?= htmlspecialchars($success) ?></div>
      </div>
    <?php endif; ?>
    <?php if ($errors): ?>
      <div class="alert alert-danger mb-6 p-4 rounded-md">
        <?php foreach($errors as $e): ?><div>⚠️ <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Filter Bar -->
    <div class="card p-4 mb-6">
      <form method="GET" class="flex items-center gap-4 flex-wrap">
        <div style="flex:1; min-width:200px;">
          <input type="text" name="q" class="form-control" placeholder="Search by name..." value="<?= htmlspecialchars($q_filter) ?>">
        </div>
        <select name="cat" class="form-control" style="width:200px;" onchange="this.form.submit()">
          <option value="">All Categories</option>
          <?php foreach($categories as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $cat_filter == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-ghost">Filter</button>
        <?php if ($q_filter || $cat_filter): ?>
          <a href="products.php" class="text-xs text-muted">Clear all</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Products Table -->
    <?php if ($products): ?>
      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Category</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Featured</th>
              <th>Status</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($products as $p): 
              $imgs = json_decode($p['images'] ?? '[]', true);
              $main_img = !empty($imgs[0]) ? BASE_URL . '/' . $imgs[0] : 'https://placehold.co/50x50/f0f0f0/999?text=P';
            ?>
              <tr>
                <td>
                  <div class="flex items-center gap-3">
                    <img src="<?= $main_img ?>" class="rounded-md" style="width:48px; height:48px; object-fit:cover;" onerror="this.src='https://placehold.co/50x50/f0f0f0/999?text=P'">
                    <div>
                      <div class="fw-bold"><?= htmlspecialchars($p['name']) ?></div>
                      <div class="text-xs text-muted"><?= htmlspecialchars($p['unit']) ?></div>
                    </div>
                  </div>
                </td>
                <td class="text-sm"><?= htmlspecialchars($p['cat_name']) ?></td>
                <td>
                  <div class="fw-bold text-primary">₹<?= number_format($p['price'], 2) ?></div>
                  <?php if ($p['discount_price']): ?>
                    <div class="text-xs text-success">Sale: ₹<?= number_format($p['discount_price'], 2) ?></div>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="<?= $p['stock'] < 10 ? 'text-danger fw-bold' : '' ?>"><?= $p['stock'] ?></span>
                </td>
                <td class="text-center"><?= $p['is_featured'] ? '⭐' : '-' ?></td>
                <td>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= CSRF_TOKEN ?>">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                    <button type="submit" class="badge <?= $p['is_active'] ? 'badge-success' : 'badge-danger' ?>" style="border:none; cursor:pointer;">
                      <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
                    </button>
                  </form>
                </td>
                <td class="text-right">
                  <div class="flex justify-end gap-2">
                    <a href="?edit=<?= $p['id'] ?>" class="btn btn-icon btn-ghost-sm" title="Edit">✏️</a>
                    <form method="POST" onsubmit="return confirm('Delete this product permanently?')" style="display:inline;">
                      <input type="hidden" name="csrf_token" value="<?= CSRF_TOKEN ?>">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                      <button type="submit" class="btn btn-icon btn-ghost-sm text-danger" title="Delete">🗑</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div class="mt-8">
        <?php 
         $current_page = $page; 
         $base_url = '?' . http_build_query(array_filter(['q'=>$q_filter, 'cat'=>$cat_filter])); 
         include '../../templates/components/pagination.php'; 
        ?>
      </div>

    <?php else: ?>
      <div class="empty-state card p-10 mt-10">
        <div class="empty-icon" style="font-size:3rem;">🧺</div>
        <h3 class="mt-4">No products found</h3>
        <p class="text-muted">Start adding your inventory to showcase it on the marketplace.</p>
        <button class="btn btn-primary mt-6" onclick="openModal('product-modal')">+ Add Your First Product</button>
      </div>
    <?php endif; ?>
  </main>
</div>

<!-- Add/Edit Product Modal -->
<?php
$ep = $edit_product;
$modal_id = 'product-modal';
$modal_title = $ep ? 'Edit Product: ' . htmlspecialchars($ep['name']) : 'Add New Product';
$modal_size = 'large';

ob_start();
?>
<form method="POST" id="product-form" enctype="multipart/form-data" class="flex-col gap-6">
  <input type="hidden" name="csrf_token" value="<?= CSRF_TOKEN ?>">
  <input type="hidden" name="action" value="<?= $ep ? 'edit' : 'add' ?>">
  <?php if ($ep): ?><input type="hidden" name="product_id" value="<?= $ep['id'] ?>"><?php endif; ?>

  <div class="grid grid-cols-2 gap-6">
    <div class="form-group col-span-full">
      <label class="form-label fw-bold" for="name">Product Title *</label>
      <input type="text" class="form-control" id="name" name="name" required placeholder="e.g. Organic Cavendish Bananas" value="<?= htmlspecialchars($ep['name'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label class="form-label fw-bold" for="category_id">Category *</label>
      <select class="form-control" id="category_id" name="category_id" required>
        <option value="">Select Category</option>
        <?php foreach($categories as $c): ?>
          <option value="<?= $c['id'] ?>" <?= ($ep && $ep['category_id'] == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label class="form-label fw-bold" for="unit">Unit (e.g. 500g, 1kg, 1 Piece) *</label>
      <input type="text" class="form-control" id="unit" name="unit" required placeholder="1kg" value="<?= htmlspecialchars($ep['unit'] ?? 'piece') ?>">
    </div>

    <div class="form-group">
      <label class="form-label fw-bold" for="price">Standard Price (₹) *</label>
      <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required placeholder="0.00" value="<?= $ep['price'] ?? '' ?>">
    </div>

    <div class="form-group">
      <label class="form-label fw-bold" for="discount_price">Discount Price (₹) <small class="text-muted">(Optional)</small></label>
      <input type="number" class="form-control" id="discount_price" name="discount_price" step="0.01" min="0" placeholder="0.00" value="<?= $ep['discount_price'] ?? '' ?>">
    </div>

    <div class="form-group">
      <label class="form-label fw-bold" for="stock">Available Stock *</label>
      <input type="number" class="form-control" id="stock" name="stock" min="0" required placeholder="100" value="<?= $ep['stock'] ?? 0 ?>">
    </div>

    <div class="form-group flex items-center gap-2 mt-4">
      <input type="checkbox" id="is_featured" name="is_featured" value="1" <?= ($ep && $ep['is_featured']) ? 'checked' : '' ?>>
      <label class="form-label" for="is_featured" style="margin:0;">Make this a "Featured Product"</label>
    </div>

    <div class="form-group col-span-full">
      <label class="form-label fw-bold">Product Images</label>
      <div class="p-4 rounded-lg" style="background:var(--color-bg); border:1px dashed var(--color-border);">
        <input type="file" name="product_images[]" id="product_images" multiple accept="image/*" class="mb-4">
        <p class="text-xs text-muted">Upload high-quality images (JPEG, PNG, WEBP). Max 5MB each. First image will be the primary one.</p>
        
        <?php if ($ep): 
           $imgs = json_decode($ep['images'] ?? '[]', true);
           if (!empty($imgs)):
        ?>
          <div class="flex gap-2 mt-4 flex-wrap">
            <?php foreach($imgs as $img): ?>
              <div class="relative group">
                <img src="<?= BASE_URL . '/' . $img ?>" style="width:60px; height:60px; object-fit:cover;" class="rounded border">
              </div>
            <?php endforeach; ?>
          </div>
          <p class="text-xs text-muted mt-2">New uploads will be added to the existing gallery.</p>
        <?php endif; endif; ?>
      </div>
    </div>

    <div class="form-group col-span-full">
      <label class="form-label fw-bold" for="description">Detailed Description</label>
      <textarea class="form-control" id="description" name="description" rows="4" placeholder="Describe the quality, origin, and any other details..."><?= htmlspecialchars($ep['description'] ?? '') ?></textarea>
    </div>
  </div>
</form>

<?php $modal_body = ob_get_clean(); 
$modal_footer = '<button class="btn btn-ghost" onclick="closeModal(\'product-modal\')">Cancel</button><button class="btn btn-primary" onclick="document.getElementById(\'product-form\').submit()">Save Product</button>';
require_once '../../templates/components/modal.php';
?>

<script src="<?= BASE_URL ?>/assets/js/utils.js"></script>
<?php if ($edit_product): ?>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    openModal('product-modal');
  });
</script>
<?php endif; ?>
</body>
</html>
