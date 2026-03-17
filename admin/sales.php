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
            $total_amount = $_POST['quantity'] * $_POST['unit_price'];
            
            $stmt = $db->prepare("INSERT INTO sales (customer_id, sold_by, sale_date, sale_type, quantity, unit, unit_price, payment_status, amount_paid, notes, flock_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['customer_id'],
                $_SESSION['user_id'],
                $_POST['sale_date'],
                $_POST['sale_type'],
                $_POST['quantity'],
                $_POST['unit'],
                $_POST['unit_price'],
                $_POST['payment_status'],
                $_POST['amount_paid'] ?: 0,
                $_POST['notes'],
                $_POST['flock_id'] ?: null
            ]);
            
            // If selling live birds, update flock count
            if ($_POST['sale_type'] === 'live_birds' && !empty($_POST['flock_id'])) {
                $db->prepare("UPDATE flocks SET current_count = current_count - ? WHERE flock_id = ?")->execute([$_POST['quantity'], $_POST['flock_id']]);
            }
            
            $success = "Sale recorded successfully!";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
    
    if ($_POST['action'] === 'update_payment') {
        try {
            $db->prepare("UPDATE sales SET payment_status=?, amount_paid=? WHERE sale_id=?")->execute([$_POST['payment_status'], $_POST['amount_paid'], $_POST['sale_id']]);
            $success = "Payment status updated!";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get sales with customer info
$sales = $db->query("
    SELECT s.*, c.full_name as customer_name, u.full_name as sold_by_name
    FROM sales s
    JOIN customers c ON s.customer_id = c.customer_id
    JOIN users u ON s.sold_by = u.user_id
    ORDER BY s.sale_date DESC, s.created_at DESC
    LIMIT 50
")->fetchAll();

// Get customers for dropdown
$customers = $db->query("SELECT customer_id, full_name FROM customers ORDER BY full_name")->fetchAll();

// Get active flocks for live bird sales
$flocks = $db->query("
    SELECT flock_id, flock_name, current_count 
    FROM flocks 
    WHERE status='active' 
    ORDER BY flock_name
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Records - Hilda's Poultry Farm</title>
    <link rel="stylesheet" href="/hildas_farm/assets/css/style.css">
    <style>
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000; }
        .modal.active { display: flex; }
        .modal-content { background: white; padding: 2rem; border-radius: 20px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; }
        .badge-paid { background: #d1fae5; color: #065f46; }
        .badge-partial { background: #fef3c7; color: #92400e; }
        .badge-pending { background: #fee2e2; color: #991b1b; }
        .sale-type-badge { background: #f1f5f9; color: #475569; padding: 0.25rem 0.75rem; border-radius: 50px; font-size: 0.85rem; }
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
        <a href="/hildas_farm/admin/sales.php" class="sidebar-link active">💰 Sales Records</a>
        <a href="/hildas_farm/admin/customers.php" class="sidebar-link">👥 Customers</a>
        <a href="/hildas_farm/admin/expenses.php" class="sidebar-link">🧾 Expenses</a>
        <a href="/hildas_farm/index.php" class="sidebar-link">🌐 View Website</a>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Sales Records</h1>
            <p>Track all sales transactions</p>
            <button class="nav-btn" style="background:var(--green);color:white;" onclick="openAddModal()">💰 Record New Sale</button>
        </div>

        <?php if (isset($success)): ?>
            <div style="background:#d1fae5;color:#065f46;padding:1rem;border-radius:10px;margin-bottom:1.5rem;"><?= $success ?></div>
        <?php endif; ?>

        <!-- Sales Summary -->
        <div class="stats-cards" style="margin-bottom:2rem;">
            <div class="stat-card green">
                <div class="stat-card-icon">💰</div>
                <div class="stat-card-value">UGX <?= number_format(array_sum(array_column($sales, 'total_amount')), 0) ?></div>
                <div class="stat-card-label">Total Revenue</div>
            </div>
            <div class="stat-card brown">
                <div class="stat-card-icon">📊</div>
                <div class="stat-card-value"><?= count($sales) ?></div>
                <div class="stat-card-label">Total Transactions</div>
            </div>
            <div class="stat-card amber">
                <div class="stat-card-icon">⏳</div>
                <div class="stat-card-value">
                    <?php
                    $pending = array_filter($sales, function($s) {
                        return $s['payment_status'] !== 'paid';
                    });
                    echo count($pending);
                    ?>
                </div>
                <div class="stat-card-label">Pending Payments</div>
            </div>
        </div>

        <!-- Sales Table -->
        <div class="dash-card">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($sale['sale_date'])) ?></td>
                        <td><?= htmlspecialchars($sale['customer_name']) ?></td>
                        <td><span class="sale-type-badge"><?= str_replace('_', ' ', $sale['sale_type']) ?></span></td>
                        <td><?= number_format($sale['quantity']) ?> <?= $sale['unit'] ?></td>
                        <td>UGX <?= number_format($sale['unit_price']) ?></td>
                        <td style="font-weight:600;">UGX <?= number_format($sale['total_amount']) ?></td>
                        <td>UGX <?= number_format($sale['amount_paid']) ?></td>
                        <td>
                            <span class="badge badge-<?= 
                                $sale['payment_status'] === 'paid' ? 'green' : 
                                ($sale['payment_status'] === 'partial' ? 'amber' : 'red') 
                            ?>">
                                <?= ucfirst($sale['payment_status']) ?>
                            </span>
                        </td>
                        <td>
                            <button onclick='openPaymentModal(<?= json_encode($sale) ?>)' style="background:none;border:none;color:var(--green);cursor:pointer;">💰 Update Payment</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Add Sale Modal -->
<div id="saleModal" class="modal">
    <div class="modal-content">
        <h2>Record New Sale</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label>Customer</label>
                <select name="customer_id" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
                    <option value="">Select Customer</option>
                    <?php foreach ($customers as $customer): ?>
                    <option value="<?= $customer['customer_id'] ?>"><?= htmlspecialchars($customer['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Sale Date</label>
                <input type="date" name="sale_date" value="<?= date('Y-m-d') ?>" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Sale Type</label>
                <select name="sale_type" id="sale_type" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;" onchange="toggleFlockField()">
                    <option value="eggs">Eggs</option>
                    <option value="live_birds">Live Birds</option>
                    <option value="dressed_birds">Dressed Birds</option>
                    <option value="manure">Manure</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="form-group" id="flock_field" style="display:none;">
                <label>Flock (for live birds)</label>
                <select name="flock_id" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
                    <option value="">Select Flock</option>
                    <?php foreach ($flocks as $flock): ?>
                    <option value="<?= $flock['flock_id'] ?>"><?= htmlspecialchars($flock['flock_name']) ?> (Available: <?= $flock['current_count'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" name="quantity" step="0.01" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
                </div>
                
                <div class="form-group">
                    <label>Unit</label>
                    <input type="text" name="unit" value="pieces" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
                </div>
            </div>
            
            <div class="form-group">
                <label>Unit Price (UGX)</label>
                <input type="number" name="unit_price" step="100" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Payment Status</label>
                <select name="payment_status" id="payment_status" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;" onchange="toggleAmountPaid()">
                    <option value="paid">Paid</option>
                    <option value="partial">Partial</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
            
            <div class="form-group" id="amount_paid_field">
                <label>Amount Paid (UGX)</label>
                <input type="number" name="amount_paid" step="100" value="0" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" rows="2" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;"></textarea>
            </div>
            
            <div style="display:flex;gap:1rem;justify-content:flex-end;">
                <button type="button" class="nav-btn nav-btn-outline" onclick="closeModal('saleModal')">Cancel</button>
                <button type="submit" class="nav-btn" style="background:var(--green);color:white;">Record Sale</button>
            </div>
        </form>
    </div>
</div>

<!-- Update Payment Modal -->
<div id="paymentModal" class="modal">
    <div class="modal-content">
        <h2>Update Payment</h2>
        <form method="POST">
            <input type="hidden" name="action" value="update_payment">
            <input type="hidden" name="sale_id" id="payment_sale_id">
            
            <div class="form-group">
                <label>Sale Details</label>
                <div id="sale_details" style="background:#f8fafc;padding:1rem;border-radius:10px;margin-bottom:1rem;"></div>
            </div>
            
            <div class="form-group">
                <label>Payment Status</label>
                <select name="payment_status" id="payment_status_update" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
                    <option value="paid">Paid</option>
                    <option value="partial">Partial</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Amount Paid (UGX)</label>
                <input type="number" name="amount_paid" id="amount_paid_update" step="100" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div style="display:flex;gap:1rem;justify-content:flex-end;">
                <button type="button" class="nav-btn nav-btn-outline" onclick="closeModal('paymentModal')">Cancel</button>
                <button type="submit" class="nav-btn" style="background:var(--green);color:white;">Update Payment</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('saleModal').classList.add('active');
    toggleFlockField();
    toggleAmountPaid();
}

function openPaymentModal(sale) {
    document.getElementById('payment_sale_id').value = sale.sale_id;
    document.getElementById('payment_status_update').value = sale.payment_status;
    document.getElementById('amount_paid_update').value = sale.amount_paid;
    
    let details = `Customer: ${sale.customer_name}<br>
                   Date: ${sale.sale_date}<br>
                   Type: ${sale.sale_type.replace('_', ' ')}<br>
                   Total: UGX ${Number(sale.total_amount).toLocaleString()}`;
    document.getElementById('sale_details').innerHTML = details;
    
    document.getElementById('paymentModal').classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

function toggleFlockField() {
    const saleType = document.getElementById('sale_type').value;
    const flockField = document.getElementById('flock_field');
    flockField.style.display = saleType === 'live_birds' ? 'block' : 'none';
}

function toggleAmountPaid() {
    const status = document.getElementById('payment_status').value;
    const amountField = document.getElementById('amount_paid_field');
    amountField.style.display = status === 'paid' ? 'none' : 'block';
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