<?php
// 1. Session and Auth Check MUST be at the very top, before ANY HTML
session_start();

// if (!isset($_SESSION['admin_id'])) { 
//     header('Location: login.php'); 
//     exit; 
// }

// TODO: $requests = fetchAllRequests($pdo);

$rows = [
    [1,  'Juan dela Cruz',    '2021-00123', 'BS Computer Science', 'Official Transcript',  '₱150', 'May 1, 2026',  'pending'],
    [2,  'Maria Santos',      '2020-00456', 'BS Nursing',           'Diploma Copy',         '₱200', 'May 1, 2026',  'in_progress'],
    [3,  'Carlos Reyes',      '2019-00789', 'BS Education',         'Certification Letter', '₱100', 'Apr 30, 2026', 'completed'],
    [4,  'Ana Liza Mendoza',  '2022-00321', 'BS Accountancy',       'Academic Records',     '₱175', 'Apr 30, 2026', 'pending'],
    [5,  'Ricky Villanueva',  '2021-00654', 'BS Engineering',       'Official Transcript',  '₱150', 'Apr 29, 2026', 'completed'],
    [6,  'Sophia Laurel',     '2023-00987', 'BS Psychology',        'Certification Letter', '₱100', 'Apr 29, 2026', 'in_progress'],
    [7,  'Miguel Torres',     '2020-00111', 'BS Chemistry',         'Diploma Copy',         '₱200', 'Apr 28, 2026', 'pending'],
    [8,  'Grace Aquino',      '2022-00555', 'BS Architecture',      'Official Transcript',  '₱150', 'Apr 28, 2026', 'completed'],
    [9,  'Patrick Lim',       '2021-00888', 'BS Information Tech',  'Academic Records',     '₱175', 'Apr 27, 2026', 'pending'],
    [10, 'Carla Bautista',    '2019-00222', 'BS Social Work',       'Certification Letter', '₱100', 'Apr 27, 2026', 'in_progress'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Request – WildDocuments Admin</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include 'includes/admin_navbar.php'; ?>

<div class="app-layout">
  <?php include 'includes/admin_sidebar.php'; ?>

  <main class="main-content">
    <div class="dashboard-page">

      <div class="page-title-row">
        <div>
          <h2>My Request</h2>
          <p>Manage and update all incoming document requests.</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
          <button class="btn btn-ghost btn-sm" onclick="window.print()">🖨️ Export</button>
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="card" style="margin-bottom:20px">
        <div class="card__body" style="padding:16px 20px">
          <div class="filter-bar" style="margin:0">
            <input type="text" class="form-control" placeholder="🔍 Search..." style="max-width:260px;flex:1">
            <select class="form-control" style="max-width:155px">
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
            </select>
            <button class="btn btn-primary btn-sm">Filter</button>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="card">
        <div class="card__header">
          <h3>All Requests <span style="font-size:.8rem;font-weight:400;color:var(--text-muted)">(128 total)</span></h3>
        </div>
        <div class="card__body" style="padding:0">
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
                  <th>Date Submitted</th>
                  <th>Status</th>
                  <th>Update Status</th>
                  <th>Details</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($rows as $r): 
                  $bc = match($r[7]) { 'pending'=>'badge-pending','in_progress'=>'badge-progress','completed'=>'badge-completed', default=>'badge-pending' };
                  $bl = match($r[7]) { 'pending'=>'Pending','in_progress'=>'In Progress','completed'=>'Completed', default=>'Unknown' };
                ?>
                <tr id="row-<?= $r[0] ?>">
                  <td class="col-id"><?= $r[0] ?></td>
                  <td class="col-name"><?= htmlspecialchars($r[1]) ?></td>
                  <td class="col-id"><?= $r[2] ?></td>
                  <td><?= htmlspecialchars($r[3]) ?></td>
                  <td><?= htmlspecialchars($r[4]) ?></td>
                  <td style="font-weight:600"><?= $r[5] ?></td>
                  <td class="col-id"><?= $r[6] ?></td>
                  <td><span class="badge <?= $bc ?>" id="badge-<?= $r[0] ?>"><?= $bl ?></span></td>
                  <td>
                    <select class="status-select" onchange="updateStatus(this, <?= $r[0] ?>)">
                      <option value="pending" <?= $r[7]==='pending' ? 'selected':'' ?>>Pending</option>
                      <option value="in_progress" <?= $r[7]==='in_progress' ? 'selected':'' ?>>In Progress</option>
                      <option value="completed" <?= $r[7]==='completed' ? 'selected':'' ?>>Completed</option>
                    </select>
                  </td>
                  <td>
                    <button class="btn btn-ghost btn-sm" onclick="viewDetails(<?= $r[0] ?>)" title="View Details">👁</button>
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

<!-- Modal -->
<div class="modal-overlay" id="detailModal">
  <div class="modal" style="max-width:460px;text-align:left">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
      <h3 style="font-size:1.05rem">Request Details</h3>
      <button onclick="closeModal()" style="background:none;border:none;font-size:1.3rem;cursor:pointer;">×</button>
    </div>
    <div id="modalContent"></div>
    <div style="margin-top:20px;display:flex;gap:8px;justify-content:flex-end">
      <button class="btn btn-ghost btn-sm" onclick="closeModal()">Close</button>
    </div>
  </div>
</div>

<?php include 'includes/admin_footer.php'; ?>

<script>
// JS functions remain largely the same, but ensure statusMap matches your values
const statusMap = {
  pending: ['badge-pending', 'Pending'],
  in_progress: ['badge-progress', 'In Progress'],
  completed: ['badge-completed', 'Completed'],
};

function updateStatus(select, id) {
  const val = select.value;
  const badge = document.getElementById('badge-' + id);
  if(badge && statusMap[val]) {
    badge.className = 'badge ' + statusMap[val][0];
    badge.textContent = statusMap[val][1];
  }
}

function viewDetails(id) {
  // Mock data for demonstration
  const d = { name:'Juan dela Cruz', student_id:'2021-00123', program:'BS CS', document:'Transcript', amount:'₱150', date:'May 1', notes:'Job application' };
  document.getElementById('modalContent').innerHTML = `<strong>Name:</strong> ${d.name}<br><strong>ID:</strong> ${d.student_id}`;
  document.getElementById('detailModal').classList.add('open');
}

function closeModal() {
  document.getElementById('detailModal').classList.remove('open');
}
</script>
</body>
</html>