<?php
// includes/user_navbar.php
// Ensure session is active (some pages might forget session_start)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the name from the session. 
// If it's empty, we check for 'user_name' which we set in login/register.
$userName = $_SESSION['user_name'] ?? 'Student User';
$userInitial = strtoupper(substr($userName, 0, 1));
?>
<nav class="navbar">
  <div class="container" style="max-width:100%;padding:0 24px">
    <div class="navbar__inner">

      <!-- FIXED: Link changed to student_dashboard.php -->
      <a href="student_dashboard.php" class="navbar__logo">
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

      <div class="navbar__actions">
        <div class="navbar__user">
          <div class="navbar__avatar"><?= htmlspecialchars($userInitial) ?></div>
          <span class="navbar__username"><?= htmlspecialchars($userName) ?></span>
        </div>
      </div>

    </div>
  </div>
</nav>