<?php
/* ===== BACKEND ===== */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';

/* ===== FRONTEND ===== */
$page_title = 'Commission Structure';
$page_description = 'Transparent fees, no hidden costs. Learn about our clear commission structure for vendors.';
$base = BASE_URL;

require_once '../../templates/layouts/header.php';
require_once '../../templates/layouts/navbar.php';
?>

<div class="page-content" style="background:var(--color-bg);min-height:100vh">
  <div class="container" style="padding-top:var(--space-7);padding-bottom:var(--space-8);max-width:900px">
    
    <div style="text-align:center;margin-bottom:var(--space-8)">
      <h1 style="font-size:3rem;margin-bottom:var(--space-3);background:var(--gradient-primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent">Transparent Commission</h1>
      <p style="font-size:var(--text-lg);color:var(--color-muted)">We only make money when you do. No hidden setup fees, no monthly software charges.</p>
    </div>

    <!-- Highlights -->
    <div class="grid grid-cols-3 gap-5 mb-8 text-center">
      <div class="card p-6">
        <div style="font-size:2.5rem;margin-bottom:var(--space-2)">🆓</div>
        <h3 class="mb-2">₹0 Setup Fees</h3>
        <p class="text-sm text-muted">Join the platform entirely for free. Zero friction to get started.</p>
      </div>
      <div class="card p-6">
        <div style="font-size:2.5rem;margin-bottom:var(--space-2)">💳</div>
        <h3 class="mb-2">Flat Rate Payments</h3>
        <p class="text-sm text-muted">Payment gateways are handled by us at no additional per-transaction fee to you.</p>
      </div>
      <div class="card p-6">
        <div style="font-size:2.5rem;margin-bottom:var(--space-2)">⚡</div>
        <h3 class="mb-2">Quick Payouts</h3>
        <p class="text-sm text-muted">Get your earnings deposited directly into your bank every week.</p>
      </div>
    </div>

    <!-- Commission Table -->
    <div class="card overflow-hidden mb-8">
      <div style="padding:var(--space-5);background:rgba(0,0,0,0.02);border-bottom:1px solid var(--color-border)">
        <h2 style="font-size:var(--text-xl);margin:0">Category breakdown</h2>
      </div>
      <table style="width:100%;border-collapse:collapse;text-align:left">
        <thead>
          <tr style="border-bottom:2px solid var(--color-border)">
            <th style="padding:var(--space-4)">Product Category</th>
            <th style="padding:var(--space-4)">Platform Commission</th>
            <th style="padding:var(--space-4)">Example (On ₹100 Sale)</th>
          </tr>
        </thead>
        <tbody>
          <tr style="border-bottom:1px solid var(--color-border)">
            <td style="padding:var(--space-4)"><strong>Fresh Produce (Fruits & Veg)</strong></td>
            <td style="padding:var(--space-4)"><span class="badge badge-success" style="font-size:1rem">5%</span></td>
            <td style="padding:var(--space-4)">You keep <strong>₹95.00</strong></td>
          </tr>
          <tr style="border-bottom:1px solid var(--color-border)">
            <td style="padding:var(--space-4)"><strong>Dairy & Eggs</strong></td>
            <td style="padding:var(--space-4)"><span class="badge badge-success" style="font-size:1rem">7%</span></td>
            <td style="padding:var(--space-4)">You keep <strong>₹93.00</strong></td>
          </tr>
          <tr style="border-bottom:1px solid var(--color-border)">
            <td style="padding:var(--space-4)"><strong>Staples & Groceries</strong></td>
            <td style="padding:var(--space-4)"><span class="badge badge-primary" style="font-size:1rem">8%</span></td>
            <td style="padding:var(--space-4)">You keep <strong>₹92.00</strong></td>
          </tr>
          <tr style="border-bottom:1px solid var(--color-border)">
            <td style="padding:var(--space-4)"><strong>Snacks & Beverages</strong></td>
            <td style="padding:var(--space-4)"><span class="badge badge-primary" style="font-size:1rem">10%</span></td>
            <td style="padding:var(--space-4)">You keep <strong>₹90.00</strong></td>
          </tr>
          <tr>
            <td style="padding:var(--space-4)"><strong>Personal Care & Cleaning</strong></td>
            <td style="padding:var(--space-4)"><span class="badge badge-muted" style="font-size:1rem;color:var(--color-dark)">12%</span></td>
            <td style="padding:var(--space-4)">You keep <strong>₹88.00</strong></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Payout logic -->
    <div class="card p-6 bg-light text-center">
      <h3 class="mb-3">Weekly Settlement Cycle</h3>
      <p class="text-muted mb-0" style="max-width:600px;margin:0 auto">All orders delivered successfully from Monday to Sunday will be processed exactly the following Wednesday. Payouts are directly wired to your listed bank account via NEFT/RTGS.</p>
    </div>

  </div>
</div>

<?php require_once '../../templates/layouts/footer.php'; ?>
