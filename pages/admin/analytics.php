<?php
require_once '../../config/config.php'; require_once '../../config/constants.php'; require_once '../../config/database.php';
$required_role=ROLE_ADMIN; require_once '../../templates/layouts/auth_wrapper.php';
$db=Database::getInstance();

$total_rev=$db->queryOne("SELECT COALESCE(SUM(total_amount),0) AS t FROM orders WHERE order_status='delivered'")['t']??0;
$total_comm=$db->queryOne("SELECT COALESCE(SUM(commission),0) AS t FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE o.order_status='delivered'")['t']??0;
$total_orders=$db->queryOne("SELECT COUNT(*) AS c FROM orders")['c']??0;
$delivered=$db->queryOne("SELECT COUNT(*) AS c FROM orders WHERE order_status='delivered'")['c']??0;
$cat_revenue=$db->query("SELECT c.name,COALESCE(SUM(oi.subtotal),0) AS rev,COUNT(oi.id) AS cnt FROM order_items oi JOIN products p ON p.id=oi.product_id JOIN categories c ON c.id=p.category_id GROUP BY c.id,c.name ORDER BY rev DESC LIMIT 10");
$monthly6=$db->query("SELECT DATE_FORMAT(created_at,'%b %Y') AS mon, COALESCE(SUM(total_amount),0) AS rev, COUNT(*) AS cnt, COALESCE(SUM(delivery_charge),0) AS del FROM orders WHERE created_at>=DATE_SUB(NOW(),INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY created_at");
$top_vendors=$db->query("SELECT v.shop_name, COALESCE(SUM(oi.vendor_earning),0) AS earn, COUNT(DISTINCT oi.order_id) AS orders FROM order_items oi JOIN vendors v ON v.id=oi.vendor_id GROUP BY oi.vendor_id ORDER BY earn DESC LIMIT 10");
$top_products=$db->query("SELECT p.name,p.views,p.total_reviews,COALESCE(SUM(oi.quantity),0) AS sold FROM order_items oi JOIN products p ON p.id=oi.product_id GROUP BY oi.product_id ORDER BY sold DESC LIMIT 10");

$base=BASE_URL; $page_title='Analytics'; $active_page='analytics.php'; $sidebar_role='admin';
require_once '../../templates/layouts/header.php';
?>
<div class="dashboard-layout">
<?php require_once '../../templates/layouts/sidebar.php'; ?>
<main class="dashboard-main">
  <h1 style="font-size:var(--text-2xl);margin-bottom:var(--space-6)">📈 Analytics</h1>

  <div class="stat-grid mb-6">
    <?php foreach([['💰','Total Revenue','₹'.number_format($total_rev,2),''],['🎯','Platform Commission','₹'.number_format($total_comm,2),'text-primary'],['📦','Total Orders',$total_orders,''],['✅','Delivered Orders',$delivered.' ('.($total_orders?round($delivered/$total_orders*100):0).'%)','text-success']] as [$i,$l,$v,$c]):?>
      <div class="stat-card"><div class="stat-icon"><?=$i?></div><div class="stat-value <?=$c?>"><?=$v?></div><div class="stat-label"><?=$l?></div></div>
    <?php endforeach;?>
  </div>

  <!-- Revenue Chart -->
  <?php if($monthly6):?>
  <div class="card p-6 mb-6">
    <h3 style="margin-bottom:var(--space-5)">Revenue Trend (Last 6 Months)</h3>
    <canvas id="revChart" height="100"></canvas>
  </div>
  <?php endif;?>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-6) mb-6">
    <!-- Top Vendors -->
    <div class="card mb-6">
      <div style="padding:var(--space-5);border-bottom:1px solid var(--color-border)"><h3>🏆 Top Vendors by Earnings</h3></div>
      <div class="table-wrapper"><table class="table">
        <thead><tr><th>#</th><th>Shop</th><th>Orders</th><th>Earnings</th></tr></thead>
        <tbody>
        <?php foreach($top_vendors as $idx=>$v):?>
          <tr>
            <td class="fw-bold text-muted"><?=$idx+1?></td>
            <td class="fw-semibold"><?=htmlspecialchars($v['shop_name'])?></td>
            <td><?=$v['orders']?></td>
            <td class="fw-bold text-primary">₹<?=number_format($v['earn'],2)?></td>
          </tr>
        <?php endforeach;?>
        </tbody>
      </table></div>
    </div>

    <!-- Category Revenue -->
    <div class="card mb-6">
      <div style="padding:var(--space-5);border-bottom:1px solid var(--color-border)"><h3>🧩 Revenue by Category</h3></div>
      <div class="table-wrapper"><table class="table">
        <thead><tr><th>Category</th><th>Orders</th><th>Revenue</th></tr></thead>
        <tbody>
        <?php foreach($cat_revenue as $cr):?>
          <tr>
            <td class="fw-semibold"><?=htmlspecialchars($cr['name'])?></td>
            <td><?=$cr['cnt']?></td>
            <td class="fw-bold text-primary">₹<?=number_format($cr['rev'],2)?></td>
          </tr>
        <?php endforeach;?>
        </tbody>
      </table></div>
    </div>

    <!-- Top Products -->
    <div class="card mb-6" style="grid-column:span 2">
      <div style="padding:var(--space-5);border-bottom:1px solid var(--color-border)"><h3>🧺 Top Selling Products</h3></div>
      <div class="table-wrapper"><table class="table">
        <thead><tr><th>#</th><th>Product</th><th>Units Sold</th><th>Views</th><th>Reviews</th></tr></thead>
        <tbody>
        <?php foreach($top_products as $idx=>$p):?>
          <tr>
            <td class="fw-bold text-muted"><?=$idx+1?></td>
            <td class="fw-semibold"><?=htmlspecialchars($p['name'])?></td>
            <td class="fw-bold"><?=$p['sold']?></td>
            <td><?=number_format($p['views'])?></td>
            <td><?=$p['total_reviews']?></td>
          </tr>
        <?php endforeach;?>
        </tbody>
      </table></div>
    </div>
  </div>
</main>
</div>

<script src="<?=$base?>/assets/js/utils.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php if($monthly6):?>
<script>
new Chart(document.getElementById('revChart'),{type:'line',data:{
  labels:<?=json_encode(array_column($monthly6,'mon'))?>,
  datasets:[{label:'Revenue (₹)',data:<?=json_encode(array_map(fn($m)=>(float)$m['rev'],$monthly6))?>,borderColor:'#FF6B35',backgroundColor:'rgba(255,107,53,0.1)',tension:0.4,fill:true,pointBackgroundColor:'#FF6B35'}]
},options:{responsive:true,scales:{y:{ticks:{callback:v=>'₹'+v.toLocaleString('en-IN')}}}}});
</script>
<?php endif;?>
</body></html>
