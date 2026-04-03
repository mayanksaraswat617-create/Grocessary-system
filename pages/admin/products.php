<?php
/* ============================================================
   ADMIN: Product Monitor
   View all products across platform and suspend/activate them
   ============================================================ */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';
$required_role = ROLE_ADMIN;
require_once '../../templates/layouts/auth_wrapper.php';

$db = Database::getInstance();
$success = '';

// Toggle status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === CSRF_TOKEN) {
    if (isset($_POST['toggle_id'])) {
        $tid = (int)$_POST['toggle_id'];
        $db->execute("UPDATE products SET is_active = NOT is_active WHERE id = ?", 'i', $tid);
        $success = 'Product status updated.';
    }
}

// Filters & Pagination
$q = trim($_GET['q'] ?? '');
$vendor_filter = (int)($_GET['vendor_id'] ?? 0);
$page = (int)($_GET['page'] ?? 1);
$per = 15;
$offset = ($page - 1) * $per;

$where = "1=1";
$params = []; $types = "";

if ($q) {
    $where .= " AND (p.name LIKE ? OR c.name LIKE ? OR v.shop_name LIKE ?)";
    $like = "%$q%";
    $params = [$like, $like, $like];
    $types .= "sss";
}
if ($vendor_filter) {
    $where .= " AND p.vendor_id = ?";
    $params[] = $vendor_filter;
    $types .= "i";
}

$count_sql = "SELECT COUNT(*) AS c FROM products p JOIN categories c ON c.id=p.category_id JOIN vendors v ON v.id=p.vendor_id WHERE $where";
$total = (int)($types ? $db->prepareOne($count_sql, $types, ...$params)['c'] : $db->queryOne($count_sql)['c']);
$total_pages = max(1, ceil($total / $per));

$sql = "SELECT p.*, c.name AS category_name, v.shop_name 
        FROM products p 
        JOIN categories c ON c.id=p.category_id 
        JOIN vendors v ON v.id=p.vendor_id 
        WHERE $where 
        ORDER BY p.created_at DESC 
        LIMIT ? OFFSET ?";
$params[] = $per; $types .= "i";
$params[] = $offset; $types .= "i";

$products = $db->prepare($sql, $types, ...$params);
$vendors_list = $db->query("SELECT id, shop_name FROM vendors ORDER BY shop_name");

$base = BASE_URL;
$page_title = 'Product Monitor';
$active_page = 'products.php';
$sidebar_role = 'admin';
require_once '../../templates/layouts/header.php';
?>

<div class="dashboard-layout">
  <?php require_once '../../templates/layouts/sidebar.php'; ?>

  <main class="dashboard-main">
    <div class="flex justify-between items-center mb-6">
      <h1 style="font-size:var(--text-2xl)">📦 Product Monitor</h1>
      <span class="badge badge-primary">Total: <?= $total ?></span>
    </div>

    <?php if ($success): ?><div class="alert alert-success mb-4">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>

    <!-- Filters -->
    <div class="card p-4 mb-6">
      <form method="GET" class="flex gap-3 flex-wrap">
        <div class="input-group-icon" style="flex:1; min-width:250px">
          <span class="icon">🔍</span>
          <input type="text" class="form-control" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search product, category, vendor...">
        </div>
        <select class="form-control" name="vendor_id" style="width:200px">
          <option value="">All Vendors</option>
          <?php foreach ($vendors_list as $vl): ?>
            <option value="<?= $vl['id'] ?>" <?= $vendor_filter === $vl['id'] ? 'selected' : '' ?>><?= htmlspecialchars($vl['shop_name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        <?php if ($q || $vendor_filter): ?><a href="products.php" class="btn btn-ghost">Clear</a><?php endif; ?>
      </form>
    </div>

    <?php if ($products): ?>
      <div class="card table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Vendor</th>
              <th>Category</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $p): ?>
              <tr>
                <td>
                  <div class="fw-semibold">
                    <a href="<?= $base ?>/pages/customer/product_detail.php?id=<?= $p['id'] ?>" target="_blank" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:8px">
                      <?= htmlspecialchars($p['name']) ?> ↗
                    </a>
                  </div>
                </td>
                <td class="text-sm">
                  <a href="<?= $base ?>/pages/admin/vendor_verify.php?id=<?= $p['vendor_id'] ?>"><?= htmlspecialchars($p['shop_name']) ?></a>
                </td>
                <td class="text-sm text-muted"><?= htmlspecialchars($p['category_name']) ?></td>
                <td class="fw-semibold">₹<?= number_format($p['price'], 2) ?></td>
                <td>
                  <?php if ($p['stock'] <= 5): ?><span class="badge badge-danger"><?= $p['stock'] ?> Left</span>
                  <?php else: ?><span class="badge badge-success"><?= $p['stock'] ?></span><?php endif; ?>
                </td>
                <td>
                  <?php if ($p['is_active']): ?><span class="badge badge-success">Active</span>
                  <?php else: ?><span class="badge badge-danger">Suspended</span><?php endif; ?>
                </td>
                <td>
                  <form method="POST" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?= CSRF_TOKEN ?>">
                    <input type="hidden" name="toggle_id" value="<?= $p['id'] ?>">
                    <?php if ($p['is_active']): ?>
                      <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Suspend this product?')">Suspend</button>
                    <?php else: ?>
                      <button type="submit" class="btn btn-sm btn-outline-success">Activate</button>
                    <?php endif; ?>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php 
        $current_page = $page; 
        $base_url = '?' . http_build_query(array_filter(['q' => $q, 'vendor_id' => $vendor_filter])); 
        include '../../templates/components/pagination.php';
      ?>
    <?php else: ?>
      <div class="empty-state card p-5">
        <div class="empty-icon">📦</div>
        <h3>No products found</h3>
        <p class="text-muted">No products match your search criteria.</p>
      </div>
    <?php endif; ?>
  </main>
</div>

<script src="<?= $base ?>/assets/js/utils.js"></script>
</body>
</html>
