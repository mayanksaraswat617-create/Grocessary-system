<?php
/* ===== BACKEND ===== */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';

$db = Database::getInstance();

// Inputs
$q          = trim($_GET['q']        ?? '');
$cat_id     = (int)($_GET['category'] ?? 0);
$vendor_id  = (int)($_GET['vendor']   ?? 0);
$sort       = $_GET['sort']           ?? 'popular';
$min_price  = (float)($_GET['min_price'] ?? 0);
$max_price  = (float)($_GET['max_price'] ?? 0);
$featured   = isset($_GET['featured']) ? 1 : 0;
$page       = max(1, (int)($_GET['page'] ?? 1));
$per_page   = ITEMS_PER_PAGE;
$offset     = ($page - 1) * $per_page;

// Build WHERE
$where = "p.is_active=1 AND v.verification_status='approved'";
$types = ''; $params = [];

if ($q)          { $qw = '%' . $q . '%'; $where .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.tags LIKE ?)"; $types .= 'sss'; $params = array_merge($params, [$qw,$qw,$qw]); }
if ($cat_id)     { $where .= " AND p.category_id=?"; $types .= 'i'; $params[] = $cat_id; }
if ($vendor_id)  { $where .= " AND p.vendor_id=?";   $types .= 'i'; $params[] = $vendor_id; }
if ($featured)   { $where .= " AND p.is_featured=1"; }
if ($min_price)  { $where .= " AND (COALESCE(p.discount_price,p.price))>=?"; $types .= 'd'; $params[] = $min_price; }
if ($max_price)  { $where .= " AND (COALESCE(p.discount_price,p.price))<=?"; $types .= 'd'; $params[] = $max_price; }

$order_map = ['popular'=>'p.views DESC','newest'=>'p.created_at DESC','price_asc'=>'COALESCE(p.discount_price,p.price) ASC','price_desc'=>'COALESCE(p.discount_price,p.price) DESC','rating'=>'p.avg_rating DESC'];
$order_sql = $order_map[$sort] ?? 'p.views DESC';

$sql_base = "FROM products p JOIN vendors v ON v.id=p.vendor_id JOIN categories c ON c.id=p.category_id WHERE $where";

// Count
if ($types) {
    $count_row = $db->prepareOne("SELECT COUNT(*) AS cnt $sql_base", $types, ...$params);
} else {
    $count_row = $db->queryOne("SELECT COUNT(*) AS cnt $sql_base");
}
$total_count = (int)($count_row['cnt'] ?? 0);
$total_pages = max(1, ceil($total_count / $per_page));

// Products
$select = "SELECT p.*, v.shop_name, c.name AS category_name $sql_base ORDER BY $order_sql LIMIT $per_page OFFSET $offset";
if ($types) {
    $products = $db->prepare($select, $types, ...$params);
} else {
    $products = $db->query($select);
}

// Categories (for filter)
$categories = $db->query("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order");

/* ===== FRONTEND ===== */
$page_title = $q ? "Search: $q" : ($cat_id ? 'Browse Products' : 'All Products');
$base       = BASE_URL;
require_once '../../templates/layouts/header.php';
require_once '../../templates/layouts/navbar.php';
?>

<div class="page-content" style="background:var(--color-bg);min-height:100vh">
  <div class="container" style="padding-top:var(--space-7)">

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
      <div>
        <h1 style="font-size:var(--text-3xl)">
          <?= $q ? '🔍 Results for "' . htmlspecialchars($q) . '"' : ($cat_id ? '🧺 Browse Products' : '🛒 All Products') ?>
        </h1>
        <p class="text-muted"><?= number_format($total_count) ?> product<?= $total_count != 1 ? 's' : '' ?> found</p>
      </div>
      <!-- Mobile search -->
      <form action="" method="GET" class="show-mobile" style="width:100%;max-width:400px">
        <input type="text" class="form-control" name="q" placeholder="Search products…" value="<?= htmlspecialchars($q) ?>">
      </form>
    </div>

    <div style="display:grid;grid-template-columns:240px 1fr;gap:var(--space-6)">

      <!-- ===== SIDEBAR FILTERS ===== -->
      <aside class="hide-mobile">
        <div class="card p-5" style="position:sticky;top:88px">
          <h3 style="font-size:var(--text-base);margin-bottom:var(--space-5)">🔧 Filters</h3>

          <form method="GET" id="filter-form">
            <?php if ($q): ?><input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>"><?php endif; ?>

            <!-- Categories -->
            <div class="form-group">
              <label class="form-label">Category</label>
              <select class="form-control" name="category" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat['id'] ?>" <?= $cat_id==$cat['id']?'selected':'' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Price Range -->
            <div class="form-group">
              <label class="form-label">Price Range (₹)</label>
              <div class="flex gap-2">
                <input type="number" class="form-control" name="min_price" placeholder="Min" value="<?= $min_price ?: '' ?>" min="0">
                <input type="number" class="form-control" name="max_price" placeholder="Max" value="<?= $max_price ?: '' ?>" min="0">
              </div>
            </div>

            <!-- Sort -->
            <div class="form-group">
              <label class="form-label">Sort By</label>
              <select class="form-control" name="sort" onchange="this.form.submit()">
                <option value="popular"    <?= $sort=='popular'   ?'selected':''?>>Most Popular</option>
                <option value="newest"     <?= $sort=='newest'    ?'selected':''?>>Newest First</option>
                <option value="price_asc"  <?= $sort=='price_asc' ?'selected':''?>>Price: Low to High</option>
                <option value="price_desc" <?= $sort=='price_desc'?'selected':''?>>Price: High to Low</option>
                <option value="rating"     <?= $sort=='rating'    ?'selected':''?>>Top Rated</option>
              </select>
            </div>

            <!-- Featured -->
            <div class="form-check mb-4">
              <input type="checkbox" id="featured_filter" name="featured" value="1" <?= $featured?'checked':'' ?> onchange="this.form.submit()">
              <label for="featured_filter" class="text-sm">⭐ Featured only</label>
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-sm">Apply Filters</button>
            <?php if ($cat_id||$q||$min_price||$max_price||$featured||$sort!='popular'): ?>
              <a href="<?= $base ?>/pages/customer/browse.php" class="btn btn-ghost btn-full btn-sm mt-2">Clear All</a>
            <?php endif; ?>
          </form>
        </div>
      </aside>

      <!-- ===== PRODUCTS GRID ===== -->
      <div>
        <!-- Sort bar (mobile) -->
        <div class="show-mobile mb-4" style="display:flex;gap:var(--space-3);flex-wrap:wrap">
          <?php foreach (['popular'=>'Popular','newest'=>'Newest','price_asc'=>'Price ↑','price_desc'=>'Price ↓','rating'=>'Rating'] as $k=>$l): ?>
            <a href="?<?= http_build_query(array_merge($_GET,['sort'=>$k])) ?>"
               class="badge <?= $sort===$k?'badge-primary':'badge-muted' ?>" style="padding:6px 12px;text-decoration:none"><?= $l ?></a>
          <?php endforeach; ?>
        </div>

        <?php if ($products): ?>
          <div class="products-grid">
            <?php foreach ($products as $product): ?>
              <?php include '../../templates/components/product_card.php'; ?>
            <?php endforeach; ?>
          </div>

          <!-- Pagination -->
          <?php
          $current_page = $page;
          $base_url = '?' . http_build_query(array_filter(array_merge($_GET, ['page'=>null]), fn($v)=>$v!==null&&$v!==''));
          include '../../templates/components/pagination.php';
          ?>

        <?php else: ?>
          <div class="empty-state">
            <div class="empty-icon">🔍</div>
            <h3>No results found</h3>
            <p>Try adjusting your filters or search terms.</p>
            <a href="<?= $base ?>/pages/customer/browse.php" class="btn btn-primary">Browse All Products</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once '../../templates/layouts/footer.php'; ?>
