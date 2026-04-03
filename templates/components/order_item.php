<?php
/**
 * Order Item Component – single line item in cart / order detail
 * Expects: $item (order_items row with product info)
 */
$base    = BASE_URL;
$name    = htmlspecialchars($item['product_name']);
$qty     = (int)$item['quantity'];
$price   = (float)$item['unit_price'];
$sub     = (float)$item['subtotal'];
$img     = !empty($item['product_image']) ? $base . '/' . $item['product_image'] : 'https://placehold.co/60x60/f0f0f0/999?text=P';
$status  = $item['item_status'] ?? 'pending';
$statuses= ITEM_STATUSES ?? [];
$sc      = $statuses[$status]['color'] ?? '#6c757d';
$sl      = $statuses[$status]['label'] ?? ucfirst($status);
?>
<div class="order-item" style="display:flex;align-items:center;gap:var(--space-4);padding:var(--space-4) 0;border-bottom:1px solid var(--color-border)">
  <!-- Image -->
  <img src="<?= $img ?>" alt="<?= $name ?>"
       style="width:60px;height:60px;object-fit:cover;border-radius:var(--radius-md);flex-shrink:0;border:1px solid var(--color-border)"
       onerror="this.src='https://placehold.co/60x60/f0f0f0/999?text=P'">

  <!-- Details -->
  <div style="flex:1;min-width:0">
    <div class="fw-semibold" style="font-size:var(--text-sm)"><?= $name ?></div>
    <div class="text-xs text-muted">Qty: <?= $qty ?> × ₹<?= number_format($price, 2) ?></div>
    <?php if (!empty($item['shop_name'])): ?>
      <div class="text-xs text-muted">Vendor: <?= htmlspecialchars($item['shop_name']) ?></div>
    <?php endif; ?>
  </div>

  <!-- Subtotal + Status -->
  <div style="text-align:right;flex-shrink:0">
    <div class="fw-bold text-primary">₹<?= number_format($sub, 2) ?></div>
    <span class="badge" style="background:<?= $sc ?>15;color:<?= $sc ?>;margin-top:4px"><?= $sl ?></span>
  </div>
</div>
