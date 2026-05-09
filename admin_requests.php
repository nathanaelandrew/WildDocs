<?php
// admin_requests.php
session_start();
require_once 'includes/db.php';

// Auth check: Only allow logged-in admins
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { 
    header('Location: login.php'); 
    exit; 
}

$pdo = getDB();

// --- 1. HANDLE FILTERS & SEARCH ---
$search = trim($_GET['search'] ?? '');
$filterStatus = $_GET['status'] ?? '';

// Build the SQL Query dynamically based on filters
$sql = "SELECT r.*, u.first_name, u.last_name, s.student_number, s.program, s.year_level
        FROM requests r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN students s ON u.id = s.user_id
        WHERE 1=1"; // Placeholder to allow adding AND conditions

$params = [];

if (!empty($search)) {
    $sql .= " AND (r.reference_number ILIKE ? OR u.first_name ILIKE ? OR u.last_name ILIKE ? OR s.student_number ILIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

if (!empty($filterStatus)) {
    $sql .= " AND r.status = ?";
    $params[] = $filterStatus;
}

$sql .= " ORDER BY r.created_at DESC LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Helper for status badge colors
function getStatusBadgeClass($status) {
    return match($status) {
        'pending'  => 'badge-pending',
        'paid'     => 'badge-paid',
        'approved' => 'badge-progress',
        'released' => 'badge-completed',
        default    => 'badge-pending',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage All Requests – WildDocuments Admin</title>
  <link rel="stylesheet" href="css/styles.css">
  <style>
    .col-actions { display: flex; gap: 8px; justify-content: center; align-items: center; }
    .modal-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
    .modal-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); display: block; margin-bottom: 3px; }
    .modal-value { font-weight: 600; color: var(--text-dark); }
    .badge-paid { background:#EFF6FF; color:#1D4ED8; }
    
    /* Ensure filter bar looks clean */
    .filter-card { margin-bottom: 20px; background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border); }
  </style>
</head>
<body>

<?php include 'includes/admin_navbar.php'; ?>

<div class="app-layout">
  <?php include 'includes/admin_sidebar.php'; ?>

  <main class="main-content" style="background: var(--bg-light); min-height: 100vh; padding-bottom: 80px;">
    <div class="dashboard-page">

      <div class="page-title-row">
        <div>
          <h2>Manage All Requests</h2>
          <p>Review and process the complete database of document applications.</p>
        </div>
        <button class="btn btn-ghost btn-sm" onclick="window.print()">🖨️ Export List</button>
      </div>

      <!-- --- 2. SEARCH AND FILTER BAR --- -->
      <div class="filter-card">
        <div class="card__body" style="padding:16px 20px">
          <form method="GET" style="display:flex; gap:12px; align-items:center; flex-wrap: wrap;">
            <div style="flex: 0 1 450px; min-width: 300px;">
                <input type="text" 
                      name="search" 
                      class="form-control" 
                      placeholder="Search by Reference, Name, or ID..." 
                      value="<?= htmlspecialchars($search) ?>">
            </div>
            <div style="width: 180px;">
                <select name="status" class="form-control">
                  <option value="">All Statuses</option>
                  <option value="pending"  <?= $filterStatus === 'pending' ? 'selected':'' ?>>Pending</option>
                  <option value="paid"     <?= $filterStatus === 'paid' ? 'selected':'' ?>>Paid</option>
                  <option value="approved" <?= $filterStatus === 'approved' ? 'selected':'' ?>>Approved</option>
                  <option value="released" <?= $filterStatus === 'released' ? 'selected':'' ?>>Released</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <?php if(!empty($search) || !empty($filterStatus)): ?>
                <a href="admin_requests.php" class="btn btn-ghost">Clear</a>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <!-- Requests Table -->
      <div class="card">
        <div class="card__header">
          <h3>Requests Database <span style="font-size: 0.8rem; font-weight: 400; color: var(--text-muted); margin-left: 8px;">(Found <?= count($requests) ?> results)</span></h3>
        </div>
        <div class="card__body" style="padding:0">
          <div class="table-wrapper" style="border:none">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Ref #</th>
                  <th>Student Name</th>
                  <th>Program</th>
                  <th>Document</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th style="text-align:center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($requests)): ?>
                  <tr><td colspan="7" style="text-align:center; padding:50px; color:var(--text-muted)">No matching records found.</td></tr>
                <?php else: ?>
                  <?php foreach ($requests as $r): ?>
                  <tr>
                    <td style="font-weight:700; color:var(--crimson)"><?= htmlspecialchars($r['reference_number']) ?></td>
                    <td>
                        <div style="font-weight:600"><?= htmlspecialchars($r['first_name'].' '.$r['last_name']) ?></div>
                        <div style="font-size:0.75rem; color:var(--text-muted)"><?= htmlspecialchars($r['student_number'] ?? 'N/A') ?></div>
                    </td>
                    <td style="font-size:.85rem"><?= htmlspecialchars($r['program']) ?></td>
                    <td style="font-weight:500"><?= htmlspecialchars($r['document_name']) ?></td>
                    <td style="font-size:.85rem"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                    <td><span class="badge <?= getStatusBadgeClass($r['status']) ?>"><?= ucfirst($r['status']) ?></span></td>
                    <td class="col-actions">
                        <select class="status-select" onchange="updateStatus(<?= $r['id'] ?>, this.value, event)">
                          <option value="pending"  <?= $r['status'] === 'pending' ? 'selected':'' ?>>Pending</option>
                          <option value="paid"     <?= $r['status'] === 'paid' ? 'selected':'' ?>>Paid</option>
                          <option value="approved" <?= $r['status'] === 'approved' ? 'selected':'' ?>>Approved</option>
                          <option value="released" <?= $r['status'] === 'released' ? 'selected':'' ?>>Released</option>
                        </select>
                        <button class="btn btn-ghost btn-sm" onclick='viewDetails(<?= htmlspecialchars(json_encode($r), ENT_QUOTES, "UTF-8") ?>)'>Details</button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Request Details Modal -->
<div class="modal-overlay" id="detailsModal">
    <div class="modal" style="max-width: 500px; text-align: left;">
        <h3 id="modalRef" style="color: var(--crimson); margin-bottom: 5px;">Ref #</h3>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px;">Detailed Request Information</p>
        <div class="modal-info-grid">
            <div><span class="modal-label">Student Name</span><div id="modalName" class="modal-value">-</div></div>
            <div><span class="modal-label">Student ID</span><div id="modalID" class="modal-value">-</div></div>
            <div><span class="modal-label">Program & Year</span><div id="modalProgram" class="modal-value">-</div></div>
            <div><span class="modal-label">Document Requested</span><div id="modalDoc" class="modal-value">-</div></div>
            <div><span class="modal-label">Total Fee</span><div id="modalAmount" class="modal-value">-</div></div>
            <div><span class="modal-label">Current Status</span><div id="modalStatus" class="modal-value">-</div></div>
        </div>
        <div style="background: var(--pink-bg); padding: 15px; border-radius: 8px; border-left: 4px solid var(--crimson);">
            <span class="modal-label">Purpose of Request</span>
            <div id="modalPurpose" style="font-style: italic; color: var(--text-mid); font-size: 0.9rem; line-height:1.4">-</div>
        </div>
        <div style="margin-top: 25px; text-align: right;">
            <button class="btn btn-primary" onclick="closeModal()">Close Details</button>
        </div>
    </div>
</div>

<script>
function viewDetails(request) {
    document.getElementById('modalRef').innerText = 'Ref: ' + request.reference_number;
    document.getElementById('modalName').innerText = request.first_name + ' ' + request.last_name;
    document.getElementById('modalID').innerText = request.student_number || 'N/A';
    document.getElementById('modalProgram').innerText = request.program + '\n(Year ' + (request.year_level || 'N/A') + ')';
    document.getElementById('modalDoc').innerText = request.document_name;
    document.getElementById('modalAmount').innerText = '₱' + parseFloat(request.total_amount).toLocaleString();
    document.getElementById('modalStatus').innerText = request.status.toUpperCase();
    document.getElementById('modalPurpose').innerText = request.purpose || 'No purpose specified.';
    document.getElementById('detailsModal').classList.add('open');
}

function closeModal() { document.getElementById('detailsModal').classList.remove('open'); }

function updateStatus(requestId, newStatus, event) {
    const select = event.target;
    select.disabled = true;
    select.style.opacity = '0.5';
    fetch('update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ requestId: requestId, status: newStatus })
    })
    .then(response => response.json())
    .then(data => { if (data.success) location.reload(); else alert('Error: ' + data.message); })
    .catch(() => alert('Network error.'));
}

window.onclick = function(event) { if (event.target == document.getElementById('detailsModal')) closeModal(); }
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>