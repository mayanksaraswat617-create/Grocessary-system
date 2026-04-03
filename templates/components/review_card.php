<?php
/**
 * Review Card Component
 * Expects: $review (reviews row joined with user name)
 */
$rating  = (int)$review['rating'];
$comment = htmlspecialchars($review['comment'] ?? '');
$author  = htmlspecialchars($review['reviewer_name'] ?? 'Anonymous');
$date    = date('d M Y', strtotime($review['created_at']));
$initial = strtoupper(substr($review['reviewer_name'] ?? 'A', 0, 1));
?>
<div class="review-card" style="padding:var(--space-5);border-bottom:1px solid var(--color-border);display:flex;gap:var(--space-4)">
  <!-- Avatar -->
  <div style="width:40px;height:40px;border-radius:50%;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;flex-shrink:0">
    <?= $initial ?>
  </div>

  <div style="flex:1">
    <div class="flex items-center justify-between mb-1">
      <span class="fw-semibold" style="font-size:var(--text-sm)"><?= $author ?></span>
      <span class="text-xs text-muted"><?= $date ?></span>
    </div>

    <!-- Stars -->
    <div class="stars mb-2" style="font-size:0.85rem">
      <?php for ($i=1;$i<=5;$i++): ?>
        <span class="<?= $i<=$rating?'':'star-empty' ?>">★</span>
      <?php endfor; ?>
    </div>

    <?php if ($comment): ?>
      <p style="font-size:var(--text-sm);margin:0;color:var(--color-text);line-height:var(--leading-relaxed)"><?= $comment ?></p>
    <?php endif; ?>
  </div>
</div>
