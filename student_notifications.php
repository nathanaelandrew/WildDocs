<?php
// student_notifications.php
session_start();
require_once 'includes/db.php';

// Auth check: Only allow logged-in students
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') { 
    header('Location: login.php'); 
    exit; 
}

$pdo = getDB();
$userId = $_SESSION['user_id'];

/**
 * 1. Fetch real student notifications from the database.
 * We use the status of the request to drive the notification content.
 */
$stmt = $pdo->prepare("
    SELECT 
        r.id, 
        r.reference_number, 
        r.student_is_viewed, 
        r.created_at, 
        r.status, 
        dt.name as document_name
    FROM requests r
    JOIN document_types dt ON r.document_type_id = dt.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC 
    LIMIT 30
");
$stmt->execute([$userId]);
$db_notifs = $stmt->fetchAll();

// 2. Count unread for the tab badge
$unreadCount = 0;
foreach($db_notifs as $n) {
    if(!$n['student_is_viewed']) $unreadCount++;
}

/**
 * Helper: Status-based icons and descriptions for Students
 */
function getStudentNotifConfig($status) {
    return match($status) {
        'pending'  => ['icon' => '⏳', 'label' => 'Request Received', 'desc' => 'Your request is currently awaiting review.'],
        'paid'     => ['icon' => '💳', 'label' => 'Payment Submitted', 'desc' => 'We received your payment details and are verifying.'],
        'approved' => ['icon' => '✅', 'label' => 'Request Approved', 'desc' => 'Your document is now being processed by the registrar.'],
        'released' => ['icon' => '📦', 'label' => 'Ready for Pickup', 'desc' => 'Your document is released! Please visit the office.'],
        default    => ['icon' => '🔔', 'label' => 'Status Update', 'desc' => 'There has been an update to your request.'],
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Notifications – WildDocuments</title>
  <link rel="stylesheet" href="css/styles.css">
  <style>
      .badge-count { background: var(--red-accent); color: #fff; border-radius: 50px; padding: 1px 7px; font-size: .7rem; margin-left: 4px; }
      .notif-item { cursor: pointer; transition: background 0.2s; position: relative; border-left: 3px solid transparent; }
      .notif-item:hover { background: var(--pink-bg); }
      .notif-item.unread { border-left-color: var(--crimson); background: #FFF5F6; }
      .badge-paid { background:#EFF6FF; color:#1D4ED8; }
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
          <h2 style="margin:0; color: var(--crimson);">My Notifications</h2>
          <p>Updates regarding your document requests.</p>
        </div>
        <?php if($unreadCount > 0): ?>
            <button class="btn btn-ghost btn-sm" onclick="markAllRead()">✓ Mark all as read</button>
        <?php endif; ?>
      </div>

      <!-- Filter Tabs -->
      <div style="display:flex; gap:8px; margin-bottom:20px">
        <button class="btn btn-primary btn-sm notif-tab" data-filter="all">All</button>
        <button class="btn btn-ghost btn-sm notif-tab" data-filter="unread">
            Unread <?php if($unreadCount > 0): ?><span class="badge-count"><?= $unreadCount ?></span><?php endif; ?>
        </button>
        <button class="btn btn-ghost btn-sm notif-tab" data-filter="read">Read</button>
      </div>

      <div class="card">
        <div class="card__header">
          <h3>Recent Updates</h3>
          <span style="font-size:.8rem; color:var(--text-muted)"><?= $unreadCount ?> unread updates</span>
        </div>

        <div class="notif-list" id="notifList">
          <?php if (empty($db_notifs)): ?>
            <div style="padding: 60px; text-align: center; color: var(--text-muted);">
                <div style="font-size: 3rem; margin-bottom: 10px;">🔔</div>
                <p>You have no notifications at this time.</p>
            </div>
          <?php else: ?>
            <?php foreach ($db_notifs as $n): 
              $isUnread = !$n['student_is_viewed'];
              $config = getStudentNotifConfig($n['status']);
            ?>
            
            <div class="notif-item <?= $isUnread ? 'unread' : '' ?>" 
                 data-read="<?= $isUnread ? 'unread' : 'read' ?>"
                 onclick="markSingleRead(<?= $n['id'] ?>)">
              
              <div class="notif-item__dot <?= $isUnread ? '' : 'read' ?>"></div>
              
              <div style="font-size:1.5rem; flex-shrink:0; width:40px; text-align:center;">
                  <?= $config['icon'] ?>
              </div>
              
              <div class="notif-item__body">
                <div class="notif-item__title">
                    <span style="color:var(--crimson); font-weight:700; font-size:0.7rem; letter-spacing:0.05em;">[<?= $n['reference_number'] ?>]</span>
                    <?= $config['label'] ?>: <?= htmlspecialchars($n['document_name']) ?>
                </div>
                <div class="notif-item__desc">
                    <?= $config['desc'] ?> Current Status: <strong><?= ucfirst($n['status']) ?></strong>
                </div>
              </div>
              
              <div class="notif-item__time">
                  <?= date('M d, g:i A', strtotime($n['created_at'])) ?>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </main>
</div>

<?php include 'includes/footer.php'; ?>

<script>
/**
 * Tab Filtering
 */
document.querySelectorAll('.notif-tab').forEach(btn => {
  btn.addEventListener('click', () => {
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

/**
 * AJAX: Mark Single Read and Redirect
 */
function markSingleRead(id) {
    fetch('mark_student_notif_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    }).finally(() => {
        window.location.href = 'student_dashboard.php';
    });
}

/**
 * AJAX: Mark All Read
 */
function markAllRead() {
    fetch('mark_student_notifs_read_all.php', { method: 'POST' })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            document.querySelectorAll('.notif-item.unread').forEach(el => {
                el.classList.remove('unread');
                el.dataset.read = 'read';
                const dot = el.querySelector('.notif-item__dot');
                if(dot) dot.classList.add('read');
            });
            document.querySelectorAll('.badge-count').forEach(b => b.style.display = 'none');
        }
    });
}

/**
 * Mark Single Read and Redirect
 * Added a small delay and error handling to ensure redirect happens
 */
function markSingleRead(id) {
    console.log("Marking as read: " + id);

    // 1. Start the database update
    fetch('mark_student_notif_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(response => {
        // Log for debugging
        return response.text(); 
    })
    .then(data => {
        console.log("Server Response:", data);
        // 2. Redirect to dashboard
        window.location.href = 'student_dashboard.php';
    })
    .catch(err => {
        console.error("Fetch Error:", err);
        // 3. Failsafe: Redirect anyway if there's a network error
        window.location.href = 'student_dashboard.php';
    });
}
</script>
</body>
</html>