# Travel Agency Booking System - Requirements

## System Requirements

### Server Environment
- **PHP Version:** 7.4 or higher (PHP 8.0+ recommended)
- **MySQL Version:** 5.7 or higher (MySQL 8.0+ or MariaDB 10.2+)
- **Web Server:** Apache 2.4+ or Nginx 1.18+
- **Operating System:** Windows, Linux, or macOS

### PHP Extensions Required
- `mysqli` - MySQL database connectivity
- `pdo` - PDO database abstraction
- `pdo_mysql` - PDO MySQL driver
- `session` - Session management
- `mbstring` - Multibyte string handling
- `json` - JSON support
- `openssl` - SSL/TLS support (for secure sessions)

### Browser Compatibility
- Google Chrome (latest 2 versions)
- Mozilla Firefox (latest 2 versions)
- Microsoft Edge (latest 2 versions)
- Safari (latest 2 versions)
- Mobile browsers (iOS Safari, Chrome Mobile)

### Software Dependencies
- **Font Awesome:** 6.4.0 (loaded via CDN)
- No other external dependencies required

## Installation Steps

### 1. Download/Clone Project
```bash
git clone <repository-url>
cd travel-agency-system
```

### 2. Database Setup

#### Option A: Using phpMyAdmin
1. Open phpMyAdmin in your browser
2. Create a new database named `travel_agency_db`
3. Click on the database
4. Go to "Import" tab
5. Select `database/travel_agency.sql`
6. Click "Go" to import

#### Option B: Using MySQL Command Line
```bash
mysql -u root -p
CREATE DATABASE travel_agency_db;
USE travel_agency_db;
SOURCE database/travel_agency.sql;
EXIT;
```

#### Option C: Using MySQL Workbench
1. Open MySQL Workbench
2. Connect to your MySQL server
3. Create new database: `travel_agency_db`
4. Right-click on database → "Table Data Import Wizard"
5. Select `database/travel_agency.sql`
6. Follow the import wizard

### 3. Configure Database Connection

Edit `includes/db.php`:

```php
define('DB_HOST', 'localhost');      // Database host
define('DB_USER', 'root');           // Database username
define('DB_PASS', '');               // Database password
define('DB_NAME', 'travel_agency_db'); // Database name
```

**Common Configurations:**

**XAMPP (Windows):**
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'travel_agency_db');
```

**WAMP (Windows):**
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

**Linux (LAMP):**
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'travel_agency_db');
```

### 4. Web Server Configuration

#### Apache (XAMPP/WAMP)
1. Copy project folder to:
   - **XAMPP:** `C:\xampp\htdocs\`
   - **WAMP:** `C:\wamp64\www\`
2. Access via: `http://localhost/travel-agency-system/`

#### Apache Virtual Host (Linux)
Create `/etc/apache2/sites-available/travel-agency.conf`:

```apache
<VirtualHost *:80>
    ServerName travel-agency.local
    DocumentRoot /var/www/travel-agency-system
    
    <Directory /var/www/travel-agency-system>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Enable site:
```bash
sudo a2ensite travel-agency.conf
sudo systemctl restart apache2
```

#### Nginx Configuration
Create `/etc/nginx/sites-available/travel-agency`:

```nginx
server {
    listen 80;
    server_name travel-agency.local;
    root /var/www/travel-agency-system;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/travel-agency /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 5. File Permissions (Linux/Mac)

```bash
# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Make sure PHP can write to session directory
chmod 777 /var/lib/php/sessions
```

### 6. PHP Configuration

Edit `php.ini` if needed:

```ini
; Enable sessions
session.save_handler = files
session.save_path = "/tmp"

; Increase upload limits (if needed)
upload_max_filesize = 10M
post_max_size = 10M

; Error reporting (for development)
display_errors = On
error_reporting = E_ALL

; For production, use:
; display_errors = Off
; error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
```

### 7. Test Installation

1. Open browser: `http://localhost/travel-agency-system/`
2. You should be redirected to login page
3. Try registering a new account
4. Login with admin credentials:
   - Email: `admin@travelagency.com`
   - Password: `admin123`

## Default Accounts

### Admin Account
- **Email:** admin@travelagency.com
- **Password:** admin123
- **Type:** Admin

**Note:** Default password is hashed. If login fails, you may need to:
1. Reset password directly in database, or
2. Create new admin account through registration and manually update user_type in database

### Creating Admin Account Manually

```sql
INSERT INTO users (username, email, password, full_name, user_type) 
VALUES ('admin', 'admin@travelagency.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin');
```

## Troubleshooting

### Database Connection Error
**Error:** "Connection failed: Access denied"
- Check username and password in `includes/db.php`
- Verify MySQL service is running
- Check user permissions in MySQL

**Error:** "Unknown database 'travel_agency_db'"
- Create database: `CREATE DATABASE travel_agency_db;`
- Import SQL file again

### Session Errors
**Error:** "Warning: session_start()"
- Check PHP session configuration
- Verify session directory exists and is writable
- Check file permissions

### Page Not Found (404)
- Verify web server is running
- Check document root configuration
- Ensure `.htaccess` is enabled (Apache)
- Check file paths in includes

### CSS/JS Not Loading
- Check file paths in HTML
- Verify `assets/` folder exists
- Check browser console for 404 errors
- Clear browser cache

### Form Submission Issues
- Check PHP error logs
- Verify form action paths
- Check database connection
- Ensure required fields are filled

## Development vs Production

### Development Settings
- Error display: ON
- Detailed error reporting
- Debug mode enabled

### Production Settings
- Error display: OFF
- Log errors to file
- Disable debug features
- Use HTTPS
- Set secure session cookies

## Security Checklist

- [ ] Change default admin password
- [ ] Use strong database passwords
- [ ] Enable HTTPS in production
- [ ] Set secure session cookies
- [ ] Validate all user inputs
- [ ] Use prepared statements (already implemented)
- [ ] Regular database backups
- [ ] Keep PHP and MySQL updated
- [ ] Restrict file permissions
- [ ] Use environment variables for sensitive data

## Support

For installation issues:
1. Check PHP and MySQL versions
2. Review error logs
3. Verify database connection
4. Check file permissions
5. Review web server configuration

## Additional Notes

- The system uses prepared statements to prevent SQL injection
- Passwords are hashed using PHP's `password_hash()`
- Sessions are used for authentication
- No external frameworks are required
- All code is commented for easy understanding

