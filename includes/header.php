<?php
/**
 * Header Include File
 * Contains common header HTML and navigation
 */
if (!isset($page_title)) {
    $page_title = 'Travel Agency';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Travel Agency</title>
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
    <?php endif; ?>

