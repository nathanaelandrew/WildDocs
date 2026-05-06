<?php
// 1. FORCE ERRORS (Standard way)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// PREVIEW MODE
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 999;
}

// 2. DATA ARRAY (Old PHP Compatible style)
$documentPricing = array(
    ''                      => array('label' => '— Select a document —', 'price' => 0),
    'official_transcript'   => array('label' => 'Official Transcript',   'price' => 150),
    'diploma_copy'          => array('label' => 'Diploma Copy',          'price' => 200),
    'certification_letter'  => array('label' => 'Certification Letter',  'price' => 100),
    'academic_records'      => array('label' => 'Academic Records',      'price' => 175),
    'honorable_dismissal'   => array('label' => 'Honorable Dismissal',   'price' => 120),
    'deans_list'            => array('label' => "Dean's List Certificate",'price' => 80),
    'authentication'        => array('label' => 'Authentication',        'price' => 90),
    'course_description'    => array('label' => 'Course Description',    'price' => 60),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request a Document</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php 
if (file_exists('includes/user_navbar.php')) {
    include 'includes/user_navbar.php'; 
} else {
    echo "<div style='padding:10px; background:red; color:white;'>Missing includes/navbar.php</div>";
}
?>

<div class="app-layout">
    <?php 
    // Only include if file exists to prevent crash
    if (file_exists('includes/user_sidebar.php')) include 'includes/user_sidebar.php'; 
    ?>

    <main style="flex:1; padding:40px;">
        <div class="card">
            <form action="payment.php" method="GET" id="requestForm">
                <h2>Request Document</h2>
                
                <div style="margin-bottom:15px">
                    <label>Document Type *</label>
                    <select name="document_type" id="document_type" class="form-control" onchange="updateAmount()">
                        <?php foreach ($documentPricing as $key => $doc): ?>
                            <option value="<?php echo $key; ?>">
                                <?php echo $doc['label']; ?> 
                                <?php if($doc['price'] > 0) echo " — ₱".$doc['price']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-bottom:15px">
                    <label>First Name *</label>
                    <input type="text" name="first_name" id="first_name" class="form-control" required>
                </div>

                <div style="margin-bottom:15px">
                    <label>Last Name *</label>
                    <input type="text" name="last_name" id="last_name" class="form-control" required>
                </div>

                <div style="background:#fff5f5; padding:20px; border-radius:8px; margin:20px 0;">
                    <strong>Total Amount: </strong>
                    <span id="amount_display" style="font-size:24px; color:#8B1A2E;">₱0.00</span>
                </div>

                <button type="submit" id="submitBtn" class="btn btn-primary" style="width:100%; padding:15px;" disabled>Proceed</button>
            </form>
        </div>
    </main>
</div>

<script>
// Legacy JS for compatibility
var prices = {
    <?php 
    foreach($documentPricing as $k => $v) {
        echo "'$k': " . $v['price'] . ",";
    }
    ?>
};

function updateAmount() {
    var select = document.getElementById('document_type');
    var price = prices[select.value] || 0;
    document.getElementById('amount_display').innerHTML = '₱' + price.toFixed(2);
    
    var fName = document.getElementById('first_name').value;
    var lName = document.getElementById('last_name').value;
    
    document.getElementById('submitBtn').disabled = (price === 0 || fName === "" || lName === "");
}

// Watch for typing
document.getElementById('first_name').oninput = updateAmount;
document.getElementById('last_name').oninput = updateAmount;
</script>

</body>
</html>