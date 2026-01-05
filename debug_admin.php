<?php
/**
 * Debug Admin Login - Check what's in the database
 * Visit: http://localhost/Travel-agency-reser/debug_admin.php
 */
require_once 'includes/db.php';

$email = 'admin@travelagency.com';
$password = 'admin123';

echo "<h1>Admin Login Debug</h1>";
echo "<pre>";

// Check if admin exists
echo "=== Checking Database ===\n";
$stmt = $conn->prepare("SELECT id, username, email, password, full_name, user_type FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "✓ User found in database:\n";
    echo "  ID: " . $user['id'] . "\n";
    echo "  Username: " . $user['username'] . "\n";
    echo "  Email: " . $user['email'] . "\n";
    echo "  Full Name: " . $user['full_name'] . "\n";
    echo "  User Type: " . $user['user_type'] . "\n";
    echo "  Password Hash: " . substr($user['password'], 0, 20) . "...\n\n";
    
    // Test password verification
    echo "=== Testing Password Verification ===\n";
    $test_password = 'admin123';
    $password_match = password_verify($test_password, $user['password']);
    echo "Testing password: '$test_password'\n";
    echo "Password matches: " . ($password_match ? "YES ✓" : "NO ✗") . "\n\n";
    
    // Test with user_type filter
    echo "=== Testing Query with user_type Filter ===\n";
    $stmt2 = $conn->prepare("SELECT id, username, email, password, full_name, user_type FROM users WHERE email = ? AND user_type = ?");
    $user_type = 'admin';
    $stmt2->bind_param("ss", $email, $user_type);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    echo "Query: SELECT ... WHERE email = '$email' AND user_type = '$user_type'\n";
    echo "Rows found: " . $result2->num_rows . "\n";
    if ($result2->num_rows > 0) {
        $user2 = $result2->fetch_assoc();
        echo "✓ User found with user_type filter\n";
        echo "  User Type: " . $user2['user_type'] . "\n";
    } else {
        echo "✗ No user found with user_type filter\n";
        echo "  This means user_type in DB is: '" . $user['user_type'] . "'\n";
        echo "  But we're looking for: 'admin'\n";
    }
    $stmt2->close();
    
    // Check all admins
    echo "\n=== All Admin Users in Database ===\n";
    $all_admins = $conn->query("SELECT id, username, email, user_type FROM users WHERE user_type = 'admin'");
    if ($all_admins->num_rows > 0) {
        while ($admin = $all_admins->fetch_assoc()) {
            echo "  - " . $admin['email'] . " (" . $admin['user_type'] . ")\n";
        }
    } else {
        echo "  No users with user_type = 'admin' found!\n";
    }
    
} else {
    echo "✗ No user found with email: $email\n";
    echo "\n=== All Users in Database ===\n";
    $all_users = $conn->query("SELECT id, username, email, user_type FROM users LIMIT 10");
    while ($u = $all_users->fetch_assoc()) {
        echo "  - " . $u['email'] . " (" . $u['user_type'] . ")\n";
    }
}

$stmt->close();

echo "\n=== Creating/Updating Admin ===\n";
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo "New password hash: " . substr($hashed_password, 0, 30) . "...\n";

// Check if admin exists
$check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND user_type = 'admin'");
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // Update
    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ? AND user_type = 'admin'");
    $update_stmt->bind_param("ss", $hashed_password, $email);
    if ($update_stmt->execute()) {
        echo "✓ Admin password updated\n";
    } else {
        echo "✗ Error updating: " . $conn->error . "\n";
    }
    $update_stmt->close();
} else {
    // Create
    $username = 'admin';
    $full_name = 'Admin User';
    $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, user_type) VALUES (?, ?, ?, ?, 'admin')");
    $insert_stmt->bind_param("ssss", $username, $email, $hashed_password, $full_name);
    if ($insert_stmt->execute()) {
        echo "✓ Admin user created\n";
    } else {
        echo "✗ Error creating: " . $conn->error . "\n";
    }
    $insert_stmt->close();
}
$check_stmt->close();

// Verify again
echo "\n=== Final Verification ===\n";
$verify_stmt = $conn->prepare("SELECT password FROM users WHERE email = ? AND user_type = 'admin'");
$verify_stmt->bind_param("s", $email);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();
if ($verify_result->num_rows > 0) {
    $verify_user = $verify_result->fetch_assoc();
    $verify_match = password_verify($password, $verify_user['password']);
    echo "Password verification: " . ($verify_match ? "SUCCESS ✓" : "FAILED ✗") . "\n";
    if ($verify_match) {
        echo "\n✅ Admin login should work now!\n";
        echo "Email: $email\n";
        echo "Password: $password\n";
    }
}
$verify_stmt->close();

echo "</pre>";
echo "<p><a href='login.php'>Go to Login Page</a></p>";
$conn->close();
?>

