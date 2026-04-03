<?php
// Determine active page for nav highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$base         = BASE_URL;
$user         = current_user();
$cart_count   = 0;
$notif_count  = 0;

if ($user && $user['role'] === 'customer') {
    $db = Database::getInstance();
    $row = $db->prepareOne("SELECT SUM(quantity) AS cnt FROM cart WHERE user_id = ?", 'i', $user['id']);
    $cart_count  = (int)($row['cnt'] ?? 0);
    $notif_count = (int)($db->prepareOne("SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = ? AND is_read = 0", 'i', $user['id'])['cnt'] ?? 0);
}
?>

<nav class="navbar" id="main-navbar">
  <div class="navbar-inner">

    <!-- Logo -->
    <a href="<?= $base ?>/pages/customer/home.php" class="navbar-logo">
      <div class="logo-icon">🛒</div>
      <span class="logo-text">Groce<span>esary</span></span>
    </a>

    <!-- Top search bar (Amazon style) -->
    <div class="navbar-search hide-mobile" style="flex:1;max-width:800px;margin:0 20px;">
      <form action="<?= $base ?>/pages/customer/browse.php" method="GET" style="display:flex;width:100%;background:#fff;border-radius:var(--radius-md);overflow:hidden">
        <select name="category" class="hide-mobile" style="background:#f3f3f3;border:none;border-right:1px solid #ddd;padding:0 10px;color:#333;font-size:13px;outline:none;cursor:pointer">
          <option value="">All</option>
          <option value="1">Fresh Produce</option>
          <option value="2">Dairy & Eggs</option>
          <option value="3">Staples</option>
        </select>
        <input type="text" name="q" placeholder="Search Groceesary.in" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" autocomplete="off" style="flex:1;border:none;padding:12px 15px;color:#0f1111;background:transparent;outline:none;font-size:15px;border-radius:0;">
        <button type="submit" style="background:var(--color-primary);border:none;padding:0 20px;cursor:pointer;color:#0f1111;font-size:18px"><span class="search-icon" style="position:static;transform:none;color:#0f1111">🔍</span></button>
      </form>
    </div>

    <!-- Right Actions -->
    <div class="navbar-actions">
      <?php if ($user): ?>
        
        <div class="hide-mobile" style="text-align:right;cursor:pointer;margin-right:10px;line-height:1.2">
          <div style="font-size:12px;color:#ccc">Hello, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?></div>
          <div style="font-size:14px;font-weight:700;color:#fff">Account & Lists ▾</div>
        </div>

        <!-- Notifications -->
        <a href="<?= $base ?>/pages/customer/profile.php#notifications" class="navbar-icon-btn" title="Notifications" style="background:transparent;border:none;font-size:1.4rem">
          🔔
          <?php if ($notif_count > 0): ?>
            <span class="badge-count cart-notif-count" style="right:0"><?= $notif_count ?></span>
          <?php endif; ?>
        </a>

        <!-- Cart -->
        <?php if ($user['role'] === 'customer'): ?>
        <a href="<?= $base ?>/pages/customer/cart.php" class="navbar-icon-btn" title="Cart" id="cart-nav-btn" style="background:transparent;border:none;font-size:1.6rem;display:flex;align-items:center;gap:5px">
          🛒
          <span style="font-size:14px;font-weight:700;color:#fff;margin-top:10px">Cart</span>
          <span class="badge-count cart-badge-count" style="<?= $cart_count > 0 ? '' : 'display:none' ?>;top:-5px;left:15px;right:auto;background:var(--color-primary);color:#0f1111">
            <?= $cart_count ?>
          </span>
        </a>
        <?php endif; ?>

        <!-- User Dropdown (mobile or secondary) -->
        <div class="dropdown" id="user-nav-dropdown">
          <button class="navbar-user" id="user-avatar-btn" aria-expanded="false" style="background:transparent;border:none">
            <div class="avatar" style="width:30px;height:30px">
              <?php if (!empty($user['avatar'])): ?>
                <img src="<?= $base . '/' . htmlspecialchars($user['avatar']) ?>" alt="<?= htmlspecialchars($user['name']) ?>">
              <?php else: ?>
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
              <?php endif; ?>
            </div>
          </button>
          <div class="dropdown-menu" id="user-dropdown">
            <div style="padding:var(--space-3) var(--space-4);border-bottom:1px solid var(--color-border)">
              <div class="fw-bold text-sm"><?= htmlspecialchars($user['name']) ?></div>
              <div class="text-xs text-muted"><?= htmlspecialchars($user['email']) ?></div>
            </div>

            <?php if ($user['role'] === 'customer'): ?>
              <a href="<?= $base ?>/pages/customer/profile.php" class="dropdown-item">👤 My Profile</a>
              <a href="<?= $base ?>/pages/customer/orders.php"  class="dropdown-item">📦 My Orders</a>
            <?php elseif ($user['role'] === 'vendor'): ?>
              <a href="<?= $base ?>/pages/vendor/dashboard.php" class="dropdown-item">📊 Dashboard</a>
              <a href="<?= $base ?>/pages/vendor/products.php"  class="dropdown-item">🧺 My Products</a>
              <a href="<?= $base ?>/pages/vendor/orders.php"    class="dropdown-item">📦 Orders</a>
            <?php elseif ($user['role'] === 'admin'): ?>
              <a href="<?= $base ?>/pages/admin/dashboard.php"  class="dropdown-item">📊 Admin Panel</a>
            <?php endif; ?>

            <div class="dropdown-divider"></div>
            <a href="<?= $base ?>/pages/auth/login.php?action=logout" class="dropdown-item danger">🚪 Logout</a>
          </div>
        </div>

      <?php else: ?>
        <a href="<?= $base ?>/pages/auth/login.php"    class="btn btn-ghost btn-sm hide-mobile" style="color:#fff">Sign In</a>
        <a href="<?= $base ?>/pages/auth/register.php" class="btn btn-primary btn-sm">Register</a>
      <?php endif; ?>

      <!-- Mobile Hamburger -->
      <button class="hamburger show-mobile" id="sidebar-toggle" aria-label="Menu" style="margin-left:10px">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</nav>

<!-- Tier 2 Mega Navbar -->
<div class="navbar-tier2 hide-mobile">
  <div class="container" style="display:flex;gap:15px;align-items:center;width:100%;max-width:1280px;margin:auto">
    
    <div class="nav-item-mega" style="position:relative;display:flex;align-items:center;cursor:pointer;padding:10px 5px">
      <a href="#" style="font-weight:700;display:flex;align-items:center;gap:5px"><span style="font-size:16px">☰</span> All Categories</a>
      
      <!-- Mega Menu -->
      <div class="mega-menu card">
        <div class="mega-col">
          <h4>Fresh Produce</h4>
          <a href="<?= $base ?>/pages/customer/browse.php?category=1">Fruits & Nuts</a>
          <a href="<?= $base ?>/pages/customer/browse.php?category=1">Seasonal Vegetables</a>
          <a href="<?= $base ?>/pages/customer/browse.php?category=1">Organic Greens</a>
        </div>
        <div class="mega-col">
          <h4>Dairy & Eggs</h4>
          <a href="<?= $base ?>/pages/customer/browse.php?category=2">Farm Fresh Milk</a>
          <a href="<?= $base ?>/pages/customer/browse.php?category=2">Cheese & Butter</a>
          <a href="<?= $base ?>/pages/customer/browse.php?category=2">Free-Range Eggs</a>
        </div>
        <div class="mega-col">
          <h4>Staples & Spices</h4>
          <a href="<?= $base ?>/pages/customer/browse.php?category=3">Premium Rice</a>
          <a href="<?= $base ?>/pages/customer/browse.php?category=3">Atta & Flours</a>
          <a href="<?= $base ?>/pages/customer/browse.php?category=3">Dals & Pulses</a>
        </div>
        <div class="mega-col">
          <h4>Partners</h4>
          <a href="<?= $base ?>/pages/customer/vendors.php">Browse All Stores</a>
          <a href="<?= $base ?>/pages/vendor/onboarding.php">Sell on Groceesary</a>
          <a href="<?= $base ?>/pages/vendor/commission.php">Vendor Commissions</a>
        </div>
      </div>
    </div>

    <a href="<?= $base ?>/pages/customer/browse.php?featured=1">Today's Deals</a>
    <a href="<?= $base ?>/pages/customer/browse.php?category=1">Fresh</a>
    <a href="<?= $base ?>/pages/customer/vendors.php">Vendors</a>
    <a href="<?= $base ?>/pages/support/help.php">Customer Service</a>
    <a href="<?= $base ?>/pages/customer/browse.php?category=3">Pantry</a>
    <a href="<?= $base ?>/pages/customer/browse.php?category=4">Snacks</a>
  </div>
</div>
