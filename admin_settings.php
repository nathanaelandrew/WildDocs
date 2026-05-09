<?php
// admin_settings.php
session_start();
require_once 'includes/db.php';

// Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { 
    header('Location: login.php'); 
    exit; 
}

$pdo = getDB();
$userId = $_SESSION['user_id'];
$successMsg = "";
$errorMsg = "";

// --- HANDLE PASSWORD CHANGE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_password') {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // Fetch current hash
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!password_verify($current, $user['password_hash'])) {
        $errorMsg = "Current password is incorrect.";
    } elseif ($new !== $confirm) {
        $errorMsg = "New passwords do not match.";
    } elseif (strlen($new) < 8) {
        $errorMsg = "New password must be at least 8 characters.";
    } else {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $update->execute([$newHash, $userId]);
        $successMsg = "Password updated successfully!";
    }
}

// --- HANDLE ACCOUNT DELETION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_account') {
    $confirmText = $_POST['delete_confirm'];
    if ($confirmText === 'DELETE') {
        try {
            // Because of our DB structure with ON DELETE CASCADE, 
            // deleting from 'users' will automatically delete from 'admins'.
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            
            session_destroy();
            header('Location: login.php?msg=account_deleted');
            exit;
        } catch (Exception $e) {
            $errorMsg = "Deletion failed: " . $e->getMessage();
        }
    } else {
        $errorMsg = "Please type 'DELETE' exactly to confirm.";
    }
}

$prefs = [
    ['new_request',     'New Request Submitted',    'Get notified when a student submits a new request.',         true],
    ['payment_confirm', 'Payment Confirmed',         'Get notified when a payment is received.',                   true],
    ['status_update',   'Status Updated',            'Get notified when a request status changes.',                false],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings – WildDocuments Admin</title>
  <link rel="stylesheet" href="css/styles.css">
  <style>
    /* Toggle Switch CSS */
    .toggle-switch { position:relative; display:inline-block; cursor:pointer; }
    .toggle-switch input { display:none; }
    .toggle-track { display:block; width:44px; height:24px; border-radius:50px; background:var(--border); transition:background .2s; position:relative; }
    .toggle-thumb { position:absolute; top:3px; left:3px; width:18px; height:18px; border-radius:50%; background:#fff; transition:transform .2s; box-shadow:0 1px 4px rgba(0,0,0,.2); }
    .toggle-switch input:checked + .toggle-track { background:var(--red-accent); }
    .toggle-switch input:checked + .toggle-track .toggle-thumb { transform:translateX(20px); }
    
    .danger-card { border: 1.5px solid #FCA5A5; background: #FFF1F2; }
    .badge-danger { background: #EF4444; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; }
  </style>
</head>
<body>

<?php include 'includes/admin_navbar.php'; ?>

<div class="app-layout">
  <?php include 'includes/admin_sidebar.php'; ?>

  <main class="main-content" style="background: var(--bg-light); min-height: 100vh; padding-bottom: 50px;">
    <div class="dashboard-page">

      <div class="page-title-row">
        <div>
          <h2>Account Settings</h2>
          <p>Configure notifications, update security, or manage your account status.</p>
        </div>
      </div>

      <?php if($successMsg): ?>
        <div class="alert alert-success" style="margin-bottom: 20px;">✅ <?= $successMsg ?></div>
      <?php endif; ?>
      <?php if($errorMsg): ?>
        <div class="alert alert-error" style="margin-bottom: 20px;">⚠️ <?= $errorMsg ?></div>
      <?php endif; ?>

      <div style="display:grid; grid-template-columns: 1fr 1.2fr; gap: 24px; align-items: start;">

        <!-- 1. NOTIFICATIONS -->
        <div class="card">
          <div class="card__header">
            <h3>🔔 Notifications</h3>
          </div>
          <div class="card__body">
            <p style="margin-bottom:20px; font-size:.85rem; color:var(--text-muted)">Configure your email and dashboard alerts.</p>

            <?php foreach ($prefs as $p): ?>
            <div style="display:flex; align-items:center; justify-content:space-between; padding:14px 0; border-bottom:1px solid var(--border-light)">
              <div style="flex:1; padding-right: 15px;">
                <div style="font-size:.88rem; font-weight:600; color:var(--text-dark)"><?= $p[1] ?></div>
                <div style="font-size:.78rem; color:var(--text-muted); margin-top:2px"><?= $p[2] ?></div>
              </div>
              <label class="toggle-switch">
                <input type="checkbox" <?= $p[3] ? 'checked' : '' ?>>
                <span class="toggle-track"><span class="toggle-thumb"></span></span>
              </label>
            </div>
            <?php endforeach; ?>

            <div style="margin-top:25px">
              <button class="btn btn-primary" onclick="alert('Preferences saved locally!')">Update Preferences</button>
            </div>
          </div>
        </div>

        <div>
            <!-- 2. SECURITY (CHANGE PASSWORD) -->
            <div class="card" style="margin-bottom: 24px;">
              <div class="card__header">
                <h3>🔒 Security</h3>
              </div>
              <div class="card__body">
                <form method="POST">
                  <input type="hidden" name="action" value="update_password">
                  <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
              </div>
            </div>

            <!-- 3. DANGER ZONE -->
            <div class="card danger-card">
              <div class="card__header" style="background:transparent; border-bottom: 1px solid #FECACA;">
                <h3>⚠️ Danger Zone</h3>
                <span class="badge-danger">Irreversible</span>
              </div>
              <div class="card__body">
                <div style="margin-bottom: 15px;">
                    <h4 style="color: #991B1B; margin-bottom: 4px;">Delete Administrator Account</h4>
                    <p style="font-size: 0.82rem; color: #7F1D1D;">Once you delete your account, there is no going back. All your access logs and profile data will be purged.</p>
                </div>
                <button class="btn btn-danger" onclick="openDeleteModal()">Delete My Account</button>
              </div>
            </div>
        </div>

      </div>
    </div>
  </main>
</div>

<!-- DELETE CONFIRMATION MODAL -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal" style="max-width: 400px;">
        <div style="font-size: 3rem; margin-bottom: 10px;">🛑</div>
        <h3>Are you absolutely sure?</h3>
        <p style="margin-bottom: 20px; color: var(--text-mid);">To confirm deletion, please type the word <strong>DELETE</strong> in the box below.</p>
        
        <form method="POST">
            <input type="hidden" name="action" value="delete_account">
            <input type="text" name="delete_confirm" class="form-control" placeholder="Type DELETE here" style="text-align: center; margin-bottom: 20px;" required>
            
            <div style="display: flex; gap: 10px;">
                <button type="button" class="btn btn-ghost btn-block" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn btn-danger btn-block">Confirm Permanent Delete</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function openDeleteModal() {
    document.getElementById('deleteModal').classList.add('open');
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('open');
}
// Close modal on click outside
window.onclick = function(event) {
    if (event.target == document.getElementById('deleteModal')) closeDeleteModal();
}
</script>

</body>
</html>