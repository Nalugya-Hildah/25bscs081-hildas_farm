<?php
$page_title = "Our Products";
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page Hero -->
<div style="background:linear-gradient(160deg,var(--green) 0%,var(--green-mid) 100%);padding:8rem 0 4rem;text-align:center;color:#fff;margin-top:72px;">
  <div class="container">
    <div class="section-tag" style="background:rgba(255,212,112,.15);color:var(--amber-light);margin-bottom:1rem;">🛒 Our Products</div>
    <h1 style="color:#fff;margin-bottom:1rem;">Fresh From Our Farm</h1>
    <p style="color:rgba(255,255,255,.75);max-width:520px;margin:0 auto;">
      Everything you need — from fresh eggs and live birds to dressed chicken and organic manure.
      All products are farm-fresh and naturally raised.
    </p>
  </div>
</div>

<section class="section">
  <div class="container">

    <!-- Filter Tabs -->
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:3rem;justify-content:center;" class="reveal">
      <?php
      $categories = ['All','Eggs','Live Birds','Dressed Chicken','Chicks','Manure'];
      foreach($categories as $cat): ?>
      <button onclick="filterProducts('<?= strtolower(str_replace(' ','-',$cat)) ?>')"
              class="btn btn-outline btn-sm" style="border-radius:50px;">
        <?= $cat ?>
      </button>
      <?php endforeach; ?>
    </div>

    <div class="products-grid">
      <?php
      $allProducts = [
        ['img'=>'https://images.unsplash.com/photo-1506976785307-8732e854ad03?w=600&q=80','badge'=>'Best Seller','cat'=>'eggs','title'=>'Fresh Eggs — Tray of 30','desc'=>'Farm-fresh eggs collected daily from healthy layer hens. Rich in protein, natural yolk colour, and full flavour.','price'=>'UGX 18,000','unit'=>'/tray','features'=>['Collected daily','Unwashed & natural','Free-range hens']],
        ['img'=>'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?w=600&q=80','badge'=>'Popular','cat'=>'eggs','title'=>'Bulk Eggs — Crate of 360','desc'=>'Perfect for restaurants, hotels, and wholesalers. Consistent size and quality with bulk pricing.','price'=>'UGX 190,000','unit'=>'/crate','features'=>['Bulk discount','Consistent grading','Delivery available']],
        ['img'=>'https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=600&q=80','badge'=>'Fresh','cat'=>'live-birds','title'=>'Live Broiler Chickens','desc'=>'Healthy, fast-growing Ross 308 broilers raised on quality feed. Available at various weights.','price'=>'UGX 25,000','unit'=>'/bird','features'=>['Fully vaccinated','6–8 weeks old','2–2.5 kg avg']],
        ['img'=>'https://images.unsplash.com/photo-1583125311428-3b67da5c9a68?w=600&q=80','badge'=>'Available','cat'=>'live-birds','title'=>'Live Layer Hens','desc'=>'Mature laying hens at peak production. Ideal for starting your own small egg-laying unit.','price'=>'UGX 35,000','unit'=>'/hen','features'=>['Peak production age','20–25 weeks','Rhode Island Red']],
        ['img'=>'https://images.unsplash.com/photo-1598103442097-8b74394b95c7?w=600&q=80','badge'=>'Ready to Cook','cat'=>'dressed-chicken','title'=>'Dressed Whole Chicken','desc'=>'Freshly slaughtered, cleaned, and ready for cooking. Available fresh or chilled.','price'=>'UGX 32,000','unit'=>'/kg','features'=>['Same-day fresh','Properly cleaned','Various sizes']],
        ['img'=>'https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=600&q=80','badge'=>'New','cat'=>'dressed-chicken','title'=>'Chicken Parts (Cut)','desc'=>'Drumsticks, breasts, thighs, and wings — available separately for your specific cooking needs.','price'=>'UGX 28,000','unit'=>'/kg','features'=>['Cut to order','Fresh daily','Restaurant grade']],
        ['img'=>'https://images.unsplash.com/photo-1611866367069-e8fd2d0a1e6a?w=600&q=80','badge'=>'Day-Old','cat'=>'chicks','title'=>'Day-Old Broiler Chicks','desc'=>'Healthy, vaccinated Ross 308 day-old chicks for your own broiler farm. High survival rate.','price'=>'UGX 4,500','unit'=>'/chick','features'=>['Marek\'s vaccinated','High hatch rate','Minimum 50 chicks']],
        ['img'=>'https://images.unsplash.com/photo-1516467508483-a7212febe31a?w=600&q=80','badge'=>'Organic','cat'=>'manure','title'=>'Organic Poultry Manure','desc'=>'Nutrient-rich poultry manure — excellent organic fertiliser for crops, gardens, and farms.','price'=>'UGX 5,000','unit'=>'/bag','features'=>['High nitrogen content','Bulk available','Delivery in Kampala']],
      ];
      foreach($allProducts as $p): ?>
      <div class="product-card reveal" data-cat="<?= $p['cat'] ?> all">
        <div class="product-card-img-wrap">
          <img src="<?= $p['img'] ?>" alt="<?= $p['title'] ?>" class="product-card-img" loading="lazy">
        </div>
        <span class="product-badge"><?= $p['badge'] ?></span>
        <div class="product-card-body">
          <h3 class="product-card-title"><?= $p['title'] ?></h3>
          <p class="product-card-desc"><?= $p['desc'] ?></p>
          <ul style="margin:.75rem 0 1rem;padding-left:1rem;">
            <?php foreach($p['features'] as $f): ?>
            <li style="font-size:.83rem;color:var(--text-muted);margin-bottom:.25rem;">✔ <?= $f ?></li>
            <?php endforeach; ?>
          </ul>
          <div class="product-card-footer">
            <div class="product-price"><?= $p['price'] ?> <span><?= $p['unit'] ?></span></div>
            <a href="/hildas_farm/pages/contact.php?product=<?= urlencode($p['title']) ?>"
               class="btn btn-primary btn-sm">Order Now</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Bulk Orders CTA -->
<div class="cta-band">
  <div class="container">
    <h2>Need Bulk Orders or Custom Quantities?</h2>
    <p>We supply hotels, restaurants, schools, and supermarkets across Uganda. Contact us for wholesale pricing and regular supply agreements.</p>
    <a href="/hildas_farm/pages/contact.php" class="btn btn-dark">📞 Request Wholesale Pricing</a>
  </div>
</div>

<script>
function filterProducts(cat) {
  document.querySelectorAll('.product-card').forEach(card => {
    const cats = card.dataset.cat || '';
    card.style.display = cats.includes(cat) ? '' : 'none';
  });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
