<?php
session_start();
require_once 'includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php'); exit;
}

$pdo = getDB();

// Handle Approval via POST (not GET) to prevent CSRF via URL bar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_id'])) {
    $payId = (int)$_POST['approve_id'];
    try {
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE payments SET status = 'completed' WHERE id = ?")->execute([$payId]);
        $pdo->prepare("UPDATE requests SET status = 'approved', student_is_viewed = FALSE
                       WHERE id = (SELECT request_id FROM payments WHERE id = ?)")->execute([$payId]);
        $pdo->commit();
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Payment verified and request approved.'];
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Error: ' . $e->getMessage()];
    }
    header('Location: admin_payments.php'); exit;
}

// ── Pagination ────────────────────────────────────────────────────────
$perPage    = 10;
$page       = max(1, (int)($_GET['page'] ?? 1));

$totalRows  = (int)$pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'pending'")->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

$stmt = $pdo->prepare(
    "SELECT p.*, r.reference_number, u.first_name, u.last_name, dt.name as doc_name
     FROM payments p
     JOIN requests r ON p.request_id = r.id
     JOIN users u ON r.user_id = u.id
     JOIN document_types dt ON r.document_type_id = dt.id
     WHERE p.status = 'pending'
     ORDER BY p.payment_date DESC
     LIMIT $perPage OFFSET $offset"
);
$stmt->execute();
$pending_payments = $stmt->fetchAll();

function pageUrl($p) {
    $q = $_GET; $q['page'] = $p;
    return '?' . http_build_query($q);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Payments — Admin</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include 'includes/admin_navbar.php'; ?>

<div class="app-layout">
    <?php include 'includes/admin_sidebar.php'; ?>
    <main class="main-content" style="background:var(--bg-light);min-height:100vh;padding-bottom:60px">
        <div class="dashboard-page">

            <div class="page-title-row">
                <div>
                    <h2>Payment Verifications</h2>
                    <p>Review and verify pending student payment submissions.</p>
                </div>
            </div>

            <div class="card">
                <div class="card__header">
                    <h3>Pending Verifications</h3>
                    <span style="font-size:.8rem;color:var(--text-muted)"><?= $totalRows ?> pending</span>
                </div>
                <div class="card__body" style="padding:0">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Document</th>
                                <th>Ref #</th>
                                <th>Method</th>
                                <th>Txn Reference</th>
                                <th style="text-align:center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pending_payments)): ?>
                            <tr>
                                <td colspan="6" style="text-align:center;padding:50px;color:var(--text-muted)">
                                    <div style="font-size:2rem;margin-bottom:8px">💳</div>
                                    No pending payments.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($pending_payments as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['first_name'].' '.$p['last_name']) ?></td>
                                <td><?= htmlspecialchars($p['doc_name']) ?></td>
                                <td style="font-weight:700;color:var(--crimson)"><?= htmlspecialchars($p['reference_number']) ?></td>
                                <td><?= htmlspecialchars($p['payment_method']) ?></td>
                                <td style="font-weight:700;font-family:monospace"><?= htmlspecialchars($p['transaction_reference']) ?></td>
                                <td style="text-align:center">
                                    <!-- Replace confirm() with WD.confirm() modal -->
                                    <button class="btn btn-success btn-sm"
                                            onclick="verifyPayment(<?= $p['id'] ?>,
                                                '<?= htmlspecialchars(addslashes($p['first_name'].' '.$p['last_name'])) ?>',
                                                '<?= htmlspecialchars(addslashes($p['reference_number'])) ?>')">
                                        ✓ Verify
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <span class="pagination__info">
                            Page <?= $page ?> of <?= $totalPages ?>
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

<!-- Hidden POST form (used by verifyPayment to submit without GET) -->
<form id="verifyForm" method="POST" style="display:none">
    <input type="hidden" name="approve_id" id="verifyPayId">
</form>

<?php include 'includes/footer.php'; ?>

<script>
function verifyPayment(payId, studentName, refNo) {
    WD.confirm(
        'Verify Payment?',
        `Confirm payment received from ${studentName} for request ${refNo}?`,
        function() {
            document.getElementById('verifyPayId').value = payId;
            document.getElementById('verifyForm').submit();
        },
        { icon: '💳', okLabel: 'Yes, Verify' }
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