<?php
// admin_notifications.php
session_start();

// --- PREVIEW MODE: Forced Identity ---
$_SESSION['admin_name'] = "Admin"; 

// Mock Data for Preview
$notifs = [
    ['unread', '📋', 'New Request Submitted', 'Juan dela Cruz submitted a request for Official Transcript.', '2 mins ago'],
    ['unread', '💳', 'Payment Confirmed', 'Maria Santos completed payment for Diploma Copy (₱200).', '15 mins ago'],
    ['unread', '📋', 'New Request Submitted', 'Ana Liza Mendoza submitted a request for Academic Records.', '42 mins ago'],
    ['read',   '✅', 'Request Completed', 'Ricky Villanueva\'s Transcript marked completed.', 'Yesterday'],
    ['read',   '⚙️', 'Status Updated', 'Patrick Lim\'s request status changed to In Progress.', '2 days ago'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notifications – WildDocuments Admin</title>
  <!-- Using your specific CSS path -->
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php 
// Ensure your navbar include exists or comment out if testing single file
if(file_exists('includes/admin_navbar.php')) include 'includes/admin_navbar.php'; 
?>

<div class="app-layout">
  <?php 
  // Ensure your sidebar include exists or comment out if testing single file
  if(file_exists('includes/admin_sidebar.php')) include 'includes/admin_sidebar.php'; 
  ?>

  <main class="main-content">
    <div class="dashboard-page">

      <!-- Header Row using your CSS variables -->
      <div class="page-title-row">
        <div>
          <!-- This line now appears with the Name -->
          <h2 style="margin:0; color: var(--crimson);">Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?>!</h2>
          <p>Stay updated on new requests and system activity.</p>
        </div>
        <button class="btn btn-ghost btn-sm" onclick="markAllRead()">✓ Mark all as read</button>
      </div>

      <!-- Filter Tabs using your btn-primary and btn-ghost classes -->
      <div style="display:flex; gap:8px; margin-bottom:20px">
        <button class="btn btn-primary btn-sm notif-tab" data-filter="all">All</button>
        <button class="btn btn-ghost btn-sm notif-tab" data-filter="unread">
            Unread <span style="background:var(--red-accent); color:#fff; border-radius:50px; padding:1px 7px; font-size:.7rem; margin-left:4px">3</span>
        </button>
        <button class="btn btn-ghost btn-sm notif-tab" data-filter="read">Read</button>
      </div>

      <div class="card">
        <div class="card__header">
          <h3>Recent Notifications</h3>
          <span style="font-size:.8rem; color:var(--text-muted)">3 unread</span>
        </div>

        <div class="notif-list" id="notifList">
          <?php foreach ($notifs as $n): 
            $status = $n[0]; // unread or read
            $isUnread = ($status === 'unread');
          ?>
          <!-- Using your specific .notif-item classes from CSS -->
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

    </div>
  </main>
</div>

<?php if (file_exists('includes/footer.php')) include 'includes/footer.php'; ?>


<script>
// Logic to handle the tabs while maintaining your CSS classes
document.querySelectorAll('.notif-tab').forEach(btn => {
  btn.addEventListener('click', () => {
    // Switch button styles
    document.querySelectorAll('.notif-tab').forEach(b => {
        b.classList.remove('btn-primary');
        b.classList.add('btn-ghost');
    });
    btn.classList.remove('btn-ghost');
    btn.classList.add('btn-primary');

    const filter = btn.dataset.filter;
    document.querySelectorAll('.notif-item').forEach(item => {
      if (filter === 'all') item.style.display = 'flex';
      else if (filter === 'unread' && item.dataset.read === 'unread') item.style.display = 'flex';
      else if (filter === 'read' && item.dataset.read === 'read') item.style.display = 'flex';
      else item.style.display = 'none';
    });
  });
});

function markAllRead() {
  document.querySelectorAll('.notif-item.unread').forEach(el => {
    el.classList.remove('unread');
    el.dataset.read = 'read';
    const dot = el.querySelector('.notif-item__dot');
    if(dot) dot.classList.add('read');
  });
}
</script>

</body>
</html>