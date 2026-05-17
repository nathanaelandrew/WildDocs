<?php
// student_request.php
session_start();
require_once 'includes/db.php';

// Auth Check: Only allow logged-in students
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') { 
    header('Location: login.php'); 
    exit; 
}

$pdo = getDB();
$userId = $_SESSION['user_id'];

// 1. Fetch student verification details
$stmt = $pdo->prepare("
    SELECT u.first_name, u.last_name, s.student_number, s.program, s.year_level 
    FROM users u 
    JOIN students s ON u.id = s.user_id 
    WHERE u.id = ?
");
$stmt->execute([$userId]);
$student = $stmt->fetch();

// 2. Fetch Document Types
$docStmt = $pdo->query("SELECT * FROM document_types ORDER BY name ASC");
$dbDocuments = $docStmt->fetchAll();

// Year Level Suffix
$year = $student['year_level'] ?? 0;
$suffix = match((int)$year) {
    1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th'
};

// 3. Handle Form Submission
$swalType = "";
$swalTitle = "";
$swalMsg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $docTypeId = $_POST['document_type_id'] ?? '';
    $purpose   = trim($_POST['purpose'] ?? '');
    
    $selectedDoc = null;
    foreach($dbDocuments as $d) {
        if($d['id'] == $docTypeId) {
            $selectedDoc = $d;
            break;
        }
    }

    if ($selectedDoc && !empty($purpose)) {
        try {
            $refNum = "WD-" . date('Y') . "-" . strtoupper(bin2hex(random_bytes(3)));
            
            $insert = $pdo->prepare("
                INSERT INTO requests (user_id, document_type_id, total_amount, reference_number, status, purpose, copies, is_viewed) 
                VALUES (?, ?, ?, ?, 'pending', ?, 1, FALSE)
            ");
            
            $insert->execute([
                $userId, 
                $selectedDoc['id'], 
                $selectedDoc['base_price'], 
                $refNum, 
                $purpose
            ]);

            $swalType = "success";
            $swalTitle = "Request Submitted!";
            $swalMsg = "Your reference number is: $refNum";
        } catch (Exception $e) {
            $swalType = "error";
            $swalTitle = "Submission Failed";
            $swalMsg = $e->getMessage();
        }
    } else {
        $swalType = "warning";
        $swalTitle = "Missing Info";
        $swalMsg = "Please complete all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Document – WildDocuments</title>
    <link rel="stylesheet" href="css/styles.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .page-header--slim { padding: 20px 0; } 
        .page-header--slim h1 { font-size: 1.5rem; margin: 0; }
        .page-header--slim p { margin: 2px 0 0; font-size: 0.85rem; }
    </style>
</head>
<body>

<?php include 'includes/student_navbar.php'; ?>

<div class="app-layout">
    <?php include 'includes/student_sidebar.php'; ?>

    <main class="main-content" style="background: var(--bg-light); min-height: 100vh; padding-bottom: 50px;">
        <header class="page-header page-header--slim">
            <div class="container">
                <span class="page-header__eyebrow">Document Application</span>
                <h1>New Request</h1>
                <p>Provide your details below to request for official school records.</p>
            </div>
        </header>

        <div class="dashboard-page">
            <div class="container--sm">
                
                <div class="card fade-up">
                    <div class="card__header">
                        <h3>Application Form</h3>
                    </div>
                    
                    <div class="card__body" style="padding: 30px;">
                        <form method="POST" id="requestForm">
                            
                            <div class="form-section">
                                <div class="form-section__title">Student Information</div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Student ID Number</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($student['student_number']) ?>" readonly style="background:#f8fafc;">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>" readonly style="background:#f8fafc;">
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="form-section__title">Document Details</div>
                                <div class="form-group">
                                    <label class="form-label">Select Document <span class="req">*</span></label>
                                    <select name="document_type_id" id="document_type" class="form-control" onchange="updateAmount()" required>
                                        <option value="" data-price="0">-- Select --</option>
                                        <?php foreach ($dbDocuments as $doc): ?>
                                            <option value="<?= $doc['id'] ?>" data-price="<?= $doc['base_price'] ?>">
                                                <?= htmlspecialchars($doc['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Purpose of Request <span class="req">*</span></label>
                                    <textarea name="purpose" id="purpose" class="form-control" placeholder="Describe the purpose (e.g. For Board Exam, Transfer, Employment)" required rows="3"></textarea>
                                </div>
                            </div>

                            <div class="amount-display">
                                <div class="amount-display__label">Total to Pay</div>
                                <div class="amount-display__value">
                                    <span class="amount-display__currency">₱</span><span id="price_val">0.00</span>
                                </div>
                                <div id="doc_name_display" class="amount-display__doc">No document selected</div>
                            </div>

                            <button type="submit" id="submitBtn" class="btn btn-primary btn-xl btn-block" disabled>
                                Submit Document Request
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// 1. DYNAMIC PRICING SCRIPT
function updateAmount() {
    const select = document.getElementById('document_type');
    const selectedOption = select.options[select.selectedIndex];
    const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
    const label = selectedOption.text === "-- Select --" ? "No document selected" : selectedOption.text;
    
    document.getElementById('price_val').innerHTML = price.toFixed(2);
    document.getElementById('doc_name_display').innerHTML = label;
    
    const btn = document.getElementById('submitBtn');
    btn.disabled = (price <= 0);
    btn.style.opacity = (price > 0) ? "1" : "0.5";
}

// 2. THE SAFETY NET (CONFIRMATION BOX)
document.getElementById('requestForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Stop form from submitting immediately
    
    const docName = document.getElementById('document_type').options[document.getElementById('document_type').selectedIndex].text;
    const price = document.getElementById('price_val').innerText;
    const purpose = document.getElementById('purpose').value;

    Swal.fire({
        title: 'Confirm Request Details',
        html: `
            <div style="text-align: left; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <p><strong>Document:</strong> ${docName}</p>
                <p><strong>Total Amount:</strong> ₱${price}</p>
                <p><strong>Purpose:</strong> ${purpose}</p>
            </div>
            <p style="margin-top: 15px; font-size: 0.9rem; color: #64748b;">Please double-check before submitting.</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#991b1b', // Your crimson color
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Submit Request',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Re-trigger the submit without the event listener loop
            this.submit();
        }
    });
});

// 3. SHOW PHP SUCCESS/ERROR MESSAGES
<?php if ($swalType): ?>
    Swal.fire({
        icon: '<?= $swalType ?>',
        title: '<?= $swalTitle ?>',
        html: '<?= addslashes($swalMsg) ?>',
        confirmButtonColor: '#991b1b'
    });
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>