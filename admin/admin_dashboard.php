<?php
/**
 * Admin Dashboard
 * Main dashboard for admin users
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$page_title = 'Admin Dashboard';

// Get statistics
$stats = [];

// Total Revenue
$result = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'completed'");
$stats['revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Total Bookings
$result = $conn->query("SELECT COUNT(*) as total FROM (
    SELECT id FROM flight_bookings UNION ALL
    SELECT id FROM hotel_reservations UNION ALL
    SELECT id FROM tour_bookings
) as bookings");
$stats['bookings'] = $result->fetch_assoc()['total'] ?? 0;

// Active Users
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE user_type = 'customer'");
$stats['users'] = $result->fetch_assoc()['total'] ?? 0;

// Pending Inquiries
$result = $conn->query("SELECT COUNT(*) as total FROM feedback WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stats['inquiries'] = $result->fetch_assoc()['total'] ?? 0;

include '../includes/header.php';
?>
<main class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark p-6 lg:p-8">
    <div class="max-w-7xl mx-auto space-y-8">
        <div class="overview-header">
            <div>
                <h1>Dashboard Overview</h1>
                <p>Welcome back, here's what's happening today.</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline">
                    <i class="fas fa-cloud-download-alt"></i> Export Report
                </button>
                <a href="../customer/book_flight.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Booking
                </a>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <h3>$<?php echo number_format($stats['revenue'], 2); ?></h3>
                    <p>Total Revenue</p>
                    <span class="stat-change positive">+5% <i class="fas fa-arrow-up"></i></span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['bookings']; ?></h3>
                    <p>Total Bookings</p>
                    <span class="stat-change positive">+12% <i class="fas fa-arrow-up"></i></span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['users']; ?></h3>
                    <p>Active Users</p>
                    <span class="stat-change positive">+2% <i class="fas fa-arrow-up"></i></span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['inquiries']; ?></h3>
                    <p>Pending Inquiries</p>
                    <span class="stat-change negative">-1% <i class="fas fa-arrow-down"></i></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="dashboard-content">
        <div class="revenue-analytics">
            <div class="section-header">
                <h2>Revenue Analytics</h2>
                <select class="select-dropdown">
                    <option>Last 30 Days</option>
                    <option>Last 7 Days</option>
                    <option>Last Year</option>
                </select>
            </div>
            <div class="chart-placeholder">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
        
        <div class="quick-actions-admin">
            <h2>Quick Actions</h2>
            <div class="actions-grid">
                <a href="manage_flights.php" class="action-btn">
                    <i class="fas fa-plus"></i>
                    <span>Add Booking</span>
                </a>
                <a href="manage_flights.php" class="action-btn">
                    <i class="fas fa-plane"></i>
                    <span>Manage Flights</span>
                </a>
                <a href="#" class="action-btn">
                    <i class="fas fa-shield-alt"></i>
                    <span>Verify User</span>
                </a>
                <a href="manage_tours.php" class="action-btn">
                    <i class="fas fa-suitcase"></i>
                    <span>Create Package</span>
                </a>
            </div>
        </div>
    </div>
    
    <div class="system-alerts">
        <h2>System Alerts</h2>
        <div class="alert-card error">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>3 Flight Schedule Changes</strong>
                <p>Flights AA102, BA440 require attention.</p>
            </div>
        </div>
        <div class="alert-card warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>5 Unresolved Tickets</strong>
                <p>Support queue is higher than average.</p>
            <div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Dashboard Overview</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Welcome back, here's what's happening today.</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">cloud_download</span>
                    Export Report
                </button>
                <button class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-sky-500 transition-colors shadow-md shadow-primary/20">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    New Booking
                </button>
            </div>
        </div>
        
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
                <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-1">$<?php echo number_format($stats['revenue'], 0); ?></h3>
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
                <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-1"><?php echo $stats['bookings']; ?></h3>
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
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Active Users</p>
                <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-1"><?php echo $stats['users']; ?></h3>
            </div>
            
            <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div class="bg-orange-100 dark:bg-orange-900/30 p-2.5 rounded-lg text-orange-600 dark:text-orange-400">
                        <span class="material-symbols-outlined">pending_actions</span>
                    </div>
                    <span class="flex items-center text-xs font-semibold text-red-600 bg-red-50 dark:bg-red-900/20 px-2 py-1 rounded-full">
                        <span class="material-symbols-outlined text-[14px] mr-1">trending_down</span>
                        -1%
                    </span>
                </div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Pending Inquiries</p>
                <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-1"><?php echo $stats['inquiries']; ?></h3>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Revenue Analytics</h3>
                    <select class="bg-slate-50 dark:bg-slate-800 border-none text-xs font-medium text-slate-600 dark:text-slate-300 rounded-lg py-1 px-3 focus:ring-0">
                        <option>Last 30 Days</option>
                        <option>Last 6 Months</option>
                        <option>This Year</option>
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
                        <div class="w-full bg-primary/20 dark:bg-primary/10 rounded-t-sm h-52 group-hover:bg-primary transition-colors relative">
                            <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] py-1 px-2 rounded pointer-events-none transition-opacity">$21k</div>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">Apr</span>
                    </div>
                    <div class="flex flex-col items-center gap-2 flex-1 group cursor-pointer">
                        <div class="w-full bg-primary/20 dark:bg-primary/10 rounded-t-sm h-44 group-hover:bg-primary transition-colors relative">
                            <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] py-1 px-2 rounded pointer-events-none transition-opacity">$18k</div>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">May</span>
                    </div>
                    <div class="flex flex-col items-center gap-2 flex-1 group cursor-pointer">
                        <div class="w-full bg-primary rounded-t-sm h-60 relative shadow-[0_0_15px_rgba(13,185,242,0.4)]">
                            <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] py-1 px-2 rounded pointer-events-none font-bold">$24.5k</div>
                        </div>
                        <span class="text-[10px] text-slate-600 dark:text-slate-300 font-bold">Jun</span>
                    </div>
                    <div class="flex flex-col items-center gap-2 flex-1 group cursor-pointer">
                        <div class="w-full bg-primary/20 dark:bg-primary/10 rounded-t-sm h-24 group-hover:bg-primary transition-colors relative">
                            <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] py-1 px-2 rounded pointer-events-none transition-opacity">$9k</div>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">Jul</span>
                    </div>
                    <div class="flex flex-col items-center gap-2 flex-1 group cursor-pointer">
                        <div class="w-full bg-primary/20 dark:bg-primary/10 rounded-t-sm h-36 group-hover:bg-primary transition-colors relative">
                            <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] py-1 px-2 rounded pointer-events-none transition-opacity">$14k</div>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">Aug</span>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col gap-6">
                <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <button class="flex flex-col items-center justify-center p-4 rounded-xl border border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-primary/30 transition-all group">
                            <div class="bg-primary/10 p-2 rounded-full mb-2 group-hover:bg-primary group-hover:text-white text-primary transition-colors">
                                <span class="material-symbols-outlined">add_circle</span>
                            </div>
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Add Booking</span>
                        </button>
                        <button class="flex flex-col items-center justify-center p-4 rounded-xl border border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-primary/30 transition-all group">
                            <div class="bg-primary/10 p-2 rounded-full mb-2 group-hover:bg-primary group-hover:text-white text-primary transition-colors">
                                <span class="material-symbols-outlined">flight</span>
                            </div>
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Manage Flights</span>
                        </button>
                        <button class="flex flex-col items-center justify-center p-4 rounded-xl border border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-primary/30 transition-all group">
                            <div class="bg-primary/10 p-2 rounded-full mb-2 group-hover:bg-primary group-hover:text-white text-primary transition-colors">
                                <span class="material-symbols-outlined">verified_user</span>
                            </div>
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Verify User</span>
                        </button>
                        <button class="flex flex-col items-center justify-center p-4 rounded-xl border border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-primary/30 transition-all group">
                            <div class="bg-primary/10 p-2 rounded-full mb-2 group-hover:bg-primary group-hover:text-white text-primary transition-colors">
                                <span class="material-symbols-outlined">confirmation_number</span>
                            </div>
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Create Package</span>
                        </button>
                    </div>
                </div>
                
                <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm flex-1">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">System Alerts</h3>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3 p-3 bg-red-50 dark:bg-red-900/10 rounded-lg border border-red-100 dark:border-red-900/20">
                            <span class="material-symbols-outlined text-red-500 text-sm mt-0.5">error</span>
                            <div>
                                <p class="text-xs font-bold text-red-700 dark:text-red-400">3 Flight Schedule Changes</p>
                                <p class="text-[10px] text-red-600/80 dark:text-red-400/70 mt-0.5">Flights AA102, BA440 require attention.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-amber-50 dark:bg-amber-900/10 rounded-lg border border-amber-100 dark:border-amber-900/20">
                            <span class="material-symbols-outlined text-amber-500 text-sm mt-0.5">warning</span>
                            <div>
                                <p class="text-xs font-bold text-amber-700 dark:text-amber-400">5 Unresolved Tickets</p>
                                <p class="text-[10px] text-amber-600/80 dark:text-amber-400/70 mt-0.5">Support queue is higher than average.</p>
                            </div>
                        </div>
                    </div>
                </div>
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
            </div>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>

