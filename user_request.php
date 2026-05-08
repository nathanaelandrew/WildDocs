<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// PREVIEW MODE - Force user identity for sidebar/navbar consistency
$_SESSION['user_name'] = "Student User"; 

$documentPricing = array(
    ''                      => array('label' => '— Select a document —', 'price' => 0),
    'official_transcript'   => array('label' => 'Official Transcript',   'price' => 150),
    'diploma_copy'          => array('label' => 'Diploma Copy',          'price' => 200),
    'certification_letter'  => array('label' => 'Certification Letter',  'price' => 100),
    'academic_records'      => array('label' => 'Academic Records',      'price' => 175),
    'honorable_dismissal'   => array('label' => 'Honorable Dismissal',   'price' => 120),
    'deans_list'            => array('label' => "Dean's List Certificate",'price' => 80),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Document – WildDocuments</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php if (file_exists('includes/user_navbar.php')) include 'includes/user_navbar.php'; ?>

<div class="app-layout">
    <?php if (file_exists('includes/user_sidebar.php')) include 'includes/user_sidebar.php'; ?>

    <main class="main-content">
        <!-- 1. SEAMLESS PAGE HEADER -->
        <header class="page-header">
            <div class="container">
                <span class="page-header__eyebrow">Application</span>
                <h1>New Request</h1>
                <p>Provide your details below to apply for official school documents.</p>
            </div>
        </header>

        <div class="dashboard-page">
            <div class="container--sm">
                
                <div class="card fade-up">
                    <div class="card__header">
                        <h3>Document Details</h3>
                    </div>
                    
                    <div class="card__body">
                        <form action="payment.php" method="GET" id="requestForm">
                            
                            <div class="form-section">
                                <div class="form-section__title">Document Information</div>
                                
                                <div class="form-group">
                                    <label class="form-label">Document Type <span class="req">*</span></label>
                                    <select name="document_type" id="document_type" class="form-control" onchange="updateAmount()">
                                        <?php foreach ($documentPricing as $key => $doc): ?>
                                            <option value="<?php echo $key; ?>">
                                                <?php echo $doc['label']; ?> 
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="form-section__title">Personal Details</div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">First Name <span class="req">*</span></label>
                                        <input type="text" name="first_name" id="first_name" class="form-control" placeholder="Enter first name" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Last Name <span class="req">*</span></label>
                                        <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Enter last name" required>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. SEAMLESS AMOUNT DISPLAY (From your CSS) -->
                            <div class="amount-display">
                                <div class="amount-display__label">Total to Pay</div>
                                <div class="amount-display__value">
                                    <span class="amount-display__currency">₱</span><span id="price_val">0</span>
                                </div>
                                <div id="doc_name_display" class="amount-display__doc">Please select a document</div>
                            </div>

                            <button type="submit" id="submitBtn" class="btn btn-primary btn-xl btn-block" disabled>
                                Proceed to Payment →
                            </button>
                        </form>
                    </div>
                </div><!-- /.card -->

                <p class="text-center" style="margin-top:20px; font-size:0.8rem; color:var(--text-muted);">
                    All requests are subject to verification by the registrar's office.
                </p>

            </div>
        </div>
    </main>
</div>

<?php if (file_exists('includes/footer.php')) include 'includes/footer.php'; ?>


<script>
// Logic using the pricing array
var docData = {
    <?php 
    foreach($documentPricing as $k => $v) {
        echo "'$k': {price: " . $v['price'] . ", label: '" . $v['label'] . "'},";
    }
    ?>
};

function updateAmount() {
    var select = document.getElementById('document_type');
    var selectedKey = select.value;
    var data = docData[selectedKey] || {price: 0, label: 'Please select a document'};
    
    // Update Amount Display
    document.getElementById('price_val').innerHTML = data.price.toFixed(2);
    document.getElementById('doc_name_display').innerHTML = data.label;
    
    // Validation
    var fName = document.getElementById('first_name').value.trim();
    var lName = document.getElementById('last_name').value.trim();
    
    var isValid = (data.price > 0 && fName !== "" && lName !== "");
    document.getElementById('submitBtn').disabled = !isValid;
}

// Watch inputs
document.getElementById('first_name').oninput = updateAmount;
document.getElementById('last_name').oninput = updateAmount;
</script>

</body>
</html>