<?php
// student_notifications.php
session_start();

// Auth check: Only allow logged-in students
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') { 
    header('Location: login.php'); 
    exit; 
}

// Mock Data for Preview (In a real app, you would fetch these from a 'notifications' table)
$notifs = [
    ['unread', '📦', 'Document Ready for Pickup', 'Your request for "Official Transcript of Records" is ready. Please visit the Registrar Window 2.', '5 mins ago'],
    ['unread', '✅', 'Request Approved', 'Your request for "Certificate of Enrollment" has been approved and is now being processed.', '1 hour ago'],
    ['unread', '💳', 'Payment Success', 'We have confirmed your payment of ₱150.00 for Reference #WD-2024-A1B2.', '4 hours ago'],
    ['read',   '⏳', 'Request Received', 'Your request for "Good Moral Character" has been received and is awaiting evaluation.', 'Yesterday'],
    ['read',   '👤', 'Profile Verified', 'Your student account details have been successfully verified by the registrar.', '3 days ago'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Notifications – WildDocuments</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include 'includes/student_navbar.php'; ?>

<div class="app-layout">
  <?php include 'includes/student_sidebar.php'; ?>

  <main class="main-content">
    <div class="dashboard-page">

      <!-- Header Row -->
      <div class="page-title-row">
        <div>
          <h2 style="margin:0; color: var(--crimson);">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h2>
          <p>Track the progress of your document requests here.</p>
        </div>
        <button class="btn btn-ghost btn-sm" onclick="markAllRead()">✓ Mark all as read</button>
      </div>

      <!-- Filter Tabs -->
      <div style="display:flex; gap:8px; margin-bottom:20px">
        <button class="btn btn-primary btn-sm notif-tab" data-filter="all">All</button>
        <button class="btn btn-ghost btn-sm notif-tab" data-filter="unread">
            Unread <span style="background:var(--red-accent); color:#fff; border-radius:50px; padding:1px 7px; font-size:.7rem; margin-left:4px">3</span>
        </button>
        <button class="btn btn-ghost btn-sm notif-tab" data-filter="read">Read</button>
      </div>

      <!-- Notifications Card -->
      <div class="card">
        <div class="card__header">
          <h3>Recent Updates</h3>
          <span style="font-size:.8rem; color:var(--text-muted)">3 unread notifications</span>
        </div>

        <div class="notif-list" id="notifList">
          <?php foreach ($notifs as $n): 
            $status = $n[0]; // unread or read
            $isUnread = ($status === 'unread');
          ?>
          <div class="notif-item <?= $isUnread ? 'unread' : '' ?>" data-read="<?= $status ?>">
            <div class="notif-item__dot <?= $isUnread ? '' : 'read' ?>"></div>
            <div style="font-size:1.2rem; flex-shrink:0; width:30px; text-align:center;"><?= $n[1] ?></div>
            <div class="notif-item__body">
              <div class="notif-item__title"><?= htmlspecialchars($n[2]) ?></div>
              <div class="notif-item__desc"><?= htmlspecialchars($n[3]) ?></div>
            </div>
            <div class="notif-item__time"><?= $n[4] ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Empty State (Hidden by default) -->
      <div id="emptyState" style="display:none; text-align:center; padding:60px 20px;">
          <div style="font-size:3rem; margin-bottom:15px;">🔔</div>
          <h3 style="color:var(--text-muted);">No notifications found</h3>
          <p>We'll notify you when your request status changes.</p>
      </div>

    </div>
  </main>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Filter Tab Logic
document.querySelectorAll('.notif-tab').forEach(btn => {
  btn.addEventListener('click', () => {
    // Switch button visual styles
    document.querySelectorAll('.notif-tab').forEach(b => {
        b.classList.remove('btn-primary');
        b.classList.add('btn-ghost');
    });
    btn.classList.remove('btn-ghost');
    btn.classList.add('btn-primary');

    const filter = btn.dataset.filter;
    let visibleCount = 0;

    document.querySelectorAll('.notif-item').forEach(item => {
      if (filter === 'all') {
          item.style.display = 'flex';
          visibleCount++;
      } else if (filter === 'unread' && item.dataset.read === 'unread') {
          item.style.display = 'flex';
          visibleCount++;
      } else if (filter === 'read' && item.dataset.read === 'read') {
          item.style.display = 'flex';
          visibleCount++;
      } else {
          item.style.display = 'none';
      }
    });

    // Show empty state if no notifications match filter
    document.getElementById('notifList').style.display = visibleCount === 0 ? 'none' : 'block';
    document.getElementById('emptyState').style.display = visibleCount === 0 ? 'block' : 'none';
  });
});

// Mark All as Read Logic
function markAllRead() {
  document.querySelectorAll('.notif-item.unread').forEach(el => {
    el.classList.remove('unread');
    el.dataset.read = 'read';
    const dot = el.querySelector('.notif-item__dot');
    if(dot) dot.classList.add('read');
  });
  // Update the unread count display (Optional UI polish)
  document.querySelectorAll('.notif-tab span').forEach(span => span.style.display = 'none');
}
</script>

</body>
</html>