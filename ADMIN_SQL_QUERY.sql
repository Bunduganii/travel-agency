-- ============================================
-- CREATE ADMIN ACCOUNT - SQL QUERY
-- ============================================
-- Email: admin@system.com
-- Password: Admin@12345
-- Username: admin
-- Full Name: System Administrator
-- User Type: admin
-- ============================================

-- Option 1: Insert new admin (if doesn't exist)
INSERT INTO users (username, email, password, full_name, user_type) 
VALUES (
    'admin', 
    'admin@system.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- This is a placeholder - use create_admin.php to generate correct hash
    'System Administrator', 
    'admin'
);

-- Option 2: Update existing user to admin (if email already exists)
UPDATE users 
SET 
    password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- Replace with actual hash
    user_type = 'admin',
    username = 'admin',
    full_name = 'System Administrator'
WHERE email = 'admin@system.com';

-- Option 3: Insert or Update (MySQL 8.0+)
INSERT INTO users (username, email, password, full_name, user_type) 
VALUES (
    'admin', 
    'admin@system.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- Replace with actual hash
    'System Administrator', 
    'admin'
)
ON DUPLICATE KEY UPDATE
    password = VALUES(password),
    user_type = 'admin',
    username = VALUES(username),
    full_name = VALUES(full_name);

-- ============================================
-- VERIFY ADMIN ACCOUNT
-- ============================================
SELECT id, username, email, full_name, user_type, created_at 
FROM users 
WHERE email = 'admin@system.com' AND user_type = 'admin';

-- ============================================
-- TEST PASSWORD VERIFICATION (PHP)
-- ============================================
-- In PHP, use: password_verify('Admin@12345', $hash_from_database)
-- This query just shows the hash, verification must be done in PHP

-- ============================================
-- IMPORTANT NOTES
-- ============================================
-- 1. The password hash above is a PLACEHOLDER
-- 2. To get the correct hash, run: create_admin.php in browser
--    OR use PHP: password_hash('Admin@12345', PASSWORD_DEFAULT)
-- 3. Never store plain text passwords
-- 4. Always use password_hash() and password_verify() in PHP
-- 5. The hash will be different each time (salt is random)
-- 6. But password_verify() will work with any hash generated for the same password

