<?php
// includes/admin_sidebar.php — Admin panel sidebar navigation
// Usage: include 'includes/admin_sidebar.php';
$currentPage = basename($_SERVER['PHP_SELF']);

function sidebarLink(string $href, string $icon, string $label, string $current): void {
    $active = ($current === $href) ? ' active' : '';
    echo "<a href=\"{$href}\" class=\"sidebar__link{$active}\"><span class=\"icon\">{$icon}</span>{$label}</a>";
}
?>
<aside class="sidebar">

  <div class="sidebar__section-label">Main Menu</div>

  <?php sidebarLink('admin_dashboard.php',     '🏠', 'Overview',      $currentPage); ?>
  <?php sidebarLink('admin_requests.php',      '📋', 'Manage Requests',  $currentPage); ?>
  <?php sidebarLink('admin_notifications.php', '🔔', 'Notifications', $currentPage); ?>

  <div class="sidebar__divider"></div>
  <div class="sidebar__section-label">Account</div>

  <?php sidebarLink('admin_profile.php',  '👤', 'Profile',  $currentPage); ?>
  <?php sidebarLink('admin_settings.php', '⚙️', 'Settings', $currentPage); ?>

  <!-- Logout pinned to bottom -->
  <div class="sidebar__bottom">
    <div class="sidebar__divider"></div>
    <a href="logout.php" class="sidebar__logout" onclick="return confirm('Log out of WildDocuments?')">
      <span class="icon">🚪</span>Logout
    </a>
  </div>

</aside>