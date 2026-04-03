<?php
/* ============================================================
   API: Cart
   Actions: get, add, update, remove
   ============================================================ */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!is_logged_in() || user_role() !== ROLE_CUSTOMER) {
    echo json_encode(['success'=>false,'message'=>'Please login to use cart.']); exit;
}

$data   = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = $data['action'] ?? '';
$db     = Database::getInstance();
$uid    = (int)(current_user()['id']);

function cart_count($db, $uid) {
    return (int)($db->prepareOne("SELECT COALESCE(SUM(quantity),0) AS c FROM cart WHERE user_id=?",'i',$uid)['c'] ?? 0);
}
function cart_total($db, $uid) {
    return (float)($db->prepareOne(
        "SELECT COALESCE(SUM(c.quantity*COALESCE(p.discount_price,p.price)),0) AS t FROM cart c JOIN products p ON p.id=c.product_id WHERE c.user_id=?",'i',$uid
    )['t'] ?? 0);
}

if ($action === 'get') {
    $items = $db->prepare(
        "SELECT c.*,p.name,COALESCE(p.discount_price,p.price) AS eff_price,p.images FROM cart c JOIN products p ON p.id=c.product_id WHERE c.user_id=?",'i',$uid);
    echo json_encode(['success'=>true,'items'=>$items,'cart_count'=>cart_count($db,$uid),'total'=>cart_total($db,$uid)]);
    exit;
}

if ($action === 'add') {
    $pid = (int)($data['product_id'] ?? 0);
    $qty = max(1,(int)($data['quantity'] ?? 1));
    if (!$pid) { echo json_encode(['success'=>false,'message'=>'Invalid product.']); exit; }
    $product = $db->prepareOne("SELECT p.*,v.id AS vid FROM products p JOIN vendors v ON v.id=p.vendor_id WHERE p.id=? AND p.is_active=1 AND p.stock>0 LIMIT 1",'i',$pid);
    if (!$product) { echo json_encode(['success'=>false,'message'=>'Product unavailable.']); exit; }
    $existing = $db->prepareOne("SELECT id,quantity FROM cart WHERE user_id=? AND product_id=? LIMIT 1",'ii',$uid,$pid);
    if ($existing) {
        $new_qty = min($product['stock'], $existing['quantity'] + $qty);
        $db->execute("UPDATE cart SET quantity=? WHERE id=?",'ii',$new_qty,$existing['id']);
    } else {
        $db->execute("INSERT INTO cart(user_id,product_id,quantity,vendor_id) VALUES(?,?,?,?)",'iiii',$uid,$pid,$qty,$product['vid']);
    }
    echo json_encode(['success'=>true,'message'=>'Added to cart','cart_count'=>cart_count($db,$uid),'total'=>cart_total($db,$uid)]);
    exit;
}

if ($action === 'update') {
    $pid = (int)($data['product_id'] ?? 0);
    $qty = max(0,(int)($data['quantity'] ?? 0));
    if ($qty === 0) {
        $db->execute("DELETE FROM cart WHERE user_id=? AND product_id=?",'ii',$uid,$pid);
    } else {
        $product = $db->prepareOne("SELECT stock FROM products WHERE id=? LIMIT 1",'i',$pid);
        $new_qty = min($product['stock']??$qty,$qty);
        $db->execute("UPDATE cart SET quantity=? WHERE user_id=? AND product_id=?",'iii',$new_qty,$uid,$pid);
    }
    $sub = (float)($db->prepareOne("SELECT COALESCE(SUM(c.quantity*COALESCE(p.discount_price,p.price)),0) AS t FROM cart c JOIN products p ON p.id=c.product_id WHERE c.user_id=? AND c.product_id=?",'ii',$uid,$pid)['t']??0);
    echo json_encode(['success'=>true,'cart_count'=>cart_count($db,$uid),'item_subtotal'=>$sub,'total'=>cart_total($db,$uid)]);
    exit;
}

if ($action === 'remove') {
    $pid = (int)($data['product_id'] ?? 0);
    $db->execute("DELETE FROM cart WHERE user_id=? AND product_id=?",'ii',$uid,$pid);
    echo json_encode(['success'=>true,'cart_count'=>cart_count($db,$uid),'total'=>cart_total($db,$uid)]);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Unknown action.']);
