<?php
/**
 * Feedback Page
 * Customer page to submit feedback
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireCustomer();

$page_title = 'Feedback';
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
            $message = 'Thank you for your feedback!';
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

