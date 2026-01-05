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
$price_min = $_GET['price_min'] ?? '';
$price_max = $_GET['price_max'] ?? '';
$duration = $_GET['duration'] ?? '';

// Build query
$where = [];
$params = [];
$types = '';

if ($package_type) {
    $where[] = "package_type = ?";
    $params[] = $package_type;
    $types .= 's';
}
if ($price_min) {
    $where[] = "price >= ?";
    $params[] = $price_min;
    $types .= 'd';
}
if ($price_max) {
    $where[] = "price <= ?";
    $params[] = $price_max;
    $types .= 'd';
}
if ($duration) {
    if ($duration === 'short') {
        $where[] = "duration_days <= 3";
    } elseif ($duration === 'medium') {
        $where[] = "duration_days BETWEEN 4 AND 7";
    } elseif ($duration === 'long') {
        $where[] = "duration_days >= 8";
    }
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
$query = "SELECT * FROM tour_packages $where_clause ORDER BY rating DESC, price ASC";

$packages = [];
if (!empty($params)) {
    $stmt = $conn->prepare($query);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $packages = $stmt->get_result();
} else {
    $packages = $conn->query("SELECT * FROM tour_packages ORDER BY rating DESC, price ASC");
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
                <select class="sort-dropdown">
                    <option>Recommended</option>
                    <option>Price: Low to High</option>
                    <option>Price: High to Low</option>
                    <option>Rating</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="packages-content">
        <aside class="filters-sidebar">
            <div class="filters-header">
                <h3>Filters</h3>
                <a href="?" class="reset-link">Reset</a>
            </div>
            
            <div class="filter-group">
                <h4>Price Range</h4>
                <div class="price-range-slider">
                    <input type="range" min="500" max="2500" value="500" class="slider" id="packagePriceMin">
                    <input type="range" min="500" max="2500" value="2500" class="slider" id="packagePriceMax">
                    <div class="price-labels">
                        <span>$500</span>
                        <span>$2500</span>
                    </div>
                </div>
            </div>
            
            <div class="filter-group">
                <h4>Duration</h4>
                <label class="filter-checkbox">
                    <input type="checkbox">
                    <span>Up to 3 days</span>
                </label>
                <label class="filter-checkbox">
                    <input type="checkbox" checked>
                    <span>4-7 days</span>
                </label>
                <label class="filter-checkbox">
                    <input type="checkbox">
                    <span>8-14 days</span>
                </label>
                <label class="filter-checkbox">
                    <input type="checkbox">
                    <span>15+ days</span>
                </label>
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
                    <input type="radio" name="rating" value="5">
                    <span>★★★★★ 5 Stars</span>
                </label>
                <label class="filter-checkbox">
                    <input type="radio" name="rating" value="4">
                    <span>★★★★★ 4 Stars & up</span>
                </label>
                <label class="filter-checkbox">
                    <input type="radio" name="rating" value="3">
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
    
    <div class="pagination">
        <button class="page-btn"><i class="fas fa-chevron-left"></i></button>
        <button class="page-btn active">1</button>
        <button class="page-btn">2</button>
        <button class="page-btn">3</button>
        <span>...</span>
        <button class="page-btn">8</button>
        <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
    </div>
</main>
<?php include '../includes/footer.php'; ?>

