<?php
/**
 * Manage Tour Packages Page
 * Admin page to add, edit, and delete tour packages
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$page_title = 'Manage Tours';
$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $title = trim($_POST['title']);
            $destination = trim($_POST['destination']);
            $duration_days = intval($_POST['duration_days']);
            $duration_nights = intval($_POST['duration_nights']);
            $price = floatval($_POST['price']);
            $original_price = floatval($_POST['original_price'] ?? 0);
            $description = trim($_POST['description']);
            $inclusions = trim($_POST['inclusions']);
            $package_type = trim($_POST['package_type']);
            $rating = floatval($_POST['rating'] ?? 0);
            $available_spots = intval($_POST['available_spots']);
            
            $stmt = $conn->prepare("INSERT INTO tour_packages (title, destination, duration_days, duration_nights, price, original_price, description, inclusions, package_type, rating, available_spots) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiidddssdi", $title, $destination, $duration_days, $duration_nights, $price, $original_price, $description, $inclusions, $package_type, $rating, $available_spots);
            
            if ($stmt->execute()) {
                $message = 'Tour package added successfully!';
                $message_type = 'success';
            } else {
                $message = 'Error adding tour package.';
                $message_type = 'error';
            }
            $stmt->close();
        } elseif ($_POST['action'] === 'delete') {
            $id = intval($_POST['package_id']);
            $stmt = $conn->prepare("DELETE FROM tour_packages WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = 'Tour package deleted successfully!';
                $message_type = 'success';
            }
            $stmt->close();
        }
    }
}

// Get all tour packages
$tours = $conn->query("SELECT * FROM tour_packages ORDER BY created_at DESC");

include '../includes/header.php';
?>
<main class="admin-page">
    <div class="page-header">
        <h1>Manage Tour Packages</h1>
        <button class="btn btn-primary" onclick="toggleTourForm()">
            <i class="fas fa-plus"></i> Add New Package
        </button>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> slide-in">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <div class="tour-form-container" id="tourForm" style="display: none;">
        <form method="POST" class="admin-form">
            <input type="hidden" name="action" value="add">
            <h2>Add New Tour Package</h2>
            
            <div class="form-group">
                <label>Package Title</label>
                <input type="text" name="title" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Destination</label>
                    <input type="text" name="destination" required>
                </div>
                <div class="form-group">
                    <label>Package Type</label>
                    <select name="package_type" required>
                        <option value="Adventure">Adventure</option>
                        <option value="Family">Family</option>
                        <option value="Romantic">Romantic</option>
                        <option value="Cultural">Cultural</option>
                        <option value="Luxury">Luxury</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Duration (Days)</label>
                    <input type="number" name="duration_days" required>
                </div>
                <div class="form-group">
                    <label>Duration (Nights)</label>
                    <input type="number" name="duration_nights" required>
                </div>
                <div class="form-group">
                    <label>Rating</label>
                    <input type="number" step="0.1" name="rating" min="0" max="5" value="0">
                </div>
                <div class="form-group">
                    <label>Available Spots</label>
                    <input type="number" name="available_spots" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Price ($)</label>
                    <input type="number" step="0.01" name="price" required>
                </div>
                <div class="form-group">
                    <label>Original Price ($) - for discount</label>
                    <input type="number" step="0.01" name="original_price" value="0">
                </div>
            </div>
            
            <div class="form-group">
                <label>Inclusions (comma-separated)</label>
                <input type="text" name="inclusions" placeholder="Flight Included, Hotel, Breakfast, Guide">
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Package</button>
                <button type="button" class="btn btn-secondary" onclick="toggleTourForm()">Cancel</button>
            </div>
        </form>
    </div>
    
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Destination</th>
                    <th>Duration</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Spots</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($tour = $tours->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($tour['title']); ?></td>
                    <td><?php echo htmlspecialchars($tour['destination']); ?></td>
                    <td><?php echo $tour['duration_days']; ?> Days / <?php echo $tour['duration_nights']; ?> Nights</td>
                    <td><?php echo htmlspecialchars($tour['package_type']); ?></td>
                    <td>$<?php echo number_format($tour['price'], 2); ?></td>
                    <td><?php echo $tour['available_spots']; ?></td>
                    <td>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="package_id" value="<?php echo $tour['id']; ?>">
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
function toggleTourForm() {
    const form = document.getElementById('tourForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>
<?php include '../includes/footer.php'; ?>

