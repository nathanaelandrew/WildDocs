<?php
// student_dashboard.php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: login.php'); exit;
}

require_once 'includes/db.php';

$pdo    = getDB();
$userId = $_SESSION['user_id'];

// ── Pagination ────────────────────────────────────────────────────────
$perPage = 10;
$page    = max(1, (int)($_GET['page'] ?? 1));

// Total count for stats (all, unpaginated)
$allRequests = fetchStudentRequests($pdo, $userId);
$total    = count($allRequests);
$statuses = array_column($allRequests, 'status');
$pending  = count(array_filter($statuses, fn($s) => $s === 'pending'));
$paid     = count(array_filter($statuses, fn($s) => $s === 'paid'));
$approved = count(array_filter($statuses, fn($s) => $s === 'approved'));
$released = count(array_filter($statuses, fn($s) => $s === 'released'));

// Paginated slice
$totalPages   = max(1, (int)ceil($total / $perPage));
$page         = min($page, $totalPages);
$offset       = ($page - 1) * $perPage;
$requests     = array_slice($allRequests, $offset, $perPage);

function statusBadge(string $status): string {
    return match($status) {
        'pending'  => 'badge-pending',
        'paid'     => 'badge-paid',
        'approved' => 'badge-progress',
        'released' => 'badge-completed',
        default    => 'badge-pending',
    };
}

function statusLabel(string $status): string {
    return match($status) {
        'pending'  => 'Pending',
        'paid'     => 'Paid',
        'approved' => 'Approved',
        'released' => 'Released',
        default    => ucfirst($status),
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
  <title>My Dashboard — WildDocuments</title>
  <link rel="stylesheet" href="css/styles.css">
  <style>
    .badge-paid { background:#EFF6FF; color:#1D4ED8; }
    .col-action { display:flex; gap:6px; justify-content:center; align-items:center; white-space:nowrap; }
  </style>
</head>
<body>

<?php include 'includes/student_navbar.php'; ?>

<div class="app-layout">
  <?php include 'includes/student_sidebar.php'; ?>

  <main style="padding:40px 0 64px;flex:1;background:var(--bg-light);overflow-x:hidden">
    <div class="container">

      <!-- Header -->
      <div class="welcome-banner" style="margin-bottom:28px">
        <div>
          <h2 style="color:white;margin-bottom:4px">My Document Requests</h2>
          <p style="color:rgba(255,255,255,0.75)">
            Welcome back, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>. Track your document requests here.
          </p>
        </div>
        <a href="student_request.php" class="btn btn-primary"
           style="background:white;color:var(--crimson);border:none">+ New Request</a>
      </div>

      <!-- Stats Grid -->
      <div class="stats-grid" style="margin-bottom:28px">
        <div class="stat-card">
          <div class="stat-card__label">Total Requests</div>
          <div class="stat-card__value"><?= $total ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Pending Payment</div>
          <div class="stat-card__value" style="color:#8A6000"><?= $pending ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">In Process</div>
          <div class="stat-card__value" style="color:#1D4ED8"><?= $paid + $approved ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Released</div>
          <div class="stat-card__value" style="color:#1A6E3C"><?= $released ?></div>
        </div>
      </div>

      <!-- Requests Table -->
      <div class="card">
        <div class="card__header">
          <h3>My Request History</h3>
          <span style="font-size:.8rem;color:var(--text-muted)"><?= $total ?> total records</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="table-wrapper" style="border:none;border-radius:0">
            <table class="data-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Reference</th>
                  <th>Document</th>
                  <th style="text-align:center">Copies</th>
                  <th>Purpose</th>
                  <th>Amount</th>
                  <th>Date Filed</th>
                  <th style="text-align:center">Status</th>
                  <th style="text-align:center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($requests)): ?>
                <tr>
                  <td colspan="9" style="text-align:center;padding:60px;color:var(--text-muted)">
                    <div style="font-size:2rem;margin-bottom:10px">📄</div>
                    <p>No document requests yet.
                       <a href="student_request.php" style="color:var(--crimson);font-weight:600">Start a new request →</a>
                    </p>
                  </td>
                </tr>
                <?php else: ?>
                <?php foreach ($requests as $i => $r): ?>
                <tr>
                  <td class="col-id"><?= $offset + $i + 1 ?></td>
                  <td style="font-weight:700;color:var(--crimson);font-size:.82rem">
                    <?= htmlspecialchars($r['reference_number']) ?>
                  </td>
                  <td style="font-weight:600"><?= htmlspecialchars($r['document_name']) ?></td>
                  <td style="text-align:center"><?= $r['copies'] ?></td>
                  <td style="font-size:.85rem;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"
                      title="<?= htmlspecialchars($r['purpose'] ?? '') ?>">
                    <?= htmlspecialchars($r['purpose'] ?? '—') ?>
                  </td>
                  <td style="font-weight:700">₱<?= number_format($r['total_amount'], 2) ?></td>
                  <td class="col-id"><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
                  <td style="text-align:center">
                    <span class="badge <?= statusBadge($r['status']) ?>"><?= statusLabel($r['status']) ?></span>
                  </td>
                  <td>
                    <div class="col-action">
                      <?php if ($r['status'] === 'released'): ?>
                        <button class="btn btn-secondary btn-sm"
                                onclick="triggerDownload('<?= htmlspecialchars(addslashes($r['document_name'])) ?>')">
                          ⬇ Download
                        </button>
                      <?php else: ?>
                        <button class="btn btn-sm" style="opacity:.35;cursor:not-allowed" disabled
                                title="Wait for document to be Released">
                          ⬇ Download
                        </button>
                      <?php endif; ?>

                      <?php if ($r['status'] === 'pending'): ?>
                        <button class="btn btn-danger btn-sm"
                                onclick="cancelRequest(<?= (int)$r['id'] ?>,
                                  '<?= htmlspecialchars(addslashes($r['reference_number'])) ?>')">
                          Cancel
                        </button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <?php if ($totalPages > 1): ?>
          <div class="pagination">
            <span class="pagination__info">
              Showing <?= $offset + 1 ?>–<?= min($offset + $perPage, $total) ?> of <?= $total ?> requests
            </span>
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
        </div>
      </div>

    </div>
  </main>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// ── Download simulation ───────────────────────────────
function triggerDownload(docName) {
    WD.toast('success', 'Downloaded: ' + docName, 5000);
}

// ── Cancel request ────────────────────────────────────
function cancelRequest(requestId, refNo) {
    WD.confirm(
        'Cancel Request?',
        `Cancel request ${refNo}? This will permanently remove the record and cannot be undone.`,
        function() {
            const allBtns = document.querySelectorAll('[onclick*="cancelRequest(' + requestId + '"]');
            allBtns.forEach(b => { b.disabled = true; b.textContent = '…'; });

            fetch('cancel_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: requestId })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    WD.toast('success', 'Request cancelled successfully.');
                    setTimeout(() => location.reload(), 1200);
                } else {
                    WD.toast('error', 'Error: ' + (data.message || 'Could not cancel.'));
                    allBtns.forEach(b => { b.disabled = false; b.textContent = 'Cancel'; });
                }
            })
            .catch(() => {
                WD.toast('error', 'Network error. Please try again.');
                allBtns.forEach(b => { b.disabled = false; b.textContent = 'Cancel'; });
            });
        },
        { danger: true, okLabel: 'Yes, Cancel It', icon: '🗑️' }
    );
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