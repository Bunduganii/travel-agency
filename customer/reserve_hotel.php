<?php
/**
 * Reserve Hotel Page
 * Customer page to search and book hotels
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireCustomer();

$page_title = 'Reserve Hotel';
$message = '';
$message_type = '';

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_hotel'])) {
    $hotel_id = intval($_POST['hotel_id']);
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $guests = intval($_POST['guests']);
    $rooms = intval($_POST['rooms']);
    
    // Get hotel details
    $stmt = $conn->prepare("SELECT * FROM hotels WHERE id = ?");
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $hotel = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($hotel && $hotel['available_rooms'] >= $rooms) {
        $nights = (strtotime($check_out) - strtotime($check_in)) / 86400;
        $total_price = $hotel['price_per_night'] * $nights * $rooms;
        
        // Create reservation
        $stmt = $conn->prepare("INSERT INTO hotel_reservations (user_id, hotel_id, check_in, check_out, guests, rooms, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $user_id = getUserId();
        $stmt->bind_param("iissiid", $user_id, $hotel_id, $check_in, $check_out, $guests, $rooms, $total_price);
        
        if ($stmt->execute()) {
            $booking_id = $conn->insert_id;
            header("Location: payment.php?type=hotel&id=" . $booking_id);
            exit();
        }
        $stmt->close();
    } else {
        $message = 'Not enough rooms available.';
        $message_type = 'error';
    }
}

// Get search parameters
$location = $_GET['location'] ?? '';
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';
$guests = $_GET['guests'] ?? 2;

// Build query
$where = [];
$params = [];
$types = '';

if ($location) {
    $where[] = "(city LIKE ? OR location LIKE ? OR country LIKE ?)";
    $params[] = "%$location%";
    $params[] = "%$location%";
    $params[] = "%$location%";
    $types .= 'sss';
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
$query = "SELECT * FROM hotels $where_clause ORDER BY star_rating DESC, price_per_night ASC LIMIT 50";

$hotels = [];
if (!empty($params)) {
    $stmt = $conn->prepare($query);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $hotels = $stmt->get_result();
} else {
    $hotels = $conn->query("SELECT * FROM hotels ORDER BY star_rating DESC, price_per_night ASC LIMIT 50");
}

include '../includes/header.php';
?>
<main class="hotel-search-page">
    <div class="search-bar">
        <form method="GET" class="hotel-search-form">
            <div class="search-field">
                <i class="fas fa-map-marker-alt"></i>
                <input type="text" name="location" placeholder="Tokyo, Japan" value="<?php echo htmlspecialchars($location); ?>">
            </div>
            
            <div class="search-field">
                <i class="fas fa-calendar"></i>
                <input type="date" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>">
            </div>
            
            <div class="search-field">
                <i class="fas fa-calendar"></i>
                <input type="date" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>">
            </div>
            
            <div class="search-field">
                <i class="fas fa-users"></i>
                <input type="number" name="guests" min="1" value="<?php echo htmlspecialchars($guests); ?>">
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Update Search
            </button>
        </form>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> slide-in">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <div class="hotels-container">
        <div class="hotels-header">
            <h2><?php echo mysqli_num_rows($hotels); ?> Hotels found<?php echo $location ? ' in ' . htmlspecialchars($location) : ''; ?></h2>
            <select class="sort-dropdown">
                <option>Recommended</option>
                <option>Price: Low to High</option>
                <option>Price: High to Low</option>
                <option>Rating</option>
            </select>
        </div>
        
        <div class="hotels-list">
            <?php while ($hotel = $hotels->fetch_assoc()): 
                $amenities = explode(',', $hotel['amenities']);
            ?>
            <div class="hotel-card">
                <div class="hotel-image">
                    <span class="hotel-badge">Recommended</span>
                </div>
                
                <div class="hotel-details">
                    <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
                    <p class="hotel-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($hotel['location']); ?> • <?php echo htmlspecialchars($hotel['city']); ?>
                    </p>
                    
                    <div class="hotel-rating">
                        <span class="rating-stars"><?php echo str_repeat('★', $hotel['star_rating']); ?></span>
                        <span class="rating-text"><?php echo $hotel['star_rating']; ?>.0 Excellent</span>
                    </div>
                    
                    <div class="hotel-amenities">
                        <?php foreach (array_slice($amenities, 0, 3) as $amenity): ?>
                            <span class="amenity-tag"><?php echo htmlspecialchars(trim($amenity)); ?></span>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="hotel-price">
                        <div class="price-info">
                            <strong>$<?php echo number_format($hotel['price_per_night'], 2); ?></strong>
                            <span>/ night</span>
                        </div>
                        <?php if ($check_in && $check_out): 
                            $nights = (strtotime($check_out) - strtotime($check_in)) / 86400;
                        ?>
                            <p class="total-price">Total: $<?php echo number_format($hotel['price_per_night'] * $nights, 2); ?></p>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <input type="hidden" name="hotel_id" value="<?php echo $hotel['id']; ?>">
                            <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>">
                            <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>">
                            <input type="hidden" name="guests" value="<?php echo htmlspecialchars($guests); ?>">
                            <input type="hidden" name="rooms" value="1">
                            <button type="submit" name="book_hotel" class="btn btn-primary">Book Now →</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>

