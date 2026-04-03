<?php
/**
 * Auth wrapper – enforce session and optionally role check.
 * Usage at top of protected page:
 *   $required_role = 'customer'; // or 'vendor', 'admin'
 *   require '../templates/layouts/auth_wrapper.php';
 */
if (!is_logged_in()) {
    $redirect_after = urlencode($_SERVER['REQUEST_URI']);
    header('Location: ' . BASE_URL . '/pages/auth/login.php?next=' . $redirect_after);
    exit;
}

if (isset($required_role) && user_role() !== $required_role) {
    // Redirect to appropriate home based on actual role
    $role_home = [
        'customer' => BASE_URL . '/pages/customer/home.php',
        'vendor'   => BASE_URL . '/pages/vendor/dashboard.php',
        'admin'    => BASE_URL . '/pages/admin/dashboard.php',
    ];
    header('Location: ' . ($role_home[user_role()] ?? BASE_URL . '/pages/auth/login.php'));
    exit;
}
?>
