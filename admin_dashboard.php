<?php
include 'includes/db.php';
session_start();

// Auth check (Uses your existing login session)
if (!isset($_SESSION['admin_id'])) { 
    header('Location: login.php'); 
    exit; 
}

$pdo = getDB();
$stats = getDashboardStats($pdo);
$requests = fetchRecentRequests($pdo);
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