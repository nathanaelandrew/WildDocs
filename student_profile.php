<?php
// student_profile.php
session_start();
require_once 'includes/db.php';

// Auth Check: Only allow logged-in students
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') { 
    header('Location: login.php'); 
    exit; 
}

$pdo = getDB();
$userId = $_SESSION['user_id'];
$successMsg = "";
$errorMsg = "";

// --- HANDLE PROFILE UPDATE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName  = trim($_POST['first_name'] ?? '');
    $lastName   = trim($_POST['last_name'] ?? '');
    $middleName = trim($_POST['middle_name'] ?? '');

    if (empty($firstName) || empty($lastName)) {
        $errorMsg = "Please fill in all required fields.";
    } else {
        try {
            // Update the parent 'users' table
            $stmt = $pdo->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, middle_name = ? 
                WHERE id = ?
            ");
            $stmt->execute([$firstName, $lastName, $middleName ?: null, $userId]);

            // Update the Session name so the Navbar/Sidebar updates immediately
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            $successMsg = "Profile updated successfully!";
        } catch (Exception $e) {
            $errorMsg = "Update failed: " . $e->getMessage();
        }
    }
}

// --- FETCH CURRENT STUDENT DATA ---
// JOIN users with students table to get names and academic info
$stmt = $pdo->prepare("
    SELECT u.*, s.student_number, s.program, s.year_level 
    FROM users u 
    JOIN students s ON u.id = s.user_id 
    WHERE u.id = ?
");
$stmt->execute([$userId]);
$student = $stmt->fetch();

// Generate Avatar Initials
$initials = strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile – WildDocuments</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include 'includes/student_navbar.php'; ?>

<div class="app-layout">
  <?php include 'includes/student_sidebar.php'; ?>

  <main class="main-content" style="background: var(--bg-light); min-height: 100vh; padding-bottom: 50px;">
    <div class="dashboard-page">

      <div class="page-title-row">
        <div>
          <h2>Account Profile</h2>
          <p>Manage your personal information and account security.</p>
        </div>
      </div>

      <?php if($successMsg): ?>
        <div class="alert alert-success" style="margin-bottom: 20px;">✅ <?= $successMsg ?></div>
      <?php endif; ?>
      
      <?php if($errorMsg): ?>
        <div class="alert alert-error" style="margin-bottom: 20px;">⚠️ <?= $errorMsg ?></div>
      <?php endif; ?>

      <div style="display:grid; grid-template-columns: 300px 1fr; gap: 24px; align-items: start;">

        <!-- Left: Profile Summary Card -->
        <div class="card">
          <div class="profile-avatar-wrap" style="padding: 40px 20px; text-align: center; border-bottom: 1px solid var(--border-light);">
            <div class="profile-avatar" style="margin: 0 auto 15px; width: 80px; height: 80px; font-size: 2rem;">
                <?= $initials ?>
            </div>
            <h3 style="margin:0; font-size: 1.1rem;"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></h3>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 4px;"><?= htmlspecialchars($student['email']) ?></p>
            <span class="badge badge-new" style="margin-top: 12px; background: var(--crimson); color: white;">
                <?= $student['student_number'] ?>
            </span>
          </div>
          <div style="padding: 20px; background: var(--off-white);">
            <div style="font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 10px;">System Metadata</div>
            <div style="font-size: 0.85rem; display: flex; flex-direction: column; gap: 8px;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Internal ID</span>
                    <span style="font-weight: 600;">#<?= $student['id'] ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Joined Date</span>
                    <span style="font-weight: 600;"><?= date('M d, Y', strtotime($student['join_date'])) ?></span>
                </div>
            </div>
          </div>
        </div>

        <!-- Right: Edit Form Card -->
        <div class="card">
          <div class="card__header">
            <h3>Update Personal Information</h3>
          </div>
          <div class="card__body" style="padding: 30px;">
            <form method="POST">
              <div class="form-section">
                <div class="form-row">
                  <div class="form-group">
                    <label class="form-label">First Name <span class="req">*</span></label>
                    <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($student['first_name']) ?>" required>
                  </div>
                  <div class="form-group">
                    <label class="form-label">Last Name <span class="req">*</span></label>
                    <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($student['last_name']) ?>" required>
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label">Middle Name <span style="font-weight: 400; color: var(--text-muted);">(Optional)</span></label>
                  <input type="text" name="middle_name" class="form-control" value="<?= htmlspecialchars($student['middle_name'] ?? '') ?>">
                </div>

                <div class="form-group">
                  <label class="form-label">Email Address <span class="req">*</span></label>
                  <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($student['email']) ?>" required>
                </div>

                <div class="form-group">
                  <label class="form-label">Assigned Role</label>
                  <input type="text" class="form-control" value="<?= $student['student_number'] ?>" readonly style="background: #f8fafc;">
                  <div class="form-hint">Administrative roles can only be modified via database management.</div>
                </div>
              </div>

              <div style="display:flex; gap:12px; margin-top: 10px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">Save Profile Changes</button>
                <button type="reset" class="btn btn-ghost">Reset Form</button>
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>
  </main>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>