<?php
require_once '../../config/config.php'; require_once '../../config/constants.php'; require_once '../../config/database.php';
if (!is_logged_in()) { header('Location:'.BASE_URL.'/pages/auth/login.php'); exit; }
$db=Database::getInstance(); $user=current_user(); $errors=[]; $success='';

// Check if vendor profile already exists
$vendor_exists=$db->prepareOne("SELECT id,verification_status FROM vendors WHERE user_id=? LIMIT 1",'i',$user['id']);

if ($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['csrf_token'])&&$_POST['csrf_token']===CSRF_TOKEN) {
    $shop_name=trim($_POST['shop_name']??''); $shop_desc=trim($_POST['shop_description']??'');
    $address=trim($_POST['shop_address']??''); $city=trim($_POST['city']??''); $state=trim($_POST['state']??''); $pincode=trim($_POST['pincode']??'');
    $aadhar=trim($_POST['aadhar_no']??''); $pan=strtoupper(trim($_POST['pan_no']??'')); $bank_acc=trim($_POST['bank_account']??''); $bank_ifsc=strtoupper(trim($_POST['bank_ifsc']??'')); $bank_name=trim($_POST['bank_name']??'');

    if (!$shop_name) $errors[]='Shop name is required.';
    if (!$city||!$state||!$pincode) $errors[]='City, state and pincode are required.';

    if (empty($errors)) {
        // Update user role to vendor
        $db->execute("UPDATE users SET role='vendor' WHERE id=?",'i',$user['id']);
        $_SESSION['user']['role']='vendor';
        if ($vendor_exists) {
            $db->execute("UPDATE vendors SET shop_name=?,shop_description=?,shop_address=?,city=?,state=?,pincode=?,aadhar_no=?,pan_no=?,bank_account=?,bank_ifsc=?,bank_name=? WHERE user_id=?",'sssssssssssi',$shop_name,$shop_desc,$address,$city,$state,$pincode,$aadhar,$pan,$bank_acc,$bank_ifsc,$bank_name,$user['id']);
        } else {
            $db->execute("INSERT INTO vendors(user_id,shop_name,shop_description,shop_address,city,state,pincode,aadhar_no,pan_no,bank_account,bank_ifsc,bank_name) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)",'isssssssssss',$user['id'],$shop_name,$shop_desc,$address,$city,$state,$pincode,$aadhar,$pan,$bank_acc,$bank_ifsc,$bank_name);
            $vid=$db->lastInsertId();
            $_SESSION['user']['vendor_id']=$vid;
            $_SESSION['user']['vendor_status']='pending';
        }
        $success='Application submitted! Our team will review your details within 24-48 hours.';
        $vendor_exists=$db->prepareOne("SELECT id,verification_status FROM vendors WHERE user_id=? LIMIT 1",'i',$user['id']);
    }
}

$base=BASE_URL; $page_title='Become a Vendor – Onboarding'; require_once '../../templates/layouts/header.php';
?>
<div style="min-height:100vh;background:var(--color-bg);padding:var(--space-8) var(--space-5)">
  <div class="container-sm">
    <div style="text-align:center;margin-bottom:var(--space-7)">
      <div style="font-size:3.5rem;margin-bottom:var(--space-4)">🏪</div>
      <h1>Become a Groceesary Vendor</h1>
      <p class="text-muted">Join 500+ local vendors earning online. Quick setup, low commission rates.</p>
    </div>

    <?php if ($vendor_exists&&$vendor_exists['verification_status']==='pending'&&!$_POST): ?>
      <div class="card p-7 text-center">
        <div style="font-size:3rem;margin-bottom:var(--space-4)">⏳</div>
        <h2>Application Under Review</h2>
        <p class="text-muted">Your vendor application is being reviewed by our team. You'll receive notification within 24-48 hours.</p>
        <a href="<?= $base ?>/pages/customer/home.php" class="btn btn-outline-primary mt-4">Go to Homepage</a>
      </div>
    <?php elseif ($vendor_exists&&$vendor_exists['verification_status']==='approved'): ?>
      <div class="card p-7 text-center">
        <div style="font-size:3rem;margin-bottom:var(--space-4)">✅</div>
        <h2>You're Already a Vendor!</h2>
        <a href="<?= $base ?>/pages/vendor/dashboard.php" class="btn btn-primary mt-4">Go to Dashboard →</a>
      </div>
    <?php else: ?>
      <?php if ($errors): ?><div class="alert alert-danger mb-5"><?php foreach($errors as $e) echo "<div>⚠️ ".htmlspecialchars($e)."</div>"; ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success mb-5">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>

      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= CSRF_TOKEN ?>">

        <!-- Progress Steps -->
        <div class="flex gap-2 mb-7">
          <?php foreach(['Shop Details','Location','KYC Documents','Bank Info'] as $i=>$step): ?>
            <div style="flex:1;text-align:center">
              <div style="width:32px;height:32px;border-radius:50%;background:var(--gradient-primary);color:#fff;font-weight:700;font-size:var(--text-sm);display:flex;align-items:center;justify-content:center;margin:0 auto"><?= $i+1 ?></div>
              <div style="font-size:10px;margin-top:4px;font-weight:600"><?= $step ?></div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="card p-6 mb-5">
          <h3 style="margin-bottom:var(--space-5)">🏪 Shop Details</h3>
          <div class="form-group"><label class="form-label">Shop Name <span class="required">*</span></label><input type="text" class="form-control" name="shop_name" value="<?= htmlspecialchars($vendor_exists?($db->prepareOne("SELECT shop_name FROM vendors WHERE user_id=?",'i',$user['id'])['shop_name']??''):'') ?>" required></div>
          <div class="form-group"><label class="form-label">Shop Description</label><textarea class="form-control" name="shop_description" rows="3" placeholder="Tell customers about your shop…"></textarea></div>
        </div>

        <div class="card p-6 mb-5">
          <h3 style="margin-bottom:var(--space-5)">📍 Location</h3>
          <div class="form-group"><label class="form-label">Shop Address <span class="required">*</span></label><textarea class="form-control" name="shop_address" rows="2" required></textarea></div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:var(--space-4)">
            <div class="form-group"><label class="form-label">City *</label><input type="text" class="form-control" name="city" required></div>
            <div class="form-group"><label class="form-label">State *</label><input type="text" class="form-control" name="state" required></div>
            <div class="form-group"><label class="form-label">Pincode *</label><input type="text" class="form-control" name="pincode" maxlength="6" required></div>
          </div>
        </div>

        <div class="card p-6 mb-5">
          <h3 style="margin-bottom:var(--space-5)">📄 KYC Details</h3>
          <div class="alert alert-info mb-4">Your KYC documents are stored securely and used only for verification.</div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4)">
            <div class="form-group"><label class="form-label">Aadhar Number</label><input type="text" class="form-control" name="aadhar_no" placeholder="12-digit Aadhar number" maxlength="12"></div>
            <div class="form-group"><label class="form-label">PAN Number</label><input type="text" class="form-control" name="pan_no" placeholder="ABCDE1234F" maxlength="10" style="text-transform:uppercase"></div>
          </div>
        </div>

        <div class="card p-6 mb-5">
          <h3 style="margin-bottom:var(--space-5)">🏦 Bank Details</h3>
          <div class="form-group"><label class="form-label">Account Number</label><input type="text" class="form-control" name="bank_account" placeholder="Bank account number"></div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4)">
            <div class="form-group"><label class="form-label">IFSC Code</label><input type="text" class="form-control" name="bank_ifsc" placeholder="SBIN0001234" style="text-transform:uppercase"></div>
            <div class="form-group"><label class="form-label">Bank Name</label><input type="text" class="form-control" name="bank_name" placeholder="State Bank of India"></div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-full btn-xl">Submit Application →</button>
      </form>
    <?php endif; ?>
  </div>
</div>
<script src="<?= $base ?>/assets/js/utils.js"></script>
</body></html>
