<?php
/* =============================================================
   GROCEESARY – Root Entry Point
   Redirects to the appropriate page based on login state
   ============================================================= */
require_once 'config/config.php';

if (is_logged_in()) {
    $role_home = [
        'customer' => BASE_URL . '/pages/customer/home.php',
        'vendor'   => BASE_URL . '/pages/vendor/dashboard.php',
        'admin'    => BASE_URL . '/pages/admin/dashboard.php',
    ];
    header('Location: ' . ($role_home[user_role()] ?? BASE_URL . '/pages/customer/home.php'));
} else {
    header('Location: ' . BASE_URL . '/pages/customer/home.php');
}
exit;
