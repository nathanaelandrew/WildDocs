<?php
// student_notifications.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: login.php'); exit;
}

$pdo    = getDB();
$userId = $_SESSION['user_id'];

// ── Auto-delete: remove this student's viewed released notifications older than 7 days ──
// FIX: Changed INTERVAL syntax and switched integer 1 to boolean TRUE
$pdo->prepare("
    DELETE FROM requests
    WHERE user_id = ?
      AND student_is_viewed = TRUE
      AND status = 'released'
      AND created_at < NOW() - INTERVAL '7 days'
")->execute([$userId]);

// ── Pagination + tab filter ───────────────────────────────────────────
$perPage   = 10;
$page      = max(1, (int)($_GET['page'] ?? 1));
$filterTab = $_GET['filter'] ?? 'all';

// Count query
$countSql    = "SELECT COUNT(*) FROM requests r
                JOIN document_types dt ON r.document_type_id = dt.id
                WHERE r.user_id = ?";
$countParams = [$userId];

// FIX: Changed = 0 / = 1 to IS FALSE / IS TRUE
if ($filterTab === 'unread') { $countSql .= " AND r.student_is_viewed = FALSE"; }
elseif ($filterTab === 'read') { $countSql .= " AND r.student_is_viewed = TRUE"; }

$cs = $pdo->prepare($countSql); $cs->execute($countParams);
$totalRows  = (int)$cs->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

// Data query
$dataSql = "SELECT r.id, r.reference_number, r.student_is_viewed, r.created_at, r.status,
                    dt.name as document_name
            FROM requests r
            JOIN document_types dt ON r.document_type_id = dt.id
            WHERE r.user_id = ?";
$dataParams = [$userId];

// FIX: Changed = 0 / = 1 to IS FALSE / IS TRUE
if ($filterTab === 'unread') { $dataSql .= " AND r.student_is_viewed = FALSE"; }
elseif ($filterTab === 'read') { $dataSql .= " AND r.student_is_viewed = TRUE"; }

// SAFETY FIX: Appending integers via placeholders to keep Postgres happy with type assignments
$dataSql .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
$dataParams[] = $perPage;
$dataParams[] = $offset;

$ds = $pdo->prepare($dataSql); $ds->execute($dataParams);
$db_notifs = $ds->fetchAll();

// Unread count (always full)
// FIX: Changed = 0 to FALSE
$uStmt = $pdo->prepare("SELECT COUNT(*) FROM requests WHERE user_id = ? AND student_is_viewed = FALSE");
$uStmt->execute([$userId]);
$unreadCount = (int)$uStmt->fetchColumn();

function getStudentNotifConfig($status) {
    return match($status) {
        'pending'  => ['icon' => '⏳', 'label' => 'Request Received',    'desc' => 'Your request is awaiting review.'],
        'paid'     => ['icon' => '💳', 'label' => 'Payment Submitted',   'desc' => 'We received your payment and are verifying.'],
        'approved' => ['icon' => '✅', 'label' => 'Request Approved',    'desc' => 'Your document is now being processed by the registrar.'],
        'released' => ['icon' => '📦', 'label' => 'Ready for Pickup',    'desc' => 'Your document is released! Please visit the office.'],
        default    => ['icon' => '🔔', 'label' => 'Status Update',       'desc' => 'There has been an update to your request.'],
    };
}

function pageUrl($p) {
    $q = $_GET; $q['page'] = $p;
    return '?' . http_build_query($q);
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
    .badge-count { background:var(--crimson); color:#fff; border-radius:50px; padding:1px 7px; font-size:.7rem; margin-left:4px; }
    .notif-item { cursor:pointer; transition:background .2s; position:relative; border-left:3px solid transparent; display:flex; align-items:flex-start; gap:14px; padding:16px 20px; }
    .notif-item:hover { background:var(--pink-bg); }
    .notif-item.unread { border-left-color:var(--crimson); background:#FFF5F6; }
    .notif-item__dot { width:8px; height:8px; border-radius:50%; background:var(--crimson); flex-shrink:0; margin-top:5px; }
    .notif-item__dot.read { background:var(--border); }
    .notif-item + .notif-item { border-top:1px solid var(--border-light); }
    .tab-bar { display:flex; gap:8px; margin-bottom:18px; flex-wrap:wrap; }
  </style>
</head>
<body>

<?php include 'includes/student_navbar.php'; ?>

<div class="app-layout">
  <?php include 'includes/student_sidebar.php'; ?>

  <main class="main-content" style="background:var(--bg-light);min-height:100vh;padding-bottom:50px">
    <div class="dashboard-page">

      <div class="page-title-row">
        <div>
          <h2 style="margin:0;color:var(--crimson)">My Notifications</h2>
        </div>
        <?php if ($unreadCount > 0): ?>
          <button class="btn btn-ghost btn-sm" onclick="markAllRead()">✓ Mark all as read</button>
        <?php endif; ?>
      </div>

      <!-- Filter tabs (GET-based so pagination is preserved) -->
      <div class="tab-bar">
        <?php
        $tabs = ['all' => 'All', 'unread' => 'Unread', 'read' => 'Read'];
        foreach ($tabs as $key => $label):
          $active = ($filterTab === $key) ? 'btn-primary' : 'btn-ghost';
          $url    = '?' . http_build_query(array_merge($_GET, ['filter' => $key, 'page' => 1]));
        ?>
          <a href="<?= $url ?>" class="btn btn-sm <?= $active ?>">
            <?= $label ?>
            <?php if ($key === 'unread' && $unreadCount > 0): ?>
              <span class="badge-count"><?= $unreadCount ?></span>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>

      <div class="card">
        <div class="card__header">
          <h3>Recent Updates</h3>
          <span style="font-size:.8rem;color:var(--text-muted)"><?= $unreadCount ?> unread</span>
        </div>

        <div id="notifList">
          <?php if (empty($db_notifs)): ?>
            <div style="padding:60px;text-align:center;color:var(--text-muted)">
              <div style="font-size:3rem;margin-bottom:10px">🔔</div>
              <p>No notifications at this time.</p>
            </div>
          <?php else: ?>
            <?php foreach ($db_notifs as $n):
              $isUnread = !$n['student_is_viewed'];
              $config   = getStudentNotifConfig($n['status']);
            ?>
            <div class="notif-item <?= $isUnread ? 'unread' : '' ?>"
                 data-id="<?= $n['id'] ?>"
                 onclick="markSingleRead(<?= $n['id'] ?>)">

              <div class="notif-item__dot <?= $isUnread ? '' : 'read' ?>"></div>
              <div style="font-size:1.5rem;flex-shrink:0;width:36px;text-align:center"><?= $config['icon'] ?></div>

              <div class="notif-item__body" style="flex:1">
                <div class="notif-item__title">
                  <span style="color:var(--crimson);font-weight:700;font-size:.7rem;letter-spacing:.05em">
                    [<?= htmlspecialchars($n['reference_number']) ?>]
                  </span>
                  <?= $config['label'] ?>: <?= htmlspecialchars($n['document_name']) ?>
                </div>
                <div class="notif-item__desc">
                  <?= $config['desc'] ?> &nbsp;
                  Status: <strong><?= ucfirst($n['status']) ?></strong>
                </div>
              </div>

              <div class="notif-item__time"><?= date('M d, g:i A', strtotime($n['created_at'])) ?></div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <span class="pagination__info">Page <?= $page ?> of <?= $totalPages ?></span>
          <div class="pagination__controls">
            <a href="<?= pageUrl(1) ?>" class="page-btn">«</a>
            <a href="<?= pageUrl(max(1,$page-1)) ?>" class="page-btn">‹</a>
            <?php for ($i=1;$i<=$totalPages;$i++): ?>
              <?php if ($i===1||$i===$totalPages||abs($i-$page)<=2): ?>
                <a href="<?= pageUrl($i) ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
              <?php elseif(abs($i-$page)===3): ?>
                <span class="page-btn page-btn--dots">…</span>
              <?php endif; ?>
            <?php endfor; ?>
            <a href="<?= pageUrl(min($totalPages,$page+1)) ?>" class="page-btn">›</a>
            <a href="<?= pageUrl($totalPages) ?>" class="page-btn">»</a>
          </div>
        </div>
        <?php endif; ?>
      </div><!-- /.card -->

    </div>
  </main>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function markAllRead() {
    fetch('mark_student_notifs_read_all.php', { method: 'POST' })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.querySelectorAll('.notif-item.unread').forEach(el => {
                el.classList.remove('unread');
                const dot = el.querySelector('.notif-item__dot');
                if (dot) { dot.classList.add('read'); }
            });
            document.querySelectorAll('.badge-count').forEach(b => b.textContent = '0');
            WD.toast('success', 'All notifications marked as read.');
        } else {
            WD.toast('error', 'Failed to update notifications.');
        }
    })
    .catch(() => WD.toast('error', 'Network error.'));
}

function markSingleRead(id) {
    fetch('mark_student_notif_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    }).finally(() => {
        window.location.href = 'student_dashboard.php';
    });
}
</script>

<script>
/* ── WD inline helpers (replaces flash.php) ──────────────────── */
const WD = (() => {
    let _toastTimer = null;
    let _confirmCb  = null;
    const ICONS = { success:'✅', error:'❌', info:'ℹ️', warning:'⚠️' };

    function toast(type, msg, duration = 4000) {
        let el = document.getElementById('wd-flash-toast');
        if (!el) {
            el = document.createElement('div');
            el.id = 'wd-flash-toast';
            el.innerHTML = '<span class="toast-icon" id="wd-toast-icon"></span><span id="wd-toast-msg"></span><button class="toast-close" onclick="WD._closeToast()" aria-label="Close">✕</button>';
            document.body.appendChild(el);
        }
        el.className = '';
        el.classList.add('show', 'toast-' + type);
        document.getElementById('wd-toast-icon').textContent = ICONS[type] ?? '🔔';
        document.getElementById('wd-toast-msg').textContent  = msg;
        clearTimeout(_toastTimer);
        _toastTimer = setTimeout(_closeToast, duration);
    }

    function _closeToast() {
        const el = document.getElementById('wd-flash-toast');
        if (el) el.classList.remove('show');
    }

    function confirm(title, body, onOk, opts = {}) {
        let overlay = document.getElementById('wd-confirm-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'wd-confirm-overlay';
            overlay.innerHTML = `<div id="wd-confirm-box">
                <div class="confirm-icon" id="wd-confirm-icon">⚠️</div>
                <h3 id="wd-confirm-title">Are you sure?</h3>
                <p id="wd-confirm-body">This action cannot be undone.</p>
                <div class="confirm-actions">
                    <button class="btn btn-ghost" id="wd-confirm-cancel">Cancel</button>
                    <button class="btn btn-danger" id="wd-confirm-ok">Confirm</button>
                </div>
            </div>`;
            document.body.appendChild(overlay);
            document.getElementById('wd-confirm-cancel').addEventListener('click', () => {
                overlay.classList.remove('open'); _confirmCb = null;
            });
            document.getElementById('wd-confirm-ok').addEventListener('click', () => {
                overlay.classList.remove('open');
                if (typeof _confirmCb === 'function') _confirmCb();
                _confirmCb = null;
            });
            overlay.addEventListener('click', function(e) {
                if (e.target === this) { this.classList.remove('open'); _confirmCb = null; }
            });
        }
        document.getElementById('wd-confirm-title').textContent = title;
        document.getElementById('wd-confirm-body').textContent  = body;
        document.getElementById('wd-confirm-icon').textContent  = opts.icon ?? (opts.danger ? '🗑️' : '⚠️');
        const okBtn = document.getElementById('wd-confirm-ok');
        okBtn.className   = 'btn ' + (opts.danger ? 'btn-danger' : 'btn-primary');
        okBtn.textContent = opts.okLabel ?? 'Confirm';
        _confirmCb = onOk;
        overlay.classList.add('open');
    }

    return { toast, confirm, _closeToast };
})();
</script>
</body>
</html>