<?php
/**
 * Home Page / Customer Dashboard
 * Main landing page for customers
 */
require_once 'includes/db.php';
require_once 'includes/auth.php';

$page_title = 'Dashboard';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Redirect admin to admin dashboard
if (isAdmin()) {
    header('Location: admin/admin_dashboard.php');
    exit();
}

// Get user bookings count
$user_id = getUserId();
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM (
    SELECT id FROM flight_bookings WHERE user_id = ?
    UNION ALL
    SELECT id FROM hotel_reservations WHERE user_id = ?
    UNION ALL
    SELECT id FROM tour_bookings WHERE user_id = ?
) as bookings");
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$bookings_result = $stmt->get_result();
$total_bookings = $bookings_result->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Get next upcoming trip
$stmt = $conn->prepare("
    SELECT * FROM (
        SELECT 'flight' as type, id, booking_date as trip_date, status FROM flight_bookings WHERE user_id = ? AND status = 'confirmed' ORDER BY booking_date ASC LIMIT 1
    ) as flight_bookings
    UNION ALL
    SELECT * FROM (
        SELECT 'hotel' as type, id, check_in as trip_date, status FROM hotel_reservations WHERE user_id = ? AND status = 'confirmed' ORDER BY check_in ASC LIMIT 1
    ) as hotel_reservations
    UNION ALL
    SELECT * FROM (
        SELECT 'tour' as type, id, travel_date as trip_date, status FROM tour_bookings WHERE user_id = ? AND status = 'confirmed' ORDER BY travel_date ASC LIMIT 1
    ) as tour_bookings
    ORDER BY trip_date ASC LIMIT 1
");
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$next_trip = $stmt->get_result()->fetch_assoc();
$stmt->close();

include 'includes/header.php';
?>
<main class="customer-dashboard">
    <div class="dashboard-header">
        <div>
            <h1>Welcome back, <?php echo htmlspecialchars(getUserFullName()); ?>!</h1>
            <p>You have <span class="highlight"><?php echo $total_bookings; ?> upcoming trips</span> scheduled.</p>
        </div>
        <a href="customer/book_flight.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Booking
        </a>
    </div>
    
    <div class="quick-actions">
        <a href="customer/book_flight.php" class="action-card">
            <div class="action-icon blue">
                <i class="fas fa-plane"></i>
            </div>
            <span>Book Flight</span>
        </a>
        
        <a href="customer/reserve_hotel.php" class="action-card">
            <div class="action-icon orange">
                <i class="fas fa-bed"></i>
            </div>
            <span>Book Hotel</span>
        </a>
        
        <a href="customer/my_bookings.php" class="action-card">
            <div class="action-icon purple">
                <i class="fas fa-user"></i>
            </div>
            <span>Profile</span>
        </a>
        
        <a href="customer/feedback.php" class="action-card">
            <div class="action-icon green">
                <i class="fas fa-headset"></i>
            </div>
            <span>Support</span>
        </a>
    </div>
    
    <?php if ($next_trip): ?>
    <div class="next-adventure">
        <div class="adventure-image">
            <span class="status-badge confirmed">Confirmed</span>
        </div>
        <div class="adventure-details">
            <span class="adventure-label">NEXT ADVENTURE</span>
            <h2>Paris, France</h2>
            <div class="adventure-info">
                <span><i class="fas fa-calendar"></i> Oct 15 - Oct 22 (7 Days)</span>
                <span><i class="fas fa-cloud"></i> Forecast: 18°C Partly Cloudy</span>
            </div>
            <div class="adventure-actions">
                <a href="customer/my_bookings.php" class="btn btn-outline">View Itinerary</a>
                <a href="customer/my_bookings.php" class="btn btn-secondary">Manage Booking</a>
            </div>
        </div>
        <div class="adventure-date">
            <span>OCT</span>
            <strong>15</strong>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="upcoming-bookings">
        <h2>Other Upcoming Bookings</h2>
        <div class="bookings-grid">
            <?php
            // Get recent bookings
            $stmt = $conn->prepare("
                SELECT 'flight' as type, id, booking_date, status FROM flight_bookings WHERE user_id = ? ORDER BY booking_date DESC LIMIT 3
            ");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $bookings = $stmt->get_result();
            
            while ($booking = $bookings->fetch_assoc()):
            ?>
            <div class="booking-card">
                <div class="booking-icon">
                    <i class="fas fa-plane"></i>
                </div>
                <div class="booking-info">
                    <h3>New York (JFK)</h3>
                    <span class="status-badge confirmed">CONFIRMED</span>
                    <p>To London (LHR)</p>
                    <p class="booking-details">Delta DL123 • Nov 12 • 08:30 AM</p>
                    <a href="customer/my_bookings.php" class="btn btn-link">Flight Details</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>

