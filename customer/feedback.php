<?php
/**
<<<<<<< HEAD
 * Feedback Page
=======
 * Feedback/Support Page - Redesigned with Tailwind CSS
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
 * Customer page to submit feedback
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireCustomer();

<<<<<<< HEAD
$page_title = 'Feedback';
=======
$page_title = 'Support & Feedback';
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
$message = '';
$message_type = '';

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $feedback_message = trim($_POST['message'] ?? '');
    $rating = intval($_POST['rating'] ?? 5);
    
    if (empty($feedback_message)) {
        $message = 'Please enter your feedback message.';
        $message_type = 'error';
    } else {
        $user_id = getUserId();
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, subject, message, rating) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $user_id, $subject, $feedback_message, $rating);
        
        if ($stmt->execute()) {
<<<<<<< HEAD
            $message = 'Thank you for your feedback!';
=======
            $message = 'Thank you for your feedback! We appreciate your input.';
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
            $message_type = 'success';
            $_POST = []; // Clear form
        } else {
            $message = 'Error submitting feedback. Please try again.';
            $message_type = 'error';
        }
        $stmt->close();
    }
}

include '../includes/header.php';
?>
<<<<<<< HEAD
<main class="feedback-page">
    <h1>Send Feedback</h1>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> slide-in">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" class="feedback-form">
        <div class="form-group">
            <label for="subject">Subject</label>
            <input type="text" id="subject" name="subject" placeholder="Feedback subject" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="rating">Rating</label>
            <div class="rating-input">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" name="rating" value="<?php echo $i; ?>" id="rating<?php echo $i; ?>" <?php echo ($_POST['rating'] ?? 5) == $i ? 'checked' : ''; ?>>
                    <label for="rating<?php echo $i; ?>" class="star-label">
                        <i class="fas fa-star"></i>
                    </label>
                <?php endfor; ?>
            </div>
        </div>
        
        <div class="form-group">
            <label for="message">Your Feedback</label>
            <textarea id="message" name="message" rows="8" placeholder="Please share your thoughts..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-paper-plane"></i> Submit Feedback
        </button>
    </form>
</main>
<?php include '../includes/footer.php'; ?>

=======
<main class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark p-6 lg:p-8">
    <div class="max-w-4xl mx-auto space-y-8">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Support & Feedback</h2>
            <p class="text-slate-500 dark:text-slate-400 mt-1">We'd love to hear from you! Share your thoughts, suggestions, or report any issues.</p>
        </div>
        
        <?php if ($message): ?>
            <div class="bg-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-50 dark:bg-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-900/10 border border-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-200 dark:border-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-900/20 text-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-700 dark:text-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-400 px-4 py-3 rounded-lg text-sm">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Send Feedback</h3>
            </div>
            
            <form method="POST" class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Subject (Optional)</label>
                    <input type="text" name="subject" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="What is this about?">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Rating</label>
                    <div class="flex items-center gap-2">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                        <label class="cursor-pointer group">
                            <input type="radio" name="rating" value="<?php echo $i; ?>" id="rating<?php echo $i; ?>" <?php echo ($_POST['rating'] ?? 5) == $i ? 'checked' : ''; ?> class="sr-only">
                            <span class="material-symbols-outlined text-4xl <?php echo ($_POST['rating'] ?? 5) >= $i ? 'text-yellow-400 fill-current' : 'text-slate-300 dark:text-slate-600'; ?> group-hover:text-yellow-400 transition-colors">star</span>
                        </label>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Your Feedback <span class="text-red-500">*</span></label>
                    <textarea name="message" rows="8" class="w-full px-4 py-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent resize-none" placeholder="Please share your thoughts, suggestions, or report any issues you've encountered..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                </div>
                
                <div class="flex items-center gap-4 pt-4">
                    <button type="submit" class="px-6 py-2.5 bg-primary hover:bg-sky-500 text-white rounded-lg font-medium shadow-sm shadow-primary/30 transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined">send</span>
                        Submit Feedback
                    </button>
                    <button type="reset" class="px-6 py-2.5 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        Clear Form
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Help Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="bg-primary/10 p-3 rounded-lg w-fit mb-4">
                    <span class="material-symbols-outlined text-primary text-3xl">help</span>
                </div>
                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Need Help?</h4>
                <p class="text-sm text-slate-500 dark:text-slate-400">Check our FAQ section for common questions and answers.</p>
            </div>
            
            <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="bg-blue-100 dark:bg-blue-900/30 p-3 rounded-lg w-fit mb-4">
                    <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-3xl">support_agent</span>
                </div>
                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Contact Support</h4>
                <p class="text-sm text-slate-500 dark:text-slate-400">Reach out to our support team for immediate assistance.</p>
            </div>
            
            <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="bg-green-100 dark:bg-green-900/30 p-3 rounded-lg w-fit mb-4">
                    <span class="material-symbols-outlined text-green-600 dark:text-green-400 text-3xl">lightbulb</span>
                </div>
                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Suggestions</h4>
                <p class="text-sm text-slate-500 dark:text-slate-400">Have an idea? We're always looking to improve!</p>
            </div>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
