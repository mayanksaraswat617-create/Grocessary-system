<?php $base = BASE_URL; ?>

<footer class="footer">
  <div class="container">
    <div class="footer-grid">

      <!-- Brand -->
      <div class="footer-brand">
        <div class="logo-text">Groce<span>esary</span></div>
        <p>Connecting you with the freshest produce from local vendors in your neighborhood. Fast, reliable, and affordable grocery delivery.</p>
        <div class="footer-social">
          <a href="#" title="Facebook"  aria-label="Facebook">📘</a>
          <a href="#" title="Instagram" aria-label="Instagram">📷</a>
          <a href="#" title="Twitter"   aria-label="Twitter">🐦</a>
          <a href="#" title="WhatsApp"  aria-label="WhatsApp">💬</a>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="footer-col">
        <h4>Shop</h4>
        <ul class="footer-links">
          <li><a href="<?= $base ?>/pages/customer/browse.php">All Products</a></li>
          <li><a href="<?= $base ?>/pages/customer/browse.php?featured=1">Featured Items</a></li>
          <li><a href="<?= $base ?>/pages/customer/browse.php?category=1">Fruits & Vegetables</a></li>
          <li><a href="<?= $base ?>/pages/customer/browse.php?category=2">Dairy & Eggs</a></li>
          <li><a href="<?= $base ?>/pages/customer/browse.php?category=3">Staples & Grains</a></li>
          <li><a href="<?= $base ?>/pages/customer/vendors.php">Vendor Directory</a></li>
        </ul>
      </div>

      <!-- Vendor -->
      <div class="footer-col">
        <h4>Vendors</h4>
        <ul class="footer-links">
          <li><a href="<?= $base ?>/pages/vendor/welcome.php">Become a Vendor</a></li>
          <li><a href="<?= $base ?>/pages/vendor/dashboard.php">Vendor Dashboard</a></li>
          <li><a href="<?= $base ?>/pages/vendor/guidelines.php">Vendor Guidelines</a></li>
          <li><a href="<?= $base ?>/pages/vendor/commission.php">Commission Structure</a></li>
        </ul>
      </div>

      <!-- Support -->
      <div class="footer-col">
        <h4>Support</h4>
        <ul class="footer-links">
          <li><a href="<?= $base ?>/pages/support/help.php">Help Center</a></li>
          <li><a href="<?= $base ?>/pages/support/privacy.php">Privacy Policy</a></li>
          <li><a href="<?= $base ?>/pages/support/terms.php">Terms of Service</a></li>
          <li><a href="<?= $base ?>/pages/support/refund_policy.php">Refund Policy</a></li>
          <li><a href="<?= $base ?>/pages/support/contact.php">Contact Us</a></li>
        </ul>
      </div>

    </div>

    <div class="footer-bottom">
      <span>© <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</span>
      <span>Made with ❤️ in India</span>
    </div>
  </div>
</footer>

<!-- Toast container -->
<div id="toast-container"></div>

<!-- Scripts -->
<script src="<?= $base ?>/assets/js/utils.js"></script>
<script src="<?= $base ?>/assets/js/api_client.js"></script>
<script src="<?= $base ?>/assets/js/cart.js"></script>
<?php if (isset($extra_js)) echo $extra_js; ?>
</body>
</html>
