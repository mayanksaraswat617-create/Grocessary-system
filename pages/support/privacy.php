<?php
/* ===== BACKEND ===== */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';

/* ===== FRONTEND ===== */
$page_title = 'Privacy Policy';
$page_description = 'How we securely handle and protect your personal data.';
$base = BASE_URL;

require_once '../../templates/layouts/header.php';
require_once '../../templates/layouts/navbar.php';
?>

<div class="page-content" style="background:var(--color-bg);min-height:100vh">
  <div class="container" style="padding-top:var(--space-7);padding-bottom:var(--space-8);max-width:800px">
    
    <div style="text-align:center;margin-bottom:var(--space-7)">
      <h1 style="font-size:3rem;margin-bottom:var(--space-3)">Privacy Policy</h1>
      <p style="font-size:var(--text-lg);color:var(--color-muted)">Effective Date: January 1, <?= date('Y') ?></p>
    </div>

    <div class="card p-6" style="line-height:1.7;color:var(--color-text)">
      <h3 class="mb-3">1. Information We Collect</h3>
      <p class="text-sm text-muted mb-5">We collect information you provide directly to us, such as your name, email address, phone number, physical delivery address, and payment information when you register, place an order, or apply as a vendor.</p>

      <h3 class="mb-3">2. How We Use Your Information</h3>
      <p class="text-sm text-muted mb-2">We use the information we collect to:</p>
      <ul class="text-sm text-muted mb-5" style="padding-left:1.5rem">
        <li>Provide, maintain, and improve our marketplace platform services.</li>
        <li>Process your transactions and seamlessly route delivery logistics.</li>
        <li>Send you technical notices, updates, security alerts, and administrative messages.</li>
      </ul>

      <h3 class="mb-3">3. Sharing of Information</h3>
      <p class="text-sm text-muted mb-2">Your information may be shared in the following situations:</p>
      <ul class="text-sm text-muted mb-5" style="padding-left:1.5rem">
        <li><strong>With Vendors:</strong> We share your name, phone number, and delivery address with the specific vendor you ordered from solely to facilitate fulfillment and delivery. We never share underlying payment logic or full card numbers with vendors.</li>
        <li><strong>With Service Providers:</strong> Third-party payment gateways (like Stripe or Razorpay) to process payments safely.</li>
      </ul>

      <h3 class="mb-3">4. Security</h3>
      <p class="text-sm text-muted mb-5">We implement commercially reasonable security measures designed to protect your information from unauthorized access and data breaches. Your passwords are cryptographically hashed and never stored in plain text.</p>

      <h3 class="mb-3">5. Contact Us</h3>
      <p class="text-sm text-muted mb-0">If you have any questions or concern regarding this Privacy Policy or how your data is handled, please reach out to us at <a href="<?= $base ?>/pages/support/contact.php">our Contact page</a>.</p>
    </div>

  </div>
</div>

<?php require_once '../../templates/layouts/footer.php'; ?>
