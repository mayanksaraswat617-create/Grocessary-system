<?php
/* ============================================================
   ADMIN: Commissions Management
   View and update vendor commission rates
   ============================================================ */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';
$required_role = ROLE_ADMIN;
require_once '../../templates/layouts/auth_wrapper.php';

$db = Database::getInstance();
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === CSRF_TOKEN) {
    if (isset($_POST['vendor_id']) && isset($_POST['commission_rate'])) {
        $vid = (int)$_POST['vendor_id'];
        $rate = (float)$_POST['commission_rate'];
        if ($rate >= 0 && $rate <= 100) {
            $db->execute("UPDATE vendors SET commission_rate = ? WHERE id = ?", 'di', $rate, $vid);
            $success = "Commission rate updated successfully.";
        }
    }
}

$q = trim($_GET['q'] ?? '');
$page = (int)($_GET['page'] ?? 1);
$per = 15;
$offset = ($page - 1) * $per;

$where = "v.verification_status='approved'";
if ($q) {
    $where .= " AND (v.shop_name LIKE '%" . addslashes($q) . "%' OR u.name LIKE '%" . addslashes($q) . "%')";
}

$total = (int)($db->queryOne("SELECT COUNT(*) AS c FROM vendors v JOIN users u ON u.id=v.user_id WHERE $where")['c'] ?? 0);
$total_pages = max(1, ceil($total / $per));

$vendors = $db->query(
    "SELECT v.id, v.shop_name, v.commission_rate, u.name AS owner_name 
     FROM vendors v 
     JOIN users u ON u.id=v.user_id 
     WHERE $where 
     ORDER BY v.shop_name ASC 
     LIMIT $per OFFSET $offset"
);

// Aggregates for context
$stats = [
    'avg_rate' => $db->queryOne("SELECT AVG(commission_rate) AS a FROM vendors WHERE verification_status='approved'")['a'] ?? DEFAULT_COMMISSION,
    'total_com' => $db->queryOne("SELECT SUM(commission) AS s FROM order_items")['s'] ?? 0
];

$base = BASE_URL;
$page_title = 'Commission Rates';
$active_page = 'commissions.php';
$sidebar_role = 'admin';
require_once '../../templates/layouts/header.php';
?>

<div class="dashboard-layout">
  <?php require_once '../../templates/layouts/sidebar.php'; ?>

  <main class="dashboard-main">
    <div class="flex justify-between items-center mb-6">
      <h1 style="font-size:var(--text-2xl)">🎯 Commission Management</h1>
    </div>

    <!-- Quick Stats -->
    <div class="stat-grid" style="grid-template-columns:1fr 1fr; gap:var(--space-6); margin-bottom:var(--space-6)">
      <div class="stat-card">
        <div class="stat-icon">📈</div>
        <div class="stat-value"><?= number_format((float)$stats['avg_rate'], 1) ?>%</div>
        <div class="stat-label">Average Commission Rate</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-value">₹<?= number_format((float)$stats['total_com'], 2) ?></div>
        <div class="stat-label">Total Commission Earned</div>
      </div>
    </div>

    <?php if ($success): ?><div class="alert alert-success mb-4">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div class="card p-6">
      <div class="flex justify-between items-center mb-5">
        <h3 class="m-0">Vendor Commissions</h3>
        <form method="GET" class="flex gap-2">
          <input type="text" class="form-control" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search vendor...">
          <button type="submit" class="btn btn-outline-primary btn-sm">Search</button>
        </form>
      </div>

      <div class="alert alert-info mb-4" style="font-size:var(--text-sm)">
        ℹ️ The default commission platform-wide is <strong><?= DEFAULT_COMMISSION ?>%</strong>. Adjust rates per vendor based on promotional offers, premium placement, or volume tiers.
      </div>

      <?php if ($vendors): ?>
        <div class="table-wrapper">
          <table class="table">
            <thead>
              <tr>
                <th>Shop Name</th>
                <th>Owner</th>
                <th>Current Rate</th>
                <th>Update Rate (%)</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($vendors as $v): ?>
                <tr>
                  <td class="fw-semibold">
                    <a href="<?= $base ?>/pages/admin/vendor_verify.php?id=<?= $v['id'] ?>"><?= htmlspecialchars($v['shop_name']) ?></a>
                  </td>
                  <td class="text-sm"><?= htmlspecialchars($v['owner_name']) ?></td>
                  <td>
                    <?php if ($v['commission_rate'] == DEFAULT_COMMISSION): ?>
                      <span class="badge badge-muted"><?= $v['commission_rate'] ?>% (Default)</span>
                    <?php else: ?>
                      <span class="badge badge-primary"><?= $v['commission_rate'] ?>% (Custom)</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <form method="POST" class="flex gap-2" style="max-width:200px">
                      <input type="hidden" name="csrf_token" value="<?= CSRF_TOKEN ?>">
                      <input type="hidden" name="vendor_id" value="<?= $v['id'] ?>">
                      <input type="number" step="0.1" min="0" max="100" class="form-control text-sm" style="padding:4px 8px" name="commission_rate" value="<?= $v['commission_rate'] ?>" required>
                      <button type="submit" class="btn btn-sm btn-primary">Save</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        
        <?php 
          $current_page = $page; 
          $base_url = '?' . http_build_query(array_filter(['q' => $q])); 
          include '../../templates/components/pagination.php';
        ?>
      <?php else: ?>
        <p class="text-muted text-center py-5">No vendors found.</p>
      <?php endif; ?>
    </div>
  </main>
</div>

<script src="<?= $base ?>/assets/js/utils.js"></script>
</body>
</html>
