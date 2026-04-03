<?php
require_once '../../config/config.php'; require_once '../../config/constants.php'; require_once '../../config/database.php';
$required_role = ROLE_CUSTOMER; require_once '../../templates/layouts/auth_wrapper.php';
$db=Database::getInstance(); $user=current_user(); $errors=[]; $success='';

if ($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['csrf_token'])&&$_POST['csrf_token']===CSRF_TOKEN) {
    $action=$_POST['action']??'profile';
    if ($action==='profile') {
        $name=trim($_POST['name']??''); $phone=trim($_POST['phone']??'');
        if (!$name) $errors[]='Name is required.';
        if ($phone&&!preg_match('/^[6-9]\d{9}$/',$phone)) $errors[]='Invalid phone number.';
        if (empty($errors)) {
            $db->execute("UPDATE users SET name=?,phone=? WHERE id=?",'ssi',$name,$phone,$user['id']);
            $_SESSION['user']['name']=$name;
            $success='Profile updated successfully!';
        }
    } elseif ($action==='password') {
        $cur=$_POST['current_password']??''; $new=$_POST['new_password']??''; $conf=$_POST['confirm_new']??'';
        $u=$db->prepareOne("SELECT password_hash FROM users WHERE id=? LIMIT 1",'i',$user['id']);
        if (!password_verify($cur,$u['password_hash'])) $errors[]='Current password is incorrect.';
        if (strlen($new)<8) $errors[]='New password must be at least 8 characters.';
        if ($new!==$conf) $errors[]='Passwords do not match.';
        if (empty($errors)) { $db->execute("UPDATE users SET password_hash=? WHERE id=?",'si',password_hash($new,PASSWORD_BCRYPT),$user['id']); $success='Password changed successfully!'; }
    } elseif ($action==='add_address') {
        $db->execute("UPDATE addresses SET is_default=0 WHERE user_id=?",'i',$user['id']);
        $db->execute("INSERT INTO addresses(user_id,label,full_name,phone,line1,line2,city,state,pincode,is_default) VALUES(?,?,?,?,?,?,?,?,?,1)",'isssssss',$user['id'],trim($_POST['label']??'Home'),trim($_POST['full_name']??''),trim($_POST['phone']??''),trim($_POST['line1']??''),trim($_POST['line2']??''),trim($_POST['city']??''),trim($_POST['state']??''),trim($_POST['pincode']??''));
        $success='Address added!';
    }
}

$u_full=$db->prepareOne("SELECT * FROM users WHERE id=? LIMIT 1",'i',$user['id']);
$addresses=$db->prepare("SELECT * FROM addresses WHERE user_id=? ORDER BY is_default DESC",'i',$user['id']);
$base=BASE_URL; $page_title='My Profile'; require_once '../../templates/layouts/header.php'; require_once '../../templates/layouts/navbar.php';
?>
<div class="page-content" style="background:var(--color-bg)">
  <div class="container" style="padding-top:var(--space-7);padding-bottom:var(--space-8)">
    <h1 style="font-size:var(--text-3xl);margin-bottom:var(--space-6)">👤 My Profile</h1>
    <?php if ($errors): ?><div class="alert alert-danger mb-4"><?php foreach($errors as $e) echo "<div>⚠️ ".htmlspecialchars($e)."</div>"; ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success mb-4">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-6)">

      <!-- Profile Info -->
      <div class="card p-6">
        <h3 style="margin-bottom:var(--space-5)">Personal Info</h3>
        <form method="POST"><input type="hidden" name="csrf_token" value="<?= CSRF_TOKEN ?>"><input type="hidden" name="action" value="profile">
          <div class="form-group"><label class="form-label">Full Name</label><input type="text" class="form-control" name="name" value="<?= htmlspecialchars($u_full['name']) ?>" required></div>
          <div class="form-group"><label class="form-label">Email (read-only)</label><input type="email" class="form-control" value="<?= htmlspecialchars($u_full['email']) ?>" disabled></div>
          <div class="form-group"><label class="form-label">Mobile</label><input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($u_full['phone']??'') ?>" maxlength="10"></div>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
      </div>

      <!-- Change Password -->
      <div class="card p-6">
        <h3 style="margin-bottom:var(--space-5)">Change Password</h3>
        <form method="POST"><input type="hidden" name="csrf_token" value="<?= CSRF_TOKEN ?>"><input type="hidden" name="action" value="password">
          <div class="form-group"><label class="form-label">Current Password</label><input type="password" class="form-control" name="current_password" required></div>
          <div class="form-group"><label class="form-label">New Password</label><input type="password" class="form-control" name="new_password" required></div>
          <div class="form-group"><label class="form-label">Confirm New Password</label><input type="password" class="form-control" name="confirm_new" required></div>
          <button type="submit" class="btn btn-outline-primary">Change Password</button>
        </form>
      </div>

      <!-- Addresses -->
      <div class="card p-6" style="grid-column:span 2">
        <div class="flex items-center justify-between mb-5">
          <h3>📍 Saved Addresses</h3>
          <button class="btn btn-primary btn-sm" onclick="openModal('add-address')">+ Add Address</button>
        </div>
        <?php if ($addresses): ?>
          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:var(--space-4)">
            <?php foreach($addresses as $addr): ?>
              <div style="border:2px solid <?= $addr['is_default']?'var(--color-primary)':'var(--color-border)' ?>;border-radius:var(--radius-xl);padding:var(--space-4)">
                <?php if($addr['is_default']): ?><span class="badge badge-primary mb-2">Default</span><?php endif; ?>
                <div class="fw-bold text-sm"><?= htmlspecialchars($addr['full_name']) ?> <span class="text-muted">(<?= htmlspecialchars($addr['label']) ?>)</span></div>
                <div class="text-xs text-muted mt-1"><?= htmlspecialchars($addr['line1']) ?><?= $addr['line2']?', '.htmlspecialchars($addr['line2']):'' ?><br><?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['state']) ?> – <?= htmlspecialchars($addr['pincode']) ?></div>
                <div class="text-xs text-muted">📱 <?= htmlspecialchars($addr['phone']) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty-state" style="padding:var(--space-6)"><div class="empty-icon">📍</div><h3>No addresses saved</h3></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Add Address Modal -->
<?php
$modal_id='add-address'; $modal_title='Add Delivery Address';
$modal_body='<form method="POST" id="addr-form"><input type="hidden" name="csrf_token" value="'.CSRF_TOKEN.'"><input type="hidden" name="action" value="add_address">
<div class="form-group"><label class="form-label">Label</label><select class="form-control" name="label"><option>Home</option><option>Work</option><option>Other</option></select></div>
<div class="form-group"><label class="form-label">Full Name <span class="required">*</span></label><input type="text" class="form-control" name="full_name" required></div>
<div class="form-group"><label class="form-label">Phone <span class="required">*</span></label><input type="tel" class="form-control" name="phone" maxlength="10" required></div>
<div class="form-group"><label class="form-label">Address Line 1 <span class="required">*</span></label><input type="text" class="form-control" name="line1" required></div>
<div class="form-group"><label class="form-label">Address Line 2</label><input type="text" class="form-control" name="line2"></div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px"><div class="form-group"><label class="form-label">City *</label><input type="text" class="form-control" name="city" required></div><div class="form-group"><label class="form-label">State *</label><input type="text" class="form-control" name="state" required></div></div>
<div class="form-group"><label class="form-label">Pincode *</label><input type="text" class="form-control" name="pincode" maxlength="6" required></div>
</form>';
$modal_footer='<button class="btn btn-ghost" onclick="closeModal(\'add-address\')">Cancel</button><button class="btn btn-primary" onclick="document.getElementById(\'addr-form\').submit()">Save Address</button>';
include '../../templates/components/modal.php';
?>

<style>@media(max-width:768px){div[style*="grid-template-columns:1fr 1fr"]{grid-template-columns:1fr!important}}</style>
<?php require_once '../../templates/layouts/footer.php'; ?>
