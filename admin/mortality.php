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
            $stmt = $db->prepare("INSERT INTO mortality (flock_id, recorded_by, record_date, quantity, cause, notes) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['flock_id'],
                $_SESSION['user_id'],
                $_POST['record_date'],
                $_POST['quantity'],
                $_POST['cause'],
                $_POST['notes']
            ]);
            
            // Update flock current_count
            $db->prepare("UPDATE flocks SET current_count = current_count - ? WHERE flock_id = ?")->execute([$_POST['quantity'], $_POST['flock_id']]);
            
            $success = "Mortality recorded successfully!";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get mortality records
$mortality = $db->query("
    SELECT m.*, f.flock_name, u.full_name as recorded_by_name
    FROM mortality m
    JOIN flocks f ON m.flock_id = f.flock_id
    JOIN users u ON m.recorded_by = u.user_id
    ORDER BY m.record_date DESC, m.created_at DESC
")->fetchAll();

// Get active flocks for dropdown
$flocks = $db->query("SELECT flock_id, flock_name, current_count FROM flocks WHERE status='active' ORDER BY flock_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mortality Log - Hilda's Poultry Farm</title>
    <link rel="stylesheet" href="/hildas_farm/assets/css/style.css">
    <style>
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000; }
        .modal.active { display: flex; }
        .modal-content { background: white; padding: 2rem; border-radius: 20px; max-width: 500px; width: 90%; }
    </style>
</head>
<body>

<!-- Top Navbar (same as before) -->
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
        <a href="/hildas_farm/admin/mortality.php" class="sidebar-link active">📉 Mortality Log</a>
        <a href="/hildas_farm/admin/health.php" class="sidebar-link">💊 Health Records</a>
        <a href="/hildas_farm/admin/eggs.php" class="sidebar-link">🥚 Egg Production</a>
        <a href="/hildas_farm/admin/feed.php" class="sidebar-link">🌾 Feed Management</a>
        <a href="/hildas_farm/admin/sales.php" class="sidebar-link">💰 Sales Records</a>
        <a href="/hildas_farm/admin/customers.php" class="sidebar-link">👥 Customers</a>
        <a href="/hildas_farm/admin/expenses.php" class="sidebar-link">🧾 Expenses</a>
        <a href="/hildas_farm/admin/staff.php" class="sidebar-link">👷 Staff</a>
        <a href="/hildas_farm/index.php" class="sidebar-link">🌐 View Website</a>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Mortality Log</h1>
            <p>Record and track bird deaths</p>
            <button class="nav-btn" style="background:var(--green);color:white;" onclick="openModal()">➕ Record Mortality</button>
        </div>

        <?php if (isset($success)): ?>
            <div style="background:#d1fae5;color:#065f46;padding:1rem;border-radius:10px;margin-bottom:1.5rem;"><?= $success ?></div>
        <?php endif; ?>

        <!-- Summary Cards -->
        <div class="stats-cards" style="margin-bottom:2rem;">
            <div class="stat-card red">
                <div class="stat-card-icon">📊</div>
                <div class="stat-card-value"><?= array_sum(array_column($mortality, 'quantity')) ?></div>
                <div class="stat-card-label">Total Mortality</div>
            </div>
            <div class="stat-card amber">
                <div class="stat-card-icon">📆</div>
                <div class="stat-card-value">
                    <?php
                    $today = array_filter($mortality, function($m) {
                        return $m['record_date'] == date('Y-m-d');
                    });
                    echo array_sum(array_column($today, 'quantity'));
                    ?>
                </div>
                <div class="stat-card-label">Today's Losses</div>
            </div>
        </div>

        <!-- Mortality Table -->
        <div class="dash-card">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Flock</th>
                        <th>Quantity</th>
                        <th>Cause</th>
                        <th>Recorded By</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mortality as $m): ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($m['record_date'])) ?></td>
                        <td><?= htmlspecialchars($m['flock_name']) ?></td>
                        <td><span class="badge badge-red"><?= $m['quantity'] ?></span></td>
                        <td><span class="badge badge-amber" style="text-transform:capitalize;"><?= $m['cause'] ?></span></td>
                        <td><?= htmlspecialchars($m['recorded_by_name']) ?></td>
                        <td><?= htmlspecialchars($m['notes'] ?: '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Add Modal -->
<div id="mortalityModal" class="modal">
    <div class="modal-content">
        <h2>Record Mortality</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label>Flock</label>
                <select name="flock_id" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
                    <option value="">Select Flock</option>
                    <?php foreach ($flocks as $flock): ?>
                    <option value="<?= $flock['flock_id'] ?>"><?= htmlspecialchars($flock['flock_name']) ?> (Current: <?= $flock['current_count'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="record_date" value="<?= date('Y-m-d') ?>" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="quantity" min="1" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Cause</label>
                <select name="cause" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
                    <option value="disease">Disease</option>
                    <option value="injury">Injury</option>
                    <option value="predator">Predator</option>
                    <option value="culled">Culled</option>
                    <option value="unknown">Unknown</option>
                </select>
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
    document.getElementById('mortalityModal').classList.add('active');
}

function closeModal() {
    document.getElementById('mortalityModal').classList.remove('active');
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