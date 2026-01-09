# Setup Checklist for New Installation

## ⚠️ Common Errors and Solutions

### Error 1: "Connection failed" or Database Error
**Solution:**
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL** services
3. Open phpMyAdmin: `http://localhost/phpmyadmin`
4. Create database: `travel_agency_db`
5. Import file: `database/travel_agency.sql`
6. Check `includes/db.php` - Update password if MySQL has a password:
   ```php
   define('DB_PASS', 'your_password_here'); // If MySQL has password
   ```

### Error 2: "Access Denied" or "Can't connect to MySQL"
**Solution:**
- If MySQL has a password on friend's laptop, update `includes/db.php`:
  ```php
  define('DB_USER', 'root');
  define('DB_PASS', 'password_here'); // Add password if exists
  ```

### Error 3: "Database doesn't exist"
**Solution:**
1. Open phpMyAdmin
2. Click "New" to create database
3. Name it: `travel_agency_db`
4. Click "Import" tab
5. Choose file: `database/travel_agency.sql`
6. Click "Go"

### Error 4: "404 Not Found" or Page Not Loading
**Solution:**
1. Make sure project folder is in: `C:\XAMPP\htdocs\Travel-agency-reser\`
2. Access via: `http://localhost/Travel-agency-reser/`
3. If folder name is different, use that name in URL

### Error 5: "Call to undefined function" or PHP Errors
**Solution:**
- Make sure PHP version is 7.4 or higher
- Check XAMPP PHP version in Control Panel

## 📋 Step-by-Step Setup (For Friend's Laptop)

### Step 1: Install XAMPP
- Download and install XAMPP from https://www.apachefriends.org/
- Make sure to install Apache and MySQL

### Step 2: Extract Project
- Extract project folder to: `C:\XAMPP\htdocs\`
- Folder should be: `C:\XAMPP\htdocs\Travel-agency-reser\`

### Step 3: Start Services
- Open XAMPP Control Panel
- Click "Start" for **Apache**
- Click "Start" for **MySQL**
- Both should show green "Running"

### Step 4: Create Database
1. Open browser: `http://localhost/phpmyadmin`
2. Click "New" on left sidebar
3. Database name: `travel_agency_db`
4. Collation: `utf8mb4_general_ci`
5. Click "Create"

### Step 5: Import Database
1. Click on `travel_agency_db` database (left sidebar)
2. Click "Import" tab (top menu)
3. Click "Choose File"
4. Select: `database/travel_agency.sql`
5. Scroll down, click "Go"
6. Wait for "Import has been successfully finished"

### Step 6: Configure Database Connection
1. Open file: `includes/db.php`
2. Check these lines:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // If MySQL has password, add it here
   define('DB_NAME', 'travel_agency_db');
   ```
3. If MySQL has a password, update `DB_PASS`

### Step 7: Test Installation
1. Open browser: `http://localhost/Travel-agency-reser/`
2. Should see the homepage
3. Try: `http://localhost/Travel-agency-reser/login.php`
4. Try: `http://localhost/Travel-agency-reser/admin_login.php`

### Step 8: Login as Admin
**Default Admin Credentials (already in database):**
- **Email:** `admin@travelagency.com`
- **Username:** `admin`
- **Password:** `password`

1. Go to: `http://localhost/Travel-agency-reser/admin_login.php`
2. Login with the credentials above
3. ⚠️ **IMPORTANT:** Change the password after first login for security!

**OR Create New Admin Account:**
1. Register a new account via `register.php`
2. Open phpMyAdmin → `travel_agency_db` → `users` table
3. Find your registered user
4. Click "Edit" (pencil icon)
5. Change `user_type` from `customer` to `admin`
6. Click "Go"
7. Now you can login as admin

## 🔍 Quick Diagnostic

If you see an error, check:

1. **XAMPP Running?**
   - Apache: Green "Running" ✓
   - MySQL: Green "Running" ✓

2. **Database Exists?**
   - Open phpMyAdmin
   - Check if `travel_agency_db` appears in left sidebar

3. **Database Has Tables?**
   - Click `travel_agency_db`
   - Should see tables: `users`, `flights`, `hotels`, etc.

4. **File Path Correct?**
   - Project should be in: `C:\XAMPP\htdocs\Travel-agency-reser\`
   - URL should be: `http://localhost/Travel-agency-reser/`

5. **Database Password?**
   - Check if MySQL has password
   - Update `includes/db.php` if needed

## 📞 Still Having Issues?

Share the **exact error message** you see, and I'll help fix it!

Common error formats:
- "Connection failed: ..."
- "Fatal error: ..."
- "Warning: ..."
- "404 Not Found"
- Any red text on the page

