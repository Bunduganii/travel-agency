<?php
/**
 * Script to fix admin password
 * Run this once via browser to ensure admin user exists with correct password
 * Visit: http://localhost/Travel-agency-reser/fix_admin.php
 */
require_once 'includes/db.php';

$email = 'admin@travelagency.com';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$message = '';
$success = false;

// Check if admin exists
$stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ? AND user_type = 'admin'");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    // Update existing admin password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ? AND user_type = 'admin'");
    $stmt->bind_param("ss", $hashed_password, $email);
    if ($stmt->execute()) {
        $message = "Admin password updated successfully!";
        $success = true;
    } else {
        $message = "Error updating admin password: " . $conn->error;
    }
} else {
    // Create new admin user
    $username = 'admin';
    $full_name = 'Admin User';
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, user_type) VALUES (?, ?, ?, ?, 'admin')");
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $full_name);
    if ($stmt->execute()) {
        $message = "Admin user created successfully!";
        $success = true;
    } else {
        $message = "Error creating admin user: " . $conn->error;
    }
}
$stmt->close();

// Verify the password works
$stmt = $conn->prepare("SELECT password FROM users WHERE email = ? AND user_type = 'admin'");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

$password_verified = false;
$password_hash_in_db = '';
if ($admin) {
    $password_hash_in_db = $admin['password'];
    $password_verified = password_verify($password, $admin['password']);
    
    // If password doesn't verify, update it
    if (!$password_verified) {
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ? AND user_type = 'admin'");
        $update_stmt->bind_param("ss", $new_hash, $email);
        $update_stmt->execute();
        $update_stmt->close();
        
        // Verify again
        $verify_stmt = $conn->prepare("SELECT password FROM users WHERE email = ? AND user_type = 'admin'");
        $verify_stmt->bind_param("s", $email);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        $verify_user = $verify_result->fetch_assoc();
        $password_verified = password_verify($password, $verify_user['password']);
        $password_hash_in_db = $verify_user['password'];
        $verify_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Admin - Travel Agency</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f8f8;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .success {
            background: #d1fae5;
            color: #065f46;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .info {
            background: #dbeafe;
            color: #1e40af;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        h1 {
            margin-top: 0;
            color: #111618;
        }
        .credentials {
            background: #f0f3f5;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .credentials strong {
            display: block;
            margin-bottom: 5px;
        }
        a {
            color: #0db9f2;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Admin Account Fix</h1>
        
        <?php if ($success): ?>
            <div class="success">
                <strong>✓ Success!</strong><br>
                <?php echo htmlspecialchars($message); ?>
            </div>
            
            <div class="credentials">
                <strong>Admin Credentials:</strong>
                <div>Email: <code><?php echo htmlspecialchars($email); ?></code></div>
                <div>Password: <code><?php echo htmlspecialchars($password); ?></code></div>
            </div>
            
            <?php if ($password_verified): ?>
                <div class="success">
                    <strong>✓ Password Verification:</strong> Password hash verified successfully!<br>
                    <small>Hash: <?php echo substr($password_hash_in_db, 0, 30); ?>...</small>
                </div>
            <?php else: ?>
                <div class="error">
                    <strong>⚠ Warning:</strong> Password verification failed. The password has been reset. Please refresh this page.
                </div>
            <?php endif; ?>
            
            <div class="info" style="margin-top: 20px;">
                <strong>Database Check:</strong><br>
                <?php
                $check_stmt = $conn->prepare("SELECT id, email, user_type FROM users WHERE email = ?");
                $check_stmt->bind_param("s", $email);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                if ($check_result->num_rows > 0) {
                    $check_user = $check_result->fetch_assoc();
                    echo "User exists with user_type: <strong>" . htmlspecialchars($check_user['user_type']) . "</strong><br>";
                    if ($check_user['user_type'] !== 'admin') {
                        echo "<span style='color: red;'>⚠ WARNING: User type is not 'admin'! It is: '" . htmlspecialchars($check_user['user_type']) . "'</span><br>";
                        echo "This will prevent admin login. The user_type needs to be exactly 'admin'.";
                    }
                }
                $check_stmt->close();
                ?>
            </div>
            
        <?php else: ?>
            <div class="error">
                <strong>✗ Error:</strong><br>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="info">
            <strong>Next Steps:</strong>
            <ol style="margin: 10px 0; padding-left: 20px;">
                <li>Go to the <a href="login.php">Login Page</a></li>
                <li>Select "Staff/Agent" user type</li>
                <li>Enter the credentials shown above</li>
                <li>Click "Log In"</li>
            </ol>
        </div>
        
        <p style="margin-top: 30px;">
            <a href="login.php">→ Go to Login Page</a>
        </p>
    </div>
</body>
</html>
