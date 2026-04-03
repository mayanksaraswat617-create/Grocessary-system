<?php
/* ===== BACKEND ===== */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';

$pid = (int)($_GET['id'] ?? 0);
if (!$pid) { header('Location: ' . BASE_URL . '/pages/customer/browse.php'); exit; }

$db      = Database::getInstance();
$product = $db->prepareOne(
    "SELECT p.*, v.shop_name, v.id AS vid, v.avg_rating AS vendor_rating, v.delivery_time, v.min_order_amount,
            c.name AS category_name, u.name AS vendor_user_name
     FROM products p
     JOIN vendors v ON v.id=p.vendor_id
     JOIN categories c ON c.id=p.category_id
     JOIN users u ON u.id=v.user_id
     WHERE p.id=? AND p.is_active=1 AND v.verification_status='approved' LIMIT 1",
    'i', $pid
);

if (!$product) { header('Location: ' . BASE_URL . '/pages/customer/browse.php?error=not_found'); exit; }

// Increment views
$db->execute("UPDATE products SET views=views+1 WHERE id=?", 'i', $pid);

// Reviews
$reviews = $db->prepare(
    "SELECT r.*, u.name AS reviewer_name FROM reviews r JOIN users u ON u.id=r.user_id
     WHERE r.product_id=? AND r.is_approved=1 ORDER BY r.created_at DESC LIMIT 20",
    'i', $pid
);

// Related products
$related = $db->prepare(
    "SELECT p.*, v.shop_name FROM products p JOIN vendors v ON v.id=p.vendor_id
     WHERE p.category_id=? AND p.id!=? AND p.is_active=1 AND v.verification_status='approved'
     ORDER BY p.avg_rating DESC LIMIT 4",
    'ii', $product['category_id'], $pid
);

$images  = json_decode($product['images'] ?? '[]', true);
$price   = (float)$product['price'];
$discount= !empty($product['discount_price']) ? (float)$product['discount_price'] : null;
$saving  = $discount ? round((($price-$discount)/$price)*100) : 0;

$base = BASE_URL;
$page_title = $product['name'];
require_once '../../templates/layouts/header.php';
require_once '../../templates/layouts/navbar.php';
?>

<div class="page-content" style="background:var(--color-bg)">
  <div class="container" style="padding-top:var(--space-7);padding-bottom:var(--space-8)">

    <!-- Breadcrumb -->
    <nav style="font-size:var(--text-xs);color:var(--color-muted);margin-bottom:var(--space-5)">
      <a href="<?= $base ?>/pages/customer/home.php">Home</a> /
      <a href="<?= $base ?>/pages/customer/browse.php?category=<?= $product['category_id'] ?>"><?= htmlspecialchars($product['category_name']) ?></a> /
      <span><?= htmlspecialchars($product['name']) ?></span>
    </nav>

    <!-- Product Detail Grid -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-8);align-items:start" class="product-detail-grid">

      <!-- Images -->
      <div>
        <div style="border-radius:var(--radius-2xl);overflow:hidden;background:#fff;border:1px solid var(--color-border);margin-bottom:var(--space-3)">
          <?php $main_img = !empty($images[0]) ? $base.'/'.$images[0] : 'https://placehold.co/500x400/f0f0f0/999?text=No+Image'; ?>
          <img id="main-product-img" src="<?= $main_img ?>" alt="<?= htmlspecialchars($product['name']) ?>"
               style="width:100%;aspect-ratio:5/4;object-fit:cover"
               onerror="this.src='https://placehold.co/500x400/f0f0f0/999?text=No+Image'">
        </div>
        <?php if (count($images) > 1): ?>
          <div style="display:flex;gap:var(--space-2);flex-wrap:wrap">
            <?php foreach ($images as $idx => $img): ?>
              <img src="<?= $base.'/'.$img ?>" alt="Thumb <?= $idx+1 ?>"
                   style="width:72px;height:72px;object-fit:cover;border-radius:var(--radius-md);border:2px solid <?= $idx===0?'var(--color-primary)':'var(--color-border)' ?>;cursor:pointer"
                   onclick="document.getElementById('main-product-img').src=this.src;document.querySelectorAll('.thumb-img').forEach(t=>t.style.borderColor='var(--color-border)');this.style.borderColor='var(--color-primary)'"
                   class="thumb-img">
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Info -->
      <div>
        <div class="badge badge-muted mb-3"><?= htmlspecialchars($product['category_name']) ?></div>
        <h1 style="font-size:var(--text-3xl);margin-bottom:var(--space-3)"><?= htmlspecialchars($product['name']) ?></h1>

        <!-- Stars -->
        <div class="flex items-center gap-3 mb-4">
          <div class="stars">
            <?php $r = round((float)$product['avg_rating']); for($i=1;$i<=5;$i++): ?>
              <span class="<?= $i<=$r?'':'star-empty' ?>">★</span>
            <?php endfor; ?>
          </div>
          <span class="text-sm text-muted"><?= number_format((float)$product['avg_rating'],1) ?> (<?= $product['total_reviews'] ?> reviews)</span>
        </div>

        <!-- Price -->
        <div class="flex items-center gap-4 mb-5">
          <span style="font-size:var(--text-4xl);font-weight:800;color:var(--color-primary);font-family:var(--font-heading)">
            ₹<?= number_format($discount ?? $price, 2) ?>
          </span>
          <?php if ($discount): ?>
            <span style="font-size:var(--text-xl);text-decoration:line-through;color:var(--color-muted)">₹<?= number_format($price,2) ?></span>
            <span class="badge badge-danger">-<?= $saving ?>% OFF</span>
          <?php endif; ?>
        </div>

        <!-- Unit / Stock -->
        <div class="flex gap-4 mb-5 text-sm">
          <span><strong>Unit:</strong> <?= htmlspecialchars($product['unit']) ?></span>
          <span class="<?= $product['stock']>0?'text-success fw-semibold':'text-danger fw-semibold' ?>">
            <?= $product['stock']>0 ? '✅ In Stock ('.$product['stock'].' available)' : '❌ Out of Stock' ?>
          </span>
        </div>

        <?php if ($product['description']): ?>
          <p class="mb-5" style="line-height:1.7"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        <?php endif; ?>

        <!-- Vendor Info -->
        <a href="<?= $base ?>/pages/customer/browse.php?vendor=<?= $product['vid'] ?>" style="display:flex;align-items:center;gap:var(--space-3);padding:var(--space-4);background:var(--color-bg);border-radius:var(--radius-xl);border:1px solid var(--color-border);margin-bottom:var(--space-5);text-decoration:none">
          <div style="width:44px;height:44px;background:var(--gradient-secondary);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.3rem;flex-shrink:0">🏪</div>
          <div>
            <div class="fw-bold text-sm"><?= htmlspecialchars($product['shop_name']) ?></div>
            <div class="text-xs text-muted">⏱ <?= htmlspecialchars($product['delivery_time']) ?> • ⭐ <?= number_format((float)$product['vendor_rating'],1) ?></div>
          </div>
          <span class="ml-auto text-xs text-primary">View Store →</span>
        </a>

        <!-- Add to Cart -->
        <?php if ($product['stock'] > 0): ?>
          <div class="flex gap-4 items-center mb-4">
            <div class="qty-stepper" id="qty-stepper">
              <button type="button" onclick="changeQty(-1)">−</button>
              <input type="number" id="product-qty" value="1" min="1" max="<?= $product['stock'] ?>">
              <button type="button" onclick="changeQty(1)">+</button>
            </div>
            <button class="btn btn-primary btn-lg flex-1" onclick="addProductToCart()" id="add-cart-main">
              🛒 Add to Cart
            </button>
          </div>
          <a href="<?= $base ?>/pages/customer/cart.php" class="btn btn-outline-primary btn-full">View Cart</a>
        <?php else: ?>
          <button class="btn btn-ghost btn-full" disabled>Out of Stock</button>
        <?php endif; ?>

        <!-- Delivery info -->
        <div class="flex gap-4 mt-5 text-xs text-muted">
          <span>🚚 Free delivery on orders ₹<?= FREE_DELIVERY_ABOVE ?>+</span>
          <span>↩️ Easy returns</span>
        </div>
      </div>
    </div>

    <!-- ===== REVIEWS ===== -->
    <div style="margin-top:var(--space-9)">
      <h2 style="font-size:var(--text-2xl);margin-bottom:var(--space-5)">⭐ Customer Reviews</h2>
      <div style="background:#fff;border-radius:var(--radius-2xl);border:1px solid var(--color-border);overflow:hidden">
        <?php if ($reviews): ?>
          <?php foreach ($reviews as $review): ?>
            <?php include '../../templates/components/review_card.php'; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state" style="padding:var(--space-7)">
            <div class="empty-icon">💬</div>
            <h3>No reviews yet</h3>
            <p>Be the first to review this product!</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ===== RELATED ===== -->
    <?php if ($related): ?>
      <div style="margin-top:var(--space-8)">
        <h2 style="font-size:var(--text-2xl);margin-bottom:var(--space-5)">🔗 Related Products</h2>
        <div class="products-grid">
          <?php foreach ($related as $product): ?>
            <?php include '../../templates/components/product_card.php'; ?>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
  @media(max-width:768px){ .product-detail-grid{grid-template-columns:1fr!important} }
</style>

<?php require_once '../../templates/layouts/footer.php'; ?>

<script>
const productId = <?= $pid ?>;
function changeQty(delta) {
  const input = document.getElementById('product-qty');
  const max   = parseInt(input.max);
  input.value = Math.max(1, Math.min(max, parseInt(input.value) + delta));
}
async function addProductToCart() {
  const qty = parseInt(document.getElementById('product-qty').value);
  const btn = document.getElementById('add-cart-main');
  btn.disabled  = true; btn.textContent = 'Adding…';
  try {
    const res = await CartAPI.add(productId, qty);
    if (res.success) {
      updateCartBadge(res.cart_count);
      showToast('Added to cart!', 'success', 'Cart Updated');
      btn.textContent = '✓ Added to Cart';
      setTimeout(() => { btn.disabled = false; btn.textContent = '🛒 Add to Cart'; }, 2000);
    }
  } catch(e) { btn.disabled = false; btn.textContent = '🛒 Add to Cart'; }
}
</script>
