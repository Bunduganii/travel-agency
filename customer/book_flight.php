<?php
/**
 * Book Flight Page
 * Customer page to search and book flights
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireCustomer();

$page_title = 'Book Flight';
$message = '';
$message_type = '';

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_flight'])) {
    $flight_id = intval($_POST['flight_id']);
    $passengers = intval($_POST['passengers']);
    
    // Get flight details
    $stmt = $conn->prepare("SELECT * FROM flights WHERE id = ?");
    $stmt->bind_param("i", $flight_id);
    $stmt->execute();
    $flight = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($flight && $flight['available_seats'] >= $passengers) {
        $total_price = $flight['price'] * $passengers;
        
        // Create booking
        $stmt = $conn->prepare("INSERT INTO flight_bookings (user_id, flight_id, passengers, total_price) VALUES (?, ?, ?, ?)");
        $user_id = getUserId();
        $stmt->bind_param("iiid", $user_id, $flight_id, $passengers, $total_price);
        
        if ($stmt->execute()) {
            $booking_id = $conn->insert_id;
            header("Location: payment.php?type=flight&id=" . $booking_id);
            exit();
        }
        $stmt->close();
    } else {
        $message = 'Not enough seats available.';
        $message_type = 'error';
    }
}

// Get search parameters
$origin = $_GET['origin'] ?? '';
$destination = $_GET['destination'] ?? '';
$departure_date = $_GET['departure_date'] ?? '';
$return_date = $_GET['return_date'] ?? '';
$trip_type = $_GET['trip_type'] ?? 'round_trip';
$passengers = $_GET['passengers'] ?? 1;
$class_type = $_GET['class'] ?? 'Economy';

// Build query
$where = [];
$params = [];
$types = '';

if ($origin) {
    $where[] = "origin LIKE ?";
    $params[] = "%$origin%";
    $types .= 's';
}
if ($destination) {
    $where[] = "destination LIKE ?";
    $params[] = "%$destination%";
    $types .= 's';
}
if ($departure_date) {
    $where[] = "DATE(departure_date) = ?";
    $params[] = $departure_date;
    $types .= 's';
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
$query = "SELECT * FROM flights $where_clause ORDER BY departure_date ASC LIMIT 50";

$flights = [];
if (!empty($params)) {
    $stmt = $conn->prepare($query);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $flights = $stmt->get_result();
} else {
    $flights = $conn->query("SELECT * FROM flights ORDER BY departure_date ASC LIMIT 50");
}

include '../includes/header.php';
?>
<main class="flight-search-page">
    <div class="search-section">
        <h1>Flight Search</h1>
        <form method="GET" class="flight-search-form">
            <div class="trip-type-toggle">
                <input type="radio" name="trip_type" value="round_trip" id="round_trip" <?php echo $trip_type === 'round_trip' ? 'checked' : ''; ?>>
                <label for="round_trip">Round Trip</label>
                
                <input type="radio" name="trip_type" value="one_way" id="one_way" <?php echo $trip_type === 'one_way' ? 'checked' : ''; ?>>
                <label for="one_way">One Way</label>
                
                <input type="radio" name="trip_type" value="multi_city" id="multi_city" <?php echo $trip_type === 'multi_city' ? 'checked' : ''; ?>>
                <label for="multi_city">Multi-city</label>
            </div>
            
            <div class="search-fields">
                <div class="field-group">
                    <label>FROM</label>
                    <div class="input-with-icon">
                        <i class="fas fa-plane-departure"></i>
                        <input type="text" name="origin" placeholder="JFK" value="<?php echo htmlspecialchars($origin); ?>">
                    </div>
                </div>
                
                <button type="button" class="swap-btn" onclick="swapAirports()">
                    <i class="fas fa-exchange-alt"></i>
                </button>
                
                <div class="field-group">
                    <label>TO</label>
                    <div class="input-with-icon">
                        <i class="fas fa-plane-arrival"></i>
                        <input type="text" name="destination" placeholder="LHR" value="<?php echo htmlspecialchars($destination); ?>">
                    </div>
                </div>
                
                <div class="field-group">
                    <label>DEPART</label>
                    <div class="input-with-icon">
                        <i class="fas fa-calendar"></i>
                        <input type="date" name="departure_date" value="<?php echo htmlspecialchars($departure_date); ?>">
                    </div>
                </div>
                
                <div class="field-group">
                    <label>RETURN</label>
                    <div class="input-with-icon">
                        <i class="fas fa-calendar"></i>
                        <input type="date" name="return_date" value="<?php echo htmlspecialchars($return_date); ?>" <?php echo $trip_type === 'one_way' ? 'disabled' : ''; ?>>
                    </div>
                </div>
                
                <div class="field-group">
                    <label>Travelers & Class</label>
                    <div class="input-with-icon">
                        <i class="fas fa-users"></i>
                        <input type="number" name="passengers" min="1" value="<?php echo htmlspecialchars($passengers); ?>">
                        <select name="class">
                            <option value="Economy" <?php echo $class_type === 'Economy' ? 'selected' : ''; ?>>Economy</option>
                            <option value="Premium" <?php echo $class_type === 'Premium' ? 'selected' : ''; ?>>Premium</option>
                            <option value="Business" <?php echo $class_type === 'Business' ? 'selected' : ''; ?>>Business</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-search">
                <i class="fas fa-search"></i> Search Flights
            </button>
        </form>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> slide-in">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <div class="results-section">
        <div class="results-header">
            <h2><?php echo mysqli_num_rows($flights); ?> Flights Found</h2>
            <div class="sort-options">
                <button class="sort-btn active">Cheapest</button>
                <button class="sort-btn">Fastest</button>
                <button class="sort-btn">Best Value</button>
            </div>
        </div>
        
        <div class="flights-list">
            <?php while ($flight = $flights->fetch_assoc()): ?>
            <div class="flight-card">
                <div class="flight-airline">
                    <strong><?php echo htmlspecialchars($flight['airline']); ?></strong>
                    <span><?php echo htmlspecialchars($flight['flight_number']); ?></span>
                    <small><?php echo htmlspecialchars($flight['aircraft']); ?></small>
                </div>
                
                <div class="flight-times">
                    <div class="time-block">
                        <strong><?php echo date('H:i', strtotime($flight['departure_date'])); ?></strong>
                        <span><?php echo htmlspecialchars($flight['origin']); ?></span>
                    </div>
                    
                    <div class="flight-path">
                        <div class="path-line"></div>
                        <i class="fas fa-plane"></i>
                        <span><?php echo htmlspecialchars($flight['duration']); ?></span>
                        <span class="stops"><?php echo $flight['stops'] == 0 ? 'Direct' : $flight['stops'] . ' Stop'; ?></span>
                    </div>
                    
                    <div class="time-block">
                        <strong><?php echo date('H:i', strtotime($flight['arrival_date'])); ?></strong>
                        <span><?php echo htmlspecialchars($flight['destination']); ?></span>
                    </div>
                </div>
                
                <div class="flight-price">
                    <strong>$<?php echo number_format($flight['price'], 2); ?></strong>
                    <span>per person</span>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="flight_id" value="<?php echo $flight['id']; ?>">
                        <input type="hidden" name="passengers" value="<?php echo $passengers; ?>">
                        <button type="submit" name="book_flight" class="btn btn-primary">Book Now →</button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</main>

<script>
function swapAirports() {
    const origin = document.querySelector('input[name="origin"]');
    const destination = document.querySelector('input[name="destination"]');
    const temp = origin.value;
    origin.value = destination.value;
    destination.value = temp;
}
</script>
<?php include '../includes/footer.php'; ?>

