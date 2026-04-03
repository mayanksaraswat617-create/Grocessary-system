/* ============================================================
   GROCEESARY – Cart.js
   Add/remove/update cart items, badge counter
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
  // Bind all "add to cart" buttons
  document.querySelectorAll('[data-add-cart]').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      const productId = btn.dataset.addCart;
      const qty       = parseInt(btn.dataset.qty || '1');
      await addToCart(productId, qty, btn);
    });
  });

  // Bind qty steppers on cart page
  document.querySelectorAll('.cart-qty-stepper').forEach(stepper => {
    const minus = stepper.querySelector('.qty-minus');
    const plus  = stepper.querySelector('.qty-plus');
    const input = stepper.querySelector('.qty-input');
    const pid   = stepper.dataset.productId;

    if (minus) minus.addEventListener('click', async () => {
      let v = Math.max(1, parseInt(input.value) - 1);
      input.value = v;
      await updateCartItem(pid, v);
    });
    if (plus) plus.addEventListener('click', async () => {
      let v = parseInt(input.value) + 1;
      input.value = v;
      await updateCartItem(pid, v);
    });
    if (input) input.addEventListener('change', async () => {
      let v = Math.max(1, parseInt(input.value));
      input.value = v;
      await updateCartItem(pid, v);
    });
  });

  // Bind remove buttons
  document.querySelectorAll('[data-remove-cart]').forEach(btn => {
    btn.addEventListener('click', async () => {
      await removeFromCart(btn.dataset.removeCart, btn.closest('.cart-item'));
    });
  });
});

async function addToCart(productId, quantity = 1, btn = null) {
  if (btn) { btn.disabled = true; btn.textContent = 'Adding…'; }
  try {
    const res = await CartAPI.add(productId, quantity);
    if (res.success) {
      updateCartBadge(res.cart_count);
      showToast('Item added to cart!', 'success', 'Cart Updated');
      if (btn) { btn.textContent = '✓ Added'; setTimeout(() => { btn.disabled = false; btn.textContent = btn.dataset.originalText || 'Add to Cart'; }, 2000); }
    }
  } catch(e) {
    if (btn) { btn.disabled = false; btn.textContent = btn.dataset.originalText || 'Add to Cart'; }
  }
}

async function updateCartItem(productId, quantity) {
  try {
    const res = await CartAPI.update(productId, quantity);
    if (res.success) {
      updateCartBadge(res.cart_count);
      // Update subtotal display if present
      const subtotalEl = document.getElementById('item-subtotal-' + productId);
      if (subtotalEl && res.item_subtotal) subtotalEl.textContent = formatCurrency(res.item_subtotal);
      const totalEl = document.getElementById('cart-total');
      if (totalEl && res.total) totalEl.textContent = formatCurrency(res.total);
    }
  } catch(e) {}
}

async function removeFromCart(productId, rowEl) {
  try {
    const res = await CartAPI.remove(productId);
    if (res.success) {
      updateCartBadge(res.cart_count);
      if (rowEl) { rowEl.style.transition = 'opacity 0.3s'; rowEl.style.opacity = '0'; setTimeout(() => rowEl.remove(), 300); }
      showToast('Item removed from cart.', 'info');
      const totalEl = document.getElementById('cart-total');
      if (totalEl && res.total !== undefined) totalEl.textContent = formatCurrency(res.total);
      if (res.cart_count === 0) {
        const cartBody = document.getElementById('cart-items-body');
        if (cartBody) cartBody.innerHTML = '<tr><td colspan="6" class="text-center" style="padding:60px"><div class="empty-state"><div class="empty-icon">🛒</div><h3>Your cart is empty</h3><a href="browse.php" class="btn btn-primary mt-4">Start Shopping</a></div></td></tr>';
      }
    }
  } catch(e) {}
}

function updateCartBadge(count) {
  document.querySelectorAll('.cart-badge-count').forEach(el => {
    el.textContent = count;
    el.style.display = count > 0 ? 'flex' : 'none';
  });
}
