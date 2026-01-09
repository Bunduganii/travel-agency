# Quick Start Guide

## 🚀 Fast Setup (5 Minutes)

### For Windows (XAMPP)

1. **Download & Install XAMPP**
   - Get latest XAMPP from https://www.apachefriends.org/
   - Install with Apache and MySQL selected

2. **Clone/Download Project**
   ```bash
   # Clone from GitHub
   git clone https://github.com/YOUR_USERNAME/travel-agency-booking-system.git
   
   # OR Download ZIP and extract
   ```

3. **Move to XAMPP htdocs**
   ```
   Copy project folder to: C:\XAMPP\htdocs\travel-agency-booking-system\
   ```

4. **Check PHP Requirements**
   ```bash
   cd C:\XAMPP\htdocs\travel-agency-booking-system
   php requirements.php
   ```
   ✅ All checks should pass

5. **Configure Database**
   - Copy `includes/db.php.example` to `includes/db.php`
   - Edit `includes/db.php` (usually no changes needed if using default XAMPP)

6. **Create Database**
   - Open http://localhost/phpmyadmin
   - Click "New" → Name: `travel_agency_db` → Create
   - Select `travel_agency_db` → Import → Choose `database/travel_agency.sql` → Go

7. **Start Services**
   - Open XAMPP Control Panel
   - Start **Apache** ✅
   - Start **MySQL** ✅

8. **Access Application**
   ```
   http://localhost/travel-agency-booking-system/
   ```

9. **Login as Admin**
   - Go to: http://localhost/travel-agency-booking-system/admin_login.php
   - Email: `admin@travelagency.com`
   - Password: `password`

**Done! ✅**

---

## 🔍 Troubleshooting

### "PHP version not recognized" Error

**Solution:**
1. Check XAMPP version - should be latest (includes PHP 7.4+)
2. Verify PHP: Open Command Prompt, type `php -v`
3. If PHP < 7.4:
   - Download latest XAMPP
   - OR install PHP 7.4+ separately and add to PATH

### "Missing mysqli extension" Error

**Solution:**
1. Open `C:\XAMPP\php\php.ini`
2. Find and uncomment (remove `;`):
   ```ini
   extension=mysqli
   extension=mbstring
   extension=session
   extension=json
   ```
3. Save file
4. Restart Apache from XAMPP Control Panel

### "Database connection failed" Error

**Solution:**
1. Check `includes/db.php` exists (copy from `db.php.example` if missing)
2. Verify MySQL is running (green in XAMPP Control Panel)
3. Check database `travel_agency_db` exists in phpMyAdmin
4. Verify credentials in `includes/db.php`

### "404 Not Found" Error

**Solution:**
1. Verify project is in `C:\XAMPP\htdocs\` folder
2. Check folder name matches URL (case-sensitive on some servers)
3. Verify `.htaccess` file exists in project root
4. Try: http://localhost/travel-agency-booking-system/index.php

---

## 📋 Quick Checklist

Before reporting issues, verify:

- [ ] XAMPP/WAMP installed (latest version)
- [ ] Apache running (green in Control Panel)
- [ ] MySQL running (green in Control Panel)
- [ ] PHP >= 7.4 (`php -v` or `php requirements.php`)
- [ ] Required extensions loaded (`php requirements.php`)
- [ ] `includes/db.php` exists (copied from `db.php.example`)
- [ ] Database `travel_agency_db` exists in phpMyAdmin
- [ ] Database schema imported (`database/travel_agency.sql`)
- [ ] Project in correct folder (`htdocs` or `www`)
- [ ] `.htaccess` file exists in project root

---

## 💡 Pro Tips

1. **Always run `php requirements.php` first** - it catches most issues
2. **Check XAMPP Control Panel** - both services should be green
3. **Verify database exists** - check phpMyAdmin
4. **Use correct URL** - match folder name exactly
5. **Check error logs** - XAMPP logs in `xampp/apache/logs/error.log`

---

## 🆘 Still Stuck?

1. **Run diagnostic:**
   ```bash
   php requirements.php
   ```
   Share the output

2. **Check XAMPP version:**
   - Should be latest (includes PHP 7.4+)
   - Check in Control Panel → About

3. **Verify all files present:**
   - `.php-version` ✓
   - `composer.json` ✓
   - `requirements.php` ✓
   - `.htaccess` ✓
   - `includes/db.php` ✓ (copied from `db.php.example`)

4. **Check file permissions:**
   - Files should be readable (no permission issues on Windows)

5. **Test PHP directly:**
   ```bash
   php -v
   php -m | grep mysqli
   ```

Share the outputs for help!
