<?php
// admin_dashboard.php
session_start();
require_once 'includes/db.php';

// Auth check: Only allow logged-in admins
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { 
    header('Location: login.php'); 
    exit; 
}

$pdo = getDB();

// 1. Fetch Stats for the cards
$stats = getDashboardStats($pdo);

// 2. Fetch Requests with JOINs (Student Name, ID, and Program)
$requests = fetchRecentRequests($pdo, 20); // Fetch last 20 requests
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — WildDocuments</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<?php include 'includes/admin_navbar.php'; ?>

<div class="app-layout">
    <?php include 'includes/admin_sidebar.php'; ?>
    
    <main class="main-content" style="background: var(--bg-light); min-height: 100vh; padding-bottom: 50px;">
        <div class="dashboard-page">
            
            <!-- Welcome Header -->
            <div class="welcome-banner" style="margin-bottom: 28px;">
                <div>
                    <h2 style="color: white; margin-bottom: 5px;">Administrator Dashboard</h2>
                    <p style="color: rgba(255,255,255,0.7); margin: 0;">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>. Manage all university document requests here.</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card__icon">📋</div>
                    <div class="stat-card__label">Total Requests</div>
                    <div class="stat-card__value"><?= $stats['total'] ?></div>
                    <div class="stat-card__sub">All time</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__icon">⏳</div>
                    <div class="stat-card__label">Pending</div>
                    <div class="stat-card__value" style="color: #854d0e;"><?= $stats['pending'] ?></div>
                    <div class="stat-card__sub">Awaiting action</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__icon">💳</div>
                    <div class="stat-card__label">Paid/Approved</div>
                    <div class="stat-card__value" style="color: #1e40af;"><?= $stats['paid'] + $stats['approved'] ?></div>
                    <div class="stat-card__sub">In processing</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__icon">📦</div>
                    <div class="stat-card__label">Released</div>
                    <div class="stat-card__value" style="color: #166534;"><?= $stats['released'] ?></div>
                    <div class="stat-card__sub">Ready/Claimed</div>
                </div>
            </div>

            <!-- Recent Requests Table -->
            <div class="card" style="margin-top: 28px;">
                <div class="card__header">
                    <h3>Recent Document Requests</h3>
                    <div class="badge badge-new"><?= count($requests) ?> Recent</div>
                </div>
                <div class="card__body" style="padding: 0;">
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref #</th>
                                    <th>Student Name</th>
                                    <th>Student ID</th>
                                    <th>Program</th>
                                    <th>Document</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Update Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($requests)): ?>
                                    <tr>
                                        <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">No requests found in the system.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($requests as $r): 
                                        $statusClass = match($r['status']) {
                                            'pending'  => 'badge-pending',
                                            'paid'     => 'badge-progress', // Blueish
                                            'approved' => 'badge-progress', // Blueish
                                            'released' => 'badge-completed',
                                            default    => 'badge-pending'
                                        };
                                    ?>
                                    <tr>
                                        <td style="font-weight: 700; color: var(--crimson);"><?= htmlspecialchars($r['reference_number']) ?></td>
                                        <td class="col-name"><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                                        <td style="font-size: 0.8rem;"><?= htmlspecialchars($r['student_number'] ?? 'N/A') ?></td>
                                        <td style="font-size: 0.8rem;"><?= htmlspecialchars($r['program'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($r['document_name']) ?></td>
                                        <td style="font-weight: 600;">₱<?= number_format($r['total_amount'], 2) ?></td>
                                        <td><span class="badge <?= $statusClass ?>"><?= ucfirst($r['status']) ?></span></td>
                                        <td>
                                            <select class="status-select" onchange="updateStatus(<?= $r['id'] ?>, this.value)">
                                                <option value="pending"  <?= $r['status'] === 'pending' ? 'selected':'' ?>>Pending</option>
                                                <option value="paid"     <?= $r['status'] === 'paid' ? 'selected':'' ?>>Paid</option>
                                                <option value="approved" <?= $r['status'] === 'approved' ? 'selected':'' ?>>Approved</option>
                                                <option value="released" <?= $r['status'] === 'released' ? 'selected':'' ?>>Released</option>
                                            </select>
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

<script>
function updateStatus(requestId, newStatus) {
    // This is where you would call an AJAX script to update the database
    // For now, it will just show an alert
    if(confirm('Change status to ' + newStatus.toUpperCase() + '?')) {
        // You would typically use fetch() here:
        // fetch('update_request.php', { method: 'POST', body: ... })
        alert('Status updated for Request ID: ' + requestId);
        location.reload(); // Refresh to see changes
    }
}
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>