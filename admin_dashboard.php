<?php
// admin_dashboard.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { 
    header('Location: login.php'); 
    exit; 
}

$pdo = getDB();

$unviewedCount = getUnviewedCount($pdo);
$stats         = getDashboardStats($pdo);

// ── Pagination ────────────────────────────────────────────────────────
$perPage    = 10;
$page       = max(1, (int)($_GET['page'] ?? 1));
$allRequests = fetchRecentRequests($pdo, 200); // fetch enough for pagination
$total      = count($allRequests);
$totalPages = max(1, (int)ceil($total / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;
$requests   = array_slice($allRequests, $offset, $perPage);

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
    <title>Admin Dashboard — WildDocuments</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .clickable-row { cursor: pointer; transition: background-color 0.2s; }
        .clickable-row:hover { background-color: var(--pink-bg) !important; }
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
                    <div class="stat-card__label">Total Requests</div>
                    <div class="stat-card__value"><?= $stats['total'] ?></div>
                    <div class="stat-card__sub">All time</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__label">Pending</div>
                    <div class="stat-card__value" style="color: #854d0e;"><?= $stats['pending'] ?></div>
                    <div class="stat-card__sub">Awaiting action</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__label">Paid/Approved</div>
                    <div class="stat-card__value" style="color: #1e40af;"><?= ($stats['paid'] ?? 0) + ($stats['approved'] ?? 0) ?></div>
                    <div class="stat-card__sub">In processing</div>
                </div>
                <div class="stat-card">
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
                    <span style="font-size:.8rem;color:var(--text-muted)"><?= $total ?> total records</span>
                </div>
                <div class="card__body" style="padding: 0;">
                    <div class="table-wrapper" style="border:none">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Ref #</th>
                                    <th>Student Name</th>
                                    <th>Program</th>
                                    <th>Document</th>
                                    <th>Date Submitted</th>
                                    <th style="text-align:center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($requests)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">No requests found in the system.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($requests as $i => $r):
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
                                        <td class="col-id"><?= $offset + $i + 1 ?></td>
                                        <td style="font-weight: 700; color: var(--crimson);"><?= htmlspecialchars($r['reference_number']) ?></td>
                                        <td>
                                            <div class="col-name"><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($r['student_number'] ?? 'No ID') ?></div>
                                        </td>
                                        <td style="font-size: 0.8rem;"><?= htmlspecialchars($r['program']) ?></td>
                                        <td style="font-size: 0.85rem; font-weight:500"><?= htmlspecialchars($r['document_name']) ?></td>
                                        <td style="font-size: 0.8rem;"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                                        <td style="text-align:center"><span class="badge <?= $statusClass ?>"><?= ucfirst($r['status']) ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination — same markup/classes as student_dashboard -->
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <span class="pagination__info">
                            Showing <?= $offset + 1 ?>–<?= min($offset + $perPage, $total) ?> of <?= $total ?> requests
                        </span>
                        <div class="pagination__controls">
                            <a href="<?= pageUrl(1) ?>" class="page-btn">«</a>
                            <a href="<?= pageUrl(max(1, $page - 1)) ?>" class="page-btn">‹</a>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if ($i === 1 || $i === $totalPages || abs($i - $page) <= 2): ?>
                                    <a href="<?= pageUrl($i) ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                                <?php elseif (abs($i - $page) === 3): ?>
                                    <span class="page-btn page-btn--dots">…</span>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <a href="<?= pageUrl(min($totalPages, $page + 1)) ?>" class="page-btn">›</a>
                            <a href="<?= pageUrl($totalPages) ?>" class="page-btn">»</a>
                        </div>
                    </div>
                    <?php endif; ?>
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