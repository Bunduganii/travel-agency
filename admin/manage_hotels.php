<?php
/**
 * Manage Hotels Page
 * Admin page to add, edit, and delete hotels
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$page_title = 'Manage Hotels';
$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $name = trim($_POST['name']);
            $location = trim($_POST['location']);
            $city = trim($_POST['city']);
            $country = trim($_POST['country']);
            $star_rating = intval($_POST['star_rating']);
            $price_per_night = floatval($_POST['price_per_night']);
            $amenities = trim($_POST['amenities']);
            $description = trim($_POST['description']);
            $available_rooms = intval($_POST['available_rooms']);
            
            $stmt = $conn->prepare("INSERT INTO hotels (name, location, city, country, star_rating, price_per_night, amenities, description, available_rooms) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssidssi", $name, $location, $city, $country, $star_rating, $price_per_night, $amenities, $description, $available_rooms);
            
            if ($stmt->execute()) {
                $message = 'Hotel added successfully!';
                $message_type = 'success';
            } else {
                $message = 'Error adding hotel.';
                $message_type = 'error';
            }
            $stmt->close();
        } elseif ($_POST['action'] === 'delete') {
            $id = intval($_POST['hotel_id']);
            $stmt = $conn->prepare("DELETE FROM hotels WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = 'Hotel deleted successfully!';
                $message_type = 'success';
            }
            $stmt->close();
        }
    }
}

// Get all hotels
$hotels = $conn->query("SELECT * FROM hotels ORDER BY created_at DESC");

include '../includes/header.php';
?>
<main class="admin-page">
    <div class="page-header">
        <h1>Manage Hotels</h1>
        <button class="btn btn-primary" onclick="toggleHotelForm()">
            <i class="fas fa-plus"></i> Add New Hotel
        </button>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> slide-in">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <div class="hotel-form-container" id="hotelForm" style="display: none;">
        <form method="POST" class="admin-form">
            <input type="hidden" name="action" value="add">
            <h2>Add New Hotel</h2>
            
            <div class="form-group">
                <label>Hotel Name</label>
                <input type="text" name="name" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Location/Address</label>
                    <input type="text" name="location" required>
                </div>
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" required>
                </div>
                <div class="form-group">
                    <label>Country</label>
                    <input type="text" name="country" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Star Rating</label>
                    <select name="star_rating" required>
                        <option value="3">3 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="5">5 Stars</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price Per Night ($)</label>
                    <input type="number" step="0.01" name="price_per_night" required>
                </div>
                <div class="form-group">
                    <label>Available Rooms</label>
                    <input type="number" name="available_rooms" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Amenities (comma-separated)</label>
                <input type="text" name="amenities" placeholder="Free WiFi, Pool, Gym, Breakfast">
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Hotel</button>
                <button type="button" class="btn btn-secondary" onclick="toggleHotelForm()">Cancel</button>
            </div>
        </form>
    </div>
    
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Hotel Name</th>
                    <th>Location</th>
                    <th>City</th>
                    <th>Rating</th>
                    <th>Price/Night</th>
                    <th>Rooms</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($hotel = $hotels->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($hotel['name']); ?></td>
                    <td><?php echo htmlspecialchars($hotel['location']); ?></td>
                    <td><?php echo htmlspecialchars($hotel['city']); ?></td>
                    <td><?php echo str_repeat('★', $hotel['star_rating']); ?></td>
                    <td>$<?php echo number_format($hotel['price_per_night'], 2); ?></td>
                    <td><?php echo $hotel['available_rooms']; ?></td>
                    <td>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="hotel_id" value="<?php echo $hotel['id']; ?>">
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
function toggleHotelForm() {
    const form = document.getElementById('hotelForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>
<?php include '../includes/footer.php'; ?>

