<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Hilda's Poultry Farm <?= isset($page_title) ? "— $page_title" : "" ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/hildas_farm/assets/css/style.css">
  <?= isset($extra_css) ? $extra_css : "" ?>
</head>
<body>

<nav class="navbar" id="navbar">
  <div class="nav-container">
    <a href="/hildas_farm/index.php" class="nav-brand">
      <span class="brand-icon">🐓</span>
      <span class="brand-text">Hilda's <em>Poultry Farm</em></span>
    </a>

    <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>

    <ul class="nav-links" id="navLinks">
      <li><a href="/hildas_farm/index.php" class="<?= $current_page=='index'?'active':'' ?>">Home</a></li>
      <li><a href="/hildas_farm/pages/products.php" class="<?= $current_page=='products'?'active':'' ?>">Our Products</a></li>
      <li><a href="/hildas_farm/pages/about.php" class="<?= $current_page=='about'?'active':'' ?>">About Us</a></li>
      <li><a href="/hildas_farm/pages/contact.php" class="<?= $current_page=='contact'?'active':'' ?>">Contact</a></li>
      <?php if (isset($_SESSION['user_id'])): ?>
        <li><a href="/hildas_farm/admin/dashboard.php" class="nav-btn">Dashboard</a></li>
        <li><a href="/hildas_farm/pages/logout.php" class="nav-btn nav-btn-outline">Logout</a></li>
      <?php else: ?>
        <li><a href="/hildas_farm/pages/login.php" class="nav-btn">Staff Login</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>
