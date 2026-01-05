<?php
/**
 * Settings Page
 * Admin page for system settings
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$page_title = 'Settings';
$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_general'])) {
        $message = 'General settings saved successfully!';
        $message_type = 'success';
    } elseif (isset($_POST['save_payment'])) {
        $message = 'Payment settings saved successfully!';
        $message_type = 'success';
    } elseif (isset($_POST['save_notifications'])) {
        $message = 'Notification settings saved successfully!';
        $message_type = 'success';
    }
}

include '../includes/header.php';
?>
<main class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark p-6 lg:p-8">
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">System Settings</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Manage your travel agency system settings and preferences</p>
            </div>
        </div>

        <!-- Message Alert -->
        <?php if ($message): ?>
            <div class="bg-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-50 dark:bg-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-900/10 border border-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-200 dark:border-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-900/20 text-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-700 dark:text-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-400 px-4 py-3 rounded-lg text-sm">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- General Settings -->
        <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">General Settings</h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="save_general" value="1">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Company Name</label>
                    <input type="text" name="company_name" value="SkyTravel" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Email Address</label>
                    <input type="email" name="email" value="admin@skytravel.com" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Phone Number</label>
                    <input type="tel" name="phone" value="+1 (555) 123-4567" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors">Save Changes</button>
            </form>
        </div>

        <!-- Payment Settings -->
        <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Payment Settings</h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="save_payment" value="1">
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="credit_card" id="credit_card" checked class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary">
                    <label for="credit_card" class="text-sm font-medium text-slate-700 dark:text-slate-300">Enable Credit Card Payments</label>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="mobile_money" id="mobile_money" checked class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary">
                    <label for="mobile_money" class="text-sm font-medium text-slate-700 dark:text-slate-300">Enable Mobile Money (Zaad, E-dahab)</label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Currency</label>
                    <select name="currency" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="GBP">GBP (£)</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors">Save Changes</button>
            </form>
        </div>

        <!-- Notification Settings -->
        <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Notification Settings</h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="save_notifications" value="1">
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="email_notifications" id="email_notifications" checked class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary">
                    <label for="email_notifications" class="text-sm font-medium text-slate-700 dark:text-slate-300">Email Notifications</label>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="sms_notifications" id="sms_notifications" checked class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary">
                    <label for="sms_notifications" class="text-sm font-medium text-slate-700 dark:text-slate-300">SMS Notifications</label>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="push_notifications" id="push_notifications" class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary">
                    <label for="push_notifications" class="text-sm font-medium text-slate-700 dark:text-slate-300">Push Notifications</label>
                </div>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors">Save Changes</button>
            </form>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
