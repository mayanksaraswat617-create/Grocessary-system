<?php
/* ===== BACKEND ===== */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';

$db = Database::getInstance();

$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;

$where = "verification_status='approved'";
$types = '';
$params = [];

if ($q) {
    $where .= " AND (shop_name LIKE ? OR city LIKE ?)";
    $types .= 'ss';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}

$sql_base = "FROM vendors WHERE $where";

// Count
if ($types) {
    $count_row = $db->prepareOne("SELECT COUNT(*) AS cnt $sql_base", $types, ...$params);
} else {
    $count_row = $db->queryOne("SELECT COUNT(*) AS cnt $sql_base");
}
$total_count = (int)($count_row['cnt'] ?? 0);
$total_pages = max(1, ceil($total_count / $per_page));

// Vendors
$select = "SELECT * $sql_base ORDER BY avg_rating DESC, total_sales DESC LIMIT $per_page OFFSET $offset";
if ($types) {
    $vendors = $db->prepare($select, $types, ...$params);
} else {
    $vendors = $db->query($select);
}

/* ===== FRONTEND ===== */
$page_title = 'Our Vendors';
$page_description = 'Browse and shop from local, trusted vendors near you.';
$base = BASE_URL;

require_once '../../templates/layouts/header.php';
require_once '../../templates/layouts/navbar.php';
?>

<div class="page-content" style="background:var(--color-bg);min-height:100vh">
  <div class="container" style="padding-top:var(--space-7);padding-bottom:var(--space-8)">
    
    <!-- Hero / Header -->
    <div style="background:var(--gradient-primary);border-radius:var(--radius-2xl);padding:var(--space-8);color:#fff;text-align:center;margin-bottom:var(--space-7);position:relative;overflow:hidden">
      <div style="position:relative;z-index:1">
        <h1 style="font-size:3rem;margin-bottom:var(--space-3)">Local Store Directory 🏪</h1>
        <p style="font-size:var(--text-lg);opacity:0.9;max-width:600px;margin:0 auto">Discover fresh produce, daily essentials, and local specialties from certified local vendors.</p>
        
        <!-- Search -->
        <form method="GET" action="" style="max-width:500px;margin:var(--space-6) auto 0;display:flex;gap:var(--space-2)">
          <input type="text" name="q" class="form-control" placeholder="Search by store name or city..." value="<?= htmlspecialchars($q) ?>" style="border:none;padding:12px 20px;border-radius:var(--radius-full)">
          <button type="submit" class="btn btn-dark" style="border-radius:var(--radius-full);padding:0 24px">Search</button>
        </form>
      </div>
      <!-- Background Decor -->
      <div style="position:absolute;top:-50px;right:-50px;width:200px;height:200px;background:rgba(255,255,255,0.1);border-radius:50%"></div>
      <div style="position:absolute;bottom:-100px;left:-50px;width:300px;height:300px;background:rgba(255,255,255,0.1);border-radius:50%"></div>
    </div>

    <!-- Vendor Grid -->
    <?php if ($vendors): ?>
      <div class="grid grid-cols-3 gap-5" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
        <?php foreach ($vendors as $vendor): ?>
          <?php include '../../templates/components/vendor_card.php'; ?>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php
      $current_page = $page;
      $base_url = '?' . http_build_query(array_filter(array_merge($_GET, ['page'=>null]), fn($v)=>$v!==null&&$v!==''));
      include '../../templates/components/pagination.php';
      ?>

    <?php else: ?>
      <div class="empty-state card text-center p-8">
        <div style="font-size:4rem;margin-bottom:var(--space-4)">🏪</div>
        <h3>No Vendors Found</h3>
        <p class="text-muted mb-5">We couldn't find any registered vendors matching your criteria.</p>
        <?php if ($q): ?>
          <a href="<?= $base ?>/pages/customer/vendors.php" class="btn btn-outline-primary">Clear Search</a>
        <?php else: ?>
          <a href="<?= $base ?>/pages/vendor/onboarding.php" class="btn btn-primary">Become a Vendor</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php require_once '../../templates/layouts/footer.php'; ?>
