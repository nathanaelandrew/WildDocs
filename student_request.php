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

// 1. Fetch student verification details (Parent/Child JOIN)
$stmt = $pdo->prepare("
    SELECT u.first_name, u.last_name, s.student_number, s.program, s.year_level 
    FROM users u 
    JOIN students s ON u.id = s.user_id 
    WHERE u.id = ?
");
$stmt->execute([$userId]);
$student = $stmt->fetch();

// 2. Fetch Document Types from the Database
$docStmt = $pdo->query("SELECT * FROM document_types ORDER BY name ASC");
$dbDocuments = $docStmt->fetchAll();

// Year Level Suffix Logic (1st, 2nd, 3rd...)
$year = $student['year_level'] ?? 0;
$suffix = match((int)$year) {
    1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th'
};

// 3. Handle Form Submission
$successMsg = "";
$errorMsg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $docTypeId = $_POST['document_type_id'] ?? '';
    $purpose   = trim($_POST['purpose'] ?? '');
    
    // Find the price from our DB results to ensure data integrity
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
            
            // Insert using the Normalized ID
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

            $successMsg = "Request submitted successfully! Ref: <strong>$refNum</strong>";
        } catch (Exception $e) {
            $errorMsg = "Submission failed: " . $e->getMessage();
        }
    } else {
        $errorMsg = "Please complete all required fields.";
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
    <style>
        .page-header--slim { padding: 20px 0; } 
        .page-header--slim h1 { font-size: 1.5rem; margin: 0; }
        .page-header--slim p { margin: 2px 0 0; font-size: 0.85rem; }
        .badge-paid { background:#EFF6FF; color:#1D4ED8; }
    </style>
</head>
<body>

<?php include 'includes/student_navbar.php'; ?>

<div class="app-layout">
    <?php include 'includes/student_sidebar.php'; ?>

    <main class="main-content" style="background: var(--bg-light); min-height: 100vh; padding-bottom: 50px;">
        <!-- UI: SLIMMED PAGE HEADER -->
        <header class="page-header page-header--slim">
            <div class="container">
                <span class="page-header__eyebrow">Document Application</span>
                <h1>New Request</h1>
                <p>Provide your details below to apply for official school records.</p>
            </div>
        </header>

        <div class="dashboard-page">
            <div class="container--sm">
                
                <?php if ($successMsg): ?>
                    <div class="alert alert-success" style="margin-bottom: 24px;">✅ <?php echo $successMsg; ?></div>
                <?php endif; ?>

                <?php if ($errorMsg): ?>
                    <div class="alert alert-error" style="margin-bottom: 24px;">⚠️ <?php echo $errorMsg; ?></div>
                <?php endif; ?>

                <div class="card fade-up">
                    <div class="card__header">
                        <h3>Application Form</h3>
                    </div>
                    
                    <div class="card__body" style="padding: 30px;">
                        <form method="POST" id="requestForm">
                            
                            <!-- UI: STUDENT INFO SECTION (Read Only) -->
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
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Program</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($student['program']) ?>" readonly style="background:#f8fafc;">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Year Level</label>
                                        <input type="text" class="form-control" value="<?= $year . $suffix ?> Year" readonly style="background:#f8fafc;">
                                    </div>
                                </div>
                            </div>

                            <!-- UI: DOCUMENT SELECTION SECTION -->
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
                                    <textarea name="purpose" class="form-control" placeholder="Describe the purpose (e.g. For Board Exam, Transfer, Employment)" required rows="3"></textarea>
                                </div>
                            </div>

                            <!-- UI: CRIMSON AMOUNT DISPLAY -->
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

                <p class="text-center" style="margin-top:20px; font-size:0.8rem; color:var(--text-muted);">
                    All fees must be settled at the Cashier before processing starts.
                </p>
            </div>
        </div>
    </main>
</div>

<!-- UI: DYNAMIC PRICING SCRIPT -->
<script>
function updateAmount() {
    const select = document.getElementById('document_type');
    const selectedOption = select.options[select.selectedIndex];
    
    // Get price and label from the data attributes
    const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
    const label = selectedOption.text === "-- Select --" ? "No document selected" : selectedOption.text;
    
    // Update the UI Amount Display
    document.getElementById('price_val').innerHTML = price.toFixed(2);
    document.getElementById('doc_name_display').innerHTML = label;
    
    // Logic: Enable button only if a valid document is chosen
    const btn = document.getElementById('submitBtn');
    if (price > 0) {
        btn.disabled = false;
        btn.style.opacity = "1";
    } else {
        btn.disabled = true;
        btn.style.opacity = "0.5";
    }
}
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>