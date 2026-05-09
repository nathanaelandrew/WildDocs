<?php
// student_dashboard.php
session_start();
// if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
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
  </style>
</head>
<body>
<?php include 'includes/user_navbar.php'; ?>
<div class="app-layout">
  <?php include 'includes/user_sidebar.php'; ?>
  <main style="padding:40px 0 64px;flex:1;background:var(--bg-light);overflow-x:hidden">
    <div class="container">

      <div class="welcome-banner" style="margin-bottom:28px">
        <div>
          <h2>My Document Requests</h2>
          <p style="color:var(--text-muted);margin-top:4px">Track and manage your submitted requests.</p>
        </div>
        <a href="student_request.php" class="btn btn-primary">+ New Request</a>
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
                </tr>
              </thead>
              <tbody>
                <?php if (empty($requests)): ?>
                  <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted)">
                    No requests yet. <a href="student_request.php" style="color:var(--crimson);font-weight:600">Submit your first request →</a>
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
<?php include 'includes/footer.php'; ?>
</body>
</html>
PHPEOF
echo "student_dashboard.php done"
Output

student_dashboard.php done

Write student_request.php