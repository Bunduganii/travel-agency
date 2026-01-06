<?php
/**
<<<<<<< HEAD
 * Reserve Hotel Page
=======
 * Reserve Hotel Page - Redesigned with Tailwind CSS
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
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
<<<<<<< HEAD
$location = $_GET['location'] ?? '';
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';
$guests = $_GET['guests'] ?? 2;
=======
$location = $_GET['location'] ?? 'Tokyo, Japan';
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';
$guests = $_GET['guests'] ?? 2;
$rooms = $_GET['rooms'] ?? 1;
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd

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

<<<<<<< HEAD
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
=======
$hotel_count = mysqli_num_rows($hotels);
$nights = ($check_in && $check_out) ? (strtotime($check_out) - strtotime($check_in)) / 86400 : 3;

include '../includes/header.php';
?>
<main class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark">
    <!-- Search Modification Header -->
    <div class="bg-white dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700 py-6 px-6 lg:px-10">
        <div class="max-w-7xl mx-auto w-full">
            <!-- Breadcrumbs -->
            <div class="flex flex-wrap gap-2 mb-4 text-sm">
                <a class="text-slate-500 hover:text-primary" href="../index.php">Home</a>
                <span class="text-slate-500">/</span>
                <a class="text-slate-500 hover:text-primary" href="reserve_hotel.php">Hotel Search</a>
                <span class="text-slate-500">/</span>
                <span class="text-slate-900 dark:text-white font-medium"><?php echo htmlspecialchars($location); ?> Results</span>
            </div>
            
            <!-- Compact Search Bar -->
            <form method="GET" class="flex flex-col lg:flex-row gap-4 items-end bg-slate-50 dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 flex-1 w-full">
                    <!-- Location -->
                    <label class="flex flex-col gap-1.5">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Location</span>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary">location_on</span>
                            <input class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none" name="location" value="<?php echo htmlspecialchars($location); ?>"/>
                        </div>
                    </label>
                    
                    <!-- Dates -->
                    <label class="flex flex-col gap-1.5">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Dates</span>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary">calendar_month</span>
                            <input class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none" type="text" value="<?php echo $check_in && $check_out ? date('M d', strtotime($check_in)) . ' - ' . date('M d', strtotime($check_out)) : 'Select dates'; ?>" readonly/>
                            <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>">
                            <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>">
                        </div>
                    </label>
                    
                    <!-- Guests -->
                    <label class="flex flex-col gap-1.5">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Guests</span>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary">group</span>
                            <input class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none" type="text" value="<?php echo $guests; ?> Adults, <?php echo $rooms; ?> Room" readonly/>
                            <input type="hidden" name="guests" value="<?php echo $guests; ?>">
                            <input type="hidden" name="rooms" value="<?php echo $rooms; ?>">
                        </div>
                    </label>
                    
                    <!-- Search Button -->
                    <div class="flex items-end">
                        <button type="submit" class="w-full h-[42px] bg-primary hover:bg-sky-500 text-white rounded-lg font-bold text-sm transition-colors flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">search</span>
                            Update Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Main Content Area -->
    <div class="max-w-7xl mx-auto w-full px-6 lg:px-10 py-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Sidebar Filters -->
        <aside class="lg:col-span-3 space-y-8">
            <!-- Map Toggle -->
            <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden group cursor-pointer">
                <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-blue-600/20 opacity-60 group-hover:opacity-70 transition-opacity"></div>
                <div class="relative z-10 flex items-center justify-center h-24">
                    <button class="bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-4 py-2 rounded-lg font-bold text-sm shadow-lg flex items-center gap-2 hover:scale-105 transition-transform">
                        <span class="material-symbols-outlined text-primary">map</span>
                        Show on Map
                    </button>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Filters</h3>
                    <button class="text-xs font-medium text-primary hover:text-sky-500">Reset All</button>
                </div>
                
                <!-- Price Range -->
                <div class="mb-8">
                    <h4 class="text-sm font-semibold mb-4 text-slate-900 dark:text-gray-200">Price Range (per night)</h4>
                    <div class="relative h-1.5 w-full bg-slate-200 dark:bg-slate-700 rounded-full mb-4">
                        <div class="absolute left-1/4 right-1/4 h-full bg-primary rounded-full"></div>
                        <div class="absolute left-1/4 top-1/2 -translate-y-1/2 size-4 bg-white border-2 border-primary rounded-full shadow cursor-pointer hover:scale-110 transition-transform"></div>
                        <div class="absolute right-1/4 top-1/2 -translate-y-1/2 size-4 bg-white border-2 border-primary rounded-full shadow cursor-pointer hover:scale-110 transition-transform"></div>
                    </div>
                    <div class="flex justify-between text-sm text-slate-500 font-medium">
                        <span>$50</span>
                        <span>$800+</span>
                    </div>
                </div>
                
                <!-- Star Rating -->
                <div class="mb-8">
                    <h4 class="text-sm font-semibold mb-3 text-slate-900 dark:text-gray-200">Star Rating</h4>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input checked class="size-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" type="checkbox"/>
                            <div class="flex text-yellow-400">
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                            </div>
                            <span class="text-xs text-slate-500">(120)</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input checked class="size-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" type="checkbox"/>
                            <div class="flex text-yellow-400">
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                            </div>
                            <span class="text-xs text-slate-500">(85)</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input class="size-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" type="checkbox"/>
                            <div class="flex text-yellow-400">
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                            </div>
                            <span class="text-xs text-slate-500">(42)</span>
                        </label>
                    </div>
                </div>
                
                <!-- Amenities -->
                <div class="mb-8">
                    <h4 class="text-sm font-semibold mb-3 text-slate-900 dark:text-gray-200">Amenities</h4>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input class="size-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" type="checkbox"/>
                            <span class="text-sm text-slate-600 dark:text-slate-400">Free WiFi</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input class="size-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" type="checkbox"/>
                            <span class="text-sm text-slate-600 dark:text-slate-400">Breakfast Included</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input class="size-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" type="checkbox"/>
                            <span class="text-sm text-slate-600 dark:text-slate-400">Pool</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input class="size-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" type="checkbox"/>
                            <span class="text-sm text-slate-600 dark:text-slate-400">Gym / Fitness</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input class="size-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" type="checkbox"/>
                            <span class="text-sm text-slate-600 dark:text-slate-400">Spa</span>
                        </label>
                    </div>
                </div>
                
                <!-- Property Type -->
                <div>
                    <h4 class="text-sm font-semibold mb-3 text-slate-900 dark:text-gray-200">Property Type</h4>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input checked class="size-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" type="checkbox"/>
                            <span class="text-sm text-slate-600 dark:text-slate-400">Hotel</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input class="size-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" type="checkbox"/>
                            <span class="text-sm text-slate-600 dark:text-slate-400">Resort</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input class="size-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" type="checkbox"/>
                            <span class="text-sm text-slate-600 dark:text-slate-400">Apartment</span>
                        </label>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Search Results -->
        <div class="lg:col-span-9 flex flex-col gap-6">
            <!-- Sort & Count Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-2">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                    <?php echo $hotel_count; ?> Hotels found in <span class="text-primary"><?php echo htmlspecialchars($location); ?></span>
                </h3>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-slate-500 whitespace-nowrap">Sort by:</span>
                    <select class="form-select text-sm font-medium border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg py-2 pl-3 pr-8 focus:ring-primary/20 focus:border-primary cursor-pointer">
                        <option>Recommended</option>
                        <option>Price (Low to High)</option>
                        <option>Price (High to Low)</option>
                        <option>Top Rated</option>
                    </select>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-900/20 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg text-sm">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Hotel Cards -->
            <div class="space-y-4">
                <?php 
                $count = 0;
                while ($hotel = $hotels->fetch_assoc()): 
                    $count++;
                    if ($count > 10) break;
                    $amenities = !empty($hotel['amenities']) ? explode(',', $hotel['amenities']) : ['Free WiFi', 'Breakfast Included', 'Pool'];
                    $total_price = $hotel['price_per_night'] * $nights;
                ?>
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden group flex flex-col sm:flex-row">
                    <!-- Image -->
                    <div class="sm:w-72 h-48 sm:h-auto relative shrink-0">
                        <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-blue-600/20"></div>
                        <?php if ($count === 1): ?>
                        <div class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm text-slate-900 text-xs font-bold px-2 py-1 rounded">
                            Recommended
                        </div>
                        <?php elseif ($count === 3): ?>
                        <div class="absolute top-3 left-3 bg-purple-100 text-purple-700 text-xs font-bold px-2 py-1 rounded border border-purple-200">
                            Top Choice
                        </div>
                        <?php endif; ?>
                        <div class="absolute bottom-3 right-3 bg-black/50 backdrop-blur-sm text-white p-1.5 rounded-full hover:bg-black/70 cursor-pointer transition-colors">
                            <span class="material-symbols-outlined text-[18px]">favorite</span>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-5 flex flex-col flex-1 justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-1">
                                <div>
                                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-1 group-hover:text-primary transition-colors"><?php echo htmlspecialchars($hotel['name']); ?></h3>
                                    <div class="flex items-center gap-1 text-sm text-slate-500 dark:text-slate-400 mb-2">
                                        <span class="material-symbols-outlined text-[16px] text-primary">location_on</span>
                                        <span><?php echo htmlspecialchars($hotel['location'] ?? $hotel['city']); ?> • <?php echo $check_in ? '0.5km' : ''; ?> from center</span>
                                        <a class="text-primary font-medium hover:underline ml-1" href="#">Show on map</a>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end shrink-0">
                                    <div class="flex items-center gap-1 mb-1">
                                        <div class="bg-primary text-white text-sm font-bold px-1.5 py-0.5 rounded"><?php echo $hotel['star_rating']; ?>.<?php echo $count == 1 ? '8' : ($count == 2 ? '5' : '9'); ?></div>
                                        <span class="text-xs font-medium text-slate-600 dark:text-slate-300"><?php echo $count == 1 ? 'Excellent' : ($count == 2 ? 'Very Good' : 'Exceptional'); ?></span>
                                    </div>
                                    <span class="text-xs text-slate-400"><?php echo $count == 1 ? '1,204' : ($count == 2 ? '856' : '420'); ?> reviews</span>
                                </div>
                            </div>
                            
                            <!-- Amenities Tags -->
                            <div class="flex flex-wrap gap-2 mb-4">
                                <?php foreach (array_slice($amenities, 0, 3) as $amenity): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 text-xs font-medium border border-blue-100 dark:border-blue-800">
                                    <span class="material-symbols-outlined text-[14px]">wifi</span> <?php echo htmlspecialchars(trim($amenity)); ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Pricing & CTA -->
                        <div class="flex flex-col sm:flex-row justify-between items-end border-t border-slate-100 dark:border-slate-700 pt-4 mt-2">
                            <div class="mb-3 sm:mb-0">
                                <?php if ($count === 1): ?>
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="bg-red-100 text-red-600 text-[10px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wide">15% OFF</span>
                                    <span class="text-xs text-slate-400 line-through">$<?php echo number_format($hotel['price_per_night'] * 1.18, 0); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-2xl font-bold text-slate-900 dark:text-white">$<?php echo number_format($hotel['price_per_night'], 0); ?></span>
                                    <span class="text-sm text-slate-500">/ night</span>
                                </div>
                                <div class="text-xs text-slate-400 mt-0.5">Total for <?php echo $nights; ?> nights: $<?php echo number_format($total_price, 0); ?></div>
                                <div class="text-xs text-blue-600 dark:text-blue-400 mt-1 font-medium">Earn $<?php echo number_format($total_price * 0.045, 0); ?> commission</div>
                            </div>
                            <div class="flex gap-3 w-full sm:w-auto">
                                <button class="flex-1 sm:flex-initial px-6 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white font-bold text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                    Details
                                </button>
                                <form method="POST" class="flex-1 sm:flex-initial">
                                    <input type="hidden" name="hotel_id" value="<?php echo $hotel['id']; ?>">
                                    <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>">
                                    <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>">
                                    <input type="hidden" name="guests" value="<?php echo htmlspecialchars($guests); ?>">
                                    <input type="hidden" name="rooms" value="<?php echo $rooms; ?>">
                                    <button type="submit" name="book_hotel" class="w-full px-6 py-2.5 rounded-lg bg-primary hover:bg-sky-500 text-white font-bold text-sm shadow-sm hover:shadow transition-all flex items-center justify-center gap-2">
                                        Book Now
                                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; 
                if ($count === 0): ?>
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-8 text-center">
                    <span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600 mb-4">hotel</span>
                    <p class="text-slate-500 dark:text-slate-400">No hotels found. Try adjusting your search criteria.</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <div class="flex justify-center mt-4">
                <nav class="flex items-center gap-1">
                    <a class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-primary hover:border-primary transition-colors" href="#">
                        <span class="material-symbols-outlined text-sm">chevron_left</span>
                    </a>
                    <a class="w-9 h-9 flex items-center justify-center rounded-lg bg-primary text-white font-bold text-sm" href="#">1</a>
                    <a class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:text-primary hover:border-primary transition-colors font-medium text-sm" href="#">2</a>
                    <a class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:text-primary hover:border-primary transition-colors font-medium text-sm" href="#">3</a>
                    <span class="px-2 text-slate-400">...</span>
                    <a class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:text-primary hover:border-primary transition-colors font-medium text-sm" href="#">8</a>
                    <a class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-primary hover:border-primary transition-colors" href="#">
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                    </a>
                </nav>
            </div>
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
<<<<<<< HEAD

=======
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
