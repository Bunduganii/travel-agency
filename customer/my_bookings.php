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

// Check for payment success
if (isset($_GET['payment']) && $_GET['payment'] === 'success') {
    $booking_type = $_GET['type'] ?? 'booking';
    $message = ucfirst($booking_type) . ' payment completed successfully!';
    $message_type = 'success';
}

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

// Get search parameter
$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? 'all';
$filter_type = $_GET['type'] ?? 'all';

// Build flight bookings query
$flight_where = ["fb.user_id = ?"];
$flight_types = "i";
$flight_params = [$user_id];

if ($search && ($filter_type === 'all' || $filter_type === 'flight')) {
    $flight_where[] = "(f.flight_number LIKE ? OR f.airline LIKE ? OR f.origin LIKE ? OR f.destination LIKE ?)";
    $search_param = "%$search%";
    $flight_types .= 'ssss';
    $flight_params[] = $search_param;
    $flight_params[] = $search_param;
    $flight_params[] = $search_param;
    $flight_params[] = $search_param;
}

if ($filter_status !== 'all' && ($filter_type === 'all' || $filter_type === 'flight')) {
    $flight_where[] = "fb.status = ?";
    $flight_types .= 's';
    $flight_params[] = $filter_status;
}

$flight_query = "SELECT fb.*, f.flight_number, f.airline, f.origin, f.destination, f.departure_date FROM flight_bookings fb JOIN flights f ON fb.flight_id = f.id WHERE " . implode(" AND ", $flight_where) . " ORDER BY fb.booking_date DESC";

$stmt = $conn->prepare($flight_query);
$stmt->bind_param($flight_types, ...$flight_params);
$stmt->execute();
$flight_bookings = $stmt->get_result();
$stmt->close();

// Build hotel bookings query
$hotel_where = ["hr.user_id = ?"];
$hotel_types = "i";
$hotel_params = [$user_id];

if ($search && ($filter_type === 'all' || $filter_type === 'hotel')) {
    $hotel_where[] = "(h.name LIKE ? OR h.city LIKE ? OR h.location LIKE ?)";
    $search_param = "%$search%";
    $hotel_types .= 'sss';
    $hotel_params[] = $search_param;
    $hotel_params[] = $search_param;
    $hotel_params[] = $search_param;
}

if ($filter_status !== 'all' && ($filter_type === 'all' || $filter_type === 'hotel')) {
    $hotel_where[] = "hr.status = ?";
    $hotel_types .= 's';
    $hotel_params[] = $filter_status;
}

$hotel_query = "SELECT hr.*, h.name, h.city FROM hotel_reservations hr JOIN hotels h ON hr.hotel_id = h.id WHERE " . implode(" AND ", $hotel_where) . " ORDER BY hr.booking_date DESC";

$stmt = $conn->prepare($hotel_query);
$stmt->bind_param($hotel_types, ...$hotel_params);
$stmt->execute();
$hotel_bookings = $stmt->get_result();
$stmt->close();

// Build tour bookings query
$tour_where = ["tb.user_id = ?"];
$tour_types = "i";
$tour_params = [$user_id];

if ($search && ($filter_type === 'all' || $filter_type === 'tour')) {
    $tour_where[] = "(tp.title LIKE ? OR tp.destination LIKE ?)";
    $search_param = "%$search%";
    $tour_types .= 'ss';
    $tour_params[] = $search_param;
    $tour_params[] = $search_param;
}

if ($filter_status !== 'all' && ($filter_type === 'all' || $filter_type === 'tour')) {
    $tour_where[] = "tb.status = ?";
    $tour_types .= 's';
    $tour_params[] = $filter_status;
}

$tour_query = "SELECT tb.*, tp.title, tp.destination FROM tour_bookings tb JOIN tour_packages tp ON tb.package_id = tp.id WHERE " . implode(" AND ", $tour_where) . " ORDER BY tb.booking_date DESC";

$stmt = $conn->prepare($tour_query);
$stmt->bind_param($tour_types, ...$tour_params);
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

        <!-- Search and Filters -->
        <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label for="booking_search" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Search Bookings</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">search</span>
                        <input type="text" id="booking_search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by flight number, airline, hotel name, destination..." class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>
                <div class="md:w-40">
                    <label for="booking_type_filter" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Type</label>
                    <select id="booking_type_filter" name="type" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="all" <?php echo $filter_type === 'all' ? 'selected' : ''; ?>>All Types</option>
                        <option value="flight" <?php echo $filter_type === 'flight' ? 'selected' : ''; ?>>Flights</option>
                        <option value="hotel" <?php echo $filter_type === 'hotel' ? 'selected' : ''; ?>>Hotels</option>
                        <option value="tour" <?php echo $filter_type === 'tour' ? 'selected' : ''; ?>>Tours</option>
                    </select>
                </div>
                <div class="md:w-40">
                    <label for="booking_status_filter" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Status</label>
                    <select id="booking_status_filter" name="status" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $filter_status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="cancelled" <?php echo $filter_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors">
                        <span class="material-symbols-outlined text-[18px] align-middle">search</span>
                        Search
                    </button>
                    <?php if ($search || $filter_type !== 'all' || $filter_status !== 'all'): ?>
                        <a href="my_bookings.php" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
                            Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <?php if ($message): ?>
            <div class="bg-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-50 dark:bg-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-900/10 border border-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-200 dark:border-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-900/20 text-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-700 dark:text-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-400 px-4 py-3 rounded-lg text-sm">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="flex gap-2 border-b border-slate-200 dark:border-slate-800">
            <button onclick="showTab('flights')" class="tab-btn px-6 py-3 text-sm font-medium border-b-2 <?php echo ($filter_type === 'all' || $filter_type === 'flight') ? 'border-primary text-primary' : 'border-transparent text-slate-600 dark:text-slate-400'; ?> hover:text-primary transition-colors">
                Flights
            </button>
            <button onclick="showTab('hotels')" class="tab-btn px-6 py-3 text-sm font-medium border-b-2 <?php echo $filter_type === 'hotel' ? 'border-primary text-primary' : 'border-transparent text-slate-600 dark:text-slate-400'; ?> hover:text-primary transition-colors">
                Hotels
            </button>
            <button onclick="showTab('tours')" class="tab-btn px-6 py-3 text-sm font-medium border-b-2 <?php echo $filter_type === 'tour' ? 'border-primary text-primary' : 'border-transparent text-slate-600 dark:text-slate-400'; ?> hover:text-primary transition-colors">
                Tours
            </button>
        </div>
        
        <div id="flights-tab" class="tab-content <?php echo ($filter_type === 'all' || $filter_type === 'flight') ? '' : 'hidden'; ?>">
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
        
        <div id="hotels-tab" class="tab-content <?php echo $filter_type === 'hotel' ? '' : 'hidden'; ?>">
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
        
        <div id="tours-tab" class="tab-content <?php echo $filter_type === 'tour' ? '' : 'hidden'; ?>">
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
    // Update filter type and reload
    const url = new URL(window.location.href);
    const filterType = tab === 'flights' ? 'flight' : (tab === 'hotels' ? 'hotel' : 'tour');
    url.searchParams.set('type', filterType);
    
    // Keep search and status filters
    const search = url.searchParams.get('search');
    const status = url.searchParams.get('status');
    
    // Reload with new filter
    window.location.href = url.toString();
}
</script>

<!-- Toast Notification -->
<?php if ($message && $message_type === 'success'): ?>
<div id="paymentToast" class="fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 animate-slide-in">
    <span class="material-symbols-outlined text-2xl">check_circle</span>
    <div>
        <p class="font-bold">Payment Successful!</p>
        <p class="text-sm opacity-90"><?php echo htmlspecialchars($message); ?></p>
    </div>
    <button onclick="closeToast()" class="ml-4 text-white hover:text-gray-200">
        <span class="material-symbols-outlined">close</span>
    </button>
</div>
<script>
function closeToast() {
    const toast = document.getElementById('paymentToast');
    if (toast) {
        toast.style.display = 'none';
    }
}
setTimeout(closeToast, 5000);
</script>
<style>
@keyframes slide-in {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
.animate-slide-in {
    animation: slide-in 0.3s ease-out;
}
</style>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

