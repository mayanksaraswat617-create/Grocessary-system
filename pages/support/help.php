<?php
/* ===== BACKEND ===== */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';

/* ===== FRONTEND ===== */
$page_title = 'Help Center';
$page_description = 'Find answers to common questions about orders, delivery, and payments.';
$base = BASE_URL;

require_once '../../templates/layouts/header.php';
require_once '../../templates/layouts/navbar.php';
?>

<div class="page-content" style="background:var(--color-bg);min-height:100vh">
  <div class="container" style="padding-top:var(--space-7);padding-bottom:var(--space-8);max-width:800px">
    
    <div style="text-align:center;margin-bottom:var(--space-7)">
      <h1 style="font-size:3rem;margin-bottom:var(--space-3)">How can we help? 💬</h1>
      <p style="font-size:var(--text-lg);color:var(--color-muted)">Browse our most frequently asked questions or contact us directly.</p>
    </div>

    <!-- Quick Links -->
    <div class="grid grid-cols-2 gap-4 mb-8">
      <a href="<?= $base ?>/pages/customer/orders.php" class="card p-5 text-center" style="text-decoration:none;transition:transform 0.2s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
        <div style="font-size:2rem;margin-bottom:var(--space-2)">📦</div>
        <h3 style="font-size:var(--text-base);margin:0;color:var(--color-text)">Track My Order</h3>
      </a>
      <a href="<?= $base ?>/pages/support/contact.php" class="card p-5 text-center" style="text-decoration:none;transition:transform 0.2s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
        <div style="font-size:2rem;margin-bottom:var(--space-2)">✉️</div>
        <h3 style="font-size:var(--text-base);margin:0;color:var(--color-text)">Contact Support</h3>
      </a>
    </div>

    <!-- FAQs -->
    <div class="card p-6 mb-6">
      <h2 class="mb-4">For Customers</h2>
      
      <div style="margin-bottom:var(--space-4);border-bottom:1px solid var(--color-border);padding-bottom:var(--space-4)">
        <h4 style="margin-bottom:var(--space-2)">Where is my order?</h4>
        <p class="text-sm text-muted mb-0">You can easily track your order status in real-time by navigating to 'My Orders' in your profile dropdown.</p>
      </div>

      <div style="margin-bottom:var(--space-4);border-bottom:1px solid var(--color-border);padding-bottom:var(--space-4)">
        <h4 style="margin-bottom:var(--space-2)">How do refunds work?</h4>
        <p class="text-sm text-muted mb-0">If you received damaged items or missing goods, initiate a refund request from the order page within 12 hours. Refunds are typically processed back to the original payment method within 3-5 business days.</p>
      </div>

      <div>
        <h4 style="margin-bottom:var(--space-2)">Can I change my delivery address?</h4>
        <p class="text-sm text-muted mb-0">Once an order is confirmed by the vendor, the address cannot be changed. If it is urgent, please quickly cancel the order and place a new one, or contact the vendor directly via support.</p>
      </div>
    </div>

    <div class="card p-6">
      <h2 class="mb-4">For Vendors</h2>

      <div style="margin-bottom:var(--space-4);border-bottom:1px solid var(--color-border);padding-bottom:var(--space-4)">
        <h4 style="margin-bottom:var(--space-2)">When do I get paid?</h4>
        <p class="text-sm text-muted mb-0">All successful deliveries from Monday-Sunday are tallied up, and funds are automatically deposited to your verified bank account exactly on Wednesday of the following week.</p>
      </div>

      <div>
        <h4 style="margin-bottom:var(--space-2)">How do I update my inventory?</h4>
        <p class="text-sm text-muted mb-0">Head to the 'My Products' section in your Vendor Dashboard. You can easily adjust the stock count, or toggle items out-of-stock instantly to prevent customer orders you cannot fulfill.</p>
      </div>
    </div>

  </div>
</div>

<?php require_once '../../templates/layouts/footer.php'; ?>
