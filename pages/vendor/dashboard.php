<?php
require_once '../../config/config.php'; require_once '../../config/constants.php'; require_once '../../config/database.php';
$required_role=ROLE_VENDOR; require_once '../../templates/layouts/auth_wrapper.php';
$db=Database::getInstance(); $user=current_user();
$vendor=$db->prepareOne("SELECT * FROM vendors WHERE user_id=? LIMIT 1",'i',$user['id']);
if(!$vendor||$vendor['verification_status']!==VENDOR_APPROVED){header('Location:'.BASE_URL.'/pages/vendor/onboarding.php');exit;}
$vid=(int)$vendor['id'];

// Stats
$today=date('Y-m-d');
$stats=[
  'today_orders'   => $db->prepareOne("SELECT COUNT(*) AS c FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE oi.vendor_id=? AND DATE(o.created_at)=?",'is',$vid,$today)['c']??0,
  'total_orders'   => $db->prepareOne("SELECT COUNT(DISTINCT order_id) AS c FROM order_items WHERE vendor_id=?",'i',$vid)['c']??0,
  'total_earnings' => $db->prepareOne("SELECT COALESCE(SUM(vendor_earning),0) AS t FROM order_items WHERE vendor_id=?",'i',$vid)['t']??0,
  'pending_orders' => $db->prepareOne("SELECT COUNT(*) AS c FROM order_items WHERE vendor_id=? AND item_status='pending'",'i',$vid)['c']??0,
  'total_products' => $db->prepareOne("SELECT COUNT(*) AS c FROM products WHERE vendor_id=?",'i',$vid)['c']??0,
];
// Monthly chart data
$monthly=$db->prepare("SELECT DATE_FORMAT(o.created_at,'%b') AS mon, COALESCE(SUM(oi.vendor_earning),0) AS rev FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE oi.vendor_id=? AND o.created_at>=DATE_SUB(NOW(),INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(o.created_at,'%Y-%m') ORDER BY o.created_at",'i',$vid);
// Recent orders
$recent=$db->prepare("SELECT o.order_number,o.created_at,oi.product_name,oi.quantity,oi.subtotal,oi.item_status,oi.id AS item_id FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE oi.vendor_id=? ORDER BY o.created_at DESC LIMIT 10",'i',$vid);

$base=BASE_URL; $page_title='Vendor Dashboard'; $active_page='dashboard.php'; $sidebar_role='vendor'; $body_class='is-dashboard';
require_once '../../templates/layouts/header.php';
?>
<div class="dashboard-layout">
<?php require_once '../../templates/layouts/sidebar.php'; ?>
<main class="dashboard-main">
  <div class="flex items-center justify-between mb-6">
    <div><h1 style="font-size:var(--text-2xl)">👋 Welcome, <?= htmlspecialchars(explode(' ',$user['name'])[0]) ?>!</h1><p class="text-muted text-sm"><?= date('l, d F Y') ?></p></div>
    <a href="<?= $base ?>/pages/vendor/products.php?action=add" class="btn btn-primary">+ Add Product</a>
  </div>

  <!-- Stats Grid -->
  <div class="stat-grid mb-6">
    <?php foreach([
      ['💰','Total Earnings','₹'.number_format($stats['total_earnings'],2),''],
      ['📦','Total Orders',$stats['total_orders'],''],
      ['⏳','Pending Orders',$stats['pending_orders'],'text-warning'],
      ['🧺','Products Listed',$stats['total_products'],''],
    ] as [$icon,$label,$val,$cls]):?>
      <div class="stat-card">
        <div class="stat-icon"><?=$icon?></div>
        <div class="stat-value <?=$cls?>"><?=$val?></div>
        <div class="stat-label"><?=$label?></div>
      </div>
    <?php endforeach;?>
  </div>

  <!-- Revenue Chart -->
  <?php if($monthly):?>
  <div class="card p-6 mb-6">
    <h3 style="margin-bottom:var(--space-5)">📈 Monthly Revenue (Last 6 Months)</h3>
    <canvas id="revenueChart" height="120"></canvas>
  </div>
  <?php endif;?>

  <!-- Recent Orders -->
  <div class="card">
    <div style="padding:var(--space-5) var(--space-6);border-bottom:1px solid var(--color-border);display:flex;justify-content:space-between;align-items:center">
      <h3>Recent Order items</h3>
      <a href="<?=$base?>/pages/vendor/orders.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <?php if($recent):?>
    <div class="table-wrapper"><table class="table">
      <thead><tr><th>Order #</th><th>Product</th><th>Qty</th><th>Amount</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
      <tbody>
      <?php foreach($recent as $r):$st=ITEM_STATUSES[$r['item_status']]??['label'=>ucfirst($r['item_status']),'color'=>'#6c757d'];?>
        <tr>
          <td class="fw-semibold"><?=htmlspecialchars($r['order_number'])?></td>
          <td class="text-sm"><?=htmlspecialchars($r['product_name'])?></td>
          <td class="text-sm"><?=$r['quantity']?></td>
          <td class="fw-bold text-primary">₹<?=number_format($r['subtotal'],2)?></td>
          <td><span class="badge" style="background:<?=$st['color']?>20;color:<?=$st['color']?>"><?=$st['label']?></span></td>
          <td class="text-xs text-muted"><?=date('d M',strtotime($r['created_at']))?></td>
          <td><a href="<?=$base?>/pages/vendor/orders.php" class="btn btn-sm btn-ghost">View</a></td>
        </tr>
      <?php endforeach;?>
      </tbody>
    </table></div>
    <?php else:?><div class="empty-state" style="padding:var(--space-7)"><div class="empty-icon">📦</div><h3>No orders yet</h3></div><?php endif;?>
  </div>
</main>
</div>

<script src="<?=$base?>/assets/js/utils.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php if($monthly):?>
<script>
new Chart(document.getElementById('revenueChart'),{type:'bar',data:{
  labels:<?=json_encode(array_column($monthly,'mon'))?>,
  datasets:[{label:'Revenue (₹)',data:<?=json_encode(array_map(fn($r)=>(float)$r['rev'],$monthly))?>,backgroundColor:'rgba(255,107,53,0.8)',borderRadius:6}]
},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{ticks:{callback:v=>'₹'+v.toLocaleString('en-IN')}}}}});
</script>
<?php endif;?>
</body></html>
