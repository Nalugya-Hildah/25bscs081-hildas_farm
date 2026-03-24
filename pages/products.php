<?php
// ============================================================
//  HILDA'S POULTRY FARM — pages/products.php
//  Milestone: From Static to Data-Driven
//  Products are now fetched from tbl_products via db_connect.php
// ============================================================

$page_title = "Our Products";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../db_connect.php';   // ← dedicated connection file

// ------------------------------------------------------------------
// 1. FETCH all active products from the database, ordered by sort_order
// ------------------------------------------------------------------
try {
    $stmt = connectDB()->query(
        "SELECT * FROM tbl_products WHERE is_active = 1 ORDER BY sort_order ASC, id ASC"
    );
    $products = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('[Hilda\'s Farm] products.php query failed: ' . $e->getMessage());
    $products = [];   // safe fallback — empty-state handled below
}

// ------------------------------------------------------------------
// 2. Build unique category list for filter tabs (derived from DB)
// ------------------------------------------------------------------
$categories = ['All'];
foreach ($products as $row) {
    $cat = $row['category'];
    if (!in_array($cat, $categories, true)) {
        $categories[] = $cat;
    }
}
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

    <!-- Filter Tabs — built from database categories -->
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:3rem;justify-content:center;" class="reveal">
      <?php foreach ($categories as $cat): ?>
      <button
        onclick="filterProducts('<?= htmlspecialchars(strtolower(str_replace(' ', '-', $cat)), ENT_QUOTES) ?>')"
        class="btn btn-outline btn-sm"
        style="border-radius:50px;">
        <?= htmlspecialchars($cat) ?>
      </button>
      <?php endforeach; ?>
    </div>

    <!-- ----------------------------------------------------------------
         3. DYNAMIC LOOP — one HTML template, repeated per database row
         ---------------------------------------------------------------- -->
    <div class="products-grid">

      <?php if (empty($products)): ?>
        <!-- Empty-state: shown when tbl_products has no active rows -->
        <div style="
          grid-column: 1 / -1;
          text-align: center;
          padding: 5rem 1rem;
          color: var(--text-muted);
        ">
          <div style="font-size:3.5rem;margin-bottom:1rem;">🐔</div>
          <h3 style="margin-bottom:.5rem;">No products found</h3>
          <p style="max-width:380px;margin:0 auto;">
            Our product catalogue is being updated. Please check back soon,
            or <a href="/hildas_farm/pages/contact.php">contact us</a> directly.
          </p>
        </div>

      <?php else: ?>
        <?php foreach ($products as $p):
            // Parse comma-separated features string into an array
            $features = !empty($p['features'])
                ? array_map('trim', explode(',', $p['features']))
                : [];
        ?>
        <div class="product-card reveal" data-cat="<?= htmlspecialchars($p['category']) ?> all">

          <div class="product-card-img-wrap">
            <img
              src="<?= htmlspecialchars($p['image_url']) ?>"
              alt="<?= htmlspecialchars($p['title']) ?>"
              class="product-card-img"
              loading="lazy">
          </div>

          <?php if (!empty($p['badge'])): ?>
          <span class="product-badge"><?= htmlspecialchars($p['badge']) ?></span>
          <?php endif; ?>

          <div class="product-card-body">
            <h3 class="product-card-title"><?= htmlspecialchars($p['title']) ?></h3>
            <p class="product-card-desc"><?= htmlspecialchars($p['description']) ?></p>

            <?php if (!empty($features)): ?>
            <ul style="margin:.75rem 0 1rem;padding-left:1rem;">
              <?php foreach ($features as $feature): ?>
              <li style="font-size:.83rem;color:var(--text-muted);margin-bottom:.25rem;">
                ✔ <?= htmlspecialchars($feature) ?>
              </li>
              <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <div class="product-card-footer">
              <div class="product-price">
                <?= htmlspecialchars($p['price']) ?>
                <span><?= htmlspecialchars($p['price_unit']) ?></span>
              </div>
              <a
                href="/hildas_farm/pages/contact.php?product=<?= urlencode($p['title']) ?>"
                class="btn btn-primary btn-sm">
                Order Now
              </a>
            </div>
          </div>

        </div>
        <?php endforeach; ?>
      <?php endif; ?>

    </div><!-- /.products-grid -->
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
  document.querySelectorAll('.product-card').forEach(function(card) {
    var cats = card.dataset.cat || '';
    card.style.display = cats.includes(cat) ? '' : 'none';
  });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
