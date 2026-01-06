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
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

