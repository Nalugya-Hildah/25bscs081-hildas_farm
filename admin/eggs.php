<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /hildas_farm/pages/login.php');
    exit;
}

require_once __DIR__ . '/../db_config.php';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        try {
            $stmt = $db->prepare("INSERT INTO egg_production (flock_id, recorded_by, record_date, eggs_collected, cracked_eggs, dirty_eggs, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['flock_id'],
                $_SESSION['user_id'],
                $_POST['record_date'],
                $_POST['eggs_collected'],
                $_POST['cracked_eggs'] ?: 0,
                $_POST['dirty_eggs'] ?: 0,
                $_POST['notes']
            ]);
            $success = "Egg production recorded successfully!";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get today's production
$today_production = $db->prepare("
    SELECT ep.*, f.flock_name, u.full_name as recorded_by_name
    FROM egg_production ep
    JOIN flocks f ON ep.flock_id = f.flock_id
    JOIN users u ON ep.recorded_by = u.user_id
    WHERE ep.record_date = CURDATE()
    ORDER BY ep.created_at DESC
");
$today_production->execute();
$today_production = $today_production->fetchAll();

// Get recent production
$recent_production = $db->query("
    SELECT ep.*, f.flock_name
    FROM egg_production ep
    JOIN flocks f ON ep.flock_id = f.flock_id
    ORDER BY ep.record_date DESC, ep.created_at DESC
    LIMIT 20
")->fetchAll();

// Get active layer flocks
$flocks = $db->query("
    SELECT f.flock_id, f.flock_name, b.breed_name
    FROM flocks f
    JOIN breeds b ON f.breed_id = b.breed_id
    WHERE f.status='active' AND (b.category='layer' OR b.category='dual_purpose')
    ORDER BY f.flock_name
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Egg Production - Hilda's Poultry Farm</title>
    <link rel="stylesheet" href="/hildas_farm/assets/css/style.css">
    <style>
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000; }
        .modal.active { display: flex; }
        .modal-content { background: white; padding: 2rem; border-radius: 20px; max-width: 500px; width: 90%; }
        .production-card { background: white; border-radius: 16px; padding: 1.5rem; margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .egg-stats { display: flex; gap: 2rem; align-items: center; }
        .egg-count { font-size: 2rem; font-weight: 900; color: var(--brown); }
        .egg-label { color: var(--text-muted); font-size: 0.9rem; }
        .saleable { color: var(--green); font-weight: 700; }
    </style>
</head>
<body>

<!-- Top Navbar (same) -->
<nav class="navbar" id="navbar">
    <div class="nav-container">
        <a href="/hildas_farm/index.php" class="nav-brand">
            <span class="brand-icon">🐓</span>
            <span class="brand-text">Hilda's <em>Poultry Farm</em></span>
        </a>
        <div style="display:flex;align-items:center;gap:1rem;">
            <span>👋 <?= htmlspecialchars($_SESSION['full_name']) ?></span>
            <a href="/hildas_farm/pages/logout.php" class="nav-btn nav-btn-outline">Logout</a>
        </div>
    </div>
</nav>

<div class="dashboard-layout">
    <!-- Sidebar (same) -->
    <aside class="sidebar">
        <div class="sidebar-logo">🐓 Farm Manager</div>
        <a href="/hildas_farm/admin/dashboard.php" class="sidebar-link">📊 Dashboard</a>
        <a href="/hildas_farm/admin/flocks.php" class="sidebar-link">🐔 Manage Flocks</a>
        <a href="/hildas_farm/admin/mortality.php" class="sidebar-link">📉 Mortality Log</a>
        <a href="/hildas_farm/admin/health.php" class="sidebar-link">💊 Health Records</a>
        <a href="/hildas_farm/admin/eggs.php" class="sidebar-link active">🥚 Egg Production</a>
        <a href="/hildas_farm/admin/feed.php" class="sidebar-link">🌾 Feed Management</a>
        <a href="/hildas_farm/admin/sales.php" class="sidebar-link">💰 Sales Records</a>
        <a href="/hildas_farm/admin/customers.php" class="sidebar-link">👥 Customers</a>
        <a href="/hildas_farm/admin/expenses.php" class="sidebar-link">🧾 Expenses</a>
        <a href="/hildas_farm/index.php" class="sidebar-link">🌐 View Website</a>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Egg Production</h1>
            <p>Track daily egg collection</p>
            <button class="nav-btn" style="background:var(--green);color:white;" onclick="openModal()">🥚 Record Today's Collection</button>
        </div>

        <?php if (isset($success)): ?>
            <div style="background:#d1fae5;color:#065f46;padding:1rem;border-radius:10px;margin-bottom:1.5rem;"><?= $success ?></div>
        <?php endif; ?>

        <!-- Today's Production Summary -->
        <div class="stats-cards" style="margin-bottom:2rem;">
            <div class="stat-card amber">
                <div class="stat-card-icon">🥚</div>
                <div class="stat-card-value">
                    <?= array_sum(array_column($today_production, 'eggs_collected')) ?>
                </div>
                <div class="stat-card-label">Total Eggs Today</div>
            </div>
            <div class="stat-card green">
                <div class="stat-card-icon">✨</div>
                <div class="stat-card-value">
                    <?php 
                    $saleable = array_sum(array_map(function($p) {
                        return $p['eggs_collected'] - $p['cracked_eggs'] - $p['dirty_eggs'];
                    }, $today_production));
                    echo $saleable;
                    ?>
                </div>
                <div class="stat-card-label">Saleable Eggs</div>
            </div>
            <div class="stat-card red">
                <div class="stat-card-icon">💔</div>
                <div class="stat-card-value">
                    <?= array_sum(array_column($today_production, 'cracked_eggs')) + array_sum(array_column($today_production, 'dirty_eggs')) ?>
                </div>
                <div class="stat-card-label">Damaged/Dirty</div>
            </div>
        </div>

        <!-- Today's Production Details -->
        <?php if (!empty($today_production)): ?>
        <div class="dash-card" style="margin-bottom:2rem;">
            <h3>Today's Production</h3>
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Flock</th>
                        <th>Collected</th>
                        <th>Cracked</th>
                        <th>Dirty</th>
                        <th>Saleable</th>
                        <th>Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($today_production as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['flock_name']) ?></td>
                        <td><span class="badge badge-green"><?= $p['eggs_collected'] ?></span></td>
                        <td><?= $p['cracked_eggs'] ?: '-' ?></td>
                        <td><?= $p['dirty_eggs'] ?: '-' ?></td>
                        <td class="saleable"><?= $p['eggs_collected'] - $p['cracked_eggs'] - $p['dirty_eggs'] ?></td>
                        <td><?= htmlspecialchars($p['recorded_by_name']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Recent Production History -->
        <div class="dash-card">
            <h3>Recent Production History</h3>
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Flock</th>
                        <th>Collected</th>
                        <th>Cracked</th>
                        <th>Dirty</th>
                        <th>Saleable</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_production as $p): ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($p['record_date'])) ?></td>
                        <td><?= htmlspecialchars($p['flock_name']) ?></td>
                        <td><?= $p['eggs_collected'] ?></td>
                        <td><?= $p['cracked_eggs'] ?: '-' ?></td>
                        <td><?= $p['dirty_eggs'] ?: '-' ?></td>
                        <td class="saleable"><?= $p['eggs_collected'] - $p['cracked_eggs'] - $p['dirty_eggs'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Add Modal -->
<div id="eggModal" class="modal">
    <div class="modal-content">
        <h2>Record Egg Collection</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label>Flock</label>
                <select name="flock_id" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
                    <option value="">Select Flock</option>
                    <?php foreach ($flocks as $flock): ?>
                    <option value="<?= $flock['flock_id'] ?>"><?= htmlspecialchars($flock['flock_name']) ?> (<?= $flock['breed_name'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="record_date" value="<?= date('Y-m-d') ?>" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Total Eggs Collected</label>
                <input type="number" name="eggs_collected" min="0" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Cracked Eggs</label>
                <input type="number" name="cracked_eggs" min="0" value="0" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Dirty Eggs</label>
                <input type="number" name="dirty_eggs" min="0" value="0" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" rows="3" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;"></textarea>
            </div>
            
            <div style="display:flex;gap:1rem;justify-content:flex-end;">
                <button type="button" class="nav-btn nav-btn-outline" onclick="closeModal()">Cancel</button>
                <button type="submit" class="nav-btn" style="background:var(--green);color:white;">Record</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('eggModal').classList.add('active');
}

function closeModal() {
    document.getElementById('eggModal').classList.remove('active');
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        closeModal();
    }
}
</script>
<script src="/hildas_farm/assets/js/main.js"></script>
</body>
</html>