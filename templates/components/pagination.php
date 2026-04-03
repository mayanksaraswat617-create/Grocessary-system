<?php
/**
 * Pagination Component
 * Expects: $current_page, $total_pages, $base_url (URL without page param)
 */
if (($total_pages ?? 1) <= 1) return;
$cp = (int)($current_page ?? 1);
$tp = (int)($total_pages  ?? 1);
$bu = $base_url ?? '?';
$sep = (strpos($bu, '?') !== false) ? '&' : '?';
?>
<nav class="pagination mt-6 justify-center" aria-label="Pagination">
  <!-- Prev -->
  <a href="<?= $bu.$sep ?>page=<?= max(1,$cp-1) ?>"
     class="page-link <?= $cp<=1?'disabled':'' ?>" aria-label="Previous">&#8592;</a>

  <?php
  $start = max(1, $cp - 2);
  $end   = min($tp, $cp + 2);
  if ($start > 1):
  ?>
    <a href="<?= $bu.$sep ?>page=1" class="page-link">1</a>
    <?php if ($start > 2): ?><span class="page-link disabled">…</span><?php endif; ?>
  <?php endif; ?>

  <?php for ($i = $start; $i <= $end; $i++): ?>
    <a href="<?= $bu.$sep ?>page=<?= $i ?>"
       class="page-link <?= $i===$cp?'active':'' ?>"><?= $i ?></a>
  <?php endfor; ?>

  <?php if ($end < $tp):?>
    <?php if ($end < $tp-1): ?><span class="page-link disabled">…</span><?php endif; ?>
    <a href="<?= $bu.$sep ?>page=<?= $tp ?>" class="page-link"><?= $tp ?></a>
  <?php endif; ?>

  <!-- Next -->
  <a href="<?= $bu.$sep ?>page=<?= min($tp,$cp+1) ?>"
     class="page-link <?= $cp>=$tp?'disabled':'' ?>" aria-label="Next">&#8594;</a>
</nav>
