<?php
/**
 * Authentication Helper Functions
 * Handles user authentication and session management
 */

session_start();

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

/**
 * Check if user is customer
 */
function isCustomer() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer';
}

/**
 * Require login - redirect to login if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}

/**
 * Require admin - redirect if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit();
    }
}

/**
 * Require customer - redirect if not customer
 */
function requireCustomer() {
    requireLogin();
    if (!isCustomer()) {
        header('Location: ../index.php');
        exit();
    }
}

/**
 * Get current user ID
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current username
 */
function getUsername() {
    return $_SESSION['username'] ?? 'Guest';
}

/**
 * Get current user full name
 */
function getUserFullName() {
    return $_SESSION['full_name'] ?? 'Guest';
}

/**
 * Logout user
 */
function logout() {
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    
    // Destroy the session
    session_unset();
    session_destroy();
    
    // Determine the correct path to login.php based on current location
    $current_dir = dirname($_SERVER['PHP_SELF']);
    if (strpos($current_dir, '/admin') !== false || strpos($current_dir, '\\admin') !== false) {
        // If we're in admin directory, go up one level
        header('Location: ../login.php');
    } elseif (strpos($current_dir, '/customer') !== false || strpos($current_dir, '\\customer') !== false) {
        // If we're in customer directory, go up one level
        header('Location: ../login.php');
    } else {
        // If we're in root directory
        header('Location: login.php');
    }
    exit();
}

