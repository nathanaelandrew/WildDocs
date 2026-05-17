<?php
// student_settings.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') { 
    header('Location: login.php'); 
    exit; 
}

$pdo    = getDB();
$userId = $_SESSION['user_id'];

// Result flags — shown via modal, not inline PHP
$pwResult  = null; // 'success' | 'error'
$pwMessage = '';

// ── Handle Password Change ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_password') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!password_verify($current, $user['password_hash'])) {
        $pwResult  = 'error';
        $pwMessage = 'Current password is incorrect.';
    } elseif ($new !== $confirm) {
        $pwResult  = 'error';
        $pwMessage = 'New passwords do not match.';
    } elseif (strlen($new) < 8) {
        $pwResult  = 'error';
        $pwMessage = 'New password must be at least 8 characters.';
    } else {
        $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")
            ->execute([password_hash($new, PASSWORD_DEFAULT), $userId]);
        $pwResult  = 'success';
        $pwMessage = 'Password updated successfully!';
    }
}

// ── Handle Account Deletion ───────────────────────────────────────────
$deleteError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_account') {
    if (trim($_POST['delete_confirm'] ?? '') === 'DELETE') {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
            $pdo->commit();
            session_destroy();
            header('Location: login.php?msg=account_deleted');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $deleteError = 'Deletion failed: ' . $e->getMessage();
        }
    } else {
        $deleteError = "Please type 'DELETE' exactly to confirm account removal.";
    }
}

$prefs = [
    ['new_update',    'Status Updates',       'Get notified when your request is approved or released.', true],
    ['system_alerts', 'University Bulletins', 'Receive important maintenance and school announcements.', true],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings – WildDocuments</title>
  <link rel="stylesheet" href="css/styles.css">
  <style>
    .toggle-switch { position:relative; display:inline-block; cursor:pointer; }
    .toggle-switch input { display:none; }
    .toggle-track { display:block; width:44px; height:24px; border-radius:50px; background:var(--border); transition:background .2s; position:relative; }
    .toggle-thumb { position:absolute; top:3px; left:3px; width:18px; height:18px; border-radius:50%; background:#fff; transition:transform .2s; box-shadow:0 1px 4px rgba(0,0,0,.2); }
    .toggle-switch input:checked + .toggle-track { background:var(--red-accent); }
    .toggle-switch input:checked + .toggle-track .toggle-thumb { transform:translateX(20px); }
    .danger-card { border: 1.5px solid #FCA5A5; background: #FFF1F2; }
    .badge-danger { background: #EF4444; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
  </style>
</head>
<body>

<?php include 'includes/student_navbar.php'; ?>

<div class="app-layout">
  <?php include 'includes/student_sidebar.php'; ?>

  <main class="main-content" style="background: var(--bg-light); min-height: 100vh; padding-bottom: 50px;">
    <div class="dashboard-page">

      <div class="page-title-row">
        <div>
          <h2>Account Settings</h2>
          <p>Configure notifications and maintain your account security.</p>
        </div>
      </div>

      <div style="display:grid; grid-template-columns: 1fr 1.2fr; gap: 24px; align-items: start;">

        <!-- NOTIFICATIONS -->
        <div class="card">
          <div class="card__header"><h3>Notifications</h3></div>
          <div class="card__body">
            <p style="margin-bottom:20px; font-size:.85rem; color:var(--text-muted)">Choose how you want to be alerted about your documents.</p>

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
              <button class="btn btn-primary" onclick="openModal('prefsModal')">Update Preferences</button>
            </div>
          </div>
        </div>

        <div>
          <!-- SECURITY -->
          <div class="card" style="margin-bottom: 24px;">
            <div class="card__header"><h3>Security</h3></div>
            <div class="card__body">
              <form id="pwForm" method="POST" onsubmit="return interceptPwSubmit(event)">
                <input type="hidden" name="action" value="update_password">
                <div class="form-group">
                  <label class="form-label">Current Password</label>
                  <input type="password" name="current_password" id="current_password" class="form-control" required>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" id="new_password" class="form-control" required minlength="8">
                  </div>
                  <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                  </div>
                </div>
                <button type="submit" class="btn btn-primary">Change Password</button>
              </form>
            </div>
          </div>

          <!-- DANGER ZONE -->
          <div class="card danger-card">
            <div class="card__header" style="background:transparent; border-bottom: 1px solid #FECACA;">
              <h3>Danger Zone</h3>
              <span class="badge-danger">Irreversible</span>
            </div>
            <div class="card__body">
              <h4 style="color: #991B1B; margin-bottom: 4px;">Delete My Student Account</h4>
              <p style="font-size: 0.82rem; color: #7F1D1D; margin-bottom: 15px;">Permanently delete your profile and all document request history. This action cannot be reversed.</p>
              <button class="btn btn-danger" onclick="openModal('deleteModal')">Delete Account</button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </main>
</div>

<!-- ══ MODAL: Change Password Confirmation ══ -->
<div class="modal-overlay" id="pwConfirmModal">
  <div class="modal" style="max-width:400px;text-align:center">
    <div style="font-size:2.6rem;margin-bottom:12px">🔑</div>
    <h3 style="margin-bottom:8px">Change Password?</h3>
    <p style="margin-bottom:24px;color:var(--text-muted);font-size:.88rem">
      Are you sure you want to update your password?
      You will stay logged in after the change.
    </p>
    <div style="display:flex;gap:10px;justify-content:center">
      <button class="btn btn-ghost" onclick="closeModal('pwConfirmModal')">Cancel</button>
      <button class="btn btn-primary" onclick="submitPwForm()">Yes, Change It</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: Password Result (success / error) ══ -->
<div class="modal-overlay" id="pwResultModal">
  <div class="modal" style="max-width:380px;text-align:center">
    <div id="pwResultIcon" style="font-size:2.6rem;margin-bottom:12px"></div>
    <h3 id="pwResultTitle" style="margin-bottom:8px"></h3>
    <p id="pwResultMsg" style="margin-bottom:24px;font-size:.88rem;color:var(--text-muted)"></p>
    <button class="btn btn-primary" onclick="closeModal('pwResultModal')">OK</button>
  </div>
</div>

<!-- ══ MODAL: Preferences Saved ══ -->
<div class="modal-overlay" id="prefsModal">
  <div class="modal" style="max-width:360px;text-align:center">
    <div style="font-size:2.6rem;margin-bottom:12px">✅</div>
    <h3 style="margin-bottom:8px">Preferences Saved</h3>
    <p style="margin-bottom:24px;color:var(--text-muted);font-size:.88rem">
      Your notification preferences have been updated.
    </p>
    <button class="btn btn-primary" onclick="closeModal('prefsModal')">Done</button>
  </div>
</div>

<!-- ══ MODAL: Delete Account Confirmation ══ -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal" style="max-width:400px;text-align:center">
    <div style="font-size:2.6rem;margin-bottom:12px">🛑</div>
    <h3 style="margin-bottom:8px">Delete Your Account?</h3>
    <p style="color:var(--text-muted);font-size:.88rem;margin-bottom:20px">
      This is <strong>permanent and irreversible</strong>. Type <strong>DELETE</strong> below to confirm.
    </p>
    <form method="POST">
      <input type="hidden" name="action" value="delete_account">
      <input type="text" name="delete_confirm" class="form-control"
             placeholder="Type DELETE here"
             style="text-align:center;margin-bottom:18px;letter-spacing:.1em;font-weight:700"
             required>
      <div style="display:flex;gap:10px">
        <button type="button" class="btn btn-ghost btn-block" onclick="closeModal('deleteModal')">Cancel</button>
        <button type="submit" class="btn btn-danger btn-block">Confirm Deletion</button>
      </div>
    </form>
  </div>
</div>

<?php if (!empty($deleteError)): ?>
<div class="modal-overlay open" id="deleteErrModal">
  <div class="modal" style="max-width:360px;text-align:center">
    <div style="font-size:2.6rem;margin-bottom:12px">⚠️</div>
    <h3 style="margin-bottom:8px">Deletion Failed</h3>
    <p style="margin-bottom:24px;color:var(--text-muted);font-size:.88rem"><?= htmlspecialchars($deleteError) ?></p>
    <button class="btn btn-primary" onclick="closeModal('deleteErrModal')">OK</button>
  </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

<script>
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// Close any modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });
});

// ── Intercept password form → show confirm modal first ────────────────
function interceptPwSubmit(e) {
    e.preventDefault();

    const np = document.getElementById('new_password').value;
    const cp = document.getElementById('confirm_password').value;

    if (np !== cp) {
        showPwResult('error', 'Passwords Do Not Match', 'Your new password and confirmation do not match. Please try again.');
        return false;
    }
    if (np.length < 8) {
        showPwResult('error', 'Password Too Short', 'New password must be at least 8 characters.');
        return false;
    }

    openModal('pwConfirmModal');
    return false;
}

function submitPwForm() {
    closeModal('pwConfirmModal');
    document.getElementById('pwForm').submit();
}

function showPwResult(type, title, msg) {
    document.getElementById('pwResultIcon').textContent  = type === 'success' ? '✅' : '❌';
    document.getElementById('pwResultTitle').textContent = title;
    document.getElementById('pwResultMsg').textContent   = msg;
    openModal('pwResultModal');
}

// ── Auto-open result modal from PHP response ──────────────────────────
<?php if ($pwResult): ?>
document.addEventListener('DOMContentLoaded', function() {
    showPwResult(
        <?= json_encode($pwResult) ?>,
        <?= json_encode($pwResult === 'success' ? 'Password Updated' : 'Update Failed') ?>,
        <?= json_encode($pwMessage) ?>
    );
});
<?php endif; ?>
</script>

</body>
</html>