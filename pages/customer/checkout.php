<?php
/* ===== BACKEND ===== */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';

$required_role = ROLE_CUSTOMER;
require_once '../../templates/layouts/auth_wrapper.php';

$db   = Database::getInstance();
$user = current_user();
$errors = []; $success = '';

// Fetch cart items
$cart_items = $db->prepare(
    "SELECT c.*, p.name AS product_name, p.images, p.unit, p.stock,
            COALESCE(p.discount_price,p.price) AS eff_price,
            v.id AS vid, v.shop_name, v.commission_rate
     FROM cart c JOIN products p ON p.id=c.product_id JOIN vendors v ON v.id=c.vendor_id
     WHERE c.user_id=? AND p.is_active=1",
    'i', $user['id']
);
if (!$cart_items) { header('Location:'.BASE_URL.'/pages/customer/cart.php?empty=1'); exit; }

// Saved addresses
$addresses = $db->prepare("SELECT * FROM addresses WHERE user_id=? ORDER BY is_default DESC", 'i', $user['id']);

// Calculate totals
$subtotal = array_sum(array_map(fn($i)=>$i['eff_price']*$i['quantity'], $cart_items));
$delivery = $subtotal >= FREE_DELIVERY_ABOVE ? 0 : DELIVERY_CHARGE;
$tax      = round($subtotal * TAX_RATE / 100, 2);
$total    = $subtotal + $delivery + $tax;

// Process order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== CSRF_TOKEN) {
        $errors[] = 'Invalid request.';
    } else {
        $addr_id  = (int)($_POST['address_id'] ?? 0);
        $pay_mth  = $_POST['payment_method'] ?? PAYMENT_COD;
        $slot     = trim($_POST['delivery_slot'] ?? '');
        $notes    = trim($_POST['notes'] ?? '');

        if (!$addr_id && (!trim($_POST['delivery_address']??''))) $errors[] = 'Please select or enter a delivery address.';
        if (!in_array($pay_mth, [PAYMENT_COD,PAYMENT_UPI,PAYMENT_CARD])) $errors[] = 'Invalid payment method.';

        if (empty($errors)) {
            $addr = $addr_id ? $db->prepareOne("SELECT * FROM addresses WHERE id=? AND user_id=?", 'ii', $addr_id, $user['id']) : null;
            $del_address = $addr ? implode(', ',array_filter([$addr['line1'],$addr['line2'],$addr['city'],$addr['state'],$addr['pincode']])) : trim($_POST['delivery_address']??'');
            $del_name  = $addr ? $addr['full_name']  : ($user['name']);
            $del_phone = $addr ? $addr['phone']       : ($user['phone'] ?? '');
            $order_num = 'GRO' . strtoupper(substr(uniqid(), -8));
            $pay_stat  = ($pay_mth === PAYMENT_COD) ? 'pending' : 'paid';

            $db->beginTransaction();
            try {
                $db->execute(
                    "INSERT INTO orders (order_number,user_id,address_id,delivery_name,delivery_phone,delivery_address,subtotal,tax,delivery_charge,total_amount,payment_method,payment_status,order_status,delivery_slot,notes)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                    'siisssddddsssss', $order_num,$user['id'],$addr_id?:null,$del_name,$del_phone,$del_address,
                    $subtotal,$tax,$delivery,$total,$pay_mth,$pay_stat,ORDER_PLACED,$slot,$notes
                );
                $order_id = $db->lastInsertId();

                // Split order items by vendor
                foreach ($cart_items as $ci) {
                    $commission = round($ci['eff_price'] * $ci['quantity'] * $ci['commission_rate'] / 100, 2);
                    $earning    = $ci['eff_price'] * $ci['quantity'] - $commission;
                    $images = json_decode($ci['images']??'[]',true);
                    $db->execute(
                        "INSERT INTO order_items (order_id,vendor_id,product_id,product_name,product_image,quantity,unit_price,subtotal,commission,vendor_earning,item_status)
                         VALUES (?,?,?,?,?,?,?,?,?,?,?)",
                        'iiissidddds', $order_id,$ci['vid'],$ci['product_id'],$ci['product_name'],$images[0]??null,
                        $ci['quantity'],$ci['eff_price'],$ci['eff_price']*$ci['quantity'],$commission,$earning,ORDER_PLACED
                    );
                    // Reduce stock
                    $db->execute("UPDATE products SET stock=stock-? WHERE id=? AND stock>=?", 'iii', $ci['quantity'],$ci['product_id'],$ci['quantity']);
                }
                // Clear cart
                $db->execute("DELETE FROM cart WHERE user_id=?", 'i', $user['id']);
                // Notification
                $db->execute("INSERT INTO notifications(user_id,title,message,type,link) VALUES(?,?,?,?,?)", 'issss',
                    $user['id'],"Order Placed ✅","Your order $order_num has been placed successfully.",
                    'order', BASE_URL.'/pages/customer/order_detail.php?id='.$order_id);

                $db->commit();
                header('Location:'.BASE_URL.'/pages/customer/order_detail.php?id='.$order_id.'&placed=1');
                exit;
            } catch(Exception $e) {
                $db->rollback();
                $errors[] = 'Order failed. Please try again.';
            }
        }
    }
}

$base = BASE_URL;
$page_title = 'Checkout';
require_once '../../templates/layouts/header.php';
require_once '../../templates/layouts/navbar.php';
?>

<div class="page-content" style="background:var(--color-bg)">
  <div class="container" style="padding-top:var(--space-7);padding-bottom:var(--space-8)">
    <h1 style="font-size:var(--text-3xl);margin-bottom:var(--space-6)">🧾 Checkout</h1>

    <?php if ($errors): ?>
      <div class="alert alert-danger mb-5"><?php foreach($errors as $e): ?><div>⚠️ <?= htmlspecialchars($e) ?></div><?php endforeach; ?></div>
    <?php endif; ?>

    <form method="POST" id="checkout-form">
      <input type="hidden" name="csrf_token" value="<?= CSRF_TOKEN ?>">
      <div style="display:grid;grid-template-columns:1fr 380px;gap:var(--space-6);align-items:start">

        <!-- Left: Delivery & Payment -->
        <div>
          <!-- Delivery Address -->
          <div class="card p-6 mb-5">
            <h3 style="margin-bottom:var(--space-5)">📍 Delivery Address</h3>
            <?php if ($addresses): ?>
              <div style="display:flex;flex-direction:column;gap:var(--space-3);margin-bottom:var(--space-5)">
                <?php foreach ($addresses as $addr): ?>
                  <label style="display:flex;gap:var(--space-3);padding:var(--space-4);border:2px solid var(--color-border);border-radius:var(--radius-xl);cursor:pointer;transition:border-color 0.2s" class="addr-card">
                    <input type="radio" name="address_id" value="<?= $addr['id'] ?>" <?= $addr['is_default']?'checked':'' ?> style="margin-top:3px;accent-color:var(--color-primary)">
                    <div>
                      <div class="fw-bold text-sm"><?= htmlspecialchars($addr['full_name']) ?> <span class="badge badge-muted ml-1"><?= htmlspecialchars($addr['label']) ?></span></div>
                      <div class="text-xs text-muted"><?= htmlspecialchars($addr['line1']) ?><?= $addr['line2']?', '.htmlspecialchars($addr['line2']):'' ?></div>
                      <div class="text-xs text-muted"><?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['state']) ?> – <?= htmlspecialchars($addr['pincode']) ?></div>
                      <div class="text-xs text-muted">📱 <?= htmlspecialchars($addr['phone']) ?></div>
                    </div>
                  </label>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="form-group">
                <label class="form-label">Delivery Address</label>
                <textarea class="form-control" name="delivery_address" rows="3" placeholder="Enter your full delivery address…" required><?= htmlspecialchars($_POST['delivery_address']??'') ?></textarea>
              </div>
            <?php endif; ?>
          </div>

          <!-- Delivery Slot -->
          <div class="card p-6 mb-5">
            <h3 style="margin-bottom:var(--space-5)">⏰ Delivery Slot</h3>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:var(--space-3)">
              <?php foreach(['ASAP (30-60 min)','Today 2–4 PM','Today 6–8 PM','Tomorrow 9–11 AM','Tomorrow 2–4 PM','Tomorrow 6–8 PM'] as $slot): ?>
                <label style="border:2px solid var(--color-border);border-radius:var(--radius-xl);padding:var(--space-3);text-align:center;cursor:pointer;font-size:var(--text-xs);font-weight:600;transition:all 0.2s">
                  <input type="radio" name="delivery_slot" value="<?= $slot ?>" style="display:none" <?= $slot==='ASAP (30-60 min)'?'checked':'' ?>>
                  <?= $slot ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Payment Method -->
          <div class="card p-6 mb-5">
            <h3 style="margin-bottom:var(--space-5)">💳 Payment Method</h3>
            <div style="display:flex;flex-direction:column;gap:var(--space-3)">
              <?php if(FEATURE_COD): ?><label style="display:flex;align-items:center;gap:var(--space-3);padding:var(--space-4);border:2px solid var(--color-border);border-radius:var(--radius-xl);cursor:pointer"><input type="radio" name="payment_method" value="cod" checked style="accent-color:var(--color-primary)"><span style="font-size:1.3rem">💵</span><div><div class="fw-semibold text-sm">Cash on Delivery</div><div class="text-xs text-muted">Pay when your order arrives</div></div></label><?php endif; ?>
              <?php if(FEATURE_UPI):  ?><label style="display:flex;align-items:center;gap:var(--space-3);padding:var(--space-4);border:2px solid var(--color-border);border-radius:var(--radius-xl);cursor:pointer"><input type="radio" name="payment_method" value="upi" style="accent-color:var(--color-primary)"><span style="font-size:1.3rem">📱</span><div><div class="fw-semibold text-sm">UPI</div><div class="text-xs text-muted">Google Pay, PhonePe, BHIM</div></div></label><?php endif; ?>
              <?php if(FEATURE_CARD): ?><label style="display:flex;align-items:center;gap:var(--space-3);padding:var(--space-4);border:2px solid var(--color-border);border-radius:var(--radius-xl);cursor:pointer"><input type="radio" name="payment_method" value="card" style="accent-color:var(--color-primary)"><span style="font-size:1.3rem">💳</span><div><div class="fw-semibold text-sm">Credit / Debit Card</div><div class="text-xs text-muted">Visa, Mastercard, RuPay</div></div></label><?php endif; ?>
            </div>
          </div>

          <!-- Notes -->
          <div class="card p-5">
            <label class="form-label">📝 Order Notes (optional)</label>
            <textarea class="form-control" name="notes" rows="2" placeholder="Special instructions for delivery…"></textarea>
          </div>
        </div>

        <!-- Right: Summary -->
        <div style="position:sticky;top:88px">
          <div class="card p-6">
            <h3 style="margin-bottom:var(--space-5)">Order Summary</h3>
            <?php foreach ($cart_items as $ci): ?>
              <div class="flex justify-between mb-3 text-sm">
                <span class="text-muted"><?= htmlspecialchars($ci['product_name']) ?> × <?= $ci['quantity'] ?></span>
                <span class="fw-semibold">₹<?= number_format($ci['eff_price']*$ci['quantity'],2) ?></span>
              </div>
            <?php endforeach; ?>
            <hr class="divider">
            <div class="flex justify-between mb-2 text-sm"><span class="text-muted">Subtotal</span><span class="fw-semibold">₹<?= number_format($subtotal,2) ?></span></div>
            <div class="flex justify-between mb-2 text-sm"><span class="text-muted">GST (<?= TAX_RATE ?>%)</span><span class="fw-semibold">₹<?= number_format($tax,2) ?></span></div>
            <div class="flex justify-between mb-4 text-sm"><span class="text-muted">Delivery</span><span class="fw-semibold text-success"><?= $delivery==0?'FREE':'₹'.number_format($delivery,2) ?></span></div>
            <div style="border-top:2px solid var(--color-border);padding-top:var(--space-4);display:flex;justify-content:space-between;margin-bottom:var(--space-6)">
              <span class="fw-bold">Total</span>
              <span class="fw-black text-primary" style="font-size:var(--text-2xl)">₹<?= number_format($total,2) ?></span>
            </div>
            <button type="submit" class="btn btn-primary btn-full btn-lg">Place Order 🎉</button>
          </div>
        </div>

      </div>
    </form>
  </div>
</div>

<style>
  @media(max-width:768px){div[style*="grid-template-columns:1fr 380px"]{grid-template-columns:1fr!important}}
  .addr-card:has(input:checked){border-color:var(--color-primary)!important;background:rgba(255,107,53,0.04)}
  label:has(input[name=delivery_slot]:checked){border-color:var(--color-primary)!important;background:rgba(255,107,53,0.08)}
  label:has(input[name=payment_method]:checked){border-color:var(--color-primary)!important;background:rgba(255,107,53,0.04)}
</style>

<?php require_once '../../templates/layouts/footer.php'; ?>
