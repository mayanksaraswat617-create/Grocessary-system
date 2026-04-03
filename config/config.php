<?php
// ============================================================
// GROCEESARY – Centralized Configuration
// ============================================================

// ---- Environment Detection ----------------------------------
$is_local = (isset($_SERVER['SERVER_NAME']) && in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1', '::1']));
$is_render = isset($_ENV['RENDER']) || isset($_SERVER['RENDER']) || (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'onrender.com') !== false);

// ---- Database Settings --------------------------------------
if ($is_local) {
    define('DB_TYPE',     'mysql');
    define('DB_HOST',     'localhost');
    define('DB_NAME',     'groceesary');
    define('DB_USER',     'root');
    define('DB_PASSWORD', '');
} elseif ($is_render) {
    // Production (Render.com) - Using SQLite for zero-config deployment
    define('DB_TYPE',     'sqlite');
    define('DB_PATH',     __DIR__ . '/../database/groceesary.sqlite');
} else {
    // Other Production (Fallback for InfinityFree etc.)
    define('DB_TYPE',     'mysql');
    define('DB_HOST',     'sql211.infinityfree.com'); 
    define('DB_NAME',     'if0_41571993_dbms_grocessary');
    define('DB_USER',     'if0_41571993');
    define('DB_PASSWORD', 'TODO_YOUR_PASSWORD'); 
}
define('DB_CHARSET',  'utf8mb4');

// ---- Application Settings -----------------------------------
define('APP_NAME',    'Groceesary');
define('APP_VERSION', '1.0.0');
define('APP_TAGLINE', 'Fresh Groceries, Delivered Fast');

// Base URL – auto-detected for XAMPP; override for production
if ($is_local) {
    $folder = basename(dirname(__DIR__));
    define('BASE_URL', 'http://localhost/' . $folder);
} elseif ($is_render) {
    $render_url = isset($_SERVER['RENDER_EXTERNAL_URL']) ? $_SERVER['RENDER_EXTERNAL_URL'] : 'https://' . ($_SERVER['HTTP_HOST'] ?? 'groceesary.onrender.com');
    define('BASE_URL', $render_url);
} else {
    define('BASE_URL', 'https://grocessary.gt.tc'); 
}

define('UPLOAD_PATH',  __DIR__ . '/../assets/uploads/');
define('UPLOAD_URL',   BASE_URL . '/assets/uploads/');

// ---- Debug & Logging ----------------------------------------
define('DEBUG_MODE', $is_local);
define('LOG_PATH',   __DIR__ . '/../logs/');

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOG_PATH . 'error.log');
}

// ---- Session Configuration ----------------------------------
define('SESSION_LIFETIME', 3600 * 24 * 7); // 7 days
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
session_set_cookie_params(SESSION_LIFETIME);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---- Feature Flags ------------------------------------------
define('FEATURE_COD',          true);  // Cash on Delivery
define('FEATURE_UPI',          true);  // UPI (dummy in Phase 1)
define('FEATURE_CARD',         true);  // Card (dummy in Phase 1)
define('FEATURE_WALLET',       false); // Wallet (Phase 2)
define('FEATURE_NOTIFICATIONS', true);
define('ENABLE_NOTIFICATIONS',  true);  // Quick toggle for SMS
define('SMS_API_KEY',          'YOUR_API_KEY_HERE'); // For production
define('SMS_SENDER_ID',        'GRCSRY');            // For production

// ---- Tax & Delivery -----------------------------------------
define('TAX_RATE',             5);    // 5% GST
define('FREE_DELIVERY_ABOVE', 500);   // Free delivery on orders ≥ ₹500
define('DELIVERY_CHARGE',     40);    // Default delivery charge (₹)

// ---- Timezone & Locale --------------------------------------
define('APP_TIMEZONE', 'Asia/Kolkata');
date_default_timezone_set(APP_TIMEZONE);
define('CURRENCY_SYMBOL', '₹');

// ---- Helper: CSRF Token -------------------------------------
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
define('CSRF_TOKEN', $_SESSION['csrf_token']);

// ---- Helper: current user -----------------------------------
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}
function is_logged_in(): bool {
    return isset($_SESSION['user']);
}
function user_role(): ?string {
    return $_SESSION['user']['role'] ?? null;
}
function require_login(?string $redirect = null): void {
    if (!is_logged_in()) {
        $r = $redirect ?? BASE_URL . '/pages/auth/login.php';
        header('Location: ' . $r);
        exit;
    }
}
function require_role(string $role, ?string $redirect = null): void {
    require_login($redirect);
    if (user_role() !== $role) {
        header('Location: ' . BASE_URL . '/pages/auth/login.php?error=unauthorized');
        exit;
    }
}
?>
