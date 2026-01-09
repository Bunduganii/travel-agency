<?php
/**
 * Manage Flights Page
 * Admin page to add, edit, and delete flights
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$page_title = 'Manage Flights';
$message = '';
$message_type = '';
$editing_flight = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' || $_POST['action'] === 'update') {
            $flight_number = trim($_POST['flight_number']);
            $airline = trim($_POST['airline']);
            $origin = trim($_POST['origin']);
            $destination = trim($_POST['destination']);
            $departure_date = $_POST['departure_date'];
            $arrival_date = $_POST['arrival_date'];
            $price = floatval($_POST['price']);
            $available_seats = intval($_POST['available_seats']);
            $aircraft = trim($_POST['aircraft']);
            $stops = intval($_POST['stops']);
            $duration = trim($_POST['duration']);
            $class_type = $_POST['class_type'];
            
            if ($_POST['action'] === 'update') {
                $id = intval($_POST['flight_id']);
                $stmt = $conn->prepare("UPDATE flights SET flight_number = ?, airline = ?, origin = ?, destination = ?, departure_date = ?, arrival_date = ?, price = ?, available_seats = ?, aircraft = ?, stops = ?, duration = ?, class_type = ? WHERE id = ?");
                $type_string = "s" . "s" . "s" . "s" . "s" . "s" . "d" . "i" . "s" . "i" . "s" . "s" . "i";
                $stmt->bind_param($type_string, $flight_number, $airline, $origin, $destination, $departure_date, $arrival_date, $price, $available_seats, $aircraft, $stops, $duration, $class_type, $id);
            } else {
                $stmt = $conn->prepare("INSERT INTO flights (flight_number, airline, origin, destination, departure_date, arrival_date, price, available_seats, aircraft, stops, duration, class_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $type_string = "s" . "s" . "s" . "s" . "s" . "s" . "d" . "i" . "s" . "i" . "s" . "s";
                $stmt->bind_param($type_string, $flight_number, $airline, $origin, $destination, $departure_date, $arrival_date, $price, $available_seats, $aircraft, $stops, $duration, $class_type);
            }
            
            if ($stmt->execute()) {
                $message = $_POST['action'] === 'update' ? 'Flight updated successfully!' : 'Flight added successfully!';
                $message_type = 'success';
            } else {
                $message = 'Error saving flight.';
                $message_type = 'error';
            }
            $stmt->close();
        } elseif ($_POST['action'] === 'delete') {
            $id = intval($_POST['flight_id']);
            $stmt = $conn->prepare("DELETE FROM flights WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = 'Flight deleted successfully!';
                $message_type = 'success';
            }
            $stmt->close();
        } elseif ($_POST['action'] === 'edit') {
            $id = intval($_POST['flight_id']);
            $stmt = $conn->prepare("SELECT * FROM flights WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $editing_flight = $result->fetch_assoc();
            $stmt->close();
        }
    }
}

// Get search/filter parameters
$search = $_GET['search'] ?? '';
$filter_airline = $_GET['airline'] ?? 'all';
$filter_status = $_GET['status'] ?? 'all';

// Build query
$where = [];
$params = [];
$types = '';

if ($search) {
    $where[] = "(flight_number LIKE ? OR airline LIKE ? OR origin LIKE ? OR destination LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ssss';
}

if ($filter_airline && $filter_airline !== 'all') {
    $where[] = "airline = ?";
    $params[] = $filter_airline;
    $types .= 's';
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
$query = "SELECT * FROM flights $where_clause ORDER BY departure_date DESC";

// Get unique airlines for filter
$airlines_result = $conn->query("SELECT DISTINCT airline FROM flights WHERE airline IS NOT NULL ORDER BY airline");

$flights = [];
if (!empty($params)) {
    $stmt = $conn->prepare($query);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $flights = $stmt->get_result();
} else {
    $flights = $conn->query($query);
}

include '../includes/header.php';
?>
<main class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark p-6 lg:p-8">
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Manage Flights</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Add, edit, and manage flight listings</p>
            </div>
            <button onclick="toggleFlightForm()" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors shadow-md shadow-primary/20">
                <span class="material-symbols-outlined text-[18px]">add</span>
                <?php echo $editing_flight ? 'Cancel Edit' : 'Add New Flight'; ?>
            </button>
        </div>

        <!-- Search and Filters -->
        <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label for="flight_search_input" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Search Flights</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px] pointer-events-none">search</span>
                        <input id="flight_search_input" type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by flight number, airline, origin, or destination" class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" aria-label="Search flights">
                    </div>
                </div>
                <div class="md:w-48">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Filter by Airline</label>
                    <select name="airline" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="all" <?php echo $filter_airline === 'all' ? 'selected' : ''; ?>>All Airlines</option>
                        <?php 
                        $airlines_result->data_seek(0);
                        while ($airline_row = $airlines_result->fetch_assoc()): 
                        ?>
                            <option value="<?php echo htmlspecialchars($airline_row['airline']); ?>" <?php echo $filter_airline === $airline_row['airline'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($airline_row['airline']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors">
                        <span class="material-symbols-outlined text-[18px] align-middle">search</span>
                        Search
                    </button>
                    <?php if ($search || $filter_airline !== 'all'): ?>
                        <a href="manage_flights.php" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
                            Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Message Alert -->
        <?php if ($message): ?>
            <div class="bg-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-50 dark:bg-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-900/10 border border-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-200 dark:border-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-900/20 text-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-700 dark:text-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-400 px-4 py-3 rounded-lg text-sm">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Add/Edit Flight Form -->
        <div id="flightForm" style="display: <?php echo $editing_flight ? 'block' : 'none'; ?>;" class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="<?php echo $editing_flight ? 'update' : 'add'; ?>">
                <?php if ($editing_flight): ?>
                    <input type="hidden" name="flight_id" value="<?php echo $editing_flight['id']; ?>">
                <?php endif; ?>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4"><?php echo $editing_flight ? 'Edit Flight' : 'Add New Flight'; ?></h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Flight Number</label>
                        <input type="text" name="flight_number" required value="<?php echo htmlspecialchars($editing_flight['flight_number'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Airline</label>
                        <input type="text" name="airline" required value="<?php echo htmlspecialchars($editing_flight['airline'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Origin</label>
                        <input type="text" name="origin" required value="<?php echo htmlspecialchars($editing_flight['origin'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Destination</label>
                        <input type="text" name="destination" required value="<?php echo htmlspecialchars($editing_flight['destination'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Departure Date & Time</label>
                        <input type="datetime-local" name="departure_date" required value="<?php echo $editing_flight ? date('Y-m-d\TH:i', strtotime($editing_flight['departure_date'])) : ''; ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Arrival Date & Time</label>
                        <input type="datetime-local" name="arrival_date" required value="<?php echo $editing_flight ? date('Y-m-d\TH:i', strtotime($editing_flight['arrival_date'])) : ''; ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Price ($)</label>
                        <input type="number" step="0.01" name="price" required value="<?php echo htmlspecialchars($editing_flight['price'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Available Seats</label>
                        <input type="number" name="available_seats" required value="<?php echo htmlspecialchars($editing_flight['available_seats'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Aircraft</label>
                        <input type="text" name="aircraft" value="<?php echo htmlspecialchars($editing_flight['aircraft'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Stops</label>
                        <input type="number" name="stops" value="<?php echo htmlspecialchars($editing_flight['stops'] ?? '0'); ?>" min="0" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Duration</label>
                        <input type="text" name="duration" value="<?php echo htmlspecialchars($editing_flight['duration'] ?? ''); ?>" placeholder="e.g., 6h 55m" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Class</label>
                        <select name="class_type" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="Economy" <?php echo ($editing_flight['class_type'] ?? 'Economy') == 'Economy' ? 'selected' : ''; ?>>Economy</option>
                            <option value="Premium" <?php echo ($editing_flight['class_type'] ?? '') == 'Premium' ? 'selected' : ''; ?>>Premium</option>
                            <option value="Business" <?php echo ($editing_flight['class_type'] ?? '') == 'Business' ? 'selected' : ''; ?>>Business</option>
                            <option value="First" <?php echo ($editing_flight['class_type'] ?? '') == 'First' ? 'selected' : ''; ?>>First</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors"><?php echo $editing_flight ? 'Update Flight' : 'Add Flight'; ?></button>
                    <button type="button" onclick="toggleFlightForm()" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">Cancel</button>
                </div>
            </form>
        </div>

        <!-- Flights Table -->
        <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Flight Number</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Airline</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Route</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Departure</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Arrival</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Seats</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <?php 
                        $has_flights = false;
                        while ($flight = $flights->fetch_assoc()): 
                            $has_flights = true;
                        ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars($flight['flight_number']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($flight['airline']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white"><?php echo htmlspecialchars($flight['origin']); ?> → <?php echo htmlspecialchars($flight['destination']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400"><?php echo date('M d, Y H:i', strtotime($flight['departure_date'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400"><?php echo date('M d, Y H:i', strtotime($flight['arrival_date'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900 dark:text-white">$<?php echo number_format($flight['price'], 2); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400"><?php echo $flight['available_seats']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center gap-2">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="flight_id" value="<?php echo $flight['id']; ?>">
                                            <button type="submit" class="text-primary hover:text-primary-dark transition-colors" title="Edit">
                                                <span class="material-symbols-outlined text-[20px]">edit</span>
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this flight?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="flight_id" value="<?php echo $flight['id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-700 transition-colors" title="Delete">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if (!$has_flights): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <span class="material-symbols-outlined text-4xl mb-2 block">flight</span>
                                    No flights found. Add your first flight above.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
    </div>
</main>

<script>
function toggleFlightForm() {
    const form = document.getElementById('flightForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
    if (form.style.display === 'none') {
        window.location.href = 'manage_flights.php';
    }
}
</script>
<?php include '../includes/footer.php'; ?>
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
