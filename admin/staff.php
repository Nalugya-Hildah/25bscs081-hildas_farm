<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /hildas_farm/pages/login.php');
    exit;
}

// Check if user is admin (only admins should access staff management)
if ($_SESSION['role'] !== 'admin') {
    header('Location: /hildas_farm/admin/dashboard.php');
    exit;
}

require_once __DIR__ . '/../db_config.php';
$db = getDB();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Add new staff
        if ($_POST['action'] === 'add') {
            try {
                $stmt = $db->prepare("INSERT INTO staff (full_name, job_title, phone, email, hire_date, salary, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['full_name'],
                    $_POST['job_title'],
                    $_POST['phone'],
                    $_POST['email'],
                    $_POST['hire_date'],
                    $_POST['salary'] ?: null,
                    $_POST['status'],
                    $_POST['notes']
                ]);
                $success = "Staff member added successfully!";
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
        
        // Update staff
        if ($_POST['action'] === 'update') {
            try {
                $stmt = $db->prepare("UPDATE staff SET full_name=?, job_title=?, phone=?, email=?, hire_date=?, salary=?, status=?, notes=? WHERE staff_id=?");
                $stmt->execute([
                    $_POST['full_name'],
                    $_POST['job_title'],
                    $_POST['phone'],
                    $_POST['email'],
                    $_POST['hire_date'],
                    $_POST['salary'] ?: null,
                    $_POST['status'],
                    $_POST['notes'],
                    $_POST['staff_id']
                ]);
                $success = "Staff member updated successfully!";
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
        
        // Link to user account
        if ($_POST['action'] === 'link_user') {
            try {
                $stmt = $db->prepare("UPDATE staff SET user_id=? WHERE staff_id=?");
                $stmt->execute([
                    $_POST['user_id'],
                    $_POST['staff_id']
                ]);
                $success = "Staff linked to user account successfully!";
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}

// Get all staff
$staff = $db->query("
    SELECT s.*, u.username, u.role as user_role
    FROM staff s
    LEFT JOIN users u ON s.user_id = u.user_id
    ORDER BY s.status, s.full_name
")->fetchAll();

// Get users not linked to staff for dropdown
$available_users = $db->query("
    SELECT user_id, username, full_name, role 
    FROM users 
    WHERE user_id NOT IN (SELECT user_id FROM staff WHERE user_id IS NOT NULL)
    ORDER BY full_name
")->fetchAll();

// Calculate payroll summary
$payroll_summary = $db->query("
    SELECT 
        COUNT(*) as total_staff,
        SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active_staff,
        SUM(CASE WHEN status='active' THEN salary ELSE 0 END) as monthly_payroll,
        AVG(CASE WHEN status='active' THEN salary ELSE NULL END) as avg_salary
    FROM staff
")->fetch();

// Get staff by department/job title
$job_titles = $db->query("
    SELECT job_title, COUNT(*) as count, SUM(salary) as total_salary
    FROM staff
    WHERE status='active'
    GROUP BY job_title
    ORDER BY count DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - Hilda's Poultry Farm</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/hildas_farm/assets/css/style.css">
    <style>
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000; }
        .modal.active { display: flex; }
        .modal-content { background: white; padding: 2rem; border-radius: 20px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-active { background: #d1fae5; color: #065f46; }
        .status-inactive { background: #f1f5f9; color: #475569; }
        .status-resigned { background: #fee2e2; color: #991b1b; }
        
        .staff-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .staff-card:hover { transform: translateY(-2px); }
        
        .staff-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        
        .staff-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--brown);
            margin: 0;
        }
        
        .staff-title {
            color: var(--green);
            font-weight: 600;
            margin: 0.25rem 0;
        }
        
        .staff-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0.5rem 0;
            color: var(--text-muted);
            font-size: 0.95rem;
        }
        
        .user-linked {
            background: #e0f2fe;
            color: #0369a1;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.85rem;
            display: inline-block;
        }
        
        .payroll-card {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
        }
        
        .payroll-amount {
            font-size: 2rem;
            font-weight: 700;
            color: var(--brown);
        }
        
        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            margin: 0 0.25rem;
            opacity: 0.7;
        }
        .action-btn:hover { opacity: 1; }
        .edit-btn { color: var(--green); }
        .link-btn { color: #3b82f6; }
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
        <a href="/hildas_farm/admin/expenses.php" class="sidebar-link">🧾 Expenses</a>
        
        <div class="sidebar-section">Admin</div>
        <a href="/hildas_farm/admin/staff.php" class="sidebar-link active">👷 Staff</a>
        <a href="/hildas_farm/admin/users.php" class="sidebar-link">🔐 User Accounts</a>
        <a href="/hildas_farm/index.php" class="sidebar-link">🌐 View Website</a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="page-header">
            <h1>Staff Management</h1>
            <p>Manage farm employees and personnel</p>
            <button class="nav-btn" style="background:var(--green);color:white;" onclick="openAddModal()">➕ Add New Staff</button>
        </div>

        <?php if (isset($success)): ?>
            <div style="background:#d1fae5;color:#065f46;padding:1rem;border-radius:10px;margin-bottom:1.5rem;"><?= $success ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div style="background:#fee2e2;color:#991b1b;padding:1rem;border-radius:10px;margin-bottom:1.5rem;"><?= $error ?></div>
        <?php endif; ?>

        <!-- Payroll Summary -->
        <div class="stats-cards" style="margin-bottom:2rem;">
            <div class="stat-card green">
                <div class="stat-card-icon">👥</div>
                <div class="stat-card-value"><?= $payroll_summary['total_staff'] ?></div>
                <div class="stat-card-label">Total Staff</div>
                <div class="stat-card-change">Active: <?= $payroll_summary['active_staff'] ?></div>
            </div>
            <div class="stat-card amber">
                <div class="stat-card-icon">💰</div>
                <div class="stat-card-value">UGX <?= number_format($payroll_summary['monthly_payroll'] ?: 0, 0) ?></div>
                <div class="stat-card-label">Monthly Payroll</div>
                <div class="stat-card-change">Active staff only</div>
            </div>
            <div class="stat-card brown">
                <div class="stat-card-icon">📊</div>
                <div class="stat-card-value">UGX <?= number_format($payroll_summary['avg_salary'] ?: 0, 0) ?></div>
                <div class="stat-card-label">Average Salary</div>
                <div class="stat-card-change">Per active staff</div>
            </div>
        </div>

        <!-- Staff Grid -->
        <div class="dashboard-grid" style="grid-template-columns:repeat(auto-fill, minmax(350px,1fr));">
            <?php foreach ($staff as $member): ?>
            <div class="staff-card">
                <div class="staff-header">
                    <div>
                        <h3 class="staff-name"><?= htmlspecialchars($member['full_name']) ?></h3>
                        <p class="staff-title"><?= htmlspecialchars($member['job_title'] ?: 'No title') ?></p>
                    </div>
                    <span class="status-badge status-<?= $member['status'] ?>">
                        <?= ucfirst($member['status']) ?>
                    </span>
                </div>
                
                <div class="staff-detail">
                    <span>📞</span> <?= htmlspecialchars($member['phone'] ?: 'No phone') ?>
                </div>
                
                <div class="staff-detail">
                    <span>✉️</span> <?= htmlspecialchars($member['email'] ?: 'No email') ?>
                </div>
                
                <?php if ($member['hire_date']): ?>
                <div class="staff-detail">
                    <span>📅</span> Hired: <?= date('d M Y', strtotime($member['hire_date'])) ?>
                </div>
                <?php endif; ?>
                
                <?php if ($member['salary']): ?>
                <div class="staff-detail">
                    <span>💰</span> Salary: UGX <?= number_format($member['salary'], 0) ?>/month
                </div>
                <?php endif; ?>
                
                <?php if ($member['user_id']): ?>
                <div class="staff-detail">
                    <span>🔐</span> 
                    <span class="user-linked">
                        Linked to: <?= htmlspecialchars($member['username']) ?> (<?= $member['user_role'] ?>)
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if ($member['notes']): ?>
                <div class="staff-detail" style="font-style:italic;color:var(--text-muted);">
                    📝 <?= htmlspecialchars($member['notes']) ?>
                </div>
                <?php endif; ?>
                
                <div style="display:flex;gap:0.5rem;margin-top:1rem;padding-top:1rem;border-top:1px solid #e2e8f0;">
                    <button class="action-btn edit-btn" onclick='editStaff(<?= json_encode($member) ?>)' title="Edit Staff">✏️ Edit</button>
                    <?php if (!$member['user_id'] && !empty($available_users)): ?>
                    <button class="action-btn link-btn" onclick='openLinkModal(<?= $member['staff_id'] ?>, "<?= htmlspecialchars($member['full_name']) ?>")' title="Link to User Account">🔗 Link User</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Job Title Breakdown -->
        <?php if (!empty($job_titles)): ?>
        <div class="dash-card" style="margin-top:2rem;">
            <h3>Staff by Role</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px,1fr));gap:1rem;margin-top:1rem;">
                <?php foreach ($job_titles as $job): ?>
                <div style="background:#f8fafc;padding:1rem;border-radius:12px;">
                    <div style="font-weight:600;"><?= htmlspecialchars($job['job_title'] ?: 'Unspecified') ?></div>
                    <div style="font-size:1.2rem;font-weight:700;color:var(--brown);"><?= $job['count'] ?> staff</div>
                    <div style="font-size:0.9rem;color:var(--text-muted);">UGX <?= number_format($job['total_salary'] ?: 0, 0) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>
</div>

<!-- Add/Edit Staff Modal -->
<div id="staffModal" class="modal">
    <div class="modal-content">
        <h2 id="modalTitle">Add New Staff</h2>
        <form method="POST">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="staff_id" id="staff_id">
            
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" id="full_name" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Job Title</label>
                <input type="text" name="job_title" id="job_title" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
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
                <label>Hire Date</label>
                <input type="date" name="hire_date" id="hire_date" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Monthly Salary (UGX)</label>
                <input type="number" name="salary" id="salary" step="1000" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Status</label>
                <select name="status" id="status" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="resigned">Resigned</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" id="notes" rows="3" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;"></textarea>
            </div>
            
            <div style="display:flex;gap:1rem;justify-content:flex-end;margin-top:1.5rem;">
                <button type="button" class="nav-btn nav-btn-outline" onclick="closeModal()">Cancel</button>
                <button type="submit" class="nav-btn" style="background:var(--green);color:white;">Save Staff</button>
            </div>
        </form>
    </div>
</div>

<!-- Link User Modal -->
<div id="linkModal" class="modal">
    <div class="modal-content">
        <h2>Link Staff to User Account</h2>
        <form method="POST">
            <input type="hidden" name="action" value="link_user">
            <input type="hidden" name="staff_id" id="link_staff_id">
            
            <p id="linkStaffName" style="margin-bottom:1.5rem;"></p>
            
            <div class="form-group">
                <label>Select User Account</label>
                <select name="user_id" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
                    <option value="">Choose user...</option>
                    <?php foreach ($available_users as $user): ?>
                    <option value="<?= $user['user_id'] ?>">
                        <?= htmlspecialchars($user['full_name']) ?> (<?= $user['username'] ?> - <?= $user['role'] ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="display:flex;gap:1rem;justify-content:flex-end;margin-top:1.5rem;">
                <button type="button" class="nav-btn nav-btn-outline" onclick="closeModal('linkModal')">Cancel</button>
                <button type="submit" class="nav-btn" style="background:var(--green);color:white;">Link Account</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Staff';
    document.getElementById('formAction').value = 'add';
    document.getElementById('staff_id').value = '';
    document.getElementById('full_name').value = '';
    document.getElementById('job_title').value = '';
    document.getElementById('phone').value = '';
    document.getElementById('email').value = '';
    document.getElementById('hire_date').value = '';
    document.getElementById('salary').value = '';
    document.getElementById('status').value = 'active';
    document.getElementById('notes').value = '';
    document.getElementById('staffModal').classList.add('active');
}

function editStaff(staff) {
    document.getElementById('modalTitle').textContent = 'Edit Staff';
    document.getElementById('formAction').value = 'update';
    document.getElementById('staff_id').value = staff.staff_id;
    document.getElementById('full_name').value = staff.full_name;
    document.getElementById('job_title').value = staff.job_title || '';
    document.getElementById('phone').value = staff.phone || '';
    document.getElementById('email').value = staff.email || '';
    document.getElementById('hire_date').value = staff.hire_date || '';
    document.getElementById('salary').value = staff.salary || '';
    document.getElementById('status').value = staff.status;
    document.getElementById('notes').value = staff.notes || '';
    document.getElementById('staffModal').classList.add('active');
}

function openLinkModal(staffId, staffName) {
    document.getElementById('link_staff_id').value = staffId;
    document.getElementById('linkStaffName').innerHTML = `<strong>${staffName}</strong><br>Select a user account to link with this staff member.`;
    document.getElementById('linkModal').classList.add('active');
}

function closeModal(modalId = 'staffModal') {
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