<?php
require_once '../../config/config.php'; require_once '../../config/constants.php'; require_once '../../config/database.php';
$required_role=ROLE_VENDOR; require_once '../../templates/layouts/auth_wrapper.php';
$db=Database::getInstance(); $user=current_user();
$vendor=$db->prepareOne("SELECT * FROM vendors WHERE user_id=? LIMIT 1",'i',$user['id']);
if(!$vendor){header('Location:'.BASE_URL.'/pages/vendor/onboarding.php');exit;}
$vid=(int)$vendor['id']; $errors=[]; $success='';

if ($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['csrf_token'])&&$_POST['csrf_token']===CSRF_TOKEN) {
    $action=$_POST['action']??'';
    if ($action==='shop') {
        $sn=trim($_POST['shop_name']??''); $sd=trim($_POST['shop_description']??''); $sa=trim($_POST['shop_address']??'');
        $city=trim($_POST['city']??''); $st2=trim($_POST['state']??''); $pin=trim($_POST['pincode']??'');
        $dt=trim($_POST['delivery_time']??''); $min=(float)($_POST['min_order_amount']??0);
        if (!$sn) $errors[]='Shop name is required.';
        if(empty($errors)) { $db->execute("UPDATE vendors SET shop_name=?,shop_description=?,shop_address=?,city=?,state=?,pincode=?,delivery_time=?,min_order_amount=? WHERE id=?",'sssssssd',$sn,$sd,$sa,$city,$st2,$pin,$dt,$min); $db->execute("UPDATE vendors SET min_order_amount=? WHERE id=?",'di',$min,$vid); $success='Shop profile updated!'; $vendor=$db->prepareOne("SELECT * FROM vendors WHERE id=? LIMIT 1",'i',$vid); }
    } elseif ($action==='bank') {
        $db->execute("UPDATE vendors SET bank_account=?,bank_ifsc=?,bank_name=? WHERE id=?",'sssi',trim($_POST['bank_account']??''),strtoupper(trim($_POST['bank_ifsc']??'')),trim($_POST['bank_name']??''),$vid);
        $success='Bank details updated!';
    }
}

$base=BASE_URL; $page_title='Shop Profile'; $active_page='profile.php'; $sidebar_role='vendor';
require_once '../../templates/layouts/header.php';
?>
<div class="dashboard-layout">
<?php require_once '../../templates/layouts/sidebar.php'; ?>
<main class="dashboard-main">
  <h1 style="font-size:var(--text-2xl);margin-bottom:var(--space-6)">👤 Shop Profile</h1>
  <?php if($errors):?><div class="alert alert-danger mb-4"><?php foreach($errors as $e) echo "<div>⚠️ ".htmlspecialchars($e)."</div>"; ?></div><?php endif;?>
  <?php if($success):?><div class="alert alert-success mb-4">✅ <?=htmlspecialchars($success)?></div><?php endif;?>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-6)">
    <div class="card p-6">
      <h3 style="margin-bottom:var(--space-5)">Shop Details</h3>
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?=CSRF_TOKEN?>"><input type="hidden" name="action" value="shop">
        <div class="form-group"><label class="form-label">Shop Name *</label><input type="text" class="form-control" name="shop_name" value="<?=htmlspecialchars($vendor['shop_name']??'')?>" required></div>
        <div class="form-group"><label class="form-label">Description</label><textarea class="form-control" name="shop_description" rows="3"><?=htmlspecialchars($vendor['shop_description']??'')?></textarea></div>
        <div class="form-group"><label class="form-label">Address</label><textarea class="form-control" name="shop_address" rows="2"><?=htmlspecialchars($vendor['shop_address']??'')?></textarea></div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
          <div class="form-group"><label class="form-label">City</label><input type="text" class="form-control" name="city" value="<?=htmlspecialchars($vendor['city']??'')?>"></div>
          <div class="form-group"><label class="form-label">State</label><input type="text" class="form-control" name="state" value="<?=htmlspecialchars($vendor['state']??'')?>"></div>
          <div class="form-group"><label class="form-label">Pincode</label><input type="text" class="form-control" name="pincode" value="<?=htmlspecialchars($vendor['pincode']??'')?>"></div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div class="form-group"><label class="form-label">Delivery Time</label><input type="text" class="form-control" name="delivery_time" value="<?=htmlspecialchars($vendor['delivery_time']??'30-60 min')?>" placeholder="e.g. 30-45 min"></div>
          <div class="form-group"><label class="form-label">Min. Order (₹)</label><input type="number" class="form-control" name="min_order_amount" value="<?=$vendor['min_order_amount']??0?>" min="0"></div>
        </div>
        <button type="submit" class="btn btn-primary">Save Profile</button>
      </form>
    </div>

    <div class="card p-6">
      <h3 style="margin-bottom:var(--space-5)">Bank Details</h3>
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?=CSRF_TOKEN?>"><input type="hidden" name="action" value="bank">
        <div class="form-group"><label class="form-label">Account Number</label><input type="text" class="form-control" name="bank_account" value="<?=htmlspecialchars($vendor['bank_account']??'')?>"></div>
        <div class="form-group"><label class="form-label">IFSC Code</label><input type="text" class="form-control" name="bank_ifsc" value="<?=htmlspecialchars($vendor['bank_ifsc']??'')?>" style="text-transform:uppercase"></div>
        <div class="form-group"><label class="form-label">Bank Name</label><input type="text" class="form-control" name="bank_name" value="<?=htmlspecialchars($vendor['bank_name']??'')?>"></div>
        <button type="submit" class="btn btn-outline-primary">Update Bank Details</button>
      </form>

      <hr class="divider">
      <h4 style="margin-bottom:var(--space-4)">KYC Status</h4>
      <?php $kyc_colors=['pending'=>'warning','submitted'=>'info','approved'=>'success','rejected'=>'danger']; ?>
      <div class="flex items-center gap-3">
        <?php $kyc_cls = $kyc_colors[$vendor['kyc_status']] ?? 'muted'; ?>
        <span class="badge badge-<?= $kyc_cls ?>"><?=ucfirst($vendor['kyc_status']??'unknown')?></span>
        <?php if($vendor['kyc_notes']):?><span class="text-xs text-muted"><?=htmlspecialchars($vendor['kyc_notes'])?></span><?php endif;?>
      </div>
    </div>
  </div>
</main>
</div>
<script src="<?=$base?>/assets/js/utils.js"></script>
</body></html>
