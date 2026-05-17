<?php
// includes/user_navbar.php — User top navbar (include only, NOT a full page)
// Session is already started by the parent page — do NOT call session_start() here
// Preview mode: $_SESSION['user_id'] is set by the parent page
$userName = $_SESSION['user_name'] ?? 'Admin';
$userInitial = strtoupper(substr($userName, 0, 1));
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
  <div class="container" style="max-width:100%;padding:0 24px">
    <div class="navbar__inner">

      <a href="index.php" class="navbar__logo">
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

      <div class="navbar__actions">
        <div class="navbar__user">
          <div class="navbar__avatar"><?= htmlspecialchars($userInitial) ?></div>
          <span class="navbar__username"><?= htmlspecialchars($userName) ?></span>
        </div>
      </div>

    </div>
  </div>
</nav>