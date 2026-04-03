<?php
/* ===== BACKEND ===== */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';

$required_role = ROLE_CUSTOMER;
require_once '../../templates/layouts/auth_wrapper.php';

$db   = Database::getInstance();
$user = current_user();

// Fetch cart items grouped by vendor
$items = $db->prepare(
    "SELECT c.*, p.name AS product_name, p.price, p.discount_price, p.images, p.stock, p.unit,
            v.shop_name, v.id AS vid, v.delivery_time, v.min_order_amount
     FROM cart c
     JOIN products p ON p.id=c.product_id
     JOIN vendors v  ON v.id=c.vendor_id
     WHERE c.user_id=? AND p.is_active=1
     ORDER BY v.shop_name, p.name",
    'i', $user['id']
);

// Group by vendor
$grouped = [];
$subtotal = 0;
foreach ($items as $item) {
    $eff_price = (float)($item['discount_price'] ?: $item['price']);
    $item['eff_price'] = $eff_price;
    $item['line_total'] = $eff_price * $item['quantity'];
    $subtotal += $item['line_total'];
    $grouped[$item['vid']]['vendor']  = ['id'=>$item['vid'],'shop_name'=>$item['shop_name'],'delivery_time'=>$item['delivery_time'],'min_order_amount'=>$item['min_order_amount']];
    $grouped[$item['vid']]['items'][] = $item;
}

$delivery = ($subtotal >= FREE_DELIVERY_ABOVE || $subtotal == 0) ? 0 : DELIVERY_CHARGE;
$tax      = round($subtotal * TAX_RATE / 100, 2);
$total    = $subtotal + $delivery + $tax;

$base = BASE_URL;
$page_title = 'My Cart';
require_once '../../templates/layouts/header.php';
require_once '../../templates/layouts/navbar.php';
?>

<div class="page-content" style="background:var(--color-bg)">
  <div class="container" style="padding-top:var(--space-7);padding-bottom:var(--space-8)">
    <h1 style="font-size:var(--text-3xl);margin-bottom:var(--space-6)">🛒 My Cart
      <span class="badge badge-primary ml-3"><?= count($items) ?> item<?= count($items)!=1?'s':'' ?></span>
    </h1>

    <?php if (!$items): ?>
      <div class="empty-state">
        <div class="empty-icon">🛒</div>
        <h3>Your cart is empty</h3>
        <p>Looks like you haven't added anything yet. Start shopping!</p>
        <a href="<?= $base ?>/pages/customer/browse.php" class="btn btn-primary btn-lg">Browse Products</a>
      </div>
    <?php else: ?>
      <div style="display:grid;grid-template-columns:1fr 360px;gap:var(--space-6);align-items:start">

        <!-- Cart Items -->
        <div>
          <?php foreach ($grouped as $vid => $group): ?>
            <div class="card mb-5">
              <div style="padding:var(--space-4) var(--space-5);background:var(--color-bg);border-bottom:1px solid var(--color-border);display:flex;align-items:center;justify-content:space-between">
                <div class="flex items-center gap-2">
                  <span style="font-size:1.2rem">🏪</span>
                  <span class="fw-bold"><?= htmlspecialchars($group['vendor']['shop_name']) ?></span>
                  <span class="text-xs text-muted">⏱ <?= htmlspecialchars($group['vendor']['delivery_time']) ?></span>
                </div>
              </div>
              <div id="cart-items-body" style="padding:0 var(--space-5)">
                <?php foreach ($group['items'] as $ci): ?>
                  <?php $images = json_decode($ci['images']??'[]',true); $img = !empty($images[0])?$base.'/'.$images[0]:'https://placehold.co/60x60/f0f0f0/999?text=P'; ?>
                  <div class="cart-item" style="display:flex;align-items:center;gap:var(--space-4);padding:var(--space-4) 0;border-bottom:1px solid var(--color-border)">
                    <a href="<?= $base ?>/pages/customer/product_detail.php?id=<?= $ci['product_id'] ?>">
                      <img src="<?= $img ?>" style="width:64px;height:64px;object-fit:cover;border-radius:var(--radius-md);flex-shrink:0" onerror="this.src='https://placehold.co/64x64/f0f0f0/999?text=P'">
                    </a>
                    <div style="flex:1;min-width:0">
                      <a href="<?= $base ?>/pages/customer/product_detail.php?id=<?= $ci['product_id'] ?>" class="fw-semibold text-sm" style="color:var(--color-text)"><?= htmlspecialchars($ci['product_name']) ?></a>
                      <div class="text-xs text-muted"><?= htmlspecialchars($ci['unit']) ?></div>
                      <div class="text-primary fw-bold">₹<?= number_format($ci['eff_price'],2) ?></div>
                    </div>
                    <div class="cart-qty-stepper qty-stepper" data-product-id="<?= $ci['product_id'] ?>">
                      <button class="qty-minus">−</button>
                      <input class="qty-input" type="number" value="<?= $ci['quantity'] ?>" min="1" max="<?= $ci['stock'] ?>">
                      <button class="qty-plus">+</button>
                    </div>
                    <div style="text-align:right;min-width:80px">
                      <div class="fw-bold" id="item-subtotal-<?= $ci['product_id'] ?>">₹<?= number_format($ci['line_total'],2) ?></div>
                    </div>
                    <button class="btn btn-icon btn-sm" data-remove-cart="<?= $ci['product_id'] ?>" title="Remove" style="color:var(--color-danger)">🗑</button>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Order Summary -->
        <div style="position:sticky;top:88px">
          <div class="card p-6">
            <h3 style="font-size:var(--text-lg);margin-bottom:var(--space-5)">Order Summary</h3>

            <?php $line=fn($l,$v,$c='text')=>"<div style='display:flex;justify-content:space-between;margin-bottom:var(--space-3);font-size:var(--text-sm)'><span style='color:var(--color-muted)'>$l</span><span class='fw-semibold $c'>$v</span></div>"; ?>
            <?= $line('Subtotal', '₹' . number_format($subtotal, 2)) ?>
            <?= $line('Tax ('. TAX_RATE .'% GST)', '₹' . number_format($tax, 2)) ?>
            <?= $line('Delivery Charge', $delivery==0?'<span class="text-success">FREE</span>':'₹'.number_format($delivery,2), 'text-success') ?>

            <?php if ($subtotal > 0 && $subtotal < FREE_DELIVERY_ABOVE): ?>
              <div class="alert alert-info" style="font-size:11px;padding:8px 12px;margin-bottom:var(--space-4)">
                Add ₹<?= number_format(FREE_DELIVERY_ABOVE-$subtotal,2) ?> more for free delivery!
              </div>
            <?php endif; ?>

            <div style="border-top:2px solid var(--color-border);padding-top:var(--space-4);display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--space-5)">
              <span class="fw-bold">Total</span>
              <span class="fw-black text-primary" style="font-size:var(--text-2xl)" id="cart-total">₹<?= number_format($total,2) ?></span>
            </div>

            <a href="<?= $base ?>/pages/customer/checkout.php" class="btn btn-primary btn-full btn-lg">Proceed to Checkout →</a>
            <a href="<?= $base ?>/pages/customer/browse.php" class="btn btn-ghost btn-full mt-3">Continue Shopping</a>
          </div>

          <!-- Trust badges -->
          <div class="flex gap-4 mt-4 text-xs text-muted justify-center">
            <span>🔒 Secure</span><span>🚚 Fast Delivery</span><span>↩️ Easy Returns</span>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>@media(max-width:768px){.cart-grid{grid-template-columns:1fr!important}}</style>

<?php require_once '../../templates/layouts/footer.php'; ?>
