<?php
// 1. ALWAYS start the session at the absolute top
session_start();

// 2. Auth Check (Uncomment this for actual use)
// if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

// TODO: $admin = fetchAdminById($pdo, $_SESSION['admin_id']);

$admin = [
  'name'       => 'System Admin',
  'email'      => 'admin@wilddocs.edu.ph',
  'role'       => 'Registrar Admin',
  'phone'      => '+63 912 345 6789',
  'joined'     => 'January 10, 2024',
  'last_login' => 'May 5, 2026 – 9:14 AM',
];

$initials = strtoupper(substr($admin['name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile – WildDocuments Admin</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include 'includes/admin_navbar.php'; ?>

<div class="app-layout">
  <?php include 'includes/admin_sidebar.php'; ?>

  <main class="main-content">
    <div class="dashboard-page">

      <div class="page-title-row">
        <div>
          <h2>Profile</h2>
          <p>View and update your administrator profile.</p>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start">

        <!-- Profile Card -->
        <div class="card">
          <div class="profile-avatar-wrap">
            <div class="profile-avatar"><?= $initials ?></div>
            <div class="profile-name"><?= htmlspecialchars($admin['name']) ?></div>
            <div class="profile-email"><?= htmlspecialchars($admin['email']) ?></div>
            <span class="badge badge-new" style="margin-top:4px">👑 <?= $admin['role'] ?></span>
          </div>
          <div style="padding:18px 20px">
            <div style="font-size:.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:12px">Account Info</div>
            <div style="display:flex;flex-direction:column;gap:10px;font-size:.85rem">
              <div style="display:flex;justify-content:space-between">
                <span style="color:var(--text-muted)">Phone</span>
                <span style="font-weight:500"><?= $admin['phone'] ?></span>
              </div>
              <div style="display:flex;justify-content:space-between">
                <span style="color:var(--text-muted)">Member Since</span>
                <span style="font-weight:500"><?= $admin['joined'] ?></span>
              </div>
              <div style="display:flex;justify-content:space-between">
                <span style="color:var(--text-muted)">Last Login</span>
                <span style="font-weight:500;font-size:.78rem"><?= $admin['last_login'] ?></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Edit Profile Form -->
        <div class="card">
          <div class="card__header">
            <h3>Edit Profile</h3>
          </div>
          <div class="card__body">
            <form method="POST" action="update_profile.php">
              <div class="form-section">
                <div class="form-section__title">Personal Information</div>
                <div class="form-row">
                  <div class="form-group">
                    <label class="form-label">First Name <span class="req">*</span></label>
                    <input type="text" name="first_name" class="form-control" value="System" required>
                  </div>
                  <div class="form-group">
                    <label class="form-label">Last Name <span class="req">*</span></label>
                    <input type="text" name="last_name" class="form-control" value="Admin" required>
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">Email Address <span class="req">*</span></label>
                  <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Phone Number</label>
                  <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($admin['phone']) ?>">
                </div>
                <div class="form-group">
                  <label class="form-label">Role / Position</label>
                  <input type="text" name="role" class="form-control" value="<?= htmlspecialchars($admin['role']) ?>" readonly>
                  <div class="form-hint">Contact a super-admin to change your role.</div>
                </div>
              </div>

              <div style="display:flex;gap:10px">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="reset" class="btn btn-ghost">Reset</button>
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>
  </main>
</div>

<?php include 'includes/admin_footer.php'; ?>

</body>
</html>