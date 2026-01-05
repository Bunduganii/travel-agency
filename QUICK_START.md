# Quick Start Guide - Travel Agency System

## 🚀 Step-by-Step Setup Instructions

### Step 1: Check Your Environment

Make sure you have:
- ✅ PHP installed (7.4 or higher)
- ✅ MySQL/MariaDB installed
- ✅ Web server (Apache/Nginx) or XAMPP/WAMP/MAMP

**Check PHP version:**
```bash
php -v
```

**Check MySQL:**
```bash
mysql --version
```

---

### Step 2: Start Your Web Server

#### Option A: Using XAMPP (Windows/Mac/Linux)
1. Open XAMPP Control Panel
2. Start **Apache**
3. Start **MySQL**
4. Wait for both to show green "Running" status

#### Option B: Using WAMP (Windows)
1. Open WAMP Server
2. Wait for icon to turn green
3. Click icon → Start All Services

#### Option C: Using MAMP (Mac)
1. Open MAMP
2. Click "Start Servers"
3. Wait for Apache and MySQL to start

#### Option D: Using Built-in PHP Server (Development Only)
```bash
# Navigate to project folder
cd Travel-agency-reser

# Start PHP built-in server
php -S localhost:8000
```
Then open: `http://localhost:8000`

---

### Step 3: Set Up Database

#### Method 1: Using phpMyAdmin (Easiest)

1. **Open phpMyAdmin:**
   - XAMPP: `http://localhost/phpmyadmin`
   - WAMP: `http://localhost/phpmyadmin`
   - MAMP: `http://localhost:8888/phpMyAdmin`

2. **Create Database:**
   - Click "New" in left sidebar
   - Database name: `travel_agency_db`
   - Collation: `utf8mb4_general_ci`
   - Click "Create"

3. **Import SQL File:**
   - Click on `travel_agency_db` database
   - Click "Import" tab at top
   - Click "Choose File"
   - Select: `database/travel_agency.sql`
   - Scroll down, click "Go"
   - Wait for "Import has been successfully finished" message

#### Method 2: Using MySQL Command Line

```bash
# Open MySQL command line
mysql -u root -p

# Enter your MySQL password (usually empty for XAMPP/WAMP)
# Then run:
CREATE DATABASE travel_agency_db;
USE travel_agency_db;
SOURCE database/travel_agency.sql;
EXIT;
```

---

### Step 4: Configure Database Connection

1. **Open the file:** `includes/db.php`

2. **Update these lines** (usually no changes needed for XAMPP/WAMP):

```php
define('DB_HOST', 'localhost');      // Usually 'localhost'
define('DB_USER', 'root');           // Usually 'root'
define('DB_PASS', '');               // Usually empty '' or 'root' for MAMP
define('DB_NAME', 'travel_agency_db'); // Database name
```

**Common Configurations:**

**XAMPP/WAMP (Windows):**
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'travel_agency_db');
```

**MAMP (Mac):**
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'travel_agency_db');
```

**Linux:**
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_mysql_password');
define('DB_NAME', 'travel_agency_db');
```

---

### Step 5: Place Project in Web Server Directory

#### XAMPP:
- Copy project folder to: `C:\xampp\htdocs\`
- Or create symlink
- Access: `http://localhost/Travel-agency-reser/`

#### WAMP:
- Copy project folder to: `C:\wamp64\www\`
- Access: `http://localhost/Travel-agency-reser/`

#### MAMP:
- Copy project folder to: `/Applications/MAMP/htdocs/`
- Access: `http://localhost:8888/Travel-agency-reser/`

#### Using PHP Built-in Server:
- Stay in project folder
- Access: `http://localhost:8000`

---

### Step 6: Test the Installation

1. **Open Browser:**
   - Go to: `http://localhost/Travel-agency-reser/` (or your URL)
   - You should see login page

2. **Test Admin Login:**
   - Email: `admin@travelagency.com`
   - Password: `admin123`
   - User Type: Select "Staff/Agent"
   - Click "Log In"

3. **Test Registration:**
   - Click "Sign up for free"
   - Fill in the form
   - Create a customer account
   - Login with new account

---

### Step 7: Verify Everything Works

✅ **Check Admin Dashboard:**
- Login as admin
- You should see dashboard with statistics
- Try adding a flight, hotel, or tour package

✅ **Check Customer Features:**
- Login as customer
- Try searching for flights
- Try booking a hotel
- View tour packages

✅ **Check Database:**
- Go to phpMyAdmin
- Check `travel_agency_db` database
- You should see all tables with data

---

## 🔧 Troubleshooting

### Problem: "Connection failed" Error

**Solution:**
1. Check MySQL is running (green in XAMPP/WAMP)
2. Verify database credentials in `includes/db.php`
3. Make sure database `travel_agency_db` exists
4. Check MySQL username/password

### Problem: "Page Not Found" or 404 Error

**Solution:**
1. Check project is in correct folder (htdocs/www)
2. Verify URL is correct
3. Check Apache is running
4. Try: `http://localhost/Travel-agency-reser/index.php`

### Problem: CSS/Images Not Loading

**Solution:**
1. Check browser console (F12) for errors
2. Verify `assets/` folder exists
3. Clear browser cache (Ctrl+F5)
4. Check file paths in HTML

### Problem: Can't Login as Admin

**Solution:**
1. Make sure you selected "Staff/Agent" user type
2. Try creating new admin in database:
   ```sql
   INSERT INTO users (username, email, password, full_name, user_type) 
   VALUES ('admin', 'admin@travelagency.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin');
   ```
3. Or register new account and change user_type to 'admin' in database

### Problem: Database Import Failed

**Solution:**
1. Make sure database is created first
2. Check file path is correct
3. Try importing via command line instead
4. Check MySQL user has CREATE privileges

---

## 📝 Quick Test Checklist

- [ ] Web server is running
- [ ] MySQL is running
- [ ] Database `travel_agency_db` exists
- [ ] SQL file imported successfully
- [ ] `includes/db.php` configured correctly
- [ ] Can access login page
- [ ] Can register new account
- [ ] Can login as admin
- [ ] Can login as customer
- [ ] Admin dashboard loads
- [ ] Can add flights/hotels/tours
- [ ] Can make bookings

---

## 🎯 Next Steps After Setup

1. **Add Sample Data:**
   - Login as admin
   - Add more flights, hotels, and tour packages
   - Use realistic data for testing

2. **Test All Features:**
   - Make a booking as customer
   - Complete payment process
   - View bookings
   - Cancel a booking
   - Submit feedback

3. **Customize:**
   - Add your logo
   - Change colors in CSS
   - Add real images
   - Modify content

4. **Prepare for Presentation:**
   - Take screenshots
   - Prepare demo data
   - Test all user flows
   - Document any customizations

---

## 💡 Tips

- **Development:** Use XAMPP/WAMP for easy setup
- **Production:** Use proper web server (Apache/Nginx)
- **Backup:** Regularly backup your database
- **Testing:** Create test accounts for different scenarios
- **Security:** Change default passwords before going live

---

## 🆘 Still Having Issues?

1. Check PHP error logs
2. Check MySQL error logs
3. Enable error display in PHP (development only)
4. Check browser console (F12)
5. Verify all file paths are correct
6. Make sure all files are uploaded

---

**You're all set! Start exploring the system.** 🎉

