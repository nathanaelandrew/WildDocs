<?php
// student_dashboard.php
session_start();

// 1. Auth Check: Only allow logged-in students
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') { 
    header('Location: login.php'); 
    exit; 
}

require_once 'includes/db.php';

$pdo = getDB();
$userId = $_SESSION['user_id'];

// 2. Fetch Student Specific Requests (Uses JOIN in db.php to get document_name)
$requests = fetchStudentRequests($pdo, $userId);

// 3. Calculate Stats for the cards
$total    = count($requests);
$statuses = array_column($requests, 'status');
$pending  = count(array_filter($statuses, fn($s) => $s === 'pending'));
$paid     = count(array_filter($statuses, fn($s) => $s === 'paid'));
$approved = count(array_filter($statuses, fn($s) => $s === 'approved'));
$released = count(array_filter($statuses, fn($s) => $s === 'released'));

/**
 * Helper: Status Badge Styling
 */
function statusBadge(string $status): string {
    return match($status) {
        'pending'  => 'badge-pending',
        'paid'     => 'badge-paid',
        'approved' => 'badge-progress',
        'released' => 'badge-completed',
        default    => 'badge-pending',
    };
}

/**
 * Helper: Status Label Icons
 */
function statusLabel(string $status): string {
    return match($status) {
        'pending'  => '⏳ Pending',
        'paid'     => '💳 Paid',
        'approved' => '✅ Approved',
        'released' => '📦 Released',
        default    => ucfirst($status),
    };
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
    
    /* UI: Action Column Layout */
    .col-action { 
        display: flex; 
        gap: 6px; 
        justify-content: center; 
        align-items: center; 
    }

    /* UI: Download Toast Notification */
    #downloadToast {
        position: fixed; bottom: 30px; right: 30px;
        background: #166534; color: white;
        padding: 16px 24px; border-radius: 12px;
        box-shadow: var(--shadow-lg);
        display: none; align-items: center; gap: 12px;
        z-index: 1000; animation: slideIn 0.3s ease-out;
    }
    @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
  </style>
</head>
<body>
<?php include 'includes/student_navbar.php'; ?>

<div class="app-layout">
  <?php include 'includes/student_sidebar.php'; ?>

  <main style="padding:40px 0 64px; flex:1; background:var(--bg-light); overflow-x:hidden">
    <div class="container">

      <!-- Header -->
      <div class="welcome-banner" style="margin-bottom:28px">
        <div>
          <h2 style="color: white; margin-bottom: 4px;">My Document Requests</h2>
          <p style="color: rgba(255,255,255,0.75);">Welcome back, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>. Track your applications here.</p>
        </div>
        <a href="student_request.php" class="btn btn-primary" style="background:white; color:var(--crimson); border:none;">+ New Request</a>
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

      <!-- Requests Table Card -->
      <div class="card">
        <div class="card__header">
          <h3>My Request History</h3>
          <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 400;"><?= $total ?> records found</span>
        </div>
        <div class="card__body" style="padding:0">
          <div class="table-wrapper" style="border:none; border-radius:0">
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
                  <tr><td colspan="9" style="text-align:center; padding:60px; color:var(--text-muted)">
                    <div style="font-size: 2rem; margin-bottom: 10px;">📄</div>
                    <p>No document requests found. <a href="student_request.php" style="color:var(--crimson); font-weight:600;">Start a new request →</a></p>
                  </td></tr>
                <?php else: ?>
                  <?php foreach ($requests as $i => $r): ?>
                  <tr>
                    <td class="col-id"><?= $i+1 ?></td>
                    <td style="font-weight:700; color:var(--crimson)"><?= htmlspecialchars($r['reference_number']) ?></td>
                    <td style="font-weight:600"><?= htmlspecialchars($r['document_name']) ?></td>
                    <td style="text-align:center"><?= $r['copies'] ?></td>
                    <td style="font-size: 0.85rem; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($r['purpose']) ?>">
                        <?= htmlspecialchars($r['purpose'] ?? '—') ?>
                    </td>
                    <td style="font-weight:700">₱<?= number_format($r['total_amount'], 2) ?></td>
                    <td class="col-id" style="font-size: 0.8rem;"><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
                    <td style="text-align:center">
                        <span class="badge <?= statusBadge($r['status']) ?>"><?= statusLabel($r['status']) ?></span>
                    </td>
                    
                    <td class="col-action">
                        <?php if ($r['status'] === 'released'): ?>
                            <button class="btn btn-secondary btn-sm" onclick="triggerDownload('<?= htmlspecialchars($r['document_name']) ?>')">
                                Download
                            </button>
                        <?php else: ?>
                            <!-- Disabled Download -->
                            <button class="btn btn-sm" style="opacity:0.3; cursor:not-allowed;" disabled title="Wait for document to be Released">
                                Download
                            </button>
                        <?php endif; ?>

                        <?php if ($r['status'] === 'pending'): ?>
                            <button class="btn btn-danger btn-sm" onclick="cancelRequest(<?= $r['id'] ?>, event)">
                                Cancel
                            </button>
                        <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- Custom Notification Popup (Toast) -->
<div id="downloadToast">
    <span style="font-size: 1.5rem;">✅</span>
    <div>
        <strong style="display:block;">Success</strong>
        <span id="toastMsg" style="font-size: 0.85rem; opacity: 0.9;">Document saved.</span>
    </div>
</div>

<script>
/**
 * 1. Simulate Document Download
 */
function triggerDownload(docName) {
    const toast = document.getElementById('downloadToast');
    const msg = document.getElementById('toastMsg');
    
    msg.innerText = "Downloaded: " + docName;
    toast.style.display = 'flex';
    toast.style.opacity = '1';

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => { toast.style.display = 'none'; }, 500);
    }, 4000);
}

/**
 * 2. AJAX Request Cancellation
 */
function cancelRequest(requestId, event) {
    if (confirm('Cancel this request? This will permanently delete the record.')) {
        const btn = event.target;
        btn.disabled = true;
        btn.innerText = "...";

        fetch('cancel_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: requestId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Refresh to update stats and list
            } else {
                alert('Error: ' + data.message);
                btn.disabled = false;
                btn.innerText = "Cancel";
            }
        })
        .catch(() => {
            alert('Connection failed.');
            btn.disabled = false;
            btn.innerText = "Cancel";
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>