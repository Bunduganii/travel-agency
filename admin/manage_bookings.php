<?php
/**
 * Manage Bookings Page
 * Admin page to view and manage all bookings (flights, hotels, tours)
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$page_title = 'Manage Bookings';
$message = '';
$message_type = '';

// Handle booking actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $booking_id = intval($_POST['booking_id']);
    $booking_type = $_POST['booking_type'];
    $action = $_POST['action'];
    
    if ($action === 'confirm') {
        if ($booking_type === 'flight') {
            $stmt = $conn->prepare("UPDATE flight_bookings SET status = 'confirmed' WHERE id = ?");
        } elseif ($booking_type === 'hotel') {
            $stmt = $conn->prepare("UPDATE hotel_reservations SET status = 'confirmed' WHERE id = ?");
        } else {
            $stmt = $conn->prepare("UPDATE tour_bookings SET status = 'confirmed' WHERE id = ?");
        }
        $stmt->bind_param("i", $booking_id);
        if ($stmt->execute()) {
            $message = 'Booking confirmed successfully!';
            $message_type = 'success';
        }
        $stmt->close();
    } elseif ($action === 'cancel') {
        if ($booking_type === 'flight') {
            $stmt = $conn->prepare("UPDATE flight_bookings SET status = 'cancelled' WHERE id = ?");
        } elseif ($booking_type === 'hotel') {
            $stmt = $conn->prepare("UPDATE hotel_reservations SET status = 'cancelled' WHERE id = ?");
        } else {
            $stmt = $conn->prepare("UPDATE tour_bookings SET status = 'cancelled' WHERE id = ?");
        }
        $stmt->bind_param("i", $booking_id);
        if ($stmt->execute()) {
            $message = 'Booking cancelled successfully!';
            $message_type = 'success';
        }
        $stmt->close();
    }
}

// Get filter parameters
$filter_type = $_GET['type'] ?? 'all';
$filter_status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query for all bookings
$bookings = [];

// Flight bookings
$flight_query = "SELECT 
    'flight' as booking_type,
    fb.id,
    fb.user_id,
    u.full_name as customer_name,
    u.email as customer_email,
    f.flight_number,
    f.airline,
    f.origin,
    f.destination,
    f.departure_date,
    f.arrival_date,
    fb.booking_date,
    fb.status,
    fb.total_price as total_amount,
    fb.passengers
FROM flight_bookings fb
JOIN users u ON fb.user_id = u.id
JOIN flights f ON fb.flight_id = f.id";

$flight_where = [];
$flight_params = [];
$flight_types = '';

if ($filter_status !== 'all') {
    $flight_where[] = "fb.status = ?";
    $flight_params[] = $filter_status;
    $flight_types .= 's';
}

if ($search) {
    $search_param = "%$search%";
    $flight_where[] = "(u.full_name LIKE ? OR u.email LIKE ?)";
    $flight_params[] = $search_param;
    $flight_params[] = $search_param;
    $flight_types .= 'ss';
}

if (!empty($flight_where)) {
    $flight_query .= " WHERE " . implode(" AND ", $flight_where);
    $stmt = $conn->prepare($flight_query);
    if ($flight_types) {
        $stmt->bind_param($flight_types, ...$flight_params);
    }
} else {
    $stmt = $conn->prepare($flight_query);
}
$stmt->execute();
$flight_result = $stmt->get_result();
while ($row = $flight_result->fetch_assoc()) {
    $bookings[] = $row;
}
$stmt->close();

// Hotel bookings
$hotel_query = "SELECT 
    'hotel' as booking_type,
    hr.id,
    hr.user_id,
    u.full_name as customer_name,
    u.email as customer_email,
    h.name as hotel_name,
    h.location,
    hr.check_in,
    hr.check_out,
    hr.booking_date,
    hr.status,
    hr.total_price as total_amount,
    hr.guests
FROM hotel_reservations hr
JOIN users u ON hr.user_id = u.id
JOIN hotels h ON hr.hotel_id = h.id";

$hotel_where = [];
$hotel_params = [];
$hotel_types = '';

if ($filter_status !== 'all') {
    $hotel_where[] = "hr.status = ?";
    $hotel_params[] = $filter_status;
    $hotel_types .= 's';
}

if ($search) {
    $search_param = "%$search%";
    $hotel_where[] = "(u.full_name LIKE ? OR u.email LIKE ?)";
    $hotel_params[] = $search_param;
    $hotel_params[] = $search_param;
    $hotel_types .= 'ss';
}

if (!empty($hotel_where)) {
    $hotel_query .= " WHERE " . implode(" AND ", $hotel_where);
    $stmt = $conn->prepare($hotel_query);
    if ($hotel_types) {
        $stmt->bind_param($hotel_types, ...$hotel_params);
    }
} else {
    $stmt = $conn->prepare($hotel_query);
}
$stmt->execute();
$hotel_result = $stmt->get_result();
while ($row = $hotel_result->fetch_assoc()) {
    $bookings[] = $row;
}
$stmt->close();

// Tour bookings
$tour_query = "SELECT 
    'tour' as booking_type,
    tb.id,
    tb.user_id,
    u.full_name as customer_name,
    u.email as customer_email,
    tp.title as tour_name,
    tp.destination,
    tb.travel_date,
    tb.booking_date,
    tb.status,
    tb.total_price as total_amount,
    tb.travelers as participants
FROM tour_bookings tb
JOIN users u ON tb.user_id = u.id
JOIN tour_packages tp ON tb.package_id = tp.id";

$tour_where = [];
$tour_params = [];
$tour_types = '';

if ($filter_status !== 'all') {
    $tour_where[] = "tb.status = ?";
    $tour_params[] = $filter_status;
    $tour_types .= 's';
}

if ($search) {
    $search_param = "%$search%";
    $tour_where[] = "(u.full_name LIKE ? OR u.email LIKE ?)";
    $tour_params[] = $search_param;
    $tour_params[] = $search_param;
    $tour_types .= 'ss';
}

if (!empty($tour_where)) {
    $tour_query .= " WHERE " . implode(" AND ", $tour_where);
    $stmt = $conn->prepare($tour_query);
    if ($tour_types) {
        $stmt->bind_param($tour_types, ...$tour_params);
    }
} else {
    $stmt = $conn->prepare($tour_query);
}
$stmt->execute();
$tour_result = $stmt->get_result();
while ($row = $tour_result->fetch_assoc()) {
    $bookings[] = $row;
}
$stmt->close();

// Filter by type if needed
if ($filter_type !== 'all') {
    $bookings = array_filter($bookings, function($booking) use ($filter_type) {
        return $booking['booking_type'] === $filter_type;
    });
}

// Sort by booking date (newest first)
usort($bookings, function($a, $b) {
    return strtotime($b['booking_date']) - strtotime($a['booking_date']);
});

include '../includes/header.php';
?>
<main class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark p-6 lg:p-8">
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Manage Bookings</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-1">View and manage all customer bookings</p>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <form method="GET" class="flex flex-col gap-4">
                <div class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[250px]">
                        <label for="booking_search_input" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Search Bookings</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px] pointer-events-none">search</span>
                            <input id="booking_search_input" type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Search by customer name or email" class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" aria-label="Search bookings">
                        </div>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Booking Type</label>
                        <select name="type" id="filterType" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="all" <?php echo $filter_type === 'all' ? 'selected' : ''; ?>>All Types</option>
                            <option value="flight" <?php echo $filter_type === 'flight' ? 'selected' : ''; ?>>Flights</option>
                            <option value="hotel" <?php echo $filter_type === 'hotel' ? 'selected' : ''; ?>>Hotels</option>
                            <option value="tour" <?php echo $filter_type === 'tour' ? 'selected' : ''; ?>>Tours</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Status</label>
                        <select name="status" id="filterStatus" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                            <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $filter_status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="cancelled" <?php echo $filter_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            <option value="completed" <?php echo $filter_status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors">
                        <span class="material-symbols-outlined text-[18px] align-middle">search</span>
                        Search
                    </button>
                    <?php if (isset($_GET['search']) || $filter_type !== 'all' || $filter_status !== 'all'): ?>
                        <a href="manage_bookings.php" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
                            Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Success/Error Message -->
        <?php if ($message): ?>
            <div class="bg-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-50 dark:bg-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-900/10 border border-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-200 dark:border-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-900/20 text-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-700 dark:text-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-400 px-4 py-3 rounded-lg text-sm">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Bookings Table -->
        <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Details</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <span class="material-symbols-outlined text-4xl mb-2 block">inbox</span>
                                    No bookings found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $booking): ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold
                                            <?php 
                                            if ($booking['booking_type'] === 'flight') echo 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
                                            elseif ($booking['booking_type'] === 'hotel') echo 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400';
                                            else echo 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400';
                                            ?>">
                                            <span class="material-symbols-outlined text-[16px]">
                                                <?php echo $booking['booking_type'] === 'flight' ? 'flight' : ($booking['booking_type'] === 'hotel' ? 'hotel' : 'luggage'); ?>
                                            </span>
                                            <?php echo ucfirst($booking['booking_type']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars($booking['customer_name']); ?></div>
                                        <div class="text-sm text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($booking['customer_email']); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-slate-900 dark:text-white">
                                            <?php if ($booking['booking_type'] === 'flight'): ?>
                                                <?php echo htmlspecialchars($booking['airline']); ?> - <?php echo htmlspecialchars($booking['flight_number']); ?><br>
                                                <span class="text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($booking['origin']); ?> → <?php echo htmlspecialchars($booking['destination']); ?></span>
                                            <?php elseif ($booking['booking_type'] === 'hotel'): ?>
                                                <?php echo htmlspecialchars($booking['hotel_name']); ?><br>
                                                <span class="text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($booking['location']); ?></span>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($booking['tour_name']); ?><br>
                                                <span class="text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($booking['destination']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                        <?php 
                                        $date_field = $booking['booking_type'] === 'flight' ? 'departure_date' : ($booking['booking_type'] === 'hotel' ? 'check_in' : 'travel_date');
                                        echo date('M d, Y', strtotime($booking[$date_field])); 
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900 dark:text-white">
                                        $<?php echo number_format($booking['total_amount'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                            <?php 
                                            if ($booking['status'] === 'confirmed') echo 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
                                            elseif ($booking['status'] === 'pending') echo 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
                                            elseif ($booking['status'] === 'cancelled') echo 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
                                            else echo 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
                                            ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center gap-2">
                                            <button onclick="viewBookingDetails('<?php echo $booking['booking_type']; ?>', <?php echo $booking['id']; ?>)" class="text-primary hover:text-primary-dark transition-colors" title="View Details">
                                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                                            </button>
                                            <?php if ($booking['status'] === 'pending'): ?>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to confirm this booking?');">
                                                    <input type="hidden" name="action" value="confirm">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="booking_type" value="<?php echo $booking['booking_type']; ?>">
                                                    <button type="submit" class="text-green-600 hover:text-green-700 transition-colors" title="Confirm">
                                                        <span class="material-symbols-outlined text-[20px]">check_circle</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($booking['status'] !== 'cancelled'): ?>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="booking_type" value="<?php echo $booking['booking_type']; ?>">
                                                    <button type="submit" class="text-red-600 hover:text-red-700 transition-colors" title="Cancel">
                                                        <span class="material-symbols-outlined text-[20px]">cancel</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
function viewBookingDetails(type, id) {
    alert('Booking Details:\nType: ' + type + '\nID: ' + id + '\n\nThis would show detailed booking information in a modal.');
}
</script>

<?php include '../includes/footer.php'; ?>

