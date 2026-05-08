<?php
// admin_dashboard.php
session_start();
include 'includes/db.php';

// Check authentication based on your login system
if (!isset($_SESSION['admin_id'])) { 
    header('Location: login.php'); 
    exit; 
}

$pdo = getDB();
$requests = fetchAllRequests($pdo);
$stats = getDashboardStats($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard – WildDocuments Admin</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include 'includes/admin_navbar.php'; ?>

<div class="app-layout">
  <?php include 'includes/admin_sidebar.php'; ?>

  <main class="main-content">
    <div class="dashboard-page">

      <div class="welcome-banner">
        <div>
          <h2>Welcome, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>!</h2>
          <p>Here's an overview of all document requests in the system.</p>
        </div>
        <a href="admin_requests.php" class="btn btn-primary">View All Requests →</a>
      </div>

      <!-- Stat Cards with Real Data -->
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

      <div class="card">
        <div class="card__header">
          <h3>Recent Requests</h3>
          <a href="admin_requests.php" class="btn btn-ghost btn-sm">View All →</a>
        </div>
        <div class="card__body" style="padding:0">
            <!-- Search & Filter (UI only for now) -->
          <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
            <div class="filter-bar" style="margin:0">
              <input type="text" class="form-control" placeholder="🔍 Search..." style="max-width:240px">
            </div>
          </div>

          <div class="table-wrapper" style="border:none;border-radius:0">
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
                    <select class="status-select" onchange="updateStatus(this, <?= $row['id'] ?>)">
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

<?php include 'includes/admin_footer.php'; ?>

<script>
async function updateStatus(select, requestId) {
  const badge = select.closest('tr').querySelector('.badge');
  const newStatus = select.value;

  try {
    const response = await fetch('update_status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id=${requestId}&status=${newStatus}`
    });
    
    const result = await response.json();
    
    if (result.success) {
      // Update UI badge
      badge.className = 'badge';
      const map = {
        pending:     ['badge-pending',  'Pending'],
        in_progress: ['badge-progress', 'In Progress'],
        completed:   ['badge-completed','Completed'],
      };
      badge.classList.add(map[newStatus][0]);
      badge.textContent = map[newStatus][1];
    } else {
      alert('Failed to update status: ' + result.message);
    }
  } catch (error) {
    console.error('Error:', error);
  }
}
</script>
</body>
</html>