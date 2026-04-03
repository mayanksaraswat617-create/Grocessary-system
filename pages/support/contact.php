<?php
/* ===== BACKEND ===== */
require_once '../../config/config.php';
require_once '../../config/constants.php';
require_once '../../config/database.php';

/* ===== FRONTEND ===== */
$page_title = 'Contact Us';
$page_description = 'Get in touch with our support team manually or via email.';
$base = BASE_URL;

require_once '../../templates/layouts/header.php';
require_once '../../templates/layouts/navbar.php';
?>

<div class="page-content" style="background:var(--color-bg);min-height:100vh">
  <div class="container" style="padding-top:var(--space-7);padding-bottom:var(--space-8);max-width:900px">
    
    <div style="text-align:center;margin-bottom:var(--space-7)">
      <h1 style="font-size:3rem;margin-bottom:var(--space-3)">Contact Us ✉️</h1>
      <p style="font-size:var(--text-lg);color:var(--color-muted)">We love hearing from our customers and partners.</p>
    </div>

    <div class="grid grid-cols-2 gap-7">
      
      <!-- Contact Info -->
      <div>
        <h2 class="mb-4">Get in Touch</h2>
        <p class="text-muted mb-6">Whether you have a question about features, pricing, disputes, or anything else, our team is ready to answer all your questions.</p>
        
        <div class="mb-5">
          <div style="font-size:1.5rem;margin-bottom:10px">📍 Office location</div>
          <div class="text-muted text-sm">
            123 Groceesary Tech Hub<br>
            Level 5, Metro Corporate Park<br>
            Mumbai, Maharashtra 400001
          </div>
        </div>

        <div class="mb-5">
          <div style="font-size:1.5rem;margin-bottom:10px">📧 Email address</div>
          <div class="text-muted text-sm">
            support@groceesary.test<br>
            vendor-relations@groceesary.test
          </div>
        </div>

        <div>
          <div style="font-size:1.5rem;margin-bottom:10px">📞 Phone</div>
          <div class="text-muted text-sm">
            +91 98765 43210<br>
            Mon-Fri from 9am to 6pm
          </div>
        </div>
      </div>

      <!-- Contact Form -->
      <div class="card p-6">
        <h3 class="mb-4">Send a message</h3>
        <form onsubmit="event.preventDefault(); showToast('Thank you! Your message has been received.', 'success'); this.reset();">
          <div class="form-group">
            <label class="form-label" for="name">Your Name</label>
            <input type="text" id="name" class="form-control" required placeholder="John Doe">
          </div>
          <div class="form-group">
            <label class="form-label" for="email">Email Address</label>
            <input type="email" id="email" class="form-control" required placeholder="john@example.com">
          </div>
          <div class="form-group">
            <label class="form-label" for="subject">Subject</label>
            <select id="subject" class="form-control" required>
              <option value="">Select a topic...</option>
              <option value="order_issue">My Order</option>
              <option value="vendor_help">Vendor Account Help</option>
              <option value="feedback">General Feedback</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="message">Message</label>
            <textarea id="message" class="form-control" rows="5" required placeholder="How can we help?"></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-full">Send Message</button>
        </form>
      </div>

    </div>

  </div>
</div>

<?php require_once '../../templates/layouts/footer.php'; ?>
