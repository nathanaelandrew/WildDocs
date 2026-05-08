<?php
// includes/user_sidebar.php — User dashboard sidebar navigation
$currentPage = basename($_SERVER['PHP_SELF']);

function userSidebarLink(string $href, string $icon, string $label, string $current): void {
    $active = ($current === $href) ? ' active' : '';
    echo "<a href=\"{$href}\" class=\"sidebar__link{$active}\"><span class=\"icon\">{$icon}</span>{$label}</a>";
}
?>
<aside class="sidebar">

  <div class="sidebar__section-label">My Documents</div>

  <?php userSidebarLink('user_dashboard.php', '🏠', 'Dashboard',        $currentPage); ?>
  <?php userSidebarLink('user_request.php',   '📄', 'Request Document', $currentPage); ?>

  <div class="sidebar__divider"></div>
  <div class="sidebar__section-label">Account</div>

  <?php userSidebarLink('user_profile.php',   '👤', 'My Profile',       $currentPage); ?>
  <?php userSidebarLink('user_settings.php', '⚙️', 'Settings', $currentPage); ?>


  <!-- Logout pinned to bottom -->
  <div class="sidebar__bottom">
    <div class="sidebar__divider"></div>
    <a href="logout.php" class="sidebar__logout" onclick="return confirm('Log out of WildDocuments?')">
      <span class="icon">🚪</span>Logout
    </a>
  </div>

</aside>