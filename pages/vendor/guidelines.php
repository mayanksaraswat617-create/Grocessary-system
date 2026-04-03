<?php
/* ===== BACKEND ===== */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';

/* ===== FRONTEND ===== */
$page_title = 'Vendor Guidelines';
$page_description = 'Learn the rules, policies, and best practices for selling on the Groceesary platform.';
$base = BASE_URL;

require_once '../../templates/layouts/header.php';
require_once '../../templates/layouts/navbar.php';
?>

<div class="page-content" style="background:var(--color-bg);min-height:100vh">
  <div class="container" style="padding-top:var(--space-7);padding-bottom:var(--space-8);max-width:800px">
    
    <div style="text-align:center;margin-bottom:var(--space-8)">
      <h1 style="font-size:3rem;margin-bottom:var(--space-3);background:var(--gradient-primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent">Vendor Guidelines</h1>
      <p style="font-size:var(--text-lg);color:var(--color-muted)">Everything you need to know to successfully sell and grow your business with us.</p>
    </div>

    <div class="card p-6 mb-6">
      <h2 class="mb-4">1. Product Quality & Sourcing</h2>
      <p class="text-muted mb-4">We hold our vendors to the highest standards regarding the freshness and quality of products sold through our platform.</p>
      <ul class="text-muted mb-0" style="padding-left:1.5rem;line-height:1.7">
        <li class="mb-2"><strong>Fresh Produce:</strong> Must be restocked daily. Spoiled or near-expiry items must be aggressively discounted or removed.</li>
        <li class="mb-2"><strong>Packaged Goods:</strong> Must have at least 30 days of shelf life remaining upon delivery to the customer.</li>
        <li><strong>Prohibited Items:</strong> Alcohol, tobacco, hazardous materials, and unregistered pharmaceuticals are strictly prohibited.</li>
      </ul>
    </div>

    <div class="card p-6 mb-6">
      <h2 class="mb-4">2. Order Fulfillment & SLAs</h2>
      <p class="text-muted mb-4">Speed and reliability are key to our customer experience.</p>
      <ul class="text-muted mb-0" style="padding-left:1.5rem;line-height:1.7">
        <li class="mb-2"><strong>Acceptance Time:</strong> Orders must be acknowledged within <strong>15 minutes</strong> of placement.</li>
        <li class="mb-2"><strong>Prep Time:</strong> Orders should be packed and ready for pickup/delivery within <strong>20-30 minutes</strong>.</li>
        <li><strong>Cancellations:</strong> Repeated order cancellations will negatively impact your store ranking and may lead to suspension. Keep inventory updated!</li>
      </ul>
    </div>

    <div class="card p-6 mb-6">
      <h2 class="mb-4">3. Packaging Standards</h2>
      <p class="text-muted mb-4">Proper packaging ensures items receive customers securely.</p>
      <ul class="text-muted mb-0" style="padding-left:1.5rem;line-height:1.7">
        <li class="mb-2"><strong>Eco-Friendly:</strong> We highly encourage the use of biodegradable or recyclable packaging materials.</li>
        <li class="mb-2"><strong>Separation:</strong> Cleaning supplies/chemicals must be bagged separately from edible groceries.</li>
        <li><strong>Sealing:</strong> Meats, dairy, and easily spillable items must be securely sealed to prevent contamination.</li>
      </ul>
    </div>

    <div class="card p-6">
      <h2 class="mb-4">4. Customer Support & Disputes</h2>
      <p class="text-muted mb-4">We intermediate all disputes, but vendor cooperation is required.</p>
      <ul class="text-muted mb-0" style="padding-left:1.5rem;line-height:1.7">
        <li class="mb-2"><strong>Returns:</strong> Damaged goods are subject to standard return policies which will be deducted from vendor payouts if poor packaging / quality is proven.</li>
        <li><strong>Communication:</strong> Always maintain professional communication through the order notes. Avoid direct contact targeting unless necessary for delivery navigation.</li>
      </ul>
    </div>

    <div class="text-center mt-7">
      <h3 class="mb-4">Ready to start growing your business?</h3>
      <a href="<?= $base ?>/pages/vendor/onboarding.php" class="btn btn-primary btn-lg">Apply Now as Vendor</a>
    </div>

  </div>
</div>

<?php require_once '../../templates/layouts/footer.php'; ?>
