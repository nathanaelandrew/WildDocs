<?php
// register.php
session_start();

// If already logged in, redirect away from the registration page
if (isset($_SESSION['user_id'])) {
    $target = ($_SESSION['user_role'] === 'admin') ? 'admin_dashboard.php' : 'student_dashboard.php';
    header("Location: $target");
    exit;
}

require_once 'includes/db.php';

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Shared fields
    $firstName  = trim($_POST['first_name']   ?? '');
    $lastName   = trim($_POST['last_name']    ?? '');
    $middleName = trim($_POST['middle_name']  ?? ''); // Handled here
    $email      = trim($_POST['email']        ?? '');
    $password   = $_POST['password']          ?? '';
    $confirmPwd = $_POST['confirm_password']  ?? '';
    $role       = $_POST['role']              ?? 'student'; 

    // Student fields
    $studentNum = trim($_POST['student_number'] ?? '');
    $program    = trim($_POST['program']        ?? '');
    $yearLevel  = (int)($_POST['year_level']    ?? 0);

    // Validation
    if (empty($firstName)) $errors[] = 'First name is required.';
    if (empty($lastName))  $errors[] = 'Last name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirmPwd) $errors[] = 'Passwords do not match.';

    if ($role === 'student') {
        if (empty($studentNum)) $errors[] = 'Student ID Number is required.';
        if (empty($program))    $errors[] = 'Program selection is required.';
        if ($yearLevel < 1)     $errors[] = 'Year level selection is required.';
    }

    if (empty($errors)) {
        try {
            $pdo = getDB();
            $pdo->beginTransaction();

            // 1. Check if email exists
            $chk = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $chk->execute([$email]);
            if ($chk->fetch()) throw new Exception('Email already registered.');

            // 2. Insert into Parent (users)
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('
                INSERT INTO users (first_name, last_name, middle_name, email, password_hash, role)
                VALUES (?, ?, ?, ?, ?, ?)
                RETURNING id
            ');
            $stmt->execute([$firstName, $lastName, $middleName ?: null, $email, $hash, $role]);
            $newUserId = $stmt->fetch()['id'];

            // 3. Insert into Child Tables
            if ($role === 'student') {
                $stmtSub = $pdo->prepare('
                    INSERT INTO students (user_id, student_number, program, year_level)
                    VALUES (?, ?, ?, ?)
                ');
                $stmtSub->execute([$newUserId, $studentNum, $program, $yearLevel]);
            } else {
                $stmtSub = $pdo->prepare('
                    INSERT INTO admins (user_id, admin_level)
                    VALUES (?, ?)
                ');
                $stmtSub->execute([$newUserId, 'High']);
            }

            $pdo->commit();
            $success = true;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}

$programs = ['BS Computer Science','BS Information Technology','BS Nursing','BS Accountancy','BS Engineering','BS Psychology'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — WildDocuments</title>
  <link rel="stylesheet" href="css/styles.css">
  <style>
    .auth-page { min-height: 100vh; display: flex; flex-direction: column; background: var(--bg-light); }
    .auth-wrap { flex: 1; display: flex; align-items: flex-start; justify-content: center; padding: 48px 16px; }
    .auth-card { background: var(--white); border-radius: var(--radius-xl); box-shadow: var(--shadow-md); padding: 40px; width: 100%; max-width: 540px; border: 1px solid var(--border); }
    .role-tabs { display: flex; margin-bottom: 24px; border-radius: var(--radius); overflow: hidden; border: 2px solid var(--border); }
    .role-tab { flex: 1; padding: 12px; text-align: center; cursor: pointer; font-size: .85rem; font-weight: 600; color: var(--text-muted); background: var(--white); border: none; transition: var(--transition); }
    .role-tab.active { background: var(--crimson); color: var(--white); }
  </style>
</head>
<body class="auth-page">
<?php include 'includes/navbar.php'; ?>

<div class="auth-wrap">
  <div class="auth-card">
    <?php if ($success): ?>
      <div class="success-screen" style="text-align:center; padding: 20px;">
        <div style="width: 80px; height: 80px; background: #EAFAF1; color: #27AE60; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 20px; border: 2px solid #27AE60;">
            ✓
        </div>
        <h2 style="font-family:'Poppins'; font-weight: 700; color: #1A1A1A;">Registration Successful!</h2>
        <p style="margin-bottom: 25px; color: #4A4A55;">Your account has been created. You can now log in.</p>
        <a href="login.php" class="btn btn-primary btn-lg btn-block">Go to Login Page</a>
      </div>
    <?php else: ?>
      <div class="text-center" style="margin-bottom:28px">
        <h2 style="font-family:'Poppins';">Create Account</h2>
        <p>Register to the document request system</p>
      </div>

      <div class="role-tabs">
        <button type="button" class="role-tab active" id="tabStudent" onclick="switchRole('student')">Student</button>
        <button type="button" class="role-tab" id="tabAdmin" onclick="switchRole('admin')">Administrator</button>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <div class="icon">⚠️</div>
          <div><?php foreach ($errors as $e) echo "<div>$e</div>"; ?></div>
        </div>
      <?php endif; ?>

      <form method="POST">
        <input type="hidden" name="role" id="roleInput" value="student">
        
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">First Name <span class="req">*</span></label>
            <input type="text" name="first_name" class="form-control" placeholder="Juan" value="<?= htmlspecialchars($firstName ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Last Name <span class="req">*</span></label>
            <input type="text" name="last_name" class="form-control" placeholder="dela Cruz" value="<?= htmlspecialchars($lastName ?? '') ?>" required>
          </div>
        </div>

        <!-- MIDDLE NAME FIELD ADDED HERE -->
        <div class="form-group">
          <label class="form-label">Middle Name <span style="font-weight:400; color:var(--text-muted)">(Optional)</span></label>
          <input type="text" name="middle_name" class="form-control" placeholder="Santos" value="<?= htmlspecialchars($middleName ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Email Address <span class="req">*</span></label>
          <input type="email" name="email" class="form-control" placeholder="user@university.edu.ph" value="<?= htmlspecialchars($email ?? '') ?>" required>
        </div>

        <div id="studentContainer">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Student ID Number <span class="req">*</span></label>
              <input type="text" name="student_number" id="inputSID" class="form-control" placeholder="2024-00123">
            </div>
            <div class="form-group">
              <label class="form-label">Year Level <span class="req">*</span></label>
              <select name="year_level" id="inputYL" class="form-control">
                <option value="">— Select —</option>
                <option value="1">1st Year</option><option value="2">2nd Year</option><option value="3">3rd Year</option><option value="4">4th Year</option><option value="5">5th Year</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Program <span class="req">*</span></label>
            <select name="program" id="inputProg" class="form-control">
              <option value="">— Select Program —</option>
              <?php foreach ($programs as $p): ?><option value="<?= $p ?>"><?= $p ?></option><?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Password <span class="req">*</span></label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
          </div>
          <div class="form-group">
            <label class="form-label">Confirm Password <span class="req">*</span></label>
            <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="padding:14px;margin-top:10px">Register Now</button>
      </form>

      <div class="auth-footer" style="text-align:center;margin-top:20px;font-size:.85rem;color:var(--text-muted)">
        Already registered? <a href="login.php" style="color:var(--crimson);font-weight:600">Login here</a>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
function switchRole(role) {
  document.getElementById('roleInput').value = role;
  document.getElementById('tabStudent').classList.toggle('active', role === 'student');
  document.getElementById('tabAdmin').classList.toggle('active', role === 'admin');
  
  const studentSection = document.getElementById('studentContainer');
  const studentInputs = [document.getElementById('inputSID'), document.getElementById('inputYL'), document.getElementById('inputProg')];

  if (role === 'admin') {
    studentSection.style.display = 'none';
    studentInputs.forEach(el => el.removeAttribute('required'));
  } else {
    studentSection.style.display = 'block';
    studentInputs.forEach(el => el.setAttribute('required', 'required'));
  }
}
switchRole('student');
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>