<?php
/* ===== BACKEND ===== */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';

/* ===== FRONTEND ===== */
$page_title = 'Terms of Service';
$page_description = 'The rules and terms governing your use of our platform.';
$base = BASE_URL;

require_once '../../templates/layouts/header.php';
require_once '../../templates/layouts/navbar.php';
?>

<div class="page-content" style="background:var(--color-bg);min-height:100vh">
  <div class="container" style="padding-top:var(--space-7);padding-bottom:var(--space-8);max-width:800px">
    
    <div style="text-align:center;margin-bottom:var(--space-7)">
      <h1 style="font-size:3rem;margin-bottom:var(--space-3)">Terms of Service</h1>
      <p style="font-size:var(--text-lg);color:var(--color-muted)">Last Updated: January 1, <?= date('Y') ?></p>
    </div>

    <div class="card p-6" style="line-height:1.7;color:var(--color-text)">
      <h3 class="mb-3">1. Acceptance of Terms</h3>
      <p class="text-sm text-muted mb-5">By accessing or using the Groceesary marketplace, ordering products, or registering an account, you legally agree to be bound by these Terms of Service. If you disagree with any part of the terms, you must discontinue use of our service immediately.</p>

      <h3 class="mb-3">2. User Accounts</h3>
      <p class="text-sm text-muted mb-5">You are fully responsible for safeguarding the password that you use to access the service and for any activities or actions occurring under your password. You must notify us immediately upon becoming aware of any breach of security or unauthorized use of your account.</p>

      <h3 class="mb-3">3. Marketplace Mechanics</h3>
      <p class="text-sm text-muted mb-5">Groceesary acts as an intermediary platform connecting independent local vendors with consumers. We do not personally inventory, inspect, or manage the products sold. All product quality warranties and delivery timeline guarantees rely on the respective vendor. We act as payment collection agents temporarily before settling to vendors.</p>

      <h3 class="mb-3">4. Intellectual Property</h3>
      <p class="text-sm text-muted mb-5">The Service and its original content (excluding physical products listed by vendors), structural features, functionality, and brand logos are and will remain the exclusive property of Groceesary and its licensors.</p>

      <h3 class="mb-3">5. Termination</h3>
      <p class="text-sm text-muted mb-5">We may terminate or suspend access to our Service immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach the Terms.</p>

      <h3 class="mb-3">6. Governing Law</h3>
      <p class="text-sm text-muted mb-0">These Terms shall be governed and construed in accordance with local laws, without regard to its conflict of law provisions.</p>
    </div>

  </div>
</div>

<?php require_once '../../templates/layouts/footer.php'; ?>
