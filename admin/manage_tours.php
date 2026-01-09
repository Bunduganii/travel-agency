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
$editing_tour = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' || $_POST['action'] === 'update') {
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
            
            if ($_POST['action'] === 'update') {
                $id = intval($_POST['package_id']);
                $stmt = $conn->prepare("UPDATE tour_packages SET title = ?, destination = ?, duration_days = ?, duration_nights = ?, price = ?, original_price = ?, description = ?, inclusions = ?, package_type = ?, rating = ?, available_spots = ? WHERE id = ?");
                $stmt->bind_param("ssiidddssdii", $title, $destination, $duration_days, $duration_nights, $price, $original_price, $description, $inclusions, $package_type, $rating, $available_spots, $id);
            } else {
                $stmt = $conn->prepare("INSERT INTO tour_packages (title, destination, duration_days, duration_nights, price, original_price, description, inclusions, package_type, rating, available_spots) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssiidddssdi", $title, $destination, $duration_days, $duration_nights, $price, $original_price, $description, $inclusions, $package_type, $rating, $available_spots);
            }
            
            if ($stmt->execute()) {
                $message = $_POST['action'] === 'update' ? 'Tour package updated successfully!' : 'Tour package added successfully!';
                $message_type = 'success';
            } else {
                $message = 'Error saving tour package.';
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
        } elseif ($_POST['action'] === 'edit') {
            $id = intval($_POST['package_id']);
            $stmt = $conn->prepare("SELECT * FROM tour_packages WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $editing_tour = $result->fetch_assoc();
            $stmt->close();
        }
    }
}

// Get search/filter parameters
$search = $_GET['search'] ?? '';
$filter_type = $_GET['type'] ?? 'all';
$filter_destination = $_GET['destination'] ?? 'all';

// Build query
$where = [];
$params = [];
$types = '';

if ($search) {
    $where[] = "(title LIKE ? OR destination LIKE ? OR description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if ($filter_type && $filter_type !== 'all') {
    $where[] = "package_type = ?";
    $params[] = $filter_type;
    $types .= 's';
}

if ($filter_destination && $filter_destination !== 'all') {
    $where[] = "destination LIKE ?";
    $params[] = "%$filter_destination%";
    $types .= 's';
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
$query = "SELECT * FROM tour_packages $where_clause ORDER BY created_at DESC";

// Get unique destinations for filter
$destinations_result = $conn->query("SELECT DISTINCT destination FROM tour_packages WHERE destination IS NOT NULL ORDER BY destination");

$tours = [];
if (!empty($params)) {
    $stmt = $conn->prepare($query);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $tours = $stmt->get_result();
} else {
    $tours = $conn->query($query);
}

include '../includes/header.php';
?>
<main class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark p-6 lg:p-8">
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Manage Tour Packages</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Add, edit, and manage tour package listings</p>
            </div>
            <button onclick="toggleTourForm()" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors shadow-md shadow-primary/20">
                <span class="material-symbols-outlined text-[18px]">add</span>
                <?php echo $editing_tour ? 'Cancel Edit' : 'Add New Package'; ?>
            </button>
        </div>

        <!-- Search and Filters -->
        <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label for="tour_search_input" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Search Tours</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px] pointer-events-none">search</span>
                        <input id="tour_search_input" type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by title, destination, or description" class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" aria-label="Search tour packages">
                    </div>
                </div>
                <div class="md:w-48">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Package Type</label>
                    <select name="type" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="all" <?php echo $filter_type === 'all' ? 'selected' : ''; ?>>All Types</option>
                        <option value="Adventure" <?php echo $filter_type === 'Adventure' ? 'selected' : ''; ?>>Adventure</option>
                        <option value="Family" <?php echo $filter_type === 'Family' ? 'selected' : ''; ?>>Family</option>
                        <option value="Romantic" <?php echo $filter_type === 'Romantic' ? 'selected' : ''; ?>>Romantic</option>
                        <option value="Cultural" <?php echo $filter_type === 'Cultural' ? 'selected' : ''; ?>>Cultural</option>
                        <option value="Luxury" <?php echo $filter_type === 'Luxury' ? 'selected' : ''; ?>>Luxury</option>
                    </select>
                </div>
                <div class="md:w-48">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Destination</label>
                    <select name="destination" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="all" <?php echo $filter_destination === 'all' ? 'selected' : ''; ?>>All Destinations</option>
                        <?php 
                        $destinations_result->data_seek(0);
                        while ($dest_row = $destinations_result->fetch_assoc()): 
                        ?>
                            <option value="<?php echo htmlspecialchars($dest_row['destination']); ?>" <?php echo $filter_destination === $dest_row['destination'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($dest_row['destination']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors">
                        <span class="material-symbols-outlined text-[18px] align-middle">search</span>
                        Search
                    </button>
                    <?php if ($search || $filter_type !== 'all' || $filter_destination !== 'all'): ?>
                        <a href="manage_tours.php" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
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

        <!-- Add/Edit Tour Form -->
        <div id="tourForm" style="display: <?php echo $editing_tour ? 'block' : 'none'; ?>;" class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="<?php echo $editing_tour ? 'update' : 'add'; ?>">
                <?php if ($editing_tour): ?>
                    <input type="hidden" name="package_id" value="<?php echo $editing_tour['id']; ?>">
                <?php endif; ?>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4"><?php echo $editing_tour ? 'Edit Tour Package' : 'Add New Tour Package'; ?></h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Package Title</label>
                        <input type="text" name="title" required value="<?php echo htmlspecialchars($editing_tour['title'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Destination</label>
                        <input type="text" name="destination" required value="<?php echo htmlspecialchars($editing_tour['destination'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Package Type</label>
                        <select name="package_type" required class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="Adventure" <?php echo ($editing_tour['package_type'] ?? '') == 'Adventure' ? 'selected' : ''; ?>>Adventure</option>
                            <option value="Family" <?php echo ($editing_tour['package_type'] ?? '') == 'Family' ? 'selected' : ''; ?>>Family</option>
                            <option value="Romantic" <?php echo ($editing_tour['package_type'] ?? '') == 'Romantic' ? 'selected' : ''; ?>>Romantic</option>
                            <option value="Cultural" <?php echo ($editing_tour['package_type'] ?? '') == 'Cultural' ? 'selected' : ''; ?>>Cultural</option>
                            <option value="Luxury" <?php echo ($editing_tour['package_type'] ?? '') == 'Luxury' ? 'selected' : ''; ?>>Luxury</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Duration (Days)</label>
                        <input type="number" name="duration_days" required value="<?php echo htmlspecialchars($editing_tour['duration_days'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Duration (Nights)</label>
                        <input type="number" name="duration_nights" required value="<?php echo htmlspecialchars($editing_tour['duration_nights'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Rating (0-5)</label>
                        <input type="number" step="0.1" name="rating" min="0" max="5" value="<?php echo htmlspecialchars($editing_tour['rating'] ?? '0'); ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Available Spots</label>
                        <input type="number" name="available_spots" required value="<?php echo htmlspecialchars($editing_tour['available_spots'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Price ($)</label>
                        <input type="number" step="0.01" name="price" required value="<?php echo htmlspecialchars($editing_tour['price'] ?? ''); ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Original Price ($) - for discount</label>
                        <input type="number" step="0.01" name="original_price" value="<?php echo htmlspecialchars($editing_tour['original_price'] ?? '0'); ?>" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Inclusions (comma-separated)</label>
                        <input type="text" name="inclusions" value="<?php echo htmlspecialchars($editing_tour['inclusions'] ?? ''); ?>" placeholder="Flight Included, Hotel, Breakfast, Guide" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Description</label>
                        <textarea name="description" rows="4" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"><?php echo htmlspecialchars($editing_tour['description'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors"><?php echo $editing_tour ? 'Update Package' : 'Add Package'; ?></button>
                    <button type="button" onclick="toggleTourForm()" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">Cancel</button>
                </div>
            </form>
        </div>

        <!-- Tours Table -->
        <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Destination</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Spots</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <?php 
                        $has_tours = false;
                        while ($tour = $tours->fetch_assoc()): 
                            $has_tours = true;
                        ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars($tour['title']); ?></td>
                                <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($tour['destination']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400"><?php echo $tour['duration_days']; ?> Days / <?php echo $tour['duration_nights']; ?> Nights</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($tour['package_type']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900 dark:text-white">$<?php echo number_format($tour['price'], 2); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400"><?php echo $tour['available_spots']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center gap-2">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="package_id" value="<?php echo $tour['id']; ?>">
                                            <button type="submit" class="text-primary hover:text-primary-dark transition-colors" title="Edit">
                                                <span class="material-symbols-outlined text-[20px]">edit</span>
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this tour package?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="package_id" value="<?php echo $tour['id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-700 transition-colors" title="Delete">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if (!$has_tours): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <span class="material-symbols-outlined text-4xl mb-2 block">luggage</span>
                                    No tour packages found. Add your first package above.
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
function toggleTourForm() {
    const form = document.getElementById('tourForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
    if (form.style.display === 'none') {
        window.location.href = 'manage_tours.php';
    }
}
</script>
<?php include '../includes/footer.php'; ?>
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
