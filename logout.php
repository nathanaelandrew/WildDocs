<?php
/**
 * logout.php — WildDocuments User Logout
 * This file clears all session data and redirects the user to the home page.
 */

// 1. Start the session to gain access to it
session_start();

// 2. Unset all session variables
$_SESSION = array();

// 3. If it's desired to kill the session, also delete the session cookie.
// Note: This completely destroys the session, not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finally, destroy the session.
session_destroy();

// 5. Redirect the user back to the landing page (index.php)
header("Location: index.php");
exit;