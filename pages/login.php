<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /hildas_farm/admin/dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../db_config.php';

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $user = loginUser($username, $password);
        if ($user) {
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];
            header('Location: /hildas_farm/admin/dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Staff Login — Hilda's Poultry Farm</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/hildas_farm/assets/css/style.css">
</head>
<body>

<div class="login-page">

  <!-- LEFT: Visual Panel -->
  <div class="login-visual">
    <div class="login-visual-icon">🐓</div>
    <h2>Welcome Back to the Farm</h2>
    <p>Log in to manage your flock, track sales, record egg production, and keep the farm running smoothly.</p>
    <div class="login-visual-stats">
      <div class="login-visual-stat">
        <span class="num">5,000+</span>
        <span class="lbl">Birds</span>
      </div>
      <div class="login-visual-stat">
        <span class="num">200+</span>
        <span class="lbl">Customers</span>
      </div>
      <div class="login-visual-stat">
        <span class="num">14</span>
        <span class="lbl">Years</span>
      </div>
    </div>
  </div>

  <!-- RIGHT: Form Panel -->
  <div class="login-form-side">
    <div class="login-box">

      <div class="login-logo">
        <span style="font-size:2rem;">🐓</span>
        <span>Hilda's Poultry Farm</span>
      </div>

      <h1>Staff Login</h1>
      <p>Sign in to access the farm management dashboard.</p>

      <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <div class="login-form">
        <form method="POST" action="">

          <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username"
                   placeholder="Enter your username"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                   autocomplete="username" required>
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <div class="password-wrap">
              <input type="password" id="password" name="password"
                     placeholder="Enter your password"
                     autocomplete="current-password" required>
              <button type="button" class="password-toggle" aria-label="Show password">👁️</button>
            </div>
          </div>

          <div class="login-extras">
            <label class="remember-me">
              <input type="checkbox" name="remember"> Remember me
            </label>
            <a href="#" class="forgot-link">Forgot password?</a>
          </div>

          <button type="submit" class="btn-login">🔐 Sign In to Dashboard</button>

        </form>

        <div class="login-divider">or</div>

        <a href="/hildas_farm/index.php" class="btn btn-outline" style="width:100%;justify-content:center;">
          ← Back to Farm Website
        </a>
      </div>

      <p style="text-align:center;margin-top:2rem;font-size:.85rem;color:var(--text-muted);">
        Not a staff member? <a href="/hildas_farm/pages/contact.php" style="color:var(--green);font-weight:600;">Contact the farm</a>
      </p>

    </div>
  </div>
</div>

<script src="/hildas_farm/assets/js/main.js"></script>
</body>
</html>
