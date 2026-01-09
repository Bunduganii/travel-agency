# Deployment Guide

This guide helps you deploy the Travel Agency Booking System to GitHub and set it up on another device.

## 📤 Pushing to GitHub

### Step 1: Prepare Files for Git

1. **Verify `.gitignore` is set up correctly:**
   - Sensitive files like `includes/db.php` should NOT be committed
   - Configuration files are in `.gitignore`

2. **Check all necessary files are present:**
   ```
   ✓ .php-version          (PHP version specification)
   ✓ composer.json         (PHP version and project info)
   ✓ requirements.php      (PHP requirements checker)
   ✓ .htaccess            (Apache configuration)
   ✓ .gitignore           (Git ignore rules)
   ✓ includes/db.php.example  (Database config template)
   ✓ README.md             (Setup instructions)
   ✓ DEPLOYMENT.md         (This file)
   ```

### Step 2: Initialize Git Repository

```bash
# If not already a git repository
git init
git add .
git commit -m "Initial commit - Travel Agency Booking System"
```

### Step 3: Create GitHub Repository

1. Go to https://github.com/new
2. Create a new repository (e.g., `travel-agency-booking-system`)
3. **DO NOT** initialize with README, .gitignore, or license (we already have these)

### Step 4: Push to GitHub

```bash
git remote add origin https://github.com/YOUR_USERNAME/travel-agency-booking-system.git
git branch -M main
git push -u origin main
```

## 📥 Setting Up on Another Device

### Step 1: Download/Clone from GitHub

```bash
# Option 1: Clone via Git
git clone https://github.com/YOUR_USERNAME/travel-agency-booking-system.git
cd travel-agency-booking-system

# Option 2: Download ZIP
# 1. Go to GitHub repository
# 2. Click "Code" → "Download ZIP"
# 3. Extract ZIP file
# 4. Move to web server directory (htdocs for XAMPP, www for WAMP)
```

### Step 2: Check PHP Requirements

**CRITICAL:** Always run this first to verify PHP environment:

```bash
php requirements.php
```

**Expected output:**
```
=== PHP Requirements Check ===

PHP Version: 7.4.33 (Required: >= 7.4.0)
✓ PHP version is compatible

Checking Required Extensions:
✓ mysqli - MySQLi extension for database connectivity
✓ pdo_mysql - PDO MySQL extension (optional but recommended)
✓ session - Session extension for user authentication
✓ mbstring - Multibyte String extension for string handling
✓ json - JSON extension for data encoding/decoding

=== Summary ===
✓ All required extensions are loaded
✓ PHP environment is ready for this application
```

**If you see errors:**
- Follow the instructions in the output
- See troubleshooting section in README.md

### Step 3: Configure Database

1. **Copy the example database configuration:**
   ```bash
   # On Linux/Mac:
   cp includes/db.php.example includes/db.php
   
   # On Windows:
   # Copy db.php.example and rename to db.php manually
   ```

2. **Edit `includes/db.php`** with your local database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');              // Add password if MySQL has one
   define('DB_NAME', 'travel_agency_db');
   ```

### Step 4: Set Up Database

1. **Start MySQL service:**
   - XAMPP: Start MySQL from Control Panel
   - WAMP: Start MySQL service
   - Linux: `sudo systemctl start mysql`

2. **Create database:**
   ```sql
   -- Via phpMyAdmin (http://localhost/phpmyadmin):
   -- 1. Click "New" on left sidebar
   -- 2. Database name: travel_agency_db
   -- 3. Collation: utf8mb4_general_ci
   -- 4. Click "Create"
   
   -- OR via command line:
   mysql -u root -p
   CREATE DATABASE travel_agency_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
   EXIT;
   ```

3. **Import database schema:**
   ```bash
   # Via phpMyAdmin:
   # 1. Select travel_agency_db database
   # 2. Click "Import" tab
   # 3. Choose database/travel_agency.sql
   # 4. Click "Go"
   
   # OR via command line:
   mysql -u root -p travel_agency_db < database/travel_agency.sql
   ```

### Step 5: Configure Web Server

#### For XAMPP (Windows/Mac/Linux):

1. **Move project to htdocs:**
   ```bash
   # Windows
   C:\XAMPP\htdocs\travel-agency-booking-system\
   
   # Mac/Linux
   /Applications/XAMPP/htdocs/travel-agency-booking-system/
   # OR
   /opt/lampp/htdocs/travel-agency-booking-system/
   ```

2. **Start Apache and MySQL from XAMPP Control Panel**

3. **Access application:**
   ```
   http://localhost/travel-agency-booking-system/
   ```

#### For WAMP (Windows):

1. **Move project to www:**
   ```
   C:\wamp64\www\travel-agency-booking-system\
   ```

2. **Start WAMP services**

3. **Access application:**
   ```
   http://localhost/travel-agency-booking-system/
   ```

#### For Nginx (Linux):

1. **Configure virtual host:**
   ```nginx
   server {
       listen 80;
       server_name localhost;
       root /var/www/travel-agency-booking-system;
       index index.php index.html;
       
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
           fastcgi_index index.php;
           include fastcgi_params;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
       }
   }
   ```

2. **Restart Nginx:**
   ```bash
   sudo systemctl restart nginx
   ```

### Step 6: Verify Installation

1. **Run requirements check:**
   ```bash
   php requirements.php
   ```

2. **Access the application:**
   ```
   http://localhost/travel-agency-booking-system/
   ```

3. **Test login:**
   - Go to `http://localhost/travel-agency-booking-system/login.php`
   - Register a new account or use admin credentials:
     - Email: `admin@travelagency.com`
     - Password: `password`

## 🔧 Common Issues When Deploying

### Issue 1: "PHP version not recognized" or "Wrong PHP version"

**Solution:**
1. Check PHP version: `php -v`
2. If version < 7.4, upgrade PHP:
   - **XAMPP:** Download latest XAMPP
   - **Linux:** `sudo apt-get install php7.4` or `php8.0`
   - **cPanel:** Change PHP version in hosting control panel
3. Restart web server
4. Run `php requirements.php` again

### Issue 2: "Missing mysqli extension"

**Solution:**
1. Enable in `php.ini`:
   ```ini
   extension=mysqli
   ```
2. Restart Apache/web server
3. Verify: `php -m | grep mysqli`

### Issue 3: "Database connection failed"

**Solution:**
1. Check `includes/db.php` exists (copy from `db.php.example`)
2. Verify database credentials are correct
3. Ensure MySQL service is running
4. Verify database `travel_agency_db` exists

### Issue 4: "404 Not Found" on all pages

**Solution:**
1. Check `.htaccess` file exists in project root
2. If using Apache: Enable `mod_rewrite`:
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```
3. Verify project is in correct directory
4. Check web server document root is set correctly

### Issue 5: "Permission denied" errors

**Solution (Linux/Mac):**
```bash
chmod 755 -R .
chmod 644 includes/db.php
chmod 644 .htaccess
```

### Issue 6: "Composer.json requires PHP 7.4" warning

**This is normal** - it's just specifying the minimum PHP version. If you have PHP 7.4+, you can ignore this. The `composer.json` file helps:
- Heroku recognize PHP version
- Version managers (phpbrew) set correct version
- IDEs recognize PHP version

**You don't need to run Composer** - this is a vanilla PHP project with no Composer dependencies.

## ✅ Post-Deployment Checklist

- [ ] PHP version >= 7.4 (verified by `php requirements.php`)
- [ ] All required extensions loaded
- [ ] Database configured (`includes/db.php` exists and has correct credentials)
- [ ] Database `travel_agency_db` created and schema imported
- [ ] Web server running (Apache/Nginx)
- [ ] Application accessible via browser
- [ ] Can login/register successfully
- [ ] Admin login works (if admin account exists in database)

## 📝 Notes for Different Environments

### Local Development (XAMPP/WAMP)
- Use `localhost` for DB_HOST
- Usually no password for MySQL (`DB_PASS = ''`)
- Project in `htdocs` or `www` folder

### Production/Shared Hosting (cPanel)
- Check hosting PHP version (should be 7.4+)
- Update `DB_HOST` (might be `localhost` or different)
- Update `DB_USER` and `DB_PASS` (from hosting panel)
- May need to adjust file permissions
- `.htaccess` should work automatically

### Cloud Platforms (Heroku, AWS, etc.)
- Use `composer.json` for PHP version specification
- Set environment variables for database credentials
- May need additional configuration files
- Check platform-specific documentation

## 🆘 Still Having Issues?

1. Run `php requirements.php` and share the output
2. Check error logs:
   - Apache: `/var/log/apache2/error.log` (Linux) or XAMPP logs folder
   - PHP: Check `php.ini` error_log setting
3. Enable error display temporarily:
   ```php
   // Add to top of index.php (remove after debugging)
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```
4. Share exact error messages for help

