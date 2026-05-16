<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { 
    header('Location: login.php'); 
    exit; 
}

$pdo = getDB();
$search = trim($_GET['search'] ?? '');

// Use 'created_at' instead of 'updated_at' just in case
$sql = "SELECT 
            r.*, 
            u.first_name, u.last_name, 
            s.student_number, s.program,
            dt.name as document_name
        FROM requests r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN students s ON u.id = s.user_id
        JOIN document_types dt ON r.document_type_id = dt.id
        WHERE r.status = 'released'"; 

$params = []; // Initialize as empty array

if (!empty($search)) {
    $sql .= " AND (r.reference_number ILIKE ? OR u.first_name ILIKE ? OR u.last_name ILIKE ? OR s.student_number ILIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
}

$sql .= " ORDER BY r.created_at DESC"; // Changed to created_at for safety

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $archived = $stmt->fetchAll();
} catch (PDOException $e) {
    // If the database has an error, this will tell us what it is!
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Archive – Admin</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .badge-released { background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; }
        .text-muted { color: #64748b; font-size: 0.85rem; }
    </style>
</head>
<body>

<?php include 'includes/admin_navbar.php'; ?>

<div class="app-layout">
    <?php include 'includes/admin_sidebar.php'; ?>

    <main class="main-content">
        <div class="dashboard-page">
            <div class="page-title-row">
                <div>
                    <h2>Document Archive</h2>
                    <p>Viewing all successfully released documents.</p>
                </div>
                <a href="admin_requests.php" class="btn btn-primary">⬅ Back to Active Requests</a>
            </div>

            <!-- Search Bar -->
            <div class="card" style="margin-bottom: 20px; padding: 15px;">
                <form method="GET" style="display:flex; gap:10px;">
                    <input type="text" name="search" placeholder="Search reference or student..." 
                           value="<?= htmlspecialchars($search) ?>" class="form-control" style="flex:1">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>

            <div class="card">
                <div class="card__body" style="padding:0">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ref #</th>
                                <th>Student</th>
                                <th>Document</th>
                                <th>Released Date</th>
                                <th style="text-align:center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($archived)): ?>
                                <tr><td colspan="5" style="text-align:center; padding: 40px;">No archived documents found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($archived as $r): ?>
                                <tr>
                                    <td style="font-weight:700;"><?= htmlspecialchars($r['reference_number']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($r['first_name'].' '.$r['last_name']) ?><br>
                                        <span class="text-muted"><?= htmlspecialchars($r['student_number']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($r['document_name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($r['updated_at'])) ?></td>
                                    <td style="text-align:center">
                                        <span class="badge-released">RELEASED</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>