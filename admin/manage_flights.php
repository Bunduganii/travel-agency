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
<<<<<<< HEAD
=======
$editing_flight = null;
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
<<<<<<< HEAD
        if ($_POST['action'] === 'add') {
=======
        if ($_POST['action'] === 'add' || $_POST['action'] === 'update') {
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
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
            
<<<<<<< HEAD
            $stmt = $conn->prepare("INSERT INTO flights (flight_number, airline, origin, destination, departure_date, arrival_date, price, available_seats, aircraft, stops, duration, class_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssdississ", $flight_number, $airline, $origin, $destination, $departure_date, $arrival_date, $price, $available_seats, $aircraft, $stops, $duration, $class_type);
            
            if ($stmt->execute()) {
                $message = 'Flight added successfully!';
                $message_type = 'success';
            } else {
                $message = 'Error adding flight.';
=======
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
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
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
<<<<<<< HEAD
=======
        } elseif ($_POST['action'] === 'edit') {
            $id = intval($_POST['flight_id']);
            $stmt = $conn->prepare("SELECT * FROM flights WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $editing_flight = $result->fetch_assoc();
            $stmt->close();
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
        }
    }
}

// Get all flights
$flights = $conn->query("SELECT * FROM flights ORDER BY departure_date DESC");

include '../includes/header.php';
?>
<<<<<<< HEAD
<main class="admin-page">
    <div class="page-header">
        <h1>Manage Flights</h1>
        <button class="btn btn-primary" onclick="toggleFlightForm()">
            <i class="fas fa-plus"></i> Add New Flight
        </button>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> slide-in">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <div class="flight-form-container" id="flightForm" style="display: none;">
        <form method="POST" class="admin-form">
            <input type="hidden" name="action" value="add">
            <h2>Add New Flight</h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Flight Number</label>
                    <input type="text" name="flight_number" required>
                </div>
                <div class="form-group">
                    <label>Airline</label>
                    <input type="text" name="airline" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Origin</label>
                    <input type="text" name="origin" required>
                </div>
                <div class="form-group">
                    <label>Destination</label>
                    <input type="text" name="destination" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Departure Date & Time</label>
                    <input type="datetime-local" name="departure_date" required>
                </div>
                <div class="form-group">
                    <label>Arrival Date & Time</label>
                    <input type="datetime-local" name="arrival_date" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Price ($)</label>
                    <input type="number" step="0.01" name="price" required>
                </div>
                <div class="form-group">
                    <label>Available Seats</label>
                    <input type="number" name="available_seats" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Aircraft</label>
                    <input type="text" name="aircraft">
                </div>
                <div class="form-group">
                    <label>Stops</label>
                    <input type="number" name="stops" value="0" min="0">
                </div>
                <div class="form-group">
                    <label>Duration</label>
                    <input type="text" name="duration" placeholder="e.g., 6h 55m">
                </div>
                <div class="form-group">
                    <label>Class</label>
                    <select name="class_type">
                        <option value="Economy">Economy</option>
                        <option value="Premium">Premium</option>
                        <option value="Business">Business</option>
                        <option value="First">First</option>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Flight</button>
                <button type="button" class="btn btn-secondary" onclick="toggleFlightForm()">Cancel</button>
            </div>
        </form>
    </div>
    
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Flight Number</th>
                    <th>Airline</th>
                    <th>Route</th>
                    <th>Departure</th>
                    <th>Arrival</th>
                    <th>Price</th>
                    <th>Seats</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($flight = $flights->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($flight['flight_number']); ?></td>
                    <td><?php echo htmlspecialchars($flight['airline']); ?></td>
                    <td><?php echo htmlspecialchars($flight['origin']); ?> → <?php echo htmlspecialchars($flight['destination']); ?></td>
                    <td><?php echo date('M d, Y H:i', strtotime($flight['departure_date'])); ?></td>
                    <td><?php echo date('M d, Y H:i', strtotime($flight['arrival_date'])); ?></td>
                    <td>$<?php echo number_format($flight['price'], 2); ?></td>
                    <td><?php echo $flight['available_seats']; ?></td>
                    <td>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="flight_id" value="<?php echo $flight['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
=======
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
<<<<<<< HEAD
}
</script>
<?php include '../includes/footer.php'; ?>

=======
    if (form.style.display === 'none') {
        window.location.href = 'manage_flights.php';
    }
}
</script>
<?php include '../includes/footer.php'; ?>
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
