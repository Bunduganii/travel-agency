<?php
/**
 * My Bookings Page
 * Customer page to view and manage bookings
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireCustomer();

$page_title = 'My Bookings';
$message = '';
$message_type = '';

// Handle cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $booking_type = $_POST['booking_type'];
    $booking_id = intval($_POST['booking_id']);
    $user_id = getUserId();
    
    if ($booking_type === 'flight') {
        $stmt = $conn->prepare("UPDATE flight_bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?");
    } elseif ($booking_type === 'hotel') {
        $stmt = $conn->prepare("UPDATE hotel_reservations SET status = 'cancelled' WHERE id = ? AND user_id = ?");
    } else {
        $stmt = $conn->prepare("UPDATE tour_bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?");
    }
    
    $stmt->bind_param("ii", $booking_id, $user_id);
    if ($stmt->execute()) {
        $message = 'Booking cancelled successfully.';
        $message_type = 'success';
    }
    $stmt->close();
}

$user_id = getUserId();

// Get all bookings
$flight_bookings = $conn->query("SELECT fb.*, f.flight_number, f.airline, f.origin, f.destination, f.departure_date FROM flight_bookings fb JOIN flights f ON fb.flight_id = f.id WHERE fb.user_id = $user_id ORDER BY fb.booking_date DESC");

$hotel_bookings = $conn->query("SELECT hr.*, h.name, h.city FROM hotel_reservations hr JOIN hotels h ON hr.hotel_id = h.id WHERE hr.user_id = $user_id ORDER BY hr.booking_date DESC");

$tour_bookings = $conn->query("SELECT tb.*, tp.title, tp.destination FROM tour_bookings tb JOIN tour_packages tp ON tb.package_id = tp.id WHERE tb.user_id = $user_id ORDER BY tb.booking_date DESC");

include '../includes/header.php';
?>
<main class="bookings-page">
    <h1>My Bookings</h1>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> slide-in">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <div class="bookings-tabs">
        <button class="tab-btn active" onclick="showTab('flights')">Flights</button>
        <button class="tab-btn" onclick="showTab('hotels')">Hotels</button>
        <button class="tab-btn" onclick="showTab('tours')">Tours</button>
    </div>
    
    <div id="flights-tab" class="tab-content active">
        <h2>Flight Bookings</h2>
        <div class="bookings-list">
            <?php while ($booking = $flight_bookings->fetch_assoc()): ?>
            <div class="booking-item">
                <div class="booking-info">
                    <div class="booking-header">
                        <h3><?php echo htmlspecialchars($booking['airline']); ?> - <?php echo htmlspecialchars($booking['flight_number']); ?></h3>
                        <span class="status-badge <?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span>
                    </div>
                    <p><?php echo htmlspecialchars($booking['origin']); ?> → <?php echo htmlspecialchars($booking['destination']); ?></p>
                    <p>Departure: <?php echo date('M d, Y H:i', strtotime($booking['departure_date'])); ?></p>
                    <p>Passengers: <?php echo $booking['passengers']; ?></p>
                    <p class="booking-price">Total: $<?php echo number_format($booking['total_price'], 2); ?></p>
                </div>
                <div class="booking-actions">
                    <?php if ($booking['status'] !== 'cancelled'): ?>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                            <input type="hidden" name="booking_type" value="flight">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                            <button type="submit" name="cancel_booking" class="btn btn-danger">Cancel</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <div id="hotels-tab" class="tab-content">
        <h2>Hotel Reservations</h2>
        <div class="bookings-list">
            <?php while ($booking = $hotel_bookings->fetch_assoc()): ?>
            <div class="booking-item">
                <div class="booking-info">
                    <div class="booking-header">
                        <h3><?php echo htmlspecialchars($booking['name']); ?></h3>
                        <span class="status-badge <?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span>
                    </div>
                    <p><?php echo htmlspecialchars($booking['city']); ?></p>
                    <p>Check-in: <?php echo date('M d, Y', strtotime($booking['check_in'])); ?></p>
                    <p>Check-out: <?php echo date('M d, Y', strtotime($booking['check_out'])); ?></p>
                    <p>Guests: <?php echo $booking['guests']; ?> | Rooms: <?php echo $booking['rooms']; ?></p>
                    <p class="booking-price">Total: $<?php echo number_format($booking['total_price'], 2); ?></p>
                </div>
                <div class="booking-actions">
                    <?php if ($booking['status'] !== 'cancelled'): ?>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                            <input type="hidden" name="booking_type" value="hotel">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                            <button type="submit" name="cancel_booking" class="btn btn-danger">Cancel</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <div id="tours-tab" class="tab-content">
        <h2>Tour Package Bookings</h2>
        <div class="bookings-list">
            <?php while ($booking = $tour_bookings->fetch_assoc()): ?>
            <div class="booking-item">
                <div class="booking-info">
                    <div class="booking-header">
                        <h3><?php echo htmlspecialchars($booking['title']); ?></h3>
                        <span class="status-badge <?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span>
                    </div>
                    <p><?php echo htmlspecialchars($booking['destination']); ?></p>
                    <p>Travel Date: <?php echo date('M d, Y', strtotime($booking['travel_date'])); ?></p>
                    <p>Travelers: <?php echo $booking['travelers']; ?></p>
                    <p class="booking-price">Total: $<?php echo number_format($booking['total_price'], 2); ?></p>
                </div>
                <div class="booking-actions">
                    <?php if ($booking['status'] !== 'cancelled'): ?>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                            <input type="hidden" name="booking_type" value="tour">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                            <button type="submit" name="cancel_booking" class="btn btn-danger">Cancel</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</main>

<script>
function showTab(tab) {
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    document.getElementById(tab + '-tab').classList.add('active');
    event.target.classList.add('active');
}
</script>
<?php include '../includes/footer.php'; ?>

