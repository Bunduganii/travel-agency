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
<<<<<<< HEAD
=======
$editing_hotel = null;
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
<<<<<<< HEAD
        if ($_POST['action'] === 'add') {
=======
        if ($_POST['action'] === 'add' || $_POST['action'] === 'update') {
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
            $name = trim($_POST['name']);
            $location = trim($_POST['location']);
            $city = trim($_POST['city']);
            $country = trim($_POST['country']);
            $star_rating = intval($_POST['star_rating']);
            $price_per_night = floatval($_POST['price_per_night']);
            $amenities = trim($_POST['amenities']);
            $description = trim($_POST['description']);
            $available_rooms = intval($_POST['available_rooms']);
            
<<<<<<< HEAD
            $stmt = $conn->prepare("INSERT INTO hotels (name, location, city, country, star_rating, price_per_night, amenities, description, available_rooms) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssidssi", $name, $location, $city, $country, $star_rating, $price_per_night, $amenities, $description, $available_rooms);
            
            if ($stmt->execute()) {
                $message = 'Hotel added successfully!';
                $message_type = 'success';
            } else {
                $message = 'Error adding hotel.';
=======
            if ($_POST['action'] === 'update') {
                $id = intval($_POST['hotel_id']);
                $stmt = $conn->prepare("UPDATE hotels SET name = ?, location = ?, city = ?, country = ?, star_rating = ?, price_per_night = ?, amenities = ?, description = ?, available_rooms = ? WHERE id = ?");
                $stmt->bind_param("ssssidssii", $name, $location, $city, $country, $star_rating, $price_per_night, $amenities, $description, $available_rooms, $id);
            } else {
                $stmt = $conn->prepare("INSERT INTO hotels (name, location, city, country, star_rating, price_per_night, amenities, description, available_rooms) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssidssi", $name, $location, $city, $country, $star_rating, $price_per_night, $amenities, $description, $available_rooms);
            }
            
            if ($stmt->execute()) {
                $message = $_POST['action'] === 'update' ? 'Hotel updated successfully!' : 'Hotel added successfully!';
                $message_type = 'success';
            } else {
                $message = 'Error saving hotel.';
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
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
<<<<<<< HEAD
=======
        } elseif ($_POST['action'] === 'edit') {
            $id = intval($_POST['hotel_id']);
            $stmt = $conn->prepare("SELECT * FROM hotels WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $editing_hotel = $result->fetch_assoc();
            $stmt->close();
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
        }
    }
}

// Get all hotels
$hotels = $conn->query("SELECT * FROM hotels ORDER BY created_at DESC");

include '../includes/header.php';
?>
<<<<<<< HEAD
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
=======
<main class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark p-6 lg:p-8">
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Manage Hotels</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Add, edit, and manage hotel listings</p>
            </div>
            <button onclick="toggleHotelForm()" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors shadow-md shadow-primary/20">
                <span class="material-symbols-outlined text-[18px]">add</span>
                <?php echo $editing_hotel ? 'Cancel Edit' : 'Add New Hotel'; ?>
            </button>
        </div>

        <!-- Message Alert -->
        <?php if ($message): ?>
            <div class="bg-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-50 dark:bg-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-900/10 border border-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-200 dark:border-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-900/20 text-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-700 dark:text-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-400 px-4 py-3 rounded-lg text-sm">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Add/Edit Hotel Form -->
        <div id="hotelForm" style="display: <?php echo $editing_hotel ? 'block' : 'none'; ?>;" class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="<?php echo $editing_hotel ? 'update' : 'add'; ?>">
                <?php if ($editing_hotel): ?>
                    <input type="hidden" name="hotel_id" value="<?php echo $editing_hotel['id']; ?>">
                <?php endif; ?>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4"><?php echo $editing_hotel ? 'Edit Hotel' : 'Add New Hotel'; ?></h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Hotel Name</label>
                        <input type="text" name="name" required value="<?php echo htmlspecialchars($editing_hotel['name'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Location/Address</label>
                        <input type="text" name="location" required value="<?php echo htmlspecialchars($editing_hotel['location'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">City</label>
                        <input type="text" name="city" required value="<?php echo htmlspecialchars($editing_hotel['city'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Country</label>
                        <input type="text" name="country" required value="<?php echo htmlspecialchars($editing_hotel['country'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Star Rating</label>
                        <select name="star_rating" required class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="3" <?php echo ($editing_hotel['star_rating'] ?? 3) == 3 ? 'selected' : ''; ?>>3 Stars</option>
                            <option value="4" <?php echo ($editing_hotel['star_rating'] ?? 3) == 4 ? 'selected' : ''; ?>>4 Stars</option>
                            <option value="5" <?php echo ($editing_hotel['star_rating'] ?? 3) == 5 ? 'selected' : ''; ?>>5 Stars</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Price Per Night ($)</label>
                        <input type="number" step="0.01" name="price_per_night" required value="<?php echo htmlspecialchars($editing_hotel['price_per_night'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Available Rooms</label>
                        <input type="number" name="available_rooms" required value="<?php echo htmlspecialchars($editing_hotel['available_rooms'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Amenities (comma-separated)</label>
                        <input type="text" name="amenities" value="<?php echo htmlspecialchars($editing_hotel['amenities'] ?? ''); ?>" placeholder="Free WiFi, Pool, Gym, Breakfast" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Description</label>
                        <textarea name="description" rows="4" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"><?php echo htmlspecialchars($editing_hotel['description'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors"><?php echo $editing_hotel ? 'Update Hotel' : 'Add Hotel'; ?></button>
                    <button type="button" onclick="toggleHotelForm()" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">Cancel</button>
                </div>
            </form>
        </div>

        <!-- Hotels Table -->
        <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Hotel Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">City</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Rating</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Price/Night</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Rooms</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <?php 
                        $has_hotels = false;
                        while ($hotel = $hotels->fetch_assoc()): 
                            $has_hotels = true;
                        ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars($hotel['name']); ?></td>
                                <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($hotel['location']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($hotel['city']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600 dark:text-yellow-400"><?php echo str_repeat('★', $hotel['star_rating']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900 dark:text-white">$<?php echo number_format($hotel['price_per_night'], 2); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400"><?php echo $hotel['available_rooms']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center gap-2">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="hotel_id" value="<?php echo $hotel['id']; ?>">
                                            <button type="submit" class="text-primary hover:text-primary-dark transition-colors" title="Edit">
                                                <span class="material-symbols-outlined text-[20px]">edit</span>
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this hotel?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="hotel_id" value="<?php echo $hotel['id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-700 transition-colors" title="Delete">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if (!$has_hotels): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <span class="material-symbols-outlined text-4xl mb-2 block">hotel</span>
                                    No hotels found. Add your first hotel above.
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
function toggleHotelForm() {
    const form = document.getElementById('hotelForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
<<<<<<< HEAD
}
</script>
<?php include '../includes/footer.php'; ?>

=======
    if (form.style.display === 'none') {
        // Reset form if canceling
        window.location.href = 'manage_hotels.php';
    }
}
</script>
<?php include '../includes/footer.php'; ?>
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
