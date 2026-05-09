<?php
// student_request.php
session_start();
require_once 'includes/db.php';

// Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') { 
    header('Location: login.php'); 
    exit; 
}

$pdo = getDB();

// Fetch current student details from the session or database to auto-fill
// Assuming these were stored during login. If not, we fetch from DB.
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users u JOIN students s ON u.id = s.user_id WHERE u.id = ?");
$stmt->execute([$userId]);
$student = $stmt->fetch();

$documentPricing = array(
    ''                      => array('label' => '— Select a document —', 'price' => 0),
    'official_transcript'   => array('label' => 'Official Transcript',   'price' => 150),
    'diploma_copy'          => array('label' => 'Diploma Copy',          'price' => 200),
    'certification_letter'  => array('label' => 'Certification Letter',  'price' => 100),
    'academic_records'      => array('label' => 'Academic Records',      'price' => 175),
    'honorable_dismissal'   => array('label' => 'Honorable Dismissal',   'price' => 120),
    'deans_list'            => array('label' => "Dean's List Certificate",'price' => 80),
);

// Handle Form Submission
$successMsg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $docKey = $_POST['document_type'];
    $purpose = trim($_POST['purpose']);
    
    if (isset($documentPricing[$docKey]) && $docKey !== '') {
        $docLabel = $documentPricing[$docKey]['label'];
        $amount = $documentPricing[$docKey]['price'];
        $refNum = "WD-" . date('Y') . "-" . strtoupper(bin2hex(random_bytes(3)));

        $insert = $pdo->prepare("
            INSERT INTO requests (user_id, document_name, total_amount, reference_number, status, purpose, copies) 
            VALUES (?, ?, ?, ?, 'pending', ?, 1)
        ");
        
        if ($insert->execute([$userId, $docLabel, $amount, $refNum, $purpose])) {
            $successMsg = "Request submitted successfully! Ref: $refNum";
        }
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
        /* CSS to lessen header size as requested */
        .page-header--slim { padding: 20px 0; } 
        .page-header--slim h1 { font-size: 1.5rem; margin: 0; }
        .page-header--slim p { margin: 2px 0 0; font-size: 0.85rem; }
    </style>
</head>
<body>

<?php include 'includes/student_navbar.php'; ?>

<div class="app-layout">
    <?php include 'includes/student_sidebar.php'; ?>

    <main class="main-content">
        <!-- SLIMMED PAGE HEADER -->
        <header class="page-header page-header--slim">
            <div class="container">
                <span class="page-header__eyebrow">Request Form</span>
                <h1>New Document Application</h1>
                <p>Fill in the details to request your official documents.</p>
            </div>
        </header>

        <div class="dashboard-page">
            <div class="container--sm">
                
                <?php if ($successMsg): ?>
                    <div class="alert alert-success">
                        <span class="icon">✅</span> <?php echo $successMsg; ?>
                    </div>
                <?php endif; ?>

                <div class="card fade-up">
                    <div class="card__header">
                        <h3>Application Form</h3>
                    </div>
                    
                    <div class="card__body">
                        <form method="POST" id="requestForm">
                            
                            <div class="form-section">
                                <div class="form-section__title">Student Information</div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Student ID Number</label>
                                        <input type="text" class="form-control" value="<?= $student['student_number'] ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control" value="<?= $student['first_name'] . ' ' . $student['last_name'] ?>" readonly>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Program</label>
                                        <input type="text" class="form-control" value="<?= $student['program'] ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Year Level</label>
                                        <input type="text" class="form-control" value="<?= $student['year_level'] ?>nd Year" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="form-section__title">Document Selection</div>
                                <div class="form-group">
                                    <label class="form-label">Document Type <span class="req">*</span></label>
                                    <select name="document_type" id="document_type" class="form-control" onchange="updateAmount()" required>
                                        <?php foreach ($documentPricing as $key => $doc): ?>
                                            <option value="<?php echo $key; ?>">
                                                <?php echo $doc['label']; ?> 
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Purpose <span class="req">*</span></label>
                                    <textarea name="purpose" class="form-control" placeholder="e.g. For employment, scholarship, etc." required></textarea>
                                </div>
                            </div>

                            <!-- AMOUNT DISPLAY -->
                            <div class="amount-display">
                                <div class="amount-display__label">Processing Fee</div>
                                <div class="amount-display__value">
                                    <span class="amount-display__currency">₱</span><span id="price_val">0</span>
                                </div>
                                <div id="doc_name_display" class="amount-display__doc">No document selected</div>
                            </div>

                            <button type="submit" id="submitBtn" class="btn btn-primary btn-xl btn-block" disabled>
                                Submit Request
                            </button>
                        </form>
                    </div>
                </div>

                <p class="text-center" style="margin-top:20px; font-size:0.8rem; color:var(--text-muted);">
                    Fees are non-refundable once the request is processed.
                </p>
            </div>
        </div>
    </main>
</div>

<script>
// Pricing logic for auto-update
const docData = {
    <?php foreach($documentPricing as $k => $v): ?>
        "<?= $k ?>": {price: <?= $v['price'] ?>, label: "<?= $v['label'] ?>"},
    <?php endforeach; ?>
};

function updateAmount() {
    const select = document.getElementById('document_type');
    const selectedKey = select.value;
    const data = docData[selectedKey] || {price: 0, label: 'No document selected'};
    
    document.getElementById('price_val').innerHTML = data.price.toFixed(2);
    document.getElementById('doc_name_display').innerHTML = data.label;
    
    // Enable button only if document is selected
    document.getElementById('submitBtn').disabled = (data.price <= 0);
}
</script>

</body>
</html>