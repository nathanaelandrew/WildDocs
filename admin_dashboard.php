<?php
// admin_dashboard.php — WildDocuments Admin Dashboard
session_start();
// Uncomment when auth is wired:
// if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
// include 'includes/db.php';
// $pdo = getDB();
// $requests = fetchAllRequests($pdo);
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

      <!-- Welcome Banner -->
      <div class="welcome-banner">
        <div>
          <h2>Welcome, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>!</h2>
          <p>Here's an overview of all document requests in the system.</p>
        </div>
        <a href="admin_requests.php" class="btn btn-primary">View All Requests →</a>
      </div>

      <!-- Stat Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-card__icon">📋</div>
          <div class="stat-card__label">Total Requests</div>
          <div class="stat-card__value">128</div>
          <!-- TODO: echo $stats['total']; -->
          <div class="stat-card__sub">All time</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__icon">🕐</div>
          <div class="stat-card__label">Pending</div>
          <div class="stat-card__value stat-card--gold" style="color:#8A6000">34</div>
          <!-- TODO: echo $stats['pending']; -->
          <div class="stat-card__sub">Awaiting action</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__icon">⚙️</div>
          <div class="stat-card__label">In Progress</div>
          <div class="stat-card__value stat-card--blue" style="color:#1A4A6E">22</div>
          <!-- TODO: echo $stats['in_progress']; -->
          <div class="stat-card__sub">Being processed</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__icon">✅</div>
          <div class="stat-card__label">Completed</div>
          <div class="stat-card__value stat-card--green" style="color:#1A6E3C">72</div>
          <!-- TODO: echo $stats['completed']; -->
          <div class="stat-card__sub">Fulfilled</div>
        </div>
      </div>

      <!-- Recent Requests Table -->
      <div class="card">
        <div class="card__header">
          <h3>Recent Requests</h3>
          <a href="admin_requests.php" class="btn btn-ghost btn-sm">View All →</a>
        </div>
        <div class="card__body" style="padding:0">

          <!-- Filter Bar -->
          <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
            <div class="filter-bar" style="margin:0">
              <input type="text" class="form-control" placeholder="🔍  Search name or ID…" style="max-width:240px">
              <select class="form-control" style="max-width:160px">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
              </select>
              <select class="form-control" style="max-width:180px">
                <option value="">All Documents</option>
                <option>Official Transcript</option>
                <option>Diploma Copy</option>
                <option>Certification Letter</option>
                <option>Academic Records</option>
              </select>
            </div>
          </div>

          <div class="table-wrapper" style="border:none;border-radius:0">
            <table class="data-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Full Name</th>
                  <th>Student ID</th>
                  <th>Program</th>
                  <th>Document Requested</th>
                  <th>Amount</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                // TODO: Replace with real DB loop: foreach ($requests as $row) { ... }
                $sample = [
                  ['Juan dela Cruz',   '2021-00123', 'BS Computer Science', 'Official Transcript',  '₱150', 'May 1, 2026',  'pending'],
                  ['Maria Santos',     '2020-00456', 'BS Nursing',           'Diploma Copy',         '₱200', 'May 1, 2026',  'in_progress'],
                  ['Carlos Reyes',     '2019-00789', 'BS Education',         'Certification Letter', '₱100', 'Apr 30, 2026', 'completed'],
                  ['Ana Liza Mendoza', '2022-00321', 'BS Accountancy',       'Academic Records',     '₱175', 'Apr 30, 2026', 'pending'],
                  ['Ricky Villanueva', '2021-00654', 'BS Engineering',       'Official Transcript',  '₱150', 'Apr 29, 2026', 'completed'],
                  ['Sophia Laurel',    '2023-00987', 'BS Psychology',        'Certification Letter', '₱100', 'Apr 29, 2026', 'in_progress'],
                ];
                $i = 1;
                foreach ($sample as $row):
                  $badgeClass = match($row[6]) {
                    'pending'     => 'badge-pending',
                    'in_progress' => 'badge-progress',
                    'completed'   => 'badge-completed',
                    default       => 'badge-pending'
                  };
                  $badgeLabel = match($row[6]) {
                    'pending'     => 'Pending',
                    'in_progress' => 'In Progress',
                    'completed'   => 'Completed',
                    default       => 'Unknown'
                  };
                ?>
                <tr>
                  <td class="col-id"><?= $i++ ?></td>
                  <td class="col-name"><?= htmlspecialchars($row[0]) ?></td>
                  <td class="col-id"><?= htmlspecialchars($row[1]) ?></td>
                  <td><?= htmlspecialchars($row[2]) ?></td>
                  <td><?= htmlspecialchars($row[3]) ?></td>
                  <td style="font-weight:600"><?= $row[4] ?></td>
                  <td class="col-id"><?= $row[5] ?></td>
                  <td><span class="badge <?= $badgeClass ?>"><?= $badgeLabel ?></span></td>
                  <td>
                    <!-- TODO: onChange POST to update_status.php with request_id -->
                    <select class="status-select" onchange="updateStatus(this, <?= $i-1 ?>)">
                      <option value="pending"     <?= $row[6]==='pending'     ? 'selected':'' ?>>Pending</option>
                      <option value="in_progress" <?= $row[6]==='in_progress' ? 'selected':'' ?>>In Progress</option>
                      <option value="completed"   <?= $row[6]==='completed'   ? 'selected':'' ?>>Completed</option>
                    </select>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

        </div><!-- /.card__body -->
      </div><!-- /.card -->

    </div><!-- /.dashboard-page -->
  </main>
</div>

<?php include 'includes/admin_footer.php'; ?>

<script>
function updateStatus(select, rowId) {
  const row   = select.closest('tr');
  const badge = row.querySelector('.badge');
  const val   = select.value;

  badge.className = 'badge';
  const map = {
    pending:     ['badge-pending',  'Pending'],
    in_progress: ['badge-progress', 'In Progress'],
    completed:   ['badge-completed','Completed'],
  };
  badge.classList.add(map[val][0]);
  badge.textContent = map[val][1];

  // TODO: AJAX POST to update_status.php
  // fetch('update_status.php', { method: 'POST', body: JSON.stringify({ request_id: rowId, status: val }), headers: { 'Content-Type': 'application/json' } });
  console.log('Status updated → row', rowId, ':', val);
}
</script>

</body>
</html>