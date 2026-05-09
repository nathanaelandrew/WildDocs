<?php
// login.php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $target = ($_SESSION['user_role'] === 'admin') ? 'admin_dashboard.php' : 'student_dashboard.php';
    header("Location: $target");
    exit;
}

$error = '';
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email         = trim($_POST['email'] ?? '');
    $password      = $_POST['password'] ?? '';
    $roleSelected  = $_POST['role'] ?? 'student'; // Role from the UI tabs

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        try {
            $pdo = getDB();

            if ($roleSelected === 'admin') {
                // JOIN users with admins table
                $stmt = $pdo->prepare('
                    SELECT u.*, a.admin_id, a.admin_level 
                    FROM users u
                    JOIN admins a ON u.id = a.user_id
                    WHERE u.email = ? AND u.role = \'admin\'
                ');
            } else {
                // JOIN users with students table
                $stmt = $pdo->prepare('
                    SELECT u.*, s.student_id, s.student_number, s.program, s.year_level 
                    FROM users u
                    JOIN students s ON u.id = s.user_id
                    WHERE u.email = ? AND u.role = \'student\'
                ');
            }

            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Verify User exists and Password is correct
            if ($user && password_verify($password, $user['password_hash'])) {
                
                // Common Session Variables
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];

                if ($user['role'] === 'admin') {
                    $_SESSION['admin_level'] = $user['admin_level'];
                    header('Location: admin_dashboard.php');
                } else {
                    $_SESSION['student_number'] = $user['student_number'];
                    $_SESSION['user_program']    = $user['program'];
                    header('Location: student_dashboard.php');
                }
                exit;
            } else {
                $error = 'Invalid email or password for the selected role.';
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
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
    .auth-page{min-height:100vh;display:flex;flex-direction:column;background:var(--bg-light)}
    .auth-wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:48px 16px}
    .auth-card{background:var(--white);border-radius:var(--radius-xl);box-shadow:var(--shadow-md);padding:44px 48px;width:100%;max-width:440px;border: 1px solid var(--border);}
    .auth-card__header{text-align:center;margin-bottom:32px}
    .auth-card__title{font-size:1.5rem;font-weight:700;color:var(--text-dark);margin-bottom:6px;font-family:'Poppins';}
    .role-tabs{display:flex;margin-bottom:28px;border-radius:8px;overflow:hidden;border:2px solid var(--border)}
    .role-tab{flex:1;padding:12px;text-align:center;cursor:pointer;font-size:.85rem;font-weight:600;color:var(--text-muted);background:var(--white);border:none;transition:all .2s;}
    .role-tab.active{background:var(--crimson);color:#fff}
    .form-group{margin-bottom:18px}
    .form-group label{display:block;font-size:.82rem;font-weight:600;color:var(--text-dark);margin-bottom:6px}
    .alert-error{padding:12px;background:#fef2f2;color:#991b1b;border-radius:8px;margin-bottom:20px;font-size:.88rem;border:1px solid #fee2e2;}
    .auth-footer{text-align:center;margin-top:20px;font-size:.85rem;color:var(--text-muted)}
    .auth-footer a{color:var(--crimson);font-weight:600}
  </style>
</head>
<body class="auth-page">
<?php include 'includes/navbar.php'; ?>

<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-card__header">
      <h2 class="auth-card__title">Welcome Back</h2>
      <p class="auth-card__sub">Sign in to WildDocuments</p>
    </div>

    <!-- UI Tabs to select role -->
    <div class="role-tabs">
      <button type="button" class="role-tab active" id="tabStudent" onclick="setRole('student')">Student</button>
      <button type="button" class="role-tab" id="tabAdmin" onclick="setRole('admin')">Administrator</button>
    </div>

    <?php if ($error): ?>
      <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <!-- This hidden input changes based on the tab clicked -->
      <input type="hidden" name="role" id="roleInput" value="student">
      
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="name@university.edu.ph" value="<?= htmlspecialchars($email ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block" style="padding:13px; font-weight:700;">Sign In</button>
    </form>

    <div class="auth-footer" id="authFooter">
      Don't have an account? <a href="register.php">Register here</a>
    </div>
  </div>
</div>

<script>
function setRole(role) {
  document.getElementById('roleInput').value = role;
  
  // Update Tab visuals
  document.getElementById('tabStudent').classList.toggle('active', role === 'student');
  document.getElementById('tabAdmin').classList.toggle('active', role === 'admin');
  
  // Hide registration link for admins if desired
  document.getElementById('authFooter').style.opacity = (role === 'admin') ? '0' : '1';
}
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>