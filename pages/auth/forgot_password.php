<?php
/* ===== BACKEND ===== */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== CSRF_TOKEN) {
        $error = 'Invalid request.';
    } else {
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            $db   = Database::getInstance();
            $user = $db->prepareOne("SELECT id, name FROM users WHERE email = ? LIMIT 1", 's', $email);
            if (!$user) {
                // Don't reveal if email exists or not
                $success = 'If that email is registered, you\'ll receive a reset link shortly.';
            } else {
                $token   = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $db->execute("UPDATE users SET reset_token=?, reset_expires=? WHERE id=?", 'ssi', $token, $expires, $user['id']);
                
                // In production, send email. For dev, show the link:
                $reset_link = BASE_URL . '/pages/auth/reset_password.php?token=' . $token;
                if (DEBUG_MODE) {
                    $success = 'Reset link (Dev): <a href="' . $reset_link . '" class="text-primary fw-bold">' . $reset_link . '</a>';
                } else {
                    $success = 'If that email is registered, you\'ll receive a reset link shortly.';
                }
            }
        }
    }
}

/* ===== FRONTEND ===== */
$page_title  = 'Forgot Password';
$hide_navbar = true;
$body_class  = 'auth-layout flex-center min-h-screen';
require_once '../../templates/layouts/header.php';
?>

<div class="container-sm">
  <div class="auth-card card p-8 mx-auto" style="max-width: 450px;">
    
    <!-- Header -->
    <div class="text-center mb-8">
      <div style="font-size: 3rem; margin-bottom: var(--space-4);">🔑</div>
      <h2 class="text-2xl fw-bold">Forgot Password?</h2>
      <p class="text-muted text-sm px-4">Enter your registered email address and we'll send you a link to reset your password.</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger mb-6 p-4 rounded-md flex items-start gap-3" style="background:#fff1f1;border:1px solid #ffcfcf;color:#c40000;font-size:13px">
        <span>⚠️</span>
        <div><?= htmlspecialchars($error) ?></div>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success mb-6 p-4 rounded-md" style="background:#f0fff4;border:1px solid #c6f6d5;color:#22543d;font-size:13px">
        <div class="flex items-start gap-3">
          <span>✅</span>
          <div><?= $success ?></div>
        </div>
      </div>
    <?php else: ?>
      <form method="POST" id="forgot-form" novalidate class="flex-col gap-4">
        <input type="hidden" name="csrf_token" value="<?= CSRF_TOKEN ?>">

        <div class="form-group">
          <label class="form-label fw-semibold block text-sm mb-1" for="email">Email Address</label>
          <input type="email" class="form-control w-full p-3 rounded" id="email" name="email"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required placeholder="name@example.com">
        </div>

        <button type="submit" class="btn btn-primary btn-full py-3 fw-bold mt-2 rounded-lg shadow">
          Send Reset Link
        </button>
      </form>
    <?php endif; ?>

    <div class="text-center mt-8 pt-6 border-t border-gray-100">
      <a href="<?= BASE_URL ?>/pages/auth/login.php" class="text-sm text-primary fw-semibold hover-underline">
        ← Back to Login
      </a>
    </div>

  </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/utils.js"></script>
</body>
</html>
