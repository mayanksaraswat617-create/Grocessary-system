<?php
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';
$required_role = ROLE_CUSTOMER;
require_once '../../templates/layouts/auth_wrapper.php';

$db = Database::getInstance();
$user = current_user();
$page = max(1,(int)($_GET['page']??1));
$per  = 10; $offset = ($page-1)*$per;
$status_filter = $_GET['status'] ?? '';

$where = "o.user_id=?";
$types = 'i'; $params = [$user['id']];
if ($status_filter) { $where .= " AND o.order_status=?"; $types .= 's'; $params[] = $status_filter; }

$total_count = (int)($db->prepareOne("SELECT COUNT(*) AS cnt FROM orders o WHERE $where", $types, ...$params)['cnt'] ?? 0);
$total_pages = max(1, ceil($total_count/$per));
$orders = $db->prepare(
    "SELECT o.*, (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id=o.id) AS item_count
     FROM orders o WHERE $where ORDER BY o.created_at DESC LIMIT $per OFFSET $offset",
    $types, ...$params
);

$base = BASE_URL;
$page_title = 'My Orders';
require_once '../../templates/layouts/header.php';
require_once '../../templates/layouts/navbar.php';
?>
<div class="page-content" style="background:var(--color-bg)">
  <div class="container" style="padding-top:var(--space-7);padding-bottom:var(--space-8)">
    <div class="flex items-center justify-between mb-6">
      <h1 style="font-size:var(--text-3xl)">📦 My Orders</h1>
    </div>

    <!-- Status Filter -->
    <div class="flex gap-2 flex-wrap mb-6">
      <a href="?" class="badge <?= !$status_filter?'badge-primary':'badge-muted' ?>" style="padding:8px 16px;text-decoration:none">All</a>
      <?php foreach(ORDER_STATUSES as $k=>$s): ?>
        <a href="?status=<?= $k ?>" class="badge <?= $status_filter===$k?'badge-primary':'badge-muted' ?>" style="padding:8px 16px;text-decoration:none"><?= $s['label'] ?></a>
      <?php endforeach; ?>
    </div>

    <?php if (!$orders): ?>
      <div class="empty-state"><div class="empty-icon">📦</div><h3>No orders yet</h3><p>When you place an order, it'll appear here.</p><a href="<?= $base ?>/pages/customer/browse.php" class="btn btn-primary">Start Shopping</a></div>
    <?php else: ?>
      <div class="table-wrapper">
        <table class="table">
          <thead><tr><th>Order #</th><th>Date</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
            <?php foreach($orders as $o):
              $st = ORDER_STATUSES[$o['order_status']] ?? ['label'=>ucfirst($o['order_status']),'color'=>'#6c757d'];
            ?>
              <tr>
                <td><span class="fw-bold"><?= htmlspecialchars($o['order_number']) ?></span></td>
                <td class="text-sm text-muted"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                <td class="text-sm"><?= $o['item_count'] ?> item<?= $o['item_count']!=1?'s':'' ?></td>
                <td class="fw-bold text-primary">₹<?= number_format($o['total_amount'],2) ?></td>
                <td><span class="badge badge-muted"><?= PAYMENT_LABELS[$o['payment_method']] ?? $o['payment_method'] ?></span></td>
                <td><span class="badge" style="background:<?= $st['color'] ?>20;color:<?= $st['color'] ?>"><?= $st['label'] ?></span></td>
                <td><a href="<?= $base ?>/pages/customer/order_detail.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary">View →</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php $current_page=$page; $base_url='?'.http_build_query(array_filter($_GET,fn($k)=>$k!=='page',ARRAY_FILTER_USE_KEY)); include '../../templates/components/pagination.php'; ?>
    <?php endif; ?>
  </div>
</div>
<?php require_once '../../templates/layouts/footer.php'; ?>
