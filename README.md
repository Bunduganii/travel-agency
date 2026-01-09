# Travel Agency Booking and Reservation System

A comprehensive web-based travel agency booking system built with HTML, CSS, JavaScript, and PHP (MySQL). This is an intermediate-level final year project designed to demonstrate full-stack web development skills.

## 🎯 Project Overview

This system allows travel agencies to manage bookings for flights, hotels, and tour packages. It includes separate interfaces for administrators and customers, with features for booking management, payment processing, and feedback collection.

## ✨ Features

### Customer Features
- User registration and authentication
- Flight search and booking
- Hotel search and reservation
- Tour package browsing and booking
- View and manage bookings
- Cancel bookings
- Payment processing (multiple payment methods)
- Submit feedback

### Admin Features
- Admin dashboard with statistics
- Manage flights (add, edit, delete)
- Manage hotels (add, edit, delete)
- Manage tour packages (add, edit, delete)
- View all bookings
- View customer feedback
- System alerts and notifications

### Design Features
- Modern, clean UI design
- Responsive layout (desktop and mobile)
- CSS animations and transitions
- JavaScript interactivity
- Smooth page transitions
- Form validation
- Loading states

## 🛠️ Technologies Used

- **Frontend:** HTML5, Tailwind CSS, JavaScript (Vanilla)
- **Backend:** PHP 7.4+ (see `.php-version` file)
- **Database:** MySQL 5.7+ or MariaDB 10.2+
- **Icons:** Material Symbols
- **Styling:** Tailwind CSS (CDN)
- **Web Server:** Apache (with .htaccess) or Nginx

## 📋 Requirements

### Server Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB 10.2+)
- Apache/Nginx web server
- PHP Extensions:
  - mysqli
  - pdo_mysql
  - session
  - mbstring

### Browser Requirements
- Modern browsers (Chrome, Firefox, Safari, Edge)
- JavaScript enabled
- CSS3 support

## 📦 Installation

### Step 1: Clone or Download the Project
```bash
git clone https://github.com/yourusername/travel-agency-system.git
cd travel-agency-system
```

### Step 1.5: Check PHP Requirements ⚠️ IMPORTANT

**Always run this first!** This ensures your PHP environment is compatible with the application.

**Option 1: Run the requirements checker (RECOMMENDED)**
```bash
php requirements.php
```

**This will automatically check:**
- ✅ PHP version (requires >= 7.4.0)
- ✅ Required extensions (mysqli, session, mbstring, json)
- ✅ Directory permissions
- ✅ PHP configuration settings
- ✅ Optional but recommended extensions

**Expected successful output:**
```
=== PHP Requirements Check ===

PHP Version: 7.4.33 (Required: >= 7.4.0)
✓ PHP version is compatible

Checking Required Extensions:
✓ mysqli - MySQLi extension for database connectivity
✓ session - Session extension for user authentication
✓ mbstring - Multibyte String extension for string handling
✓ json - JSON extension for data encoding/decoding

=== Summary ===
✓ All required extensions are loaded
✓ PHP environment is ready for this application
```

**If you see ✗ (errors):**
- See [Troubleshooting](#-troubleshooting) section below
- Most common: PHP version too old or missing extensions

**Option 2: Manual check**
```bash
php -v  # Should show PHP 7.4.0 or higher
php -m  # List all loaded extensions (should include mysqli, mbstring, session, json)
```

**If PHP version is too old:**
- **Windows (XAMPP):** Download latest XAMPP from https://www.apachefriends.org/
- **Linux:** `sudo apt-get install php7.4` or use phpbrew
- **Mac:** `brew install php@7.4` or use phpbrew
- **cPanel/Hosting:** Contact your hosting provider to upgrade PHP version

**If extensions are missing:**
- **Windows (XAMPP):** Uncomment extension in `php.ini` file
- **Linux:** `sudo apt-get install php7.4-mysqli php7.4-mbstring php7.4-session`
- **Mac:** `brew install php@7.4-mysqli php@7.4-mbstring`
- **cPanel/Hosting:** Enable extensions via hosting control panel

### Step 2: Configure Database Connection
1. **Copy the example configuration file:**
   ```bash
   # On Linux/Mac:
   cp includes/db.php.example includes/db.php
   
   # On Windows: Copy db.php.example and rename to db.php
   ```

2. **Edit `includes/db.php`** with your database credentials:
   ```php
   define('DB_HOST', 'localhost');     // Usually 'localhost'
   define('DB_USER', 'root');          // Your MySQL username
   define('DB_PASS', '');              // Your MySQL password (empty if no password)
   define('DB_NAME', 'travel_agency_db'); // Your database name
   ```

### Step 3: Database Setup

1. **Create Database:**
   - Open phpMyAdmin or MySQL command line
   - Create a new database named `travel_agency_db`
   - Or import the SQL file directly

2. **Import Database Schema:**
   ```sql
   -- Option 1: Using phpMyAdmin
   -- Go to phpMyAdmin → Select database → Import → Choose database/travel_agency.sql

   -- Option 2: Using MySQL command line
   mysql -u root -p travel_agency_db < database/travel_agency.sql
   ```

### Step 4: Web Server Configuration

#### For Apache (XAMPP/WAMP):
1. Copy the project folder to `htdocs` (XAMPP) or `www` (WAMP)
2. Access via: `http://localhost/travel-agency-system/`

#### For Nginx:
1. Configure virtual host pointing to project directory
2. Ensure PHP-FPM is configured

### Step 5: Verify Installation
1. **Run PHP requirements check:**
   ```bash
   php requirements.php
   ```
   All checks should pass (show ✓).

2. **Test the application:**
   - Open your browser
   - Navigate to: `http://localhost/travel-agency-system/`
   - You should see the homepage

3. **Test database connection:**
   - Try to register a new account or login
   - If you see database errors, check `includes/db.php` configuration

### Step 6: File Permissions (Linux/Mac only)
```bash
# Set proper permissions
chmod 755 -R .
chmod 644 includes/db.php
chmod 644 .htaccess
```

### Step 7: Login as Admin

**Default Admin Account (already included in database):**
- **Email:** `admin@travelagency.com`
- **Username:** `admin`
- **Password:** `password`

⚠️ **IMPORTANT:** Change the default password after first login for security!

**Alternative: Create New Admin Account**

1. **Using the registration page** - Register a new account and manually update the `user_type` field in the database to `'admin'`
2. **Direct SQL insertion** - Insert an admin user directly into the `users` table with `user_type = 'admin'`

**Note:** Make sure to hash passwords using PHP's `password_hash()` function before inserting into the database.

## 📁 Project Structure

```
travel-agency-system/
│
├── index.php                 # Customer dashboard/home page
├── login.php                 # Login page
├── register.php              # Registration page
├── logout.php                # Logout handler
│
├── admin/                    # Admin section
│   ├── admin_dashboard.php   # Admin main dashboard
│   ├── manage_flights.php    # Flight management
│   ├── manage_hotels.php     # Hotel management
│   └── manage_tours.php      # Tour package management
│
├── customer/                 # Customer section
│   ├── book_flight.php       # Flight booking
│   ├── reserve_hotel.php     # Hotel reservation
│   ├── tour_packages.php     # Tour packages
│   ├── my_bookings.php       # View bookings
│   ├── payment.php           # Payment processing
│   └── feedback.php          # Feedback form
│
├── includes/                 # Shared includes
│   ├── db.php                # Database connection
│   ├── auth.php               # Authentication functions
│   ├── header.php             # Common header
│   └── footer.php             # Common footer
│
├── assets/                   # Static assets
│   ├── css/
│   │   ├── style.css         # Main stylesheet
│   │   └── animations.css    # Animation styles
│   ├── js/
│   │   ├── main.js           # Main JavaScript
│   │   └── animations.js     # Animation scripts
│   └── images/               # Image assets
│
└── database/
    └── travel_agency.sql     # Database schema
```

## 🗄️ Database Tables

The system uses the following main tables:

- **users** - User accounts (admin and customers)
- **flights** - Flight information
- **hotels** - Hotel information
- **tour_packages** - Tour package details
- **flight_bookings** - Flight reservations
- **hotel_reservations** - Hotel bookings
- **tour_bookings** - Tour package bookings
- **payments** - Payment transactions
- **feedback** - Customer feedback

See `database/travel_agency.sql` for complete schema.

## 💳 Payment Methods

The system supports the following payment methods:
- Credit/Debit Cards (Visa, Mastercard, Amex)
- Zaad (Mobile Money)
- Edahab (Mobile Money)
- Waafi (Mobile Money)
- Dahab Plus (Mobile Money)

**Note:** This is a demo system. No actual payment processing is implemented. All payments are simulated.

## 🎨 Design Features

- **Responsive Design:** Works on desktop, tablet, and mobile devices
- **Modern UI:** Clean, professional interface built with Tailwind CSS
- **Material Symbols:** Modern icon library for consistent UI elements
- **User-Friendly:** Intuitive navigation and forms
- **Accessibility:** Semantic HTML and proper form labels

## 🔒 Security Features

- Password hashing using PHP `password_hash()`
- SQL injection prevention using prepared statements
- Session-based authentication
- Input validation and sanitization
- XSS protection

## 🚀 Usage

### For Customers:
1. Register a new account or login
2. Browse flights, hotels, or tour packages
3. Make bookings
4. Complete payment
5. View and manage bookings

### For Admins:
1. Login with admin credentials
2. Access admin dashboard
3. Manage flights, hotels, and tours
4. View bookings and feedback
5. Monitor system statistics

## 📝 Code Style

- Clean, readable PHP code
- Consistent naming conventions (camelCase for variables, snake_case for database)
- Modular file structure with includes
- Separation of concerns (database, authentication, presentation)
- Prepared statements for all database queries

## 🐛 Troubleshooting

### PHP Version Error / "Unrecognized PHP":
**Problem:** "Fatal error: Unsupported version" or "Call to undefined function"

**Solution:**
1. Check your PHP version: `php -v` or run `php requirements.php`
2. **If PHP < 7.4:**
   - **XAMPP:** Download latest XAMPP from https://www.apachefriends.org/
   - **WAMP:** Update to latest version with PHP 7.4+
   - **Linux:** `sudo apt-get install php7.4` or `sudo apt-get install php8.0`
   - **Mac:** `brew install php@7.4` or update via Homebrew
   - **Hosting/cPanel:** Change PHP version in hosting control panel (Select PHP Version)
3. Restart Apache/web server after PHP update
4. Run `php requirements.php` again to verify

### Missing PHP Extensions:
**Problem:** "Call to undefined function mysqli_connect()" or similar

**Solution:**
1. Run `php requirements.php` to see which extensions are missing
2. Enable extensions in `php.ini`:
   ```ini
   extension=mysqli
   extension=mbstring
   extension=session
   extension=json
   ```
3. Restart Apache/web server
4. Verify: `php -m | grep mysqli`

### Database Connection Error:
- Check database credentials in `includes/db.php`
- Ensure MySQL service is running (green in XAMPP Control Panel)
- Verify database `travel_agency_db` exists in phpMyAdmin
- Test connection: `php -r "new mysqli('localhost', 'root', '', 'travel_agency_db');"`

### Session Issues:
- Check PHP session configuration in `php.ini`
- Ensure `session_start()` is called in files
- Check file permissions (755 for directories, 644 for files)
- Verify session directory is writable

### Page Not Found (404):
- Verify `.htaccess` file exists in project root
- If using Apache: Check `mod_rewrite` is enabled
- If using Nginx: Configure rewrite rules separately
- Check file paths are correct
- Ensure project is in correct directory (`htdocs` for XAMPP, `www` for WAMP)

### Composer/Version Recognition Issues:
If using Composer or version managers:
- **For Heroku:** Use `composer.json` (already included)
- **For phpbrew:** Use `.php-version` file (already included)
- **For cPanel:** Contact hosting provider to set PHP version to 7.4+

### Git/Deployment Issues:
**When deploying to GitHub and downloading:**
1. Don't commit `includes/db.php` (it's in `.gitignore`)
2. After download: Copy `includes/db.php.example` to `includes/db.php`
3. Run `php requirements.php` to verify environment
4. Configure database credentials in `includes/db.php`

## 📄 License

This project is created for educational purposes as a Final Year Project.

## 👨‍💻 Project Structure

This project follows a simple MVC-like structure:
- **Presentation Layer:** PHP files in root, `admin/`, and `customer/` directories
- **Business Logic:** PHP logic embedded in presentation files
- **Data Layer:** `includes/db.php` for database connections
- **Shared Components:** `includes/` folder for headers, footers, and authentication

## 🔄 Future Enhancements

Potential improvements:
- Email notifications
- PDF booking confirmations
- Advanced search filters
- User profile management
- Booking history export
- Real payment gateway integration
- Multi-language support

## 📞 Support

For issues or questions:
1. Check the troubleshooting section
2. Review code comments
3. Check database connection settings
4. Verify file permissions

## 🙏 Acknowledgments

- Material Symbols for icons
- Tailwind CSS for styling framework
- Unsplash for placeholder images
- PHP and MySQL communities

---

**Note:** This is a demonstration system. For production use, additional security measures, error handling, and testing should be implemented.

