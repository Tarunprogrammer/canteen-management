<?php
/**
 * Smart Canteen | Logout Processor
 * Clears all session data and redirects to the home page.
 */

session_start();

// 1. Unset all session variables
$_SESSION = array();

// 2. If it's desired to kill the session, also delete the session cookie.
// Note: This forces the browser to generate a new session ID on next visit.
if (ini_get("session_use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Finally, destroy the session.
session_destroy();

// 4. Redirect to the index page
header("Location: login.php");
exit();
?>