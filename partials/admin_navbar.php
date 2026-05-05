<?php
// partials/admin_navbar.php
// TODO: $adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminName = 'Admin';
?>
<nav class="navbar">
  <div class="container" style="max-width:100%;padding:0 24px">
    <div class="navbar__inner">

      <!-- Logo -->
      <a href="admin_dashboard.php" class="navbar__logo">
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

      <!-- Right side -->
      <div class="navbar__actions">
        <!-- Notification bell -->
        <button class="btn btn-ghost btn-sm" style="color:#fff;border-color:rgba(255,255,255,.2);position:relative" title="Notifications">
          🔔
          <span style="position:absolute;top:4px;right:4px;width:8px;height:8px;background:var(--red-accent);border-radius:50%;border:2px solid var(--crimson-dark)"></span>
        </button>

        <!-- Admin user pill -->
        <div class="navbar__user">
          <div class="navbar__avatar">A</div>
          <span class="navbar__username"><?= htmlspecialchars($adminName) ?></span>
        </div>
      </div>

    </div>
  </div>
</nav>