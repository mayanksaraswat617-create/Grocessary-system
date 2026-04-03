<?php
require_once '../../config/config.php'; require_once '../../config/constants.php'; require_once '../../config/database.php';
$required_role=ROLE_ADMIN; require_once '../../templates/layouts/auth_wrapper.php';
$db=Database::getInstance(); $success=''; $errors=[];

// Vendor verify/reject/ban actions
if ($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['csrf_token'])&&$_POST['csrf_token']===CSRF_TOKEN) {
    $vid=(int)$_POST['vendor_id']; $action=$_POST['action']??'';
    if (in_array($action,['approve','reject','ban'])) {
        $status_map=['approve'=>VENDOR_APPROVED,'reject'=>VENDOR_REJECTED,'ban'=>VENDOR_REJECTED]; // ban = rejected + suspend user
        $db->execute("UPDATE vendors SET verification_status=?,kyc_notes=? WHERE id=?",'ssi',$status_map[$action],trim($_POST['notes']??''),$vid);
        if ($action==='ban') $db->execute("UPDATE users u JOIN vendors v ON u.id=v.user_id SET u.is_active=0 WHERE v.id=?",'i',$vid);
        // Notify vendor
        $v=$db->prepareOne("SELECT v.user_id,v.shop_name FROM vendors v WHERE v.id=? LIMIT 1",'i',$vid);
        if ($v) $db->execute("INSERT INTO notifications(user_id,title,message,type) VALUES(?,?,?,?)",'isss',$v['user_id'],
            $action==='approve'?'Application Approved ✅':'Application Update',
            $action==='approve'?"Your shop {$v['shop_name']} has been approved! You can now start selling."
                :"Your vendor application for {$v['shop_name']} was ".($action==='reject'?'rejected':'suspended').". ".($_POST['notes']??''),
            'vendor');
        $success="Vendor $action action completed.";
    }
}

$status_filter=trim($_GET['status']??''); $q=trim($_GET['q']??'');
$page=(int)($_GET['page']??1); $per=15; $offset=($page-1)*$per;
$where="1=1"; if($status_filter) $where.=" AND v.verification_status='".addslashes($status_filter)."'";
if($q) $where.=" AND (v.shop_name LIKE '%".addslashes($q)."%' OR u.name LIKE '%".addslashes($q)."%')";
$total=(int)($db->queryOne("SELECT COUNT(*) AS c FROM vendors v JOIN users u ON u.id=v.user_id WHERE $where")['c']??0);
$total_pages=max(1,ceil($total/$per));
$vendors=$db->query("SELECT v.*,u.name AS owner_name,u.email,u.phone,u.is_active FROM vendors v JOIN users u ON u.id=v.user_id WHERE $where ORDER BY v.created_at DESC LIMIT $per OFFSET $offset");
$vc=['pending'=>'warning','approved'=>'success','rejected'=>'danger'];

$base=BASE_URL; $page_title='Manage Vendors'; $active_page='vendors.php'; $sidebar_role='admin';
require_once '../../templates/layouts/header.php';
?>
<div class="dashboard-layout">
<?php require_once '../../templates/layouts/sidebar.php'; ?>
<main class="dashboard-main">
  <h1 style="font-size:var(--text-2xl);margin-bottom:var(--space-6)">🏪 Vendors</h1>
  <?php if($success):?><div class="alert alert-success mb-4">✅ <?=htmlspecialchars($success)?></div><?php endif;?>

  <!-- Filters -->
  <div class="flex gap-2 flex-wrap mb-5">
    <a href="?" class="badge <?=!$status_filter?'badge-primary':'badge-muted'?>" style="padding:8px 16px;text-decoration:none">All</a>
    <?php foreach(['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $k=>$l):?><a href="?status=<?=$k?>" class="badge badge-<?=$vc[$k]?>" style="padding:8px 16px;text-decoration:none;opacity:<?=$status_filter===$k?'1':'0.5'?>"><?=$l?></a><?php endforeach;?>
  </div>
  <form class="flex gap-3 mb-5" method="GET">
    <input type="hidden" name="status" value="<?=htmlspecialchars($status_filter)?>">
    <input type="text" class="form-control" name="q" placeholder="Search vendors…" value="<?=htmlspecialchars($q)?>" style="max-width:280px">
    <button type="submit" class="btn btn-ghost btn-sm">Search</button>
  </form>

  <?php if($vendors):?>
  <div class="table-wrapper">
    <table class="table">
      <thead><tr><th>Shop Name</th><th>Owner</th><th>City</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($vendors as $v):?>
        <tr>
          <td><div class="fw-semibold"><?=htmlspecialchars($v['shop_name'])?></div><div class="text-xs text-muted"><?=htmlspecialchars($v['email'])?></div></td>
          <td class="text-sm"><?=htmlspecialchars($v['owner_name'])?><br><span class="text-xs text-muted"><?=htmlspecialchars($v['phone']??'')?></span></td>
          <td class="text-sm text-muted"><?=htmlspecialchars($v['city']??'-')?></td>
          <?php $vc_cls = $vc[$v['verification_status']] ?? 'muted'; ?>
          <td><span class="badge badge-<?= $vc_cls ?>"><?=ucfirst($v['verification_status'])?></span><?php if(!$v['is_active']):?><span class="badge badge-danger ml-1">Banned</span><?php endif;?></td>
          <td class="text-xs text-muted"><?=date('d M Y',strtotime($v['created_at']))?></td>
          <td>
            <div class="flex gap-2">
              <a href="<?=$base?>/pages/admin/vendor_verify.php?id=<?=$v['id']?>" class="btn btn-sm btn-outline-primary">View</a>
              <?php if($v['verification_status']==='pending'):?>
                <form method="POST"><input type="hidden" name="csrf_token" value="<?=CSRF_TOKEN?>"><input type="hidden" name="vendor_id" value="<?=$v['id']?>"><input type="hidden" name="action" value="approve"><button type="submit" class="btn btn-sm btn-success">Approve</button></form>
              <?php endif;?>
            </div>
          </td>
        </tr>
      <?php endforeach;?>
      </tbody>
    </table>
  </div>
  <?php $current_page=$page; $base_url='?'.http_build_query(array_filter(['status'=>$status_filter,'q'=>$q])); include '../../templates/components/pagination.php';
  else:?><div class="empty-state"><div class="empty-icon">🏪</div><h3>No vendors found</h3></div><?php endif;?>
</main>
</div>
<script src="<?=$base?>/assets/js/utils.js"></script>
</body></html>
