<?php
/**
 * Reserve Hotel Page
 * Reserve Hotel Page - Redesigned with Tailwind CSS
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
    $hotel_id = intval($_POST['hotel_id'] ?? 0);
    $check_in = trim($_POST['check_in'] ?? '');
    $check_out = trim($_POST['check_out'] ?? '');
    $guests = intval($_POST['guests'] ?? 2);
    $rooms = intval($_POST['rooms'] ?? 1);
    
    // Validation
    if (!$hotel_id) {
        $message = 'Please select a hotel.';
        $message_type = 'error';
    } elseif (!$check_in || !$check_out) {
        $message = 'Please select check-in and check-out dates.';
        $message_type = 'error';
    } elseif (strtotime($check_in) >= strtotime($check_out)) {
        $message = 'Check-out date must be after check-in date.';
        $message_type = 'error';
    } elseif ($guests < 1 || $rooms < 1) {
        $message = 'Please enter valid number of guests and rooms.';
        $message_type = 'error';
    } else {
        // Get hotel details
        $stmt = $conn->prepare("SELECT * FROM hotels WHERE id = ?");
        $stmt->bind_param("i", $hotel_id);
        $stmt->execute();
        $hotel = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($hotel) {
            if ($hotel['available_rooms'] >= $rooms) {
                $nights = (strtotime($check_out) - strtotime($check_in)) / 86400;
                $total_price = $hotel['price_per_night'] * $nights * $rooms;
                
                // Create reservation
                $stmt = $conn->prepare("INSERT INTO hotel_reservations (user_id, hotel_id, check_in, check_out, guests, rooms, total_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
                $user_id = getUserId();
                $stmt->bind_param("iissiid", $user_id, $hotel_id, $check_in, $check_out, $guests, $rooms, $total_price);
                
                if ($stmt->execute()) {
                    $booking_id = $conn->insert_id;
                    header("Location: payment.php?type=hotel&id=" . $booking_id);
                    exit();
                } else {
                    $message = 'Error creating reservation. Please try again.';
                    $message_type = 'error';
                }
                $stmt->close();
            } else {
                $message = 'Not enough rooms available.';
                $message_type = 'error';
            }
        } else {
            $message = 'Hotel not found.';
            $message_type = 'error';
        }
    }
}

// Get search parameters
$location = $_GET['location'] ?? 'Tokyo, Japan';
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';
$guests = $_GET['guests'] ?? 2;
$rooms = $_GET['rooms'] ?? 1;

// Set default dates if not provided (tomorrow for check-in, 3 days later for check-out)
if (empty($check_in)) {
    $check_in = date('Y-m-d', strtotime('+1 day'));
}
if (empty($check_out)) {
    $check_out = date('Y-m-d', strtotime('+3 days'));
}

// Get filter parameters
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 1000;
$star_ratings = isset($_GET['stars']) ? explode(',', $_GET['stars']) : [];

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

// Price filter
if ($min_price > 0 || $max_price < 1000) {
    $where[] = "price_per_night >= ? AND price_per_night <= ?";
    $params[] = $min_price;
    $params[] = $max_price;
    $types .= 'dd';
} else {
    // Default range if not set
    $min_price = 0;
    $max_price = 500;
}

// Star rating filter
if (!empty($star_ratings) && !in_array('all', $star_ratings)) {
    $placeholders = implode(',', array_fill(0, count($star_ratings), '?'));
    $where[] = "star_rating IN ($placeholders)";
    foreach ($star_ratings as $star) {
        $params[] = intval($star);
        $types .= 'i';
    }
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Sort order
$hotel_sort = $_GET['sort'] ?? 'recommended';
$order_by = "ORDER BY ";
switch ($hotel_sort) {
    case 'price_low':
        $order_by .= "price_per_night ASC, star_rating DESC";
        break;
    case 'price_high':
        $order_by .= "price_per_night DESC, star_rating DESC";
        break;
    case 'top_rated':
        $order_by .= "star_rating DESC, price_per_night ASC";
        break;
    case 'recommended':
    default:
        $order_by .= "star_rating DESC, price_per_night ASC";
        break;
}

$query = "SELECT * FROM hotels $where_clause $order_by LIMIT 50";

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
                    <label for="hotel_location_search" class="flex flex-col gap-1.5">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Location</span>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary text-[20px]">location_on</span>
                            <input id="hotel_location_search" class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none" name="location" value="<?php echo htmlspecialchars($location); ?>" placeholder="City, hotel name, or location" required/>
                        </div>
                    </label>
                    
                    <!-- Dates -->
                    <div class="flex flex-col gap-1.5">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Dates</span>
                        <div class="flex gap-2">
                            <div class="flex-1 relative group">
                                <label for="check_in_input" class="sr-only">Check-in Date</label>
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary text-[18px] cursor-pointer z-10" onclick="openDatePicker('check_in_input')">calendar_month</span>
                                <input id="check_in_input" type="date" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none text-slate-900 dark:text-white" required/>
                            </div>
                            <div class="flex-1 relative group">
                                <label for="check_out_input" class="sr-only">Check-out Date</label>
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary text-[18px] cursor-pointer z-10" onclick="openDatePicker('check_out_input')">calendar_month</span>
                                <input id="check_out_input" type="date" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>" min="<?php echo $check_in ? date('Y-m-d', strtotime($check_in . ' +1 day')) : date('Y-m-d', strtotime('+2 days')); ?>" class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none text-slate-900 dark:text-white" required/>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Guests -->
                    <label for="hotel_guests_input" class="flex flex-col gap-1.5">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Guests</span>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary text-[20px]">group</span>
                            <input id="hotel_guests_input" class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none" type="number" name="guests" value="<?php echo $guests; ?>" min="1" max="10" placeholder="Number of guests" required/>
                        </div>
                        <div class="mt-1">
                            <label for="hotel_rooms_input" class="block text-xs text-slate-500 mb-1">Rooms</label>
                            <input id="hotel_rooms_input" type="number" name="rooms" value="<?php echo $rooms; ?>" min="1" max="5" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none" required/>
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
            <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden group cursor-pointer" onclick="toggleMap()">
                <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-blue-600/20 opacity-60 group-hover:opacity-70 transition-opacity"></div>
                <div class="relative z-10 flex items-center justify-center h-24">
                    <button type="button" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-4 py-2 rounded-lg font-bold text-sm shadow-lg flex items-center gap-2 hover:scale-105 transition-transform">
                        <span class="material-symbols-outlined text-primary">map</span>
                        <span id="mapToggleText">Show on Map</span>
                    </button>
                </div>
            </div>
            
            <!-- Map Container (Hidden by default) -->
            <div id="mapContainer" class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hidden">
                <div class="relative w-full h-64 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-700">
                    <iframe 
                        width="100%" 
                        height="100%" 
                        frameborder="0" 
                        style="border:0" 
                        src="https://www.google.com/maps?q=<?php echo urlencode($location); ?>&output=embed" 
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Filters</h3>
                    <button type="button" onclick="resetFilters()" class="text-xs font-medium text-primary hover:text-sky-500">Reset All</button>
                </div>
                
                <!-- Price Range -->
                <div class="mb-8">
                    <h4 class="text-sm font-semibold mb-4 text-slate-900 dark:text-gray-200">Price Range (per night)</h4>
                    <div class="space-y-2 mb-2">
                        <div class="flex items-center gap-2">
                            <label class="text-xs text-slate-500 w-16">Min:</label>
                            <input type="number" id="minPrice" min="0" max="500" step="10" value="<?php echo $min_price; ?>" class="flex-1 px-2 py-1 text-sm border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800" onchange="updatePriceFilter()">
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-xs text-slate-500 w-16">Max:</label>
                            <input type="number" id="maxPrice" min="0" max="500" step="10" value="<?php echo $max_price; ?>" class="flex-1 px-2 py-1 text-sm border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800" onchange="updatePriceFilter()">
                        </div>
                    </div>
                    <div class="relative h-1.5 w-full bg-slate-200 dark:bg-slate-700 rounded-full mb-4">
                        <div class="absolute h-full bg-primary rounded-full" id="priceRangeFill" style="left: <?php echo ($min_price / 500) * 100; ?>%; width: <?php echo (($max_price - $min_price) / 500) * 100; ?>%;"></div>
                    </div>
                    <div class="flex justify-between text-sm text-slate-500 font-medium">
                        <span>$0</span>
                        <span>$500+</span>
                    </div>
                </div>
                
                <!-- Star Rating -->
                <div class="mb-8">
                    <h4 class="text-sm font-semibold mb-3 text-slate-900 dark:text-gray-200">Star Rating</h4>
                    <div class="space-y-2">
                        <?php
                        $star_counts = [5 => 0, 4 => 0, 3 => 0];
                        // Count hotels by star rating
                        $count_query = $conn->query("SELECT star_rating, COUNT(*) as count FROM hotels GROUP BY star_rating");
                        while ($row = $count_query->fetch_assoc()) {
                            if (isset($star_counts[$row['star_rating']])) {
                                $star_counts[$row['star_rating']] = $row['count'];
                            }
                        }
                        ?>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input name="star_rating" value="5" type="checkbox" class="star-filter w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" <?php echo in_array('5', $star_ratings) || empty($star_ratings) ? 'checked' : ''; ?> onchange="applyFilters()"/>
                            <div class="flex text-yellow-400">
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                            </div>
                            <span class="text-xs text-slate-500">(<?php echo $star_counts[5]; ?>)</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input name="star_rating" value="4" type="checkbox" class="star-filter w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" <?php echo in_array('4', $star_ratings) || empty($star_ratings) ? 'checked' : ''; ?> onchange="applyFilters()"/>
                            <div class="flex text-yellow-400">
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                            </div>
                            <span class="text-xs text-slate-500">(<?php echo $star_counts[4]; ?>)</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input name="star_rating" value="3" type="checkbox" class="star-filter w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" <?php echo in_array('3', $star_ratings) || empty($star_ratings) ? 'checked' : ''; ?> onchange="applyFilters()"/>
                            <div class="flex text-yellow-400">
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                                <span class="material-symbols-outlined text-[18px] fill-current">star</span>
                            </div>
                            <span class="text-xs text-slate-500">(<?php echo $star_counts[3]; ?>)</span>
                        </label>
                    </div>
                </div>
                
                <!-- Amenities -->
                <div class="mb-8">
                    <h4 class="text-sm font-semibold mb-3 text-slate-900 dark:text-gray-200">Amenities</h4>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" type="checkbox"/>
                            <span class="text-sm text-slate-600 dark:text-slate-400">Free WiFi</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" type="checkbox"/>
                            <span class="text-sm text-slate-600 dark:text-slate-400">Breakfast Included</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" type="checkbox"/>
                            <span class="text-sm text-slate-600 dark:text-slate-400">Pool</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" type="checkbox"/>
                            <span class="text-sm text-slate-600 dark:text-slate-400">Gym / Fitness</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" type="checkbox"/>
                            <span class="text-sm text-slate-600 dark:text-slate-400">Spa</span>
                        </label>
                    </div>
                </div>
                
                <!-- Property Type -->
                <div>
                    <h4 class="text-sm font-semibold mb-3 text-slate-900 dark:text-gray-200">Property Type</h4>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input checked class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" type="checkbox"/>
                            <span class="text-sm text-slate-600 dark:text-slate-400">Hotel</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" type="checkbox"/>
                            <span class="text-sm text-slate-600 dark:text-slate-400">Resort</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer" type="checkbox"/>
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
                    <select id="hotelSort" onchange="applyHotelSort()" class="form-select text-sm font-medium border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg py-2 pl-3 pr-8 focus:ring-primary/20 focus:border-primary cursor-pointer">
                        <option value="recommended" <?php echo ($_GET['sort'] ?? 'recommended') === 'recommended' ? 'selected' : ''; ?>>Recommended</option>
                        <option value="price_low" <?php echo ($_GET['sort'] ?? '') === 'price_low' ? 'selected' : ''; ?>>Price (Low to High)</option>
                        <option value="price_high" <?php echo ($_GET['sort'] ?? '') === 'price_high' ? 'selected' : ''; ?>>Price (High to Low)</option>
                        <option value="top_rated" <?php echo ($_GET['sort'] ?? '') === 'top_rated' ? 'selected' : ''; ?>>Top Rated</option>
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
                    <!-- Hotel Icon -->
                    <div class="sm:w-72 h-48 sm:h-auto relative shrink-0 flex items-center justify-center bg-gradient-to-br from-primary/20 to-blue-600/20">
                        <div class="absolute inset-0 bg-gradient-to-br from-primary/10 to-blue-600/10"></div>
                        <div class="relative z-10 flex flex-col items-center justify-center p-8">
                            <span class="material-symbols-outlined text-6xl text-primary mb-2">hotel</span>
                            <div class="flex items-center gap-1">
                                <?php for ($i = 0; $i < $hotel['star_rating']; $i++): ?>
                                    <span class="material-symbols-outlined text-yellow-400 text-lg fill-current">star</span>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <?php if ($count === 1): ?>
                        <div class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm text-slate-900 text-xs font-bold px-2 py-1 rounded z-20">
                            Recommended
                        </div>
                        <?php elseif ($count === 3): ?>
                        <div class="absolute top-3 left-3 bg-purple-100 text-purple-700 text-xs font-bold px-2 py-1 rounded border border-purple-200 z-20">
                            Top Choice
                        </div>
                        <?php endif; ?>
                        <div class="absolute bottom-3 right-3 bg-black/50 backdrop-blur-sm text-white p-1.5 rounded-full hover:bg-black/70 cursor-pointer transition-colors z-20">
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
                                        <a class="text-primary font-medium hover:underline ml-1" href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($hotel['location'] ?? $hotel['city']); ?>" target="_blank">Show on map</a>
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
                                <form method="POST" class="flex-1 sm:flex-initial" onsubmit="return validateHotelBooking(this);">
                                    <input type="hidden" name="hotel_id" value="<?php echo $hotel['id']; ?>">
                            <input type="hidden" name="check_in" id="check_in_<?php echo $hotel['id']; ?>" value="<?php echo htmlspecialchars($check_in); ?>" required>
                            <input type="hidden" name="check_out" id="check_out_<?php echo $hotel['id']; ?>" value="<?php echo htmlspecialchars($check_out); ?>" required>
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
        </div>
    </div>
</main>

<script>
function toggleMap() {
    const mapContainer = document.getElementById('mapContainer');
    const mapToggleText = document.getElementById('mapToggleText');
    
    if (mapContainer.classList.contains('hidden')) {
        mapContainer.classList.remove('hidden');
        mapToggleText.textContent = 'Hide Map';
    } else {
        mapContainer.classList.add('hidden');
        mapToggleText.textContent = 'Show on Map';
    }
}

function validateHotelBooking(form) {
    let checkIn = form.querySelector('input[name="check_in"]').value;
    let checkOut = form.querySelector('input[name="check_out"]').value;
    
    // Get dates from main search form if not in booking form
    if (!checkIn || !checkOut || checkIn.trim() === '' || checkOut.trim() === '') {
        const mainCheckIn = document.getElementById('check_in_input');
        const mainCheckOut = document.getElementById('check_out_input');
        
        if (mainCheckIn && mainCheckIn.value) {
            checkIn = mainCheckIn.value;
        } else {
            // Use default dates
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            const checkOutDate = new Date(today);
            checkOutDate.setDate(checkOutDate.getDate() + 3);
            
            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };
            
            checkIn = formatDate(tomorrow);
            checkOut = formatDate(checkOutDate);
        }
        
        if (mainCheckOut && mainCheckOut.value) {
            checkOut = mainCheckOut.value;
        } else if (!checkOut) {
            const today = new Date();
            const checkOutDate = new Date(today);
            checkOutDate.setDate(checkOutDate.getDate() + 3);
            checkOut = checkOutDate.toISOString().split('T')[0];
        }
        
        form.querySelector('input[name="check_in"]').value = checkIn;
        form.querySelector('input[name="check_out"]').value = checkOut;
    }
    
    // Validate dates
    if (new Date(checkIn) >= new Date(checkOut)) {
        alert('Check-out date must be after check-in date.');
        return false;
    }
    
    return true;
}

// Update check-out min date when check-in changes
document.addEventListener('DOMContentLoaded', function() {
    const checkInInput = document.getElementById('check_in_input');
    const checkOutInput = document.getElementById('check_out_input');
    
    if (checkInInput && checkOutInput) {
        checkInInput.addEventListener('change', function() {
            if (this.value) {
                const checkInDate = new Date(this.value);
                checkInDate.setDate(checkInDate.getDate() + 1);
                checkOutInput.min = checkInDate.toISOString().split('T')[0];
                
                // Update check-out value if it's before the new minimum
                if (checkOutInput.value && new Date(checkOutInput.value) <= new Date(this.value)) {
                    checkOutInput.value = checkInDate.toISOString().split('T')[0];
                }
            }
        });
    }
});

function updatePriceFilter() {
    const minPrice = parseInt(document.getElementById('minPrice').value) || 0;
    const maxPrice = parseInt(document.getElementById('maxPrice').value) || 500;
    
    // Validate range
    if (minPrice > maxPrice) {
        alert('Minimum price cannot be greater than maximum price.');
        return;
    }
    
    // Update visual slider
    const fillLeft = (minPrice / 500) * 100;
    const fillWidth = ((maxPrice - minPrice) / 500) * 100;
    document.getElementById('priceRangeFill').style.left = fillLeft + '%';
    document.getElementById('priceRangeFill').style.width = fillWidth + '%';
    
    applyFilters();
}

function applyFilters() {
    const url = new URL(window.location.href);
    const minPrice = document.getElementById('minPrice').value || 0;
    const maxPrice = document.getElementById('maxPrice').value || 500;
    const starRatings = Array.from(document.querySelectorAll('.star-filter:checked')).map(cb => cb.value);
    
    url.searchParams.set('min_price', minPrice);
    url.searchParams.set('max_price', maxPrice);
    if (starRatings.length > 0) {
        url.searchParams.set('stars', starRatings.join(','));
    } else {
        url.searchParams.delete('stars');
    }
    
    window.location.href = url.toString();
}

function resetFilters() {
    const url = new URL(window.location.href);
    url.searchParams.delete('min_price');
    url.searchParams.delete('max_price');
    url.searchParams.delete('stars');
    url.searchParams.delete('sort');
    window.location.href = url.toString();
}

function applyHotelSort() {
    const url = new URL(window.location.href);
    const sortValue = document.getElementById('hotelSort').value;
    url.searchParams.set('sort', sortValue);
    window.location.href = url.toString();
}

// Date picker function with fallback
function openDatePicker(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        // Try modern showPicker() API first
        if (input.showPicker && typeof input.showPicker === 'function') {
            input.showPicker().catch(() => {
                // Fallback: focus and click the input
                input.focus();
                input.click();
            });
        } else {
            // Fallback for browsers that don't support showPicker()
            input.focus();
            input.click();
        }
    }
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
