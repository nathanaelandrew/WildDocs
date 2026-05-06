<?php
// includes/navbar.php — Public-facing navbar (landing, login, register)
// Usage: include 'includes/navbar.php';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
  <div class="container" style="max-width:100%;padding:0 32px">
    <div class="navbar__inner">

      <!-- Logo -->
      <a href="landing_page.php" class="navbar__logo">
        <div class="navbar__logo-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="#8B1A2E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <polyline points="14 2 14 8 20 8" stroke="#8B1A2E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="16" y1="13" x2="8" y2="13" stroke="#8B1A2E" stroke-width="2" stroke-linecap="round"/>
            <line x1="16" y1="17" x2="8" y2="17" stroke="#8B1A2E" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
        <span class="navbar__brand">WildDocuments</span>
      </a>

      <!-- Nav links -->
      <ul class="navbar__links">
        <li><a href="#features"  class="navbar__link <?= $currentPage==='landing_page.php'?'':'hidden-mobile' ?>">Features</a></li>
        <li><a href="#how"       class="navbar__link <?= $currentPage==='landing_page.php'?'':'hidden-mobile' ?>">How It Works</a></li>
        <li><a href="#documents" class="navbar__link <?= $currentPage==='landing_page.php'?'':'hidden-mobile' ?>">Documents</a></li>
      </ul>

      <!-- CTA -->
      <div class="navbar__actions">
        <?php if ($currentPage !== 'login.php'): ?>
          <a href="login.php" class="btn btn-outline btn-sm">Login</a>
        <?php endif; ?>
        <?php if ($currentPage !== 'register.php'): ?>
          <a href="register.php" class="btn btn-primary btn-sm">Sign Up</a>
        <?php endif; ?>
      </div>

    </div>
  </div>
</nav>