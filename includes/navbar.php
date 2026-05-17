<?php
// includes/navbar.php — Public-facing navbar (landing, login, register)
// Usage: include 'includes/navbar.php';
$currentPage = basename($_SERVER['PHP_SELF']);

// Helper function to append index.php if the user is on an outside page
function getPrefix($page) {
    return ($page === 'index.php') ? '' : 'index.php';
}
?>
<nav class="navbar" style="position: sticky; top: 0; z-index: 9999;">
  <div class="container" style="max-width:100%;padding:0 32px">
    <div class="navbar__inner" style="display: flex; align-items: center; justify-content: space-between; position: relative;">

      <a href="index.php" class="navbar__logo" style="position: relative; z-index: 10;">
        <div class="navbar__logo-icon" style="width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;vertical-align:middle;background:#ffffff;border-radius:8px;padding:0;margin:0">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="#8B1A2E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <polyline points="14 2 14 8 20 8" stroke="#8B1A2E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="16" y1="13" x2="8" y2="13" stroke="#8B1A2E" stroke-width="2" stroke-linecap="round"/>
            <line x1="16" y1="17" x2="8" y2="17" stroke="#8B1A2E" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
        <span class="navbar__brand">WildDocuments</span>
      </a>

      <div style="position: absolute; left: 0; right: 0; display: flex; justify-content: center; pointer-events: none; z-index: 5;">
        <ul class="navbar__links" style="display: flex; align-items: center; list-style: none; margin: 0; padding: 0; pointer-events: auto;">
          <li><a href="<?= getPrefix($currentPage) ?>#features"  class="navbar__link <?= $currentPage==='index.php'?'':'hidden-mobile' ?>">Features</a></li>
          <li><a href="<?= getPrefix($currentPage) ?>#how"       class="navbar__link <?= $currentPage==='index.php'?'':'hidden-mobile' ?>">How It Works</a></li>
          <li><a href="<?= getPrefix($currentPage) ?>#documents" class="navbar__link <?= $currentPage==='index.php'?'':'hidden-mobile' ?>">Documents</a></li>
        </ul>
      </div>

      <div class="navbar__actions" style="position: relative; z-index: 10;">
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