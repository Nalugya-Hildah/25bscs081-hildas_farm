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
        // Add new expense
        if ($_POST['action'] === 'add') {
            try {
                $stmt = $db->prepare("INSERT INTO expenses (expense_date, category, description, amount, paid_to, recorded_by, receipt_no, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['expense_date'],
                    $_POST['category'],
                    $_POST['description'],
                    $_POST['amount'],
                    $_POST['paid_to'],
                    $_SESSION['user_id'],
                    $_POST['receipt_no'],
                    $_POST['notes']
                ]);
                $success = "Expense recorded successfully!";
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
        
        // Update expense
        if ($_POST['action'] === 'update') {
            try {
                $stmt = $db->prepare("UPDATE expenses SET expense_date=?, category=?, description=?, amount=?, paid_to=?, receipt_no=?, notes=? WHERE expense_id=?");
                $stmt->execute([
                    $_POST['expense_date'],
                    $_POST['category'],
                    $_POST['description'],
                    $_POST['amount'],
                    $_POST['paid_to'],
                    $_POST['receipt_no'],
                    $_POST['notes'],
                    $_POST['expense_id']
                ]);
                $success = "Expense updated successfully!";
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
        
        // Delete expense
        if ($_POST['action'] === 'delete') {
            try {
                $stmt = $db->prepare("DELETE FROM expenses WHERE expense_id=?");
                $stmt->execute([$_POST['expense_id']]);
                $success = "Expense deleted successfully!";
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}

// Get filter parameters
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Build query with filters
$query = "
    SELECT e.*, u.full_name as recorded_by_name
    FROM expenses e
    JOIN users u ON e.recorded_by = u.user_id
    WHERE 1=1
";
$params = [];

if ($month) {
    $query .= " AND DATE_FORMAT(e.expense_date, '%Y-%m') = ?";
    $params[] = $month;
}

if ($category_filter) {
    $query .= " AND e.category = ?";
    $params[] = $category_filter;
}

$query .= " ORDER BY e.expense_date DESC, e.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$expenses = $stmt->fetchAll();

// Get expense summary by category for the selected month
$summary = $db->prepare("
    SELECT 
        category,
        COUNT(*) as count,
        SUM(amount) as total
    FROM expenses
    WHERE DATE_FORMAT(expense_date, '%Y-%m') = ?
    GROUP BY category
    ORDER BY total DESC
");
$summary->execute([$month]);
$category_summary = $summary->fetchAll();

// Get total for the month
$total_stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE DATE_FORMAT(expense_date, '%Y-%m') = ?");
$total_stmt->execute([$month]);
$month_total = $total_stmt->fetch()['total'];

// Get previous months for dropdown
$months = $db->query("
    SELECT DISTINCT DATE_FORMAT(expense_date, '%Y-%m') as month,
           DATE_FORMAT(expense_date, '%M %Y') as month_name
    FROM expenses
    ORDER BY month DESC
")->fetchAll();

// Get all categories for filter
$categories = $db->query("SELECT DISTINCT category FROM expenses ORDER BY category")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expenses - Hilda's Poultry Farm</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/hildas_farm/assets/css/style.css">
    <style>
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000; }
        .modal.active { display: flex; }
        .modal-content { background: white; padding: 2rem; border-radius: 20px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; }
        .expense-card { background: white; border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .category-badge { 
            padding: 0.25rem 0.75rem; 
            border-radius: 50px; 
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        .category-utilities { background: #dbeafe; color: #1e40af; }
        .category-labor { background: #fef3c7; color: #92400e; }
        .category-equipment { background: #e0e7ff; color: #3730a3; }
        .category-maintenance { background: #fae8ff; color: #86198f; }
        .category-transport { background: #dcfce7; color: #166534; }
        .category-other { background: #f1f5f9; color: #475569; }
        
        .filter-bar {
            display: flex;
            gap: 1rem;
            align-items: center;
            background: white;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .summary-chart {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .summary-item {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .summary-item .label {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }
        
        .summary-item .value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--brown);
        }
        
        .summary-item .count {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }
        
        .delete-btn {
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
            font-size: 1.2rem;
            opacity: 0.7;
        }
        .delete-btn:hover { opacity: 1; }
        
        .edit-btn {
            background: none;
            border: none;
            color: var(--green);
            cursor: pointer;
            margin-right: 0.5rem;
        }
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
        <a href="/hildas_farm/admin/feed.php" class="sidebar-link">🌾 Feed Management</a>
        
        <div class="sidebar-section">Business</div>
        <a href="/hildas_farm/admin/sales.php" class="sidebar-link">💰 Sales Records</a>
        <a href="/hildas_farm/admin/customers.php" class="sidebar-link">👥 Customers</a>
        <a href="/hildas_farm/admin/expenses.php" class="sidebar-link active">🧾 Expenses</a>
        
        <div class="sidebar-section">Admin</div>
        <a href="/hildas_farm/admin/staff.php" class="sidebar-link">👷 Staff</a>
        <a href="/hildas_farm/admin/users.php" class="sidebar-link">🔐 User Accounts</a>
        <a href="/hildas_farm/index.php" class="sidebar-link">🌐 View Website</a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="page-header">
            <h1>Expense Tracking</h1>
            <p>Monitor and manage all farm expenses</p>
            <button class="nav-btn" style="background:var(--green);color:white;" onclick="openAddModal()">➕ Record New Expense</button>
        </div>

        <?php if (isset($success)): ?>
            <div style="background:#d1fae5;color:#065f46;padding:1rem;border-radius:10px;margin-bottom:1.5rem;"><?= $success ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div style="background:#fee2e2;color:#991b1b;padding:1rem;border-radius:10px;margin-bottom:1.5rem;"><?= $error ?></div>
        <?php endif; ?>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="GET" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:center;width:100%;">
                <div style="display:flex;align-items:center;gap:0.5rem;">
                    <label style="font-weight:500;">Month:</label>
                    <select name="month" onchange="this.form.submit()" style="padding:0.5rem;border-radius:8px;border:2px solid #e2e8f0;">
                        <?php if (empty($months)): ?>
                            <option value="<?= date('Y-m') ?>"><?= date('F Y') ?></option>
                        <?php else: ?>
                            <?php foreach ($months as $m): ?>
                            <option value="<?= $m['month'] ?>" <?= $m['month'] == $month ? 'selected' : '' ?>>
                                <?= $m['month_name'] ?>
                            </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div style="display:flex;align-items:center;gap:0.5rem;">
                    <label style="font-weight:500;">Category:</label>
                    <select name="category" onchange="this.form.submit()" style="padding:0.5rem;border-radius:8px;border:2px solid #e2e8f0;">
                        <option value="">All Categories</option>
                        <option value="utilities" <?= $category_filter == 'utilities' ? 'selected' : '' ?>>Utilities</option>
                        <option value="labor" <?= $category_filter == 'labor' ? 'selected' : '' ?>>Labor</option>
                        <option value="equipment" <?= $category_filter == 'equipment' ? 'selected' : '' ?>>Equipment</option>
                        <option value="maintenance" <?= $category_filter == 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                        <option value="transport" <?= $category_filter == 'transport' ? 'selected' : '' ?>>Transport</option>
                        <option value="other" <?= $category_filter == 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                
                <?php if ($month || $category_filter): ?>
                <a href="/hildas_farm/admin/expenses.php" style="color:var(--green);text-decoration:none;">Clear Filters</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Monthly Summary -->
        <div class="summary-chart">
            <div class="summary-item">
                <div class="label">Total Expenses</div>
                <div class="value">UGX <?= number_format($month_total, 0) ?></div>
                <div class="count"><?= count($expenses) ?> transactions</div>
            </div>
            
            <?php 
            $top_category = !empty($category_summary) ? $category_summary[0] : null;
            if ($top_category): 
            ?>
            <div class="summary-item">
                <div class="label">Top Category</div>
                <div class="value" style="text-transform:capitalize;"><?= $top_category['category'] ?></div>
                <div class="count">UGX <?= number_format($top_category['total'], 0) ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Category Breakdown -->
        <?php if (!empty($category_summary)): ?>
        <div class="dash-card" style="margin-bottom:2rem;">
            <h3>Expense Breakdown by Category</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(150px,1fr));gap:1rem;margin-top:1rem;">
                <?php foreach ($category_summary as $cat): ?>
                <div style="background:#f8fafc;padding:1rem;border-radius:12px;">
                    <span class="category-badge category-<?= $cat['category'] ?>">
                        <?= ucfirst($cat['category']) ?>
                    </span>
                    <div style="font-size:1.2rem;font-weight:600;margin-top:0.5rem;">
                        UGX <?= number_format($cat['total'], 0) ?>
                    </div>
                    <div style="font-size:0.85rem;color:var(--text-muted);">
                        <?= $cat['count'] ?> transaction(s)
                    </div>
                    <div style="font-size:0.85rem;color:var(--text-muted);">
                        <?= round(($cat['total'] / $month_total) * 100, 1) ?>% of total
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Expenses Table -->
        <div class="dash-card">
            <h3>Expense Details</h3>
            <?php if (empty($expenses)): ?>
                <p style="text-align:center;padding:3rem;color:var(--text-muted);">No expenses found for the selected period.</p>
            <?php else: ?>
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Paid To</th>
                        <th>Receipt No.</th>
                        <th>Recorded By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($expense['expense_date'])) ?></td>
                        <td>
                            <span class="category-badge category-<?= $expense['category'] ?>">
                                <?= ucfirst($expense['category']) ?>
                            </span>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($expense['description']) ?></strong>
                            <?php if ($expense['notes']): ?>
                            <br><small style="color:var(--text-muted);"><?= htmlspecialchars($expense['notes']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td style="font-weight:600;color:#dc2626;">UGX <?= number_format($expense['amount'], 0) ?></td>
                        <td><?= htmlspecialchars($expense['paid_to'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($expense['receipt_no'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($expense['recorded_by_name']) ?></td>
                        <td>
                            <button class="edit-btn" onclick='editExpense(<?= json_encode($expense) ?>)'>✏️</button>
                            <button class="delete-btn" onclick="deleteExpense(<?= $expense['expense_id'] ?>, '<?= htmlspecialchars($expense['description']) ?>')">🗑️</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot style="background:#f8fafc;font-weight:600;">
                    <tr>
                        <td colspan="3" style="text-align:right;">Total:</td>
                        <td>UGX <?= number_format(array_sum(array_column($expenses, 'amount')), 0) ?></td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Add Expense Modal -->
<div id="expenseModal" class="modal">
    <div class="modal-content">
        <h2 id="modalTitle">Record New Expense</h2>
        <form method="POST">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="expense_id" id="expense_id">
            
            <div class="form-group">
                <label>Expense Date</label>
                <input type="date" name="expense_date" id="expense_date" value="<?= date('Y-m-d') ?>" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Category</label>
                <select name="category" id="category" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
                    <option value="">Select Category</option>
                    <option value="utilities">Utilities (Electricity, Water)</option>
                    <option value="labor">Labor (Wages, Salaries)</option>
                    <option value="equipment">Equipment (Purchase, Repair)</option>
                    <option value="maintenance">Maintenance (Building, Pens)</option>
                    <option value="transport">Transport (Fuel, Deliveries)</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" id="description" required placeholder="e.g., Electricity bill, Weekly wages" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Amount (UGX)</label>
                <input type="number" name="amount" id="amount" step="100" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Paid To</label>
                <input type="text" name="paid_to" id="paid_to" placeholder="Supplier, employee, company name" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Receipt Number</label>
                <input type="text" name="receipt_no" id="receipt_no" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Additional Notes</label>
                <textarea name="notes" id="notes" rows="3" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;"></textarea>
            </div>
            
            <div style="display:flex;gap:1rem;justify-content:flex-end;margin-top:1.5rem;">
                <button type="button" class="nav-btn nav-btn-outline" onclick="closeModal()">Cancel</button>
                <button type="submit" class="nav-btn" style="background:var(--green);color:white;">Save Expense</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content" style="max-width:400px;">
        <h2>Confirm Delete</h2>
        <p id="deleteMessage" style="margin:1.5rem 0;">Are you sure you want to delete this expense?</p>
        
        <form method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="expense_id" id="delete_expense_id">
            
            <div style="display:flex;gap:1rem;justify-content:flex-end;">
                <button type="button" class="nav-btn nav-btn-outline" onclick="closeModal('deleteModal')">Cancel</button>
                <button type="submit" class="nav-btn" style="background:#dc2626;color:white;">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Record New Expense';
    document.getElementById('formAction').value = 'add';
    document.getElementById('expense_id').value = '';
    document.getElementById('expense_date').value = '<?= date('Y-m-d') ?>';
    document.getElementById('category').value = '';
    document.getElementById('description').value = '';
    document.getElementById('amount').value = '';
    document.getElementById('paid_to').value = '';
    document.getElementById('receipt_no').value = '';
    document.getElementById('notes').value = '';
    document.getElementById('expenseModal').classList.add('active');
}

function editExpense(expense) {
    document.getElementById('modalTitle').textContent = 'Edit Expense';
    document.getElementById('formAction').value = 'update';
    document.getElementById('expense_id').value = expense.expense_id;
    document.getElementById('expense_date').value = expense.expense_date;
    document.getElementById('category').value = expense.category;
    document.getElementById('description').value = expense.description;
    document.getElementById('amount').value = expense.amount;
    document.getElementById('paid_to').value = expense.paid_to || '';
    document.getElementById('receipt_no').value = expense.receipt_no || '';
    document.getElementById('notes').value = expense.notes || '';
    document.getElementById('expenseModal').classList.add('active');
}

function deleteExpense(expenseId, description) {
    document.getElementById('delete_expense_id').value = expenseId;
    document.getElementById('deleteMessage').textContent = `Are you sure you want to delete "${description}"? This action cannot be undone.`;
    document.getElementById('deleteModal').classList.add('active');
}

function closeModal(modalId = 'expenseModal') {
    document.getElementById(modalId).classList.remove('active');
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