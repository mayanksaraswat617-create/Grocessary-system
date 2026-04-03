<?php
require_once '../../config/config.php'; require_once '../../config/constants.php'; require_once '../../config/database.php';
$required_role=ROLE_VENDOR; require_once '../../templates/layouts/auth_wrapper.php';
$db=Database::getInstance(); $user=current_user();
$vendor=$db->prepareOne("SELECT * FROM vendors WHERE user_id=? LIMIT 1",'i',$user['id']);
if(!$vendor||$vendor['verification_status']!==VENDOR_APPROVED){header('Location:'.BASE_URL.'/pages/vendor/onboarding.php');exit;}
$vid=(int)$vendor['id'];

$total_earning=$db->prepareOne("SELECT COALESCE(SUM(vendor_earning),0) AS t FROM order_items WHERE vendor_id=?",'i',$vid)['t']??0;
$total_commission=$db->prepareOne("SELECT COALESCE(SUM(commission),0) AS t FROM order_items WHERE vendor_id=?",'i',$vid)['t']??0;
$paid_payout=$db->prepareOne("SELECT COALESCE(SUM(amount),0) AS t FROM payouts WHERE vendor_id=? AND status='paid'",'i',$vid)['t']??0;
$balance=$total_earning-$paid_payout;
$monthly=$db->prepare("SELECT DATE_FORMAT(o.created_at,'%b %Y') AS mon, COALESCE(SUM(oi.vendor_earning),0) AS earn, COALESCE(SUM(oi.commission),0) AS comm FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE oi.vendor_id=? GROUP BY DATE_FORMAT(o.created_at,'%Y-%m') ORDER BY o.created_at DESC LIMIT 6",'i',$vid);

$base=BASE_URL; $page_title='Earnings'; $active_page='earnings.php'; $sidebar_role='vendor';
require_once '../../templates/layouts/header.php';
?>
<div class="dashboard-layout">
<?php require_once '../../templates/layouts/sidebar.php'; ?>
<main class="dashboard-main">
  <h1 style="font-size:var(--text-2xl);margin-bottom:var(--space-6)">💰 Earnings</h1>

  <div class="stat-grid mb-6">
    <?php foreach([['💰','Gross Earnings','₹'.number_format($total_earning,2),''],['✂️','Commission Deducted','₹'.number_format($total_commission,2),'text-danger'],['💸','Total Paid Out','₹'.number_format($paid_payout,2),''],['🏦','Available Balance','₹'.number_format($balance,2),'text-success']] as [$i,$l,$v,$c]):?>
      <div class="stat-card"><div class="stat-icon"><?=$i?></div><div class="stat-value <?=$c?>"><?=$v?></div><div class="stat-label"><?=$l?></div></div>
    <?php endforeach;?>
  </div>

  <div class="card p-6 mb-6">
    <div class="flex items-center justify-between mb-5">
      <h3>Monthly Breakdown</h3>
      <a href="<?=$base?>/pages/vendor/payouts.php" class="btn btn-primary btn-sm">Request Payout</a>
    </div>
    <canvas id="earningsChart" height="100"></canvas>
  </div>

  <?php if($monthly):?>
  <div class="table-wrapper">
    <table class="table">
      <thead><tr><th>Month</th><th>Gross Earnings</th><th>Commission</th><th>Net Earning</th></tr></thead>
      <tbody>
      <?php foreach($monthly as $m):?>
        <tr>
          <td class="fw-semibold"><?=htmlspecialchars($m['mon'])?></td>
          <td>₹<?=number_format($m['earn'],2)?></td>
          <td class="text-danger">-₹<?=number_format($m['comm'],2)?></td>
          <td class="fw-bold text-success">₹<?=number_format($m['earn']-$m['comm'],2)?></td>
        </tr>
      <?php endforeach;?>
      </tbody>
    </table>
  </div>
  <?php endif;?>
</main>
</div>

<script src="<?=$base?>/assets/js/utils.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('earningsChart'),{type:'line',data:{
  labels:<?=json_encode(array_map(fn($m)=>$m['mon'],array_reverse($monthly??[])))?>,
  datasets:[
    {label:'Net Earnings (₹)',data:<?=json_encode(array_map(fn($m)=>(float)$m['earn']-(float)$m['comm'],array_reverse($monthly??[])))?>  ,borderColor:'#2ecc71',backgroundColor:'rgba(46,204,113,0.1)',tension:0.4,fill:true,pointBackgroundColor:'#2ecc71'},
    {label:'Commission (₹)',  data:<?=json_encode(array_map(fn($m)=>(float)$m['comm'],array_reverse($monthly??[])))?>                    ,borderColor:'#e74c3c',backgroundColor:'rgba(231,76,60,0.05)',tension:0.4,borderDash:[5,5]}
  ]
},options:{responsive:true,scales:{y:{ticks:{callback:v=>'₹'+v.toLocaleString('en-IN')}}}}});
</script>
</body></html>
