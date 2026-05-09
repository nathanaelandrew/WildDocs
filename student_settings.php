<?php
// 1. Session start MUST be at the absolute top
session_start();

// 2. User Authentication check
// if (!isset($_SESSION['user_id'])) { 
//     header('Location: login.php'); 
//     exit; 
// }

// 3. Data logic for the page
$prefs = [
    ['new_request',     'New Request Submitted',    'Get notified when a student submits a new request.',         true],
    ['payment_confirm', 'Payment Confirmed',         'Get notified when a payment is received.',                   true],
    ['status_update',   'Status Updated',            'Get notified when a request status changes.',                false],
    ['system_alerts',   'System Alerts',             'Receive system maintenance and important alerts.',            true],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings – WildDocuments User</title>
  <link rel="stylesheet" href="css/styles.css">
  <style>
    /* Toggle Switch CSS */
    .toggle-switch { position:relative; display:inline-block; cursor:pointer; }
    .toggle-switch input { display:none; }
    .toggle-track {
      display:block; width:44px; height:24px; border-radius:50px;
      background:var(--border); transition:background .2s; position:relative;
    }
    .toggle-thumb {
      position:absolute; top:3px; left:3px;
      width:18px; height:18px; border-radius:50%;
      background:#fff; transition:transform .2s;
      box-shadow:0 1px 4px rgba(0,0,0,.2);
    }
    .toggle-switch input:checked + .toggle-track { background:var(--red-accent); }
    .toggle-switch input:checked + .toggle-track .toggle-thumb { transform:translateX(20px); }
    .divider { margin: 20px 0; border-top: 1px solid var(--border-light); }
  </style>
</head>
<body>

<?php include 'includes/student_navbar.php'; ?>

<div class="app-layout">
  <?php include 'includes/student_sidebar.php'; ?>

  <main class="main-content">
    <div class="dashboard-page">

      <div class="page-title-row">
        <div>
          <h2>Settings</h2>
          <p>Manage your account preferences and security settings.</p>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start">

        <!-- Notification Preferences -->
        <div class="card">
          <div class="card__header">
            <h3>🔔 Notification Preferences</h3>
          </div>
          <div class="card__body">
            <p style="margin-bottom:18px;font-size:.85rem">Choose which events you want to be notified about.</p>

            <?php foreach ($prefs as $p): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border-light)">
              <div>
                <div style="font-size:.88rem;font-weight:600;color:var(--text-dark)"><?= $p[1] ?></div>
                <div style="font-size:.78rem;color:var(--text-muted);margin-top:2px"><?= $p[2] ?></div>
              </div>
              <label class="toggle-switch">
                <input type="checkbox" name="notif_<?= $p[0] ?>" <?= $p[3] ? 'checked' : '' ?>>
                <span class="toggle-track">
                  <span class="toggle-thumb"></span>
                </span>
              </label>
            </div>
            <?php endforeach; ?>

            <div style="margin-top:20px">
              <button class="btn btn-primary btn-sm" onclick="savePrefs()">Save Preferences</button>
            </div>
          </div>
        </div>

        <!-- Security -->
        <div class="card">
          <div class="card__header">
            <h3>🔒 Security</h3>
          </div>
          <div class="card__body">
            <p style="margin-bottom:18px;font-size:.85rem">Update your user password to keep your account secure.</p>

            <form method="POST" action="change_password.php" onsubmit="return validatePwForm()">
              <div class="form-group">
                <label class="form-label">Current Password <span class="req">*</span></label>
                <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
              </div>
              <div class="form-group">
                <label class="form-label">New Password <span class="req">*</span></label>
                <input type="password" name="new_password" id="newPw" class="form-control" placeholder="Minimum 8 characters" required>
                <div class="form-hint">Use at least 8 characters with letters and numbers.</div>
              </div>
              <div class="form-group">
                <label class="form-label">Confirm New Password <span class="req">*</span></label>
                <input type="password" name="confirm_password" id="confirmPw" class="form-control" placeholder="Re-enter new password" required>
                <div class="form-error" id="pwError" style="display:none; color:red; font-size:0.8rem;">Passwords do not match.</div>
              </div>
              <button type="submit" class="btn btn-primary btn-sm">Update Password</button>
            </form>

            <div class="divider"></div>

            <!-- Danger Zone -->
            <div style="background:var(--pink-bg);border:1px solid var(--pink-soft);border-radius:var(--radius);padding:16px">
              <div style="font-size:.85rem;font-weight:700;color:var(--crimson);margin-bottom:6px">⚠️ Danger Zone</div>
              <p style="font-size:.82rem;margin-bottom:12px">Logging out will end your current user session.</p>
              <a href="logout.php" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to log out?')">Logout Now</a>
            </div>
          </div>
        </div>

      </div>
    </div>
  </main>
</div>

<?php if (file_exists('includes/footer.php')) include 'includes/footer.php'; ?>

<script>
function savePrefs() {
  alert('Preferences saved!');
}
function validatePwForm() {
  const np = document.getElementById('newPw').value;
  const cp = document.getElementById('confirmPw').value;
  const err = document.getElementById('pwError');
  if (np !== cp) { 
      err.style.display = 'block'; 
      return false; 
  }
  err.style.display = 'none'; 
  return true;
}
</script>

</body>
</html>