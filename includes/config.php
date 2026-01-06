<?php
/**
 * Configuration File
 * Contains path and configuration constants
 */

// Get the base path dynamically
$script_name = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
$script_dir = dirname($script_name);
$script_dir = str_replace('\\', '/', $script_dir);
$script_dir = trim($script_dir, '/');

// Determine assets path based on directory depth
if (strpos($script_dir, 'admin') !== false || strpos($script_dir, 'customer') !== false) {
    define('ASSETS_PATH', '../assets/');
    define('BASE_PATH', '../');
} else {
    define('ASSETS_PATH', 'assets/');
    define('BASE_PATH', '');
}

