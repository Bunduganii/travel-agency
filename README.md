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

<<<<<<< HEAD
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Icons:** Font Awesome 6.4.0
- **No Frameworks:** Pure PHP, no frameworks used
=======
- **Frontend:** HTML5, Tailwind CSS, JavaScript (Vanilla)
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Icons:** Material Symbols
- **Styling:** Tailwind CSS (CDN)
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd

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

### Step 2: Database Setup

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

3. **Database Configuration:**
   - Open `includes/db.php`
   - Update database credentials:
   ```php
   define('DB_HOST', 'localhost');  // Your database host
   define('DB_USER', 'root');       // Your database username
   define('DB_PASS', '');           // Your database password
   define('DB_NAME', 'travel_agency_db'); // Your database name
   ```

### Step 3: Web Server Configuration

#### For Apache (XAMPP/WAMP):
1. Copy the project folder to `htdocs` (XAMPP) or `www` (WAMP)
2. Access via: `http://localhost/travel-agency-system/`

#### For Nginx:
1. Configure virtual host pointing to project directory
2. Ensure PHP-FPM is configured

### Step 4: File Permissions
```bash
# Set proper permissions (Linux/Mac)
chmod 755 -R .
chmod 644 includes/db.php
```

<<<<<<< HEAD
### Step 5: Initial Login

**Admin Account:**
- Email: `admin@travelagency.com`
- Password: `admin123`

**Note:** Default admin password is hashed. You may need to reset it or use the registration system to create a new admin account.
=======
### Step 5: Create Admin Account

After setting up the database, you need to create an admin account. You can do this by:

1. **Using the registration page** - Register a new account and manually update the `user_type` field in the database to `'admin'`
2. **Direct SQL insertion** - Insert an admin user directly into the `users` table with `user_type = 'admin'`

**Note:** Make sure to hash passwords using PHP's `password_hash()` function before inserting into the database.
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd

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
<<<<<<< HEAD
- **Animations:** Smooth CSS and JavaScript animations
- **Modern UI:** Clean, professional interface
=======
- **Modern UI:** Clean, professional interface built with Tailwind CSS
- **Material Symbols:** Modern icon library for consistent UI elements
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
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

<<<<<<< HEAD
- Clean, readable code
- Well-commented functions
- Consistent naming conventions
- Modular file structure
- Separation of concerns
=======
- Clean, readable PHP code
- Consistent naming conventions (camelCase for variables, snake_case for database)
- Modular file structure with includes
- Separation of concerns (database, authentication, presentation)
- Prepared statements for all database queries
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd

## 🐛 Troubleshooting

### Database Connection Error:
- Check database credentials in `includes/db.php`
- Ensure MySQL service is running
- Verify database exists

### Session Issues:
- Check PHP session configuration
- Ensure `session_start()` is called
- Check file permissions

### Page Not Found:
- Verify .htaccess configuration (if using Apache)
- Check file paths and includes
- Ensure web server is configured correctly

## 📄 License

This project is created for educational purposes as a Final Year Project.

<<<<<<< HEAD
## 👨‍💻 Developer Notes

- Code is written for intermediate-level understanding
- No advanced frameworks or libraries used
- Suitable for learning and demonstration
- Easy to modify and extend
=======
## 👨‍💻 Project Structure

This project follows a simple MVC-like structure:
- **Presentation Layer:** PHP files in root, `admin/`, and `customer/` directories
- **Business Logic:** PHP logic embedded in presentation files
- **Data Layer:** `includes/db.php` for database connections
- **Shared Components:** `includes/` folder for headers, footers, and authentication
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd

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

<<<<<<< HEAD
- Font Awesome for icons
=======
- Material Symbols for icons
- Tailwind CSS for styling framework
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
- Unsplash for placeholder images
- PHP and MySQL communities

---

**Note:** This is a demonstration system. For production use, additional security measures, error handling, and testing should be implemented.

