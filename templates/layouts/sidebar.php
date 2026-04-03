<?php
/**
 * Sidebar for Admin and Vendor dashboards.
 * Expects: $sidebar_role ('admin'|'vendor'), $active_page
 */
$base         = BASE_URL;
$sidebar_role = $sidebar_role ?? user_role();
$active_page  = $active_page  ?? basename($_SERVER['PHP_SELF']);
$user         = current_user();

$admin_links = [
    ['icon'=>'📊','label'=>'Dashboard',      'href'=>$base.'/pages/admin/dashboard.php',    'file'=>'dashboard.php'],
    ['icon'=>'🏪','label'=>'Vendors',         'href'=>$base.'/pages/admin/vendors.php',       'file'=>'vendors.php'],
    ['icon'=>'✅','label'=>'Verify KYC',       'href'=>$base.'/pages/admin/vendor_verify.php', 'file'=>'vendor_verify.php'],
    ['icon'=>'🧺','label'=>'Products',         'href'=>$base.'/pages/admin/products.php',      'file'=>'products.php'],
    ['icon'=>'📦','label'=>'Orders',           'href'=>$base.'/pages/admin/orders.php',        'file'=>'orders.php'],
    ['icon'=>'💰','label'=>'Commissions',      'href'=>$base.'/pages/admin/commissions.php',   'file'=>'commissions.php'],
    ['icon'=>'💸','label'=>'Payouts',          'href'=>$base.'/pages/admin/payouts.php',        'file'=>'payouts.php'],
    ['icon'=>'👥','label'=>'Users',            'href'=>$base.'/pages/admin/users.php',          'file'=>'users.php'],
    ['icon'=>'📈','label'=>'Analytics',        'href'=>$base.'/pages/admin/analytics.php',      'file'=>'analytics.php'],
    ['icon'=>'🚨','label'=>'Fraud Monitor',    'href'=>$base.'/pages/admin/fraud_monitor.php',  'file'=>'fraud_monitor.php'],
];

$vendor_links = [
    ['icon'=>'📊','label'=>'Dashboard',     'href'=>$base.'/pages/vendor/dashboard.php',  'file'=>'dashboard.php'],
    ['icon'=>'🧺','label'=>'My Products',   'href'=>$base.'/pages/vendor/products.php',   'file'=>'products.php'],
    ['icon'=>'📦','label'=>'Orders',        'href'=>$base.'/pages/vendor/orders.php',     'file'=>'orders.php'],
    ['icon'=>'💰','label'=>'Earnings',      'href'=>$base.'/pages/vendor/earnings.php',   'file'=>'earnings.php'],
    ['icon'=>'💸','label'=>'Payouts',       'href'=>$base.'/pages/vendor/payouts.php',    'file'=>'payouts.php'],
    ['icon'=>'👤','label'=>'Shop Profile',  'href'=>$base.'/pages/vendor/profile.php',    'file'=>'profile.php'],
];

$links = ($sidebar_role === 'admin') ? $admin_links : $vendor_links;
?>

<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="sidebar-brand">🛒 Groceesary</div>
    <div class="sidebar-role"><?= ucfirst($sidebar_role) ?> Panel</div>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-section">Main Menu</div>
    <?php foreach ($links as $link): ?>
      <a href="<?= $link['href'] ?>"
         class="sidebar-link <?= $active_page === $link['file'] ? 'active' : '' ?>">
        <span class="icon"><?= $link['icon'] ?></span>
        <span><?= $link['label'] ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <?php if ($user): ?>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
      <div style="overflow:hidden">
        <div class="name"><?= htmlspecialchars($user['name']) ?></div>
        <div class="email"><?= htmlspecialchars($user['email']) ?></div>
      </div>
    </div>
    <a href="<?= $base ?>/pages/auth/login.php?action=logout"
       style="display:flex;align-items:center;gap:8px;margin-top:12px;color:rgba(255,255,255,0.45);font-size:var(--text-xs)">
      🚪 Logout
    </a>
  </div>
  <?php endif; ?>
</aside>

<!-- Overlay for mobile -->
<div id="sidebar-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:199"
     onclick="document.getElementById('sidebar').classList.remove('open');this.style.display='none'"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const toggle  = document.getElementById('sidebar-toggle');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebar-overlay');
  if (toggle && sidebar) {
    toggle.addEventListener('click', () => {
      const open = sidebar.classList.toggle('open');
      if (overlay) overlay.style.display = open ? 'block' : 'none';
    });
  }
});
</script>
