# Fix MySQL Connection Error

## Error Message
```
mysqli::real_connect(): (HY000/2002): No connection could be made because the target machine actively refused it
```

This means **MySQL server is not running** in XAMPP.

---

## ✅ Solution: Start MySQL in XAMPP

### Step 1: Open XAMPP Control Panel
1. Press `Windows Key` and search for "XAMPP Control Panel"
2. Or navigate to: `C:\xampp\xampp-control.exe`
3. Open it (you may need to run as Administrator)

### Step 2: Start MySQL
1. In XAMPP Control Panel, find the **MySQL** row
2. Click the **"Start"** button next to MySQL
3. Wait for it to turn green (status should show "Running")

### Step 3: Verify MySQL is Running
- The MySQL status should show:
  - ✅ **Green** background
  - Status: **Running**
  - Port: **3306**

### Step 4: Test the Connection
1. Refresh your browser page: `http://localhost/Travel-agency-reser/create_admin.php`
2. Or try: `http://localhost/Travel-agency-reser/login.php`

---

## 🔧 Alternative: Check MySQL Port

If MySQL still doesn't connect, check if port 3306 is available:

1. Open XAMPP Control Panel
2. Click **"Config"** next to MySQL
3. Select **"my.ini"**
4. Look for `port=3306`
5. Make sure no other application is using port 3306

---

## 📝 Database Configuration

Your database settings in `includes/db.php`:
- **Host:** `localhost`
- **User:** `root`
- **Password:** (empty)
- **Database:** `travel_agency_db`

Make sure:
1. MySQL is running in XAMPP
2. Database `travel_agency_db` exists (create it in phpMyAdmin if needed)
3. All tables are created (run `database/travel_agency.sql` if needed)

---

## 🚀 Quick Fix Checklist

- [ ] XAMPP Control Panel is open
- [ ] MySQL is **Started** (green status)
- [ ] Apache is **Started** (if needed)
- [ ] Database `travel_agency_db` exists
- [ ] Try accessing the page again

---

## 💡 Still Having Issues?

1. **Check XAMPP Logs:**
   - XAMPP Control Panel → MySQL → **Logs**
   - Look for error messages

2. **Check if MySQL is already running:**
   - Open Task Manager (Ctrl+Shift+Esc)
   - Look for `mysqld.exe` process
   - If it exists, MySQL might be running from another source

3. **Restart XAMPP:**
   - Stop MySQL
   - Stop Apache
   - Close XAMPP Control Panel
   - Reopen XAMPP Control Panel
   - Start MySQL again

4. **Check Windows Firewall:**
   - Sometimes Windows Firewall blocks MySQL
   - Try temporarily disabling firewall to test

---

## ✅ After MySQL is Running

Once MySQL is started:
1. Go to: `http://localhost/Travel-agency-reser/create_admin.php`
2. Create the admin account
3. Then try logging in at: `http://localhost/Travel-agency-reser/login.php`

The login form should now work correctly with the `user_type` field being submitted properly!

