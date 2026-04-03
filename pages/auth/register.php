<?php
/* ===== BACKEND ===== */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../libs/NotificationService.php';

if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/pages/customer/home.php');
    exit;
}

$errors  = [];
$success = false;

// Role detection
$role = $_REQUEST['role'] ?? ROLE_CUSTOMER;
if (!in_array($role, [ROLE_CUSTOMER, ROLE_VENDOR])) {
    $role = ROLE_CUSTOMER;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== CSRF_TOKEN) {
        $errors[] = 'Invalid request. Please refresh and try again.';
    } else {
        $name     = trim($_POST['name']     ?? '');
        $email    = trim($_POST['email']    ?? '');
        $phone    = trim($_POST['phone']    ?? '');
        $password = $_POST['password']      ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        
        $pan_no = trim($_POST['pan_no'] ?? '');
        $gst_no = trim($_POST['gst_no'] ?? '');

        if (!$name)                              $errors[] = 'Full name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email.';
        if (!preg_match('/^[6-9]\d{9}$/', $phone))      $errors[] = 'Enter a valid 10-digit mobile number.';
        if (strlen($password) < 8)               $errors[] = 'Password must be at least 8 characters.';
        if ($password !== $confirm)              $errors[] = 'Passwords do not match.';

        if ($role === ROLE_VENDOR) {
            if (!$pan_no) $errors[] = 'PAN Card number is required for vendors.';
            if (!$gst_no) $errors[] = 'GST Number is required for vendors.';
        }

        if (empty($errors)) {
            $db = Database::getInstance();
            // Check if email or phone already exists
            $exists = $db->prepareOne("SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1", 'ss', $email, $phone);
            
            if ($exists) {
                $errors[] = 'An account with this email or phone already exists.';
            } else {
                // Generate 6-digit OTP
                $otp = rand(100000, 999999);
                $expires = time() + (10 * 60); // 10 minutes from now

                // Store in session
                $_SESSION['pending_registration'] = [
                    'name'     => $name,
                    'email'    => $email,
                    'phone'    => $phone,
                    'password' => $password, // Will hash during final creation for freshness
                    'role'     => $role,
                    'pan_no'   => $pan_no,
                    'gst_no'   => $gst_no
                ];

                $_SESSION['otp_data'] = [
                    'code'    => $otp,
                    'expires' => $expires,
                    'phone'   => $phone
                ];

                // Send OTP
                try {
                    $notifier = NotificationService::getInstance();
                    $notifier->sendOTP($phone, $otp);
                    
                    // Redirect to OTP verification page
                    header('Location: ' . BASE_URL . '/pages/auth/otp_verify.php');
                    exit;
                } catch (Exception $e) {
                    $errors[] = 'Failed to send OTP. Please try again.';
                }
            }
        }
    }
}

/* ===== FRONTEND ===== */
$page_title  = 'Create Account';
$hide_navbar = true;
$body_class  = 'auth-layout';
require_once '../../templates/layouts/header.php';
?>

<div class="auth-container">
  <div class="auth-card" style="max-width: 500px;">
    
    <!-- Unified Branding Header -->
    <div class="auth-logo-wrap" style="margin-bottom: var(--space-6);">
      <a href="<?= BASE_URL ?>" class="navbar-logo" style="transform: scale(1.15);">
        <div class="logo-icon">G</div>
        <span class="logo-text" style="color: var(--color-dark);">Groce<span>esary</span></span>
      </a>
      <div class="text-center mt-6">
        <h2 style="font-size: var(--text-2xl); font-weight: 800; color: var(--color-dark); letter-spacing: -0.01em;">
          <?= ($role === ROLE_VENDOR) ? 'Vendor Registration' : 'Create Account' ?>
        </h2>
        <p style="font-size: 13px; color: var(--color-muted); margin-top: 4px;">
          <?= ($role === ROLE_VENDOR) ? 'Join our marketplace today' : 'Start your grocery journey' ?>
        </p>
      </div>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-danger" style="margin-bottom: var(--space-6);">
        <ul style="margin: 0; padding-left: 14px; font-size: 13px;">
          <?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" id="register-form" novalidate class="flex-col gap-4">
      <input type="hidden" name="csrf_token" value="<?= CSRF_TOKEN ?>">

      <!-- Grid for Name/Phone -->
      <div class="grid grid-cols-2 gap-4" style="margin-bottom: var(--space-4);">
        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label" for="name" style="font-size: 13px; margin-bottom: 6px;">Your Name</label>
          <input type="text" class="form-control" id="name" name="name" placeholder="Full Name"
                 value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required autocomplete="name">
        </div>
        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label" for="phone" style="font-size: 13px; margin-bottom: 6px;">Mobile</label>
          <input type="tel" class="form-control" id="phone" name="phone" placeholder="10-digit number"
                 value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" maxlength="10" required autocomplete="tel">
        </div>
      </div>

      <div class="form-group" style="margin-bottom: var(--space-4);">
        <label class="form-label" for="email" style="font-size: 13px; margin-bottom: 6px;">Email address</label>
        <input type="email" class="form-control" id="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required placeholder="name@example.com">
      </div>

      <!-- Grid for Passwords -->
      <div class="grid grid-cols-2 gap-4" style="margin-bottom: var(--space-4);">
        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label" for="password" style="font-size: 13px; margin-bottom: 6px;">Password</label>
          <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
        </div>
        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label" for="confirm_password" style="font-size: 13px; margin-bottom: 6px;">Confirm</label>
          <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="••••••••">
        </div>
      </div>

      <!-- KYC Fields for Vendor -->
      <?php if ($role === ROLE_VENDOR): ?>
        <div class="grid grid-cols-2 gap-4 mt-2 p-4 rounded-lg" style="background: var(--color-bg); border: 1px dashed var(--color-border); margin-bottom: var(--space-4);">
          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="pan_no" style="font-size: 13px; margin-bottom: 6px;">PAN Card</label>
            <input type="text" class="form-control" id="pan_no" name="pan_no" 
                   value="<?= htmlspecialchars($_POST['pan_no'] ?? '') ?>" maxlength="10" required placeholder="ABCDE1234F" style="text-transform:uppercase">
          </div>
          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" for="gst_no" style="font-size: 13px; margin-bottom: 6px;">GSTIN</label>
            <input type="text" class="form-control" id="gst_no" name="gst_no" 
                   value="<?= htmlspecialchars($_POST['gst_no'] ?? '') ?>" maxlength="15" required placeholder="15-digit GST" style="text-transform:uppercase">
          </div>
        </div>
      <?php endif; ?>

      <div class="form-check" style="margin-bottom: var(--space-6);">
        <input type="checkbox" id="terms" name="terms" required checked>
        <label for="terms" style="font-size: 11px; color: var(--color-muted); line-height: 1.4;">
          By creating an account, you agree to our <a href="#" style="color: var(--color-primary);">Conditions of Use</a> and <a href="#" style="color: var(--color-primary);">Privacy Notice</a>.
        </label>
      </div>

      <button type="submit" class="btn btn-primary btn-full" style="padding: 14px; font-size: 15px; font-weight: 700; border-radius: var(--radius-lg);">
        Create Account
      </button>

      <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">
    </form>

    <div style="text-align: center; margin-top: var(--space-8); padding-top: var(--space-6); border-top: 1px solid var(--color-border);">
      <p style="font-size: 13px; color: var(--color-muted);">
        Already have an account? <a href="<?= BASE_URL ?>/pages/auth/login.php" style="color: var(--color-primary); font-weight: 700;">Sign in</a>
      </p>
    </div>

  </div>

  <!-- Card Footer -->
  <div class="auth-footer-links">
    <a href="<?= BASE_URL ?>/pages/vendor/welcome.php">Become a Vendor</a>
    <a href="#">Conditions</a>
    <a href="#">Privacy</a>
    <a href="#">Help</a>
  </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/utils.js"></script>
<script src="<?= BASE_URL ?>/assets/js/validation.js"></script>
<script>
document.getElementById('register-form').addEventListener('submit', function(e) {
  const valid = Validate.form({
    name:             { rules:{ required:true, minLength:2 } },
    email:            { rules:{ required:true, email:true } },
    phone:            { rules:{ required:true, phone:true } },
    password:         { rules:{ required:true, minLength:8 } },
    confirm_password: { rules:{ required:true, match:'password' } },
  });
  if (!document.getElementById('terms').checked) {
    showToast('Please accept the terms and conditions.', 'warning');
    e.preventDefault(); return;
  }
  if (!valid) e.preventDefault();
});
</script>
</body>
</html>
