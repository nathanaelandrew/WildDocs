<?php
// includes/admin_sidebar.php
$currentPage = basename($_SERVER['PHP_SELF']);

function sidebarLink(string $href, string $icon, string $label, string $current): void {
    $active = ($current === $href) ? ' active' : '';
    echo "<a href=\"{$href}\" class=\"sidebar__link{$active}\"><span class=\"icon\">{$icon}</span>{$label}</a>";
}
?>
<aside class="sidebar">

  <div class="sidebar__section-label">Main Menu</div>

  <?php sidebarLink('admin_dashboard.php',     '🏠', 'Overview',         $currentPage); ?>
  <?php sidebarLink('admin_requests.php',      '📋', 'Manage Requests',  $currentPage); ?>
  <?php sidebarLink('admin_payments.php',      '💳', 'Payments',         $currentPage); ?>
  <?php sidebarLink('admin_notifications.php', '🔔', 'Notifications',    $currentPage); ?>

  <div class="sidebar__divider"></div>
  <div class="sidebar__section-label">Account</div>

  <?php sidebarLink('admin_profile.php',  '👤', 'Profile',  $currentPage); ?>
  <?php sidebarLink('admin_settings.php', '⚙️', 'Settings', $currentPage); ?>

  <div class="sidebar__bottom">
    <div class="sidebar__divider"></div>
    <!-- Logout: opens inline modal instead of browser confirm() -->
    <button type="button"
            class="sidebar__logout"
            style="display: flex; justify-content: center; align-items: center;"
            onclick="document.getElementById('logoutModal').classList.add('open')">
      <span class="icon"></span> Logout
    </button>
  </div>

</aside>

<!-- ── Logout Confirmation Modal ──────────────────────── -->
<!-- Scoped here so it works on every admin page automatically -->
<div class="modal-overlay" id="logoutModal">
  <div class="modal" style="max-width:380px;text-align:center">
    <div style="font-size:2.6rem;margin-bottom:12px">🔐</div>
    <h3 style="margin-bottom:8px">Log Out?</h3>
    <p style="margin-bottom:28px;color:var(--text-muted);font-size:.88rem">
      You will be returned to the login page. Any unsaved changes will be lost.
    </p>
    <div style="display:flex;gap:10px;justify-content:center">
      <button class="btn btn-ghost"
              onclick="document.getElementById('logoutModal').classList.remove('open')">
        Cancel
      </button>
      <a href="logout.php" class="btn btn-danger">Yes, Log Out</a>
    </div>
  </div>
</div>