<?php
/* ============================================================
   ADMIN: Fraud / Activity Monitor
   Flag suspicious users and vendors (high cancellation rates, etc)
   ============================================================ */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';
$required_role = ROLE_ADMIN;
require_once '../../templates/layouts/auth_wrapper.php';

$db = Database::getInstance();

// 1. High Cancellation Users
// Users who have > 3 cancelled/disputed orders
$suspicious_users = $db->query(
    "SELECT u.id, u.name, u.email, u.phone, COUNT(o.id) as bad_orders 
     FROM users u 
     JOIN orders o ON o.user_id = u.id 
     WHERE o.order_status IN ('cancelled', 'disputed') 
     GROUP BY u.id 
     HAVING bad_orders >= 3 
     ORDER BY bad_orders DESC 
     LIMIT 20"
);

// 2. High Rejection Vendors
// Vendors whose items are frequently rejected/cancelled
$suspicious_vendors = $db->query(
    "SELECT v.id, v.shop_name, u.name as owner, u.email, COUNT(oi.id) as rejected_items 
     FROM vendors v 
     JOIN users u ON u.id = v.user_id 
     JOIN order_items oi ON oi.vendor_id = v.id 
     WHERE oi.item_status = 'rejected' 
     GROUP BY v.id 
     HAVING rejected_items >= 5 
     ORDER BY rejected_items DESC 
     LIMIT 20"
);

// 3. Very High Value Orders recently (anomaly detection)
// Arbitrary threshold: orders > ₹5000 in the last 7 days
$high_value_orders = $db->query(
    "SELECT o.id, o.order_number, o.total_amount, o.order_status, u.name as customer_name, o.created_at 
     FROM orders o 
     JOIN users u ON u.id = o.user_id 
     WHERE o.total_amount > 5000 
     AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
     ORDER BY o.total_amount DESC 
     LIMIT 20"
);

$base = BASE_URL;
$page_title = 'Activity & Fraud Monitor';
$active_page = 'fraud_monitor.php';
$sidebar_role = 'admin';
require_once '../../templates/layouts/header.php';
?>

<div class="dashboard-layout">
  <?php require_once '../../templates/layouts/sidebar.php'; ?>

  <main class="dashboard-main">
    <div class="flex items-center gap-3 mb-6">
      <h1 style="font-size:var(--text-2xl)">🚨 Activity Monitor</h1>
      <span class="text-muted text-sm border-left" style="padding-left:12px; border-left: 2px solid var(--color-border)">Automated heuristic flags</span>
    </div>

    <div class="alert alert-warning mb-6">
      <strong>Note:</strong> These are automated heuristic flags. A flag does not guarantee fraud. Please investigate manually before suspending accounts.
    </div>

    <!-- Suspicious Users -->
    <div class="card mb-6">
      <div style="padding:var(--space-4) var(--space-5); border-bottom:1px solid var(--color-border); background:rgba(220,53,69,0.05)">
        <h3 class="m-0 text-danger" style="font-size:var(--text-lg)">🚩 High Cancellation Users</h3>
        <p class="text-xs text-muted mt-1 m-0">Users with 3 or more cancelled/disputed orders.</p>
      </div>
      <div class="p-0">
        <?php if ($suspicious_users): ?>
          <div class="table-wrapper">
            <table class="table">
              <thead><tr><th>User</th><th>Email</th><th>Phone</th><th>Cancelled Orders</th><th>Action</th></tr></thead>
              <tbody>
                <?php foreach ($suspicious_users as $u): ?>
                  <tr>
                    <td class="fw-semibold text-sm"><?= htmlspecialchars($u['name']) ?></td>
                    <td class="text-sm"><?= htmlspecialchars($u['email']) ?></td>
                    <td class="text-sm"><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
                    <td><span class="badge badge-danger"><?= $u['bad_orders'] ?></span></td>
                    <td><a href="<?= $base ?>/pages/admin/users.php?q=<?= urlencode($u['email']) ?>" class="btn btn-sm btn-outline-danger">Investigate</a></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="text-muted text-sm p-4 m-0 text-center">No suspicious users flagged.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Suspicious Vendors -->
    <div class="card mb-6">
      <div style="padding:var(--space-4) var(--space-5); border-bottom:1px solid var(--color-border); background:rgba(253,126,20,0.05)">
        <h3 class="m-0 text-warning" style="font-size:var(--text-lg)">🚩 Vendors with High Rejections</h3>
        <p class="text-xs text-muted mt-1 m-0">Vendors who have rejected 5 or more items.</p>
      </div>
      <div class="p-0">
        <?php if ($suspicious_vendors): ?>
          <div class="table-wrapper">
            <table class="table">
              <thead><tr><th>Shop Name</th><th>Owner</th><th>Email</th><th>Total Rejected Items</th><th>Action</th></tr></thead>
              <tbody>
                <?php foreach ($suspicious_vendors as $v): ?>
                  <tr>
                    <td class="fw-semibold text-sm"><?= htmlspecialchars($v['shop_name']) ?></td>
                    <td class="text-sm"><?= htmlspecialchars($v['owner']) ?></td>
                    <td class="text-sm"><?= htmlspecialchars($v['email']) ?></td>
                    <td><span class="badge badge-warning"><?= $v['rejected_items'] ?></span></td>
                    <td><a href="<?= $base ?>/pages/admin/vendor_verify.php?id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-warning">Investigate</a></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="text-muted text-sm p-4 m-0 text-center">No suspicious vendors flagged.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- High Value Anomalies -->
    <div class="card mb-6">
      <div style="padding:var(--space-4) var(--space-5); border-bottom:1px solid var(--color-border); background:rgba(13,110,253,0.05)">
        <h3 class="m-0 text-primary" style="font-size:var(--text-lg)">🔍 High Value Orders (7 Days)</h3>
        <p class="text-xs text-muted mt-1 m-0">Orders exceeding ₹5000 in total value.</p>
      </div>
      <div class="p-0">
        <?php if ($high_value_orders): ?>
          <div class="table-wrapper">
            <table class="table">
              <thead><tr><th>Order #</th><th>Customer</th><th>Date</th><th>Status</th><th>Total Amount</th><th>Action</th></tr></thead>
              <tbody>
                <?php foreach ($high_value_orders as $o): ?>
                  <tr>
                    <td class="fw-mono text-sm"><?= htmlspecialchars($o['order_number']) ?></td>
                    <td class="text-sm"><?= htmlspecialchars($o['customer_name']) ?></td>
                    <td class="text-sm text-muted"><?= date('d M, h:i A', strtotime($o['created_at'])) ?></td>
                    <td>
                      <?php $st = ORDER_STATUSES[$o['order_status']] ?? ['label'=>ucfirst($o['order_status']), 'color'=>'#6c757d']; ?>
                      <span class="badge" style="background:<?= $st['color'] ?>20; color:<?= $st['color'] ?>"><?= $st['label'] ?></span>
                    </td>
                    <td class="fw-bold text-primary">₹<?= number_format($o['total_amount'], 2) ?></td>
                    <td><a href="<?= $base ?>/pages/admin/orders.php?q=<?= htmlspecialchars($o['order_number']) ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="text-muted text-sm p-4 m-0 text-center">No high value orders in the last 7 days.</p>
        <?php endif; ?>
      </div>
    </div>

  </main>
</div>

<script src="<?= $base ?>/assets/js/utils.js"></script>
</body>
</html>
