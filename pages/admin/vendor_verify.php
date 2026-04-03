<?php
require_once '../../config/config.php'; require_once '../../config/constants.php'; require_once '../../config/database.php';
$required_role=ROLE_ADMIN; require_once '../../templates/layouts/auth_wrapper.php';
$db=Database::getInstance(); $success=''; $errors=[];

$vid2=(int)($_GET['id']??0);
if(!$vid2){header('Location:'.BASE_URL.'/pages/admin/vendors.php');exit;}
$vendor=$db->prepareOne("SELECT v.*,u.name AS owner_name,u.email,u.phone,u.is_active FROM vendors v JOIN users u ON u.id=v.user_id WHERE v.id=? LIMIT 1",'i',$vid2);
if(!$vendor){header('Location:'.BASE_URL.'/pages/admin/vendors.php');exit;}

if ($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['csrf_token'])&&$_POST['csrf_token']===CSRF_TOKEN) {
    $action=$_POST['action']??'';
    if (in_array($action,['approve','reject','ban'])) {
        $status_map=['approve'=>VENDOR_APPROVED,'reject'=>VENDOR_REJECTED,'ban'=>VENDOR_REJECTED];
        $db->execute("UPDATE vendors SET verification_status=?,kyc_notes=?,commission_rate=? WHERE id=?",'ssdi',$status_map[$action],trim($_POST['notes']??''),(float)($_POST['commission_rate']??DEFAULT_COMMISSION),$vid2);
        if ($action==='ban') $db->execute("UPDATE users SET is_active=0 WHERE id=?",'i',$vendor['user_id']);
        $db->execute("INSERT INTO notifications(user_id,title,message,type) VALUES(?,?,?,?)",'isss',$vendor['user_id'],
            $action==='approve'?'✅ Vendor Approved':'Vendor Application Update',
            $action==='approve'?"Your shop has been approved! Start adding products."
                :"Your application was ".($action==='reject'?'rejected':'suspended').". Note: ".trim($_POST['notes']??''),'vendor');
        $success='Vendor '.ucfirst($action).'d successfully!';
        $vendor=$db->prepareOne("SELECT v.*,u.name AS owner_name,u.email,u.phone,u.is_active FROM vendors v JOIN users u ON u.id=v.user_id WHERE v.id=? LIMIT 1",'i',$vid2);
    }
}

$products=$db->prepare("SELECT * FROM products WHERE vendor_id=? ORDER BY created_at DESC LIMIT 10",'i',$vid2);
$stats=['orders'=>$db->prepareOne("SELECT COUNT(DISTINCT order_id) AS c FROM order_items WHERE vendor_id=?",'i',$vid2)['c']??0,'revenue'=>$db->prepareOne("SELECT COALESCE(SUM(vendor_earning),0) AS t FROM order_items WHERE vendor_id=?",'i',$vid2)['t']??0,'commission'=>$db->prepareOne("SELECT COALESCE(SUM(commission),0) AS t FROM order_items WHERE vendor_id=?",'i',$vid2)['t']??0];
$vc=['pending'=>'warning','approved'=>'success','rejected'=>'danger'];

$base=BASE_URL; $page_title='Vendor Details'; $active_page='vendor_verify.php'; $sidebar_role='admin'; $body_class='is-dashboard';
require_once '../../templates/layouts/header.php';
?>
<div class="dashboard-layout">
<?php require_once '../../templates/layouts/sidebar.php'; ?>
<main class="dashboard-main">
  <div class="flex items-center gap-3 mb-6">
    <a href="<?=$base?>/pages/admin/vendors.php" style="color:var(--color-muted)">← Vendors</a>
    <h1 style="font-size:var(--text-2xl)"><?=htmlspecialchars($vendor['shop_name'])?></h1>
    <?php $vv_cls = $vc[$vendor['verification_status']] ?? 'muted'; ?>
    <span class="badge badge-<?= $vv_cls ?>"><?=ucfirst($vendor['verification_status'])?></span>
  </div>

  <?php if($success):?><div class="alert alert-success mb-4">✅ <?=htmlspecialchars($success)?></div><?php endif;?>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-6)">

    <!-- Vendor Info -->
    <div class="card p-6">
      <h3 style="margin-bottom:var(--space-5)">Shop Details</h3>
      <div class="text-sm" style="display:flex;flex-direction:column;gap:var(--space-3)">
        <?php foreach([['Shop Name',htmlspecialchars($vendor['shop_name'])],['Owner',htmlspecialchars($vendor['owner_name'])],['Email',htmlspecialchars($vendor['email'])],['Phone',htmlspecialchars($vendor['phone']??'-')],['City',htmlspecialchars($vendor['city']??'-')],['State',htmlspecialchars($vendor['state']??'-')],['Pincode',htmlspecialchars($vendor['pincode']??'-')],['Registration',date('d M Y',strtotime($vendor['created_at']))]] as [$l,$v]):?>
          <div class="flex justify-between"><span class="text-muted"><?=$l?></span><span class="fw-semibold"><?=$v?></span></div>
        <?php endforeach;?>
      </div>
    </div>

    <!-- KYC & Bank -->
    <div class="card p-6">
      <h3 style="margin-bottom:var(--space-5)">KYC & Bank Details</h3>
      <div class="text-sm" style="display:flex;flex-direction:column;gap:var(--space-3)">
        <?php foreach([['Aadhar No',htmlspecialchars($vendor['aadhar_no']??'-')],['PAN No',htmlspecialchars($vendor['pan_no']??'-')],['GST No',htmlspecialchars($vendor['gst_no']??'-')],['Bank Account',htmlspecialchars($vendor['bank_account']??'-')],['IFSC Code',htmlspecialchars($vendor['bank_ifsc']??'-')],['Bank Name',htmlspecialchars($vendor['bank_name']??'-')]] as [$l,$v]):?>
          <div class="flex justify-between"><span class="text-muted"><?=$l?></span><span class="fw-semibold font-mono"><?=$v?></span></div>
        <?php endforeach;?>
      </div>
    </div>

    <!-- Stats -->
    <div class="card p-6">
      <h3 style="margin-bottom:var(--space-5)">Performance</h3>
      <div class="stat-grid" style="grid-template-columns:repeat(3,1fr)">
        <div class="stat-card"><div class="stat-icon">📦</div><div class="stat-value"><?=$stats['orders']?></div><div class="stat-label">Orders</div></div>
        <div class="stat-card"><div class="stat-icon">💰</div><div class="stat-value">₹<?=number_format($stats['revenue'],0)?></div><div class="stat-label">Earnings</div></div>
        <div class="stat-card"><div class="stat-icon">🎯</div><div class="stat-value">₹<?=number_format($stats['commission'],0)?></div><div class="stat-label">Commission</div></div>
      </div>
    </div>

    <!-- Action Panel -->
    <div class="card p-6">
      <h3 style="margin-bottom:var(--space-5)">Actions</h3>
      <?php if($vendor['verification_status']==='pending'): ?>
        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?=CSRF_TOKEN?>">
          <div class="form-group"><label class="form-label">Commission Rate (%)</label><input type="number" class="form-control" name="commission_rate" value="<?=$vendor['commission_rate']??DEFAULT_COMMISSION?>" step="0.5" min="0" max="50"></div>
          <div class="form-group"><label class="form-label">Notes (optional)</label><textarea class="form-control" name="notes" rows="2"><?=htmlspecialchars($vendor['kyc_notes']??'')?></textarea></div>
          <div class="flex gap-3">
            <button type="submit" name="action" value="approve" class="btn btn-success flex-1">✅ Approve</button>
            <button type="submit" name="action" value="reject"  class="btn btn-danger flex-1" onclick="return confirm('Reject this vendor?')">❌ Reject</button>
          </div>
        </form>
      <?php elseif($vendor['verification_status']==='approved'): ?>
        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?=CSRF_TOKEN?>">
          <div class="form-group"><label class="form-label">Commission Rate (%)</label><input type="number" class="form-control" name="commission_rate" value="<?=$vendor['commission_rate']??DEFAULT_COMMISSION?>" step="0.5" min="0" max="50"></div>
          <div class="form-group"><label class="form-label">Reason for Ban</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
          <button type="submit" name="action" value="ban" class="btn btn-danger btn-full" onclick="return confirm('Ban this vendor?')">🚫 Suspend Vendor</button>
        </form>
      <?php else: ?>
        <p class="text-muted">No actions available for this status.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Products Preview -->
  <?php if($products): ?>
    <div class="card mt-6">
      <div style="padding:var(--space-5);border-bottom:1px solid var(--color-border)"><h3>Products (<?=count($products)?> shown)</h3></div>
      <div class="table-wrapper">
        <table class="table">
          <thead><tr><th>Product</th><th>Price</th><th>Stock</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach($products as $p):?>
            <tr>
              <td class="fw-semibold"><?=htmlspecialchars($p['name'])?></td>
              <td>₹<?=number_format($p['price'],2)?></td>
              <td class="<?=$p['stock']<5?'text-danger':''?>"><?=$p['stock']?></td>
              <td><span class="badge <?=$p['is_active']?'badge-success':'badge-danger'?>"><?=$p['is_active']?'Active':'Inactive'?></span></td>
            </tr>
          <?php endforeach;?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif;?>
</main>
</div>
<script src="<?=$base?>/assets/js/utils.js"></script>
</body></html>
