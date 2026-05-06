<?php
// register.php — WildDocuments User Registration
session_start();
// Redirect if already logged in
// if (isset($_SESSION['user_id'])) {
//     header('Location: user_dashboard.php');
//     exit;
// }

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO: include 'includes/db.php'; $pdo = getDB();
    $firstName  = trim($_POST['first_name']  ?? '');
    $lastName   = trim($_POST['last_name']   ?? '');
    $studentId  = trim($_POST['student_id']  ?? '');
    $program    = trim($_POST['program']     ?? '');
    $email      = trim($_POST['email']       ?? '');
    $password   = $_POST['password']         ?? '';
    $confirmPwd = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($firstName))  $errors[] = 'First name is required.';
    if (empty($lastName))   $errors[] = 'Last name is required.';
    if (empty($studentId))  $errors[] = 'Student ID is required.';
    if (empty($program))    $errors[] = 'Program is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirmPwd) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        // TODO: Check if email/student_id already exists
        // $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR student_id = ?');
        // $stmt->execute([$email, $studentId]);
        // if ($stmt->fetch()) { $errors[] = 'Email or Student ID is already registered.'; }
        // else {
        //   $hash = password_hash($password, PASSWORD_BCRYPT);
        //   $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, student_id, program, email, password_hash) VALUES (?,?,?,?,?,?)');
        //   $stmt->execute([$firstName, $lastName, $studentId, $program, $email, $hash]);
        //   $_SESSION['user_id'] = $pdo->lastInsertId();
        //   $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        //   header('Location: user_dashboard.php');
        //   exit;
        // }
        $success = true; // Remove once DB is wired
    }
}

$programs = [
    'BS Computer Science', 'BS Information Technology', 'BS Nursing',
    'BS Accountancy', 'BS Business Administration', 'BS Engineering',
    'BS Education', 'BS Psychology', 'BS Architecture', 'BS Medical Technology',
    'AB Communication', 'AB Political Science',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — WildDocuments</title>
  <link rel="stylesheet" href="css/styles.css">
  <style>
    .auth-page {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      background: var(--bg-light);
    }
    .auth-wrap {
      flex: 1;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      padding: 48px 16px 64px;
    }
    .auth-card {
      background: var(--white);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-md);
      padding: 40px 44px;
      width: 100%;
      max-width: 560px;
    }
    .auth-card__header {
      text-align: center;
      margin-bottom: 32px;
    }
    .auth-card__logo {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 20px;
      text-decoration: none;
    }
    .auth-card__logo-icon {
      width: 40px; height: 40px;
      background: var(--pink-bg);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
    }
    .auth-card__logo-icon svg { width: 22px; height: 22px; }
    .auth-card__brand { font-family: 'Poppins', sans-serif; font-size: 1.2rem; font-weight: 700; color: var(--crimson); }
    .auth-card__title { font-size: 1.5rem; font-weight: 700; color: var(--text-dark); margin-bottom: 6px; }
    .auth-card__sub { font-size: .88rem; color: var(--text-muted); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: .82rem; font-weight: 600; color: var(--text-dark); margin-bottom: 6px; }
    .form-group label span { color: var(--red-accent); }
    .auth-divider { text-align: center; margin: 24px 0 18px; font-size: .82rem; color: var(--text-muted); }
    .auth-footer { text-align: center; margin-top: 20px; font-size: .85rem; color: var(--text-muted); }
    .auth-footer a { color: var(--crimson); font-weight: 600; }
    .alert { padding: 12px 16px; border-radius: var(--radius); margin-bottom: 20px; font-size: .88rem; }
    .alert-error { background: #FDF0F2; border: 1px solid var(--pink-soft); color: var(--crimson-dark); }
    .alert-success { background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; }
    .alert ul { margin: 6px 0 0 16px; }
    @media (max-width: 600px) {
      .auth-card { padding: 28px 20px; }
      .form-row { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body class="auth-page">

<?php include 'includes/navbar.php'; ?>

<div class="auth-wrap">
  <div class="auth-card">

    <div class="auth-card__header">
      <h2 class="auth-card__title">Create Your Account</h2>
      <p class="auth-card__sub">Register to request and track official university documents.</p>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <strong>Please fix the following:</strong>
        <ul>
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success">
        <strong>Registration successful!</strong> Your account has been created. <a href="login.php" style="color:#166534;font-weight:600">Log in now →</a>
      </div>
    <?php else: ?>

    <form method="POST" action="register.php" novalidate>

      <div class="form-row">
        <div class="form-group">
          <label for="first_name">First Name <span>*</span></label>
          <input type="text" id="first_name" name="first_name" class="form-control"
                 placeholder="e.g. Juan" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label for="last_name">Last Name <span>*</span></label>
          <input type="text" id="last_name" name="last_name" class="form-control"
                 placeholder="e.g. dela Cruz" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="student_id">Student ID <span>*</span></label>
          <input type="text" id="student_id" name="student_id" class="form-control"
                 placeholder="e.g. 2021-00123" value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label for="program">Program <span>*</span></label>
          <select id="program" name="program" class="form-control" required>
            <option value="">— Select Program —</option>
            <?php foreach ($programs as $prog): ?>
              <option value="<?= htmlspecialchars($prog) ?>"
                <?= (($_POST['program'] ?? '') === $prog) ? 'selected' : '' ?>>
                <?= htmlspecialchars($prog) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="email">University Email <span>*</span></label>
        <input type="email" id="email" name="email" class="form-control"
               placeholder="yourname@university.edu.ph" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="password">Password <span>*</span></label>
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="Min. 8 characters" required>
        </div>
        <div class="form-group">
          <label for="confirm_password">Confirm Password <span>*</span></label>
          <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                 placeholder="Repeat password" required>
        </div>
      </div>

      <div style="margin-bottom:20px">
        <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:.85rem;color:var(--text-mid)">
          <input type="checkbox" name="agree_terms" required style="margin-top:2px;accent-color:var(--crimson)">
          I agree to the <a href="#" style="color:var(--crimson);font-weight:600">Terms of Service</a> and
          <a href="#" style="color:var(--crimson);font-weight:600">Privacy Policy</a>.
        </label>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px">
        Create Account
      </button>

    </form>

    <div class="auth-footer">
      Already have an account? <a href="login.php">Log in here</a>
    </div>

    <?php endif; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>