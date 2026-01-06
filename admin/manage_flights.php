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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
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
            
            $stmt = $conn->prepare("INSERT INTO flights (flight_number, airline, origin, destination, departure_date, arrival_date, price, available_seats, aircraft, stops, duration, class_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssdississ", $flight_number, $airline, $origin, $destination, $departure_date, $arrival_date, $price, $available_seats, $aircraft, $stops, $duration, $class_type);
            
            if ($stmt->execute()) {
                $message = 'Flight added successfully!';
                $message_type = 'success';
            } else {
                $message = 'Error adding flight.';
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
        }
    }
}

// Get all flights
$flights = $conn->query("SELECT * FROM flights ORDER BY departure_date DESC");

include '../includes/header.php';
?>
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
    </div>
</main>

<script>
function toggleFlightForm() {
    const form = document.getElementById('flightForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>
<?php include '../includes/footer.php'; ?>

