<?php
/* ===== BACKEND ===== */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';

$db = Database::getInstance();

// Search fallback
if (isset($_GET['q'])) {
  header("Location: browse.php?q=" . urlencode($_GET['q']));
  exit;
}

$just_registered = isset($_GET['registered']);

// All Categories
$categories = $db->query("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order ASC LIMIT 10");
// Banners (Now we have 3 generated banners)
$banners = $db->query("SELECT * FROM banners WHERE is_active=1 ORDER BY sort_order ASC LIMIT 5");

// Deal of the Day (random featured items)
$deals = $db->query(
  "SELECT p.*, v.shop_name, c.name AS category_name
     FROM products p
     JOIN vendors v ON v.id = p.vendor_id
     JOIN categories c ON c.id = p.category_id
     WHERE p.is_active=1 AND p.is_featured=1 AND p.discount_price IS NOT NULL AND v.verification_status='approved'
     ORDER BY RAND() LIMIT 8"
);

// Trending categories or sections
$new_arrivals = $db->query(
  "SELECT p.*, v.shop_name FROM products p JOIN vendors v ON v.id = p.vendor_id
     WHERE p.is_active=1 AND v.verification_status='approved' ORDER BY p.created_at DESC LIMIT 8"
);

$staples = $db->query(
  "SELECT p.*, v.shop_name FROM products p JOIN vendors v ON v.id = p.vendor_id
     WHERE p.is_active=1 AND p.category_id=3 ORDER BY p.views DESC LIMIT 8"
);

$category_images = [
  'https://placehold.co/100x100/f3a847/fff?text=Fruits',
  'https://placehold.co/100x100/f3a847/fff?text=Dairy',
  'https://placehold.co/100x100/f3a847/fff?text=Staples',
  'https://placehold.co/100x100/f3a847/fff?text=Snacks',
  'https://placehold.co/100x100/f3a847/fff?text=Meat',
  'https://placehold.co/100x100/f3a847/fff?text=Bakery',
  'https://placehold.co/100x100/f3a847/fff?text=Frozen',
  'https://placehold.co/100x100/f3a847/fff?text=Care',
  'https://placehold.co/100x100/f3a847/fff?text=Home',
  'https://placehold.co/100x100/f3a847/fff?text=Baby'
];

/* ===== FRONTEND ===== */
$page_title = 'Online Grocery Shopping and Local Store Delivery';
$page_description = 'Order fresh groceries from local vendors near you.';
$base = BASE_URL;

require_once '../../templates/layouts/header.php';
require_once '../../templates/layouts/navbar.php';
?>

<style>
  /* Local CSS for Home Slider */
  .hero-slider-wrap {
    width: 100%;
    position: relative;
    overflow: hidden;
    height: 480px;
    background: #eaeded;
  }

  .hero-slider {
    display: flex;
    width: 300%;
    height: 100%;
    transition: transform 0.5s ease-in-out;
  }

  .hero-slide {
    width: 100%;
    height: 100%;
    flex-shrink: 0;
    position: relative;
  }

  .hero-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center top;
  }

  .hero-slide::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 150px;
    background: linear-gradient(to top, var(--color-bg), transparent);
  }

  .slider-btn {
    position: absolute;
    top: 150px;
    background: rgba(255, 255, 255, 0.7);
    border: none;
    width: 60px;
    height: 100px;
    font-size: 40px;
    color: #333;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
  }

  .slider-btn:hover {
    background: rgba(255, 255, 255, 0.9);
  }

  .slider-prev {
    left: 20px;
  }

  .slider-next {
    right: 20px;
  }

  /* Grid adjustments to lay on top of gradient */
  .home-content {
    position: relative;
    z-index: 10;
    margin-top: -150px;
    padding: 0 20px;
    max-width: 1500px;
    margin-inline: auto;
  }
</style>

<!-- ============================================================
     HERO SLIDER
     ============================================================ -->
<section class="hero-slider-wrap">
  <div class="hero-slider" id="heroSlider">
    <?php foreach ($banners as $b): ?>
      <div class="hero-slide">
        <!-- Default fallback image logic but the script updated them to webp/png -->
        <img src="<?= $base . '/' . $b['image'] ?>" alt="<?= htmlspecialchars($b['title']) ?>"
          onerror="this.src='https://placehold.co/1500x500/232f3e/fff?text=Promo+Banner'">
      </div>
    <?php endforeach; ?>
  </div>
  <button class="slider-btn slider-prev" onclick="moveSlide(-1)">❮</button>
  <button class="slider-btn slider-next" onclick="moveSlide(1)">❯</button>
</section>

<script>
  let currentSlide = 0;
  const slider = document.getElementById('heroSlider');
  const totalSlides = <?= count($banners) ?> || 1;
  function moveSlide(dir) {
    currentSlide = (currentSlide + dir + totalSlides) % totalSlides;
    slider.style.transform = `translateX(-${currentSlide * (100 / totalSlides)}%)`;
  }
  // Auto play
  setInterval(() => moveSlide(1), 5000);
</script>

<div class="home-content">

  <!-- ============================================================
       TOP WIDGETS
       ============================================================ -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:20px;margin-bottom:20px">
    <div class="card" style="padding:20px;background:#fff">
      <h3 style="font-size:21px;margin-bottom:15px;color:#0f1111">Up to 40% off | Daily essentials</h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
        <?php foreach (array_slice($categories, 0, 4) as $idx => $cat): ?>
          <a href="<?= $base ?>/pages/customer/browse.php?category=<?= $cat['id'] ?>" style="text-align:center">
            <img src="<?= $category_images[$idx] ?>" style="width:100%;border-radius:4px;margin-bottom:5px">
            <div style="font-size:12px;color:#0f1111"><?= htmlspecialchars($cat['name']) ?></div>
          </a>
        <?php endforeach; ?>
      </div>
      <a href="<?= $base ?>/pages/customer/browse.php"
        style="display:block;margin-top:15px;color:#007185;font-size:13px">See all offers</a>
    </div>

    <div class="card" style="padding:20px;background:#fff">
      <h3 style="font-size:21px;margin-bottom:15px;color:#0f1111">Starting ₹99 | Fresh Fruits & Veg</h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">
        <?php foreach (array_slice($categories, 4, 4) as $idx => $cat): ?>
          <a href="<?= $base ?>/pages/customer/browse.php?category=<?= $cat['id'] ?>" style="text-align:center">
            <img src="<?= $category_images[$idx + 4] ?>" style="width:100%;border-radius:4px;margin-bottom:5px">
            <div style="font-size:12px;color:#0f1111"><?= htmlspecialchars($cat['name']) ?></div>
          </a>
        <?php endforeach; ?>
      </div>
      <a href="<?= $base ?>/pages/customer/browse.php?category=1"
        style="display:block;margin-top:15px;color:#007185;font-size:13px">Shop fresh produce</a>
    </div>

    <!-- Sign in or Vendor Promo Widget -->
    <div class="card" style="padding:20px;background:#fff;display:flex;flex-direction:column">
      <h3 style="font-size:21px;margin-bottom:15px;color:#0f1111">Sign in for your best experience</h3>
      <?php if (!$user): ?>
        <a href="<?= $base ?>/pages/auth/login.php" class="btn btn-primary"
          style="width:100%;text-align:center;background:#ffd814;color:#0f1111;border:1px solid #fcd200;border-radius:8px">Sign
          in securely</a>
        <div style="text-align:center;margin-top:10px;font-size:13px">New customer? <a
            href="<?= $base ?>/pages/auth/register.php" style="color:#007185">Start here.</a></div>
      <?php else: ?>
        <p style="font-size:14px;color:#565959">Welcome back, <?= htmlspecialchars($user['name']) ?>! Check your recent
          orders or discover new local vendors.</p>
        <a href="<?= $base ?>/pages/customer/orders.php" class="btn btn-outline-primary mt-2">Your Orders</a>
      <?php endif; ?>

      <div style="margin-top:auto;padding-top:20px">
        <a href="<?= $base ?>/pages/vendor/onboarding.php"><img
            src="https://placehold.co/400x150/131921/fff?text=Sell+On+Groceesary"
            style="width:100%;border-radius:4px"></a>
      </div>
    </div>
  </div>

  <!-- ============================================================
       DEAL OF THE DAY HORIZONTAL SCROLL
       ============================================================ -->
  <?php if ($deals): ?>
    <div class="card mb-5" style="padding:20px;background:#fff;overflow:hidden">
      <div style="display:flex;align-items:center;gap:15px;margin-bottom:15px">
        <h2 style="font-size:24px;margin:0">Today's Deals</h2>
        <a href="<?= $base ?>/pages/customer/browse.php?featured=1"
          style="color:#007185;font-size:14px;font-weight:500">See all deals</a>
      </div>
      <div class="h-scroll-container">
        <?php foreach ($deals as $product): ?>
          <div style="min-width:220px;max-width:220px;">
            <?php include '../../templates/components/product_card.php'; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- ============================================================
       STAPLES SCROLL
       ============================================================ -->
  <?php if ($staples): ?>
    <div class="card mb-5" style="padding:20px;background:#fff;overflow:hidden">
      <div style="display:flex;align-items:center;gap:15px;margin-bottom:15px">
        <h2 style="font-size:24px;margin:0">Everyday Staples & Grains</h2>
        <a href="<?= $base ?>/pages/customer/browse.php?category=3"
          style="color:#007185;font-size:14px;font-weight:500">Shop all</a>
      </div>
      <div class="h-scroll-container">
        <?php foreach ($staples as $product): ?>
          <div style="min-width:220px;max-width:220px;">
            <?php include '../../templates/components/product_card.php'; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- ============================================================
       NEW ARRIVALS / RECOMMENDED
       ============================================================ -->
  <?php if ($new_arrivals): ?>
    <div class="card mb-5" style="padding:20px;background:#fff;overflow:hidden">
      <div style="display:flex;align-items:center;gap:15px;margin-bottom:15px">
        <h2 style="font-size:24px;margin:0">New Arrivals Near You</h2>
        <a href="<?= $base ?>/pages/customer/browse.php?sort=newest"
          style="color:#007185;font-size:14px;font-weight:500">Discover more</a>
      </div>
      <div class="h-scroll-container">
        <?php foreach ($new_arrivals as $product): ?>
          <div style="min-width:220px;max-width:220px;">
            <?php include '../../templates/components/product_card.php'; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

</div>

<!-- Registered toast trigger -->
<?php if ($just_registered): ?>
  <script>document.addEventListener('DOMContentLoaded', () => showToast('Welcome to Groceesary! 🎉', 'success', 'Registration Successful', 6000));</script>
<?php endif; ?>

<?php require_once '../../templates/layouts/footer.php'; ?>