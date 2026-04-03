<?php
require_once '../../config/config.php'; require_once '../../config/constants.php'; require_once '../../config/database.php';
$required_role=ROLE_ADMIN; require_once '../../templates/layouts/auth_wrapper.php';
$db=Database::getInstance(); $success=''; $errors=[];

if ($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['csrf_token'])&&$_POST['csrf_token']===CSRF_TOKEN) {
    $action=$_POST['action']??''; $uid=(int)$_POST['user_id'];
    if ($action==='toggle_active') {
        $db->execute("UPDATE users SET is_active=1-is_active WHERE id=?",'i',$uid);
        $success='User status updated.';
    } elseif ($action==='change_role') {
        $nr=$_POST['new_role']??'';
        if(in_array($nr,[ROLE_CUSTOMER,ROLE_VENDOR,ROLE_ADMIN])) { $db->execute("UPDATE users SET role=? WHERE id=?",'si',$nr,$uid); $success='User role updated.'; }
    }
}

$role_filter=trim($_GET['role']??''); $q=trim($_GET['q']??'');
$page=(int)($_GET['page']??1); $per=20; $offset=($page-1)*$per;
$where="role!='admin'"; if($role_filter) $where.=" AND role='".addslashes($role_filter)."'";
if($q) $where.=" AND (name LIKE '%".addslashes($q)."%' OR email LIKE '%".addslashes($q)."%')";
$total=(int)($db->queryOne("SELECT COUNT(*) AS c FROM users WHERE $where")['c']??0);
$total_pages=max(1,ceil($total/$per));
$users=$db->query("SELECT * FROM users WHERE $where ORDER BY created_at DESC LIMIT $per OFFSET $offset");

$base=BASE_URL; $page_title='Manage Users'; $active_page='users.php'; $sidebar_role='admin';
require_once '../../templates/layouts/header.php';
?>
<div class="dashboard-layout">
<?php require_once '../../templates/layouts/sidebar.php'; ?>
<main class="dashboard-main">
  <h1 style="font-size:var(--text-2xl);margin-bottom:var(--space-6)">👥 Users</h1>
  <?php if($success):?><div class="alert alert-success mb-4">✅ <?=htmlspecialchars($success)?></div><?php endif;?>

  <div class="flex gap-2 flex-wrap mb-4">
    <a href="?" class="badge <?=!$role_filter?'badge-primary':'badge-muted'?>" style="padding:8px 16px;text-decoration:none">All</a>
    <?php foreach([ROLE_CUSTOMER=>'Customers',ROLE_VENDOR=>'Vendors'] as $k=>$l):?><a href="?role=<?=$k?>" class="badge <?=$role_filter===$k?'badge-primary':'badge-muted'?>" style="padding:8px 16px;text-decoration:none"><?=$l?></a><?php endforeach;?>
  </div>
  <form class="flex gap-3 mb-5" method="GET"><input type="hidden" name="role" value="<?=htmlspecialchars($role_filter)?>"><input type="text" class="form-control" name="q" placeholder="Search users…" value="<?=htmlspecialchars($q)?>" style="max-width:280px"><button type="submit" class="btn btn-ghost btn-sm">Search</button></form>

  <?php if($users):?>
  <div class="table-wrapper">
    <table class="table">
      <thead><tr><th>User</th><th>Role</th><th>Phone</th><th>Joined</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($users as $u):?>
        <tr>
          <td><div class="fw-semibold"><?=htmlspecialchars($u['name'])?></div><div class="text-xs text-muted"><?=htmlspecialchars($u['email'])?></div></td>
          <td><span class="badge <?=$u['role']===ROLE_VENDOR?'badge-primary':'badge-muted'?>"><?=ucfirst($u['role'])?></span></td>
          <td class="text-sm text-muted"><?=htmlspecialchars($u['phone']??'-')?></td>
          <td class="text-xs text-muted"><?=date('d M Y',strtotime($u['created_at']))?></td>
          <td>
            <form method="POST" style="display:inline"><input type="hidden" name="csrf_token" value="<?=CSRF_TOKEN?>"><input type="hidden" name="action" value="toggle_active"><input type="hidden" name="user_id" value="<?=$u['id']?>">
              <button type="submit" class="badge <?=$u['is_active']?'badge-success':'badge-danger'?>" style="border:none;cursor:pointer"><?=$u['is_active']?'Active':'Banned'?></button>
            </form>
          </td>
          <td class="flex gap-2">
            <form method="POST"><input type="hidden" name="csrf_token" value="<?=CSRF_TOKEN?>"><input type="hidden" name="action" value="toggle_active"><input type="hidden" name="user_id" value="<?=$u['id']?>"><button type="submit" class="btn btn-sm btn-ghost"><?=$u['is_active']?'🚫 Ban':'✅ Unban'?></button></form>
          </td>
        </tr>
      <?php endforeach;?>
      </tbody>
    </table>
  </div>
  <?php $current_page=$page; $base_url='?'.http_build_query(array_filter(['role'=>$role_filter,'q'=>$q])); include '../../templates/components/pagination.php';
  else:?><div class="empty-state"><div class="empty-icon">👥</div><h3>No users found</h3></div><?php endif;?>
</main>
</div>
<script src="<?=$base?>/assets/js/utils.js"></script>
</body></html>
