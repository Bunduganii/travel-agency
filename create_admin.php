<?php
/**
 * Create New Admin Account
 * Email: admin@system.com
 * Password: Admin@12345
 * 
 * Visit: http://localhost/Travel-agency-reser/create_admin.php
 */
require_once 'includes/db.php';

$email = 'admin@system.com';
$password = 'Admin@12345';
$username = 'admin';
$full_name = 'System Administrator';

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$message = '';
$success = false;
$errors = [];

// Check if user with email exists
$check_email_stmt = $conn->prepare("SELECT id, username, email, user_type FROM users WHERE email = ?");
$check_email_stmt->bind_param("s", $email);
$check_email_stmt->execute();
$check_email_result = $check_email_stmt->get_result();
$existing_user_by_email = $check_email_result->fetch_assoc();
$check_email_stmt->close();

// Check if user with username exists
$check_username_stmt = $conn->prepare("SELECT id, username, email, user_type FROM users WHERE username = ?");
$check_username_stmt->bind_param("s", $username);
$check_username_stmt->execute();
$check_username_result = $check_username_stmt->get_result();
$existing_user_by_username = $check_username_result->fetch_assoc();
$check_username_stmt->close();

// Determine which user to update/create
$user_to_update = null;
$update_reason = '';

if ($existing_user_by_email && $existing_user_by_username) {
    // Both email and username exist
    if ($existing_user_by_email['id'] === $existing_user_by_username['id']) {
        // Same user - update it
        $user_to_update = $existing_user_by_email;
        $update_reason = 'same_user';
    } else {
        // Different users - update the one with matching email (preferred)
        $user_to_update = $existing_user_by_email;
        $update_reason = 'email_match';
        // Note: The username conflict will be resolved by updating the existing user
    }
} elseif ($existing_user_by_email) {
    // Only email exists
    $user_to_update = $existing_user_by_email;
    $update_reason = 'email_exists';
} elseif ($existing_user_by_username) {
    // Only username exists - update that user
    $user_to_update = $existing_user_by_username;
    $update_reason = 'username_exists';
}

if ($user_to_update) {
    // Update existing user
    $update_id = $user_to_update['id'];
    
    // If updating username conflict, we need to handle it carefully
    if ($update_reason === 'email_match' && $existing_user_by_username && $existing_user_by_username['id'] !== $update_id) {
        // There's a username conflict - we'll update the email user and change the username user's username
        // First, change the conflicting username to something else
        $conflict_username = 'admin_' . time();
        $fix_conflict_stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ? AND id != ?");
        $fix_conflict_stmt->bind_param("sii", $conflict_username, $existing_user_by_username['id'], $update_id);
        $fix_conflict_stmt->execute();
        $fix_conflict_stmt->close();
    }
    
    // Now update the target user
    if ($user_to_update['user_type'] !== 'admin') {
        // Update to admin
        $update_stmt = $conn->prepare("UPDATE users SET password = ?, user_type = 'admin', username = ?, full_name = ?, email = ? WHERE id = ?");
        $update_stmt->bind_param("ssssi", $hashed_password, $username, $full_name, $email, $update_id);
        if ($update_stmt->execute()) {
            $message = "Existing user updated to admin successfully!";
            $success = true;
        } else {
            $message = "Error updating user: " . $conn->error;
            $errors[] = $conn->error;
        }
        $update_stmt->close();
    } else {
        // Already admin, just update password and other fields
        $update_stmt = $conn->prepare("UPDATE users SET password = ?, username = ?, full_name = ?, email = ? WHERE id = ? AND user_type = 'admin'");
        $update_stmt->bind_param("ssssi", $hashed_password, $username, $full_name, $email, $update_id);
        if ($update_stmt->execute()) {
            $message = "Admin account updated successfully!";
            $success = true;
        } else {
            $message = "Error updating admin: " . $conn->error;
            $errors[] = $conn->error;
        }
        $update_stmt->close();
    }
} else {
    // No existing user - create new admin user
    try {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, user_type) VALUES (?, ?, ?, ?, 'admin')");
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $full_name);
        if ($stmt->execute()) {
            $message = "Admin user created successfully!";
            $success = true;
        } else {
            throw new Exception($conn->error);
        }
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        // Handle duplicate entry error
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            if (strpos($e->getMessage(), 'username') !== false) {
                // Username conflict - update existing user with that username
                $fallback_stmt = $conn->prepare("UPDATE users SET password = ?, user_type = 'admin', email = ?, full_name = ? WHERE username = ?");
                $fallback_stmt->bind_param("ssss", $hashed_password, $email, $full_name, $username);
                if ($fallback_stmt->execute()) {
                    $message = "Existing user with username 'admin' updated to admin account successfully!";
                    $success = true;
                } else {
                    $message = "Error updating existing user: " . $conn->error;
                    $errors[] = $conn->error;
                }
                $fallback_stmt->close();
            } elseif (strpos($e->getMessage(), 'email') !== false) {
                // Email conflict - update existing user with that email
                $fallback_stmt = $conn->prepare("UPDATE users SET password = ?, user_type = 'admin', username = ?, full_name = ? WHERE email = ?");
                $fallback_stmt->bind_param("ssss", $hashed_password, $username, $full_name, $email);
                if ($fallback_stmt->execute()) {
                    $message = "Existing user with email 'admin@system.com' updated to admin account successfully!";
                    $success = true;
                } else {
                    $message = "Error updating existing user: " . $conn->error;
                    $errors[] = $conn->error;
                }
                $fallback_stmt->close();
            } else {
                $message = "Duplicate entry error: " . $e->getMessage();
                $errors[] = $e->getMessage();
            }
        } else {
            $message = "Error creating admin user: " . $e->getMessage();
            $errors[] = $e->getMessage();
        }
    } catch (Exception $e) {
        $message = "Error creating admin user: " . $e->getMessage();
        $errors[] = $e->getMessage();
    }
}

// Verify the password works
$verify_stmt = $conn->prepare("SELECT id, username, email, password, full_name, user_type FROM users WHERE email = ? AND user_type = 'admin'");
$verify_stmt->bind_param("s", $email);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();
$admin = $verify_result->fetch_assoc();
$verify_stmt->close();

$password_verified = false;
$password_hash_in_db = '';
$admin_info = null;

if ($admin) {
    $password_hash_in_db = $admin['password'];
    $password_verified = password_verify($password, $admin['password']);
    $admin_info = $admin;
    
    // If password doesn't verify, update it
    if (!$password_verified) {
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ? AND user_type = 'admin'");
        $update_stmt->bind_param("ss", $new_hash, $email);
        $update_stmt->execute();
        $update_stmt->close();
        
        // Verify again
        $verify_stmt2 = $conn->prepare("SELECT password FROM users WHERE email = ? AND user_type = 'admin'");
        $verify_stmt2->bind_param("s", $email);
        $verify_stmt2->execute();
        $verify_result2 = $verify_stmt2->get_result();
        $verify_user = $verify_result2->fetch_assoc();
        $password_verified = password_verify($password, $verify_user['password']);
        $password_hash_in_db = $verify_user['password'];
        $verify_stmt2->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account - Travel Agency</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            max-width: 700px;
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
            border-left: 4px solid #10b981;
        }
        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #ef4444;
        }
        .info {
            background: #dbeafe;
            color: #1e40af;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #3b82f6;
        }
        .warning {
            background: #fef3c7;
            color: #92400e;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #f59e0b;
        }
        h1 {
            margin-top: 0;
            color: #111618;
        }
        .credentials {
            background: #f0f3f5;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 2px solid #0db9f2;
        }
        .credentials strong {
            display: block;
            margin-bottom: 10px;
            color: #0c4a6e;
            font-size: 16px;
        }
        .credentials code {
            background: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            display: inline-block;
            margin: 5px 0;
        }
        .sql-box {
            background: #1e293b;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
        }
        .sql-box code {
            color: #60a5fa;
        }
        a {
            color: #0db9f2;
            text-decoration: none;
            font-weight: 600;
        }
        a:hover {
            text-decoration: underline;
        }
        .btn {
            display: inline-block;
            background: #0db9f2;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 20px;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #0ea5e9;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔐 Create Admin Account</h1>
        
        <?php if ($success): ?>
            <div class="success">
                <strong>✓ Success!</strong><br>
                <?php echo htmlspecialchars($message); ?>
            </div>
            
            <div class="credentials">
                <strong>📋 Admin Credentials:</strong>
                <div style="margin-top: 10px;">
                    <strong>Email:</strong> <code><?php echo htmlspecialchars($email); ?></code><br>
                    <strong>Password:</strong> <code><?php echo htmlspecialchars($password); ?></code><br>
                    <strong>Username:</strong> <code><?php echo htmlspecialchars($username); ?></code><br>
                    <strong>Full Name:</strong> <code><?php echo htmlspecialchars($full_name); ?></code><br>
                    <strong>User Type:</strong> <code>admin</code>
                </div>
            </div>
            
            <?php if ($password_verified): ?>
                <div class="success">
                    <strong>✓ Password Verification:</strong> Password hash verified successfully!<br>
                    <small style="opacity: 0.8;">Hash: <?php echo substr($password_hash_in_db, 0, 30); ?>...</small>
                </div>
            <?php else: ?>
                <div class="error">
                    <strong>⚠ Warning:</strong> Password verification failed. Please refresh this page.
                </div>
            <?php endif; ?>
            
            <?php if ($admin_info): ?>
                <div class="info">
                    <strong>📊 Admin Account Details:</strong><br>
                    <div style="margin-top: 10px;">
                        ID: <strong><?php echo $admin_info['id']; ?></strong><br>
                        Username: <strong><?php echo htmlspecialchars($admin_info['username']); ?></strong><br>
                        Email: <strong><?php echo htmlspecialchars($admin_info['email']); ?></strong><br>
                        Full Name: <strong><?php echo htmlspecialchars($admin_info['full_name']); ?></strong><br>
                        User Type: <strong style="color: #059669;"><?php echo htmlspecialchars($admin_info['user_type']); ?></strong><br>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="error">
                <strong>✗ Error:</strong><br>
                <?php echo htmlspecialchars($message); ?>
                <?php if (!empty($errors)): ?>
                    <ul style="margin-top: 10px; padding-left: 20px;">
                        <?php foreach ($errors as $err): ?>
                            <li><?php echo htmlspecialchars($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="sql-box">
            <strong style="color: #60a5fa;">SQL Query Used:</strong><br><br>
            <code>
<?php
$sql_query = "-- Create Admin Account\n";
$sql_query .= "INSERT INTO users (username, email, password, full_name, user_type) VALUES\n";
$sql_query .= "('" . addslashes($username) . "', '" . addslashes($email) . "', '" . addslashes($hashed_password) . "', '" . addslashes($full_name) . "', 'admin');\n\n";
$sql_query .= "-- Or if user exists, update:\n";
$sql_query .= "UPDATE users SET password = '" . addslashes($hashed_password) . "', user_type = 'admin', username = '" . addslashes($username) . "', full_name = '" . addslashes($full_name) . "' WHERE email = '" . addslashes($email) . "';";
echo htmlspecialchars($sql_query);
?>
            </code>
        </div>
        
        <div class="info">
            <strong>📝 Next Steps:</strong>
            <ol style="margin: 10px 0; padding-left: 20px;">
                <li>Go to the <a href="login.php">Login Page</a></li>
                <li>Select <strong>"Staff/Agent"</strong> user type</li>
                <li>Enter the credentials shown above</li>
                <li>Click "Log In"</li>
            </ol>
        </div>
        
        <a href="login.php" class="btn">→ Go to Login Page</a>
    </div>
</body>
</html>

