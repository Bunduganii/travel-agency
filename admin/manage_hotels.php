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
$editing_hotel = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' || $_POST['action'] === 'update') {
            $name = trim($_POST['name']);
            $location = trim($_POST['location']);
            $city = trim($_POST['city']);
            $country = trim($_POST['country']);
            $star_rating = intval($_POST['star_rating']);
            $price_per_night = floatval($_POST['price_per_night']);
            $amenities = trim($_POST['amenities']);
            $description = trim($_POST['description']);
            $available_rooms = intval($_POST['available_rooms']);
            
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
        } elseif ($_POST['action'] === 'edit') {
            $id = intval($_POST['hotel_id']);
            $stmt = $conn->prepare("SELECT * FROM hotels WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $editing_hotel = $result->fetch_assoc();
            $stmt->close();
        }
    }
}

// Get search/filter parameters
$search = $_GET['search'] ?? '';
$filter_city = $_GET['city'] ?? 'all';
$filter_rating = $_GET['rating'] ?? 'all';

// Build query
$where = [];
$params = [];
$types = '';

if ($search) {
    $where[] = "(name LIKE ? OR location LIKE ? OR city LIKE ? OR country LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ssss';
}

if ($filter_city && $filter_city !== 'all') {
    $where[] = "city = ?";
    $params[] = $filter_city;
    $types .= 's';
}

if ($filter_rating && $filter_rating !== 'all') {
    $where[] = "star_rating = ?";
    $params[] = intval($filter_rating);
    $types .= 'i';
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
$query = "SELECT * FROM hotels $where_clause ORDER BY created_at DESC";

// Get unique cities for filter
$cities_result = $conn->query("SELECT DISTINCT city FROM hotels WHERE city IS NOT NULL ORDER BY city");

$hotels = [];
if (!empty($params)) {
    $stmt = $conn->prepare($query);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $hotels = $stmt->get_result();
} else {
    $hotels = $conn->query($query);
}

include '../includes/header.php';
?>
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

        <!-- Search and Filters -->
        <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label for="hotel_search_input" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Search Hotels</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px] pointer-events-none">search</span>
                        <input id="hotel_search_input" type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name, location, city, or country" class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" aria-label="Search hotels">
                    </div>
                </div>
                <div class="md:w-48">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Filter by City</label>
                    <select name="city" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="all" <?php echo $filter_city === 'all' ? 'selected' : ''; ?>>All Cities</option>
                        <?php 
                        $cities_result->data_seek(0);
                        while ($city_row = $cities_result->fetch_assoc()): 
                        ?>
                            <option value="<?php echo htmlspecialchars($city_row['city']); ?>" <?php echo $filter_city === $city_row['city'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($city_row['city']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="md:w-32">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Star Rating</label>
                    <select name="rating" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="all" <?php echo $filter_rating === 'all' ? 'selected' : ''; ?>>All Ratings</option>
                        <option value="5" <?php echo $filter_rating === '5' ? 'selected' : ''; ?>>5 Stars</option>
                        <option value="4" <?php echo $filter_rating === '4' ? 'selected' : ''; ?>>4 Stars</option>
                        <option value="3" <?php echo $filter_rating === '3' ? 'selected' : ''; ?>>3 Stars</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors">
                        <span class="material-symbols-outlined text-[18px] align-middle">search</span>
                        Search
                    </button>
                    <?php if ($search || $filter_city !== 'all' || $filter_rating !== 'all'): ?>
                        <a href="manage_hotels.php" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
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
    if (form.style.display === 'none') {
        // Reset form if canceling
        window.location.href = 'manage_hotels.php';
    }
}
</script>
<?php include '../includes/footer.php'; ?>
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
