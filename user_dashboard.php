<?php
// FORCE ERRORS TO SHOW (Fixes the white screen issue)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// PREVIEW MODE: Prevents redirect
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 999;
}

// 1. Setup Logic (Sample Data)
$sample = [
    ['Official Transcript',  'Employment',      1, '₱150', 'May 1, 2026',  'pending'],
    ['Certification Letter', 'Scholarship',     1, '₱100', 'May 1, 2026',  'in_progress'],
    ['Diploma Copy',         'Further Studies', 1, '₱200', 'Apr 28, 2026', 'completed'],
    ['Academic Records',     'Graduate School', 2, '₱350', 'Apr 20, 2026', 'pending'],
];

$counts = array_count_values(array_column($sample, 5));
$total = count($sample);
$pending = ($counts['pending'] ?? 0);
$progress = ($counts['in_progress'] ?? 0);
$completed = ($counts['completed'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard — WildDocuments</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php 
// CHECKPOINT: Change this to 'navbar.php' if 'user_navbar.php' doesn't exist yet
if (file_exists('includes/user_navbar.php')) {
    include 'includes/user_navbar.php'; 
} else {
    echo "<div style='padding:10px; background:red; color:white;'>Missing includes/user_navbar.php</div>";
}
?>

<div class="app-layout">
    <?php 
    // Only include if file exists to prevent crash
    if (file_exists('includes/user_sidebar.php')) include 'includes/user_sidebar.php'; 
    ?>

    <main style="padding: 40px 0 64px; flex: 1; background: var(--bg-light); overflow-x: hidden;">
        <div class="container">
            <!-- Table and Stats code remains the same as your previous working block -->
            <div class="welcome-banner" style="margin-bottom: 28px;">
                <h2>My Document Requests</h2>
            </div>
            
            <div class="stats-grid" style="margin-bottom: 28px;">
                <div class="stat-card">
                    <div class="stat-card__label">Total Requests</div>
                    <div class="stat-card__value"><?= $total ?></div>
                </div>
                <!-- ... other stat cards ... -->
            </div>

            <div class="card">
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Document</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sample as $i => $row): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($row[0]) ?></td>
                                <td><?= ucwords(str_replace('_', ' ', $row[5])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php if (file_exists('includes/footer.php')) include 'includes/footer.php'; ?>

</body>
</html>