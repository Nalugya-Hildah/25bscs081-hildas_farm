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
            $stmt = $db->prepare("INSERT INTO customers (full_name, phone, email, address, customer_type, notes) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['full_name'],
                $_POST['phone'],
                $_POST['email'],
                $_POST['address'],
                $_POST['customer_type'],
                $_POST['notes']
            ]);
            $success = "Customer added successfully!";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
    
    if ($_POST['action'] === 'update') {
        try {
            $stmt = $db->prepare("UPDATE customers SET full_name=?, phone=?, email=?, address=?, customer_type=?, notes=? WHERE customer_id=?");
            $stmt->execute([
                $_POST['full_name'],
                $_POST['phone'],
                $_POST['email'],
                $_POST['address'],
                $_POST['customer_type'],
                $_POST['notes'],
                $_POST['customer_id']
            ]);
            $success = "Customer updated successfully!";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get all customers
$customers = $db->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM sales WHERE customer_id = c.customer_id) as total_purchases,
           (SELECT SUM(total_amount) FROM sales WHERE customer_id = c.customer_id) as total_spent
    FROM customers c
    ORDER BY c.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Hilda's Poultry Farm</title>
    <link rel="stylesheet" href="/hildas_farm/assets/css/style.css">
    <style>
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000; }
        .modal.active { display: flex; }
        .modal-content { background: white; padding: 2rem; border-radius: 20px; max-width: 500px; width: 90%; }
        .customer-type { background: #f1f5f9; padding: 0.25rem 0.75rem; border-radius: 50px; font-size: 0.85rem; }
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
        <a href="/hildas_farm/admin/eggs.php" class="sidebar-link">🥚 Egg Production</a>
        <a href="/hildas_farm/admin/feed.php" class="sidebar-link">🌾 Feed Management</a>
        <a href="/hildas_farm/admin/sales.php" class="sidebar-link">💰 Sales Records</a>
        <a href="/hildas_farm/admin/customers.php" class="sidebar-link active">👥 Customers</a>
        <a href="/hildas_farm/admin/expenses.php" class="sidebar-link">🧾 Expenses</a>
        <a href="/hildas_farm/index.php" class="sidebar-link">🌐 View Website</a>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Customers</h1>
            <p>Manage your customer relationships</p>
            <button class="nav-btn" style="background:var(--green);color:white;" onclick="openAddModal()">➕ Add New Customer</button>
        </div>

        <?php if (isset($success)): ?>
            <div style="background:#d1fae5;color:#065f46;padding:1rem;border-radius:10px;margin-bottom:1.5rem;"><?= $success ?></div>
        <?php endif; ?>

        <!-- Customers Grid -->
        <div class="dashboard-grid" style="grid-template-columns:repeat(auto-fill, minmax(300px,1fr));">
            <?php foreach ($customers as $customer): ?>
            <div class="dash-card" style="padding:1.5rem;">
                <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:1rem;">
                    <h3 style="margin:0;"><?= htmlspecialchars($customer['full_name']) ?></h3>
                    <span class="customer-type"><?= ucfirst($customer['customer_type']) ?></span>
                </div>
                
                <?php if ($customer['phone']): ?>
                <p style="margin:0.5rem 0;">📞 <?= htmlspecialchars($customer['phone']) ?></p>
                <?php endif; ?>
                
                <?php if ($customer['email']): ?>
                <p style="margin:0.5rem 0;">✉️ <?= htmlspecialchars($customer['email']) ?></p>
                <?php endif; ?>
                
                <?php if ($customer['address']): ?>
                <p style="margin:0.5rem 0;color:var(--text-muted);">📍 <?= htmlspecialchars($customer['address']) ?></p>
                <?php endif; ?>
                
                <hr style="margin:1rem 0;border-color:#e2e8f0;">
                
                <div style="display:flex;justify-content:space-between;margin-bottom:1rem;">
                    <div>
                        <div style="font-size:0.85rem;color:var(--text-muted);">Purchases</div>
                        <div style="font-weight:600;"><?= $customer['total_purchases'] ?></div>
                    </div>
                    <div>
                        <div style="font-size:0.85rem;color:var(--text-muted);">Total Spent</div>
                        <div style="font-weight:600;">UGX <?= number_format($customer['total_spent'] ?: 0) ?></div>
                    </div>
                </div>
                
                <button onclick='editCustomer(<?= json_encode($customer) ?>)' style="background:var(--green);color:white;border:none;padding:0.5rem 1rem;border-radius:50px;cursor:pointer;width:100%;">Edit Customer</button>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<!-- Add/Edit Modal -->
<div id="customerModal" class="modal">
    <div class="modal-content">
        <h2 id="modalTitle">Add New Customer</h2>
        <form method="POST">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="customer_id" id="customer_id">
            
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" id="full_name" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" id="phone" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="email" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" id="address" rows="2" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;"></textarea>
            </div>
            
            <div class="form-group">
                <label>Customer Type</label>
                <select name="customer_type" id="customer_type" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
                    <option value="individual">Individual</option>
                    <option value="wholesale">Wholesale</option>
                    <option value="retailer">Retailer</option>
                    <option value="restaurant">Restaurant</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" id="notes" rows="2" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;"></textarea>
            </div>
            
            <div style="display:flex;gap:1rem;justify-content:flex-end;">
                <button type="button" class="nav-btn nav-btn-outline" onclick="closeModal()">Cancel</button>
                <button type="submit" class="nav-btn" style="background:var(--green);color:white;">Save Customer</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Customer';
    document.getElementById('formAction').value = 'add';
    document.getElementById('customer_id').value = '';
    document.getElementById('full_name').value = '';
    document.getElementById('phone').value = '';
    document.getElementById('email').value = '';
    document.getElementById('address').value = '';
    document.getElementById('customer_type').value = 'individual';
    document.getElementById('notes').value = '';
    document.getElementById('customerModal').classList.add('active');
}

function editCustomer(customer) {
    document.getElementById('modalTitle').textContent = 'Edit Customer';
    document.getElementById('formAction').value = 'update';
    document.getElementById('customer_id').value = customer.customer_id;
    document.getElementById('full_name').value = customer.full_name;
    document.getElementById('phone').value = customer.phone || '';
    document.getElementById('email').value = customer.email || '';
    document.getElementById('address').value = customer.address || '';
    document.getElementById('customer_type').value = customer.customer_type;
    document.getElementById('notes').value = customer.notes || '';
    document.getElementById('customerModal').classList.add('active');
}

function closeModal() {
    document.getElementById('customerModal').classList.remove('active');
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