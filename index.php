<?php
session_start();

// Auth check: Redirect logged-in users to their respective dashboards
if (isset($_SESSION['user_id'])) {
    $target = ($_SESSION['user_role'] === 'admin') ? 'admin_dashboard.php' : 'student_dashboard.php';
    header("Location: $target");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>WildDocuments — University Document Request Portal</title>
  <link rel="stylesheet" href="css/styles.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    /* ── Hero Refinement ── */
    .hero {
      background: var(--crimson-deeper);
      min-height: 90vh;
      display: flex;
      align-items: center;
      position: relative;
      overflow: hidden;
      color: var(--white);
    }
    .hero__bg-img {
      position: absolute;
      inset: 0;
      background: url('images/gle.png') center / cover no-repeat;
      transform: scale(1.05);
      pointer-events: none;
    }
    .hero__overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(105deg, rgba(74, 13, 24, 0.95) 0%, rgba(107, 18, 34, 0.85) 50%, rgba(139, 26, 46, 0.40) 100%);
      pointer-events: none;
    }
    .hero__content { position: relative; z-index: 10; max-width: 650px; padding: 60px 0; }
    .hero h1 .accent { color: var(--red-accent); background: linear-gradient(to right, #fff, #fca5a5); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    /* ── ALIGNED PROCESS (How it works) ── */
    .how-grid {
      display: flex;
      align-items: stretch; /* Makes all cards same height */
      justify-content: center;
      gap: 0; /* Gap managed by padding for arrows */
      margin-top: 40px;
    }
    .how-step {
      flex: 1;
      text-align: center;
      background: var(--white);
      padding: 40px 24px;
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-sm);
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .how-step__num {
      width: 54px;
      height: 54px;
      background: var(--crimson);
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 1.2rem;
      margin-bottom: 24px;
      box-shadow: 0 0 0 8px var(--pink-bg);
      flex-shrink: 0;
    }
    .how-step h4 { font-size: 1.1rem; margin-bottom: 12px; color: var(--text-dark); }
    .how-step p { font-size: 0.88rem; color: var(--text-muted); line-height: 1.6; }

    .how-arrow {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0 15px;
      color: var(--crimson-light);
      font-size: 1.5rem;
      font-weight: bold;
      opacity: 0.4;
    }

    /* Step 4 Highlight */
    .how-step--highlight {
      background: var(--crimson);
      transform: scale(1.05);
      z-index: 2;
      box-shadow: var(--shadow-lg);
    }
    .how-step--highlight .how-step__num { background: white; color: var(--crimson); box-shadow: 0 0 0 8px rgba(255,255,255,0.1); }
    .how-step--highlight h4 { color: white; }
    .how-step--highlight p { color: rgba(255,255,255,0.8); }

    /* Responsive alignment */
    @media (max-width: 1024px) {
      .how-grid { flex-wrap: wrap; gap: 30px; }
      .how-arrow { display: none; }
      .how-step { flex: 0 0 calc(50% - 15px); }
    }
    @media (max-width: 600px) {
      .how-step { flex: 0 0 100%; }
    }
  </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<section class="hero">
  <div class="hero__bg-img"></div>
  <div class="hero__overlay"></div>
  <div class="container">
    <div class="hero__content">

      <span class="hero__eyebrow fade-up" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);">
        🎓 &nbsp;Official Registrar Portal
      </span>

      <h1 class="fade-up delay-1">
        Request Documents <br><span class="accent">Online. Fast. Secure.</span>
      </h1>

      <p class="hero__desc fade-up delay-2">
        The official WildDocuments portal allows students and alumni to request transcripts, diplomas, and certifications without the need for physical queues.
      </p>

      <div class="hero__actions fade-up delay-3">
        <a href="register.php" class="btn btn-primary btn-xl">Get Started Now</a>
        <a href="#features" class="btn btn-outline btn-xl">View Services</a>
      </div>

    </div>
  </div>
</section>

<section class="section section--white" id="features">
  <div class="container">
    <div class="section__header">
      <span class="section__eyebrow">Key Features</span>
      <h2>Experience Modern Convenience</h2>
      <p>We've transformed the traditional document request process into a seamless digital experience.</p>
    </div>

    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-card__icon" style="background: #E0F2FE; color: #0369A1;">📡</div>
        <h3>Real-Time Tracking</h3>
        <p>Monitor your request status from submission to release through your personal dashboard.</p>
      </div>
      <div class="feature-card">
        <div class="feature-card__icon" style="background: #F0FDF4; color: #15803D;">💳</div>
        <h3>Cashless Payment</h3>
        <p>Complete transactions via GCash, Maya, or Bank Transfer with auto-generated receipts.</p>
      </div>
      <div class="feature-card">
        <div class="feature-card__icon" style="background: #FEF2F2; color: #991B1B;">🔒</div>
        <h3>Verified Records</h3>
        <p>All documents are digitally verified by the University Registrar for authenticity.</p>
      </div>
      <div class="feature-card">
        <div class="feature-card__icon" style="background: #F5F3FF; color: #6D28D9;">⚡</div>
        <h3>Priority Processing</h3>
        <p>Automated workflows ensure faster turnaround times compared to manual requests.</p>
      </div>
      <div class="feature-card">
        <div class="feature-card__icon" style="background: #FFFBEB; color: #A16207;">📂</div>
        <h3>Digital Archive</h3>
        <p>Access your history of requested documents and receipts anytime you need them.</p>
      </div>
      <div class="feature-card">
        <div class="feature-card__icon" style="background: #FDF2F8; color: #BE185D;">⬇️</div>
        <h3>Instant Download</h3>
        <p>Download certified electronic copies immediately once your request is released.</p>
      </div>
    </div>
  </div>
</section>

<section class="section section--light" id="how">
  <div class="container">
    <div class="section__header">
      <span class="section__eyebrow">The Process</span>
      <h2>As Simple as 1-2-3</h2>
      <p>Follow these steps to get your official university documents delivered digitally.</p>
    </div>

    <div class="how-grid">
      <div class="how-step">
        <div class="how-step__num">1</div>
        <h4>Sign Up</h4>
        <p>Create an account using your Student ID and university email.</p>
      </div>

      <div class="how-arrow">→</div>

      <div class="how-step">
        <div class="how-step__num">2</div>
        <h4>Select Document</h4>
        <p>Choose the specific document and provide the purpose of request.</p>
      </div>

      <div class="how-arrow">→</div>

      <div class="how-step">
        <div class="how-step__num">3</div>
        <h4>Verify Payment</h4>
        <p>Submit your payment details and wait for registrar approval.</p>
      </div>

      <div class="how-arrow">→</div>

      <div class="how-step how-step--highlight">
        <div class="how-step__num">4</div>
        <h4>Collect</h4>
        <p>Download your digital file or pick up the physical copy at the office.</p>
      </div>
    </div>
  </div>
</section>

<section class="section section--white" id="documents">
  <div class="container">
    <div class="section__header">
      <span class="section__eyebrow">Available Services</span>
      <h2>Documents We Process</h2>
    </div>

    <div class="docs-grid">
      <div class="doc-tile">
        <div class="doc-tile__icon">📜</div>
        <h4>Official Transcript of Records</h4>
        <p>Official record of your entire academic history</p>
      </div>
      <div class="doc-tile">
        <div class="doc-tile__icon">📊</div>
        <h4>Certified Copy of Grades</h4>
        <p>Certified copy of semester grade records</p>
      </div>
      <div class="doc-tile">
        <div class="doc-tile__icon">🚪</div>
        <h4>Transfer Credentials</h4>
        <p>Required processing for student transfers</p>
      </div>
      <div class="doc-tile">
        <div class="doc-tile__icon">📄</div>
        <h4>Honorable Dismissal</h4>
        <p>Official clearance indicating clean academic leave</p>
      </div>
      <div class="doc-tile">
        <div class="doc-tile__icon">📖</div>
        <h4>Course Syllabus</h4>
        <p>Certified subject descriptions for credit evaluation</p>
      </div>
      <div class="doc-tile">
        <div class="doc-tile__icon">🎓</div>
        <h4>Diploma Copy</h4>
        <p>Certified replacement or copy of graduation diploma</p>
      </div>
      <div class="doc-tile">
        <div class="doc-tile__icon">🎖️</div>
        <h4>Good Moral Character</h4>
        <p>Official student conduct and clearance certificate</p>
      </div>
      <div class="doc-tile">
        <div class="doc-tile__icon">📋</div>
        <h4>Certification Letter</h4>
        <p>Official proof of enrollment, units, or completion status</p>
      </div>
    </div>
  </div>
</section>

<section class="cta-banner" style="border-radius: 0;">
  <div class="container">
    <h2 class="fade-up delay-1">Ready to get started?</h2>
    <p class="fade-up delay-2">Join thousands of students using WildDocuments for faster document processing.</p>
    <div class="cta-banner__actions fade-up delay-3 ">
      <a href="register.php" class="btn btn-primary btn-xl">Create Account</a>
      <a href="login.php" class="btn btn-outline btn-xl">Login to Portal</a>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>