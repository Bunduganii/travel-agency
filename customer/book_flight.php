<?php
/**
<<<<<<< HEAD
 * Book Flight Page
=======
 * Book Flight Page - Redesigned with Tailwind CSS
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
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
<<<<<<< HEAD
$origin = $_GET['origin'] ?? '';
$destination = $_GET['destination'] ?? '';
$departure_date = $_GET['departure_date'] ?? '';
$return_date = $_GET['return_date'] ?? '';
$trip_type = $_GET['trip_type'] ?? 'round_trip';
$passengers = $_GET['passengers'] ?? 1;
=======
$origin = $_GET['origin'] ?? 'JFK';
$destination = $_GET['destination'] ?? 'LHR';
$departure_date = $_GET['departure_date'] ?? '';
$return_date = $_GET['return_date'] ?? '';
$trip_type = $_GET['trip_type'] ?? 'round_trip';
$passengers = $_GET['passengers'] ?? 2;
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
$class_type = $_GET['class'] ?? 'Economy';

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

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
$query = "SELECT * FROM flights $where_clause ORDER BY departure_date ASC LIMIT 50";

$flights = [];
if (!empty($params)) {
    $stmt = $conn->prepare($query);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $flights = $stmt->get_result();
} else {
    $flights = $conn->query("SELECT * FROM flights ORDER BY departure_date ASC LIMIT 50");
}

<<<<<<< HEAD
include '../includes/header.php';
?>
<main class="flight-search-page">
    <div class="search-section">
        <h1>Flight Search</h1>
        <form method="GET" class="flight-search-form">
            <div class="trip-type-toggle">
                <input type="radio" name="trip_type" value="round_trip" id="round_trip" <?php echo $trip_type === 'round_trip' ? 'checked' : ''; ?>>
                <label for="round_trip">Round Trip</label>
                
                <input type="radio" name="trip_type" value="one_way" id="one_way" <?php echo $trip_type === 'one_way' ? 'checked' : ''; ?>>
                <label for="one_way">One Way</label>
                
                <input type="radio" name="trip_type" value="multi_city" id="multi_city" <?php echo $trip_type === 'multi_city' ? 'checked' : ''; ?>>
                <label for="multi_city">Multi-city</label>
            </div>
            
            <div class="search-fields">
                <div class="field-group">
                    <label>FROM</label>
                    <div class="input-with-icon">
                        <i class="fas fa-plane-departure"></i>
                        <input type="text" name="origin" placeholder="JFK" value="<?php echo htmlspecialchars($origin); ?>">
                    </div>
                </div>
                
                <button type="button" class="swap-btn" onclick="swapAirports()">
                    <i class="fas fa-exchange-alt"></i>
                </button>
                
                <div class="field-group">
                    <label>TO</label>
                    <div class="input-with-icon">
                        <i class="fas fa-plane-arrival"></i>
                        <input type="text" name="destination" placeholder="LHR" value="<?php echo htmlspecialchars($destination); ?>">
                    </div>
                </div>
                
                <div class="field-group">
                    <label>DEPART</label>
                    <div class="input-with-icon">
                        <i class="fas fa-calendar"></i>
                        <input type="date" name="departure_date" value="<?php echo htmlspecialchars($departure_date); ?>">
                    </div>
                </div>
                
                <div class="field-group">
                    <label>RETURN</label>
                    <div class="input-with-icon">
                        <i class="fas fa-calendar"></i>
                        <input type="date" name="return_date" value="<?php echo htmlspecialchars($return_date); ?>" <?php echo $trip_type === 'one_way' ? 'disabled' : ''; ?>>
                    </div>
                </div>
                
                <div class="field-group">
                    <label>Travelers & Class</label>
                    <div class="input-with-icon">
                        <i class="fas fa-users"></i>
                        <input type="number" name="passengers" min="1" value="<?php echo htmlspecialchars($passengers); ?>">
                        <select name="class">
                            <option value="Economy" <?php echo $class_type === 'Economy' ? 'selected' : ''; ?>>Economy</option>
                            <option value="Premium" <?php echo $class_type === 'Premium' ? 'selected' : ''; ?>>Premium</option>
                            <option value="Business" <?php echo $class_type === 'Business' ? 'selected' : ''; ?>>Business</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-search">
                <i class="fas fa-search"></i> Search Flights
            </button>
        </form>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> slide-in">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <div class="results-section">
        <div class="results-header">
            <h2><?php echo mysqli_num_rows($flights); ?> Flights Found</h2>
            <div class="sort-options">
                <button class="sort-btn active">Cheapest</button>
                <button class="sort-btn">Fastest</button>
                <button class="sort-btn">Best Value</button>
            </div>
        </div>
        
        <div class="flights-list">
            <?php while ($flight = $flights->fetch_assoc()): ?>
            <div class="flight-card">
                <div class="flight-airline">
                    <strong><?php echo htmlspecialchars($flight['airline']); ?></strong>
                    <span><?php echo htmlspecialchars($flight['flight_number']); ?></span>
                    <small><?php echo htmlspecialchars($flight['aircraft']); ?></small>
                </div>
                
                <div class="flight-times">
                    <div class="time-block">
                        <strong><?php echo date('H:i', strtotime($flight['departure_date'])); ?></strong>
                        <span><?php echo htmlspecialchars($flight['origin']); ?></span>
                    </div>
                    
                    <div class="flight-path">
                        <div class="path-line"></div>
                        <i class="fas fa-plane"></i>
                        <span><?php echo htmlspecialchars($flight['duration']); ?></span>
                        <span class="stops"><?php echo $flight['stops'] == 0 ? 'Direct' : $flight['stops'] . ' Stop'; ?></span>
                    </div>
                    
                    <div class="time-block">
                        <strong><?php echo date('H:i', strtotime($flight['arrival_date'])); ?></strong>
                        <span><?php echo htmlspecialchars($flight['destination']); ?></span>
                    </div>
                </div>
                
                <div class="flight-price">
                    <strong>$<?php echo number_format($flight['price'], 2); ?></strong>
                    <span>per person</span>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="flight_id" value="<?php echo $flight['id']; ?>">
                        <input type="hidden" name="passengers" value="<?php echo $passengers; ?>">
                        <button type="submit" name="book_flight" class="btn btn-primary">Book Now →</button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
=======
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
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wide">From</label>
                            <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-lg bg-background-light dark:bg-background-dark focus-within:ring-2 focus-within:ring-primary/50 transition-all overflow-hidden">
                                <span class="material-symbols-outlined text-slate-400 pl-3 text-[20px]">flight_takeoff</span>
                                <input class="w-full bg-transparent border-none text-slate-900 dark:text-white font-bold placeholder-slate-400 focus:ring-0 text-base py-3" placeholder="City or Airport" type="text" name="origin" value="<?php echo htmlspecialchars($origin); ?>"/>
                            </div>
                        </div>
                        <div class="relative">
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wide">To</label>
                            <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-lg bg-background-light dark:bg-background-dark focus-within:ring-2 focus-within:ring-primary/50 transition-all overflow-hidden">
                                <span class="material-symbols-outlined text-slate-400 pl-3 text-[20px]">flight_land</span>
                                <input class="w-full bg-transparent border-none text-slate-900 dark:text-white font-bold placeholder-slate-400 focus:ring-0 text-base py-3" placeholder="City or Airport" type="text" name="destination" value="<?php echo htmlspecialchars($destination); ?>"/>
                            </div>
                        </div>
                        <button type="button" onclick="swapAirports()" class="absolute -right-3 top-[34px] z-10 bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-600 rounded-full p-1 shadow-sm cursor-pointer hover:scale-110 transition-transform hidden md:block">
                            <span class="material-symbols-outlined text-primary text-[16px]">swap_horiz</span>
                        </button>
                    </div>
                    
                    <!-- Date Group -->
                    <div class="md:col-span-3 grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Depart</label>
                            <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-lg bg-background-light dark:bg-background-dark focus-within:ring-2 focus-within:ring-primary/50 transition-all overflow-hidden">
                                <span class="material-symbols-outlined text-slate-400 pl-3 text-[20px]">calendar_today</span>
                                <input class="w-full bg-transparent border-none text-slate-900 dark:text-white font-medium focus:ring-0 text-sm py-3.5" type="date" name="departure_date" value="<?php echo htmlspecialchars($departure_date); ?>"/>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Return</label>
                            <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-lg bg-background-light dark:bg-background-dark focus-within:ring-2 focus-within:ring-primary/50 transition-all overflow-hidden">
                                <span class="material-symbols-outlined text-slate-400 pl-3 text-[20px]">calendar_today</span>
                                <input class="w-full bg-transparent border-none text-slate-900 dark:text-white font-medium focus:ring-0 text-sm py-3.5" type="date" name="return_date" value="<?php echo htmlspecialchars($return_date); ?>" <?php echo $trip_type === 'one_way' ? 'disabled' : ''; ?>/>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Passengers & Class -->
                    <div class="md:col-span-3">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Travelers & Class</label>
                        <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-lg bg-background-light dark:bg-background-dark focus-within:ring-2 focus-within:ring-primary/50 transition-all overflow-hidden">
                            <span class="material-symbols-outlined text-slate-400 pl-3 text-[20px]">group</span>
                            <div class="flex-1 px-3 py-3.5 flex items-center justify-between">
                                <span class="text-sm font-medium text-slate-900 dark:text-white"><?php echo $passengers; ?> Adults, <?php echo $class_type; ?></span>
                                <span class="material-symbols-outlined text-slate-400 text-[18px]">expand_more</span>
                            </div>
                            <input type="hidden" name="passengers" value="<?php echo $passengers; ?>">
                            <input type="hidden" name="class" value="<?php echo $class_type; ?>">
                        </div>
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
                        <div class="relative h-1 bg-slate-200 dark:bg-slate-700 rounded-full">
                            <div class="absolute left-[10%] right-[30%] top-0 bottom-0 bg-primary rounded-full"></div>
                            <div class="absolute left-[10%] top-1/2 -translate-y-1/2 w-4 h-4 bg-white border-2 border-primary rounded-full shadow cursor-pointer"></div>
                            <div class="absolute right-[30%] top-1/2 -translate-y-1/2 w-4 h-4 bg-white border-2 border-primary rounded-full shadow cursor-pointer"></div>
                        </div>
                    </div>
                    <div class="flex justify-between text-sm text-slate-500 dark:text-slate-400 font-medium">
                        <span>$200</span>
                        <span>$1,200</span>
                    </div>
                </div>
                
                <!-- Stops -->
                <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-slate-900 dark:text-white">Stops</h3>
                    </div>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary/50" type="checkbox"/>
                            <span class="text-sm text-slate-900 dark:text-slate-300 group-hover:text-primary transition-colors">Direct only</span>
                            <span class="ml-auto text-xs text-slate-500 dark:text-slate-500">$450</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input checked class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary/50" type="checkbox"/>
                            <span class="text-sm text-slate-900 dark:text-slate-300 group-hover:text-primary transition-colors">1 Stop</span>
                            <span class="ml-auto text-xs text-slate-500 dark:text-slate-500">$320</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary/50" type="checkbox"/>
                            <span class="text-sm text-slate-900 dark:text-slate-300 group-hover:text-primary transition-colors">2+ Stops</span>
                            <span class="ml-auto text-xs text-slate-500 dark:text-slate-500">$280</span>
                        </label>
                    </div>
                </div>
                
                <!-- Airlines -->
                <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-slate-900 dark:text-white">Airlines</h3>
                    </div>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input checked class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary/50" type="checkbox"/>
                            <span class="text-sm text-slate-900 dark:text-slate-300 group-hover:text-primary transition-colors">Delta</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input checked class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary/50" type="checkbox"/>
                            <span class="text-sm text-slate-900 dark:text-slate-300 group-hover:text-primary transition-colors">British Airways</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary/50" type="checkbox"/>
                            <span class="text-sm text-slate-900 dark:text-slate-300 group-hover:text-primary transition-colors">Emirates</span>
                        </label>
                    </div>
                </div>
                
                <!-- Departure Time -->
                <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-slate-900 dark:text-white">Departure Time</h3>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="flex-1 py-2 rounded border border-slate-200 dark:border-slate-700 bg-background-light dark:bg-background-dark text-xs font-medium hover:border-primary hover:text-primary transition-all flex flex-col items-center gap-1">
                            <span class="material-symbols-outlined text-base">wb_twilight</span>
                            Morning
                        </button>
                        <button type="button" class="flex-1 py-2 rounded border border-primary bg-primary/10 text-primary text-xs font-bold transition-all flex flex-col items-center gap-1">
                            <span class="material-symbols-outlined text-base">wb_sunny</span>
                            Afternoon
                        </button>
                        <button type="button" class="flex-1 py-2 rounded border border-slate-200 dark:border-slate-700 bg-background-light dark:bg-background-dark text-xs font-medium hover:border-primary hover:text-primary transition-all flex flex-col items-center gap-1">
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
                        <button class="px-4 py-1.5 rounded-md text-sm font-bold bg-primary text-white shadow-sm">Cheapest</button>
                        <button class="px-4 py-1.5 rounded-md text-sm font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">Fastest</button>
                        <button class="px-4 py-1.5 rounded-md text-sm font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">Best Value</button>
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
                        <button class="w-10 h-10 flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-surface-dark text-slate-500 hover:border-primary hover:text-primary transition-colors">
                            <span class="material-symbols-outlined">chevron_left</span>
                        </button>
                        <button class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary text-white font-bold shadow-sm shadow-primary/30">1</button>
                        <button class="w-10 h-10 flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-surface-dark text-slate-500 hover:border-primary hover:text-primary transition-colors font-medium">2</button>
                        <button class="w-10 h-10 flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-surface-dark text-slate-500 hover:border-primary hover:text-primary transition-colors font-medium">3</button>
                        <span class="px-2 text-slate-500">...</span>
                        <button class="w-10 h-10 flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-surface-dark text-slate-500 hover:border-primary hover:text-primary transition-colors">
                            <span class="material-symbols-outlined">chevron_right</span>
                        </button>
                    </nav>
                </div>
            </section>
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
        </div>
    </div>
</main>

<script>
<<<<<<< HEAD
=======
function setTripType(type) {
    document.getElementById('trip_type_input').value = type;
    const returnInput = document.querySelector('input[name="return_date"]');
    if (type === 'one_way' && returnInput) {
        returnInput.disabled = true;
    } else if (returnInput) {
        returnInput.disabled = false;
    }
}

>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
function swapAirports() {
    const origin = document.querySelector('input[name="origin"]');
    const destination = document.querySelector('input[name="destination"]');
    const temp = origin.value;
    origin.value = destination.value;
    destination.value = temp;
}
</script>
<?php include '../includes/footer.php'; ?>
<<<<<<< HEAD

=======
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
