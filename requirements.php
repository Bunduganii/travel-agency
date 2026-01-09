<?php
/**
 * PHP Requirements Checker
 * Run this file to verify your PHP environment meets the requirements
 */

echo "=== PHP Requirements Check ===\n\n";

// Check PHP Version
$requiredVersion = '7.4.0';
$currentVersion = phpversion();
echo "PHP Version: $currentVersion (Required: >= $requiredVersion)\n";

if (version_compare($currentVersion, $requiredVersion, '>=')) {
    echo "✓ PHP version is compatible\n\n";
} else {
    echo "✗ PHP version is too old. Please upgrade to PHP $requiredVersion or higher\n\n";
    exit(1);
}

// Check Required Extensions
$requiredExtensions = [
    'mysqli' => 'MySQLi extension for database connectivity',
    'pdo_mysql' => 'PDO MySQL extension (optional but recommended)',
    'session' => 'Session extension for user authentication',
    'mbstring' => 'Multibyte String extension for string handling',
    'json' => 'JSON extension for data encoding/decoding',
];

echo "Checking Required Extensions:\n";
$allExtensionsLoaded = true;

foreach ($requiredExtensions as $extension => $description) {
    if (extension_loaded($extension)) {
        echo "✓ $extension - $description\n";
    } else {
        echo "✗ $extension - $description (MISSING)\n";
        $allExtensionsLoaded = false;
    }
}

echo "\n";

// Check Optional Extensions
$optionalExtensions = [
    'curl' => 'cURL extension (for API calls)',
    'openssl' => 'OpenSSL extension (for secure connections)',
    'gd' => 'GD extension (for image processing)',
    'zip' => 'ZIP extension (for file compression)',
];

echo "Checking Optional Extensions:\n";
foreach ($optionalExtensions as $extension => $description) {
    if (extension_loaded($extension)) {
        echo "✓ $extension - $description\n";
    } else {
        echo "○ $extension - $description (not installed)\n";
    }
}

echo "\n";

// Check Critical PHP Settings
echo "Checking PHP Configuration:\n";

// Check display_errors (should be off in production)
$displayErrors = ini_get('display_errors');
echo "display_errors: $displayErrors " . ($displayErrors ? "(⚠ Should be '0' in production)" : "(✓ OK)") . "\n";

// Check file_uploads
$fileUploads = ini_get('file_uploads');
echo "file_uploads: $fileUploads " . ($fileUploads ? "(✓ OK)" : "(⚠ File uploads disabled)") . "\n";

// Check upload_max_filesize
$uploadMaxSize = ini_get('upload_max_filesize');
echo "upload_max_filesize: $uploadMaxSize\n";

// Check post_max_size
$postMaxSize = ini_get('post_max_size');
echo "post_max_size: $postMaxSize\n";

// Check memory_limit
$memoryLimit = ini_get('memory_limit');
echo "memory_limit: $memoryLimit\n";

// Check max_execution_time
$maxExecutionTime = ini_get('max_execution_time');
echo "max_execution_time: $maxExecutionTime seconds\n";

echo "\n";

// Check Directory Permissions
echo "Checking Directory Permissions:\n";
$directories = [
    __DIR__ => 'Project root',
    __DIR__ . '/includes' => 'Includes directory',
    __DIR__ . '/customer' => 'Customer directory',
    __DIR__ . '/admin' => 'Admin directory',
];

foreach ($directories as $dir => $name) {
    if (is_dir($dir)) {
        if (is_readable($dir)) {
            echo "✓ $name is readable\n";
        } else {
            echo "✗ $name is not readable\n";
            $allExtensionsLoaded = false;
        }
        if (is_writable($dir)) {
            echo "  ✓ $name is writable\n";
        } else {
            echo "  ○ $name is not writable (may cause issues)\n";
        }
    } else {
        echo "✗ $name does not exist\n";
        $allExtensionsLoaded = false;
    }
}

echo "\n";

// Final Summary
echo "=== Summary ===\n";
if ($allExtensionsLoaded) {
    echo "✓ All required extensions are loaded\n";
    echo "✓ PHP environment is ready for this application\n";
    exit(0);
} else {
    echo "✗ Some required components are missing\n";
    echo "✗ Please install missing extensions or update PHP configuration\n";
    exit(1);
}
?>

