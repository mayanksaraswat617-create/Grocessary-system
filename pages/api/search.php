<?php
/* ============================================================
   API: Search Autocomplete
   Returns up to 8 matching products + categories
   ============================================================ */
require_once '../../config/config.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) { echo json_encode(['success'=>true,'results',[]]); exit; }

$db   = Database::getInstance();
$like = '%' . $q . '%';
$products = $db->prepare(
    "SELECT p.id,p.name,COALESCE(p.discount_price,p.price) AS price,p.images,c.name AS category FROM products p JOIN categories c ON c.id=p.category_id JOIN vendors v ON v.id=p.vendor_id WHERE p.is_active=1 AND v.verification_status='approved' AND (p.name LIKE ? OR p.tags LIKE ?) ORDER BY p.views DESC LIMIT 8",
    'ss', $like, $like
);

$base = BASE_URL;
$results = array_map(function($p) use ($base) {
    $images = json_decode($p['images'] ?? '[]', true);
    return [
        'id'    => $p['id'],
        'name'  => $p['name'],
        'price' => '₹' . number_format((float)$p['price'], 2),
        'cat'   => $p['category'],
        'image' => !empty($images[0]) ? $base . '/' . $images[0] : null,
        'url'   => $base . '/pages/customer/product_detail.php?id=' . $p['id'],
    ];
}, $products ?: []);

echo json_encode(['success'=>true,'results'=>$results]);
