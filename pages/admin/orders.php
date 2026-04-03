<?php
require_once '../../config/config.php'; require_once '../../config/constants.php'; require_once '../../config/database.php';
$required_role=ROLE_ADMIN; require_once '../../templates/layouts/auth_wrapper.php';
$db=Database::getInstance(); $success='';

if ($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['csrf_token'])&&$_POST['csrf_token']===CSRF_TOKEN) {
    $action=$_POST['action']??''; $oid=(int)$_POST['order_id'];
    if ($action==='update_status'&&$oid) {
        $new_status=$_POST['order_status']??'';
        if (array_key_exists($new_status,ORDER_STATUSES)) {
            $db->execute("UPDATE orders SET order_status=? WHERE id=?",'si',$new_status,$oid);
            $o=$db->prepareOne("SELECT user_id,order_number FROM orders WHERE id=? LIMIT 1",'i',$oid);
            if($o) $db->execute("INSERT INTO notifications(user_id,title,message,type,link) VALUES(?,?,?,?,?)",'issss',$o['user_id'],"Order Update 📦","Order {$o['order_number']} status: ".ORDER_STATUSES[$new_status]['label'],"order",BASE_URL.'/pages/customer/order_detail.php?id='.$oid);
            $success='Order status updated!';
        }
    }
}

$status_filter=trim($_GET['status']??''); $q=trim($_GET['q']??'');
$page=(int)($_GET['page']??1); $per=20; $offset=($page-1)*$per;
$where="1=1"; if($status_filter) $where.=" AND o.order_status='".addslashes($status_filter)."'";
if($q) $where.=" AND (o.order_number LIKE '%".addslashes($q)."%' OR u.name LIKE '%".addslashes($q)."%')";
$total=(int)($db->queryOne("SELECT COUNT(*) AS c FROM orders o JOIN users u ON u.id=o.user_id WHERE $where")['c']??0);
$total_pages=max(1,ceil($total/$per));
$orders=$db->query("SELECT o.*,u.name AS customer_name FROM orders o JOIN users u ON u.id=o.user_id WHERE $where ORDER BY o.created_at DESC LIMIT $per OFFSET $offset");

$base=BASE_URL; $page_title='Manage Orders'; $active_page='orders.php'; $sidebar_role='admin';
require_once '../../templates/layouts/header.php';
?>
<div class="dashboard-layout">
<?php require_once '../../templates/layouts/sidebar.php'; ?>
<main class="dashboard-main">
  <h1 style="font-size:var(--text-2xl);margin-bottom:var(--space-6)">📦 All Orders</h1>
  <?php if($success):?><div class="alert alert-success mb-4">✅ <?=htmlspecialchars($success)?></div><?php endif;?>

  <div class="flex gap-2 flex-wrap mb-4">
    <a href="?" class="badge <?=!$status_filter?'badge-primary':'badge-muted'?>" style="padding:8px 16px;text-decoration:none">All</a>
    <?php foreach(ORDER_STATUSES as $k=>$s):?><a href="?status=<?=$k?>" class="badge <?=$status_filter===$k?'badge-primary':'badge-muted'?>" style="padding:8px 16px;text-decoration:none"><?=$s['label']?></a><?php endforeach;?>
  </div>
  <form class="flex gap-3 mb-5" method="GET">
    <input type="hidden" name="status" value="<?=htmlspecialchars($status_filter)?>">
    <input type="text" class="form-control" name="q" placeholder="Search order # or customer…" value="<?=htmlspecialchars($q)?>" style="max-width:280px">
    <button type="submit" class="btn btn-ghost btn-sm">Search</button>
  </form>

  <?php if($orders):?>
  <div class="table-wrapper">
    <table class="table">
      <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th>Update Status</th></tr></thead>
      <tbody>
      <?php foreach($orders as $o):$st=ORDER_STATUSES[$o['order_status']]??['label'=>ucfirst($o['order_status']),'color'=>'#6c757d'];?>
        <tr>
          <td><a href="<?=$base?>/pages/customer/order_detail.php?id=<?=$o['id']?>" class="fw-semibold text-primary"><?=htmlspecialchars($o['order_number'])?></a></td>
          <td class="text-sm"><?=htmlspecialchars($o['customer_name'])?></td>
          <td class="fw-bold">₹<?=number_format($o['total_amount'],2)?></td>
          <td><div class="text-xs"><?=PAYMENT_LABELS[$o['payment_method']]??$o['payment_method']?></div><span class="badge <?=$o['payment_status']==='paid'?'badge-success':'badge-warning'?> badge-sm"><?=ucfirst($o['payment_status'])?></span></td>
          <td><span class="badge" style="background:<?=$st['color']?>20;color:<?=$st['color']?>"><?=$st['label']?></span></td>
          <td class="text-xs text-muted"><?=date('d M Y',strtotime($o['created_at']))?></td>
          <td>
            <form method="POST" class="flex gap-1">
              <input type="hidden" name="csrf_token" value="<?=CSRF_TOKEN?>">
              <input type="hidden" name="action" value="update_status">
              <input type="hidden" name="order_id" value="<?=$o['id']?>">
              <select class="form-control" name="order_status" style="font-size:11px;padding:4px 6px;height:auto">
                <?php foreach(ORDER_STATUSES as $k=>$s):?><option value="<?=$k?>" <?=$o['order_status']===$k?'selected':''?>><?=$s['label']?></option><?php endforeach;?>
              </select>
              <button type="submit" class="btn btn-primary btn-sm">Set</button>
            </form>
          </td>
        </tr>
      <?php endforeach;?>
      </tbody>
    </table>
  </div>
  <?php $current_page=$page; $base_url='?'.http_build_query(array_filter(['status'=>$status_filter,'q'=>$q])); include '../../templates/components/pagination.php';
  else:?><div class="empty-state"><div class="empty-icon">📦</div><h3>No orders found</h3></div><?php endif;?>
</main>
</div>
<script src="<?=$base?>/assets/js/utils.js"></script>
</body></html>
