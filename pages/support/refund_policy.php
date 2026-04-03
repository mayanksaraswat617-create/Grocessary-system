<?php
/* ===== BACKEND ===== */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';

/* ===== FRONTEND ===== */
$page_title = 'Refund Policy';
$page_description = 'Learn about our conditions for order cancellations, damaged goods, and refunds.';
$base = BASE_URL;

require_once '../../templates/layouts/header.php';
require_once '../../templates/layouts/navbar.php';
?>

<div class="page-content" style="background:var(--color-bg);min-height:100vh">
  <div class="container" style="padding-top:var(--space-7);padding-bottom:var(--space-8);max-width:800px">
    
    <div style="text-align:center;margin-bottom:var(--space-7)">
      <h1 style="font-size:3rem;margin-bottom:var(--space-3)">Refund Policy</h1>
      <p style="font-size:var(--text-lg);color:var(--color-muted)">Simple, fair, and fast refunds when things don't go right.</p>
    </div>

    <div class="card p-6" style="line-height:1.7;color:var(--color-text)">
      <h3 class="mb-3">1. Order Cancellations</h3>
      <p class="text-sm text-muted mb-5">Customers can cancel their order free of charge if the vendor has not yet "Accepted" or "Packed" the order. Once the order status changes to "Out for Delivery", cancellations cannot be processed normally.</p>

      <h3 class="mb-3">2. Missing or Incorrect Items</h3>
      <p class="text-sm text-muted mb-5">If you receive an order with missing items or entirely wrong items, please contact customer support within 12 hours of delivery. Upon verifying with the vendor, the cost of missing/incorrect items will be refunded to your original payment method immediately.</p>

      <h3 class="mb-3">3. Damaged or Expired Goods</h3>
      <p class="text-sm text-muted mb-5">Quality is paramount. If you receive spoiled produce, damaged packaging, or past-expiry items, you are entitled to a full refund. Please provide photographic evidence through our Help Center within 24 hours of receiving your delivery.</p>

      <h3 class="mb-3">4. Refund Processing Time</h3>
      <p class="text-sm text-muted mb-5">Once a refund decision is approved, it usually takes between 3 to 5 business days for the funds to reflect in your bank account or credit card statement, depending on your financial institution's processing times.</p>

      <h3 class="mb-3">5. Exceptions to Refunds</h3>
      <ul class="text-sm text-muted mb-0" style="padding-left:1.5rem">
        <li>Change of mind for perishable grocery items.</li>
        <li>Failure of delivery due to an incorrect address provided at checkout.</li>
        <li>Items that were consumed partially before complaints were raised.</li>
      </ul>
    </div>

  </div>
</div>

<?php require_once '../../templates/layouts/footer.php'; ?>
