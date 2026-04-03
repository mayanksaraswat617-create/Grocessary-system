<?php
require_once '../../config/config.php'; require_once '../../config/constants.php'; require_once '../../config/database.php';
$required_role = ROLE_CUSTOMER; require_once '../../templates/layouts/auth_wrapper.php';
$db = Database::getInstance(); $user = current_user(); $errors=[]; $success='';
$order_id = (int)($_GET['order_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token'])||$_POST['csrf_token']!==CSRF_TOKEN) { $errors[]='Invalid request.'; }
    else {
        $product_id=(int)$_POST['product_id']; $order_id_p=(int)$_POST['order_id'];
        $rating=(int)$_POST['rating']; $comment=trim($_POST['comment']??'');
        if(!$rating||$rating<1||$rating>5) { $errors[]='Please select a rating.'; }
        else {
            $oi=$db->prepareOne("SELECT oi.*, p.vendor_id FROM order_items oi JOIN products p ON p.id=oi.product_id JOIN orders o ON o.id=oi.order_id WHERE oi.order_id=? AND oi.product_id=? AND o.user_id=? AND o.order_status='delivered' LIMIT 1",'iii',$order_id_p,$product_id,$user['id']);
            if(!$oi) { $errors[]='You can only review products from delivered orders.'; }
            else {
                $exists=$db->prepareOne("SELECT id FROM reviews WHERE user_id=? AND product_id=? AND order_id=? LIMIT 1",'iii',$user['id'],$product_id,$order_id_p);
                if($exists) { $errors[]='You already reviewed this product.'; }
                else {
                    $db->execute("INSERT INTO reviews(product_id,vendor_id,user_id,order_id,rating,comment) VALUES(?,?,?,?,?,?)",'iiiiss',$product_id,$oi['vendor_id'],$user['id'],$order_id_p,$rating,$comment);
                    $avg=$db->prepareOne("SELECT AVG(rating) AS a, COUNT(*) AS c FROM reviews WHERE product_id=? AND is_approved=1",'i',$product_id);
                    $db->execute("UPDATE products SET avg_rating=?,total_reviews=? WHERE id=?",'dii',round($avg['a'],2),$avg['c'],$product_id);
                    $success='Review submitted! Thank you.';
                }
            }
        }
    }
}
// Eligible items (delivered orders)
$eligible=$db->prepare("SELECT oi.product_id,oi.product_name,oi.product_image,o.id AS order_id,o.order_number,o.created_at, (SELECT id FROM reviews WHERE user_id=? AND product_id=oi.product_id AND order_id=o.id LIMIT 1) AS reviewed_id FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE o.user_id=? AND o.order_status='delivered' ORDER BY o.created_at DESC",'ii',$user['id'],$user['id']);
$base=BASE_URL; $page_title='My Reviews'; require_once '../../templates/layouts/header.php'; require_once '../../templates/layouts/navbar.php';
?>
<div class="page-content" style="background:var(--color-bg)">
  <div class="container-sm" style="padding-top:var(--space-7);padding-bottom:var(--space-8)">
    <h1 style="font-size:var(--text-3xl);margin-bottom:var(--space-6)">⭐ Write Reviews</h1>
    <?php if ($errors): ?><div class="alert alert-danger mb-4"><?php foreach($errors as $e) echo "<div>⚠️ ".htmlspecialchars($e)."</div>"; ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success mb-4">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if (!$eligible): ?>
      <div class="empty-state"><div class="empty-icon">⭐</div><h3>No items to review</h3><p>You can review products after your order is delivered.</p></div>
    <?php else: ?>
      <div style="display:flex;flex-direction:column;gap:var(--space-4)">
        <?php foreach($eligible as $ei):
          $img=!empty($ei['product_image'])?$base.'/'.$ei['product_image']:'https://placehold.co/60x60/f0f0f0/999?text=P';
        ?>
          <div class="card p-5">
            <div class="flex gap-4 mb-4">
              <img src="<?= $img ?>" style="width:60px;height:60px;object-fit:cover;border-radius:var(--radius-md)" onerror="this.src='https://placehold.co/60x60/f0f0f0/999?text=P'">
              <div><div class="fw-semibold"><?= htmlspecialchars($ei['product_name']) ?></div><div class="text-xs text-muted">Order #<?= htmlspecialchars($ei['order_number']) ?> · <?= date('d M Y',strtotime($ei['created_at'])) ?></div></div>
              <?php if($ei['reviewed_id']): ?><span class="badge badge-success ml-auto">✓ Reviewed</span><?php endif; ?>
            </div>
            <?php if(!$ei['reviewed_id']): ?>
              <form method="POST" style="border-top:1px solid var(--color-border);padding-top:var(--space-4)">
                <input type="hidden" name="csrf_token" value="<?= CSRF_TOKEN ?>">
                <input type="hidden" name="product_id" value="<?= $ei['product_id'] ?>">
                <input type="hidden" name="order_id"   value="<?= $ei['order_id'] ?>">
                <div class="form-group">
                  <label class="form-label">Rating</label>
                  <div class="flex gap-2">
                    <?php for($i=1;$i<=5;$i++): ?>
                      <label style="cursor:pointer;font-size:1.5rem">
                        <input type="radio" name="rating" value="<?= $i ?>" style="display:none">
                        <span class="star-input" data-val="<?= $i ?>">☆</span>
                      </label>
                    <?php endfor; ?>
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">Comment (optional)</label>
                  <textarea class="form-control" name="comment" rows="2" placeholder="Share your experience…"></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Submit Review</button>
              </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
<script>
document.querySelectorAll('form').forEach(form=>{
  const stars=form.querySelectorAll('.star-input');
  stars.forEach((s,i)=>{
    s.addEventListener('mouseover',()=>stars.forEach((x,j)=>x.textContent=j<=i?'★':'☆'));
    form.addEventListener('mouseleave',()=>{const v=form.querySelector('input[name=rating]:checked')?.value;stars.forEach((x,j)=>x.textContent=v&&j<v?'★':'☆')});
    s.addEventListener('click',()=>{form.querySelector(`input[name=rating][value="${i+1}"]`).checked=true;stars.forEach((x,j)=>x.textContent=j<=i?'★':'☆')});
  });
});
</script>
<?php require_once '../../templates/layouts/footer.php'; ?>
