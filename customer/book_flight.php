<?php
/**
 * Book Flight Page - Redesigned with Tailwind CSS
 * Customer page to search and book flights
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireCustomer();

$page_title = 'Book Flight';
$message = '';
$message_type = '';

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_flight'])) {
    $flight_id = intval($_POST['flight_id']);
    $passengers = intval($_POST['passengers']);
    
    // Get flight details
    $stmt = $conn->prepare("SELECT * FROM flights WHERE id = ?");
    $stmt->bind_param("i", $flight_id);
    $stmt->execute();
    $flight = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($flight && $flight['available_seats'] >= $passengers) {
        $total_price = $flight['price'] * $passengers;
        
        // Create booking
        $stmt = $conn->prepare("INSERT INTO flight_bookings (user_id, flight_id, passengers, total_price) VALUES (?, ?, ?, ?)");
        $user_id = getUserId();
        $stmt->bind_param("iiid", $user_id, $flight_id, $passengers, $total_price);
        
        if ($stmt->execute()) {
            $booking_id = $conn->insert_id;
            header("Location: payment.php?type=flight&id=" . $booking_id);
            exit();
        }
        $stmt->close();
    } else {
        $message = 'Not enough seats available.';
        $message_type = 'error';
    }
}

// Get search parameters
$origin = $_GET['origin'] ?? 'JFK';
$destination = $_GET['destination'] ?? 'LHR';
$departure_date = $_GET['departure_date'] ?? '';
$return_date = $_GET['return_date'] ?? '';
$trip_type = $_GET['trip_type'] ?? 'round_trip';
$passengers = $_GET['passengers'] ?? 2;
$class_type = $_GET['class'] ?? 'Economy';

// Get filter parameters
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 2000;
$stops_filter = isset($_GET['stops']) ? explode(',', $_GET['stops']) : [];
$airlines_filter = isset($_GET['airlines']) ? explode(',', $_GET['airlines']) : [];
$departure_time = $_GET['departure_time'] ?? '';
$sort_by = $_GET['sort'] ?? 'cheapest';

// Build query
$where = [];
$params = [];
$types = '';

if ($origin) {
    $where[] = "origin LIKE ?";
    $params[] = "%$origin%";
    $types .= 's';
}
if ($destination) {
    $where[] = "destination LIKE ?";
    $params[] = "%$destination%";
    $types .= 's';
}
if ($departure_date) {
    $where[] = "DATE(departure_date) = ?";
    $params[] = $departure_date;
    $types .= 's';
}

// Price filter
if ($min_price > 0 || $max_price < 2000) {
    $where[] = "price >= ? AND price <= ?";
    $params[] = $min_price;
    $params[] = $max_price;
    $types .= 'dd';
}

// Stops filter
if (!empty($stops_filter) && !in_array('all', $stops_filter)) {
    $stops_conditions = [];
    foreach ($stops_filter as $stop) {
        if ($stop === 'direct') {
            $stops_conditions[] = "stops = 0";
        } elseif ($stop === '1') {
            $stops_conditions[] = "stops = 1";
        } elseif ($stop === '2+') {
            $stops_conditions[] = "stops >= 2";
        }
    }
    if (!empty($stops_conditions)) {
        $where[] = "(" . implode(" OR ", $stops_conditions) . ")";
    }
}

// Airlines filter
if (!empty($airlines_filter)) {
    $airline_placeholders = implode(',', array_fill(0, count($airlines_filter), '?'));
    $where[] = "airline IN ($airline_placeholders)";
    foreach ($airlines_filter as $airline) {
        $params[] = trim($airline);
        $types .= 's';
    }
}

// Departure time filter
if ($departure_time) {
    if ($departure_time === 'morning') {
        $where[] = "HOUR(departure_date) BETWEEN 6 AND 11";
    } elseif ($departure_time === 'afternoon') {
        $where[] = "HOUR(departure_date) BETWEEN 12 AND 17";
    } elseif ($departure_time === 'evening') {
        $where[] = "HOUR(departure_date) >= 18 OR HOUR(departure_date) < 6";
    }
}

// Class type filter
if ($class_type && $class_type !== 'all') {
    $where[] = "class_type = ?";
    $params[] = $class_type;
    $types .= 's';
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Sort order
$order_by = "ORDER BY ";
switch ($sort_by) {
    case 'fastest':
        // Try to extract hours from duration string like "6h 55m"
        $order_by .= "CAST(SUBSTRING_INDEX(REPLACE(duration, 'h', ''), ' ', 1) AS UNSIGNED) ASC, departure_date ASC";
        break;
    case 'best_value':
        // Best value = lowest price with available seats
        $order_by .= "price ASC, available_seats DESC, departure_date ASC";
        break;
    case 'cheapest':
    default:
        $order_by .= "price ASC, departure_date ASC";
        break;
}

$query = "SELECT * FROM flights $where_clause $order_by LIMIT 100";

$flights = [];
if (!empty($params)) {
    $stmt = $conn->prepare($query);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $flights = $stmt->get_result();
} else {
    $flights = $conn->query($query);
}

$flight_count = mysqli_num_rows($flights);

include '../includes/header.php';
?>
<main class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark p-6 lg:p-8">
    <div class="max-w-7xl mx-auto space-y-8">
        <!-- Search Hero Section -->
        <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-lg border border-slate-100 dark:border-slate-800 p-6">
            <!-- Tabs -->
            <div class="flex gap-6 mb-6 border-b border-slate-100 dark:border-slate-800 pb-2">
                <button type="button" onclick="setTripType('round_trip')" class="flex items-center gap-2 pb-2 border-b-2 <?php echo $trip_type === 'round_trip' ? 'border-primary text-primary font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'; ?> text-sm transition-colors">
                    <span class="material-symbols-outlined text-[20px]">sync_alt</span>
                    Round Trip
                </button>
                <button type="button" onclick="setTripType('one_way')" class="flex items-center gap-2 pb-2 border-b-2 <?php echo $trip_type === 'one_way' ? 'border-primary text-primary font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'; ?> text-sm transition-colors">
                    <span class="material-symbols-outlined text-[20px]">arrow_right_alt</span>
                    One Way
                </button>
                <button type="button" onclick="setTripType('multi_city')" class="flex items-center gap-2 pb-2 border-b-2 <?php echo $trip_type === 'multi_city' ? 'border-primary text-primary font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium'; ?> text-sm transition-colors">
                    <span class="material-symbols-outlined text-[20px]">call_split</span>
                    Multi-city
                </button>
            </div>
            
            <!-- Search Form -->
            <form method="GET" class="space-y-4">
                <input type="hidden" name="trip_type" id="trip_type_input" value="<?php echo htmlspecialchars($trip_type); ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <!-- Location Group -->
                    <div class="md:col-span-4 grid grid-cols-2 gap-2 relative">
                        <div class="relative group">
                            <label for="origin_input" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wide">From</label>
                            <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-lg bg-background-light dark:bg-background-dark focus-within:ring-2 focus-within:ring-primary/50 transition-all overflow-hidden">
                                <span class="material-symbols-outlined text-slate-400 pl-3 text-[20px]">flight_takeoff</span>
                                <input id="origin_input" class="w-full bg-transparent border-none text-slate-900 dark:text-white font-bold placeholder-slate-400 focus:ring-0 text-base py-3 pr-3" placeholder="City or Airport" type="text" name="origin" value="<?php echo htmlspecialchars($origin); ?>" required/>
                            </div>
                        </div>
                        <div class="relative">
                            <label for="destination_input" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wide">To</label>
                            <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-lg bg-background-light dark:bg-background-dark focus-within:ring-2 focus-within:ring-primary/50 transition-all overflow-hidden">
                                <span class="material-symbols-outlined text-slate-400 pl-3 text-[20px]">flight_land</span>
                                <input id="destination_input" class="w-full bg-transparent border-none text-slate-900 dark:text-white font-bold placeholder-slate-400 focus:ring-0 text-base py-3 pr-3" placeholder="City or Airport" type="text" name="destination" value="<?php echo htmlspecialchars($destination); ?>" required/>
                            </div>
                        </div>
                        <button type="button" onclick="swapAirports()" class="absolute -right-3 top-[34px] z-10 bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-600 rounded-full p-1 shadow-sm cursor-pointer hover:scale-110 transition-transform hidden md:block">
                            <span class="material-symbols-outlined text-primary text-[16px]">swap_horiz</span>
                        </button>
                    </div>
                    
                    <!-- Date Group -->
                    <div class="md:col-span-3 grid grid-cols-2 gap-2">
                        <div>
                            <label for="departure_date_input" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Depart</label>
                            <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-lg bg-background-light dark:bg-background-dark focus-within:ring-2 focus-within:ring-primary/50 transition-all overflow-hidden">
                                <span class="material-symbols-outlined text-slate-400 pl-3 text-[20px] cursor-pointer" onclick="openDatePicker('departure_date_input')">calendar_today</span>
                                <input id="departure_date_input" class="w-full bg-transparent border-none text-slate-900 dark:text-white font-medium focus:ring-0 text-sm py-3.5 pr-3" type="date" name="departure_date" value="<?php echo htmlspecialchars($departure_date); ?>" min="<?php echo date('Y-m-d'); ?>" required/>
                            </div>
                        </div>
                        <div>
                            <label for="return_date_input" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Return</label>
                            <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-lg bg-background-light dark:bg-background-dark focus-within:ring-2 focus-within:ring-primary/50 transition-all overflow-hidden">
                                <span class="material-symbols-outlined text-slate-400 pl-3 text-[20px] cursor-pointer" onclick="openDatePicker('return_date_input')">calendar_today</span>
                                <input id="return_date_input" class="w-full bg-transparent border-none text-slate-900 dark:text-white font-medium focus:ring-0 text-sm py-3.5 pr-3" type="date" name="return_date" value="<?php echo htmlspecialchars($return_date); ?>" min="<?php echo $departure_date ? date('Y-m-d', strtotime($departure_date . ' +1 day')) : date('Y-m-d', strtotime('+1 day')); ?>" <?php echo $trip_type === 'one_way' ? 'disabled' : ''; ?>/>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Passengers & Class -->
                    <div class="md:col-span-3">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Travelers & Class</label>
                        <button type="button" onclick="openTravelerModal()" class="w-full flex items-center border border-slate-200 dark:border-slate-700 rounded-lg bg-background-light dark:bg-background-dark focus-within:ring-2 focus-within:ring-primary/50 transition-all overflow-hidden cursor-pointer hover:border-primary/50">
                            <span class="material-symbols-outlined text-slate-400 pl-3 text-[20px]">group</span>
                            <div class="flex-1 px-3 py-3.5 flex items-center justify-between">
                                <span class="text-sm font-medium text-slate-900 dark:text-white" id="travelerDisplay"><?php echo $passengers; ?> Adults, <?php echo $class_type; ?></span>
                                <span class="material-symbols-outlined text-slate-400 text-[18px]">expand_more</span>
                            </div>
                        </button>
                        <input type="hidden" name="passengers" id="passengersInput" value="<?php echo $passengers; ?>">
                        <input type="hidden" name="class" id="classInput" value="<?php echo $class_type; ?>">
                    </div>
                    
                    <!-- Search Button -->
                    <div class="md:col-span-2">
                        <button type="submit" class="w-full h-[52px] bg-primary hover:bg-sky-500 text-white rounded-lg font-bold text-base shadow-md shadow-primary/30 flex items-center justify-center gap-2 transition-all">
                            <span class="material-symbols-outlined">search</span>
                            Search Flights
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <?php if ($message): ?>
            <div class="bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-900/20 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg text-sm">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar Filters -->
            <aside class="w-full lg:w-72 flex-shrink-0 space-y-6">
                <!-- Price Range -->
                <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-slate-900 dark:text-white">Price Range</h3>
                    </div>
                    <div class="mb-4 px-1">
                        <div class="space-y-2 mb-2">
                            <div class="flex items-center gap-2">
                                <label class="text-xs text-slate-500 w-16">Min:</label>
                                <input type="number" id="flightMinPrice" min="0" max="2000" step="50" value="<?php echo $min_price; ?>" class="flex-1 px-2 py-1 text-sm border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800" onchange="applyFlightFilters()">
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="text-xs text-slate-500 w-16">Max:</label>
                                <input type="number" id="flightMaxPrice" min="0" max="2000" step="50" value="<?php echo $max_price; ?>" class="flex-1 px-2 py-1 text-sm border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800" onchange="applyFlightFilters()">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between text-sm text-slate-500 dark:text-slate-400 font-medium">
                        <span>$0</span>
                        <span>$2,000+</span>
                    </div>
                </div>
                
                <!-- Stops -->
                <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-slate-900 dark:text-white">Stops</h3>
                    </div>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input name="stops_filter" value="direct" type="checkbox" class="stops-filter w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary/50" <?php echo in_array('direct', $stops_filter) || empty($stops_filter) ? 'checked' : ''; ?> onchange="applyFlightFilters()"/>
                            <span class="text-sm text-slate-900 dark:text-slate-300 group-hover:text-primary transition-colors">Direct only</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input name="stops_filter" value="1" type="checkbox" class="stops-filter w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary/50" <?php echo in_array('1', $stops_filter) || empty($stops_filter) ? 'checked' : ''; ?> onchange="applyFlightFilters()"/>
                            <span class="text-sm text-slate-900 dark:text-slate-300 group-hover:text-primary transition-colors">1 Stop</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input name="stops_filter" value="2+" type="checkbox" class="stops-filter w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary/50" <?php echo in_array('2+', $stops_filter) || empty($stops_filter) ? 'checked' : ''; ?> onchange="applyFlightFilters()"/>
                            <span class="text-sm text-slate-900 dark:text-slate-300 group-hover:text-primary transition-colors">2+ Stops</span>
                        </label>
                    </div>
                </div>
                
                <!-- Airlines -->
                <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-slate-900 dark:text-white">Airlines</h3>
                    </div>
                    <div class="space-y-3">
                        <?php
                        // Get unique airlines from database
                        $airlines_result = $conn->query("SELECT DISTINCT airline FROM flights WHERE airline IS NOT NULL AND airline != '' ORDER BY airline");
                        $db_airlines = [];
                        while ($row = $airlines_result->fetch_assoc()) {
                            $db_airlines[] = $row['airline'];
                        }
                        ?>
                        <?php foreach ($db_airlines as $airline): ?>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input name="airlines_filter" value="<?php echo htmlspecialchars($airline); ?>" type="checkbox" class="airlines-filter w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary/50" <?php echo in_array($airline, $airlines_filter) || empty($airlines_filter) ? 'checked' : ''; ?> onchange="applyFlightFilters()"/>
                            <span class="text-sm text-slate-900 dark:text-slate-300 group-hover:text-primary transition-colors"><?php echo htmlspecialchars($airline); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Departure Time -->
                <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-slate-900 dark:text-white">Departure Time</h3>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick="setDepartureTime('')" class="flex-1 py-2 rounded border <?php echo $departure_time === '' ? 'border-primary bg-primary/10 text-primary' : 'border-slate-200 dark:border-slate-700 bg-background-light dark:bg-background-dark'; ?> text-xs font-medium hover:border-primary hover:text-primary transition-all flex flex-col items-center gap-1">
                            <span class="material-symbols-outlined text-base">schedule</span>
                            All
                        </button>
                        <button type="button" onclick="setDepartureTime('morning')" class="flex-1 py-2 rounded border <?php echo $departure_time === 'morning' ? 'border-primary bg-primary/10 text-primary' : 'border-slate-200 dark:border-slate-700 bg-background-light dark:bg-background-dark'; ?> text-xs font-medium hover:border-primary hover:text-primary transition-all flex flex-col items-center gap-1">
                            <span class="material-symbols-outlined text-base">wb_twilight</span>
                            Morning
                        </button>
                        <button type="button" onclick="setDepartureTime('afternoon')" class="flex-1 py-2 rounded border <?php echo $departure_time === 'afternoon' ? 'border-primary bg-primary/10 text-primary' : 'border-slate-200 dark:border-slate-700 bg-background-light dark:bg-background-dark'; ?> text-xs font-medium hover:border-primary hover:text-primary transition-all flex flex-col items-center gap-1">
                            <span class="material-symbols-outlined text-base">wb_sunny</span>
                            Afternoon
                        </button>
                        <button type="button" onclick="setDepartureTime('evening')" class="flex-1 py-2 rounded border <?php echo $departure_time === 'evening' ? 'border-primary bg-primary/10 text-primary' : 'border-slate-200 dark:border-slate-700 bg-background-light dark:bg-background-dark'; ?> text-xs font-medium hover:border-primary hover:text-primary transition-all flex flex-col items-center gap-1">
                            <span class="material-symbols-outlined text-base">dark_mode</span>
                            Evening
                        </button>
                    </div>
                </div>
            </aside>
            
            <!-- Results Section -->
            <section class="flex-1">
                <!-- Sort Bar -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white"><span class="text-primary"><?php echo $flight_count; ?></span> Flights Found</h2>
                    <div class="flex bg-surface-light dark:bg-surface-dark rounded-lg p-1 shadow-sm border border-slate-100 dark:border-slate-800">
                        <button type="button" onclick="setSort('cheapest')" class="px-4 py-1.5 rounded-md text-sm <?php echo $sort_by === 'cheapest' ? 'font-bold bg-primary text-white shadow-sm' : 'font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'; ?> transition-colors">Cheapest</button>
                        <button type="button" onclick="setSort('fastest')" class="px-4 py-1.5 rounded-md text-sm <?php echo $sort_by === 'fastest' ? 'font-bold bg-primary text-white shadow-sm' : 'font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'; ?> transition-colors">Fastest</button>
                        <button type="button" onclick="setSort('best_value')" class="px-4 py-1.5 rounded-md text-sm <?php echo $sort_by === 'best_value' ? 'font-bold bg-primary text-white shadow-sm' : 'font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'; ?> transition-colors">Best Value</button>
                    </div>
                </div>
                
                <!-- Flight Cards List -->
                <div class="space-y-4">
                    <?php 
                    $count = 0;
                    while ($flight = $flights->fetch_assoc()): 
                        $count++;
                        if ($count > 10) break;
                        $stops = $flight['stops'] ?? 0;
                    ?>
                    <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-100 dark:border-slate-800 hover:shadow-md transition-shadow group relative overflow-hidden">
                        <?php if ($count === 1): ?>
                        <div class="absolute top-0 left-0 w-1 h-full bg-primary"></div>
                        <?php endif; ?>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                            <!-- Airline Info -->
                            <div class="md:col-span-3 flex flex-col gap-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full p-1 border border-slate-100 bg-white dark:bg-slate-800">
                                        <span class="material-symbols-outlined text-primary text-2xl">flight</span>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 dark:text-white leading-tight"><?php echo htmlspecialchars($flight['airline']); ?></h4>
                                        <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($flight['flight_number']); ?> • <?php echo htmlspecialchars($flight['aircraft'] ?? 'Boeing 777'); ?></p>
                                    </div>
                                </div>
                                <div class="flex gap-2 mt-1">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-[10px] font-semibold text-slate-600 dark:text-slate-300"><?php echo htmlspecialchars($flight['class_type']); ?></span>
                                    <span class="px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-900/30 text-[10px] font-semibold text-blue-600 dark:text-blue-300 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[10px]">luggage</span> Incl.
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Flight Path -->
                            <div class="md:col-span-6 flex items-center justify-between text-center px-4">
                                <div class="text-left">
                                    <p class="text-2xl font-bold text-slate-900 dark:text-white"><?php echo date('H:i', strtotime($flight['departure_date'])); ?></p>
                                    <p class="text-sm font-bold text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($flight['origin']); ?></p>
                                </div>
                                <div class="flex-1 px-6 flex flex-col items-center gap-1">
                                    <p class="text-xs text-slate-500 dark:text-slate-500 font-medium"><?php echo htmlspecialchars($flight['duration'] ?? '6h 55m'); ?></p>
                                    <div class="w-full h-px bg-slate-300 dark:bg-slate-600 relative flex items-center justify-center">
                                        <div class="absolute w-2 h-2 rounded-full bg-slate-300 dark:bg-slate-600 left-0"></div>
                                        <span class="material-symbols-outlined text-primary bg-surface-light dark:bg-surface-dark px-1 text-lg rotate-90">flight</span>
                                        <?php if ($stops > 0): ?>
                                        <div class="absolute w-2 h-2 rounded-full bg-white border-2 border-orange-400 left-1/2 -translate-x-1/2 z-10" title="<?php echo $stops; ?> Stop"></div>
                                        <?php endif; ?>
                                        <div class="absolute w-2 h-2 rounded-full bg-slate-300 dark:bg-slate-600 right-0"></div>
                                    </div>
                                    <p class="text-xs <?php echo $stops == 0 ? 'text-green-600 dark:text-green-400' : 'text-orange-500'; ?> font-bold">
                                        <?php echo $stops == 0 ? 'Direct' : ($stops . ' Stop' . ($stops > 1 ? 's' : '')); ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-slate-900 dark:text-white"><?php echo date('H:i', strtotime($flight['arrival_date'] ?? $flight['departure_date'])); ?></p>
                                    <p class="text-sm font-bold text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($flight['destination']); ?></p>
                                </div>
                            </div>
                            
                            <!-- Price & Action -->
                            <div class="md:col-span-3 flex flex-col items-end justify-center border-l border-slate-100 dark:border-slate-800 md:pl-6 pl-0 pt-4 md:pt-0 border-t md:border-t-0 mt-4 md:mt-0">
                                <h3 class="text-3xl font-bold <?php echo $count === 1 ? 'text-primary' : 'text-slate-900 dark:text-white'; ?> mb-1">$<?php echo number_format($flight['price'], 0); ?></h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">per person</p>
                                <form method="POST" class="w-full">
                                    <input type="hidden" name="flight_id" value="<?php echo $flight['id']; ?>">
                                    <input type="hidden" name="passengers" value="<?php echo $passengers; ?>">
                                    <button type="submit" name="book_flight" class="w-full bg-primary hover:bg-sky-500 text-white font-bold py-2.5 px-4 rounded-lg shadow-sm shadow-primary/30 transition-all flex items-center justify-center gap-2 group-hover:scale-[1.02]">
                                        Book Now
                                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; 
                    if ($count === 0): ?>
                    <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-100 dark:border-slate-800 p-8 text-center">
                        <span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600 mb-4">flight</span>
                        <p class="text-slate-500 dark:text-slate-400">No flights found. Try adjusting your search criteria.</p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <div class="mt-8 flex justify-center">
                    <nav class="flex items-center gap-2">
                        <button onclick="navigatePage(-1)" class="w-10 h-10 flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-white text-slate-600 dark:text-slate-600 hover:border-primary hover:text-primary transition-colors shadow-sm">
                            <span class="material-symbols-outlined text-sm">chevron_left</span>
                        </button>
                        <button onclick="goToPage(1)" class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary hover:bg-primary text-white font-bold text-sm shadow-sm">1</button>
                        <button onclick="goToPage(2)" class="w-10 h-10 flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-200 bg-white dark:bg-white text-slate-700 dark:text-slate-700 hover:border-primary hover:text-primary transition-colors font-medium text-sm shadow-sm">2</button>
                        <button onclick="goToPage(3)" class="w-10 h-10 flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-200 bg-white dark:bg-white text-slate-700 dark:text-slate-700 hover:border-primary hover:text-primary transition-colors font-medium text-sm shadow-sm">3</button>
                        <span class="px-2 text-slate-600 dark:text-slate-600 font-medium text-sm">...</span>
                        <button onclick="navigatePage(1)" class="w-10 h-10 flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-white text-slate-600 dark:text-slate-600 hover:border-primary hover:text-primary transition-colors shadow-sm">
                            <span class="material-symbols-outlined text-sm">chevron_right</span>
                        </button>
                    </nav>
                </div>
            </section>
        </div>
    </div>
</main>

<script>
function setTripType(type) {
    document.getElementById('trip_type_input').value = type;
    const returnInput = document.getElementById('return_date_input');
    if (type === 'one_way' && returnInput) {
        returnInput.disabled = true;
        returnInput.removeAttribute('required');
    } else if (returnInput) {
        returnInput.disabled = false;
        returnInput.setAttribute('required', 'required');
        // Update min date based on departure date
        const departureInput = document.getElementById('departure_date_input');
        if (departureInput && departureInput.value) {
            const departureDate = new Date(departureInput.value);
            departureDate.setDate(departureDate.getDate() + 1);
            returnInput.min = departureDate.toISOString().split('T')[0];
        }
    }
}

// Update return date min when departure date changes
document.addEventListener('DOMContentLoaded', function() {
    const departureInput = document.getElementById('departure_date_input');
    const returnInput = document.getElementById('return_date_input');
    
    if (departureInput && returnInput) {
        departureInput.addEventListener('change', function() {
            if (this.value) {
                const departureDate = new Date(this.value);
                departureDate.setDate(departureDate.getDate() + 1);
                returnInput.min = departureDate.toISOString().split('T')[0];
                if (returnInput.value && new Date(returnInput.value) <= new Date(this.value)) {
                    returnInput.value = departureDate.toISOString().split('T')[0];
                }
            }
        });
    }
});

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

let currentPassengers = <?php echo $passengers; ?>;

function openTravelerModal() {
    // Create or show modal
    let modal = document.getElementById('travelerModal');
    if (!modal) {
        // Create modal HTML
        modal = document.createElement('div');
        modal.id = 'travelerModal';
        modal.className = 'fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden';
        modal.innerHTML = `
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-md w-full p-6" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Travelers & Class</h3>
                    <button onclick="closeTravelerModal()" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Adults</label>
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="changePassengers(-1)" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 flex items-center justify-center hover:bg-primary hover:text-white transition-colors">
                                <span class="material-symbols-outlined text-[18px]">remove</span>
                            </button>
                            <span id="passengerCount" class="text-lg font-bold text-slate-900 dark:text-white w-12 text-center"><?php echo $passengers; ?></span>
                            <button type="button" onclick="changePassengers(1)" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 flex items-center justify-center hover:bg-primary hover:text-white transition-colors">
                                <span class="material-symbols-outlined text-[18px]">add</span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Class</label>
                        <select id="classSelect" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
                            <option value="Economy" <?php echo $class_type === 'Economy' ? 'selected' : ''; ?>>Economy</option>
                            <option value="Premium" <?php echo $class_type === 'Premium' ? 'selected' : ''; ?>>Premium</option>
                            <option value="Business" <?php echo $class_type === 'Business' ? 'selected' : ''; ?>>Business</option>
                            <option value="First" <?php echo $class_type === 'First' ? 'selected' : ''; ?>>First</option>
                        </select>
                    </div>
                    <button onclick="applyTravelerSelection()" class="w-full px-4 py-2 bg-primary text-white rounded-lg font-medium hover:bg-primary-dark transition-colors">
                        Apply
                    </button>
                </div>
            </div>
        `;
        modal.onclick = closeTravelerModal;
        document.body.appendChild(modal);
    }
    modal.classList.remove('hidden');
}

function closeTravelerModal() {
    const modal = document.getElementById('travelerModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

function changePassengers(delta) {
    currentPassengers = Math.max(1, Math.min(9, currentPassengers + delta));
    document.getElementById('passengerCount').textContent = currentPassengers;
}

function applyTravelerSelection() {
    const classValue = document.getElementById('classSelect').value;
    document.getElementById('passengersInput').value = currentPassengers;
    document.getElementById('classInput').value = classValue;
    document.getElementById('travelerDisplay').textContent = currentPassengers + ' Adults, ' + classValue;
    closeTravelerModal();
}

function swapAirports() {
    const origin = document.querySelector('input[name="origin"]');
    const destination = document.querySelector('input[name="destination"]');
    const temp = origin.value;
    origin.value = destination.value;
    destination.value = temp;
}

function applyFlightFilters() {
    const url = new URL(window.location.href);
    
    // Price filter
    const minPrice = document.getElementById('flightMinPrice').value || 0;
    const maxPrice = document.getElementById('flightMaxPrice').value || 2000;
    url.searchParams.set('min_price', minPrice);
    url.searchParams.set('max_price', maxPrice);
    
    // Stops filter
    const stops = Array.from(document.querySelectorAll('.stops-filter:checked')).map(cb => cb.value);
    if (stops.length > 0) {
        url.searchParams.set('stops', stops.join(','));
    } else {
        url.searchParams.delete('stops');
    }
    
    // Airlines filter
    const airlines = Array.from(document.querySelectorAll('.airlines-filter:checked')).map(cb => cb.value);
    if (airlines.length > 0) {
        url.searchParams.set('airlines', airlines.join(','));
    } else {
        url.searchParams.delete('airlines');
    }
    
    window.location.href = url.toString();
}

function setDepartureTime(time) {
    const url = new URL(window.location.href);
    if (time) {
        url.searchParams.set('departure_time', time);
    } else {
        url.searchParams.delete('departure_time');
    }
    window.location.href = url.toString();
}

function setSort(sortType) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', sortType);
    window.location.href = url.toString();
}
</script>
<?php include '../includes/footer.php'; ?>
