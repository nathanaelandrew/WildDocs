<?php
// admin_notifications.php
session_start();
require_once 'includes/db.php';

// Auth check: Only allow logged-in admins
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { 
    header('Location: login.php'); 
    exit; 
}

$pdo = getDB();

/**
 * 1. Fetch real notifications from the database.
 * JOIN logic:
 * - JOIN users: to get Student Name
 * - JOIN document_types: to get the Document Name (Normalization fix)
 */
$stmt = $pdo->query("
    SELECT 
        r.id, 
        r.reference_number, 
        r.is_viewed, 
        r.created_at, 
        r.status, 
        u.first_name, 
        u.last_name,
        dt.name as document_name
    FROM requests r
    JOIN users u ON r.user_id = u.id
    JOIN document_types dt ON r.document_type_id = dt.id
    ORDER BY r.created_at DESC 
    LIMIT 50
");
$db_notifs = $stmt->fetchAll();

// 2. Count unread for the tab badge
$unreadCount = 0;
foreach($db_notifs as $n) {
    if(!$n['is_viewed']) $unreadCount++;
}

/**
 * Helper: Status-based icons and dynamic messages
 */
function getNotifConfig($status) {
    return match($status) {
        'pending'  => ['icon' => '📋', 'label' => 'New Request'],
        'paid'     => ['icon' => '💳', 'label' => 'Payment Confirmed'],
        'approved' => ['icon' => '✅', 'label' => 'Request Approved'],
        'released' => ['icon' => '📦', 'label' => 'Document Released'],
        default    => ['icon' => '🔔', 'label' => 'Status Update'],
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notifications – WildDocuments Admin</title>
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

<?php include 'includes/admin_navbar.php'; ?>

<div class="app-layout">
  <?php include 'includes/admin_sidebar.php'; ?>

  <main class="main-content" style="background: var(--bg-light); min-height: 100vh; padding-bottom: 50px;">
    <div class="dashboard-page">

      <div class="page-title-row">
        <div>
          <h2 style="margin:0; color: var(--crimson);">System Activity</h2>
          <p>Recent updates for <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></p>
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
          <h3>Recent Notifications</h3>
          <span style="font-size:.8rem; color:var(--text-muted)"><?= $unreadCount ?> unread items</span>
        </div>

        <div class="notif-list" id="notifList">
          <?php if (empty($db_notifs)): ?>
            <div style="padding: 60px; text-align: center; color: var(--text-muted);">
                <div style="font-size: 3rem; margin-bottom: 10px;">📭</div>
                <p>No activity recorded yet.</p>
            </div>
          <?php else: ?>
            <?php foreach ($db_notifs as $n): 
              $isUnread = !$n['is_viewed'];
              $config = getNotifConfig($n['status']);
            ?>
            
            <div class="notif-item <?= $isUnread ? 'unread' : '' ?>" 
              data-read="<?= $isUnread ? 'unread' : 'read' ?>"
              onclick="markSingleRead(<?= $n['id'] ?>, '<?= urlencode($n['reference_number']) ?>')"
              title="Click to manage request <?= $n['reference_number'] ?>">
              
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
                    Student <strong><?= htmlspecialchars($n['first_name'].' '.$n['last_name']) ?></strong> 
                    is currently at the <u><?= ucfirst($n['status']) ?></u> stage.
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
 * Tab Filtering Logic
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
 * Mark All Read (AJAX)
 */
function markAllRead() {
    fetch('mark_notifs_read.php', { method: 'POST' })
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
 * Mark an individual notification as read and then redirect
 */
function markSingleRead(id, ref) {
    // 1. Send AJAX to mark this specific ID as viewed
    fetch('mark_single_notif_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .finally(() => {
        // 2. Regardless of success/fail, redirect the admin to the request page
        window.location.href = 'admin_requests.php?search=' + ref;
    });
}
</script>
</body>
</html>