<?php
// student_dashboard.php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
require_once 'includes/db.php';

$pdo      = getDB();
$requests = fetchStudentRequests($pdo, $_SESSION['user_id']);

$total    = count($requests);
$statuses = array_column($requests, 'status');
$pending  = count(array_filter($statuses, fn($s) => $s === 'pending'));
$paid     = count(array_filter($statuses, fn($s) => $s === 'paid'));
$approved = count(array_filter($statuses, fn($s) => $s === 'approved'));
$released = count(array_filter($statuses, fn($s) => $s === 'released'));

// Badge helpers
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
    /* Flex container to keep buttons aligned side-by-side */
    .col-action { 
        display: flex; 
        gap: 8px; 
        justify-content: center; 
        align-items: center; 
    }
    #downloadToast {
        position: fixed; bottom: 30px; right: 30px;
        background: #1A6E3C; color: white;
        padding: 16px 24px; border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
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
  <main style="padding:40px 0 64px;flex:1;background:var(--bg-light);overflow-x:hidden">
    <div class="container">

      <div class="welcome-banner" style="margin-bottom:28px">
        <div>
          <h2>My Document Requests</h2>
          <p style="color:var(--text-muted);margin-top:4px">Track and manage your submitted requests.</p>
        </div>
      </div>

      <!-- Stats -->
      <div class="stats-grid" style="margin-bottom:28px">
        <div class="stat-card">
          <div class="stat-card__icon">📋</div>
          <div class="stat-card__label">Total Requests</div>
          <div class="stat-card__value"><?= $total ?></div>
          <div class="stat-card__sub">All time</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__icon">⏳</div>
          <div class="stat-card__label">Pending Payment</div>
          <div class="stat-card__value" style="color:#8A6000"><?= $pending ?></div>
          <div class="stat-card__sub">Awaiting payment</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__icon">⚙️</div>
          <div class="stat-card__label">In Process</div>
          <div class="stat-card__value" style="color:#1A4A6E"><?= $paid + $approved ?></div>
          <div class="stat-card__sub">Paid or approved</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__icon">📦</div>
          <div class="stat-card__label">Released</div>
          <div class="stat-card__value" style="color:#1A6E3C"><?= $released ?></div>
          <div class="stat-card__sub">Ready to claim</div>
        </div>
      </div>

      <!-- Requests Table -->
      <div class="card">
        <div class="card__header">
          <h3>My Requests</h3>
          <a href="student_request.php" class="btn btn-primary btn-sm">+ New Request</a>
        </div>
        <div class="card__body" style="padding:0">
          <div class="table-wrapper" style="border:none;border-radius:0">
            <table class="data-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Reference</th>
                  <th>Document</th>
                  <th>Copies</th>
                  <th>Purpose</th>
                  <th>Amount</th>
                  <th>Date Filed</th>
                  <th>Status</th>
                  <th style="text-align:center">Action</th> 
                </tr>
              </thead>
              <tbody>
                <?php if (empty($requests)): ?>
                  <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted)">
                    No requests yet.
                  </td></tr>
                <?php else: ?>
                  <?php foreach ($requests as $i => $r): ?>
                  <tr>
                    <td class="col-id"><?= $i+1 ?></td>
                    <td class="col-id" style="font-weight:600;color:var(--crimson)"><?= htmlspecialchars($r['reference_number']) ?></td>
                    <td class="col-name"><?= htmlspecialchars($r['document_name']) ?></td>
                    <td style="text-align:center"><?= $r['copies'] ?></td>
                    <td><?= htmlspecialchars($r['purpose'] ?? '—') ?></td>
                    <td style="font-weight:600">₱<?= number_format($r['total_amount'], 2) ?></td>
                    <td class="col-id"><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
                    <td><span class="badge <?= statusBadge($r['status']) ?>"><?= statusLabel($r['status']) ?></span></td>
                    
                    <!-- ACTION COLUMN -->
                    <td class="col-action">
                        <?php if ($r['status'] === 'released'): ?>
                            <button class="btn btn-secondary btn-sm" onclick="triggerDownload('<?= htmlspecialchars($r['document_name']) ?>')">
                                Download
                            </button>
                        <?php else: ?>
                            <button class="btn btn-sm" style="opacity:0.4; cursor:not-allowed;" disabled>
                                Download
                            </button>
                        <?php endif; ?>

                        <!-- CANCEL BUTTON: Only visible if pending -->
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

<!-- Custom Notification Popup -->
<div id="downloadToast">
    <span style="font-size: 1.5rem;">✅</span>
    <div>
        <strong style="display:block;">Download Started</strong>
        <span id="toastMsg" style="font-size: 0.85rem; opacity: 0.9;">Your document is being saved.</span>
    </div>
</div>

<script>
/**
 * 1. Download Document Simulation
 */
function triggerDownload(docName) {
    const toast = document.getElementById('downloadToast');
    const msg = document.getElementById('toastMsg');
    
    msg.innerText = docName + " has been downloaded successfully.";
    toast.style.display = 'flex';

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
            toast.style.display = 'none';
            toast.style.opacity = '1';
        }, 500);
    }, 4000);
}

/**
 * 2. AJAX Request Cancellation
 */
function cancelRequest(requestId, event) {
    if (confirm('Are you sure you want to cancel and delete this request? This cannot be undone.')) {
        const btn = event.target;
        btn.disabled = true;
        btn.style.opacity = '0.5';

        fetch('cancel_request.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: requestId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh page to recalculate card stats and update table
                location.reload();
            } else {
                alert('Error: ' + data.message);
                btn.disabled = false;
                btn.style.opacity = '1';
            }
        })
        .catch(() => {
            alert('A connection error occurred. Could not cancel request.');
            btn.disabled = false;
            btn.style.opacity = '1';
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>