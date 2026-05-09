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

// Fetch Unviewed count for the "New" badge
$unviewedCount = getUnviewedCount($pdo);

// Fetch Stats for the cards
$stats = getDashboardStats($pdo);

// Fetch Requests with JOINs (Student Name, ID, and Program)
$requests = fetchRecentRequests($pdo, 20); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — WildDocuments</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Make rows look interactive */
        .clickable-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .clickable-row:hover {
            background-color: var(--pink-bg) !important;
        }
        .badge-paid { background:#EFF6FF; color:#1D4ED8; }
    </style>
</head>
<body>
<?php include 'includes/admin_navbar.php'; ?>

<div class="app-layout">
    <?php include 'includes/admin_sidebar.php'; ?>
    
    <main class="main-content" style="background: var(--bg-light); min-height: 100vh; padding-bottom: 80px;">
        <div class="dashboard-page">
            
            <!-- Welcome Header -->
            <div class="welcome-banner" style="margin-bottom: 28px;">
                <div>
                    <h2 style="color: white; margin-bottom: 5px;">Administrator Dashboard</h2>
                    <p style="color: rgba(255,255,255,0.7); margin: 0;">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>. Click any request to manage it.</p>
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
                    <div class="stat-card__value" style="color: #1e40af;"><?= ($stats['paid'] ?? 0) + ($stats['approved'] ?? 0) ?></div>
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
                    <div style="display:flex; align-items:center; gap:12px">
                        <h3 style="margin:0">Recent Document Requests</h3>
                        <?php if ($unviewedCount > 0): ?>
                            <span class="badge badge-new"><?= $unviewedCount ?> New</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card__body" style="padding: 0;">
                    <div class="table-wrapper" style="border:none">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref #</th>
                                    <th>Student Name</th>
                                    <th>Program</th>
                                    <th>Document</th>
                                    <th>Date Submitted</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($requests)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">No requests found in the system.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($requests as $r): 
                                        // Highlight unviewed rows with a slight gold tint
                                        $rowBg = (!$r['is_viewed']) ? '#FFF9F0' : '#FFFFFF';
                                        
                                        $statusClass = match($r['status']) {
                                            'pending'  => 'badge-pending',
                                            'paid'     => 'badge-paid',
                                            'approved' => 'badge-progress',
                                            'released' => 'badge-completed',
                                            default    => 'badge-pending'
                                        };
                                    ?>
                                    <tr class="clickable-row" 
                                        style="background-color: <?= $rowBg ?>;"
                                        onclick="window.location.href='admin_requests.php?search=<?= urlencode($r['reference_number']) ?>'"
                                        title="Click to view details and update status">
                                        
                                        <td style="font-weight: 700; color: var(--crimson);"><?= htmlspecialchars($r['reference_number']) ?></td>
                                        <td>
                                            <div class="col-name"><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($r['student_number'] ?? 'No ID') ?></div>
                                        </td>
                                        <td style="font-size: 0.8rem;"><?= htmlspecialchars($r['program']) ?></td>
                                        <td style="font-size: 0.85rem; font-weight:500"><?= htmlspecialchars($r['document_name']) ?></td>
                                        <td style="font-size: 0.8rem;"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                                        <td><span class="badge <?= $statusClass ?>"><?= ucfirst($r['status']) ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="admin_requests.php" class="btn btn-ghost">View All Requests →</a>
            </div>
        </div>
    </main>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>