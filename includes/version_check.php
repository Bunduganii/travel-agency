<?php
/**
 * PHP Version and Requirements Check
 * This file can be included to verify PHP environment compatibility
 * Include this at the top of critical files if needed
 */

// Minimum required PHP version
define('MIN_PHP_VERSION', '7.4.0');

// Check PHP version
if (version_compare(phpversion(), MIN_PHP_VERSION, '<')) {
    die("
    <!DOCTYPE html>
    <html>
    <head>
        <title>PHP Version Error</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
            .error-box { background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #d32f2f; margin-top: 0; }
            .current { color: #666; font-size: 18px; margin: 10px 0; }
            .required { color: #1976d2; font-size: 18px; margin: 10px 0; }
            .solution { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; border-radius: 4px; }
            code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
        </style>
    </head>
    <body>
        <div class='error-box'>
            <h1>⚠️ PHP Version Error</h1>
            <p class='current'><strong>Current PHP Version:</strong> " . phpversion() . "</p>
            <p class='required'><strong>Required PHP Version:</strong> " . MIN_PHP_VERSION . " or higher</p>
            <div class='solution'>
                <h3>How to Fix:</h3>
                <p><strong>For XAMPP/WAMP:</strong> Download latest version from <a href='https://www.apachefriends.org/' target='_blank'>apachefriends.org</a></p>
                <p><strong>For Linux:</strong> <code>sudo apt-get install php7.4</code> or <code>php8.0</code></p>
                <p><strong>For Mac:</strong> <code>brew install php@7.4</code></p>
                <p><strong>For cPanel/Shared Hosting:</strong> Change PHP version in hosting control panel (usually called 'Select PHP Version')</p>
                <p><strong>Run requirements check:</strong> <code>php requirements.php</code></p>
            </div>
            <p><strong>After upgrading PHP, restart your web server (Apache/Nginx) and try again.</strong></p>
        </div>
    </body>
    </html>
    ");
}

// Check critical extensions
$criticalExtensions = ['mysqli', 'session', 'mbstring', 'json'];
$missingExtensions = [];

foreach ($criticalExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

// If critical extensions are missing, show error (but allow to continue in some cases)
if (!empty($missingExtensions) && php_sapi_name() !== 'cli') {
    $missingList = implode(', ', $missingExtensions);
    $errorMessage = "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Missing PHP Extensions</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
            .error-box { background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #d32f2f; margin-top: 0; }
            .missing { color: #666; font-size: 18px; margin: 10px 0; background: #ffebee; padding: 10px; border-radius: 4px; }
            .solution { background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 20px 0; border-radius: 4px; }
            code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
        </style>
    </head>
    <body>
        <div class='error-box'>
            <h1>⚠️ Missing PHP Extensions</h1>
            <p class='missing'><strong>Missing Extensions:</strong> $missingList</p>
            <div class='solution'>
                <h3>How to Fix:</h3>
                <p><strong>For XAMPP/WAMP:</strong></p>
                <ol>
                    <li>Open <code>php.ini</code> file (usually in PHP installation folder)</li>
                    <li>Find the extension lines and uncomment (remove <code>;</code>)</li>
                    <li>Example: Change <code>;extension=mysqli</code> to <code>extension=mysqli</code></li>
                    <li>Restart Apache/web server</li>
                </ol>
                <p><strong>For Linux:</strong> <code>sudo apt-get install php7.4-mysqli php7.4-mbstring php7.4-session php7.4-json</code></p>
                <p><strong>For Mac:</strong> <code>brew install php@7.4-mysqli php@7.4-mbstring</code></p>
                <p><strong>For cPanel/Shared Hosting:</strong> Enable extensions via hosting control panel (usually in PHP Extensions section)</p>
                <p><strong>Verify:</strong> Run <code>php requirements.php</code> or <code>php -m</code> to check loaded extensions</p>
            </div>
        </div>
    </body>
    </html>
    ";
    die($errorMessage);
}

// Version check passed - optionally log for debugging
if (defined('DEBUG') && DEBUG) {
    error_log("PHP Version Check: OK - PHP " . phpversion() . " with all required extensions");
}

