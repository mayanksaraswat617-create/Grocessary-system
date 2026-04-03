<?php
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';
$required_role = ROLE_CUSTOMER;
require_once '../../templates/layouts/auth_wrapper.php';

$db = Database::getInstance();
$user = current_user();
$oid = (int)($_GET['id'] ?? 0);
if (!$oid) { header('Location:'.BASE_URL.'/pages/customer/orders.php'); exit; }

$order = $db->prepareOne("SELECT * FROM orders WHERE id=? AND user_id=? LIMIT 1", 'ii', $oid, $user['id']);
if (!$order) { header('Location:'.BASE_URL.'/pages/customer/orders.php'); exit; }

$items = $db->prepare("SELECT oi.*, v.shop_name FROM order_items oi JOIN vendors v ON v.id=oi.vendor_id WHERE oi.order_id=? ORDER BY v.shop_name", 'i', $oid) ?: [];

// Group items by vendor
$grouped = [];
foreach ($items as $item) {
    $grouped[$item['vendor_id']]['vendor'] = $item['shop_name'];
    $grouped[$item['vendor_id']]['items'][] = $item;
}

$just_placed = isset($_GET['placed']);
$base = BASE_URL;
$page_title = 'Order #' . $order['order_number'];
require_once '../../templates/layouts/header.php';
require_once '../../templates/layouts/navbar.php';

$st = ORDER_STATUSES[$order['order_status']] ?? ['label'=>ucfirst($order['order_status']),'color'=>'#6c757d'];
$timeline_steps = ['placed','confirmed','processing','shipped','delivered'];
$cur_step_idx = array_search($order['order_status'], $timeline_steps) ?: 0;
?>

<div class="page-content" style="background:var(--color-bg)">
  <div class="container" style="padding-top:var(--space-7);padding-bottom:var(--space-8)">
    <?php if ($just_placed): ?>
      <div class="alert alert-success mb-6" style="font-size:var(--text-lg)">🎉 Your order has been placed successfully!</div>
    <?php endif; ?>

    <!-- Header -->
    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
      <div>
        <a href="<?= $base ?>/pages/customer/orders.php" style="color:var(--color-muted);font-size:var(--text-sm)">← My Orders</a>
        <h1 style="font-size:var(--text-3xl);margin-top:var(--space-1)">Order #<?= htmlspecialchars($order['order_number']) ?></h1>
        <p class="text-muted">Placed on <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
      </div>
      <div class="flex gap-3 flex-wrap">
        <span class="badge" style="font-size:var(--text-sm);padding:8px 16px;background:<?= $st['color'] ?>20;color:<?= $st['color'] ?>"><?= $st['label'] ?></span>
        <a href="javascript:window.print()" class="btn btn-ghost btn-sm no-print">🖨 Print Invoice</a>
      </div>
    </div>

    <!-- Timeline -->
    <div class="card p-6 mb-6">
      <h3 style="margin-bottom:var(--space-6)">📍 Order Tracking</h3>
      <div style="display:flex;align-items:center;position:relative">
        <?php foreach($timeline_steps as $idx=>$step):
          $is_done    = $cur_step_idx >= $idx;
          $is_current = $cur_step_idx === $idx;
          $label      = ORDER_STATUSES[$step]['label'] ?? ucfirst($step);
        ?>
          <div style="flex:1;display:flex;flex-direction:column;align-items:center;position:relative">
            <?php if($idx>0): ?>
              <div style="position:absolute;top:19px;right:50%;left:-50%;height:3px;background:<?= $cur_step_idx>=$idx?'var(--color-primary)':'var(--color-border)' ?>"></div>
            <?php endif; ?>
            <div style="width:38px;height:38px;border-radius:50%;background:<?= $is_done?'var(--color-primary)':'var(--color-border)' ?>;display:flex;align-items:center;justify-content:center;z-index:1;font-size:1rem;<?= $is_current?'box-shadow:0 0 0 4px rgba(255,107,53,0.2)':'' ?>">
              <?= $is_done?'✓':($idx+1) ?>
            </div>
            <div style="font-size:10px;font-weight:600;margin-top:6px;text-align:center;color:<?= $is_done?'var(--color-primary)':'var(--color-muted)' ?>"><?= ucwords($label) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 340px;gap:var(--space-6)">

      <!-- Items per vendor -->
      <div>
        <?php foreach($grouped as $vid => $grp): ?>
          <div class="card mb-5">
            <div style="padding:var(--space-4) var(--space-5);border-bottom:1px solid var(--color-border);background:var(--color-bg)">
              <span class="fw-bold">🏪 <?= htmlspecialchars($grp['vendor']) ?></span>
            </div>
            <div style="padding:0 var(--space-5)">
              <?php foreach($grp['items'] as $item): ?>
                <?php include '../../templates/components/order_item.php'; ?>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>

        <!-- Review CTA -->
        <?php if ($order['order_status'] === ORDER_DELIVERED): ?>
          <div class="card p-5">
            <div class="flex items-center justify-between">
              <span class="fw-semibold">Rate your order</span>
              <a href="<?= $base ?>/pages/customer/reviews.php?order_id=<?= $oid ?>" class="btn btn-primary btn-sm">Write Review ⭐</a>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Summary -->
      <div>
        <div class="card p-5 mb-4">
          <h4 style="margin-bottom:var(--space-4)">📦 Order Info</h4>
          <div class="text-sm" style="display:flex;flex-direction:column;gap:var(--space-3)">
            <div class="flex justify-between"><span class="text-muted">Order #</span><span class="fw-semibold"><?= htmlspecialchars($order['order_number']) ?></span></div>
            <div class="flex justify-between"><span class="text-muted">Subtotal</span><span>₹<?= number_format($order['subtotal'],2) ?></span></div>
            <div class="flex justify-between"><span class="text-muted">Tax</span><span>₹<?= number_format($order['tax'],2) ?></span></div>
            <div class="flex justify-between"><span class="text-muted">Delivery</span><span class="text-success"><?= $order['delivery_charge']==0?'FREE':'₹'.number_format($order['delivery_charge'],2) ?></span></div>
            <div style="border-top:2px solid var(--color-border);padding-top:var(--space-3)" class="flex justify-between">
              <span class="fw-bold">Total</span><span class="fw-black text-primary">₹<?= number_format($order['total_amount'],2) ?></span>
            </div>
          </div>
        </div>

        <div class="card p-5 mb-4">
          <h4 style="margin-bottom:var(--space-4)">📍 Delivery Address</h4>
          <div class="text-sm text-muted"><?= nl2br(htmlspecialchars($order['delivery_address'])) ?></div>
          <?php if($order['delivery_name']): ?><div class="text-sm fw-semibold mt-2"><?= htmlspecialchars($order['delivery_name']) ?></div><?php endif; ?>
          <?php if($order['delivery_phone']): ?><div class="text-xs text-muted">📱 <?= htmlspecialchars($order['delivery_phone']) ?></div><?php endif; ?>
        </div>

        <div class="card p-5">
          <h4 style="margin-bottom:var(--space-4)">💳 Payment</h4>
          <div class="text-sm">
            <div class="flex justify-between mb-2"><span class="text-muted">Method</span><span><?= PAYMENT_LABELS[$order['payment_method']] ?? $order['payment_method'] ?></span></div>
            <div class="flex justify-between"><span class="text-muted">Status</span><span class="badge <?= $order['payment_status']==='paid'?'badge-success':'badge-warning' ?>"><?= ucfirst($order['payment_status']) ?></span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
@media(max-width:768px){div[style*="grid-template-columns:1fr 340px"]{grid-template-columns:1fr!important}}
</style>

<?php require_once '../../templates/layouts/footer.php'; ?>
