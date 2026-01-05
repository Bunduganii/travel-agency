<?php
/**
 * Wallet Page
 * Customer page to view wallet balance and transactions
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireCustomer();

$page_title = 'Wallet';

$user_id = getUserId();

// Get wallet balance (if wallet table exists, otherwise show placeholder)
$wallet_balance = 0.00;
$stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as balance FROM payments WHERE user_id = ? AND status = 'completed'");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $row = $result->fetch_assoc();
        $wallet_balance = $row['balance'] ?? 0.00;
    }
    $stmt->close();
}

include '../includes/header.php';
?>
<main class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark p-6 lg:p-8">
    <div class="max-w-7xl mx-auto space-y-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">My Wallet</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Manage your travel funds and transactions.</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gradient-to-br from-primary/10 to-blue-200/20 dark:from-primary/5 dark:to-blue-900/10 p-6 rounded-xl border border-primary/20">
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-primary/20 p-2 rounded-lg">
                        <span class="material-symbols-outlined text-primary text-2xl">account_balance_wallet</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Available Balance</p>
                        <h3 class="text-3xl font-bold text-slate-900 dark:text-white">$<?php echo number_format($wallet_balance, 2); ?></h3>
                    </div>
                </div>
            </div>
            
            <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-green-100 dark:bg-green-900/30 p-2 rounded-lg">
                        <span class="material-symbols-outlined text-green-600 dark:text-green-400">trending_up</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Total Spent</p>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white">$<?php echo number_format($wallet_balance, 2); ?></h3>
                    </div>
                </div>
            </div>
            
            <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-blue-100 dark:bg-blue-900/30 p-2 rounded-lg">
                        <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">receipt</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Transactions</p>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white">0</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Recent Transactions</h3>
            </div>
            <div class="p-8 text-center">
                <span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600 mb-4">receipt_long</span>
                <p class="text-slate-500 dark:text-slate-400">No transactions yet.</p>
            </div>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>

