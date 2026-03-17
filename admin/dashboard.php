<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /hildas_farm/pages/login.php');
    exit;
}

require_once __DIR__ . '/../db_config.php';
$db = getDB();

// Fetch summary stats
function getStat(PDO $db, string $sql, array $params = []): string {
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $val = $stmt->fetchColumn();
        return $val !== false ? number_format((float)$val) : '0';
    } catch (Exception $e) { return '0'; }
}

$totalBirds    = getStat($db, "SELECT COALESCE(SUM(current_count),0) FROM flocks WHERE status='active'");
$eggsToday     = getStat($db, "SELECT COALESCE(SUM(eggs_collected),0) FROM egg_production WHERE record_date = CURDATE()");
$salesThisMonth= getStat($db, "SELECT COALESCE(SUM(total_amount),0) FROM sales WHERE MONTH(sale_date)=MONTH(CURDATE()) AND YEAR(sale_date)=YEAR(CURDATE())");
$activeFlocks  = getStat($db, "SELECT COUNT(*) FROM flocks WHERE status='active'");

// Recent sales
try {
    $recentSales = $db->query("
        SELECT s.sale_date, c.full_name, s.sale_type, s.quantity, s.unit, s.total_amount, s.payment_status
        FROM sales s JOIN customers c ON s.customer_id = c.customer_id
        ORDER BY s.created_at DESC LIMIT 8
    ")->fetchAll();
} catch (Exception $e) { $recentSales = []; }

// Recent mortality
try {
    $recentMortality = $db->query("
        SELECT m.record_date, f.flock_name, m.quantity, m.cause
        FROM mortality m JOIN flocks f ON m.flock_id = f.flock_id
        ORDER BY m.created_at DESC LIMIT 5
    ")->fetchAll();
} catch (Exception $e) { $recentMortality = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard — Hilda's Poultry Farm</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/hildas_farm/assets/css/style.css">
</head>
<body>

<!-- Top Navbar -->
<nav class="navbar" id="navbar">
  <div class="nav-container">
    <a href="/hildas_farm/index.php" class="nav-brand">
      <span class="brand-icon">🐓</span>
      <span class="brand-text">Hilda's <em>Poultry Farm</em></span>
    </a>
    <div style="display:flex;align-items:center;gap:1rem;">
      <span style="font-size:.9rem;color:var(--text-muted);">
        👋 Hello, <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong>
        <span style="background:var(--green);color:#fff;padding:.2rem .6rem;border-radius:50px;font-size:.75rem;margin-left:.4rem;">
          <?= ucfirst($_SESSION['role']) ?>
        </span>
      </span>
      <a href="/hildas_farm/pages/logout.php" class="nav-btn nav-btn-outline" style="padding:.4rem 1rem;border-radius:50px;font-size:.85rem;border:2px solid var(--green);color:var(--green);">Logout</a>
    </div>
  </div>
</nav>

<div class="dashboard-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-logo">🐓 Farm Manager</div>

    <div class="sidebar-section">Overview</div>
    <a href="/hildas_farm/admin/dashboard.php" class="sidebar-link active">
      <span class="icon">📊</span> Dashboard
    </a>

    <div class="sidebar-section">Flock</div>
    <a href="/hildas_farm/admin/flocks.php" class="sidebar-link">
      <span class="icon">🐔</span> Manage Flocks
    </a>
    <a href="/hildas_farm/admin/mortality.php" class="sidebar-link">
      <span class="icon">📉</span> Mortality Log
    </a>
    <a href="/hildas_farm/admin/health.php" class="sidebar-link">
      <span class="icon">💊</span> Health Records
    </a>

    <div class="sidebar-section">Production</div>
    <a href="/hildas_farm/admin/eggs.php" class="sidebar-link">
      <span class="icon">🥚</span> Egg Production
    </a>
    <a href="/hildas_farm/admin/feed.php" class="sidebar-link">
      <span class="icon">🌾</span> Feed Management
    </a>

    <div class="sidebar-section">Business</div>
    <a href="/hildas_farm/admin/sales.php" class="sidebar-link">
      <span class="icon">💰</span> Sales Records
    </a>
    <a href="/hildas_farm/admin/customers.php" class="sidebar-link">
      <span class="icon">👥</span> Customers
    </a>
    <a href="/hildas_farm/admin/expenses.php" class="sidebar-link">
      <span class="icon">🧾</span> Expenses
    </a>

    <div class="sidebar-section">Admin</div>
    <a href="/hildas_farm/admin/staff.php" class="sidebar-link">
      <span class="icon">👷</span> Staff
    </a>
    <a href="/hildas_farm/admin/users.php" class="sidebar-link">
      <span class="icon">🔐</span> User Accounts
    </a>
    <a href="/hildas_farm/index.php" class="sidebar-link">
      <span class="icon">🌐</span> View Website
    </a>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">

    <div class="page-header">
      <h1>Farm Dashboard</h1>
      <p>Welcome back! Here's today's overview — <?= date('l, d F Y') ?></p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-cards">
      <div class="stat-card">
        <div class="stat-card-icon">🐔</div>
        <div class="stat-card-value"><?= $totalBirds ?></div>
        <div class="stat-card-label">Total Live Birds</div>
        <div class="stat-card-change">↑ Active Flocks: <?= $activeFlocks ?></div>
      </div>
      <div class="stat-card amber">
        <div class="stat-card-icon">🥚</div>
        <div class="stat-card-value"><?= $eggsToday ?></div>
        <div class="stat-card-label">Eggs Collected Today</div>
        <div class="stat-card-change">↑ Updated this morning</div>
      </div>
      <div class="stat-card brown">
        <div class="stat-card-icon">💰</div>
        <div class="stat-card-value">UGX <?= $salesThisMonth ?></div>
        <div class="stat-card-label">Sales This Month</div>
        <div class="stat-card-change">↑ All sale types combined</div>
      </div>
      <div class="stat-card red">
        <div class="stat-card-icon">📋</div>
        <div class="stat-card-value"><?= $activeFlocks ?></div>
        <div class="stat-card-label">Active Flocks</div>
        <div class="stat-card-change">Across all pens</div>
      </div>
    </div>

    <!-- Dashboard Grid -->
    <div class="dashboard-grid">

      <!-- Recent Sales -->
      <div class="dash-card">
        <div class="dash-card-header">
          <h3>💰 Recent Sales</h3>
          <a href="/hildas_farm/admin/sales.php" style="font-size:.85rem;color:var(--green);font-weight:600;">View All →</a>
        </div>
        <?php if (empty($recentSales)): ?>
          <p style="color:var(--text-muted);text-align:center;padding:2rem;">No sales recorded yet.</p>
        <?php else: ?>
        <table class="dash-table">
          <thead>
            <tr><th>Date</th><th>Customer</th><th>Type</th><th>Amount</th><th>Status</th></tr>
          </thead>
          <tbody>
            <?php foreach($recentSales as $s): ?>
            <tr>
              <td><?= date('d M', strtotime($s['sale_date'])) ?></td>
              <td><?= htmlspecialchars($s['full_name']) ?></td>
              <td style="text-transform:capitalize;"><?= str_replace('_',' ',$s['sale_type']) ?></td>
              <td style="font-weight:600;">UGX <?= number_format($s['total_amount']) ?></td>
              <td>
                <?php
                $sc = $s['payment_status'];
                $cls = $sc==='paid' ? 'badge-green' : ($sc==='partial' ? 'badge-amber' : 'badge-red');
                ?>
                <span class="badge <?= $cls ?>"><?= ucfirst($sc) ?></span>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>

      <!-- Quick Actions + Mortality -->
      <div style="display:flex;flex-direction:column;gap:1.5rem;">
        <div class="dash-card">
          <div class="dash-card-header"><h3>⚡ Quick Actions</h3></div>
          <div class="quick-actions">
            <a href="/hildas_farm/admin/eggs.php" class="qa-btn">🥚 Record Egg Collection</a>
            <a href="/hildas_farm/admin/sales.php" class="qa-btn">💰 Record New Sale</a>
            <a href="/hildas_farm/admin/mortality.php" class="qa-btn">📉 Log Mortality</a>
            <a href="/hildas_farm/admin/feed.php" class="qa-btn">🌾 Record Feed Usage</a>
            <a href="/hildas_farm/admin/customers.php" class="qa-btn">👥 Add Customer</a>
          </div>
        </div>

        <div class="dash-card">
          <div class="dash-card-header">
            <h3>📉 Recent Mortality</h3>
            <a href="/hildas_farm/admin/mortality.php" style="font-size:.85rem;color:var(--green);font-weight:600;">View All →</a>
          </div>
          <?php if (empty($recentMortality)): ?>
            <p style="color:var(--text-muted);text-align:center;padding:1rem;">No mortality recorded recently.</p>
          <?php else: ?>
          <table class="dash-table">
            <thead><tr><th>Date</th><th>Flock</th><th>Qty</th><th>Cause</th></tr></thead>
            <tbody>
              <?php foreach($recentMortality as $m): ?>
              <tr>
                <td><?= date('d M', strtotime($m['record_date'])) ?></td>
                <td><?= htmlspecialchars($m['flock_name']) ?></td>
                <td><span class="badge badge-red"><?= $m['quantity'] ?></span></td>
                <td style="text-transform:capitalize;"><?= $m['cause'] ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </main>
</div>

<script src="/hildas_farm/assets/js/main.js"></script>
</body>
</html>
