<?php
/**
 * Product Card Component (Amazon/Flipkart Mix)
 * Expects: $product (associative array from DB)
 */
$base = BASE_URL;
$pid = (int) $product['id'];
$name = htmlspecialchars($product['name']);
$price = (float) $product['price'];
$discount = !empty($product['discount_price']) ? (float) $product['discount_price'] : null;
$display_price = $discount ?? $price;
$saving = $discount ? round((($price - $discount) / $price) * 100) : 0;
$rating = (float) ($product['avg_rating'] ?? 0);
$reviews = (int) ($product['total_reviews'] ?? 0);
$unit = htmlspecialchars($product['unit'] ?? 'piece');
$stock = (int) ($product['stock'] ?? 0);
$is_out = $stock <= 0;

// Image logic
// If there are generated images in the array, use the first one. Otherwise use a placeholder.
$images = json_decode($product['images'] ?? '[]', true);
$thumb = !empty($images[0]) ? $base . '/' . $images[0] : 'https://placehold.co/280x280/f0f0f0/999?text=Product+Image';

// Specific fallbacks based on slug for the generated images we just created
if ($product['slug'] === 'fresh-tomatoes') $thumb = $base . '/assets/images/products/tomatoes.png';
if ($product['slug'] === 'full-cream-milk') $thumb = $base . '/assets/images/products/milk.png';

$slug_link = $base . '/pages/customer/product_detail.php?id=' . $pid;
?>
<div class="product-card card" id="product-card-<?= $pid ?>" style="border-radius:4px;border:1px solid var(--color-border);overflow:hidden;position:relative">
  
  <a href="<?= $slug_link ?>" class="product-img-wrap" style="position:relative;padding:15px">
    <img src="<?= $thumb ?>" alt="<?= $name ?>" class="card-img product-thumb"
      onerror="this.src='https://placehold.co/280x280/f0f0f0/999?text=Image'" loading="lazy" style="max-height:100%;max-width:100%;object-fit:contain">
    
    <?php if ($saving > 0): ?>
      <span class="deal-badge" style="position:absolute;top:10px;left:10px;margin:0;border-radius:2px"><?= $saving ?>% OFF</span>
    <?php endif; ?>
    
    <?php if (!empty($product['is_featured'])): ?>
      <span class="badge badge-warning" style="position:absolute;top:10px;right:10px;background:var(--color-primary);color:#0f1111;font-weight:700">⭐ Featured</span>
    <?php endif; ?>
    
    <?php if ($is_out): ?>
      <div style="position:absolute;inset:0;background:rgba(255,255,255,0.7);display:flex;align-items:center;justify-content:center">
        <span style="color:#0f1111;font-weight:700;font-size:var(--text-sm);background:#ddd;padding:4px 12px;border-radius:4px">Currently Unavailable</span>
      </div>
    <?php endif; ?>
  </a>

  <div class="card-body" style="padding:15px">
    
    <!-- Title -->
    <a href="<?= $slug_link ?>" style="text-decoration:none">
      <h3 class="card-title" style="font-size:15px;line-height:1.4;margin-bottom:5px;font-weight:500;color:#0f1111;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;text-overflow:ellipsis;min-height:42px">
        <?= $name ?>
      </h3>
    </a>

    <!-- Rating -->
    <div class="flex items-center gap-1 mb-2">
      <div class="stars" style="color:#ffa41c">
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <span class="<?= $i <= round($rating) ? '' : 'star-empty' ?>" style="<?= $i > round($rating) ? 'color:#ddd' : '' ?>">★</span>
        <?php endfor; ?>
      </div>
      <span class="text-xs" style="color:#007185"><?= $reviews ?></span>
    </div>

    <!-- Pricing -->
    <div style="margin-bottom:10px">
      <span style="font-size:24px;font-weight:600;color:#B12704">₹<?= number_format($display_price, 2) ?></span>
      <?php if ($discount): ?>
        <span class="text-xs" style="color:#565959;text-decoration:line-through;margin-left:5px">M.R.P: ₹<?= number_format($price, 2) ?></span>
      <?php endif; ?>
      <div style="font-size:12px;color:#565959;margin-top:2px">(₹<?= number_format($display_price, 2) ?> / <?= $unit ?>)</div>
    </div>

    <!-- Vendor -->
    <?php if (!empty($product['shop_name'])): ?>
      <div style="font-size:12px;color:#565959;margin-bottom:10px">By <span style="color:#007185"><?= htmlspecialchars($product['shop_name']) ?></span></div>
    <?php endif; ?>

    <!-- Action -->
    <?php if (!$is_out): ?>
      <div class="inline-cart-add">
        <button class="inline-add-btn" data-add-cart="<?= $pid ?>" id="add-cart-<?= $pid ?>">Add to Cart</button>
      </div>
    <?php else: ?>
       <div class="inline-cart-add" style="visibility:hidden">
         <button class="inline-add-btn">Add to Cart</button>
       </div>
    <?php endif; ?>

  </div>
</div>