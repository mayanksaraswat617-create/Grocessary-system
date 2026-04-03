<?php
// ============================================================
// GROCEESARY – Application Constants
// ============================================================

// ---- Roles --------------------------------------------------
define('ROLE_CUSTOMER', 'customer');
define('ROLE_VENDOR',   'vendor');
define('ROLE_ADMIN',    'admin');

// ---- Order Statuses -----------------------------------------
define('ORDER_PLACED',      'placed');
define('ORDER_CONFIRMED',   'confirmed');
define('ORDER_PROCESSING',  'processing');
define('ORDER_SHIPPED',     'shipped');
define('ORDER_DELIVERED',   'delivered');
define('ORDER_CANCELLED',   'cancelled');
define('ORDER_DISPUTED',    'disputed');

define('ORDER_STATUSES', [
    ORDER_PLACED     => ['label' => 'Order Placed',  'color' => '#6c757d'],
    ORDER_CONFIRMED  => ['label' => 'Confirmed',     'color' => '#0d6efd'],
    ORDER_PROCESSING => ['label' => 'Processing',    'color' => '#fd7e14'],
    ORDER_SHIPPED    => ['label' => 'Shipped',       'color' => '#0dcaf0'],
    ORDER_DELIVERED  => ['label' => 'Delivered',     'color' => '#198754'],
    ORDER_CANCELLED  => ['label' => 'Cancelled',     'color' => '#dc3545'],
    ORDER_DISPUTED   => ['label' => 'Disputed',      'color' => '#ffc107'],
]);

// ---- Item Statuses ------------------------------------------
define('ITEM_STATUSES', [
    'pending'   => ['label' => 'Pending',   'color' => '#6c757d'],
    'accepted'  => ['label' => 'Accepted',  'color' => '#0d6efd'],
    'rejected'  => ['label' => 'Rejected',  'color' => '#dc3545'],
    'packed'    => ['label' => 'Packed',    'color' => '#fd7e14'],
    'shipped'   => ['label' => 'Shipped',   'color' => '#0dcaf0'],
    'delivered' => ['label' => 'Delivered', 'color' => '#198754'],
]);

// ---- Payment Methods ----------------------------------------
define('PAYMENT_COD',    'cod');
define('PAYMENT_UPI',    'upi');
define('PAYMENT_CARD',   'card');
define('PAYMENT_WALLET', 'wallet');

define('PAYMENT_LABELS', [
    PAYMENT_COD    => 'Cash on Delivery',
    PAYMENT_UPI    => 'UPI / QR Code',
    PAYMENT_CARD   => 'Credit / Debit Card',
    PAYMENT_WALLET => 'Wallet',
]);

// ---- Payout Statuses ----------------------------------------
define('PAYOUT_PENDING',  'pending');
define('PAYOUT_APPROVED', 'approved');
define('PAYOUT_REJECTED', 'rejected');
define('PAYOUT_PAID',     'paid');

// ---- Vendor Verification Statuses ---------------------------
define('VENDOR_PENDING',   'pending');
define('VENDOR_APPROVED',  'approved');
define('VENDOR_REJECTED',  'rejected');
define('VENDOR_SUSPENDED', 'suspended');

// ---- Default Commission Rate --------------------------------
define('DEFAULT_COMMISSION', 8); // 8% default

// ---- Commission Tiers (default fallback) --------------------
define('COMMISSION_TIERS', [
    ['min' => 0,       'max' => 500,   'rate' => 10],
    ['min' => 500.01,  'max' => 2000,  'rate' => 8],
    ['min' => 2000.01, 'max' => null,  'rate' => 6],
]);

// ---- Pagination ---------------------------------------------
define('ITEMS_PER_PAGE', 12);

// ---- File Upload Limits -------------------------------------
define('MAX_UPLOAD_SIZE',    5 * 1024 * 1024); // 5 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('ALLOWED_DOC_TYPES',   ['image/jpeg', 'image/png', 'application/pdf']);
?>
