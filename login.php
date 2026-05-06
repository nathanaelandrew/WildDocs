<?php
// login.php — WildDocuments Login (User & Admin)
session_start();

// if (isset($_SESSION['user_id'])) {
//     header('Location: user_dashboard.php'); exit;
// }
// if (isset($_SESSION['admin_id'])) {
//     header('Location: admin_dashboard.php'); exit;
// }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO: include 'includes/db.php'; $pdo = getDB();
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $role     = $_POST['role']          ?? 'user';

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        // TODO: Real DB lookup
        // if ($role === 'admin') {
        //     $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = ?');
        //     $stmt->execute([$email]);
        //     $admin = $stmt->fetch();
        //     if ($admin && password_verify($password, $admin['password_hash'])) {
        //         $_SESSION['admin_id']   = $admin['id'];
        //         $_SESSION['admin_name'] = $admin['first_name'];
        //         header('Location: admin_dashboard.php'); exit;
        //     }
        // } else {
        //     $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        //     $stmt->execute([$email]);
        //     $user = $stmt->fetch();
        //     if ($user && password_verify($password, $user['password_hash'])) {
        //         $_SESSION['user_id']   = $user['id'];
        //         $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        //         header('Location: user_dashboard.php'); exit;
        //     }
        // }
        $error = 'Invalid email or password. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — WildDocuments</title>
  <link rel="stylesheet" href="css/styles.css">
  <style>
    .auth-page { min-height: 100vh; display: flex; flex-direction: column; background: var(--bg-light); }
    .auth-wrap {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 48px 16px;
    }
    .auth-card {
      background: var(--white);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-md);
      padding: 44px 48px;
      width: 100%;
      max-width: 440px;
    }
    .auth-card__header { text-align: center; margin-bottom: 32px; }
    .auth-card__icon {
      width: 56px; height: 56px;
      background: var(--pink-bg);
      border-radius: var(--radius-lg);
      display: inline-flex; align-items: center; justify-content: center;
      margin-bottom: 16px;
    }
    .auth-card__icon svg { width: 28px; height: 28px; }
    .auth-card__title { font-size: 1.5rem; font-weight: 700; color: var(--text-dark); margin-bottom: 6px; }
    .auth-card__sub { font-size: .88rem; color: var(--text-muted); }

    /* Role tabs */
    .role-tabs { display: flex; gap: 0; margin-bottom: 28px; border-radius: var(--radius); overflow: hidden; border: 2px solid var(--border); }
    .role-tab {
      flex: 1; padding: 10px; text-align: center; cursor: pointer;
      font-size: .85rem; font-weight: 600; color: var(--text-muted);
      background: var(--white); border: none; transition: var(--transition);
    }
    .role-tab.active { background: var(--crimson); color: var(--white); }

    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: .82rem; font-weight: 600; color: var(--text-dark); margin-bottom: 6px; }
    .forgot-link { display: block; text-align: right; font-size: .8rem; color: var(--crimson); font-weight: 600; margin-top: -10px; margin-bottom: 20px; }
    .auth-footer { text-align: center; margin-top: 20px; font-size: .85rem; color: var(--text-muted); }
    .auth-footer a { color: var(--crimson); font-weight: 600; }
    .alert { padding: 12px 16px; border-radius: var(--radius); margin-bottom: 20px; font-size: .88rem; background: #FDF0F2; border: 1px solid var(--pink-soft); color: var(--crimson-dark); }
    @media (max-width: 500px) { .auth-card { padding: 28px 20px; } }
  </style>
</head>
<body class="auth-page">

<?php include 'includes/navbar.php'; ?>

<div class="auth-wrap">
  <div class="auth-card">

    <div class="auth-card__header">
      <div class="auth-card__icon">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="#8B1A2E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <polyline points="14 2 14 8 20 8" stroke="#8B1A2E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <line x1="16" y1="13" x2="8" y2="13" stroke="#8B1A2E" stroke-width="2" stroke-linecap="round"/>
          <line x1="16" y1="17" x2="8" y2="17" stroke="#8B1A2E" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </div>
      <h2 class="auth-card__title">Welcome Back</h2>
      <p class="auth-card__sub">Sign in to WildDocuments</p>
    </div>

    <!-- Role selector -->
    <div class="role-tabs" id="roleTabs">
      <button type="button" class="role-tab active" onclick="setRole('user')">🎓 Student / Alumni</button>
      <button type="button" class="role-tab"        onclick="setRole('admin')">🔐 Administrator</button>
    </div>

    <?php if ($error): ?>
      <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <input type="hidden" name="role" id="roleInput" value="<?= htmlspecialchars($_POST['role'] ?? 'user') ?>">

      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" class="form-control"
               placeholder="yourname@university.edu.ph"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control"
               placeholder="Enter your password" required>
      </div>

      <a href="forgot_password.php" class="forgot-link">Forgot password?</a>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px">
        Sign In
      </button>
    </form>

    <div class="auth-footer" id="authFooter">
      Don't have an account? <a href="register.php">Register here</a>
    </div>

  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function setRole(role) {
  document.getElementById('roleInput').value = role;
  const tabs = document.querySelectorAll('.role-tab');
  tabs[0].classList.toggle('active', role === 'user');
  tabs[1].classList.toggle('active', role === 'admin');
  // Hide register link for admin role
  const footer = document.getElementById('authFooter');
  footer.style.display = (role === 'admin') ? 'none' : '';
}

// Restore role state from POST
(function() {
  const savedRole = document.getElementById('roleInput').value;
  if (savedRole === 'admin') setRole('admin');
})();
</script>

</body>
</html>