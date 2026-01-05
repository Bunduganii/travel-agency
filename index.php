<?php
/**
 * Home Page / Customer Dashboard
 * Main landing page for customers
 */
require_once 'includes/db.php';
require_once 'includes/auth.php';

$page_title = 'Dashboard';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Redirect admin to admin dashboard
if (isAdmin()) {
    header('Location: admin/admin_dashboard.php');
    exit();
}

// Get user bookings count
$user_id = getUserId();
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM (
    SELECT id FROM flight_bookings WHERE user_id = ?
    UNION ALL
    SELECT id FROM hotel_reservations WHERE user_id = ?
    UNION ALL
    SELECT id FROM tour_bookings WHERE user_id = ?
) as bookings");
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$bookings_result = $stmt->get_result();
$total_bookings = $bookings_result->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Get next upcoming trip - fixed query
$next_trip = null;
// Try flights first
$stmt = $conn->prepare("SELECT 'flight' as type, id, booking_date as trip_date, status FROM flight_bookings WHERE user_id = ? AND status = 'confirmed' ORDER BY booking_date ASC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$flight_trip = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Try hotels
$stmt = $conn->prepare("SELECT 'hotel' as type, id, check_in as trip_date, status FROM hotel_reservations WHERE user_id = ? AND status = 'confirmed' ORDER BY check_in ASC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$hotel_trip = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Try tours
$stmt = $conn->prepare("SELECT 'tour' as type, id, travel_date as trip_date, status FROM tour_bookings WHERE user_id = ? AND status = 'confirmed' ORDER BY travel_date ASC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tour_trip = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Find earliest trip
$trips = array_filter([$flight_trip, $hotel_trip, $tour_trip]);
if (!empty($trips)) {
    usort($trips, function($a, $b) {
        return strtotime($a['trip_date']) - strtotime($b['trip_date']);
    });
    $next_trip = $trips[0];
}

include 'includes/header.php';
?>
<main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-10 scroll-smooth">
    <div class="max-w-[1200px] mx-auto flex flex-col gap-8">
        <section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex flex-col gap-1">
                <h2 class="text-text-main dark:text-white text-3xl md:text-4xl font-extrabold tracking-tight">Welcome back, <?php echo htmlspecialchars(explode(' ', getUserFullName())[0]); ?>!</h2>
                <p class="text-text-secondary dark:text-text-secondary-dark text-base">You have <span class="text-primary font-bold"><?php echo $total_bookings; ?> upcoming trips</span> scheduled.</p>
            </div>
            <div class="flex gap-3">
                <a href="customer/book_flight.php" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-bold shadow-lg shadow-primary/20 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    New Booking
                </a>
            </div>
        </section>
        
        <section class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <a href="customer/book_flight.php" class="flex flex-col items-center gap-3 bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-sm border border-transparent hover:border-primary/20 hover:shadow-md transition-all group">
                <div class="rounded-full bg-blue-50 dark:bg-blue-900/20 p-4 group-hover:bg-primary group-hover:text-white transition-colors text-primary">
                    <span class="material-symbols-outlined">flight</span>
                </div>
                <span class="text-text-main dark:text-white text-sm font-bold">Book Flight</span>
            </a>
            <a href="customer/reserve_hotel.php" class="flex flex-col items-center gap-3 bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-sm border border-transparent hover:border-primary/20 hover:shadow-md transition-all group">
                <div class="rounded-full bg-orange-50 dark:bg-orange-900/20 p-4 group-hover:bg-orange-500 group-hover:text-white transition-colors text-orange-500">
                    <span class="material-symbols-outlined">hotel</span>
                </div>
                <span class="text-text-main dark:text-white text-sm font-bold">Book Hotel</span>
            </a>
            <a href="customer/my_bookings.php" class="flex flex-col items-center gap-3 bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-sm border border-transparent hover:border-primary/20 hover:shadow-md transition-all group">
                <div class="rounded-full bg-purple-50 dark:bg-purple-900/20 p-4 group-hover:bg-purple-500 group-hover:text-white transition-colors text-purple-500">
                    <span class="material-symbols-outlined">person</span>
                </div>
                <span class="text-text-main dark:text-white text-sm font-bold">Profile</span>
            </a>
            <a href="customer/feedback.php" class="flex flex-col items-center gap-3 bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-sm border border-transparent hover:border-primary/20 hover:shadow-md transition-all group">
                <div class="rounded-full bg-green-50 dark:bg-green-900/20 p-4 group-hover:bg-green-500 group-hover:text-white transition-colors text-green-500">
                    <span class="material-symbols-outlined">support_agent</span>
                </div>
                <span class="text-text-main dark:text-white text-sm font-bold">Support</span>
            </a>
        </section>
        
        <?php if ($next_trip): ?>
        <section class="@container">
            <div class="flex flex-col md:flex-row bg-surface-light dark:bg-surface-dark rounded-2xl shadow-sm overflow-hidden border border-[#f0f3f5] dark:border-gray-700">
                <div class="w-full md:w-2/5 h-64 md:h-auto bg-center bg-no-repeat bg-cover relative" style="background-image: url('https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800');">
                    <div class="absolute top-4 left-4 bg-white/90 dark:bg-black/70 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold text-text-main dark:text-white flex items-center gap-1 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span> Confirmed
                    </div>
                </div>
                <div class="w-full md:w-3/5 p-6 md:p-8 flex flex-col justify-between">
                    <div class="flex flex-col gap-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-primary font-bold uppercase tracking-wider text-xs mb-1">Next Adventure</p>
                                <h3 class="text-text-main dark:text-white text-2xl md:text-3xl font-bold leading-tight">Paris, France</h3>
                            </div>
                            <div class="bg-[#f0f3f5] dark:bg-white/5 rounded-lg p-2 flex flex-col items-center min-w-[60px]">
                                <span class="text-xs font-bold text-text-secondary dark:text-text-secondary-dark uppercase">OCT</span>
                                <span class="text-xl font-black text-text-main dark:text-white">15</span>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2 border-l-2 border-primary/30 pl-4 py-1">
                            <div class="flex items-center gap-2 text-text-secondary dark:text-text-secondary-dark text-sm">
                                <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                                Oct 15 - Oct 22 (7 Days)
                            </div>
                            <div class="flex items-center gap-2 text-text-secondary dark:text-text-secondary-dark text-sm">
                                <span class="material-symbols-outlined text-[18px]">cloud</span>
                                Forecast: 18°C Partly Cloudy
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 mt-8 pt-6 border-t border-[#f0f3f5] dark:border-gray-700">
                        <a href="customer/my_bookings.php" class="flex-1 bg-primary hover:bg-primary-dark text-white h-10 px-6 rounded-lg text-sm font-bold transition-colors text-center flex items-center justify-center">View Itinerary</a>
                        <a href="customer/my_bookings.php" class="flex-1 bg-[#f0f3f5] hover:bg-gray-200 dark:bg-white/5 dark:hover:bg-white/10 text-text-main dark:text-white h-10 px-6 rounded-lg text-sm font-bold transition-colors text-center flex items-center justify-center">Manage Booking</a>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>
        
        <section>
            <h3 class="text-text-main dark:text-white text-xl font-bold mb-4">Other Upcoming Bookings</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                // Get all recent bookings (flights, hotels, tours)
                $all_bookings = [];
                
                // Get flight bookings
                $stmt = $conn->prepare("SELECT fb.*, f.origin, f.destination, f.departure_date, f.flight_number, f.airline FROM flight_bookings fb JOIN flights f ON fb.flight_id = f.id WHERE fb.user_id = ? ORDER BY fb.booking_date DESC LIMIT 3");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $flight_bookings = $stmt->get_result();
                while ($booking = $flight_bookings->fetch_assoc()) {
                    $booking['type'] = 'flight';
                    $all_bookings[] = $booking;
                }
                $stmt->close();
                
                // Get hotel bookings
                $stmt = $conn->prepare("SELECT hr.*, h.name, h.city FROM hotel_reservations hr JOIN hotels h ON hr.hotel_id = h.id WHERE hr.user_id = ? ORDER BY hr.booking_date DESC LIMIT 3");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $hotel_bookings = $stmt->get_result();
                while ($booking = $hotel_bookings->fetch_assoc()) {
                    $booking['type'] = 'hotel';
                    $all_bookings[] = $booking;
                }
                $stmt->close();
                
                // Get tour bookings
                $stmt = $conn->prepare("SELECT tb.*, tp.title, tp.destination FROM tour_bookings tb JOIN tour_packages tp ON tb.package_id = tp.id WHERE tb.user_id = ? ORDER BY tb.booking_date DESC LIMIT 3");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $tour_bookings = $stmt->get_result();
                while ($booking = $tour_bookings->fetch_assoc()) {
                    $booking['type'] = 'tour';
                    $all_bookings[] = $booking;
                }
                $stmt->close();
                
                // Sort by booking date and limit to 3
                usort($all_bookings, function($a, $b) {
                    $date_a = $a['booking_date'] ?? '';
                    $date_b = $b['booking_date'] ?? '';
                    return strtotime($date_b) - strtotime($date_a);
                });
                $all_bookings = array_slice($all_bookings, 0, 3);
                
                if (empty($all_bookings)):
                ?>
                <p class="text-text-secondary dark:text-text-secondary-dark">No upcoming bookings yet. Start by booking your first trip!</p>
                <?php else:
                    foreach ($all_bookings as $booking):
                        if ($booking['type'] == 'flight'):
                ?>
                <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-[#f0f3f5] dark:border-gray-700 p-5 flex flex-col gap-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined">flight_takeoff</span>
                            </div>
                            <div>
                                <p class="text-text-main dark:text-white font-bold"><?php echo htmlspecialchars($booking['origin'] ?? 'N/A'); ?> (JFK)</p>
                                <p class="text-text-secondary dark:text-text-secondary-dark text-xs">To <?php echo htmlspecialchars($booking['destination'] ?? 'N/A'); ?> (LHR)</p>
                            </div>
                        </div>
                        <span class="bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-[10px] font-bold px-2 py-1 rounded">CONFIRMED</span>
                    </div>
                    <div class="h-px bg-[#f0f3f5] dark:bg-gray-700"></div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-text-secondary dark:text-text-secondary-dark"><?php echo htmlspecialchars($booking['airline'] ?? ''); ?> <?php echo htmlspecialchars($booking['flight_number'] ?? ''); ?></span>
                        <span class="text-text-main dark:text-white font-medium"><?php echo date('M d', strtotime($booking['departure_date'])); ?> • <?php echo date('H:i', strtotime($booking['departure_date'])); ?></span>
                    </div>
                    <a href="customer/my_bookings.php" class="w-full mt-auto py-2 rounded-lg border border-[#f0f3f5] dark:border-gray-700 text-text-main dark:text-white text-sm font-bold hover:bg-[#f0f3f5] dark:hover:bg-white/5 transition-colors text-center">Flight Details</a>
                </div>
                <?php elseif ($booking['type'] == 'hotel'): ?>
                <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-[#f0f3f5] dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow flex flex-col">
                    <div class="h-32 bg-center bg-cover bg-no-repeat relative" style="background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400');">
                        <div class="absolute top-2 right-2 bg-white/90 dark:bg-black/60 px-2 py-1 rounded text-xs font-bold text-text-main dark:text-white">3 Nights</div>
                    </div>
                    <div class="p-5 flex flex-col gap-2 flex-1">
                        <h4 class="text-text-main dark:text-white font-bold leading-tight"><?php echo htmlspecialchars($booking['name'] ?? 'Hotel'); ?></h4>
                        <p class="text-text-secondary dark:text-text-secondary-dark text-xs flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">location_on</span>
                            <?php echo htmlspecialchars($booking['city'] ?? 'N/A'); ?>
                        </p>
                        <p class="text-text-secondary dark:text-text-secondary-dark text-sm mt-2"><?php echo date('M d', strtotime($booking['check_in'])); ?> - <?php echo date('M d', strtotime($booking['check_out'])); ?></p>
                        <a href="customer/my_bookings.php" class="w-full mt-auto py-2 rounded-lg border border-[#f0f3f5] dark:border-gray-700 text-text-main dark:text-white text-sm font-bold hover:bg-[#f0f3f5] dark:hover:bg-white/5 transition-colors text-center">Voucher</a>
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-[#f0f3f5] dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow flex flex-col">
                    <div class="h-32 bg-center bg-cover bg-no-repeat relative" style="background-image: url('https://images.unsplash.com/photo-1539650116574-75c0c6d73a6e?w=400');"></div>
                    <div class="p-5 flex flex-col gap-2 flex-1">
                        <div class="flex items-center gap-2 text-purple-600 dark:text-purple-400 text-xs font-bold uppercase">
                            <span class="material-symbols-outlined text-[16px]">local_activity</span> Excursion
                        </div>
                        <h4 class="text-text-main dark:text-white font-bold leading-tight"><?php echo htmlspecialchars($booking['title'] ?? 'Tour'); ?></h4>
                        <p class="text-text-secondary dark:text-text-secondary-dark text-sm mt-2"><?php echo date('M d', strtotime($booking['travel_date'])); ?> • 10:00 AM</p>
                        <a href="customer/my_bookings.php" class="w-full mt-auto py-2 rounded-lg border border-[#f0f3f5] dark:border-gray-700 text-text-main dark:text-white text-sm font-bold hover:bg-[#f0f3f5] dark:hover:bg-white/5 transition-colors text-center">Details</a>
                    </div>
                </div>
                <?php 
                        endif;
                    endforeach;
                endif; ?>
            </div>
        </section>
    </div>
    <div class="h-20"></div>
</main>
<?php include 'includes/footer.php'; ?>

