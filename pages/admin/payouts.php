<?php
require_once '../../config/config.php'; require_once '../../config/constants.php'; require_once '../../config/database.php';
$required_role=ROLE_ADMIN; require_once '../../templates/layouts/auth_wrapper.php';
$db=Database::getInstance(); $success='';

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['csrf_token'])&&$_POST['csrf_token']===CSRF_TOKEN){
    $action=$_POST['action']??''; $pid=(int)$_POST['payout_id'];
    if(in_array($action,['approve','reject','mark_paid'])){
        $s_map=['approve'=>'approved','reject'=>'rejected','mark_paid'=>'paid'];
        $db->execute("UPDATE payouts SET status=?,processed_at=NOW() WHERE id=?",'si',$s_map[$action],$pid);
        $payout=$db->prepareOne("SELECT p.*,v.user_id FROM payouts p JOIN vendors v ON v.id=p.vendor_id WHERE p.id=? LIMIT 1",'i',$pid);
        if($payout) $db->execute("INSERT INTO notifications(user_id,title,message,type) VALUES(?,?,?,?)",'isss',$payout['user_id'],'Payout Update 💸',"Your payout of ₹".number_format($payout['amount'],2)." is ".ucfirst($s_map[$action]).".","payout");
        $success="Payout $action done!";
    }
}

$status_filter=trim($_GET['status']??'pending'); $page=(int)($_GET['page']??1); $per=20; $offset=($page-1)*$per;
$where=$status_filter?"p.status='".addslashes($status_filter)."'":'1=1';
$total=(int)($db->queryOne("SELECT COUNT(*) AS c FROM payouts p WHERE $where")['c']??0);
$total_pages=max(1,ceil($total/$per));
$payouts=$db->query("SELECT p.*,v.shop_name,v.bank_account,v.bank_ifsc,u.name AS owner_name,u.email FROM payouts p JOIN vendors v ON v.id=p.vendor_id JOIN users u ON u.id=v.user_id WHERE $where ORDER BY p.requested_at DESC LIMIT $per OFFSET $offset");
$ps=['pending'=>['label'=>'Pending','color'=>'#f39c12'],'approved'=>['label'=>'Approved','color'=>'#3498db'],'rejected'=>['label'=>'Rejected','color'=>'#e74c3c'],'paid'=>['label'=>'Paid','color'=>'#2ecc71']];

$base=BASE_URL; $page_title='Manage Payouts'; $active_page='payouts.php'; $sidebar_role='admin';
require_once '../../templates/layouts/header.php';
?>
<div class="dashboard-layout">
<?php require_once '../../templates/layouts/sidebar.php'; ?>
<main class="dashboard-main">
  <h1 style="font-size:var(--text-2xl);margin-bottom:var(--space-6)">💸 Payouts</h1>
  <?php if($success):?><div class="alert alert-success mb-4">✅ <?=htmlspecialchars($success)?></div><?php endif;?>

  <div class="flex gap-2 flex-wrap mb-5">
    <?php foreach([''=>'All']+array_map(fn($s)=>$s['label'],$ps) as $k=>$l):?><a href="?status=<?=$k?>" class="badge <?=$status_filter===$k?'badge-primary':'badge-muted'?>" style="padding:8px 16px;text-decoration:none"><?=$l?></a><?php endforeach;?>
  </div>

  <?php if($payouts):?>
  <div class="table-wrapper">
    <table class="table">
      <thead><tr><th>Vendor</th><th>Amount</th><th>Bank</th><th>Status</th><th>Requested</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($payouts as $p):$st=$ps[$p['status']]??['label'=>ucfirst($p['status']),'color'=>'#6c757d'];?>
        <tr>
          <td><div class="fw-semibold"><?=htmlspecialchars($p['shop_name'])?></div><div class="text-xs text-muted"><?=htmlspecialchars($p['email'])?></div></td>
          <td class="fw-bold text-primary">₹<?=number_format($p['amount'],2)?></td>
          <td><div class="text-xs font-mono"><?=htmlspecialchars($p['bank_account']??'-')?></div><div class="text-xs text-muted"><?=htmlspecialchars($p['bank_ifsc']??'-')?></div></td>
          <td><span class="badge" style="background:<?=$st['color']?>20;color:<?=$st['color']?>"><?=$st['label']?></span></td>
          <td class="text-xs text-muted"><?=date('d M Y',strtotime($p['requested_at']))?></td>
          <td>
            <?php if($p['status']==='pending'):?>
              <form method="POST" style="display:flex;gap:6px">
                <input type="hidden" name="csrf_token" value="<?=CSRF_TOKEN?>"><input type="hidden" name="payout_id" value="<?=$p['id']?>">
                <button name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                <button name="action" value="reject" class="btn btn-sm btn-danger" onclick="return confirm('Reject payout?')">Reject</button>
              </form>
            <?php elseif($p['status']==='approved'):?>
              <form method="POST"><input type="hidden" name="csrf_token" value="<?=CSRF_TOKEN?>"><input type="hidden" name="payout_id" value="<?=$p['id']?>"><button name="action" value="mark_paid" class="btn btn-sm btn-primary">Mark Paid ✓</button></form>
            <?php else:?><span class="text-xs text-muted">—</span><?php endif;?>
          </td>
        </tr>
      <?php endforeach;?>
      </tbody>
    </table>
  </div>
  <?php $current_page=$page; $base_url='?status='.urlencode($status_filter); include '../../templates/components/pagination.php';
  else:?><div class="empty-state"><div class="empty-icon">💸</div><h3>No payout requests</h3></div><?php endif;?>
</main>
</div>
<script src="<?=$base?>/assets/js/utils.js"></script>
</body></html>
