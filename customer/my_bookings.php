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

<<<<<<< HEAD
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
=======
// Get all bookings using prepared statements
$stmt = $conn->prepare("SELECT fb.*, f.flight_number, f.airline, f.origin, f.destination, f.departure_date FROM flight_bookings fb JOIN flights f ON fb.flight_id = f.id WHERE fb.user_id = ? ORDER BY fb.booking_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$flight_bookings = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare("SELECT hr.*, h.name, h.city FROM hotel_reservations hr JOIN hotels h ON hr.hotel_id = h.id WHERE hr.user_id = ? ORDER BY hr.booking_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$hotel_bookings = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare("SELECT tb.*, tp.title, tp.destination FROM tour_bookings tb JOIN tour_packages tp ON tb.package_id = tp.id WHERE tb.user_id = ? ORDER BY tb.booking_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tour_bookings = $stmt->get_result();
$stmt->close();

include '../includes/header.php';
?>
<main class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark p-6 lg:p-8">
    <div class="max-w-7xl mx-auto space-y-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">My Bookings</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-1">View and manage all your bookings in one place.</p>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="bg-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-50 dark:bg-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-900/10 border border-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-200 dark:border-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-900/20 text-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-700 dark:text-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-400 px-4 py-3 rounded-lg text-sm">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="flex gap-2 border-b border-slate-200 dark:border-slate-800">
            <button onclick="showTab('flights')" class="tab-btn px-6 py-3 text-sm font-medium border-b-2 border-primary text-primary transition-colors">
                Flights
            </button>
            <button onclick="showTab('hotels')" class="tab-btn px-6 py-3 text-sm font-medium border-b-2 border-transparent text-slate-600 dark:text-slate-400 hover:text-primary transition-colors">
                Hotels
            </button>
            <button onclick="showTab('tours')" class="tab-btn px-6 py-3 text-sm font-medium border-b-2 border-transparent text-slate-600 dark:text-slate-400 hover:text-primary transition-colors">
                Tours
            </button>
        </div>
        
        <div id="flights-tab" class="tab-content">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Flight Bookings</h3>
            <div class="space-y-4">
                <?php 
                $has_flights = false;
                while ($booking = $flight_bookings->fetch_assoc()): 
                    $has_flights = true;
                ?>
                <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 hover:shadow-md transition-shadow">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-lg font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($booking['airline']); ?> - <?php echo htmlspecialchars($booking['flight_number']); ?></h4>
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $booking['status'] === 'confirmed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : ($booking['status'] === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'); ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                            <div class="space-y-1 text-sm text-slate-600 dark:text-slate-400">
                                <p><span class="font-medium">Route:</span> <?php echo htmlspecialchars($booking['origin']); ?> → <?php echo htmlspecialchars($booking['destination']); ?></p>
                                <p><span class="font-medium">Departure:</span> <?php echo date('M d, Y H:i', strtotime($booking['departure_date'])); ?></p>
                                <p><span class="font-medium">Passengers:</span> <?php echo $booking['passengers']; ?></p>
                                <p class="text-lg font-bold text-slate-900 dark:text-white mt-2">Total: $<?php echo number_format($booking['total_price'], 2); ?></p>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <?php if ($booking['status'] !== 'cancelled'): ?>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                    <input type="hidden" name="booking_type" value="flight">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" name="cancel_booking" class="w-full px-4 py-2 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg text-sm font-medium transition-colors">
                                        Cancel Booking
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; 
                if (!$has_flights): ?>
                <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-100 dark:border-slate-800 p-8 text-center">
                    <span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600 mb-4">flight</span>
                    <p class="text-slate-500 dark:text-slate-400">No flight bookings yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div id="hotels-tab" class="tab-content hidden">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Hotel Reservations</h3>
            <div class="space-y-4">
                <?php 
                $has_hotels = false;
                while ($booking = $hotel_bookings->fetch_assoc()): 
                    $has_hotels = true;
                ?>
                <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 hover:shadow-md transition-shadow">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-lg font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($booking['name']); ?></h4>
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $booking['status'] === 'confirmed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : ($booking['status'] === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'); ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                            <div class="space-y-1 text-sm text-slate-600 dark:text-slate-400">
                                <p><span class="font-medium">Location:</span> <?php echo htmlspecialchars($booking['city']); ?></p>
                                <p><span class="font-medium">Check-in:</span> <?php echo date('M d, Y', strtotime($booking['check_in'])); ?></p>
                                <p><span class="font-medium">Check-out:</span> <?php echo date('M d, Y', strtotime($booking['check_out'])); ?></p>
                                <p><span class="font-medium">Guests:</span> <?php echo $booking['guests']; ?> | <span class="font-medium">Rooms:</span> <?php echo $booking['rooms']; ?></p>
                                <p class="text-lg font-bold text-slate-900 dark:text-white mt-2">Total: $<?php echo number_format($booking['total_price'], 2); ?></p>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <?php if ($booking['status'] !== 'cancelled'): ?>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                    <input type="hidden" name="booking_type" value="hotel">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" name="cancel_booking" class="w-full px-4 py-2 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg text-sm font-medium transition-colors">
                                        Cancel Booking
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; 
                if (!$has_hotels): ?>
                <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-100 dark:border-slate-800 p-8 text-center">
                    <span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600 mb-4">hotel</span>
                    <p class="text-slate-500 dark:text-slate-400">No hotel reservations yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div id="tours-tab" class="tab-content hidden">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Tour Package Bookings</h3>
            <div class="space-y-4">
                <?php 
                $has_tours = false;
                while ($booking = $tour_bookings->fetch_assoc()): 
                    $has_tours = true;
                ?>
                <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 hover:shadow-md transition-shadow">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-lg font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($booking['title']); ?></h4>
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $booking['status'] === 'confirmed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : ($booking['status'] === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'); ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                            <div class="space-y-1 text-sm text-slate-600 dark:text-slate-400">
                                <p><span class="font-medium">Destination:</span> <?php echo htmlspecialchars($booking['destination']); ?></p>
                                <p><span class="font-medium">Travel Date:</span> <?php echo date('M d, Y', strtotime($booking['travel_date'])); ?></p>
                                <p><span class="font-medium">Travelers:</span> <?php echo $booking['travelers']; ?></p>
                                <p class="text-lg font-bold text-slate-900 dark:text-white mt-2">Total: $<?php echo number_format($booking['total_price'], 2); ?></p>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <?php if ($booking['status'] !== 'cancelled'): ?>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                    <input type="hidden" name="booking_type" value="tour">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" name="cancel_booking" class="w-full px-4 py-2 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg text-sm font-medium transition-colors">
                                        Cancel Booking
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; 
                if (!$has_tours): ?>
                <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-100 dark:border-slate-800 p-8 text-center">
                    <span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600 mb-4">luggage</span>
                    <p class="text-slate-500 dark:text-slate-400">No tour package bookings yet.</p>
                </div>
                <?php endif; ?>
            </div>
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
        </div>
    </div>
</main>

<script>
function showTab(tab) {
<<<<<<< HEAD
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    document.getElementById(tab + '-tab').classList.add('active');
    event.target.classList.add('active');
=======
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('border-primary', 'text-primary');
        btn.classList.add('border-transparent', 'text-slate-600', 'dark:text-slate-400');
    });
    
    // Show selected tab
    document.getElementById(tab + '-tab').classList.remove('hidden');
    
    // Add active class to clicked button
    event.target.classList.add('border-primary', 'text-primary');
    event.target.classList.remove('border-transparent', 'text-slate-600', 'dark:text-slate-400');
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
}
</script>
<?php include '../includes/footer.php'; ?>

