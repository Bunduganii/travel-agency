<?php
/**
 * Sidebar Navigation
 * Left sidebar for admin and customer dashboards
 */
if (!defined('BASE_PATH')) {
    $base_path = '';
} else {
    $base_path = BASE_PATH;
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="flex h-screen w-full">
<aside class="w-64 flex-shrink-0 bg-surface-light dark:bg-surface-dark border-r border-slate-200 dark:border-slate-800 flex flex-col transition-all duration-300 hidden md:flex">
    <div class="h-16 flex items-center px-6 border-b border-slate-100 dark:border-slate-800">
        <div class="flex items-center gap-3">
            <div class="bg-primary/10 p-1.5 rounded-lg text-primary">
                <span class="material-symbols-outlined text-2xl">flight_takeoff</span>
            </div>
            <h1 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white"><?php echo isAdmin() ? 'SkyTravel' : 'TravelCo'; ?></h1>
        </div>
    </div>
    <div class="flex-1 overflow-y-auto py-4 px-3 flex flex-col gap-1">
        <?php if (isAdmin()): ?>
            <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-2">Main Menu</p>
            <a href="<?php echo $base_path; ?>admin/admin_dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'admin_dashboard.php' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors group'; ?>">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="text-sm font-medium">Dashboard</span>
            </a>
            <a href="<?php echo $base_path; ?>admin/manage_bookings.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'manage_bookings.php' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors group'; ?>">
                <span class="material-symbols-outlined group-hover:text-primary transition-colors">book_online</span>
                <span class="text-sm font-medium">Bookings</span>
            </a>
            <a href="<?php echo $base_path; ?>admin/manage_flights.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'manage_flights.php' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors group'; ?>">
                <span class="material-symbols-outlined group-hover:text-primary transition-colors">flight</span>
                <span class="text-sm font-medium">Flights</span>
            </a>
            <a href="<?php echo $base_path; ?>admin/manage_hotels.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'manage_hotels.php' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors group'; ?>">
                <span class="material-symbols-outlined group-hover:text-primary transition-colors">hotel</span>
                <span class="text-sm font-medium">Hotels</span>
            </a>
            <a href="<?php echo $base_path; ?>admin/manage_tours.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'manage_tours.php' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors group'; ?>">
                <span class="material-symbols-outlined group-hover:text-primary transition-colors">luggage</span>
                <span class="text-sm font-medium">Packages</span>
            </a>
            <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-6">Administration</p>
            <a href="<?php echo $base_path; ?>admin/manage_users.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'manage_users.php' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors group'; ?>">
                <span class="material-symbols-outlined group-hover:text-primary transition-colors">group</span>
                <span class="text-sm font-medium">Users</span>
            </a>
            <a href="<?php echo $base_path; ?>admin/reports.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'reports.php' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors group'; ?>">
                <span class="material-symbols-outlined group-hover:text-primary transition-colors">analytics</span>
                <span class="text-sm font-medium">Reports</span>
            </a>
            <a href="<?php echo $base_path; ?>admin/settings.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'settings.php' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors group'; ?>">
                <span class="material-symbols-outlined group-hover:text-primary transition-colors">settings</span>
                <span class="text-sm font-medium">Settings</span>
            </a>
        <?php else: ?>
            <a href="<?php echo $base_path; ?>index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'index.php' ? 'bg-primary/10 text-primary dark:text-primary transition-colors' : 'hover:bg-[#f0f3f5] dark:hover:bg-white/5 text-text-main dark:text-text-main-dark transition-colors group'; ?>">
                <span class="material-symbols-outlined">dashboard</span>
                <p class="text-sm <?php echo $current_page == 'index.php' ? 'font-bold' : 'font-medium'; ?>">Dashboard</p>
            </a>
            <a href="<?php echo $base_path; ?>customer/my_bookings.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'my_bookings.php' ? 'bg-primary/10 text-primary dark:text-primary transition-colors' : 'hover:bg-[#f0f3f5] dark:hover:bg-white/5 text-text-main dark:text-text-main-dark transition-colors group'; ?>">
                <span class="material-symbols-outlined text-text-secondary group-hover:text-text-main dark:text-text-secondary-dark dark:group-hover:text-white">luggage</span>
                <p class="text-sm <?php echo $current_page == 'my_bookings.php' ? 'font-bold' : 'font-medium'; ?>">My Trips</p>
            </a>
            <a href="<?php echo $base_path; ?>customer/saved.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'saved.php' ? 'bg-primary/10 text-primary dark:text-primary transition-colors' : 'hover:bg-[#f0f3f5] dark:hover:bg-white/5 text-text-main dark:text-text-main-dark transition-colors group'; ?>">
                <span class="material-symbols-outlined text-text-secondary group-hover:text-text-main dark:text-text-secondary-dark dark:group-hover:text-white">favorite</span>
                <p class="text-sm <?php echo $current_page == 'saved.php' ? 'font-bold' : 'font-medium'; ?>">Saved</p>
            </a>
            <a href="<?php echo $base_path; ?>customer/wallet.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'wallet.php' ? 'bg-primary/10 text-primary dark:text-primary transition-colors' : 'hover:bg-[#f0f3f5] dark:hover:bg-white/5 text-text-main dark:text-text-main-dark transition-colors group'; ?>">
                <span class="material-symbols-outlined text-text-secondary group-hover:text-text-main dark:text-text-secondary-dark dark:group-hover:text-white">account_balance_wallet</span>
                <p class="text-sm <?php echo $current_page == 'wallet.php' ? 'font-bold' : 'font-medium'; ?>">Wallet</p>
            </a>
            <div class="h-px bg-gray-200 dark:bg-gray-700 my-2 mx-3"></div>
            <a href="<?php echo $base_path; ?>customer/settings.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'settings.php' ? 'bg-primary/10 text-primary dark:text-primary transition-colors' : 'hover:bg-[#f0f3f5] dark:hover:bg-white/5 text-text-main dark:text-text-main-dark transition-colors group'; ?>">
                <span class="material-symbols-outlined text-text-secondary group-hover:text-text-main dark:text-text-secondary-dark dark:group-hover:text-white">settings</span>
                <p class="text-sm <?php echo $current_page == 'settings.php' ? 'font-bold' : 'font-medium'; ?>">Settings</p>
            </a>
        <?php endif; ?>
    </div>
    <?php if (isAdmin()): ?>
    <div class="p-4 border-t border-slate-100 dark:border-slate-800 space-y-2">
        <div class="bg-gradient-to-br from-primary/10 to-blue-200/20 dark:from-primary/5 dark:to-blue-900/10 p-4 rounded-xl mb-2">
            <div class="flex items-center gap-3 mb-2">
                <span class="material-symbols-outlined text-primary">headset_mic</span>
                <span class="text-sm font-bold text-slate-800 dark:text-white">Support</span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Need help with the platform?</p>
            <a href="https://wa.me/1234567890?text=Hello%2C%20I%20need%20help%20with%20the%20SkyTravel%20platform" target="_blank" rel="noopener noreferrer" class="w-full bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-medium py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:shadow-sm flex items-center justify-center gap-2 transition-all hover:bg-green-50 dark:hover:bg-green-900/10">
                <span class="material-symbols-outlined text-[16px]">chat</span>
                Contact Tech
            </a>
        </div>
        <a href="<?php echo $base_path; ?>logout.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/10 text-slate-600 dark:text-slate-300 transition-colors group">
            <span class="material-symbols-outlined text-text-secondary group-hover:text-red-500 dark:text-text-secondary-dark">logout</span>
            <span class="text-sm font-medium group-hover:text-red-500">Logout</span>
        </a>
    </div>
    <?php else: ?>
    <div class="p-4 border-t border-[#f0f3f5] dark:border-gray-700">
        <a href="<?php echo $base_path; ?>logout.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/10 text-text-main dark:text-text-main-dark transition-colors group">
            <span class="material-symbols-outlined text-text-secondary group-hover:text-red-500 dark:text-text-secondary-dark">logout</span>
            <p class="text-sm font-medium group-hover:text-red-500">Logout</p>
        </a>
    </div>
    <?php endif; ?>
</aside>

