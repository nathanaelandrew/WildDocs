<?php
// partials/admin_sidebar.php
// Determine active page for highlight
$currentPage = basename($_SERVER['PHP_SELF']);
function sidebarLink($href, $icon, $label, $current) {
  $active = ($current === $href) ? ' active' : '';
  echo "<a href=\"{$href}\" class=\"sidebar__link{$active}\"><span class=\"icon\">{$icon}</span>{$label}</a>";
}
?>
<aside class="sidebar">

  <div class="sidebar__section-label">Main Menu</div>

  <?php sidebarLink('admin_dashboard.php',    '🏠', 'Overview',       $currentPage); ?>
  <?php sidebarLink('admin_requests.php',     '📋', 'My Request',     $currentPage); ?>
  <?php sidebarLink('admin_manage.php',       '⚙️', 'Dashboard',      $currentPage); ?>
  <?php sidebarLink('admin_notifications.php','🔔', 'Notifications',  $currentPage); ?>
  <?php sidebarLink('admin_withdraw.php',     '💳', 'Withdraw',       $currentPage); ?>

  <div class="sidebar__divider"></div>
  <div class="sidebar__section-label">Account</div>

  <?php sidebarLink('admin_settings.php',     '⚙️', 'Settings',       $currentPage); ?>

  <div class="sidebar__bottom">
    <div class="sidebar__divider"></div>
    <?php sidebarLink('admin_help.php',       '❓', 'Help & Support',  $currentPage); ?>
    <a href="logout.php" class="sidebar__link" onclick="return confirm('Log out?')">
      <span class="icon">🚪</span>Logout
    </a>
  </div>

</aside>