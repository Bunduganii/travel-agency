<?php
/**
 * Reports Page
 * Admin page for viewing system reports
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$page_title = 'Reports';

// Get report statistics
$total_revenue = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0;
$total_bookings = $conn->query("SELECT COUNT(*) as total FROM (
    SELECT id FROM flight_bookings UNION ALL
    SELECT id FROM hotel_reservations UNION ALL
    SELECT id FROM tour_bookings
) as bookings")->fetch_assoc()['total'] ?? 0;
$total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'] ?? 0;
$total_customers = $conn->query("SELECT COUNT(*) as total FROM users WHERE user_type = 'customer'")->fetch_assoc()['total'] ?? 0;

// Get revenue by type
$flight_revenue = $conn->query("SELECT COALESCE(SUM(total_price), 0) as total FROM flight_bookings WHERE status = 'confirmed'")->fetch_assoc()['total'] ?? 0;
$hotel_revenue = $conn->query("SELECT COALESCE(SUM(total_price), 0) as total FROM hotel_reservations WHERE status = 'confirmed'")->fetch_assoc()['total'] ?? 0;
$tour_revenue = $conn->query("SELECT COALESCE(SUM(total_price), 0) as total FROM tour_bookings WHERE status = 'confirmed'")->fetch_assoc()['total'] ?? 0;

include '../includes/header.php';
?>
<main class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark p-6 lg:p-8">
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Reports & Analytics</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Comprehensive reports and analytics for your travel agency</p>
            </div>
            <div class="flex gap-3">
                <button class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">download</span>
                    Export Report
                </button>
                <button class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors shadow-md shadow-primary/20">
                    <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                    Custom Date Range
                </button>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
            <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div class="bg-green-100 dark:bg-green-900/30 p-2.5 rounded-lg text-green-600 dark:text-green-400">
                        <span class="material-symbols-outlined">payments</span>
                    </div>
                    <span class="flex items-center text-xs font-semibold text-green-600 bg-green-50 dark:bg-green-900/20 px-2 py-1 rounded-full">
                        <span class="material-symbols-outlined text-[14px] mr-1">trending_up</span>
                        +5%
                    </span>
                </div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Revenue</p>
                <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-1">$<?php echo number_format($total_revenue, 2); ?></h3>
            </div>
            
            <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div class="bg-blue-100 dark:bg-blue-900/30 p-2.5 rounded-lg text-blue-600 dark:text-blue-400">
                        <span class="material-symbols-outlined">book_online</span>
                    </div>
                    <span class="flex items-center text-xs font-semibold text-green-600 bg-green-50 dark:bg-green-900/20 px-2 py-1 rounded-full">
                        <span class="material-symbols-outlined text-[14px] mr-1">trending_up</span>
                        +12%
                    </span>
                </div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Bookings</p>
                <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-1"><?php echo $total_bookings; ?></h3>
            </div>
            
            <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div class="bg-violet-100 dark:bg-violet-900/30 p-2.5 rounded-lg text-violet-600 dark:text-violet-400">
                        <span class="material-symbols-outlined">group</span>
                    </div>
                    <span class="flex items-center text-xs font-semibold text-green-600 bg-green-50 dark:bg-green-900/20 px-2 py-1 rounded-full">
                        <span class="material-symbols-outlined text-[14px] mr-1">trending_up</span>
                        +2%
                    </span>
                </div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Users</p>
                <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-1"><?php echo $total_users; ?></h3>
            </div>
            
            <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div class="bg-orange-100 dark:bg-orange-900/30 p-2.5 rounded-lg text-orange-600 dark:text-orange-400">
                        <span class="material-symbols-outlined">person</span>
                    </div>
                </div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Customers</p>
                <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-1"><?php echo $total_customers; ?></h3>
            </div>
        </div>

        <!-- Revenue Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Revenue by Type</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/10 rounded-lg">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">flight</span>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Flights</span>
                        </div>
                        <span class="text-sm font-bold text-slate-900 dark:text-white">$<?php echo number_format($flight_revenue, 2); ?></span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-orange-50 dark:bg-orange-900/10 rounded-lg">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-orange-600 dark:text-orange-400">hotel</span>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Hotels</span>
                        </div>
                        <span class="text-sm font-bold text-slate-900 dark:text-white">$<?php echo number_format($hotel_revenue, 2); ?></span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-purple-50 dark:bg-purple-900/10 rounded-lg">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-purple-600 dark:text-purple-400">luggage</span>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Tours</span>
                        </div>
                        <span class="text-sm font-bold text-slate-900 dark:text-white">$<?php echo number_format($tour_revenue, 2); ?></span>
                    </div>
                </div>
            </div>

            <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Revenue Analytics</h3>
                    <select class="px-3 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-medium text-slate-600 dark:text-slate-300 focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option>Last 30 Days</option>
                        <option>Last 7 Days</option>
                        <option>Last Year</option>
                    </select>
                </div>
                <div class="h-64 w-full flex items-end justify-between gap-2 px-2 pb-2">
                    <div class="flex flex-col items-center gap-2 flex-1 group cursor-pointer">
                        <div class="w-full bg-primary/20 dark:bg-primary/10 rounded-t-sm h-32 group-hover:bg-primary transition-colors relative">
                            <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] py-1 px-2 rounded pointer-events-none transition-opacity">$12k</div>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">Jan</span>
                    </div>
                    <div class="flex flex-col items-center gap-2 flex-1 group cursor-pointer">
                        <div class="w-full bg-primary/20 dark:bg-primary/10 rounded-t-sm h-40 group-hover:bg-primary transition-colors relative">
                            <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] py-1 px-2 rounded pointer-events-none transition-opacity">$15k</div>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">Feb</span>
                    </div>
                    <div class="flex flex-col items-center gap-2 flex-1 group cursor-pointer">
                        <div class="w-full bg-primary/20 dark:bg-primary/10 rounded-t-sm h-28 group-hover:bg-primary transition-colors relative">
                            <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] py-1 px-2 rounded pointer-events-none transition-opacity">$10k</div>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">Mar</span>
                    </div>
                    <div class="flex flex-col items-center gap-2 flex-1 group cursor-pointer">
                        <div class="w-full bg-primary/20 dark:bg-primary/10 rounded-t-sm h-36 group-hover:bg-primary transition-colors relative">
                            <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] py-1 px-2 rounded pointer-events-none transition-opacity">$13k</div>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">Apr</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-3">
                    <div class="bg-blue-100 dark:bg-blue-900/30 p-3 rounded-lg text-blue-600 dark:text-blue-400">
                        <span class="material-symbols-outlined">book_online</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Booking Reports</h3>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">View detailed booking reports by type, date, and status.</p>
                <a href="manage_bookings.php" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    View Report
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </a>
            </div>
            
            <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-3">
                    <div class="bg-violet-100 dark:bg-violet-900/30 p-3 rounded-lg text-violet-600 dark:text-violet-400">
                        <span class="material-symbols-outlined">group</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">User Activity</h3>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Track user registrations and activity over time.</p>
                <a href="manage_users.php" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    View Report
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </a>
            </div>
            
            <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 mb-3">
                    <div class="bg-green-100 dark:bg-green-900/30 p-3 rounded-lg text-green-600 dark:text-green-400">
                        <span class="material-symbols-outlined">receipt_long</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Financial Summary</h3>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Complete financial breakdown and revenue analysis.</p>
                <button class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    View Report
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </button>
            </div>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
