<?php
/**
 * Saved Items Page
 * Customer page to view saved flights, hotels, and tours
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireCustomer();

$page_title = 'Saved Items';

include '../includes/header.php';
?>
<main class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark p-6 lg:p-8">
    <div class="max-w-7xl mx-auto space-y-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Saved Items</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Your favorite flights, hotels, and packages.</p>
            </div>
        </div>
        
        <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm p-8 text-center">
            <span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600 mb-4">favorite</span>
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">No Saved Items Yet</h3>
            <p class="text-slate-500 dark:text-slate-400 mb-6">Start saving your favorite flights, hotels, and packages for later.</p>
            <a href="book_flight.php" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-sky-500 transition-colors">
                <span class="material-symbols-outlined">explore</span>
                Browse Flights
            </a>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>

