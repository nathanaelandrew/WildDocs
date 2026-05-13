<?php
// includes/student_sidebar.php — Specifically for Student Dashboard
$currentPage = basename($_SERVER['PHP_SELF']);

function studentSidebarLink(string $href, string $icon, string $label, string $current, string $badge = ''): void {
    $active = ($current === $href) ? ' active' : '';
    $badgeHtml = $badge ? "<span class='badge badge-new' style='margin-left: auto; font-size: 0.65rem; padding: 2px 6px;'>{$badge}</span>" : '';
    
    echo "<a href=\"{$href}\" class=\"sidebar__link{$active}\">
            <span class=\"icon\">{$icon}</span>
            {$label}
            {$badgeHtml}
          </a>";
}
?>
<aside class="sidebar">

  <div class="sidebar__section-label">My Documents</div>

  <?php studentSidebarLink('student_dashboard.php', '🏠', 'Dashboard', $currentPage); ?>
  <?php studentSidebarLink('student_request.php',   '📄', 'Request Document', $currentPage); ?>
  <?php studentSidebarLink('student_payments.php', '💳', 'Payments', $currentPage); ?>
  
  <!-- ADDED: Notifications Link -->
  <!-- You can replace '3' with a dynamic count from your database later -->
  <?php studentSidebarLink('student_notifications.php', '🔔', 'Notifications', $currentPage); ?>

  <div class="sidebar__divider"></div>
  <div class="sidebar__section-label">Account</div>

  <?php studentSidebarLink('student_profile.php',  '👤', 'My Profile', $currentPage); ?>
  <?php studentSidebarLink('student_settings.php', '⚙️', 'Settings',   $currentPage); ?>
  <div class="sidebar__bottom">
    <div class="sidebar__divider"></div>
    <a href="logout.php" 
      class="sidebar__logout" 
      style="display: flex; justify-content: center; align-items: center;" 
      onclick="return confirm('Log out of WildDocuments?')">
          Logout
    </a>
    </a>
  </div>

</aside>