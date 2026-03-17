<?php
$page_title = "About Us";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="about-hero">
  <div class="container">
    <div class="section-tag" style="background:rgba(255,212,112,.15);color:var(--amber-light);margin-bottom:1rem;">🌿 Our Story</div>
    <h1 style="color:#fff;margin-bottom:1rem;">About Hilda's Poultry Farm</h1>
    <p style="color:rgba(255,255,255,.75);max-width:560px;margin:0 auto 2rem;">
      A family farm built on passion, dedication, and a love of quality food. Started in 2010 and never looked back.
    </p>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="about-story">
      <div class="about-img reveal">
        <img src="https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=900&q=80" alt="Hilda's Farm">
      </div>
      <div class="reveal">
        <div class="section-tag">Our Story</div>
        <h2 style="margin:1rem 0 1.5rem;">From a Small Backyard to Uganda's Trusted Poultry Farm</h2>
        <p style="color:var(--text-muted);margin-bottom:1.2rem;">
          Hilda's Poultry Farm was founded in 2010 by Hilda Nakato with just 50 chicks and a dream.
          What started as a backyard operation in Kampala quickly grew into one of the region's most
          trusted poultry farms, supplying fresh eggs and quality birds to households, restaurants,
          and supermarkets across Uganda.
        </p>
        <p style="color:var(--text-muted);margin-bottom:1.2rem;">
          We believe that quality food starts with healthy, happy birds. That's why we invest heavily
          in biosecurity, proper nutrition, veterinary care, and humane farming practices. Every flock
          at our farm is raised with care — and that care shows in every egg and every bird we sell.
        </p>
        <p style="color:var(--text-muted);margin-bottom:2rem;">
          Today, we manage over 5,000 birds across multiple pens, employ over 15 local staff, and
          serve more than 200 regular customers. But our values remain the same as day one: freshness,
          quality, and community.
        </p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
          <?php
          $values = [
            ['🌿','Natural Farming','No growth hormones or artificial additives'],
            ['❤️','Animal Welfare','Humane, stress-free conditions for every bird'],
            ['🤝','Community First','Supporting local staff and farmers'],
            ['🔬','Science-Based','Vet-guided health and nutrition programs'],
          ];
          foreach($values as $v): ?>
          <div style="display:flex;gap:.75rem;align-items:flex-start;">
            <span style="font-size:1.5rem;"><?= $v[0] ?></span>
            <div>
              <strong style="display:block;margin-bottom:.2rem;"><?= $v[1] ?></strong>
              <span style="font-size:.85rem;color:var(--text-muted);"><?= $v[2] ?></span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Stats -->
<section class="section section-dark">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:3rem;text-align:center;">
      <?php
      $farmStats = [
        ['5,000+','Birds on Farm'],
        ['200+','Happy Customers'],
        ['14','Years of Service'],
        ['15+','Local Staff Employed'],
        ['4','Poultry Breeds Raised'],
        ['Daily','Fresh Egg Collection'],
      ];
      foreach($farmStats as $s): ?>
      <div class="reveal">
        <div class="stat-number"><?= $s[0] ?></div>
        <div class="stat-label" style="color:rgba(255,255,255,.65);"><?= $s[1] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Team -->
<section class="section section-alt">
  <div class="container">
    <div class="section-header reveal">
      <div class="section-tag">👷 Our Team</div>
      <h2>The People Behind the Farm</h2>
      <p>Dedicated, passionate professionals who care about every bird and every customer.</p>
    </div>
    <div class="team-grid">
      <?php
      $team = [
        ['https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=400&q=80','Hilda Nakato','Founder & Farm Owner'],
        ['https://images.unsplash.com/photo-1552058544-f2b08422138a?w=400&q=80','James Okello','Farm Manager'],
        ['https://images.unsplash.com/photo-1580489944761-15a19d654956?w=400&q=80','Grace Namukasa','Sales Manager'],
        ['https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=400&q=80','Dr. Peter Kato','Poultry Veterinarian'],
      ];
      foreach($team as $m): ?>
      <div class="team-card reveal">
        <img src="<?= $m[0] ?>" alt="<?= $m[1] ?>" loading="lazy">
        <div class="team-card-body">
          <h4><?= $m[1] ?></h4>
          <p><?= $m[2] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<div class="cta-band">
  <div class="container">
    <h2>Want to Visit the Farm?</h2>
    <p>We welcome farm visits, tours, and consultations. Come see how we work and taste the difference.</p>
    <a href="/hildas_farm/pages/contact.php" class="btn btn-dark">📍 Get Directions</a>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
