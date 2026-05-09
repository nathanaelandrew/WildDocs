<?php
// register.php — WildDocuments User Registration
session_start();
include 'includes/db.php'; // Ensure this is included

// Redirect guard
if (isset($_SESSION['user_id']) && !isset($_GET['registered'])) {
    header('Location: user_dashboard.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName  = trim($_POST['first_name']  ?? '');
    $lastName   = trim($_POST['last_name']   ?? '');
    $studentId  = trim($_POST['student_id']  ?? '');
    $program    = trim($_POST['program']     ?? '');
    $email      = trim($_POST['email']       ?? '');
    $password   = $_POST['password']         ?? '';
    $confirmPwd = $_POST['confirm_password'] ?? '';
    $agreeTerms = $_POST['agree_terms']      ?? '';

    // 1. Validation
    if (empty($firstName))  $errors[] = 'First name is required.';
    if (empty($lastName))   $errors[] = 'Last name is required.';
    if (empty($studentId))  $errors[] = 'Student ID is required.';
    if (empty($program))    $errors[] = 'Please select your academic program.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid university email.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirmPwd) $errors[] = 'Passwords do not match.';
    if (empty($agreeTerms)) $errors[] = 'You must agree to the Terms & Conditions.';

    if (empty($errors)) {
        $pdo = getDB();

        // 2. Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'This email is already registered.';
        } else {
            // 3. Hash password and Insert
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            try {
                $sql = "INSERT INTO users (first_name, last_name, student_id, program, email, password_hash) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$firstName, $lastName, $studentId, $program, $email, $hashedPassword]);

                // 4. Get the last inserted ID (Supabase/Postgres compatible)
                $userId = $pdo->lastInsertId();

                // 5. Log them in automatically
                $_SESSION['user_id'] = $userId; 
                $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                
                $success = true;
            } catch (PDOException $e) {
                $errors[] = "Registration failed. Please try again later.";
                // For debugging: $errors[] = $e->getMessage(); 
            }
        }
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
  <title>Sign Up — WildDocuments</title>
  <link rel="stylesheet" href="css/styles.css">
  <style>
    .auth-page {
      min-height: 100vh;
      display: flex; flex-direction: column; background: #f8fafc;
    }
    .auth-wrap {
      flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px 16px;
    }
    .auth-card {
      background: var(--white); border-radius: 16px;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
      padding: 40px; width: 100%; max-width: 540px;
    }
    .auth-card__header { text-align: center; margin-bottom: 28px; }
    .auth-card__title { font-size: 1.6rem; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
    .auth-card__sub { font-size: 0.9rem; color: #64748b; line-height: 1.5; }

    /* ── Professional Success Icon (SVG) ── */
    .success-state { text-align: center; padding: 10px 0; }
    .success-icon-wrap {
      width: 80px; height: 80px; background: #f0fdf4; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 24px; color: #16a34a;
    }

    /* ── Clean Error UI ── */
    .alert-error {
      background: #fff1f2; border-left: 4px solid #e11d48; color: #9f1239;
      padding: 16px; margin-bottom: 24px; border-radius: 8px;
    }
    .alert-error ul { list-style: none; padding: 0; margin: 8px 0 0; }
    .alert-error li { font-size: 0.88rem; display: flex; align-items: center; margin-bottom: 4px; }
    .alert-error li::before { content: "•"; margin-right: 10px; color: #e11d48; font-weight: bold; }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px; }
    .form-group label span { color: #e11d48; }

    .auth-footer { 
      text-align: center; margin-top: 24px; font-size: 0.9rem; color: #64748b; 
      border-top: 1px solid #f1f5f9; padding-top: 20px; 
    }
    .auth-footer a { color: var(--crimson); font-weight: 600; text-decoration: none; }
    
    @media (max-width: 600px) {
      .auth-card { padding: 24px; }
      .form-row { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body class="auth-page">

<?php include 'includes/navbar.php'; ?>

<div class="auth-wrap">
  <div class="auth-card">

    <?php if ($success): ?>
      <!-- ════════════ SUCCESS CARD ════════════ -->
      <div class="success-state">
        <div class="success-icon-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
        </div>
        <h2 class="auth-card__title">Registration Complete!</h2>
        <p class="auth-card__sub" style="margin-bottom: 30px;">
          Your account was created successfully. You are now logged in and ready to manage your documents.
        </p>
        <!-- Directly to Dashboard -->
        <a href="user_dashboard.php" class="btn btn-primary btn-xl" style="width:100%; justify-content:center;">
          Go to Dashboard
        </a>
      </div>

    <?php else: ?>
      <!-- ════════════ REGISTRATION FORM ════════════ -->
      <div class="auth-card__header">
        <h2 class="auth-card__title">Create Your Account</h2>
        <p class="auth-card__sub">Join WildDocuments to manage your document requests.</p>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="alert-error">
          <strong style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">Registration Errors:</strong>
          <ul>
            <?php foreach ($errors as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="POST" action="register.php" novalidate>
        <div class="form-row">
          <div class="form-group">
            <label for="first_name">First Name <span>*</span></label>
            <input type="text" id="first_name" name="first_name" class="form-control"
                   placeholder="Juan" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label for="last_name">Last Name <span>*</span></label>
            <input type="text" id="last_name" name="last_name" class="form-control"
                   placeholder="dela Cruz" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="student_id">Student ID <span>*</span></label>
            <input type="text" id="student_id" name="student_id" class="form-control"
                   placeholder="2021-00123" value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>" required>
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

        <div style="margin-bottom:24px">
          <label style="display:flex;align-items:flex-start;gap:12px;cursor:pointer;font-size:0.85rem;color:#475569;line-height:1.4">
            <input type="checkbox" name="agree_terms" value="1" required style="margin-top:3px; width:16px; height:16px; accent-color:var(--crimson)">
            <span>I agree to the <a href="#" style="color:var(--crimson);font-weight:600">Terms of Service</a> and
            <a href="#" style="color:var(--crimson);font-weight:600">Privacy Policy</a>.</span>
          </label>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:14px; font-size:1rem;">
          Sign Up
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