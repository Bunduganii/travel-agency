<?php
/**
 * Header Include File
 * Contains common header HTML and navigation
 */
if (!isset($page_title)) {
    $page_title = 'Travel Agency';
}
<<<<<<< HEAD
?>
<!DOCTYPE html>
<html lang="en">
=======

// Load config if not already loaded - SIMPLE AND RELIABLE
if (!defined('ASSETS_PATH')) {
    // Get the actual file path of the script that included this header
    $included_from = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'] ?? __FILE__;
    $included_dir = dirname($included_from);
    
    // Check if assets folder exists in the same directory as the including file
    if (file_exists($included_dir . DIRECTORY_SEPARATOR . 'assets')) {
        $assets_path = 'assets/';
        $base_path = '';
    } 
    // Check if assets folder exists one level up
    elseif (file_exists(dirname($included_dir) . DIRECTORY_SEPARATOR . 'assets')) {
        $assets_path = '../assets/';
        $base_path = '../';
    }
    // Fallback: use script path detection
    else {
        $script_name = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
        $script_dir = dirname($script_name);
        $script_dir = str_replace('\\', '/', $script_dir);
        
        if (strpos($script_dir, '/admin') !== false || strpos($script_dir, '/customer') !== false ||
            strpos($script_dir, '\\admin') !== false || strpos($script_dir, '\\customer') !== false) {
            $assets_path = '../assets/';
            $base_path = '../';
        } else {
            $assets_path = 'assets/';
            $base_path = '';
        }
    }
    
    define('ASSETS_PATH', $assets_path);
    define('BASE_PATH', $base_path);
}

$assets_path = ASSETS_PATH;
$base_path = BASE_PATH;
?>
<!DOCTYPE html>
<html class="light" lang="en">
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Travel Agency</title>
<<<<<<< HEAD
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <header class="main-header">
        <div class="header-container">
            <div class="logo">
                <i class="fas fa-plane"></i>
                <span>Travel Agency</span>
            </div>
            <nav class="main-nav">
                <?php if (isAdmin()): ?>
                    <a href="admin/admin_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
                    <a href="admin/manage_flights.php">Flights</a>
                    <a href="admin/manage_hotels.php">Hotels</a>
                    <a href="admin/manage_tours.php">Tours</a>
                <?php else: ?>
                    <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a>
                    <a href="customer/book_flight.php">Flights</a>
                    <a href="customer/reserve_hotel.php">Hotels</a>
                    <a href="customer/tour_packages.php">Packages</a>
                    <a href="customer/my_bookings.php">My Bookings</a>
                <?php endif; ?>
            </nav>
            <div class="header-actions">
                <div class="user-profile">
                    <span class="user-name"><?php echo htmlspecialchars(getUserFullName()); ?></span>
                    <span class="user-type"><?php echo isAdmin() ? 'Admin' : 'Customer'; ?></span>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>
=======
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Theme Configuration -->
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#0db9f2",
                        "primary-dark": "#0a9acb",
                        "background-light": "#f5f8f8",
                        "background-dark": "#101e22",
                        "surface-light": "#ffffff",
                        "surface-dark": "#1a2c32",
                        "text-main": "#111618",
                        "text-secondary": "#60808a",
                        "text-main-dark": "#e0e6e8",
                        "text-secondary-dark": "#94aab2",
                    },
                    fontFamily: {
                        "display": ["Plus Jakarta Sans", "sans-serif"]
                    },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-white">
    <?php if (isLoggedIn()): ?>
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="flex-1 flex flex-col h-screen relative overflow-hidden">
        <header class="h-16 bg-surface-light dark:bg-surface-dark border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 z-10 flex-shrink-0">
            <div class="flex items-center gap-4">
                <button class="md:hidden text-slate-500 hover:text-slate-700">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <div class="hidden md:flex items-center bg-background-light dark:bg-slate-800 rounded-lg px-3 py-2 w-64 lg:w-96 border border-transparent focus-within:border-primary/50 transition-colors">
                    <span class="material-symbols-outlined text-slate-400 text-[20px]">search</span>
                    <input class="bg-transparent border-none outline-none text-sm ml-2 w-full text-slate-700 dark:text-slate-200 placeholder:text-slate-400 focus:ring-0" placeholder="<?php echo isAdmin() ? 'Search bookings, flights, users...' : 'Search destinations, hotels...'; ?>" type="text">
                </div>
            </div>
            <div class="flex items-center gap-4">
                <button class="relative p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white transition-colors">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white dark:border-surface-dark"></span>
                </button>
                <div class="flex items-center gap-3 pl-4 border-l border-slate-200 dark:border-slate-700">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-slate-900 dark:text-white leading-none"><?php echo htmlspecialchars(getUserFullName()); ?></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?php echo isAdmin() ? 'Super Admin' : 'Gold Member'; ?></p>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden border-2 border-white dark:border-slate-600 shadow-sm">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode(getUserFullName()); ?>&background=0db9f2&color=fff" alt="Profile" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>
        </header>
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
    <?php endif; ?>

