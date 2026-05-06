<?php
// payment.php — WildDocuments Payment Confirmation Page
session_start();
// if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$firstName     = htmlspecialchars($_GET['first_name']     ?? 'Juan');
$lastName      = htmlspecialchars($_GET['last_name']      ?? 'dela Cruz');
$studentId     = htmlspecialchars($_GET['student_id']     ?? '2021-00123');
$program       = htmlspecialchars($_GET['program']        ?? 'BS Computer Science');
$email         = htmlspecialchars($_GET['email']          ?? '');
$documentType  = htmlspecialchars($_GET['document_type']  ?? '');
$documentLabel = htmlspecialchars($_GET['document_label'] ?? 'Official Transcript');
$copies        = (int)($_GET['copies'] ?? 1);
$purpose       = htmlspecialchars($_GET['purpose']        ?? '');
$remarks       = htmlspecialchars($_GET['remarks']        ?? '');
$amount        = (float)($_GET['amount'] ?? 150);
$fullName      = $firstName . ' ' . $lastName;
$amountFormatted = '₱' . number_format($amount, 2);
$referenceNo   = 'WD-' . strtoupper(substr(md5(uniqid()), 0, 8));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment — WildDocuments</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include 'includes/user_navbar.php'; ?>

<div class="app-layout">
  <?php include 'includes/user_sidebar.php'; ?>

  <main style="flex:1;background:var(--bg-light);overflow-x:hidden;">

    <!-- Page header strip -->
    <div style="background:linear-gradient(120deg,var(--crimson-dark) 0%,var(--crimson) 100%);padding:32px 0 28px">
      <div class="container--xs text-center">
        <span class="section__eyebrow" style="background:rgba(255,255,255,.15);color:rgba(255,255,255,.9);margin-bottom:10px">
          Step 2 of 2
        </span>
        <h1 style="color:#fff;font-size:clamp(1.4rem,3vw,2rem);margin-bottom:8px">Confirm Payment</h1>
        <p style="color:rgba(255,255,255,.65);font-size:.93rem">Review your request details before finalizing.</p>
      </div>
    </div>

    <!-- Main content -->
    <div style="padding:40px 0 64px">
      <div class="container--xs">

        <!-- Amount Display -->
        <div class="amount-display fade-up" style="margin-bottom:22px">
          <div class="amount-display__label">Total Amount Due</div>
          <div class="amount-display__value">
            <span class="amount-display__currency">₱</span><?= number_format($amount, 2) ?>
          </div>
          <div class="amount-display__doc">
            <?= $documentLabel ?><?= $copies > 1 ? " &times; {$copies} copies" : '' ?>
          </div>
        </div>

        <!-- Request Summary -->
        <div class="card fade-up" style="margin-bottom:18px">
          <div class="card__header">
            <h3>Request Summary</h3>
            <a href="user_request.php" style="font-size:.8rem;color:var(--crimson);font-weight:600">✏️ Edit</a>
          </div>
          <div class="card__body" style="padding:0">
            <table style="width:100%;border-collapse:collapse;font-size:.88rem">
              <?php
              $rows = [
                ['Full Name',          $fullName],
                ['Student ID',         $studentId],
                ['Program',            $program],
                ['Email',              $email ?: '—'],
                ['Document Requested', $documentLabel],
                ['Number of Copies',   $copies . ($copies > 1 ? ' copies' : ' copy')],
                ['Purpose',            $purpose ?: '—'],
                ['Reference No.',      $referenceNo],
              ];
              foreach ($rows as [$lbl, $val]):
              ?>
              <tr style="border-bottom:1px solid var(--border-light)">
                <td style="padding:11px 18px;color:var(--text-muted);font-size:.8rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;width:40%;white-space:nowrap">
                  <?= $lbl ?>
                </td>
                <td style="padding:11px 18px;font-weight:600;color:var(--text-dark)">
                  <?= $val ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </table>
            <?php if ($remarks): ?>
            <div style="padding:11px 18px;border-top:1px solid var(--border-light)">
              <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:4px">Remarks</div>
              <p style="font-size:.86rem;color:var(--text-dark);font-weight:500"><?= $remarks ?></p>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Payment Method -->
        <div class="card fade-up" style="margin-bottom:22px">
          <div class="card__header"><h3>Payment Method</h3></div>
          <div class="card__body">
            <div style="display:flex;flex-direction:column;gap:10px" id="paymentMethods">

              <label class="payment-method-option" style="display:flex;align-items:center;gap:14px;padding:14px 16px;border:2px solid var(--crimson);border-radius:var(--radius-lg);cursor:pointer;background:var(--pink-bg)">
                <input type="radio" name="payment_method" value="gcash" checked style="accent-color:var(--crimson)">
                <span style="font-size:1.3rem">📱</span>
                <div><div style="font-weight:700;font-size:.9rem">GCash</div><div style="font-size:.75rem;color:var(--text-muted)">Pay via GCash mobile wallet</div></div>
              </label>

              <label class="payment-method-option" style="display:flex;align-items:center;gap:14px;padding:14px 16px;border:2px solid var(--border);border-radius:var(--radius-lg);cursor:pointer">
                <input type="radio" name="payment_method" value="maya" style="accent-color:var(--crimson)">
                <span style="font-size:1.3rem">💳</span>
                <div><div style="font-weight:700;font-size:.9rem">Maya</div><div style="font-size:.75rem;color:var(--text-muted)">Pay via Maya e-wallet</div></div>
              </label>

              <label class="payment-method-option" style="display:flex;align-items:center;gap:14px;padding:14px 16px;border:2px solid var(--border);border-radius:var(--radius-lg);cursor:pointer">
                <input type="radio" name="payment_method" value="bank_transfer" style="accent-color:var(--crimson)">
                <span style="font-size:1.3rem">🏦</span>
                <div><div style="font-weight:700;font-size:.9rem">Bank Transfer</div><div style="font-size:.75rem;color:var(--text-muted)">BDO, BPI, UnionBank</div></div>
              </label>

              <label class="payment-method-option" style="display:flex;align-items:center;gap:14px;padding:14px 16px;border:2px solid var(--border);border-radius:var(--radius-lg);cursor:pointer">
                <input type="radio" name="payment_method" value="cashier" style="accent-color:var(--crimson)">
                <span style="font-size:1.3rem">🏫</span>
                <div><div style="font-weight:700;font-size:.9rem">Pay at Cashier</div><div style="font-size:.75rem;color:var(--text-muted)">Present reference no. at the university cashier</div></div>
              </label>

            </div>
          </div>
        </div>

        <!-- Info alert -->
        <div class="alert alert-info fade-up" style="margin-bottom:22px">
          <span class="icon">ℹ️</span>
          <div>
            <strong>Note:</strong> Your document request will be processed once payment is confirmed.
            Keep your reference number <strong><?= $referenceNo ?></strong> for tracking.
          </div>
        </div>

        <!-- Actions -->
        <div style="display:flex;gap:10px;flex-direction:column" class="fade-up">
          <button class="btn btn-primary btn-xl btn-block" onclick="openModal()">
            ✅ &nbsp;Confirm Payment — <?= $amountFormatted ?>
          </button>
          <a href="user_request.php" class="btn btn-ghost btn-block" style="text-align:center">
            ← Go Back and Edit Request
          </a>
        </div>

      </div>
    </div>

  </main>
</div>

<?php include 'includes/footer.php'; ?>


<!-- Confirm Modal -->
<div class="modal-overlay" id="confirmModal">
  <div class="modal">
    <div style="font-size:2.5rem;margin-bottom:12px">💳</div>
    <h3>Confirm Your Payment</h3>
    <p>
      You are about to pay <strong><?= $amountFormatted ?></strong> for
      <strong><?= $documentLabel ?></strong>.
      This action cannot be undone.
    </p>
    <div class="modal__actions">
      <button class="btn btn-ghost"   onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="processPayment()">Confirm Payment</button>
    </div>
  </div>
</div>

<!-- Success Modal -->
<div class="modal-overlay" id="successModal">
  <div class="modal" style="max-width:420px">
    <div class="success-icon" style="margin:0 auto 16px">✓</div>
    <h3 style="color:var(--text-dark)">Payment Successful!</h3>
    <p style="margin-bottom:4px">Your document request has been submitted.</p>
    <ul class="success-checklist">
      <li><span class="chk">✓</span> Request recorded — Ref: <strong><?= $referenceNo ?></strong></li>
      <li><span class="chk">✓</span> Payment of <strong><?= $amountFormatted ?></strong> confirmed</li>
      <li><span class="chk">✓</span> Confirmation sent to your email</li>
      <li><span class="chk">✓</span> Status: <strong>Pending</strong> — will be processed shortly</li>
    </ul>
    <div style="display:flex;flex-direction:column;gap:10px">
      <a href="user_dashboard.php"  class="btn btn-primary btn-block">Go to My Dashboard</a>
      <a href="user_request.php"    class="btn btn-ghost   btn-block">Request Another Document</a>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('#paymentMethods input[type="radio"]').forEach(radio => {
  radio.addEventListener('change', () => {
    document.querySelectorAll('.payment-method-option').forEach(el => {
      el.style.borderColor = 'var(--border)';
      el.style.background  = '';
    });
    const chosen = radio.closest('.payment-method-option');
    chosen.style.borderColor = 'var(--crimson)';
    chosen.style.background  = 'var(--pink-bg)';
  });
});

function openModal()  { document.getElementById('confirmModal').classList.add('open'); }
function closeModal() { document.getElementById('confirmModal').classList.remove('open'); }
function processPayment() {
  closeModal();
  // TODO: Replace with actual POST to process_payment.php
  document.getElementById('successModal').classList.add('open');
}
</script>

</body>
</html>