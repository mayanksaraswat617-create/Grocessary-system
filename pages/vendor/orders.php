<?php
require_once '../../config/config.php'; require_once '../../config/constants.php'; require_once '../../config/database.php';
$required_role=ROLE_VENDOR; require_once '../../templates/layouts/auth_wrapper.php';
$db=Database::getInstance(); $user=current_user();
$vendor=$db->prepareOne("SELECT * FROM vendors WHERE user_id=? LIMIT 1",'i',$user['id']);
if(!$vendor||$vendor['verification_status']!==VENDOR_APPROVED){header('Location:'.BASE_URL.'/pages/vendor/onboarding.php');exit;}
$vid=(int)$vendor['id']; $success='';

if ($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['csrf_token'])&&$_POST['csrf_token']===CSRF_TOKEN) {
    $item_id=(int)$_POST['item_id']; $new_status=$_POST['new_status']??'';
    $allowed=['accepted','rejected','packed','shipped','delivered'];
    if($item_id&&in_array($new_status,$allowed)) {
        $db->execute("UPDATE order_items SET item_status=? WHERE id=? AND vendor_id=?",'sii',$new_status,$item_id,$vid);
        // Notify customer
        $oi=$db->prepareOne("SELECT o.user_id,o.id AS oid,o.order_number FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE oi.id=? LIMIT 1",'i',$item_id);
        if($oi) $db->execute("INSERT INTO notifications(user_id,title,message,type,link) VALUES(?,?,?,?,?)",'issss',$oi['user_id'],"Order Update 📦","Item in order {$oi['order_number']} is now ".ucfirst($new_status).".","order",BASE_URL.'/pages/customer/order_detail.php?id='.$oi['oid']);
        $success='Status updated successfully!';
    }
}

$status_filter=trim($_GET['status']??''); $page=(int)($_GET['page']??1); $per=ITEMS_PER_PAGE; $offset=($page-1)*$per;
$where="oi.vendor_id=$vid"; if($status_filter) $where.=" AND oi.item_status='".addslashes($status_filter)."'";
$total=(int)($db->queryOne("SELECT COUNT(*) AS c FROM order_items oi WHERE $where")['c']??0);
$total_pages=max(1,ceil($total/$per));
$orders=$db->query("SELECT oi.*,o.order_number,o.created_at,o.payment_method,o.payment_status,u.name AS customer_name,u.phone AS customer_phone FROM order_items oi JOIN orders o ON o.id=oi.order_id JOIN users u ON u.id=o.user_id WHERE $where ORDER BY o.created_at DESC LIMIT $per OFFSET $offset");

$base=BASE_URL; $page_title='Manage Orders'; $active_page='orders.php'; $sidebar_role='vendor';
require_once '../../templates/layouts/header.php';
?>
<div class="dashboard-layout">
<?php require_once '../../templates/layouts/sidebar.php'; ?>
<main class="dashboard-main">
  <h1 style="font-size:var(--text-2xl);margin-bottom:var(--space-6)">📦 Orders</h1>
  <?php if($success):?><div class="alert alert-success mb-4">✅ <?=htmlspecialchars($success)?></div><?php endif;?>

  <!-- Status Tabs -->
  <div class="flex gap-2 flex-wrap mb-5">
    <a href="?" class="badge <?=!$status_filter?'badge-primary':'badge-muted'?>" style="padding:8px 16px;text-decoration:none">All</a>
    <?php foreach(ITEM_STATUSES as $k=>$s):?><a href="?status=<?=$k?>" class="badge <?=$status_filter===$k?'badge-primary':'badge-muted'?>" style="padding:8px 16px;text-decoration:none"><?=$s['label']?></a><?php endforeach;?>
  </div>

  <?php if($orders):?>
  <div class="table-wrapper">
    <table class="table">
      <thead><tr><th>Order #</th><th>Customer</th><th>Product</th><th>Qty</th><th>Amount</th><th>Status</th><th>Date</th><th>Update Status</th></tr></thead>
      <tbody>
      <?php foreach($orders as $o):$st=ITEM_STATUSES[$o['item_status']]??['label'=>ucfirst($o['item_status']),'color'=>'#6c757d'];?>
        <tr>
          <td class="fw-semibold"><?=htmlspecialchars($o['order_number'])?></td>
          <td><div class="text-sm"><?=htmlspecialchars($o['customer_name'])?></div><div class="text-xs text-muted">📱 <?=htmlspecialchars($o['customer_phone']??'')?></div></td>
          <td class="text-sm"><?=htmlspecialchars($o['product_name'])?></td>
          <td class="text-sm"><?=$o['quantity']?></td>
          <td class="fw-bold text-primary">₹<?=number_format($o['subtotal'],2)?></td>
          <td><span class="badge" style="background:<?=$st['color']?>20;color:<?=$st['color']?>"><?=$st['label']?></span></td>
          <td class="text-xs text-muted"><?=date('d M Y',strtotime($o['created_at']))?></td>
          <td>
            <?php if($o['item_status']!=='delivered'&&$o['item_status']!=='rejected'):
              $next_map=['pending'=>['accepted','rejected'],'accepted'=>['packed'],'packed'=>['shipped'],'shipped'=>['delivered']];
              $nexts=$next_map[$o['item_status']]??[];
              foreach($nexts as $ns):?>
                <form method="POST" style="display:inline-block;margin-right:4px">
                  <input type="hidden" name="csrf_token" value="<?=CSRF_TOKEN?>">
                  <input type="hidden" name="item_id" value="<?=$o['id']?>">
                  <input type="hidden" name="new_status" value="<?=$ns?>">
                  <button type="submit" class="btn btn-sm <?=$ns==='rejected'?'btn-danger':'btn-primary'?>"><?=ucfirst($ns)?></button>
                </form>
            <?php endforeach;endif;?>
          </td>
        </tr>
      <?php endforeach;?>
      </tbody>
    </table>
  </div>
  <?php $current_page=$page; $base_url='?'.http_build_query(array_filter(['status'=>$status_filter])); include '../../templates/components/pagination.php';
  else:?><div class="empty-state"><div class="empty-icon">📦</div><h3>No orders found</h3></div><?php endif;?>
</main>
</div>
<script src="<?=$base?>/assets/js/utils.js"></script>
</body></html>
