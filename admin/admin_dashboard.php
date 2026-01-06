<?php
/**
 * Admin Dashboard
 * Main dashboard for admin users
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$page_title = 'Admin Dashboard';

// Get statistics
$stats = [];

// Total Revenue
$result = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'completed'");
$stats['revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Total Bookings
$result = $conn->query("SELECT COUNT(*) as total FROM (
    SELECT id FROM flight_bookings UNION ALL
    SELECT id FROM hotel_reservations UNION ALL
    SELECT id FROM tour_bookings
) as bookings");
$stats['bookings'] = $result->fetch_assoc()['total'] ?? 0;

// Active Users
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE user_type = 'customer'");
$stats['users'] = $result->fetch_assoc()['total'] ?? 0;

// Pending Inquiries
$result = $conn->query("SELECT COUNT(*) as total FROM feedback WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stats['inquiries'] = $result->fetch_assoc()['total'] ?? 0;

include '../includes/header.php';
?>
<main class="admin-dashboard">
    <div class="dashboard-overview">
        <div class="overview-header">
            <div>
                <h1>Dashboard Overview</h1>
                <p>Welcome back, here's what's happening today.</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline">
                    <i class="fas fa-cloud-download-alt"></i> Export Report
                </button>
                <a href="../customer/book_flight.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Booking
                </a>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <h3>$<?php echo number_format($stats['revenue'], 2); ?></h3>
                    <p>Total Revenue</p>
                    <span class="stat-change positive">+5% <i class="fas fa-arrow-up"></i></span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['bookings']; ?></h3>
                    <p>Total Bookings</p>
                    <span class="stat-change positive">+12% <i class="fas fa-arrow-up"></i></span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['users']; ?></h3>
                    <p>Active Users</p>
                    <span class="stat-change positive">+2% <i class="fas fa-arrow-up"></i></span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['inquiries']; ?></h3>
                    <p>Pending Inquiries</p>
                    <span class="stat-change negative">-1% <i class="fas fa-arrow-down"></i></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="dashboard-content">
        <div class="revenue-analytics">
            <div class="section-header">
                <h2>Revenue Analytics</h2>
                <select class="select-dropdown">
                    <option>Last 30 Days</option>
                    <option>Last 7 Days</option>
                    <option>Last Year</option>
                </select>
            </div>
            <div class="chart-placeholder">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
        
        <div class="quick-actions-admin">
            <h2>Quick Actions</h2>
            <div class="actions-grid">
                <a href="manage_flights.php" class="action-btn">
                    <i class="fas fa-plus"></i>
                    <span>Add Booking</span>
                </a>
                <a href="manage_flights.php" class="action-btn">
                    <i class="fas fa-plane"></i>
                    <span>Manage Flights</span>
                </a>
                <a href="#" class="action-btn">
                    <i class="fas fa-shield-alt"></i>
                    <span>Verify User</span>
                </a>
                <a href="manage_tours.php" class="action-btn">
                    <i class="fas fa-suitcase"></i>
                    <span>Create Package</span>
                </a>
            </div>
        </div>
    </div>
    
    <div class="system-alerts">
        <h2>System Alerts</h2>
        <div class="alert-card error">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>3 Flight Schedule Changes</strong>
                <p>Flights AA102, BA440 require attention.</p>
            </div>
        </div>
        <div class="alert-card warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>5 Unresolved Tickets</strong>
                <p>Support queue is higher than average.</p>
            </div>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>

