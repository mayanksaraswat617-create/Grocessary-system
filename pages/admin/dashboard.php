<?php
require_once '../../config/config.php'; require_once '../../config/constants.php'; require_once '../../config/database.php';
$required_role=ROLE_ADMIN; require_once '../../templates/layouts/auth_wrapper.php';
$db=Database::getInstance();

$stats=[
  'total_orders'    =>$db->queryOne("SELECT COUNT(*) AS c FROM orders")['c']??0,
  'today_orders'    =>$db->queryOne("SELECT COUNT(*) AS c FROM orders WHERE DATE(created_at)=CURDATE()")['c']??0,
  'total_revenue'   =>$db->queryOne("SELECT COALESCE(SUM(total_amount),0) AS t FROM orders WHERE order_status='delivered'")['t']??0,
  'commission_earned'=>$db->queryOne("SELECT COALESCE(SUM(commission),0) AS t FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE o.order_status='delivered'")['t']??0,
  'total_vendors'   =>$db->queryOne("SELECT COUNT(*) AS c FROM vendors")['c']??0,
  'pending_vendors' =>$db->queryOne("SELECT COUNT(*) AS c FROM vendors WHERE verification_status='pending'")['c']??0,
  'total_customers' =>$db->queryOne("SELECT COUNT(*) AS c FROM users WHERE role='customer'")['c']??0,
  'total_products'  =>$db->queryOne("SELECT COUNT(*) AS c FROM products WHERE is_active=1")['c']??0,
];
$monthly=$db->query("SELECT DATE_FORMAT(created_at,'%b') AS mon, COALESCE(SUM(total_amount),0) AS rev, COUNT(*) AS cnt FROM orders WHERE created_at>=DATE_SUB(NOW(),INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY created_at");
$recent_orders=$db->query("SELECT o.*,u.name AS customer_name FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.created_at DESC LIMIT 10");
$pending_vendors=$db->query("SELECT v.*,u.name,u.email FROM vendors v JOIN users u ON u.id=v.user_id WHERE v.verification_status='pending' ORDER BY v.created_at DESC LIMIT 5");

$base=BASE_URL; $page_title='Admin Dashboard'; $active_page='dashboard.php'; $sidebar_role='admin'; $body_class='is-dashboard';
require_once '../../templates/layouts/header.php';
?>
<div class="dashboard-layout">
<?php require_once '../../templates/layouts/sidebar.php'; ?>
<main class="dashboard-main">
  <div class="flex items-center justify-between mb-6">
    <div><h1 style="font-size:var(--text-2xl)">Platform Overview</h1><p class="text-muted text-sm"><?=date('l, d F Y')?></p></div>
    <a href="<?=$base?>/pages/admin/analytics.php" class="btn btn-primary">View Full Analytics</a>
  </div>

  <!-- Stats -->
  <div class="stat-grid mb-6">
    <?php foreach([['💰','Total Revenue','₹'.number_format($stats['total_revenue'],2),''],['🎯','Commission Earned','₹'.number_format($stats['commission_earned'],2),'text-primary'],['📦','Total Orders',$stats['total_orders'],''],['🚀','Today\'s Orders',$stats['today_orders'],'text-success'],['🏪','Total Vendors',$stats['total_vendors'],''],['⏳','Pending Approval',$stats['pending_vendors'],'text-warning'],['👥','Customers',$stats['total_customers'],''],['🧺','Active Products',$stats['total_products'],'']] as [$i,$l,$v,$c]):?>
      <div class="stat-card"><div class="stat-icon"><?=$i?></div><div class="stat-value <?=$c?>"><?=$v?></div><div class="stat-label"><?=$l?></div></div>
    <?php endforeach;?>
  </div>

  <?php if($monthly):?>
  <div class="card p-6 mb-6">
    <h3 style="margin-bottom:var(--space-5)">📈 Revenue & Orders (Last 6 Months)</h3>
    <canvas id="revenueChart" height="100"></canvas>
  </div>
  <?php endif;?>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-6)">

    <!-- Pending Vendors -->
    <div class="card">
      <div style="padding:var(--space-5);border-bottom:1px solid var(--color-border);display:flex;justify-content:space-between;align-items:center">
        <h3>⏳ Pending Vendor Approvals</h3>
        <a href="<?=$base?>/pages/admin/vendors.php?status=pending" class="btn btn-sm btn-outline-primary">View All</a>
      </div>
      <?php if($pending_vendors):?>
      <div style="padding:0 var(--space-5)">
        <?php foreach($pending_vendors as $v):?>
          <div style="display:flex;align-items:center;justify-content:space-between;padding:var(--space-4) 0;border-bottom:1px solid var(--color-border)">
            <div><div class="fw-semibold text-sm"><?=htmlspecialchars($v['shop_name'])?></div><div class="text-xs text-muted"><?=htmlspecialchars($v['email'])?></div></div>
            <a href="<?=$base?>/pages/admin/vendor_verify.php?id=<?=$v['id']?>" class="btn btn-primary btn-sm">Review</a>
          </div>
        <?php endforeach;?>
      </div>
      <?php else:?><div class="empty-state" style="padding:var(--space-5)"><div class="empty-icon">✅</div><p>No pending approvals</p></div><?php endif;?>
    </div>

    <!-- Recent Orders -->
    <div class="card">
      <div style="padding:var(--space-5);border-bottom:1px solid var(--color-border);display:flex;justify-content:space-between;align-items:center">
        <h3>📦 Recent Orders</h3>
        <a href="<?=$base?>/pages/admin/orders.php" class="btn btn-sm btn-outline-primary">View All</a>
      </div>
      <?php if($recent_orders):?>
      <div style="padding:0 var(--space-5)">
        <?php foreach($recent_orders as $o):$st=ORDER_STATUSES[$o['order_status']]??['label'=>ucfirst($o['order_status']),'color'=>'#6c757d'];?>
          <div style="display:flex;align-items:center;justify-content:space-between;padding:var(--space-3) 0;border-bottom:1px solid var(--color-border)">
            <div><div class="fw-semibold text-sm"><?=htmlspecialchars($o['order_number'])?></div><div class="text-xs text-muted"><?=htmlspecialchars($o['customer_name'])?> · ₹<?=number_format($o['total_amount'],2)?></div></div>
            <span class="badge" style="background:<?=$st['color']?>20;color:<?=$st['color']?>"><?=$st['label']?></span>
          </div>
        <?php endforeach;?>
      </div>
      <?php else:?><div class="empty-state" style="padding:var(--space-5)"><div class="empty-icon">📦</div><p>No orders yet</p></div><?php endif;?>
    </div>
  </div>
</main>
</div>

<script src="<?=$base?>/assets/js/utils.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php if($monthly):?>
<script>
const ctx=document.getElementById('revenueChart');
new Chart(ctx,{type:'bar',data:{
  labels:<?=json_encode(array_column($monthly,'mon'))?>,
  datasets:[
    {label:'Revenue (₹)',data:<?=json_encode(array_map(fn($m)=>(float)$m['rev'],$monthly))?>,backgroundColor:'rgba(255,107,53,0.8)',borderRadius:6,yAxisID:'y'},
    {label:'Orders',data:<?=json_encode(array_map(fn($m)=>(int)$m['cnt'],$monthly))?>,type:'line',borderColor:'#004E89',backgroundColor:'rgba(0,78,137,0.1)',yAxisID:'y1',tension:0.4}
  ]
},options:{responsive:true,scales:{y:{ticks:{callback:v=>'₹'+v.toLocaleString('en-IN')},position:'left'},y1:{position:'right',grid:{drawOnChartArea:false}}}}});
</script>
<?php endif;?>
</body></html>
