<?php
// admin_requests.php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { 
    header('Location: login.php'); 
    exit; 
}

$pdo = getDB();

$search = trim($_GET['search'] ?? '');
$filterStatus = $_GET['status'] ?? '';

// 1. Logic: Filter out 'released' requests (Archive only)
$sql = "SELECT 
            r.*, 
            u.first_name, u.last_name, 
            s.student_number, s.program, s.year_level,
            dt.name as document_name
        FROM requests r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN students s ON u.id = s.user_id
        JOIN document_types dt ON r.document_type_id = dt.id
        WHERE r.status != 'released'"; 

$params = [];

if (!empty($search)) {
    $sql .= " AND (r.reference_number ILIKE ? OR u.first_name ILIKE ? OR u.last_name ILIKE ? OR s.student_number ILIKE ?)";
    $searchTerm = "%$search%";
    array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
}

if (!empty($filterStatus)) {
    $sql .= " AND r.status = ?";
    $params[] = $filterStatus;
}

$sql .= " ORDER BY r.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

function getStatusBadgeClass($status) {
    return match($status) {
        'pending'  => 'badge-pending',
        'paid'     => 'badge-paid',
        'approved' => 'badge-progress',
        default    => 'badge-pending',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Active Requests – Admin</title>
  <link rel="stylesheet" href="css/styles.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .col-actions { display: flex; gap: 8px; justify-content: center; align-items: center; }
    
    /* Green Release Button */
    .btn-release-action { 
        background: #16a34a; 
        color: white; 
        border: none; 
        padding: 6px 12px; 
        border-radius: 6px; 
        cursor: pointer; 
        font-weight: 700;
        font-size: 0.75rem;
        animation: slideIn 0.3s ease;
        box-shadow: 0 2px 4px rgba(22, 163, 74, 0.2);
    }
    .btn-release-action:hover { background: #15803d; }
    
    @keyframes slideIn { from { opacity: 0; transform: translateX(10px); } to { opacity: 1; transform: translateX(0); } }
    
    .badge-paid { background:#EFF6FF; color:#1D4ED8; }
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
          <h2>Document Processing</h2>
          <p>Update statuses below. Once a document is <strong>Approved</strong>, you can <strong>Release</strong> it to the archive.</p>
        </div>
        <a href="admin_archive.php" class="btn btn-ghost">📂 View Released</a>
      </div>

      <!-- Table -->
      <div class="card">
        <div class="card__body" style="padding:0">
          <table class="data-table">
            <thead>
              <tr>
                <th>Ref #</th>
                <th>Student Name</th>
                <th>Document</th>
                <th>Date</th>
                <th style="text-align:center">Status</th>
                <th style="text-align:center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($requests as $r): ?>
              <tr>
                <td style="font-weight:700; color:var(--crimson)"><?= htmlspecialchars($r['reference_number']) ?></td>
                <td><?= htmlspecialchars($r['first_name'].' '.$r['last_name']) ?></td>
                <td><?= htmlspecialchars($r['document_name']) ?></td>
                <td style="font-size:0.85rem"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                <td style="text-align:center">
                    <span class="badge <?= getStatusBadgeClass($r['status']) ?>"><?= ucfirst($r['status']) ?></span>
                </td>
                <td class="col-actions">
                    <select class="status-select" 
                            data-current="<?= $r['status'] ?>" 
                            onchange="handleDropdownChange(<?= $r['id'] ?>, this)">
                        <option value="pending"  <?= (strtolower($r['status']) === 'pending') ? 'selected':'' ?>>Pending</option>
                        <option value="paid"     <?= (strtolower($r['status']) === 'paid') ? 'selected':'' ?>>Paid</option>
                        <option value="approved" <?= (strtolower($r['status']) === 'approved') ? 'selected':'' ?>>Approved</option>
                    </select>

                    <?php 
                        // Logic check: Is it approved?
                        $isApproved = (trim(strtolower($r['status'])) === 'approved');
                        
                        // Pick the color based on the logic
                        $btnStyle = $isApproved 
                            ? "background-color: #16a34a !important; cursor: pointer; opacity: 1;" 
                            : "background-color: #e2e8f0 !important; cursor: not-allowed; opacity: 0.7; color: #94a3b8 !important;";
                    ?>

                    <button id="release-btn-<?= $r['id'] ?>" 
                            type="button"
                            class="btn-release-action" 
                            style="<?= $btnStyle ?>"
                            <?= !$isApproved ? 'disabled' : '' ?>
                            onclick="confirmRelease(<?= $r['id'] ?>)">
                        Released
                    </button>

                    <button type="button" class="btn btn-ghost btn-sm" onclick='viewDetails(<?= json_encode($r) ?>)'>Details</button>
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

<script>
/**
 * Logic:
 * 1. If user changes status to 'Approved', show the green Release button.
 * 2. If user changes status away from 'Approved', hide the button.
 * 3. All dropdown changes trigger an immediate update.
 */
function handleDropdownChange(requestId, select) {
    const releaseBtn = document.getElementById('release-btn-' + requestId);
    const newStatus = select.value.toLowerCase();

    if (newStatus === 'approved') {
        // TURN GREEN
        releaseBtn.disabled = false;
        releaseBtn.style.backgroundColor = "#16a34a";
        releaseBtn.style.color = "#ffffff";
        releaseBtn.style.cursor = "pointer";
        releaseBtn.style.opacity = "1";
    } else {
        // TURN GRAY
        releaseBtn.disabled = true;
        releaseBtn.style.backgroundColor = "#e2e8f0";
        releaseBtn.style.color = "#94a3b8";
        releaseBtn.style.cursor = "not-allowed";
        releaseBtn.style.opacity = "0.7";
    }

    updateStatusAJAX(requestId, select.value, select);
}

function updateStatusAJAX(requestId, newStatus, select) {
    const oldStatus = select.getAttribute('data-current');
    const releaseBtn = document.getElementById('release-btn-' + requestId);
    
    Swal.fire({
        title: 'Update Status?',
        text: `Change this request to ${newStatus.toUpperCase()}?`,
        icon: 'question',
        showCancelButton: true,
        reverseButtons: true,
        confirmButtonColor: '#991b1b'
    }).then((result) => {
        if (result.isConfirmed) {
            performFetch(requestId, newStatus);
        } else {
            // USER CANCELLED: 
            // 1. Put the dropdown back to what it was
            select.value = oldStatus;
            
            // 2. Put the button back to its original state
            if (oldStatus === 'approved') {
                releaseBtn.disabled = false;
            } else {
                releaseBtn.disabled = true;
            }
        }
    });
}

function confirmRelease(requestId) {
    Swal.fire({
        title: 'Complete & Release?',
        html: 'Mark this document as released and <strong>move to archive</strong>?',
        icon: 'warning',
        showCancelButton: true,
        reverseButtons: true,
        confirmButtonColor: '#16a34a',
        confirmButtonText: 'Yes, Release'
    }).then((result) => {
        if (result.isConfirmed) {
            performFetch(requestId, 'released');
        }
    });
}

function performFetch(requestId, status) {
    fetch('update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ requestId: requestId, status: status })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({ 
                icon: 'success', 
                title: status === 'released' ? 'Moved to Archive' : 'Status Updated', 
                timer: 1000, 
                showConfirmButton: false 
            }).then(() => location.reload());
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
}

// ... viewDetails code ...
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>