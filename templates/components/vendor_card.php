<?php
/**
 * Vendor Card Component
 * Expects: $vendor (associative array from DB join)
 */
$base   = BASE_URL;
$vid    = (int)$vendor['id'];
$name   = htmlspecialchars($vendor['shop_name']);
$city   = htmlspecialchars($vendor['city'] ?? '');
$rating = (float)($vendor['avg_rating'] ?? 0);
$reviews= (int)($vendor['total_reviews'] ?? 0);
$dtime  = htmlspecialchars($vendor['delivery_time'] ?? '30-60 min');
$min_order = (float)($vendor['min_order_amount'] ?? 0);
$logo   = !empty($vendor['shop_logo']) ? $base . '/' . $vendor['shop_logo'] : null;
$link   = $base . '/pages/customer/browse.php?vendor=' . $vid;
?>
<a href="<?= $link ?>" class="vendor-card card" style="text-decoration:none">
  <!-- Banner Area -->
  <div style="height:90px;background:var(--gradient-secondary);position:relative;overflow:hidden">
    <?php if (!empty($vendor['shop_banner'])): ?>
      <img src="<?= $base . '/' . htmlspecialchars($vendor['shop_banner']) ?>" alt="<?= $name ?>" style="width:100%;height:100%;object-fit:cover">
    <?php endif; ?>
    <!-- Logo Badge -->
    <div style="position:absolute;bottom:-22px;left:16px;width:44px;height:44px;border-radius:var(--radius-md);background:var(--color-white);border:3px solid var(--color-white);box-shadow:var(--shadow-md);overflow:hidden;display:flex;align-items:center;justify-content:center;font-size:1.4rem">
      <?php if ($logo): ?>
        <img src="<?= $logo ?>" alt="<?= $name ?>" style="width:100%;height:100%;object-fit:cover">
      <?php else: ?>
        🏪
      <?php endif; ?>
    </div>
    <!-- Verified Badge -->
    <span class="badge badge-success" style="position:absolute;top:8px;right:8px">✓ Verified</span>
  </div>

  <div class="card-body" style="padding-top:34px">
    <h3 style="font-size:var(--text-base);font-weight:700;margin-bottom:2px"><?= $name ?></h3>
    <?php if ($city): ?>
      <div class="text-xs text-muted mb-2">📍 <?= $city ?></div>
    <?php endif; ?>
    <!-- Rating -->
    <div class="flex items-center gap-2 mb-3">
      <div class="stars" style="font-size:0.8rem">
        <?php for ($i=1;$i<=5;$i++): ?>
          <span class="<?= $i<=round($rating)?'':'star-empty' ?>">★</span>
        <?php endfor; ?>
      </div>
      <span class="text-xs text-muted"><?= number_format($rating,1) ?> (<?= $reviews ?>)</span>
    </div>
    <!-- Meta -->
    <div class="flex gap-3" style="font-size:11px;color:var(--color-muted)">
      <span>⏱ <?= $dtime ?></span>
      <?php if ($min_order > 0): ?>
        <span>🛒 Min ₹<?= number_format($min_order) ?></span>
      <?php endif; ?>
    </div>
  </div>
</a>
