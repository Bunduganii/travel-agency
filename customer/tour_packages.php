<?php
/**
 * Tour Packages Page
 * Customer page to browse and book tour packages
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireCustomer();

$page_title = 'Tour Packages';
$message = '';
$message_type = '';

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_tour'])) {
    $package_id = intval($_POST['package_id']);
    $travel_date = $_POST['travel_date'];
    $travelers = intval($_POST['travelers']);
    
    // Get package details
    $stmt = $conn->prepare("SELECT * FROM tour_packages WHERE id = ?");
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $package = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($package && $package['available_spots'] >= $travelers) {
        $total_price = $package['price'] * $travelers;
        
        // Create booking
        $stmt = $conn->prepare("INSERT INTO tour_bookings (user_id, package_id, travel_date, travelers, total_price) VALUES (?, ?, ?, ?, ?)");
        $user_id = getUserId();
        $stmt->bind_param("iisid", $user_id, $package_id, $travel_date, $travelers, $total_price);
        
        if ($stmt->execute()) {
            $booking_id = $conn->insert_id;
            header("Location: payment.php?type=tour&id=" . $booking_id);
            exit();
        }
        $stmt->close();
    } else {
        $message = 'Not enough spots available.';
        $message_type = 'error';
    }
}

// Get filter parameters
$package_type = $_GET['type'] ?? '';
$price_min = isset($_GET['price_min']) ? floatval($_GET['price_min']) : 0;
$price_max = isset($_GET['price_max']) ? floatval($_GET['price_max']) : 10000;
$duration_filter = isset($_GET['duration']) ? explode(',', $_GET['duration']) : [];
$rating_filter = $_GET['rating'] ?? '';
$sort_by = $_GET['sort'] ?? 'recommended';

// Build query
$where = [];
$params = [];
$types = '';

if ($package_type) {
    $where[] = "package_type = ?";
    $params[] = $package_type;
    $types .= 's';
}

// Price filter
if ($price_min > 0 || $price_max < 10000) {
    $where[] = "price >= ? AND price <= ?";
    $params[] = $price_min;
    $params[] = $price_max;
    $types .= 'dd';
}

// Duration filter
if (!empty($duration_filter) && !in_array('all', $duration_filter)) {
    $duration_conditions = [];
    foreach ($duration_filter as $duration) {
        if ($duration === 'short') {
            $duration_conditions[] = "duration_days <= 3";
        } elseif ($duration === 'medium') {
            $duration_conditions[] = "duration_days BETWEEN 4 AND 7";
        } elseif ($duration === 'long') {
            $duration_conditions[] = "duration_days BETWEEN 8 AND 14";
        } elseif ($duration === 'extended') {
            $duration_conditions[] = "duration_days >= 15";
        }
    }
    if (!empty($duration_conditions)) {
        $where[] = "(" . implode(" OR ", $duration_conditions) . ")";
    }
}

// Rating filter
if ($rating_filter && is_numeric($rating_filter)) {
    $where[] = "rating >= ?";
    $params[] = floatval($rating_filter);
    $types .= 'd';
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Sort order
$order_by = "ORDER BY ";
switch ($sort_by) {
    case 'price_low':
        $order_by .= "price ASC, rating DESC";
        break;
    case 'price_high':
        $order_by .= "price DESC, rating DESC";
        break;
    case 'rating':
        $order_by .= "rating DESC, price ASC";
        break;
    case 'recommended':
    default:
        $order_by .= "rating DESC, price ASC";
        break;
}

$query = "SELECT * FROM tour_packages $where_clause $order_by";

$packages = [];
if (!empty($params)) {
    $stmt = $conn->prepare($query);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $packages = $stmt->get_result();
} else {
    $packages = $conn->query($query);
}

include '../includes/header.php';
?>
<main class="packages-page">
            <div class="packages-header">
        <div class="breadcrumbs">Home / Tours / All Packages</div>
        <div class="packages-title-section">
            <div>
                <h1>Discover Your Next Adventure</h1>
                <p>Browse our curated selection of premium holiday packages designed for unforgettable memories.</p>
            </div>
            <div class="sort-wrapper">
                <label>Sort by:</label>
                <select id="tourSort" onchange="applyTourSort()" class="sort-dropdown">
                    <option value="recommended" <?php echo $sort_by === 'recommended' ? 'selected' : ''; ?>>Recommended</option>
                    <option value="price_low" <?php echo $sort_by === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_high" <?php echo $sort_by === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="rating" <?php echo $sort_by === 'rating' ? 'selected' : ''; ?>>Rating</option>
                </select>
            </div>
        </div>
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
    </div>
    
    <div class="packages-content">
        <aside class="filters-sidebar">
            <div class="filters-header">
                <h3>Filters</h3>
                <a href="tour_packages.php" class="reset-link">Reset</a>
            </div>
            
            <div class="filter-group">
                <h4>Price Range</h4>
                <div class="space-y-2 mb-2">
                    <div class="flex items-center gap-2">
                        <label class="text-xs text-slate-500 w-16">Min:</label>
                        <input type="number" id="packagePriceMin" min="0" max="10000" step="100" value="<?php echo $price_min; ?>" class="flex-1 px-2 py-1 text-sm border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800" onchange="applyTourFilters()">
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs text-slate-500 w-16">Max:</label>
                        <input type="number" id="packagePriceMax" min="0" max="10000" step="100" value="<?php echo $price_max; ?>" class="flex-1 px-2 py-1 text-sm border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800" onchange="applyTourFilters()">
                    </div>
                </div>
                <div class="flex justify-between text-xs text-slate-500">
                    <span>$0</span>
                    <span>$10,000+</span>
                </div>
            </div>
            
            <div class="filter-group">
                <h4>Duration</h4>
                <label class="filter-checkbox">
                    <input type="checkbox" name="duration_filter" value="short" class="duration-filter" <?php echo in_array('short', $duration_filter) || empty($duration_filter) ? 'checked' : ''; ?> onchange="applyTourFilters()">
                    <span>Up to 3 days</span>
                </label>
                <label class="filter-checkbox">
                    <input type="checkbox" name="duration_filter" value="medium" class="duration-filter" <?php echo in_array('medium', $duration_filter) || empty($duration_filter) ? 'checked' : ''; ?> onchange="applyTourFilters()">
                    <span>4-7 days</span>
                </label>
                <label class="filter-checkbox">
                    <input type="checkbox" name="duration_filter" value="long" class="duration-filter" <?php echo in_array('long', $duration_filter) || empty($duration_filter) ? 'checked' : ''; ?> onchange="applyTourFilters()">
                    <span>8-14 days</span>
                </label>
                <label class="filter-checkbox">
                    <input type="checkbox" name="duration_filter" value="extended" class="duration-filter" <?php echo in_array('extended', $duration_filter) || empty($duration_filter) ? 'checked' : ''; ?> onchange="applyTourFilters()">
                    <span>15+ days</span>
                </label>
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
            </div>
            
            <div class="filter-group">
                <h4>Trip Type</h4>
                <div class="type-buttons">
                    <a href="?type=Adventure" class="type-btn <?php echo $package_type === 'Adventure' ? 'active' : ''; ?>">Adventure</a>
                    <a href="?type=Family" class="type-btn <?php echo $package_type === 'Family' ? 'active' : ''; ?>">Family</a>
                    <a href="?type=Romantic" class="type-btn <?php echo $package_type === 'Romantic' ? 'active' : ''; ?>">Romantic</a>
                    <a href="?type=Cultural" class="type-btn <?php echo $package_type === 'Cultural' ? 'active' : ''; ?>">Cultural</a>
                    <a href="?type=Luxury" class="type-btn <?php echo $package_type === 'Luxury' ? 'active' : ''; ?>">Luxury</a>
                </div>
            </div>
            
            <div class="filter-group">
                <h4>Rating</h4>
                <label class="filter-checkbox">
                    <input type="radio" name="rating" value="" class="rating-filter" <?php echo empty($rating_filter) ? 'checked' : ''; ?> onchange="applyTourFilters()">
                    <span>All Ratings</span>
                </label>
                <label class="filter-checkbox">
                    <input type="radio" name="rating" value="5" class="rating-filter" <?php echo $rating_filter === '5' ? 'checked' : ''; ?> onchange="applyTourFilters()">
                    <span>★★★★★ 5 Stars</span>
                </label>
                <label class="filter-checkbox">
                    <input type="radio" name="rating" value="4" class="rating-filter" <?php echo $rating_filter === '4' ? 'checked' : ''; ?> onchange="applyTourFilters()">
                    <span>★★★★★ 4 Stars & up</span>
                </label>
                <label class="filter-checkbox">
                    <input type="radio" name="rating" value="3" class="rating-filter" <?php echo $rating_filter === '3' ? 'checked' : ''; ?> onchange="applyTourFilters()">
                    <span>★★★★★ 3 Stars & up</span>
                </label>
            </div>
        </aside>
        
        <div class="packages-grid">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> slide-in">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php while ($package = $packages->fetch_assoc()): 
                $inclusions = explode(',', $package['inclusions']);
            ?>
            <div class="package-card">
                <div class="package-image">
                    <img src="https://images.unsplash.com/photo-1539650116574-75c0c6d73a6e?w=400" alt="<?php echo htmlspecialchars($package['title']); ?>">
                    <span class="package-badge"><?php echo htmlspecialchars($package['package_type']); ?></span>
                </div>
                
                <div class="package-info">
                    <p class="package-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($package['destination']); ?>
                    </p>
                    <h3><?php echo htmlspecialchars($package['title']); ?></h3>
                    
                    <div class="package-rating">
                        <i class="fas fa-star"></i>
                        <span><?php echo number_format($package['rating'], 1); ?></span>
                    </div>
                    
                    <p class="package-description"><?php echo htmlspecialchars(substr($package['description'], 0, 80)); ?>...</p>
                    
                    <div class="package-features">
                        <?php 
                        $feature_icons = ['calendar', 'plane', 'hotel', 'utensils'];
                        $feature_count = 0;
                        foreach (array_slice($inclusions, 0, 4) as $inclusion): 
                            $icon = $feature_icons[$feature_count] ?? 'check';
                            $feature_count++;
                        ?>
                            <span class="feature-tag">
                                <i class="fas fa-<?php echo $icon; ?>"></i>
                                <?php echo htmlspecialchars(trim($inclusion)); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="package-price">
                        <?php if ($package['original_price'] > 0 && $package['original_price'] > $package['price']): ?>
                            <span class="original-price">$<?php echo number_format($package['original_price'], 0); ?></span>
                        <?php endif; ?>
                        <strong class="current-price">$<?php echo number_format($package['price'], 0); ?> / person</strong>
                    </div>
                    
                    <button type="button" class="btn btn-primary btn-book-package" onclick="document.getElementById('bookForm<?php echo $package['id']; ?>').submit();">
                        Book Now
                    </button>
                    <form method="POST" id="bookForm<?php echo $package['id']; ?>" style="display: none;">
                        <input type="hidden" name="package_id" value="<?php echo $package['id']; ?>">
                        <input type="date" name="travel_date" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                        <input type="number" name="travelers" min="1" value="1" required>
                        <button type="submit" name="book_tour">Submit</button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="mt-8 flex justify-center">
        <nav class="flex items-center gap-2">
            <button onclick="navigatePage(-1)" class="w-10 h-10 flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-200 bg-white dark:bg-white text-slate-600 dark:text-slate-600 hover:border-primary hover:text-primary transition-colors shadow-sm">
                <span class="material-symbols-outlined text-sm">chevron_left</span>
            </button>
            <button onclick="goToPage(1)" class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary hover:bg-primary text-white font-bold text-sm shadow-sm">1</button>
            <button onclick="goToPage(2)" class="w-10 h-10 flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-200 bg-white dark:bg-white text-slate-700 dark:text-slate-700 hover:border-primary hover:text-primary transition-colors font-medium text-sm shadow-sm">2</button>
            <button onclick="goToPage(3)" class="w-10 h-10 flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-200 bg-white dark:bg-white text-slate-700 dark:text-slate-700 hover:border-primary hover:text-primary transition-colors font-medium text-sm shadow-sm">3</button>
            <span class="px-2 text-slate-600 dark:text-slate-600 font-medium text-sm">...</span>
            <button onclick="goToPage(8)" class="w-10 h-10 flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-200 bg-white dark:bg-white text-slate-700 dark:text-slate-700 hover:border-primary hover:text-primary transition-colors font-medium text-sm shadow-sm">8</button>
            <button onclick="navigatePage(1)" class="w-10 h-10 flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-200 bg-white dark:bg-white text-slate-600 dark:text-slate-600 hover:border-primary hover:text-primary transition-colors shadow-sm">
                <span class="material-symbols-outlined text-sm">chevron_right</span>
            </button>
        </nav>
    </div>
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
</main>

<script>
function applyTourFilters() {
    const url = new URL(window.location.href);
    
    // Price filter
    const minPrice = document.getElementById('packagePriceMin').value || 0;
    const maxPrice = document.getElementById('packagePriceMax').value || 10000;
    url.searchParams.set('price_min', minPrice);
    url.searchParams.set('price_max', maxPrice);
    
    // Duration filter
    const durations = Array.from(document.querySelectorAll('.duration-filter:checked')).map(cb => cb.value);
    if (durations.length > 0) {
        url.searchParams.set('duration', durations.join(','));
    } else {
        url.searchParams.delete('duration');
    }
    
    // Rating filter
    const rating = document.querySelector('.rating-filter:checked')?.value || '';
    if (rating) {
        url.searchParams.set('rating', rating);
    } else {
        url.searchParams.delete('rating');
    }
    
    window.location.href = url.toString();
}

function applyTourSort() {
    const url = new URL(window.location.href);
    const sortValue = document.getElementById('tourSort').value;
    url.searchParams.set('sort', sortValue);
    window.location.href = url.toString();
}

// Pagination functions
function getCurrentPage() {
    const urlParams = new URLSearchParams(window.location.search);
    return parseInt(urlParams.get('page')) || 1;
}

function navigatePage(direction) {
    const currentPage = getCurrentPage();
    const newPage = Math.max(1, currentPage + direction);
    goToPage(newPage);
}

function goToPage(page) {
    const url = new URL(window.location.href);
    url.searchParams.set('page', page);
    window.location.href = url.toString();
}
</script>

<?php include '../includes/footer.php'; ?>

