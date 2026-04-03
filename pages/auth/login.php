<?php
/* ===== BACKEND ===== */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';

// Logout handler
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = [];
    session_destroy();
    header('Location: ' . BASE_URL . '/pages/auth/login.php?msg=logged_out');
    exit;
}

// Already logged in → redirect
if (is_logged_in()) {
    $role_home = [
        ROLE_CUSTOMER => BASE_URL . '/pages/customer/home.php',
        ROLE_VENDOR   => BASE_URL . '/pages/vendor/dashboard.php',
        ROLE_ADMIN    => BASE_URL . '/pages/admin/dashboard.php',
    ];
    header('Location: ' . ($role_home[user_role()] ?? BASE_URL . '/pages/customer/home.php'));
    exit;
}

$error   = '';
$success = '';
$next    = $_GET['next'] ?? '';
$role    = $_GET['role'] ?? '';

if (isset($_GET['msg']) && $_GET['msg'] === 'logged_out') {
    $success = 'You have been logged out successfully.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== CSRF_TOKEN) {
        $error = 'Invalid request. Please refresh and try again.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $error = 'Please fill in all fields.';
        } else {
            $db   = Database::getInstance();
            $user = $db->prepareOne(
                "SELECT u.*, v.id AS vendor_id, v.verification_status FROM users u
                 LEFT JOIN vendors v ON v.user_id = u.id
                 WHERE u.email = ? LIMIT 1",
                's', $email
            );

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $error = 'Invalid email or password.';
            } elseif (!$user['is_active']) {
                $error = 'Your account has been suspended. Contact support.';
            } elseif ($user['role'] === ROLE_VENDOR && $user['verification_status'] === VENDOR_REJECTED) {
                $error = 'Your vendor application was rejected. Contact support.';
            } else {
                // Set session
                $_SESSION['user'] = [
                    'id'        => (int)$user['id'],
                    'name'      => $user['name'],
                    'email'     => $user['email'],
                    'role'      => $user['role'],
                    'avatar'    => $user['avatar'],
                    'vendor_id' => $user['vendor_id'] ? (int)$user['vendor_id'] : null,
                    'vendor_status' => $user['verification_status'] ?? null,
                ];
                session_regenerate_id(true);

                $redirect = $next ?: ($user['role'] === ROLE_ADMIN
                    ? BASE_URL . '/pages/admin/dashboard.php'
                    : ($user['role'] === ROLE_VENDOR
                        ? BASE_URL . '/pages/vendor/dashboard.php'
                        : BASE_URL . '/pages/customer/home.php'));
                header('Location: ' . $redirect);
                exit;
            }
        }
    }
}

/* ===== FRONTEND ===== */
$page_title  = ($role === ROLE_VENDOR) ? 'Vendor Login' : 'Login';
$hide_navbar = true;
$body_class  = 'auth-layout';
require_once '../../templates/layouts/header.php';
?>

<div class="auth-container">
  <div class="auth-card" style="max-width: 440px;">
    
    <!-- Unified Branding Header -->
    <div class="auth-logo-wrap">
      <a href="<?= BASE_URL ?>" class="navbar-logo" style="transform: scale(1.2);">
        <div class="logo-icon" style="box-shadow: var(--shadow-sm);">G</div>
        <span class="logo-text" style="color: var(--color-dark);">Groce<span>esary</span></span>
      </a>
      <div class="text-center mt-6">
        <h2 style="font-size: var(--text-2xl); font-weight: 800; color: var(--color-dark); letter-spacing: -0.01em;">
          <?= ($role === ROLE_VENDOR) ? 'Vendor Portal' : 'Welcome Back' ?>
        </h2>
        <p style="font-size: 13px; color: var(--color-muted); margin-top: 4px;">
          <?= ($role === ROLE_VENDOR) ? 'Manage your shop and orders' : 'Log in to continue shopping' ?>
        </p>
      </div>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger" style="margin-bottom: var(--space-6);">
        <span style="font-size: 1.2rem;">⚠️</span>
        <div style="font-size: 13px;"><strong>Problem:</strong> <?= htmlspecialchars($error) ?></div>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success" style="margin-bottom: var(--space-6);">
        <span style="font-size: 1.2rem;">✅</span>
        <div style="font-size: 13px;"><?= htmlspecialchars($success) ?></div>
      </div>
    <?php endif; ?>

    <form method="POST" id="login-form" novalidate>
      <input type="hidden" name="csrf_token" value="<?= CSRF_TOKEN ?>">

      <div class="form-group" style="margin-bottom: var(--space-4);">
        <label class="form-label" for="email" style="font-size: 13px; margin-bottom: 6px;">Email Address</label>
        <input type="email" class="form-control" id="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" autocomplete="email" required placeholder="name@example.com">
      </div>

      <div class="form-group" style="margin-bottom: var(--space-6);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
          <label class="form-label" for="password" style="font-size: 13px; margin: 0;">Password</label>
          <a href="<?= BASE_URL ?>/pages/auth/forgot_password.php" style="font-size: 12px; color: var(--color-primary); font-weight: 600;">Forgot Password?</a>
        </div>
        <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required placeholder="••••••••">
      </div>

      <button type="submit" class="btn btn-primary btn-full" style="padding: 14px; font-size: 15px; letter-spacing: 0.02em; border-radius: var(--radius-lg);" id="login-btn">
        Sign In
      </button>

      <div style="text-align: center; margin-top: var(--space-6); font-size: 11px; color: var(--color-muted); line-height: 1.6;">
        By continuing, you agree to Groceesary's <a href="#" style="color: var(--color-primary); font-weight: 500;">Terms of Use</a> and <a href="#" style="color: var(--color-primary); font-weight: 500;">Privacy Notice</a>.
      </div>
    </form>

    <div class="divider-new">New to Groceesary?</div>

    <a href="<?= BASE_URL ?>/pages/auth/register.php<?= ($role?'?role='.$role:'') ?>" class="btn btn-outline-primary btn-full" style="padding: 12px; font-size: 14px; border-radius: var(--radius-lg);">
      Create your Groceesary account
    </a>

    <!-- Card Footer (Internal-Links) -->
    <div class="auth-footer-links">
      <a href="<?= BASE_URL ?>/pages/vendor/welcome.php">Sell on Groceesary</a>
      <a href="#">Conditions</a>
      <a href="#">Privacy</a>
      <a href="#">Help</a>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/utils.js"></script>
<script src="<?= BASE_URL ?>/assets/js/validation.js"></script>
<script>
document.getElementById('login-form').addEventListener('submit', function(e) {
  const valid = Validate.form({
    email:    { rules:{ required:true, email:true } },
    password: { rules:{ required:true } },
  });
  if (!valid) e.preventDefault();
});
</script>
</body>
</html>
