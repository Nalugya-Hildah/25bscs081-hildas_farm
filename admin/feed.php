<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /hildas_farm/pages/login.php');
    exit;
}

require_once __DIR__ . '/../db_config.php';
$db = getDB();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Add feed purchase
        if ($_POST['action'] === 'add_purchase') {
            try {
                $stmt = $db->prepare("INSERT INTO feed_inventory (feed_id, quantity, unit_cost, supplier, purchase_date, expiry_date, batch_no, added_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['feed_id'],
                    $_POST['quantity'],
                    $_POST['unit_cost'],
                    $_POST['supplier'],
                    $_POST['purchase_date'],
                    $_POST['expiry_date'] ?: null,
                    $_POST['batch_no'],
                    $_SESSION['user_id']
                ]);
                $success = "Feed purchase recorded successfully!";
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
        
        // Record feed usage
        if ($_POST['action'] === 'add_usage') {
            try {
                $stmt = $db->prepare("INSERT INTO feed_usage (flock_id, feed_id, recorded_by, usage_date, quantity, notes) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['flock_id'],
                    $_POST['feed_id'],
                    $_SESSION['user_id'],
                    $_POST['usage_date'],
                    $_POST['quantity'],
                    $_POST['notes']
                ]);
                
                // Optionally update inventory (reduce available stock)
                // This would require a more complex inventory tracking system
                
                $success = "Feed usage recorded successfully!";
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}

// Get current inventory
$inventory = $db->query("
    SELECT fi.*, ft.feed_name, ft.category, ft.unit, u.full_name as added_by_name
    FROM feed_inventory fi
    JOIN feed_types ft ON fi.feed_id = ft.feed_id
    JOIN users u ON fi.added_by = u.user_id
    ORDER BY fi.purchase_date DESC
")->fetchAll();

// Get recent feed usage
$feed_usage = $db->query("
    SELECT fu.*, ft.feed_name, f.flock_name, u.full_name as recorded_by_name
    FROM feed_usage fu
    JOIN feed_types ft ON fu.feed_id = ft.feed_id
    JOIN flocks f ON fu.flock_id = f.flock_id
    JOIN users u ON fu.recorded_by = u.user_id
    ORDER BY fu.usage_date DESC, fu.created_at DESC
    LIMIT 20
")->fetchAll();

// Get feed types for dropdowns
$feed_types = $db->query("SELECT feed_id, feed_name, category, unit FROM feed_types ORDER BY feed_name")->fetchAll();

// Get active flocks for dropdown
$flocks = $db->query("SELECT flock_id, flock_name, current_count FROM flocks WHERE status='active' ORDER BY flock_name")->fetchAll();

// Calculate total inventory by feed type
$inventory_summary = $db->query("
    SELECT ft.feed_id, ft.feed_name, ft.category, ft.unit, SUM(fi.quantity) as total_quantity, AVG(fi.unit_cost) as avg_cost
    FROM feed_inventory fi
    JOIN feed_types ft ON fi.feed_id = ft.feed_id
    GROUP BY ft.feed_id, ft.feed_name, ft.category, ft.unit
    ORDER BY ft.feed_name
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed Management - Hilda's Poultry Farm</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/hildas_farm/assets/css/style.css">
    <style>
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000; }
        .modal.active { display: flex; }
        .modal-content { background: white; padding: 2rem; border-radius: 20px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; }
        .feed-type-badge { background: #f1f5f9; color: #475569; padding: 0.25rem 0.75rem; border-radius: 50px; font-size: 0.85rem; }
        .inventory-card { background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-radius: 16px; padding: 1.5rem; }
        .tab-container { display: flex; gap: 0.5rem; margin-bottom: 2rem; border-bottom: 2px solid #e2e8f0; }
        .tab { padding: 0.75rem 1.5rem; cursor: pointer; font-weight: 600; color: var(--text-muted); border-bottom: 3px solid transparent; }
        .tab.active { color: var(--green); border-bottom-color: var(--green); }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }
    </style>
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
            <a href="/hildas_farm/pages/logout.php" class="nav-btn nav-btn-outline">Logout</a>
        </div>
    </div>
</nav>

<div class="dashboard-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">🐓 Farm Manager</div>
        <div class="sidebar-section">Overview</div>
        <a href="/hildas_farm/admin/dashboard.php" class="sidebar-link">📊 Dashboard</a>
        
        <div class="sidebar-section">Flock</div>
        <a href="/hildas_farm/admin/flocks.php" class="sidebar-link">🐔 Manage Flocks</a>
        <a href="/hildas_farm/admin/mortality.php" class="sidebar-link">📉 Mortality Log</a>
        <a href="/hildas_farm/admin/health.php" class="sidebar-link">💊 Health Records</a>
        
        <div class="sidebar-section">Production</div>
        <a href="/hildas_farm/admin/eggs.php" class="sidebar-link">🥚 Egg Production</a>
        <a href="/hildas_farm/admin/feed.php" class="sidebar-link active">🌾 Feed Management</a>
        
        <div class="sidebar-section">Business</div>
        <a href="/hildas_farm/admin/sales.php" class="sidebar-link">💰 Sales Records</a>
        <a href="/hildas_farm/admin/customers.php" class="sidebar-link">👥 Customers</a>
        <a href="/hildas_farm/admin/expenses.php" class="sidebar-link">🧾 Expenses</a>
        
        <div class="sidebar-section">Admin</div>
        <a href="/hildas_farm/admin/staff.php" class="sidebar-link">👷 Staff</a>
        <a href="/hildas_farm/admin/users.php" class="sidebar-link">🔐 User Accounts</a>
        <a href="/hildas_farm/index.php" class="sidebar-link">🌐 View Website</a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="page-header">
            <h1>Feed Management</h1>
            <p>Track feed inventory and consumption</p>
            <div style="display:flex;gap:1rem;">
                <button class="nav-btn" style="background:var(--green);color:white;" onclick="openModal('purchaseModal')">➕ Record Feed Purchase</button>
                <button class="nav-btn nav-btn-outline" onclick="openModal('usageModal')">🌾 Record Feed Usage</button>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div style="background:#d1fae5;color:#065f46;padding:1rem;border-radius:10px;margin-bottom:1.5rem;"><?= $success ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div style="background:#fee2e2;color:#991b1b;padding:1rem;border-radius:10px;margin-bottom:1.5rem;"><?= $error ?></div>
        <?php endif; ?>

        <!-- Inventory Summary Cards -->
        <div class="stats-cards" style="margin-bottom:2rem;">
            <?php foreach ($inventory_summary as $item): ?>
            <div class="stat-card" style="background:white;">
                <div class="stat-card-icon">🌾</div>
                <div class="stat-card-value"><?= number_format($item['total_quantity'], 2) ?> <?= $item['unit'] ?></div>
                <div class="stat-card-label"><?= $item['feed_name'] ?></div>
                <div class="stat-card-change">Avg Cost: UGX <?= number_format($item['avg_cost'] ?: 0, 0) ?>/<?= $item['unit'] ?></div>
                <span class="feed-type-badge" style="margin-top:0.5rem;display:inline-block;"><?= ucfirst($item['category']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Tabs -->
        <div class="tab-container">
            <div class="tab active" onclick="switchTab('inventory')">📦 Feed Inventory</div>
            <div class="tab" onclick="switchTab('usage')">📊 Feed Usage History</div>
            <div class="tab" onclick="switchTab('types')">📋 Feed Types</div>
        </div>

        <!-- Inventory Tab -->
        <div id="inventory-tab" class="tab-pane active">
            <div class="dash-card">
                <h3>Feed Purchases</h3>
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Purchase Date</th>
                            <th>Feed Type</th>
                            <th>Quantity</th>
                            <th>Unit Cost</th>
                            <th>Total Cost</th>
                            <th>Supplier</th>
                            <th>Batch No.</th>
                            <th>Expiry</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory as $item): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($item['purchase_date'])) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($item['feed_name']) ?></strong>
                                <br><span class="feed-type-badge"><?= ucfirst($item['category']) ?></span>
                            </td>
                            <td><?= number_format($item['quantity'], 2) ?> <?= $item['unit'] ?></td>
                            <td>UGX <?= number_format($item['unit_cost'], 0) ?></td>
                            <td style="font-weight:600;">UGX <?= number_format($item['quantity'] * $item['unit_cost'], 0) ?></td>
                            <td><?= htmlspecialchars($item['supplier'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($item['batch_no'] ?: '-') ?></td>
                            <td>
                                <?php if ($item['expiry_date']): ?>
                                    <?php
                                    $expiry = strtotime($item['expiry_date']);
                                    $now = time();
                                    $days = ceil(($expiry - $now) / (60 * 60 * 24));
                                    $color = $days < 30 ? '#dc2626' : ($days < 60 ? '#f59e0b' : '#10b981');
                                    ?>
                                    <span style="color:<?= $color ?>;font-weight:600;">
                                        <?= date('d M Y', $expiry) ?>
                                        <?php if ($days < 30): ?>
                                            <br><small>(<?= $days ?> days left)</small>
                                        <?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Usage Tab -->
        <div id="usage-tab" class="tab-pane">
            <div class="dash-card">
                <h3>Recent Feed Usage</h3>
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Flock</th>
                            <th>Feed Type</th>
                            <th>Quantity</th>
                            <th>Recorded By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feed_usage as $usage): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($usage['usage_date'])) ?></td>
                            <td><?= htmlspecialchars($usage['flock_name']) ?></td>
                            <td><?= htmlspecialchars($usage['feed_name']) ?></td>
                            <td><span class="badge badge-green"><?= number_format($usage['quantity'], 2) ?> kg</span></td>
                            <td><?= htmlspecialchars($usage['recorded_by_name']) ?></td>
                            <td><?= htmlspecialchars($usage['notes'] ?: '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Feed Types Tab -->
        <div id="types-tab" class="tab-pane">
            <div class="dash-card">
                <h3>Feed Types</h3>
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Feed Name</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $feed_types_all = $db->query("SELECT * FROM feed_types ORDER BY category, feed_name")->fetchAll();
                        foreach ($feed_types_all as $type):
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($type['feed_name']) ?></strong></td>
                            <td><span class="feed-type-badge"><?= ucfirst($type['category']) ?></span></td>
                            <td><?= $type['unit'] ?></td>
                            <td><?= htmlspecialchars($type['description'] ?: '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Purchase Modal -->
<div id="purchaseModal" class="modal">
    <div class="modal-content">
        <h2>Record Feed Purchase</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add_purchase">
            
            <div class="form-group">
                <label>Feed Type</label>
                <select name="feed_id" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
                    <option value="">Select Feed Type</option>
                    <?php foreach ($feed_types as $type): ?>
                    <option value="<?= $type['feed_id'] ?>"><?= htmlspecialchars($type['feed_name']) ?> (<?= ucfirst($type['category']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Purchase Date</label>
                <input type="date" name="purchase_date" value="<?= date('Y-m-d') ?>" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="quantity" step="0.01" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Unit Cost (UGX per unit)</label>
                <input type="number" name="unit_cost" step="100" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Supplier</label>
                <input type="text" name="supplier" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Batch Number</label>
                <input type="text" name="batch_no" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Expiry Date</label>
                <input type="date" name="expiry_date" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div style="display:flex;gap:1rem;justify-content:flex-end;margin-top:1.5rem;">
                <button type="button" class="nav-btn nav-btn-outline" onclick="closeModal('purchaseModal')">Cancel</button>
                <button type="submit" class="nav-btn" style="background:var(--green);color:white;">Record Purchase</button>
            </div>
        </form>
    </div>
</div>

<!-- Usage Modal -->
<div id="usageModal" class="modal">
    <div class="modal-content">
        <h2>Record Feed Usage</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add_usage">
            
            <div class="form-group">
                <label>Flock</label>
                <select name="flock_id" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
                    <option value="">Select Flock</option>
                    <?php foreach ($flocks as $flock): ?>
                    <option value="<?= $flock['flock_id'] ?>"><?= htmlspecialchars($flock['flock_name']) ?> (<?= $flock['current_count'] ?> birds)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Feed Type</label>
                <select name="feed_id" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
                    <option value="">Select Feed Type</option>
                    <?php foreach ($feed_types as $type): ?>
                    <option value="<?= $type['feed_id'] ?>"><?= htmlspecialchars($type['feed_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Usage Date</label>
                <input type="date" name="usage_date" value="<?= date('Y-m-d') ?>" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Quantity (kg)</label>
                <input type="number" name="quantity" step="0.01" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" rows="3" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;"></textarea>
            </div>
            
            <div style="display:flex;gap:1rem;justify-content:flex-end;margin-top:1.5rem;">
                <button type="button" class="nav-btn nav-btn-outline" onclick="closeModal('usageModal')">Cancel</button>
                <button type="submit" class="nav-btn" style="background:var(--green);color:white;">Record Usage</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

function switchTab(tabName) {
    // Update tab buttons
    document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
    event.target.classList.add('active');
    
    // Update tab panes
    document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
    document.getElementById(tabName + '-tab').classList.add('active');
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('active');
    }
}
</script>
<script src="/hildas_farm/assets/js/main.js"></script>
</body>
</html>