<?php
session_start();
require_once 'includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { header('Location: login.php'); exit; }

$pdo = getDB();

// Handle Approval
if (isset($_GET['approve_id'])) {
    $payId = $_GET['approve_id'];
    // 1. Mark payment as completed
    $pdo->prepare("UPDATE payments SET status = 'completed' WHERE id = ?")->execute([$payId]);
    // 2. Mark request as approved and notify student
    $pdo->prepare("UPDATE requests SET status = 'approved', student_is_viewed = FALSE 
                   WHERE id = (SELECT request_id FROM payments WHERE id = ?)")->execute([$payId]);
    header('Location: admin_payments.php?msg=verified');
}

$stmt = $pdo->query("SELECT p.*, r.reference_number, u.first_name, u.last_name, dt.name as doc_name
                     FROM payments p
                     JOIN requests r ON p.request_id = r.id
                     JOIN users u ON r.user_id = u.id
                     JOIN document_types dt ON r.document_type_id = dt.id
                     WHERE p.status = 'pending' ORDER BY p.payment_date DESC");
$pending_payments = $stmt->fetchAll();
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
    <main class="main-content">
        <div class="dashboard-page">
            <div class="page-title-row"><h2>Payment Verifications</h2></div>
            
            <div class="card">
                <div class="card__header"><h3>Pending Verifications</h3></div>
                <div class="card__body" style="padding:0">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Document</th>
                                <th>Ref #</th>
                                <th>Method</th>
                                <th>Txn Ref</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($pending_payments as $p): ?>
                            <tr>
                                <td><?= $p['first_name'] ?> <?= $p['last_name'] ?></td>
                                <td><?= $p['doc_name'] ?></td>
                                <td><?= $p['reference_number'] ?></td>
                                <td><?= $p['payment_method'] ?></td>
                                <td style="font-weight:700; color:var(--crimson)"><?= $p['transaction_reference'] ?></td>
                                <td>
                                    <a href="?approve_id=<?= $p['id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Confirm payment received?')">Verify</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>