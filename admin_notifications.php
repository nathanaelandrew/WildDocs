<?php
// 1. MUST be at the very top
session_start();

// 2. Uncomment this for security
// if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

// TODO: $notifications = fetchAdminNotifications($pdo, $_SESSION['admin_id']);

$notifs = [
    ['unread', '📋', 'New Request Submitted', 'Juan dela Cruz submitted a request for Official Transcript.', '2 mins ago'],
    ['unread', '💳', 'Payment Confirmed', 'Maria Santos has completed payment for Diploma Copy (₱200).', '15 mins ago'],
    ['unread', '📋', 'New Request Submitted', 'Ana Liza Mendoza submitted a request for Academic Records.', '42 mins ago'],
    ['unread', '🔔', 'Request Due for Review', 'Carlos Reyes\'s Certification Letter request is pending for 3 days.', '1 hour ago'],
    ['unread', '📋', 'New Request Submitted', 'Sophia Laurel submitted a request for Certification Letter.', '2 hours ago'],
    ['read',   '✅', 'Request Completed', 'Ricky Villanueva\'s Official Transcript has been marked completed.', 'Yesterday'],
    ['read',   '✅', 'Request Completed', 'Grace Aquino\'s Official Transcript has been marked completed.', 'Yesterday'],
    ['read',   '⚙️', 'Status Updated', 'Patrick Lim\'s request status changed to In Progress.', '2 days ago'],
    ['read',   '💳', 'Payment Confirmed', 'Miguel Torres completed payment for Diploma Copy (₱200).', '2 days ago'],
    ['read',   '🔔', 'System Maintenance Scheduled', 'The system will be under maintenance on May 10, 2026 from 12–2 AM.', '3 days ago'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notifications – WildDocuments Admin</title>
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
          <h2>Notifications</h2>
          <p>Stay updated on new requests and system activity.</p>
        </div>
        <button class="btn btn-ghost btn-sm" onclick="markAllRead()">✓ Mark all as read</button>
      </div>

      <!-- Notification Tabs -->
      <div style="display:flex;gap:6px;margin-bottom:18px">
        <button class="btn btn-primary btn-sm notif-tab active" data-filter="all">All</button>
        <button class="btn btn-ghost btn-sm notif-tab" data-filter="unread">Unread <span style="background:var(--red-accent);color:#fff;border-radius:50px;padding:1px 7px;font-size:.72rem;margin-left:4px">5</span></button>
        <button class="btn btn-ghost btn-sm notif-tab" data-filter="read">Read</button>
      </div>

      <div class="card">
        <div class="card__header">
          <h3>Recent Notifications</h3>
          <span style="font-size:.8rem;color:var(--text-muted)">5 unread</span>
        </div>

        <div class="notif-list" id="notifList">
          <?php foreach ($notifs as $n): 
            $isUnread = $n[0] === 'unread';
          ?>
          <div class="notif-item <?= $isUnread ? 'unread' : '' ?>" data-read="<?= $n[0] ?>">
            <div class="notif-item__dot <?= $isUnread ? '' : 'read' ?>"></div>
            <div style="font-size:1.2rem;flex-shrink:0;width:28px;text-align:center"><?= $n[1] ?></div>
            <div class="notif-item__body">
              <div class="notif-item__title"><?= htmlspecialchars($n[2]) ?></div>
              <div class="notif-item__desc"><?= htmlspecialchars($n[3]) ?></div>
            </div>
            <div class="notif-item__time"><?= $n[4] ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </main>
</div>

<?php include 'includes/admin_footer.php'; ?>

<script>
// JS logic is already solid
function markAllRead() {
  document.querySelectorAll('.notif-item.unread').forEach(el => {
    el.classList.remove('unread');
    el.dataset.read = 'read';
    el.querySelector('.notif-item__dot').classList.add('read');
  });
}

document.querySelectorAll('.notif-tab').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.notif-tab').forEach(b => {
        b.classList.remove('btn-primary');
        b.classList.add('btn-ghost');
    });
    btn.classList.add('btn-primary');
    btn.classList.remove('btn-ghost');

    const filter = btn.dataset.filter;
    document.querySelectorAll('.notif-item').forEach(item => {
      if (filter === 'all' || item.dataset.read === filter) {
          item.style.display = '';
      } else {
          item.style.display = 'none';
      }
    });
  });
});
</script>
</body>
</html>