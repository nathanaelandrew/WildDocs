<?php
// admin_dashboard.php
session_start();

// 1. MOCK SESSION (Since you aren't logged in)
$_SESSION['admin_name'] = "Admin Preview";

// 2. MOCK STATISTICS
$stats = [
    'total'       => 124,
    'pending'     => 45,
    'in_progress' => 12,
    'completed'   => 67
];

// 3. MOCK REQUESTS DATA
$requests = [
    [
        'id'            => 1001,
        'full_name'     => 'Juan Dela Cruz',
        'student_id'    => '2021-0001',
        'program'       => 'BS IT',
        'document_type' => 'Transcript of Records',
        'amount'        => 250.00,
        'created_at'    => '2023-10-20 09:30:00',
        'status'        => 'pending'
    ],
    [
        'id'            => 1002,
        'full_name'     => 'Maria Clara',
        'student_id'    => '2021-0042',
        'program'       => 'BS CS',
        'document_type' => 'Certificate of Enrollment',
        'amount'        => 100.00,
        'created_at'    => '2023-10-21 14:15:00',
        'status'        => 'in_progress'
    ],
    [
        'id'            => 1003,
        'full_name'     => 'Crisostomo Ibarra',
        'student_id'    => '2020-0123',
        'program'       => 'BEED',
        'document_type' => 'Diploma Copy',
        'amount'        => 500.00,
        'created_at'    => '2023-10-18 11:00:00',
        'status'        => 'completed'
    ],
    [
        'id'            => 1004,
        'full_name'     => 'Leonor Rivera',
        'student_id'    => '2022-0055',
        'program'       => 'BS Accountancy',
        'document_type' => 'Good Moral Certificate',
        'amount'        => 75.00,
        'created_at'    => '2023-10-22 08:45:00',
        'status'        => 'pending'
    ]
];

// Commenting these out so the page doesn't crash
// include 'includes/db.php';
// $pdo = getDB();
// $requests = fetchAllRequests($pdo);
// $stats = getDashboardStats($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard – WildDocuments Admin</title>
  <!-- NOTE: If your CSS file doesn't exist yet, the styling will be missing -->
  <link rel="stylesheet" href="css/styles.css"> 
  <style>
    /* Adding some basic fallback styles in case your CSS isn't loading */
    :root { --border: #ddd; --primary: #007bff; }
    body { font-family: sans-serif; background: #f4f7f6; margin: 0; }
    .app-layout { display: flex; min-height: 100vh; }
    .main-content { flex: 1; padding: 20px; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .stat-card__value { font-size: 24px; font-weight: bold; }
    .data-table { width: 100%; border-collapse: collapse; background: white; }
    .data-table th, .data-table td { padding: 12px; border-bottom: 1px solid var(--border); text-align: left; }
    .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    .badge-pending { background: #fff3cd; color: #856404; }
    .badge-progress { background: #cce5ff; color: #004085; }
    .badge-completed { background: #d4edda; color: #155724; }
  </style>
</head>
<body>

<?php 
// Only include these if the files actually exist in your folder
if (file_exists('includes/admin_navbar.php')) include 'includes/admin_navbar.php'; 
?>

<div class="app-layout">
  <?php if (file_exists('includes/admin_sidebar.php')) include 'includes/admin_sidebar.php'; ?>

  <main class="main-content">
    <div class="dashboard-page">

      <div class="welcome-banner" style="background: var(crimson-deeper); padding: 20px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <div>
          <h2 style="margin:0">Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?>!</h2>
          <p style="margin:5px 0 0">Here's an overview of all document requests (PREVIEW MODE).</p>
        </div>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-card__icon">📋</div>
          <div class="stat-card__label">Total Requests</div>
          <div class="stat-card__value"><?= $stats['total'] ?></div>
          <div class="stat-card__sub">All time</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__icon">🕐</div>
          <div class="stat-card__label">Pending</div>
          <div class="stat-card__value" style="color:#8A6000"><?= $stats['pending'] ?></div>
          <div class="stat-card__sub">Awaiting action</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__icon">⚙️</div>
          <div class="stat-card__label">In Progress</div>
          <div class="stat-card__value" style="color:#1A4A6E"><?= $stats['in_progress'] ?></div>
          <div class="stat-card__sub">Being processed</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__icon">✅</div>
          <div class="stat-card__label">Completed</div>
          <div class="stat-card__value" style="color:#1A6E3C"><?= $stats['completed'] ?></div>
          <div class="stat-card__sub">Fulfilled</div>
        </div>
      </div>

      <div class="card" style="background:white; border-radius:8px; overflow:hidden;">
        <div class="card__header" style="padding: 20px; display:flex; justify-content:space-between; border-bottom:1px solid #eee">
          <h3 style="margin:0">Recent Requests</h3>
        </div>
        <div class="card__body">
          <div class="table-wrapper">
            <table class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Full Name</th>
                  <th>Student ID</th>
                  <th>Program</th>
                  <th>Document</th>
                  <th>Amount</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($requests as $row): 
                  $status = $row['status'];
                  $badgeClass = match($status) {
                    'pending'     => 'badge-pending',
                    'in_progress' => 'badge-progress',
                    'completed'   => 'badge-completed',
                    default       => 'badge-pending'
                  };
                ?>
                <tr>
                  <td><?= $row['id'] ?></td>
                  <td class="col-name"><?= htmlspecialchars($row['full_name']) ?></td>
                  <td><?= htmlspecialchars($row['student_id']) ?></td>
                  <td><?= htmlspecialchars($row['program']) ?></td>
                  <td><?= htmlspecialchars($row['document_type']) ?></td>
                  <td style="font-weight:600">₱<?= number_format($row['amount'], 2) ?></td>
                  <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                  <td><span class="badge <?= $badgeClass ?>"><?= ucwords(str_replace('_', ' ', $status)) ?></span></td>
                  <td>
                    <select class="status-select">
                      <option value="pending"     <?= $status==='pending' ? 'selected':'' ?>>Pending</option>
                      <option value="in_progress" <?= $status==='in_progress' ? 'selected':'' ?>>In Progress</option>
                      <option value="completed"   <?= $status==='completed' ? 'selected':'' ?>>Completed</option>
                    </select>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<?php if (file_exists('includes/footer.php')) include 'includes/footer.php'; ?>
<script>
  // JS to handle status change (this is just a placeholder, you would need to implement the actual update logic)
  document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', function() {
      const newStatus = this.value;
      const row = this.closest('tr');
      const id = row.querySelector('td').textContent; // Assuming ID is in the first cell
      console.log(`Request ID ${id} status changed to ${newStatus}`);
      // Here you would make an AJAX call to update the status in the database
    });
  });

</body>
</html>