<?php
/**
 * logout.php
 * Handles admin session destruction and redirects to login.php (same folder)
 */
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Redirect to login.php in the same folder
header("Location: login.php");
exit();
?>