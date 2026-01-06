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
<<<<<<< HEAD
=======
$editing_tour = null;
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
<<<<<<< HEAD
        if ($_POST['action'] === 'add') {
=======
        if ($_POST['action'] === 'add' || $_POST['action'] === 'update') {
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
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
            
<<<<<<< HEAD
            $stmt = $conn->prepare("INSERT INTO tour_packages (title, destination, duration_days, duration_nights, price, original_price, description, inclusions, package_type, rating, available_spots) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiidddssdi", $title, $destination, $duration_days, $duration_nights, $price, $original_price, $description, $inclusions, $package_type, $rating, $available_spots);
            
            if ($stmt->execute()) {
                $message = 'Tour package added successfully!';
                $message_type = 'success';
            } else {
                $message = 'Error adding tour package.';
=======
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
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
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
<<<<<<< HEAD
=======
        } elseif ($_POST['action'] === 'edit') {
            $id = intval($_POST['package_id']);
            $stmt = $conn->prepare("SELECT * FROM tour_packages WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $editing_tour = $result->fetch_assoc();
            $stmt->close();
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
        }
    }
}

// Get all tour packages
$tours = $conn->query("SELECT * FROM tour_packages ORDER BY created_at DESC");

include '../includes/header.php';
?>
<<<<<<< HEAD
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
=======
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
<<<<<<< HEAD
}
</script>
<?php include '../includes/footer.php'; ?>

=======
    if (form.style.display === 'none') {
        window.location.href = 'manage_tours.php';
    }
}
</script>
<?php include '../includes/footer.php'; ?>
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
