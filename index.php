<?php
$page_title = "Fresh Farm, Fresh Food";
require_once __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-pattern"></div>
  <div class="hero-container">
    <div class="hero-content">
      <div class="hero-badge">🌿 100% Farm Fresh &nbsp;·&nbsp; Kampala, Uganda</div>
      <h1 class="hero-title">
        <span class="accent">Hilda's Poultry Farm</span>
        <span>The Finest <span class="highlight">Farm-Fresh</span><br>Poultry Products</span>
      </h1>
      <p class="hero-desc">
        From our free-range flocks to your family's table — fresh eggs, healthy birds,
        and quality poultry products raised with care every single day.
      </p>
      <div class="hero-actions">
        <a href="/hildas_farm/pages/products.php" class="btn btn-primary">🛒 Shop Our Products</a>
        <a href="/hildas_farm/pages/contact.php" class="btn btn-secondary">📞 Order Now</a>
      </div>
      <div class="hero-stats">
        <div class="stat-item">
          <span class="stat-number">5000+</span>
          <span class="stat-label">Birds on Farm</span>
        </div>
        <div class="stat-item">
          <span class="stat-number">200+</span>
          <span class="stat-label">Happy Customers</span>
        </div>
        <div class="stat-item">
          <span class="stat-number">14</span>
          <span class="stat-label">Years of Service</span>
        </div>
      </div>
    </div>

    <div class="hero-visual">
      <img src="https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=800&q=80"
           alt="Free-range chickens at Hilda's Farm" class="hero-img-main" loading="eager">
      <img src="https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?w=400&q=80"
           alt="Fresh eggs" class="hero-img-float">
      <div class="hero-tag">
        <div class="tag-num">⭐ 4.9</div>
        <div class="tag-txt">Customer Rating</div>
      </div>
    </div>
  </div>
</section>

<!-- PRODUCTS PREVIEW -->
<section class="section" id="products">
  <div class="container">
    <div class="section-header reveal">
      <div class="section-tag">🥚 What We Offer</div>
      <h2>Fresh Products, Direct From Our Farm</h2>
      <p>Every product is raised naturally, with no harmful additives — just wholesome goodness.</p>
    </div>

    <div class="products-grid">
      <?php
      $products = [
        ['img'=>'https://images.unsplash.com/photo-1506976785307-8732e854ad03?w=600&q=80',
         'badge'=>'Best Seller','title'=>'Fresh Eggs (Tray)','desc'=>'Farm-fresh eggs collected daily from our healthy layer hens. Rich in protein and nutrients.',
         'price'=>'UGX 18,000','unit'=>'/ tray of 30'],
        ['img'=>'https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=600&q=80',
         'badge'=>'Popular','title'=>'Live Broiler Chickens','desc'=>'Healthy, fast-growing broilers raised on quality feed. Perfect for your household or restaurant.',
         'price'=>'UGX 25,000','unit'=>'/ bird'],
        ['img'=>'https://images.unsplash.com/photo-1598103442097-8b74394b95c7?w=600&q=80',
         'badge'=>'Ready to Cook','title'=>'Dressed Chicken','desc'=>'Freshly slaughtered and cleaned chicken, ready for cooking. Available in various sizes.',
         'price'=>'UGX 32,000','unit'=>'/ kg'],
        ['img'=>'https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=600&q=80',
         'badge'=>'New Arrivals','title'=>'Day-Old Chicks','desc'=>'Healthy, vaccinated day-old chicks for your own farm. Quality breeds available.',
         'price'=>'UGX 4,500','unit'=>'/ chick'],
        ['img'=>'https://images.unsplash.com/photo-1583125311428-3b67da5c9a68?w=600&q=80',
         'badge'=>'Organic','title'=>'Layer Hens','desc'=>'Mature layer hens at peak production. Take the work out of starting your own egg farm.',
         'price'=>'UGX 35,000','unit'=>'/ hen'],
        ['img'=>'https://images.unsplash.com/photo-1516467508483-a7212febe31a?w=600&q=80',
         'badge'=>'Eco-Friendly','title'=>'Organic Poultry Manure','desc'=>'Nutrient-rich manure — ideal fertilizer for your gardens and crops. Available in bulk.',
         'price'=>'UGX 5,000','unit'=>'/ bag'],
      ];
      foreach($products as $p): ?>
      <div class="product-card reveal">
        <div class="product-card-img-wrap">
          <img src="<?= $p['img'] ?>" alt="<?= $p['title'] ?>" class="product-card-img" loading="lazy">
        </div>
        <span class="product-badge"><?= $p['badge'] ?></span>
        <div class="product-card-body">
          <h3 class="product-card-title"><?= $p['title'] ?></h3>
          <p class="product-card-desc"><?= $p['desc'] ?></p>
          <div class="product-card-footer">
            <div class="product-price"><?= $p['price'] ?> <span><?= $p['unit'] ?></span></div>
            <a href="/hildas_farm/pages/contact.php" class="btn btn-primary btn-sm">Order</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div style="text-align:center;margin-top:3rem;" class="reveal">
      <a href="/hildas_farm/pages/products.php" class="btn btn-outline">View All Products →</a>
    </div>
  </div>
</section>

<!-- WHY CHOOSE US -->
<section class="section section-dark" id="why">
  <div class="container">
    <div class="section-header reveal">
      <div class="section-tag" style="background:rgba(255,212,112,.15);color:var(--amber-light)">✅ Why Choose Us</div>
      <h2 style="color:#fff">Why Hilda's Farm is Uganda's Favourite</h2>
      <p style="color:rgba(255,255,255,.7)">We've been committed to quality, care, and community since day one.</p>
    </div>
    <div class="features-grid">
      <?php
      $features = [
        ['🌿','100% Natural','No hormones, no artificial feed — just clean, natural nutrition for every bird.'],
        ['🥚','Daily Fresh Eggs','Eggs collected every morning, ensuring peak freshness when they reach you.'],
        ['🚚','Fast Delivery','Same-day or next-day delivery within Kampala and surrounding areas.'],
        ['🩺','Vet-Certified','All flocks are regularly checked by certified poultry veterinarians.'],
        ['💰','Best Prices','Wholesale and retail pricing to suit individual buyers and large businesses.'],
        ['🤝','Trusted Since 2010','14 years of reliable service and thousands of satisfied customers.'],
      ];
      foreach($features as $f): ?>
      <div class="feature-card reveal">
        <div class="feature-icon"><?= $f[0] ?></div>
        <h3><?= $f[1] ?></h3>
        <p><?= $f[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- GALLERY -->
<section class="section section-alt" id="gallery">
  <div class="container">
    <div class="section-header reveal">
      <div class="section-tag">📸 Farm Gallery</div>
      <h2>Life at Hilda's Farm</h2>
      <p>A peek behind the fence — see where your food comes from.</p>
    </div>
    <div class="gallery-grid reveal">
      <div class="gallery-item">
        <img src="https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=800&q=80" alt="Free range chickens">
        <div class="gallery-overlay"><span>Our Free-Range Flock</span></div>
      </div>
      <div class="gallery-item">
        <img src="https://images.unsplash.com/photo-1506976785307-8732e854ad03?w=500&q=80" alt="Fresh eggs">
        <div class="gallery-overlay"><span>Fresh Daily Eggs</span></div>
      </div>
      <div class="gallery-item">
        <img src="https://images.unsplash.com/photo-1611866367069-e8fd2d0a1e6a?w=500&q=80" alt="Chicks">
        <div class="gallery-overlay"><span>Day-Old Chicks</span></div>
      </div>
      <div class="gallery-item">
        <img src="https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?w=500&q=80" alt="Egg sorting">
        <div class="gallery-overlay"><span>Quality Egg Sorting</span></div>
      </div>
      <div class="gallery-item">
        <img src="https://images.unsplash.com/photo-1610848985462-1f67538e22fb?w=500&q=80" alt="Poultry house">
        <div class="gallery-overlay"><span>Modern Poultry Houses</span></div>
      </div>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="section" id="testimonials">
  <div class="container">
    <div class="section-header reveal">
      <div class="section-tag">💬 Testimonials</div>
      <h2>What Our Customers Say</h2>
      <p>Don't just take our word for it.</p>
    </div>
    <div class="testimonials-grid">
      <?php
      $testimonials = [
        ['⭐⭐⭐⭐⭐','I have been buying eggs from Hilda\'s Farm for over 3 years. The quality is always consistent and delivery is always on time. Highly recommend!','Sarah K.','Restaurant Owner, Kampala'],
        ['⭐⭐⭐⭐⭐','I bought day-old chicks and they were all healthy and vaccinated. The team was very helpful with advice on rearing. My mortality rate has been very low!','James M.','Poultry Farmer, Wakiso'],
        ['⭐⭐⭐⭐⭐','Best dressed chicken in Kampala. Always fresh and the price is very fair. I will not go anywhere else for my poultry needs.','Grace N.','Housewife, Entebbe'],
      ];
      foreach($testimonials as $t): ?>
      <div class="testimonial-card reveal">
        <div class="testimonial-stars"><?= $t[0] ?></div>
        <p class="testimonial-text">"<?= $t[1] ?>"</p>
        <div class="testimonial-author">
          <div class="testimonial-avatar">👤</div>
          <div>
            <div class="testimonial-name"><?= $t[2] ?></div>
            <div class="testimonial-role"><?= $t[3] ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA BAND -->
<div class="cta-band">
  <div class="container">
    <h2>Ready to Order? Let's Get Started!</h2>
    <p>Contact us today for wholesale pricing, bulk orders, or just to ask a question. We're always happy to help.</p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
      <a href="/hildas_farm/pages/contact.php" class="btn btn-dark">📞 Call / WhatsApp Us</a>
      <a href="/hildas_farm/pages/products.php" class="btn btn-outline" style="border-color:var(--charcoal);color:var(--charcoal);">Browse All Products</a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
