# Admin Login Debugging Guide

## Quick Fix Steps

### Step 1: Run the Debug Script
1. Open your browser
2. Go to: `http://localhost/Travel-agency-reser/debug_admin.php`
3. This will show you:
   - If the admin user exists
   - What the user_type is in the database
   - If the password hash is correct
   - It will also fix/create the admin account

### Step 2: Run the Fix Script
1. Go to: `http://localhost/Travel-agency-reser/fix_admin.php`
2. This will create/update the admin account
3. Check the output - it should show:
   - ✓ Admin password updated/created
   - ✓ Password verification: SUCCESS
   - User type in database should be exactly: **admin**

### Step 3: Check Browser Console
1. Open the login page
2. Press **F12** to open Developer Tools
3. Go to the **Console** tab
4. Try to log in as admin
5. Check the console for debug messages showing:
   - What user_type is being submitted
   - What the form data contains

### Step 4: Verify Database
1. Open phpMyAdmin
2. Go to `travel_agency_db` database
3. Open the `users` table
4. Find the user with email: `admin@travelagency.com`
5. Check:
   - **user_type** column should be exactly: `admin` (not `Admin` or `ADMIN`)
   - **email** should be exactly: `admin@travelagency.com` (case-sensitive)

## Common Issues

### Issue 1: User Type Mismatch
**Problem:** The user_type in database is not exactly 'admin'
- It might be 'Admin', 'ADMIN', or something else
- **Solution:** Run `fix_admin.php` or manually update in phpMyAdmin:
  ```sql
  UPDATE users SET user_type = 'admin' WHERE email = 'admin@travelagency.com';
  ```

### Issue 2: Password Hash Issue
**Problem:** Password hash doesn't match
- **Solution:** Run `fix_admin.php` - it will regenerate the password hash

### Issue 3: Form Not Submitting user_type
**Problem:** The radio button value is not being sent
- **Solution:** 
  1. Open browser console (F12)
  2. Check if user_type is in the form data
  3. Make sure you click "Staff/Agent" before submitting

### Issue 4: Email Case Sensitivity
**Problem:** Email doesn't match exactly
- Make sure you enter: `admin@travelagency.com` (lowercase)
- Not: `Admin@TravelAgency.com` or `ADMIN@TRAVELAGENCY.COM`

## Manual Database Fix

If the scripts don't work, run this SQL in phpMyAdmin:

```sql
-- Delete existing admin if any
DELETE FROM users WHERE email = 'admin@travelagency.com';

-- Create new admin with correct password hash
INSERT INTO users (username, email, password, full_name, user_type) 
VALUES (
    'admin', 
    'admin@travelagency.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'Admin User', 
    'admin'
);
```

**Note:** The password hash above is for password: `admin123`

## Testing

After fixing, test login:
1. Go to login page
2. Click "Staff/Agent" (should highlight)
3. Enter email: `admin@travelagency.com`
4. Enter password: `admin123`
5. Click "Log In"
6. Should redirect to admin dashboard

## Still Not Working?

1. Check PHP error logs (usually in XAMPP: `C:\xampp\php\logs\php_error_log`)
2. Check Apache error logs (usually in XAMPP: `C:\xampp\apache\logs\error.log`)
3. Make sure MySQL is running
4. Make sure the database `travel_agency_db` exists
5. Check that `includes/db.php` has correct database credentials

