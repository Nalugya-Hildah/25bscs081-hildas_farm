<?php
$page_title = "Contact Us";
$product_inquiry = htmlspecialchars($_GET['product'] ?? '');
require_once __DIR__ . '/../includes/header.php';
?>

<div style="background:linear-gradient(160deg,var(--green) 0%,var(--green-mid) 100%);padding:8rem 0 4rem;text-align:center;color:#fff;margin-top:72px;">
  <div class="container">
    <div class="section-tag" style="background:rgba(255,212,112,.15);color:var(--amber-light);margin-bottom:1rem;">📞 Contact</div>
    <h1 style="color:#fff;margin-bottom:1rem;">Get In Touch With Us</h1>
    <p style="color:rgba(255,255,255,.75);max-width:480px;margin:0 auto;">
      Whether you want to place an order, ask a question, or visit the farm — we're here for you.
    </p>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="contact-grid">

      <!-- Contact Info -->
      <div class="reveal">
        <h2 style="margin-bottom:1rem;">How to Reach Us</h2>
        <p style="color:var(--text-muted);margin-bottom:2rem;">
          We're open Monday through Saturday. Reach us by phone, WhatsApp, or visit us at the farm.
        </p>
        <div class="email-nalugyahildah3@gmail.com">
          <div class="tel:+256785606579>
            <div class="">📍</div>
            <div class="contact-item-text">
              <strong>Address</strong>
              Hilda's Farm Road, Off Entebbe Highway<br>Kampala, Uganda
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-item-icon">📞</div>
            <div class="contact-item-text">
              <strong>Phone / WhatsApp</strong>
              +256 785606579&nbsp;|&nbsp; +256 752108582
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-item-icon">✉️</div>
            <div class="contact-item-text">
              <strong>Email</strong>
              nalugyahildah3@gmail.com
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-item-icon">🕐</div>
            <div class="contact-item-text">
              <strong>Opening Hours</strong>
              Monday – Saturday: 7:00 AM – 6:00 PM<br>Sunday: 9:00 AM – 1:00 PM
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-item-icon">🚚</div>
            <div class="contact-item-text">
              <strong>Delivery</strong>
              Same-day delivery available within Kampala for orders placed before 10 AM.
            </div>
          </div>
        </div>
      </div>

      <!-- Contact Form -->
      <div class="contact-form reveal">
        <h3 style="margin-bottom:.5rem;">Send Us a Message</h3>
        <p style="color:var(--text-muted);font-size:.9rem;margin-bottom:1.5rem;">
          Fill in the form below and we'll get back to you within a few hours.
        </p>

        <form id="contactForm" method="POST" action="">
          <div class="form-row">
            <div class="form-group">
              <label>Your Name *</label>
              <input type="text" name="name" placeholder="e.g. Sarah Namukasa" required>
            </div>
            <div class="form-group">
              <label>Phone Number *</label>
              <input type="tel" name="phone" placeholder="+256 7XX XXX XXX" required>
            </div>
          </div>
          <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="your@email.com">
          </div>
          <div class="form-group">
            <label>What Are You Interested In?</label>
            <select name="interest">
              <option value="">— Select a product —</option>
              <option <?= $product_inquiry=='Fresh Eggs (Tray)'?'selected':'' ?>>Fresh Eggs (Tray)</option>
              <option <?= $product_inquiry=='Bulk Eggs — Crate of 360'?'selected':'' ?>>Bulk Eggs (Crate)</option>
              <option>Live Broiler Chickens</option>
              <option>Dressed Chicken</option>
              <option>Day-Old Chicks</option>
              <option>Layer Hens</option>
              <option>Organic Manure</option>
              <option>Wholesale / Bulk Order</option>
              <option>Farm Visit</option>
              <option>Other</option>
            </select>
          </div>
          <div class="form-group">
            <label>Your Message *</label>
            <textarea name="message" placeholder="Tell us what you need, quantity required, delivery location, etc." required></textarea>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:1rem;border-radius:50px;">
            📨 Send Message
          </button>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- Map Placeholder -->
<div style="background:var(--green);height:340px;display:flex;align-items:center;justify-content:center;text-align:center;color:rgba(255,255,255,.8);">
  <div>
    <div style="font-size:3rem;margin-bottom:1rem;">📍</div>
    <h3 style="color:#fff;">Hilda's Poultry Farm</h3>
    <p>Hilda's Farm Road, Off Entebbe Highway, Kampala, Uganda</p>
    <a href="https://maps.google.com" target="_blank" class="btn btn-secondary" style="margin-top:1rem;">
      Open in Google Maps
    </a>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
