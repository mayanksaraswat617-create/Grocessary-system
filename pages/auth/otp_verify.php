<?php
/* ===== BACKEND ===== */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../libs/NotificationService.php';

// If already logged in, redirect
if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/pages/customer/home.php');
    exit;
}

// Redirect if no pending registration
if (!isset($_SESSION['pending_registration']) || !isset($_SESSION['otp_data'])) {
    header('Location: ' . BASE_URL . '/pages/auth/register.php');
    exit;
}

$pending = $_SESSION['pending_registration'];
$otp_data = $_SESSION['otp_data'];
$errors = [];
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'verify';

    if ($action === 'verify') {
        $entered_otp = implode('', $_POST['otp'] ?? []);
        
        if (strlen($entered_otp) !== 6) {
            $errors[] = 'Please enter a valid 6-digit OTP.';
        } elseif ($entered_otp != $otp_data['code']) {
            $errors[] = 'Invalid OTP. Please check the code sent to your mobile.';
        } elseif (time() > $otp_data['expires']) {
            $errors[] = 'OTP has expired. Please request a new one.';
        } else {
            // SUCCESS! Create the account
            $db = Database::getInstance();
            $hash = password_hash($pending['password'], PASSWORD_BCRYPT);
            
            try {
                $db->execute(
                    "INSERT INTO users (name, email, phone, password_hash, role, phone_verified, is_active) VALUES (?,?,?,?,?,1,1)",
                    'sssss', $pending['name'], $pending['email'], $pending['phone'], $hash, $pending['role']
                );
                $userId = $db->lastInsertId();

                if ($userId && $pending['role'] === ROLE_VENDOR) {
                    $db->execute(
                        "INSERT INTO vendors (user_id, shop_name, pan_no, gst_no, verification_status) VALUES (?,?,?,?,'pending')",
                        'isss', $userId, $pending['name'] . "'s Shop", $pending['pan_no'], $pending['gst_no']
                    );
                }

                if ($userId) {
                    // Log the user in
                    $_SESSION['user'] = [
                        'id'        => $userId,
                        'name'      => $pending['name'],
                        'email'     => $pending['email'],
                        'role'      => $pending['role'],
                        'avatar'    => null,
                        'vendor_id' => null
                    ];
                    session_regenerate_id(true);

                    // Send Welcome Message
                    $notifier = NotificationService::getInstance();
                    $notifier->sendWelcomeMessage($pending['phone'], $pending['name'], $pending['role']);

                    // Clear session data
                    unset($_SESSION['pending_registration']);
                    unset($_SESSION['otp_data']);

                    // Redirect
                    if ($pending['role'] === ROLE_VENDOR) {
                        header('Location: ' . BASE_URL . '/pages/vendor/onboarding.php?new=1');
                    } else {
                        header('Location: ' . BASE_URL . '/pages/customer/home.php?registered=1');
                    }
                    exit;
                }
            } catch (Exception $e) {
                $errors[] = 'Registration failed: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'resend') {
        // Handle Resend
        $new_otp = rand(100000, 999999);
        $otp_data['code'] = $new_otp;
        $otp_data['expires'] = time() + (10 * 60);
        $_SESSION['otp_data'] = $otp_data;

        $notifier = NotificationService::getInstance();
        $notifier->sendOTP($otp_data['phone'], $new_otp);
        $success_msg = 'A new OTP has been sent to ' . $otp_data['phone'];
    }
}

/* ===== FRONTEND ===== */
$page_title  = 'Verify OTP';
$hide_navbar = true;
$body_class  = 'auth-layout';
require_once '../../templates/layouts/header.php';
?>

<div class="auth-container">
  <div class="auth-card" style="max-width: 450px;">
    
    <div class="auth-logo-wrap" style="margin-bottom: var(--space-6);">
      <a href="<?= BASE_URL ?>" class="navbar-logo">
        <div class="logo-icon">G</div>
        <span class="logo-text" style="color: var(--color-dark);">Groce<span>esary</span></span>
      </a>
      <div class="text-center mt-6">
        <h2 style="font-size: var(--text-2xl); font-weight: 800; color: var(--color-dark);">Verify Mobile Number</h2>
        <p style="font-size: 13px; color: var(--color-muted); margin-top: 4px;">
          We have sent a 6-digit code to <strong><?= htmlspecialchars($otp_data['phone']) ?></strong>
        </p>
      </div>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-danger mb-6">
        <?php foreach($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($success_msg): ?>
      <div class="alert alert-success mb-6">
        <?= htmlspecialchars($success_msg) ?>
      </div>
    <?php endif; ?>

    <form method="POST" id="otp-form" class="flex-col gap-6">
      <input type="hidden" name="csrf_token" value="<?= CSRF_TOKEN ?>">
      <input type="hidden" name="action" value="verify">

      <div class="otp-input-wrapper" style="display: flex; gap: 10px; justify-content: center; margin: 20px 0;">
        <?php for($i=0; $i<6; $i++): ?>
          <input type="text" name="otp[]" class="otp-digit" maxlength="1" required
                 style="width: 50px; height: 60px; text-align: center; font-size: 24px; font-weight: 700; border: 2px solid var(--color-border); border-radius: 12px; background: #fff; transition: all 0.2s;"
                 oninput="this.value=this.value.replace(/[^0-9]/g,''); if(this.value.length==1) this.nextElementSibling?.focus()"
                 onkeydown="if(event.key=='Backspace' && !this.value) this.previousElementSibling?.focus()">
        <?php endfor; ?>
      </div>

      <button type="submit" class="btn btn-primary btn-full" style="padding: 14px; font-size: 16px; font-weight: 700;">
        Verify & Create Account
      </button>
    </form>

    <div style="text-align: center; margin-top: 24px;">
      <p style="font-size: 13px; color: var(--color-muted);">
        Didn't receive the code? 
        <form method="POST" style="display: inline;">
          <input type="hidden" name="action" value="resend">
          <input type="hidden" name="csrf_token" value="<?= CSRF_TOKEN ?>">
          <button type="submit" class="btn btn-link" id="resend-btn" style="padding: 0; font-size: 13px; color: var(--color-primary); font-weight: 700; text-decoration: none; border: none; background: transparent; cursor: pointer;">
            Resend OTP
          </button>
        </form>
      </p>
      <a href="register.php" style="display: block; margin-top: 15px; font-size: 12px; color: var(--color-muted); text-decoration: none;">
        ← Edit Mobile Number
      </a>
    </div>

  </div>
</div>

<style>
.otp-digit:focus {
  border-color: var(--color-primary) !important;
  box-shadow: 0 0 0 4px rgba(243, 168, 71, 0.15);
  outline: none;
}
.btn-link:hover {
  text-decoration: underline !important;
}
</style>

<script>
// Auto-focus first digit
document.querySelector('.otp-digit').focus();

// Paste handling
document.querySelector('.otp-input-wrapper').addEventListener('paste', function(e) {
  const data = e.clipboardData.getData('text').trim();
  if (data.length === 6 && /^\d+$/.test(data)) {
    const inputs = document.querySelectorAll('.otp-digit');
    data.split('').forEach((digit, i) => inputs[i].value = digit);
    inputs[5].focus();
  }
});
</script>

</body>
</html>
