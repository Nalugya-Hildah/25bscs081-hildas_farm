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
        // Add new flock
        if ($_POST['action'] === 'add') {
            try {
                $stmt = $db->prepare("INSERT INTO flocks (flock_name, breed_id, pen_id, date_acquired, initial_count, current_count, source, acquisition_cost, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['flock_name'],
                    $_POST['breed_id'],
                    $_POST['pen_id'],
                    $_POST['date_acquired'],
                    $_POST['initial_count'],
                    $_POST['initial_count'], // current_count starts same as initial
                    $_POST['source'],
                    $_POST['acquisition_cost'] ?: 0,
                    $_POST['status'],
                    $_POST['notes']
                ]);
                $success = "Flock added successfully!";
            } catch (Exception $e) {
                $error = "Error adding flock: " . $e->getMessage();
            }
        }
        
        // Update flock
        if ($_POST['action'] === 'update') {
            try {
                $stmt = $db->prepare("UPDATE flocks SET flock_name=?, breed_id=?, pen_id=?, date_acquired=?, initial_count=?, current_count=?, source=?, acquisition_cost=?, status=?, notes=? WHERE flock_id=?");
                $stmt->execute([
                    $_POST['flock_name'],
                    $_POST['breed_id'],
                    $_POST['pen_id'],
                    $_POST['date_acquired'],
                    $_POST['initial_count'],
                    $_POST['current_count'],
                    $_POST['source'],
                    $_POST['acquisition_cost'] ?: 0,
                    $_POST['status'],
                    $_POST['notes'],
                    $_POST['flock_id']
                ]);
                $success = "Flock updated successfully!";
            } catch (Exception $e) {
                $error = "Error updating flock: " . $e->getMessage();
            }
        }
    }
}

// Get all flocks with details
$flocks = $db->query("
    SELECT f.*, b.breed_name, b.category, p.pen_name, p.pen_type
    FROM flocks f
    JOIN breeds b ON f.breed_id = b.breed_id
    JOIN pens p ON f.pen_id = p.pen_id
    ORDER BY f.created_at DESC
")->fetchAll();

// Get breeds and pens for dropdowns
$breeds = $db->query("SELECT breed_id, breed_name, category FROM breeds ORDER BY breed_name")->fetchAll();
$pens = $db->query("SELECT pen_id, pen_name, capacity, status FROM pens WHERE status='active' ORDER BY pen_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Flocks - Hilda's Poultry Farm</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/hildas_farm/assets/css/style.css">
    <style>
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000; }
        .modal.active { display: flex; }
        .modal-content { background: white; padding: 2rem; border-radius: 20px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-dark); }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 10px; font-family: 'DM Sans', sans-serif; }
        .status-badge { padding: 0.25rem 0.75rem; border-radius: 50px; font-size: 0.85rem; font-weight: 600; }
        .status-active { background: #d1fae5; color: #065f46; }
        .status-sold { background: #fee2e2; color: #991b1b; }
        .status-culled { background: #fef3c7; color: #92400e; }
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
    <!-- SIDEBAR (same as dashboard) -->
    <aside class="sidebar">
        <div class="sidebar-logo">🐓 Farm Manager</div>
        <div class="sidebar-section">Overview</div>
        <a href="/hildas_farm/admin/dashboard.php" class="sidebar-link">📊 Dashboard</a>
        <div class="sidebar-section">Flock</div>
        <a href="/hildas_farm/admin/flocks.php" class="sidebar-link active">🐔 Manage Flocks</a>
        <a href="/hildas_farm/admin/mortality.php" class="sidebar-link">📉 Mortality Log</a>
        <a href="/hildas_farm/admin/health.php" class="sidebar-link">💊 Health Records</a>
        <div class="sidebar-section">Production</div>
        <a href="/hildas_farm/admin/eggs.php" class="sidebar-link">🥚 Egg Production</a>
        <a href="/hildas_farm/admin/feed.php" class="sidebar-link">🌾 Feed Management</a>
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
            <h1>Manage Flocks</h1>
            <p>Track and manage all your poultry flocks</p>
            <button class="nav-btn" style="background:var(--green);color:white;padding:0.75rem 1.5rem;" onclick="openAddModal()">➕ Add New Flock</button>
        </div>

        <?php if (isset($success)): ?>
            <div style="background:#d1fae5;color:#065f46;padding:1rem;border-radius:10px;margin-bottom:1.5rem;"><?= $success ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div style="background:#fee2e2;color:#991b1b;padding:1rem;border-radius:10px;margin-bottom:1.5rem;"><?= $error ?></div>
        <?php endif; ?>

        <!-- Flocks Table -->
        <div class="dash-card">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Flock Name</th>
                        <th>Breed</th>
                        <th>Pen</th>
                        <th>Acquired</th>
                        <th>Initial Count</th>
                        <th>Current Count</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($flocks as $flock): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($flock['flock_name']) ?></strong></td>
                        <td><?= htmlspecialchars($flock['breed_name']) ?></td>
                        <td><?= htmlspecialchars($flock['pen_name']) ?></td>
                        <td><?= date('d M Y', strtotime($flock['date_acquired'])) ?></td>
                        <td><?= number_format($flock['initial_count']) ?></td>
                        <td><?= number_format($flock['current_count']) ?></td>
                        <td>
                            <span class="status-badge status-<?= $flock['status'] ?>">
                                <?= ucfirst($flock['status']) ?>
                            </span>
                        </td>
                        <td>
                            <button onclick='editFlock(<?= json_encode($flock) ?>)' style="background:none;border:none;color:var(--green);cursor:pointer;">✏️ Edit</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Add/Edit Modal -->
<div id="flockModal" class="modal">
    <div class="modal-content">
        <h2 id="modalTitle">Add New Flock</h2>
        <form method="POST">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="flock_id" id="flock_id">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Flock Name</label>
                    <input type="text" name="flock_name" id="flock_name" required>
                </div>
                
                <div class="form-group">
                    <label>Breed</label>
                    <select name="breed_id" id="breed_id" required>
                        <option value="">Select Breed</option>
                        <?php foreach ($breeds as $breed): ?>
                        <option value="<?= $breed['breed_id'] ?>"><?= htmlspecialchars($breed['breed_name']) ?> (<?= $breed['category'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Pen</label>
                    <select name="pen_id" id="pen_id" required>
                        <option value="">Select Pen</option>
                        <?php foreach ($pens as $pen): ?>
                        <option value="<?= $pen['pen_id'] ?>"><?= htmlspecialchars($pen['pen_name']) ?> (Capacity: <?= $pen['capacity'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Date Acquired</label>
                    <input type="date" name="date_acquired" id="date_acquired" required>
                </div>
                
                <div class="form-group">
                    <label>Initial Count</label>
                    <input type="number" name="initial_count" id="initial_count" required>
                </div>
                
                <div class="form-group" id="currentCountGroup">
                    <label>Current Count</label>
                    <input type="number" name="current_count" id="current_count">
                </div>
                
                <div class="form-group">
                    <label>Source</label>
                    <input type="text" name="source" id="source" placeholder="Hatchery/Supplier">
                </div>
                
                <div class="form-group">
                    <label>Acquisition Cost (UGX)</label>
                    <input type="number" name="acquisition_cost" id="acquisition_cost" step="0.01">
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="status" required>
                        <option value="active">Active</option>
                        <option value="sold">Sold</option>
                        <option value="culled">Culled</option>
                        <option value="transferred">Transferred</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" id="notes" rows="3"></textarea>
            </div>
            
            <div style="display:flex;gap:1rem;justify-content:flex-end;margin-top:1.5rem;">
                <button type="button" class="nav-btn nav-btn-outline" onclick="closeModal()">Cancel</button>
                <button type="submit" class="nav-btn" style="background:var(--green);color:white;">Save Flock</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Flock';
    document.getElementById('formAction').value = 'add';
    document.getElementById('flock_id').value = '';
    document.getElementById('flock_name').value = '';
    document.getElementById('breed_id').value = '';
    document.getElementById('pen_id').value = '';
    document.getElementById('date_acquired').value = '';
    document.getElementById('initial_count').value = '';
    document.getElementById('current_count').value = '';
    document.getElementById('source').value = '';
    document.getElementById('acquisition_cost').value = '';
    document.getElementById('status').value = 'active';
    document.getElementById('notes').value = '';
    document.getElementById('currentCountGroup').style.display = 'none';
    document.getElementById('flockModal').classList.add('active');
}

function editFlock(flock) {
    document.getElementById('modalTitle').textContent = 'Edit Flock';
    document.getElementById('formAction').value = 'update';
    document.getElementById('flock_id').value = flock.flock_id;
    document.getElementById('flock_name').value = flock.flock_name;
    document.getElementById('breed_id').value = flock.breed_id;
    document.getElementById('pen_id').value = flock.pen_id;
    document.getElementById('date_acquired').value = flock.date_acquired;
    document.getElementById('initial_count').value = flock.initial_count;
    document.getElementById('current_count').value = flock.current_count;
    document.getElementById('source').value = flock.source || '';
    document.getElementById('acquisition_cost').value = flock.acquisition_cost;
    document.getElementById('status').value = flock.status;
    document.getElementById('notes').value = flock.notes || '';
    document.getElementById('currentCountGroup').style.display = 'block';
    document.getElementById('flockModal').classList.add('active');
}

function closeModal() {
    document.getElementById('flockModal').classList.remove('active');
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