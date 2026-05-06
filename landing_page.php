<?php
// landing_page.php — WildDocuments Public Landing Page
// No session required; open to all visitors
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>WildDocuments — University Document Request Portal</title>
  <link rel="stylesheet" href="css/styles.css">
  <style>
    /* ── Hero: gle.png background + gradient overlay ── */
    .hero {
      background: var(--crimson-deeper); /* fallback if image missing */
      min-height: calc(100vh - 62px);
      display: flex;
      align-items: center;
      position: relative;
      overflow: hidden;
    }
    /* Layer 1 — the actual campus photo */
    .hero__bg-img {
      position: absolute;
      inset: 0;
      background: url('images/gle.png') center / cover no-repeat;
      pointer-events: none;
    }
    /* Layer 2 — crimson gradient sits ON TOP of the photo so text remains legible */
    .hero__overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(
        105deg,
        rgba(74, 13, 24, 0.92) 0%,
        rgba(107, 18, 34, 0.80) 55%,
        rgba(139, 26, 46, 0.55) 100%
      );
      pointer-events: none;
    }
    /* Layer 3 — content sits above both layers */
    .hero__content {
      position: relative;
      z-index: 1;
      max-width: 580px;
      padding: 80px 0;
    }
  </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>


<!-- ══════════════════════════════════════════
     HERO
══════════════════════════════════════════ -->
<section class="hero">
  <div class="hero__bg-img"></div>
  <div class="hero__overlay"></div>
  <div class="container">
    <div class="hero__content">

      <span class="hero__eyebrow fade-up">
        🎓 &nbsp;University Registrar Portal
      </span>

      <h1 class="fade-up delay-1">
        Request Documents <br><span class="accent">Online. Fast.</span><br>Hassle-Free.
      </h1>

      <p class="hero__desc fade-up delay-2">
        WildDocuments lets students, alumni, and university stakeholders request official documents anytime, anywhere — no more long lines, no more waiting days just to submit a form.
      </p>

      <div class="hero__actions fade-up delay-3">
        <a href="register.php" class="btn btn-primary btn-xl">📄 &nbsp;Get Started</a>
        <a href="login.php"    class="btn btn-outline  btn-xl">🔐 &nbsp;Sign In</a>
      </div>

    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════
     FEATURES
══════════════════════════════════════════ -->
<section class="section section--white" id="features">
  <div class="container">

    <div class="section__header">
      <span class="section__eyebrow">Why WildDocuments</span>
      <h2>Everything You Need, In One Place</h2>
      <p>A streamlined platform designed to make document requests as simple as a few clicks — from submission to delivery.</p>
    </div>

    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-card__icon">📋</div>
        <h3>Online Requests</h3>
        <p>Submit document requests 24/7 without visiting the registrar's office. Fill out a simple form and you're done.</p>
      </div>
      <div class="feature-card">
        <div class="feature-card__icon">📡</div>
        <h3>Real-Time Tracking</h3>
        <p>Monitor the status of your request live — from Pending to In Progress to Completed — right from your dashboard.</p>
      </div>
      <div class="feature-card">
        <div class="feature-card__icon">💳</div>
        <h3>Secure Online Payment</h3>
        <p>Pay for your documents securely through the platform. No need to line up at the cashier or bring exact change.</p>
      </div>
      <div class="feature-card">
        <div class="feature-card__icon">⬇️</div>
        <h3>Digital Downloads</h3>
        <p>Once your request is fulfilled, download your documents instantly — official, verified, and ready to use.</p>
      </div>
      <div class="feature-card">
        <div class="feature-card__icon">🔒</div>
        <h3>Secure & Private</h3>
        <p>Your student records and personal data are protected. Only you and authorized registrar staff can access your files.</p>
      </div>
      <div class="feature-card">
        <div class="feature-card__icon">⚡</div>
        <h3>Faster Processing</h3>
        <p>Automated workflows reduce manual errors and processing time, so you get your documents sooner.</p>
      </div>
    </div>

  </div>
</section>


<!-- ══════════════════════════════════════════
     HOW IT WORKS
══════════════════════════════════════════ -->
<section class="section section--light" id="how">
  <div class="container">

    <div class="section__header">
      <span class="section__eyebrow">How It Works</span>
      <h2>Request Your Documents in 4 Easy Steps</h2>
      <p>From form to file — the entire process is designed to be quick and painless.</p>
    </div>

    <div class="how-grid">

      <div class="how-step">
        <div class="how-step__num">1</div>
        <h4>Create an Account</h4>
        <p>Register with your student ID and university email to get started.</p>
      </div>

      <div class="how-arrow">→</div>

      <div class="how-step">
        <div class="how-step__num">2</div>
        <h4>Fill Out the Form</h4>
        <p>Enter your details and choose the document you need.</p>
      </div>

      <div class="how-arrow">→</div>

      <div class="how-step">
        <div class="how-step__num">3</div>
        <h4>Confirm &amp; Pay</h4>
        <p>Review your request summary and complete payment online.</p>
      </div>

      <div class="how-arrow" style="visibility:hidden">→</div>

      <div class="how-step">
        <div class="how-step__num">4</div>
        <h4>Download Document</h4>
        <p>Once approved, download your official document instantly.</p>
      </div>

    </div>

  </div>
</section>


<!-- ══════════════════════════════════════════
     AVAILABLE DOCUMENTS
══════════════════════════════════════════ -->
<section class="section section--white" id="documents">
  <div class="container">

    <div class="section__header">
      <span class="section__eyebrow">Available Documents</span>
      <h2>What Can You Request?</h2>
      <p>All official documents issued by the University Registrar's Office are available through this portal.</p>
    </div>

    <div class="docs-grid">
      <div class="doc-tile"><div class="doc-tile__icon">📜</div><h4>Official Transcript</h4><p>Complete academic transcript with official seal and signature.</p></div>
      <div class="doc-tile"><div class="doc-tile__icon">🎓</div><h4>Diploma Copy</h4><p>Certified copy of your diploma for employment or further studies.</p></div>
      <div class="doc-tile"><div class="doc-tile__icon">📋</div><h4>Certification Letter</h4><p>Official letter certifying your enrollment or graduation status.</p></div>
      <div class="doc-tile"><div class="doc-tile__icon">📁</div><h4>Academic Records</h4><p>Comprehensive record of all completed academic work and grades.</p></div>
      <div class="doc-tile"><div class="doc-tile__icon">📑</div><h4>Honorable Dismissal</h4><p>Transfer credential for students moving to another institution.</p></div>
      <div class="doc-tile"><div class="doc-tile__icon">🏅</div><h4>Dean's List Certificate</h4><p>Recognition document for academic excellence per semester.</p></div>
      <div class="doc-tile"><div class="doc-tile__icon">🆔</div><h4>Authentication</h4><p>Document authentication for use in government transactions.</p></div>
      <div class="doc-tile"><div class="doc-tile__icon">📝</div><h4>Course Description</h4><p>Official course descriptions for credit transfer or graduate school.</p></div>
    </div>

  </div>
</section>


<!-- ══════════════════════════════════════════
     CTA BANNER
══════════════════════════════════════════ -->
<section class="cta-banner">
  <div class="container">
    <h2>Ready to Request Your Documents?</h2>
    <p>Stop wasting time in line. Register now and get your documents faster than ever.</p>
    <div class="cta-banner__actions">
      <a href="register.php" class="btn btn-primary btn-xl">📄 &nbsp;Get Started Now</a>
      <a href="login.php"    class="btn btn-outline  btn-xl">🔐 &nbsp;Sign In</a>
    </div>
  </div>
</section>


<?php include 'includes/footer.php'; ?>

</body>
</html>