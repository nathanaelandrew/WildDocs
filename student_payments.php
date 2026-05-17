<?php
session_start();
require_once 'includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: login.php'); exit;
}

$pdo    = getDB();
$userId = $_SESSION['user_id'];

// Handle Payment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reqId  = (int)$_POST['request_id'];
    $method = $_POST['method'];
    $trxRef = $_POST['generated_ref'];

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO payments (request_id, amount, payment_method, transaction_reference, status)
                               SELECT id, total_amount, ?, ?, 'pending' FROM requests WHERE id = ?");
        $stmt->execute([$method, $trxRef, $reqId]);
        $pdo->prepare("UPDATE requests SET status = 'paid', is_viewed = FALSE WHERE id = ?")->execute([$reqId]);
        $pdo->commit();
        $_SESSION['flash'] = ['type' => 'success', 'msg' => "Payment submitted! Transaction Ref: $trxRef"];
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Payment failed: ' . $e->getMessage()];
    }
    header('Location: student_payments.php'); exit;
}

// ── Pagination ────────────────────────────────────────────────────────
$perPage = 10;
$page    = max(1, (int)($_GET['page'] ?? 1));

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM requests r
                             JOIN document_types dt ON r.document_type_id = dt.id
                             WHERE r.user_id = ? AND r.status IN ('pending','paid','approved')");
$countStmt->execute([$userId]);
$totalRows  = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

$requests = $pdo->prepare("SELECT r.*, dt.name as document_name FROM requests r
                            JOIN document_types dt ON r.document_type_id = dt.id
                            WHERE r.user_id = ? AND r.status IN ('pending','paid','approved')
                            ORDER BY r.created_at DESC
                            LIMIT $perPage OFFSET $offset");
$requests->execute([$userId]);
$rows = $requests->fetchAll();

function pageUrl($p) {
    $q = $_GET; $q['page'] = $p;
    return '?' . http_build_query($q);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payments — WildDocuments</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .badge-paid { background:#EFF6FF; color:#1D4ED8; }
        .payment-summary { background:var(--off-white); padding:15px; border-radius:8px; margin-bottom:20px; border:1px dashed var(--border); }
        .summary-item { display:flex; justify-content:space-between; margin-bottom:8px; font-size:.9rem; }
        .summary-item span:first-child { color:var(--text-muted); }
        .summary-item span:last-child { font-weight:600; color:var(--text-dark); }
        #display_trx { font-family:monospace; color:var(--crimson); letter-spacing:.5px; }
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
                    <h2>My Payments</h2>
                    <p>Settle your fees to begin document processing.</p>
                </div>
            </div>

            <div class="card">
                <div class="card__header">
                    <h3>Payable Documents</h3>
                    <span style="font-size:.8rem;color:var(--text-muted)"><?= $totalRows ?> records</span>
                </div>
                <div class="card__body" style="padding:0">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ref #</th>
                                <th>Document</th>
                                <th>Amount</th>
                                <th style="text-align:center">Status</th>
                                <th style="text-align:center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rows)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted)">
                                    No pending payments.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($rows as $r): ?>
                            <tr>
                                <td style="font-weight:700;color:var(--crimson)"><?= htmlspecialchars($r['reference_number']) ?></td>
                                <td><?= htmlspecialchars($r['document_name']) ?></td>
                                <td style="font-weight:600">₱<?= number_format($r['total_amount'], 2) ?></td>
                                <td style="text-align:center">
                                    <span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span>
                                </td>
                                <td style="text-align:center">
                                    <?php if ($r['status'] === 'pending'): ?>
                                        <button class="btn btn-primary btn-sm"
                                                onclick="openPay(
                                                    '<?= $r['id'] ?>',
                                                    '<?= htmlspecialchars(addslashes($r['document_name'])) ?>',
                                                    '<?= $r['total_amount'] ?>'
                                                )">Pay Now</button>
                                    <?php else: ?>
                                        <span style="color:#16a34a;font-size:.78rem;font-weight:700">✓ Submitted</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

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
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Payment confirmation modal -->
<div class="modal-overlay" id="payModal">
    <div class="modal" style="max-width:400px;text-align:left">
        <h3 style="margin-bottom:5px">Confirm Payment</h3>
        <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:20px">
            Review your payment details before submitting.
        </p>

        <div class="payment-summary">
            <div class="summary-item">
                <span>Document:</span><span id="display_doc">-</span>
            </div>
            <div class="summary-item">
                <span>Amount:</span><span id="display_amt">₱0.00</span>
            </div>
            <div class="summary-item">
                <span>TRX Reference:</span><span id="display_trx" style="text-align: right">TRX-00000000-0000</span>
            </div>
        </div>

        <form method="POST" id="payForm">
            <input type="hidden" name="request_id"   id="pay_req_id">
            <input type="hidden" name="generated_ref" id="hidden_trx_ref">

            <div class="form-group">
                <label class="form-label">Payment Method</label>
                <select name="method" class="form-control" required>
                    <option value="GCash">GCash</option>
                    <option value="Maya">Maya</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                </select>
            </div>

            <div style="display:flex;gap:10px;margin-top:22px">
                <button type="button" class="btn btn-ghost btn-block" onclick="closePay()">Cancel</button>
                <!-- Confirm uses WD.confirm before final submit -->
                <button type="button" class="btn btn-primary btn-block" onclick="confirmAndPay()">
                    Confirm & Pay
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function generateTrxID() {
    const now  = new Date();
    const date = now.getFullYear() +
                 String(now.getMonth()+1).padStart(2,'0') +
                 String(now.getDate()).padStart(2,'0');
    const rand = Math.random().toString(36).substring(2,10).toUpperCase();
    return `TRX-${date}-${rand}`;
}

function openPay(id, doc, amt) {
    document.getElementById('pay_req_id').value   = id;
    document.getElementById('display_doc').textContent = doc;
    document.getElementById('display_amt').textContent = '₱' + parseFloat(amt).toFixed(2);

    const trxId = generateTrxID();
    document.getElementById('display_trx').textContent  = trxId;
    document.getElementById('hidden_trx_ref').value     = trxId;

    document.getElementById('payModal').classList.add('open');
}

function closePay() { document.getElementById('payModal').classList.remove('open'); }

// Final confirm step before submitting the form
function confirmAndPay() {
    const doc = document.getElementById('display_doc').textContent;
    const amt = document.getElementById('display_amt').textContent;
    WD.confirm(
        'Submit Payment?',
        `Pay ${amt} for "${doc}"? This will notify the admin for verification.`,
        function() { document.getElementById('payForm').submit(); },
        { icon: '💳', okLabel: 'Yes, Submit' }
    );
}

// Close modal on overlay click
document.getElementById('payModal').addEventListener('click', function(e) {
    if (e.target === this) closePay();
});
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