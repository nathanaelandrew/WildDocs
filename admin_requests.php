<?php
// admin_requests.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php'); exit;
}

$pdo = getDB();

// ── Auto-delete notifications older than 1 week ─────────────────────
$pdo->exec("DELETE FROM requests 
             WHERE status = 'pending' 
               AND is_viewed = TRUE 
               AND created_at < NOW() - INTERVAL '7 days'
               AND id NOT IN (SELECT request_id FROM payments)");

// ── Filters ──────────────────────────────────────────────────────────
$search       = trim($_GET['search'] ?? '');
$filterStatus = $_GET['status'] ?? '';

// ── Pagination Settings ──────────────────────────────────────────────
$perPage    = 10;
$page       = max(1, (int)($_GET['page'] ?? 1));
$offset     = ($page - 1) * $perPage;

// ── Base query (count) ───────────────────────────────────────────────
$countSql = "SELECT COUNT(*) FROM requests r
             JOIN users u ON r.user_id = u.id
             LEFT JOIN students s ON u.id = s.user_id
             JOIN document_types dt ON r.document_type_id = dt.id
             WHERE r.status != 'released'";

$params = [];

if (!empty($search)) {
    $countSql .= " AND (r.reference_number LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR s.student_number LIKE ?)";
    $t = "%$search%";
    array_push($params, $t, $t, $t, $t);
}
if (!empty($filterStatus)) {
    $countSql .= " AND r.status = ?";
    $params[] = $filterStatus;
}

// Safely evaluate total rows matching conditions
$totalRows = (function() use ($pdo, $countSql, $params) {
    $s = $pdo->prepare($countSql); 
    $s->execute($params); 
    return (int)$s->fetchColumn();
})();

$totalPages = max(1, (int)ceil($totalRows / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

// ── Main query (paginated) ───────────────────────────────────────────
$sql = "SELECT r.*, u.first_name, u.last_name,
               s.student_number, s.program, s.year_level,
               dt.name as document_name
        FROM requests r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN students s ON u.id = s.user_id
        JOIN document_types dt ON r.document_type_id = dt.id
        WHERE r.status != 'released'";

if (!empty($search)) {
    $sql .= " AND (r.reference_number LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR s.student_number LIKE ?)";
}
if (!empty($filterStatus)) {
    $sql .= " AND r.status = ?";
}

// Append ordering rules and limit conditions
$sql .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";

$stmt = $pdo->prepare($sql);

// FIX: Explicitly bind the variable parameters step-by-step to preserve numerical types for Postgres
$paramIndex = 1;
foreach ($params as $value) {
    $stmt->bindValue($paramIndex++, $value, PDO::PARAM_STR);
}

// Strictly bind LIMIT and OFFSET as structural integers to satisfy HY093/PostgreSQL requirements
$stmt->bindValue($paramIndex++, $perPage, PDO::PARAM_INT);
$stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);

$stmt->execute();
$requests = $stmt->fetchAll();

function getStatusBadgeClass($status) {
    return match($status) {
        'pending'  => 'badge-pending',
        'paid'     => 'badge-paid',
        'approved' => 'badge-progress',
        default    => 'badge-pending',
    };
}

// Build pagination URL helper
function pageUrl($p) {
    $q = $_GET; $q['page'] = $p;
    return '?' . http_build_query($q);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Active Requests – Admin</title>
  <link rel="stylesheet" href="css/styles.css">
  <style>
    .badge-paid { background:#EFF6FF; color:#1D4ED8; }
    .btn-release-action {
        color: white; border: none; padding: 6px 12px; border-radius: 6px;
        cursor: pointer; font-weight: 700; font-size: 0.75rem; min-width: 80px;
        transition: background .2s;
    }
  </style>
</head>
<body>

<?php include 'includes/admin_navbar.php'; ?>

<div class="app-layout">
  <?php include 'includes/admin_sidebar.php'; ?>

  <main class="main-content" style="background:var(--bg-light);min-height:100vh;padding-bottom:80px">
    <div class="dashboard-page">

      <div class="page-title-row">
        <div>
          <h2>Document Processing</h2>
          <p>Update statuses below. Once a document is <strong>Approved</strong>, you can <strong>Release</strong> it to the archive.</p>
        </div>
        <a href="admin_archive.php" class="btn btn-ghost">View Released</a>
      </div>

      <form method="GET" style="display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
               placeholder="Search name, ref #, student ID…" class="form-control" style="max-width:260px">
        <select name="status" class="form-control" style="max-width:160px">
          <option value="">All Statuses</option>
          <option value="pending"  <?= $filterStatus==='pending'  ?'selected':'' ?>>Pending</option>
          <option value="paid"     <?= $filterStatus==='paid'     ?'selected':'' ?>>Paid</option>
          <option value="approved" <?= $filterStatus==='approved' ?'selected':'' ?>>Approved</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        <?php if ($search || $filterStatus): ?>
          <a href="admin_requests.php" class="btn btn-ghost">Clear</a>
        <?php endif; ?>
      </form>

      <div class="card">
        <div class="card__body" style="padding:0">
          <table class="data-table" style="table-layout:fixed;width:100%">
            <colgroup>
              <col style="width:150px">
              <col style="width:160px">
              <col style="width:160px">
              <col style="width:110px">
              <col style="width:110px">
              <col style="width:240px">
            </colgroup>
            <thead>
              <tr>
                <th>Ref #</th>
                <th>Student Name</th>
                <th>Document</th>
                <th>Date</th>
                <th style="text-align:center">Status</th>
                <th style="text-align:center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($requests)): ?>
              <tr>
                <td colspan="6" style="text-align:center;padding:50px;color:var(--text-muted)">
                  No requests found.
                </td>
              </tr>
              <?php ?>
              <?php else: ?>
              <?php foreach ($requests as $r): ?>
              <tr>
                <td style="font-weight:700;color:var(--crimson);font-size:.82rem">
                  <?= htmlspecialchars($r['reference_number']) ?>
                </td>
                <td><?= htmlspecialchars($r['first_name'].' '.$r['last_name']) ?></td>
                <td style="font-size:.88rem"><?= htmlspecialchars($r['document_name']) ?></td>
                <td style="font-size:.82rem;color:var(--text-muted)">
                  <?= date('M d, Y', strtotime($r['created_at'])) ?>
                </td>
                <td style="text-align:center">
                  <span class="badge <?= getStatusBadgeClass($r['status']) ?>">
                    <?= ucfirst($r['status']) ?>
                  </span>
                </td>
                <td class="col-actions">
                  <div class="col-actions-inner">
                    <select class="status-select"
                            data-current="<?= htmlspecialchars($r['status']) ?>"
                            onchange="handleDropdownChange(<?= $r['id'] ?>, this)">
                      <option value="pending"  <?= strtolower($r['status'])==='pending'  ?'selected':'' ?>>Pending</option>
                      <option value="paid"     <?= strtolower($r['status'])==='paid'     ?'selected':'' ?>>Paid</option>
                      <option value="approved" <?= strtolower($r['status'])==='approved' ?'selected':'' ?>>Approved</option>
                    </select>

                    <?php $isApproved = trim(strtolower($r['status'])) === 'approved'; ?>
                    <button id="release-btn-<?= $r['id'] ?>"
                            type="button"
                            class="btn-release-action"
                            style="background-color:<?= $isApproved ? '#16a34a' : '#e2e8f0' ?>;
                                   color:<?= $isApproved ? '#fff' : '#94a3b8' ?>;
                                   cursor:<?= $isApproved ? 'pointer' : 'not-allowed' ?>;
                                   opacity:<?= $isApproved ? '1' : '0.7' ?>"
                            <?= !$isApproved ? 'disabled' : '' ?>
                            onclick="confirmRelease(<?= $r['id'] ?>)">
                      Release
                    </button>

                    <button type="button" class="btn btn-ghost btn-sm"
                            onclick='viewDetails(<?= json_encode($r) ?>)'>Details</button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>

          <?php if ($totalPages > 1): ?>
          <div class="pagination">
            <span class="pagination__info">
              Showing <?= $offset + 1 ?>–<?= min($offset + $perPage, $totalRows) ?> of <?= $totalRows ?> requests
            </span>
            <div class="pagination__controls">
              <a href="<?= pageUrl(1) ?>" class="page-btn" <?= $page <= 1 ? 'aria-disabled="true"' : '' ?>>«</a>
              <a href="<?= pageUrl(max(1, $page - 1)) ?>" class="page-btn" <?= $page <= 1 ? 'aria-disabled="true"' : '' ?>>‹</a>
              <?php
              $range = 2;
              for ($i = 1; $i <= $totalPages; $i++):
                if ($i === 1 || $i === $totalPages || abs($i - $page) <= $range):
              ?>
                <a href="<?= pageUrl($i) ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
              <?php
                elseif (abs($i - $page) === $range + 1):
              ?>
                <span class="page-btn page-btn--dots">…</span>
              <?php endif; endfor; ?>
              <a href="<?= pageUrl(min($totalPages, $page + 1)) ?>" class="page-btn" <?= $page >= $totalPages ? 'aria-disabled="true"' : '' ?>>›</a>
              <a href="<?= pageUrl($totalPages) ?>" class="page-btn" <?= $page >= $totalPages ? 'aria-disabled="true"' : '' ?>>»</a>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div></div>
  </main>
</div>

<div class="modal-overlay" id="detailsModal">
  <div class="modal" style="max-width:560px;text-align:left">
    <h3 style="margin-bottom:18px">Request Details</h3>
    <div class="modal-info-grid" id="detailsContent"></div>
    <div style="text-align:right;margin-top:24px">
      <button class="btn btn-ghost" onclick="document.getElementById('detailsModal').classList.remove('open')">Close</button>
    </div>
  </div>
</div>

<script>
function handleDropdownChange(requestId, select) {
    const releaseBtn = document.getElementById('release-btn-' + requestId);
    const newStatus  = select.value.toLowerCase();
    const oldStatus  = select.getAttribute('data-current');

    WD.confirm(
        'Update Status?',
        'Change this request to ' + newStatus.toUpperCase() + '?',
        function() {
            performFetch(requestId, newStatus, select);
            if (newStatus === 'approved') {
                releaseBtn.disabled = false;
                releaseBtn.style.cssText = 'background-color:#16a34a;color:#fff;cursor:pointer;opacity:1';
            } else {
                releaseBtn.disabled = true;
                releaseBtn.style.cssText = 'background-color:#e2e8f0;color:#94a3b8;cursor:not-allowed;opacity:0.7';
            }
        },
        { icon: '🔄' }
    );

    select.value = oldStatus;
    if (oldStatus === 'approved') {
        releaseBtn.disabled = false;
        releaseBtn.style.cssText = 'background-color:#16a34a;color:#fff;cursor:pointer;opacity:1';
    } else {
        releaseBtn.disabled = true;
        releaseBtn.style.cssText = 'background-color:#e2e8f0;color:#94a3b8;cursor:not-allowed;opacity:0.7';
    }
}

function confirmRelease(requestId) {
    WD.confirm(
        'Complete & Release?',
        'Mark this document as released and move it to the archive? This cannot be undone.',
        function() { performFetch(requestId, 'released', null); },
        { danger: true, okLabel: 'Yes, Release', icon: '📦' }
    );
}

function performFetch(requestId, status, select) {
    fetch('update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ requestId, status })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            WD.toast('success', status === 'released' ? 'Moved to archive.' : 'Status updated to ' + status + '.');
            setTimeout(() => location.reload(), 1200);
        } else {
            WD.toast('error', data.message || 'Update failed.');
            if (select) select.value = select.getAttribute('data-current');
        }
    })
    .catch(() => { WD.toast('error', 'Network error. Please try again.'); });
}

function viewDetails(r) {
    const fields = [
        ['Reference #',  r.reference_number],
        ['Student',      (r.first_name||'') + ' ' + (r.last_name||'')],
        ['Student #',    r.student_number || '—'],
        ['Program',      r.program || '—'],
        ['Document',     r.document_name],
        ['Status',       r.status],
        ['Date Filed',   r.created_at ? r.created_at.substring(0,10) : '—'],
    ];
    let html = '<div class="modal-info-grid">';
    fields.forEach(([label, val]) => {
        html += `<div><div class="modal-label">${label}</div><div class="modal-value">${val ?? '—'}</div></div>`;
    });
    html += '</div>';
    document.getElementById('detailsContent').innerHTML = html;
    document.getElementById('detailsModal').classList.add('open');
}

document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });
});
</script>

<?php include 'includes/footer.php'; ?>

<script>
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