<?php
/**
 * Logout Page
 * Handles user logout
 */
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_unset();
session_destroy();

// Redirect to login page - handle path correctly
$script_name = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
$script_dir = dirname($script_name);
$script_dir = str_replace('\\', '/', $script_dir);

if (strpos($script_dir, '/admin') !== false || strpos($script_dir, '\\admin') !== false) {
    header('Location: ../login.php');
} elseif (strpos($script_dir, '/customer') !== false || strpos($script_dir, '\\customer') !== false) {
    header('Location: ../login.php');
} else {
    header('Location: login.php');
}
exit();

