<?php
require_once '../../config/config.php';
require_once '../../config/constants.php';

$base = BASE_URL;
$page_title = 'Sell on ' . APP_NAME;
$page_description = 'Join thousands of local vendors and grow your business with ' . APP_NAME . '.';

require_once '../../templates/layouts/header.php';
require_once '../../templates/layouts/navbar.php';
?>

<style>
  .vendor-hero {
    padding: var(--space-9) 0;
    background: linear-gradient(135deg, #004643 0%, #001e1d 100%);
    color: #fff;
    text-align: center;
    position: relative;
    overflow: hidden;
  }
  .vendor-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url('https://images.unsplash.com/photo-1488459711635-de86df20d5af?q=80&w=2070&auto=format&fit=crop') center/cover no-repeat;
    opacity: 0.15;
    z-index: 0;
  }
  .vendor-hero-content {
    position: relative;
    z-index: 1;
    max-width: 800px;
    margin: 0 auto;
  }
  .vendor-hero h1 {
    font-size: clamp(32px, 5vw, 56px);
    font-weight: 800;
    margin-bottom: var(--space-4);
    line-height: 1.1;
  }
  .vendor-hero p {
    font-size: var(--text-lg);
    opacity: 0.9;
    margin-bottom: var(--space-6);
  }
  .cta-group {
    display: flex;
    gap: var(--space-4);
    justify-content: center;
    flex-wrap: wrap;
  }
  .btn-vendor-primary {
    background: #f3a847;
    color: #0f1111;
    padding: 14px 32px;
    font-weight: 700;
    font-size: 16px;
    border-radius: var(--radius-lg);
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(243,168,71,0.3);
    transition: transform 0.2s, background 0.2s;
  }
  .btn-vendor-primary:hover {
    background: #e29b40;
    transform: translateY(-2px);
  }
  .btn-vendor-secondary {
    background: transparent;
    color: #fff;
    padding: 14px 32px;
    font-weight: 700;
    font-size: 16px;
    border-radius: var(--radius-lg);
    text-decoration: none;
    border: 2px solid rgba(255,255,255,0.4);
    transition: border-color 0.2s, background 0.2s;
  }
  .btn-vendor-secondary:hover {
    border-color: #fff;
    background: rgba(255,255,255,0.1);
  }

  .benefits-section {
    padding: var(--space-9) 0;
    background: #fff;
  }
  .benefits-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--space-6);
  }
  .benefit-card {
    padding: var(--space-6);
    border-radius: var(--radius-xl);
    background: #f8f9fa;
    border: 1px solid var(--color-border);
    transition: transform 0.3s;
  }
  .benefit-card:hover {
    transform: translateY(-5px);
  }
  .benefit-icon {
    font-size: 40px;
    margin-bottom: var(--space-4);
    display: block;
  }
  .benefit-card h3 {
    font-size: var(--text-xl);
    margin-bottom: var(--space-3);
    color: var(--color-dark);
  }

  .steps-section {
    padding: var(--space-9) 0;
    background: #f3f3f3;
  }
  .step-item {
    display: flex;
    gap: var(--space-6);
    margin-bottom: var(--space-7);
    align-items: center;
  }
  .step-number {
    width: 60px;
    height: 60px;
    background: var(--color-primary);
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 800;
    flex-shrink: 0;
  }
</style>

<section class="vendor-hero">
  <div class="container">
    <div class="vendor-hero-content" data-aos="fade-up">
      <h1>Turn your local store into a digital powerhouse</h1>
      <p>Join the <?= APP_NAME ?> family and start selling to thousands of customers in your neighborhood today.</p>
      <div class="cta-group">
        <a href="<?= $base ?>/pages/auth/register.php?role=vendor" class="btn-vendor-primary">Register as Vendor</a>
        <a href="<?= $base ?>/pages/auth/login.php?role=vendor" class="btn-vendor-secondary">Vendor Login</a>
      </div>
    </div>
  </div>
</section>

<section class="benefits-section">
  <div class="container">
    <div class="section-header text-center mb-8">
      <h2 style="font-size:var(--text-4xl);font-weight:800">Why sell on <?= APP_NAME ?>?</h2>
      <p class="text-muted">We provide the tools, you provide the fresh quality.</p>
    </div>
    <div class="benefits-grid">
      <div class="benefit-card">
        <span class="benefit-icon">📈</span>
        <h3>Low Commissions</h3>
        <p>Keep more of what you earn. Our transparent commission structure starts as low as 6%.</p>
      </div>
      <div class="benefit-card">
        <span class="benefit-icon">⚡</span>
        <h3>Fast Payouts</h3>
        <p>Get your money fast. We process payouts every week directly to your bank account.</p>
      </div>
      <div class="benefit-card">
        <span class="benefit-icon">📱</span>
        <h3>Easy Management</h3>
        <p>Manage your inventory, orders, and payouts with our simple vendor dashboard.</p>
      </div>
      <div class="benefit-card">
        <span class="benefit-icon">🚛</span>
        <h3>Delivery Support</h3>
        <p>Leverage our network of local delivery partners to get your products to customers fast.</p>
      </div>
    </div>
  </div>
</section>

<section class="steps-section">
  <div class="container">
    <div class="section-header text-center mb-9">
      <h2 style="font-size:var(--text-4xl);font-weight:800">Start selling in 3 easy steps</h2>
    </div>
    <div style="max-width:700px;margin: 0 auto;">
      <div class="step-item" data-aos="fade-left">
        <div class="step-number">1</div>
        <div>
          <h3 style="font-size:var(--text-xl);font-weight:700">Register Online</h3>
          <p>Fill out our simple registration form with your shop details and identity proof.</p>
        </div>
      </div>
      <div class="step-item" data-aos="fade-left" data-aos-delay="100">
        <div class="step-number">2</div>
        <div>
          <h3 style="font-size:var(--text-xl);font-weight:700">Upload Products</h3>
          <p>Add your fresh produce, set prices, and manage stock levels in seconds.</p>
        </div>
      </div>
      <div class="step-item" data-aos="fade-left" data-aos-delay="200">
        <div class="step-number">3</div>
        <div>
          <h3 style="font-size:var(--text-xl);font-weight:700">Start Receiving Orders</h3>
          <p>Get notified of new orders, pack them, and let our delivery partners handle the rest.</p>
        </div>
      </div>
    </div>
    <div class="text-center mt-9">
        <a href="<?= $base ?>/pages/auth/register.php?role=vendor" class="btn-vendor-primary">Apply Now – It's Free!</a>
    </div>
  </div>
</section>

<?php require_once '../../templates/layouts/footer.php'; ?>
