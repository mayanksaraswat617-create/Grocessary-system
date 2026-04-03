<?php
require_once '../../config/config.php'; require_once '../../config/constants.php'; require_once '../../config/database.php';
$required_role=ROLE_VENDOR; require_once '../../templates/layouts/auth_wrapper.php';
$db=Database::getInstance(); $user=current_user();
$vendor=$db->prepareOne("SELECT * FROM vendors WHERE user_id=? LIMIT 1",'i',$user['id']);
if(!$vendor||$vendor['verification_status']!==VENDOR_APPROVED){header('Location:'.BASE_URL.'/pages/vendor/onboarding.php');exit;}
$vid=(int)$vendor['id']; $errors=[]; $success='';

$earned=(float)($db->prepareOne("SELECT COALESCE(SUM(vendor_earning),0) AS t FROM order_items WHERE vendor_id=?",'i',$vid)['t']??0);
$paid=(float)($db->prepareOne("SELECT COALESCE(SUM(amount),0) AS t FROM payouts WHERE vendor_id=? AND status='paid'",'i',$vid)['t']??0);
$pending_payout=(float)($db->prepareOne("SELECT COALESCE(SUM(amount),0) AS t FROM payouts WHERE vendor_id=? AND status='pending'",'i',$vid)['t']??0);
$balance=$earned-$paid-$pending_payout;

if ($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['csrf_token'])&&$_POST['csrf_token']===CSRF_TOKEN) {
    $amount=(float)($_POST['amount']??0); $notes=trim($_POST['notes']??'');
    if ($amount<=0) $errors[]='Enter a valid amount.';
    elseif ($amount>$balance) $errors[]='Requested amount exceeds available balance (₹'.number_format($balance,2).').';
    else { $db->execute("INSERT INTO payouts(vendor_id,amount,notes) VALUES(?,?,?)",'ids',$vid,$amount,$notes); $success='Payout request submitted!'; $balance-=$amount; }
}

$payouts=$db->prepare("SELECT * FROM payouts WHERE vendor_id=? ORDER BY requested_at DESC LIMIT 20",'i',$vid);
$payout_statuses=['pending'=>['label'=>'Pending','color'=>'#f39c12'],'approved'=>['label'=>'Approved','color'=>'#3498db'],'rejected'=>['label'=>'Rejected','color'=>'#e74c3c'],'paid'=>['label'=>'Paid','color'=>'#2ecc71']];

$base=BASE_URL; $page_title='Payouts'; $active_page='payouts.php'; $sidebar_role='vendor';
require_once '../../templates/layouts/header.php';
?>
<div class="dashboard-layout">
<?php require_once '../../templates/layouts/sidebar.php'; ?>
<main class="dashboard-main">
  <h1 style="font-size:var(--text-2xl);margin-bottom:var(--space-6)">💸 Payouts</h1>

  <!-- Balance -->
  <div class="card p-6 mb-6" style="background:var(--gradient-secondary)">
    <div style="color:rgba(255,255,255,0.7);font-size:var(--text-sm);margin-bottom:var(--space-2)">Available Balance</div>
    <div style="color:#fff;font-size:3rem;font-weight:800;font-family:var(--font-heading)">₹<?=number_format($balance,2)?></div>
    <div style="color:rgba(255,255,255,0.6);font-size:var(--text-xs);margin-top:var(--space-2)">Gross ₹<?=number_format($earned,2)?> · Paid ₹<?=number_format($paid,2)?> · Pending ₹<?=number_format($pending_payout,2)?></div>
  </div>

  <?php if($errors):?><div class="alert alert-danger mb-4"><?php foreach($errors as $e) echo "<div>⚠️ ".htmlspecialchars($e)."</div>"; ?></div><?php endif;?>
  <?php if($success):?><div class="alert alert-success mb-4">✅ <?=htmlspecialchars($success)?></div><?php endif;?>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-6)">
    <!-- Request Payout Form -->
    <div class="card p-6">
      <h3 style="margin-bottom:var(--space-5)">Request Payout</h3>
      <?php if($balance>0):?>
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?=CSRF_TOKEN?>">
        <div class="form-group"><label class="form-label">Amount (₹) *</label><input type="number" class="form-control" name="amount" step="0.01" min="100" max="<?=$balance?>" placeholder="Enter withdrawal amount" required><div class="form-hint">Available: ₹<?=number_format($balance,2)?></div></div>
        <div class="form-group"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2" placeholder="Optional note for the payout request"></textarea></div>
        <div class="alert alert-info mb-4" style="font-size:var(--text-xs)">Payouts are processed to your bank account registered during onboarding. Processing time: 3-5 business days.</div>
        <button type="submit" class="btn btn-primary btn-full">Request Payout →</button>
      </form>
      <?php else:?><div class="text-muted text-sm">No balance available for withdrawal.</div><?php endif;?>
    </div>

    <!-- Vendor Bank Info -->
    <div class="card p-6">
      <h3 style="margin-bottom:var(--space-4)">Bank Details</h3>
      <div class="text-sm" style="display:flex;flex-direction:column;gap:var(--space-3)">
        <div><span class="text-muted">Account Number:</span> <span class="fw-semibold"><?=htmlspecialchars($vendor['bank_account']?:'Not set')?></span></div>
        <div><span class="text-muted">IFSC Code:</span> <span class="fw-semibold"><?=htmlspecialchars($vendor['bank_ifsc']?:'Not set')?></span></div>
        <div><span class="text-muted">Bank Name:</span> <span class="fw-semibold"><?=htmlspecialchars($vendor['bank_name']?:'Not set')?></span></div>
      </div>
      <a href="<?=$base?>/pages/vendor/profile.php" class="btn btn-ghost btn-sm mt-4">Update Bank Details</a>
    </div>
  </div>

  <!-- Payout History -->
  <div class="card mt-6">
    <div style="padding:var(--space-5);border-bottom:1px solid var(--color-border)"><h3>Payout History</h3></div>
    <?php if($payouts):?>
    <div class="table-wrapper">
      <table class="table">
        <thead><tr><th>Date</th><th>Amount</th><th>Status</th><th>Notes</th><th>Processed</th></tr></thead>
        <tbody>
        <?php foreach($payouts as $p):$st=$payout_statuses[$p['status']]??['label'=>ucfirst($p['status']),'color'=>'#6c757d'];?>
          <tr>
            <td class="text-sm"><?=date('d M Y',strtotime($p['requested_at']))?></td>
            <td class="fw-bold text-primary">₹<?=number_format($p['amount'],2)?></td>
            <td><span class="badge" style="background:<?=$st['color']?>20;color:<?=$st['color']?>"><?=$st['label']?></span></td>
            <td class="text-xs text-muted"><?=htmlspecialchars($p['notes']??'')?></td>
            <td class="text-xs text-muted"><?=$p['processed_at']?date('d M Y',strtotime($p['processed_at'])):'-'?></td>
          </tr>
        <?php endforeach;?>
        </tbody>
      </table>
    </div>
    <?php else:?><div class="empty-state" style="padding:var(--space-6)"><div class="empty-icon">💸</div><h3>No payout history</h3></div><?php endif;?>
  </div>
</main>
</div>
<script src="<?=$base?>/assets/js/utils.js"></script>
</body></html>
