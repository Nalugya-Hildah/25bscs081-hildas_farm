<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /hildas_farm/pages/login.php');
    exit;
}

// Check if user is admin (only admins should manage users)
if ($_SESSION['role'] !== 'admin') {
    header('Location: /hildas_farm/admin/dashboard.php');
    exit;
}

require_once __DIR__ . '/../db_config.php';
$db = getDB();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Add new user
        if ($_POST['action'] === 'add') {
            try {
                // Check if username exists
                $check = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                $check->execute([$_POST['username']]);
                if ($check->fetchColumn() > 0) {
                    $error = "Username already exists!";
                } else {
                    // Hash password
                    $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
                    
                    $stmt = $db->prepare("INSERT INTO users (full_name, username, password_hash, role, email, phone, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['full_name'],
                        $_POST['username'],
                        $password_hash,
                        $_POST['role'],
                        $_POST['email'],
                        $_POST['phone'],
                        isset($_POST['is_active']) ? 1 : 0
                    ]);
                    $success = "User account created successfully!";
                }
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
        
        // Update user
        if ($_POST['action'] === 'update') {
            try {
                // Don't allow changing own role/status if last admin
                if ($_POST['user_id'] == $_SESSION['user_id'] && 
                    ($_POST['role'] !== 'admin' || $_POST['is_active'] == 0)) {
                    
                    // Check if this is the last admin
                    $admin_count = $db->query("SELECT COUNT(*) FROM users WHERE role='admin' AND is_active=1")->fetchColumn();
                    if ($admin_count <= 1) {
                        $error = "Cannot change your own admin status as you are the last active admin!";
                        throw new Exception("Last admin protection");
                    }
                }
                
                $sql = "UPDATE users SET full_name=?, username=?, role=?, email=?, phone=?, is_active=? WHERE user_id=?";
                $params = [
                    $_POST['full_name'],
                    $_POST['username'],
                    $_POST['role'],
                    $_POST['email'],
                    $_POST['phone'],
                    isset($_POST['is_active']) ? 1 : 0,
                    $_POST['user_id']
                ];
                
                // If password is provided, update it
                if (!empty($_POST['password'])) {
                    $sql = "UPDATE users SET full_name=?, username=?, password_hash=?, role=?, email=?, phone=?, is_active=? WHERE user_id=?";
                    $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
                    array_splice($params, 2, 0, $password_hash);
                }
                
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $success = "User account updated successfully!";
            } catch (Exception $e) {
                if (!isset($error)) {
                    $error = "Error: " . $e->getMessage();
                }
            }
        }
        
        // Reset password
        if ($_POST['action'] === 'reset_password') {
            try {
                $password_hash = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
                $stmt = $db->prepare("UPDATE users SET password_hash=? WHERE user_id=?");
                $stmt->execute([$password_hash, $_POST['user_id']]);
                $success = "Password reset successfully!";
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}

// Get all users
$users = $db->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM staff WHERE user_id = u.user_id) as has_staff_record
    FROM users u
    ORDER BY u.role, u.full_name
")->fetchAll();

// Get user activity stats
$user_stats = $db->query("
    SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN role='admin' AND is_active=1 THEN 1 ELSE 0 END) as active_admins,
        SUM(CASE WHEN role='manager' AND is_active=1 THEN 1 ELSE 0 END) as active_managers,
        SUM(CASE WHEN role='staff' AND is_active=1 THEN 1 ELSE 0 END) as active_staff,
        SUM(CASE WHEN is_active=0 THEN 1 ELSE 0 END) as inactive_users
    FROM users
")->fetch();

// Get recent user activity (last logins would need a login_log table - this is placeholder)
// For now, just show when they were created
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Accounts - Hilda's Poultry Farm</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/hildas_farm/assets/css/style.css">
    <style>
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000; }
        .modal.active { display: flex; }
        .modal-content { background: white; padding: 2rem; border-radius: 20px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; }
        
        .role-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .role-admin { background: #fee2e2; color: #991b1b; }
        .role-manager { background: #fef3c7; color: #92400e; }
        .role-staff { background: #dbeafe; color: #1e40af; }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.85rem;
        }
        .status-active { background: #d1fae5; color: #065f46; }
        .status-inactive { background: #f1f5f9; color: #475569; }
        
        .user-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: all 0.2s;
        }
        .user-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
        
        .user-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .user-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--brown);
            margin: 0;
        }
        
        .user-username {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin: 0.25rem 0;
        }
        
        .user-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0.5rem 0;
            color: var(--text-muted);
        }
        
        .staff-linked {
            background: #e0f2fe;
            color: #0369a1;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.85rem;
            display: inline-block;
        }
        
        .password-input-group {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        .password-input-group input {
            flex: 1;
        }
        .toggle-password {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
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
        <a href="/hildas_farm/admin/expenses.php" class="sidebar-link">🧾 Expenses</a>
        
        <div class="sidebar-section">Admin</div>
        <a href="/hildas_farm/admin/staff.php" class="sidebar-link">👷 Staff</a>
        <a href="/hildas_farm/admin/users.php" class="sidebar-link active">🔐 User Accounts</a>
        <a href="/hildas_farm/index.php" class="sidebar-link">🌐 View Website</a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="page-header">
            <h1>User Accounts</h1>
            <p>Manage system users and permissions</p>
            <button class="nav-btn" style="background:var(--green);color:white;" onclick="openAddModal()">➕ Create New User</button>
        </div>

        <?php if (isset($success)): ?>
            <div style="background:#d1fae5;color:#065f46;padding:1rem;border-radius:10px;margin-bottom:1.5rem;"><?= $success ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div style="background:#fee2e2;color:#991b1b;padding:1rem;border-radius:10px;margin-bottom:1.5rem;"><?= $error ?></div>
        <?php endif; ?>

        <!-- User Stats -->
        <div class="stats-cards" style="margin-bottom:2rem;">
            <div class="stat-card green">
                <div class="stat-card-icon">👥</div>
                <div class="stat-card-value"><?= $user_stats['total_users'] ?></div>
                <div class="stat-card-label">Total Users</div>
            </div>
            <div class="stat-card red">
                <div class="stat-card-icon">👑</div>
                <div class="stat-card-value"><?= $user_stats['active_admins'] ?></div>
                <div class="stat-card-label">Active Admins</div>
            </div>
            <div class="stat-card amber">
                <div class="stat-card-icon">📋</div>
                <div class="stat-card-value"><?= $user_stats['active_managers'] ?></div>
                <div class="stat-card-label">Active Managers</div>
            </div>
            <div class="stat-card brown">
                <div class="stat-card-icon">👷</div>
                <div class="stat-card-value"><?= $user_stats['active_staff'] ?></div>
                <div class="stat-card-label">Active Staff</div>
            </div>
        </div>

        <!-- Users Grid -->
        <div class="dashboard-grid" style="grid-template-columns:repeat(auto-fill, minmax(350px,1fr));">
            <?php foreach ($users as $user): ?>
            <div class="user-card">
                <div class="user-header">
                    <div>
                        <h3 class="user-name"><?= htmlspecialchars($user['full_name']) ?></h3>
                        <p class="user-username">@<?= htmlspecialchars($user['username']) ?></p>
                    </div>
                    <span class="role-badge role-<?= $user['role'] ?>">
                        <?= ucfirst($user['role']) ?>
                    </span>
                </div>
                
                <div class="user-detail">
                    <span>📧</span> <?= htmlspecialchars($user['email'] ?: 'No email') ?>
                </div>
                
                <div class="user-detail">
                    <span>📞</span> <?= htmlspecialchars($user['phone'] ?: 'No phone') ?>
                </div>
                
                <div class="user-detail">
                    <span>📅</span> Created: <?= date('d M Y', strtotime($user['created_at'])) ?>
                </div>
                
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1rem;">
                    <span class="status-badge status-<?= $user['is_active'] ? 'active' : 'inactive' ?>">
                        <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                    
                    <?php if ($user['has_staff_record']): ?>
                    <span class="staff-linked">👤 Linked to Staff</span>
                    <?php endif; ?>
                </div>
                
                <div style="display:flex;gap:0.5rem;margin-top:1rem;padding-top:1rem;border-top:1px solid #e2e8f0;">
                    <button class="action-btn edit-btn" onclick='editUser(<?= json_encode($user) ?>)' title="Edit User">✏️ Edit</button>
                    <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                    <button class="action-btn link-btn" onclick='openPasswordModal(<?= $user['user_id'] ?>, "<?= htmlspecialchars($user['username']) ?>")' title="Reset Password">🔑 Reset Password</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<!-- Add/Edit User Modal -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <h2 id="modalTitle">Create New User</h2>
        <form method="POST">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="user_id" id="user_id">
            
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" id="full_name" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" id="username" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group" id="password_field">
                <label>Password</label>
                <div class="password-input-group">
                    <input type="password" name="password" id="password" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">👁️</button>
                </div>
                <small style="color:var(--text-muted);">Leave blank to keep current password when editing</small>
            </div>
            
            <div class="form-group">
                <label>Role</label>
                <select name="role" id="role" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
                    <option value="staff">Staff</option>
                    <option value="manager">Manager</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="email" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" id="phone" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:0.5rem;">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked>
                    Account is active
                </label>
            </div>
            
            <div style="display:flex;gap:1rem;justify-content:flex-end;margin-top:1.5rem;">
                <button type="button" class="nav-btn nav-btn-outline" onclick="closeModal()">Cancel</button>
                <button type="submit" class="nav-btn" style="background:var(--green);color:white;">Save User</button>
            </div>
        </form>
    </div>
</div>

<!-- Reset Password Modal -->
<div id="passwordModal" class="modal">
    <div class="modal-content">
        <h2>Reset Password</h2>
        <form method="POST">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="user_id" id="reset_user_id">
            
            <p id="resetUsername" style="margin-bottom:1.5rem;"></p>
            
            <div class="form-group">
                <label>New Password</label>
                <div class="password-input-group">
                    <input type="password" name="new_password" id="new_password" required style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
                    <button type="button" class="toggle-password" onclick="togglePassword('new_password')">👁️</button>
                </div>
            </div>
            
            <div class="form-group">
                <label>Confirm Password</label>
                <div class="password-input-group">
                    <input type="password" id="confirm_password" style="width:100%;padding:0.75rem;border-radius:10px;border:2px solid #e2e8f0;">
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">👁️</button>
                </div>
            </div>
            
            <div style="display:flex;gap:1rem;justify-content:flex-end;margin-top:1.5rem;">
                <button type="button" class="nav-btn nav-btn-outline" onclick="closeModal('passwordModal')">Cancel</button>
                <button type="submit" class="nav-btn" style="background:var(--green);color:white;" onclick="return validatePassword()">Reset Password</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Create New User';
    document.getElementById('formAction').value = 'add';
    document.getElementById('user_id').value = '';
    document.getElementById('full_name').value = '';
    document.getElementById('username').value = '';
    document.getElementById('password').value = '';
    document.getElementById('password').required = true;
    document.getElementById('role').value = 'staff';
    document.getElementById('email').value = '';
    document.getElementById('phone').value = '';
    document.getElementById('is_active').checked = true;
    document.getElementById('password_field').style.display = 'block';
    document.getElementById('userModal').classList.add('active');
}

function editUser(user) {
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('formAction').value = 'update';
    document.getElementById('user_id').value = user.user_id;
    document.getElementById('full_name').value = user.full_name;
    document.getElementById('username').value = user.username;
    document.getElementById('password').value = '';
    document.getElementById('password').required = false;
    document.getElementById('role').value = user.role;
    document.getElementById('email').value = user.email || '';
    document.getElementById('phone').value = user.phone || '';
    document.getElementById('is_active').checked = user.is_active == 1;
    document.getElementById('password_field').style.display = 'block';
    document.getElementById('userModal').classList.add('active');
}

function openPasswordModal(userId, username) {
    document.getElementById('reset_user_id').value = userId;
    document.getElementById('resetUsername').innerHTML = `Reset password for <strong>${username}</strong>`;
    document.getElementById('new_password').value = '';
    document.getElementById('confirm_password').value = '';
    document.getElementById('passwordModal').classList.add('active');
}

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    field.type = field.type === 'password' ? 'text' : 'password';
}

function validatePassword() {
    const newPass = document.getElementById('new_password').value;
    const confirmPass = document.getElementById('confirm_password').value;
    
    if (newPass !== confirmPass) {
        alert('Passwords do not match!');
        return false;
    }
    
    if (newPass.length < 6) {
        alert('Password must be at least 6 characters long!');
        return false;
    }
    
    return true;
}

function closeModal(modalId = 'userModal') {
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