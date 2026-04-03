<?php
/**
 * Generic Modal Component
 * Expects: $modal_id, $modal_title, $modal_body, $modal_footer (HTML strings)
 */
$modal_size = $modal_size ?? ''; // 'large' for wider modals
?>
<div class="modal-overlay" id="<?= $modal_id ?>-overlay">
  <div class="modal <?= $modal_size === 'large' ? 'modal-lg' : '' ?>" id="<?= $modal_id ?>-modal" style="<?= $modal_size==='large'?'max-width:760px':'' ?>">
    <div class="modal-header">
      <h4 class="modal-title"><?= $modal_title ?? 'Dialog' ?></h4>
      <button class="modal-close" onclick="closeModal('<?= $modal_id ?>')" aria-label="Close">&times;</button>
    </div>
    <div class="modal-body"><?= $modal_body ?? '' ?></div>
    <?php if (!empty($modal_footer)): ?>
      <div class="modal-footer"><?= $modal_footer ?></div>
    <?php endif; ?>
  </div>
</div>

<script>
function openModal(id)  {
  const el = document.getElementById(id + '-overlay');
  if (el) el.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  const el = document.getElementById(id + '-overlay');
  if (el) el.classList.remove('open');
  document.body.style.overflow = '';
}
// Close on overlay click
document.getElementById('<?= $modal_id ?>-overlay')?.addEventListener('click', function(e) {
  if (e.target === this) closeModal('<?= $modal_id ?>');
});
// Close on Escape
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal('<?= $modal_id ?>'); });
</script>
