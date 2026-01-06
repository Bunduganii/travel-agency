<?php
/**
 * Payment Page
 * Handles payment processing for bookings
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireCustomer();

$page_title = 'Payment';
$error = '';
$success = '';

// Get booking type and ID
$booking_type = $_GET['type'] ?? '';
$booking_id = intval($_GET['id'] ?? 0);

if (!$booking_type || !$booking_id) {
    header('Location: my_bookings.php');
    exit();
}

$user_id = getUserId();
$booking = null;
$total_amount = 0;
$booking_details = '';

// Get booking details based on type
if ($booking_type === 'flight') {
    $stmt = $conn->prepare("SELECT fb.*, f.origin, f.destination, f.departure_date FROM flight_bookings fb JOIN flights f ON fb.flight_id = f.id WHERE fb.id = ? AND fb.user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    if ($booking) {
        $total_amount = $booking['total_price'];
        $booking_details = $booking['origin'] . ' → ' . $booking['destination'];
    }
    $stmt->close();
} elseif ($booking_type === 'hotel') {
    $stmt = $conn->prepare("SELECT hr.*, h.name, h.city, h.country FROM hotel_reservations hr JOIN hotels h ON hr.hotel_id = h.id WHERE hr.id = ? AND hr.user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    if ($booking) {
        $total_amount = $booking['total_price'];
        $booking_details = $booking['name'] . ', ' . $booking['city'];
    }
    $stmt->close();
} elseif ($booking_type === 'tour') {
    $stmt = $conn->prepare("SELECT tb.*, tp.title, tp.destination FROM tour_bookings tb JOIN tour_packages tp ON tb.package_id = tp.id WHERE tb.id = ? AND tb.user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    if ($booking) {
        $total_amount = $booking['total_price'];
        $booking_details = $booking['title'];
    }
    $stmt->close();
}

if (!$booking) {
    header('Location: my_bookings.php');
    exit();
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';
    $card_number = $_POST['card_number'] ?? '';
    $expiry_date = $_POST['expiry_date'] ?? '';
    $cvc = $_POST['cvc'] ?? '';
    $cardholder_name = $_POST['cardholder_name'] ?? '';
    
    if (empty($payment_method)) {
        $error = 'Please select a payment method.';
    } else {
        // Generate transaction ID
        $transaction_id = 'TXN' . time() . rand(1000, 9999);
        
        // Insert payment record
        $stmt = $conn->prepare("INSERT INTO payments (booking_type, booking_id, user_id, amount, payment_method, transaction_id, status) VALUES (?, ?, ?, ?, ?, ?, 'completed')");
        $stmt->bind_param("siidss", $booking_type, $booking_id, $user_id, $total_amount, $payment_method, $transaction_id);
        
        if ($stmt->execute()) {
            // Update booking status
            if ($booking_type === 'flight') {
                $update_stmt = $conn->prepare("UPDATE flight_bookings SET status = 'confirmed', payment_status = 'paid' WHERE id = ?");
            } elseif ($booking_type === 'hotel') {
                $update_stmt = $conn->prepare("UPDATE hotel_reservations SET status = 'confirmed', payment_status = 'paid' WHERE id = ?");
            } else {
                $update_stmt = $conn->prepare("UPDATE tour_bookings SET status = 'confirmed', payment_status = 'paid' WHERE id = ?");
            }
            $update_stmt->bind_param("i", $booking_id);
            $update_stmt->execute();
            $update_stmt->close();
            
            $success = 'Payment successful! Your booking has been confirmed.';
            header('refresh:2;url=my_bookings.php');
        } else {
            $error = 'Payment processing failed. Please try again.';
        }
        $stmt->close();
    }
}

include '../includes/header.php';
?>
<main class="payment-page">
    <div class="payment-container">
        <div class="payment-form-section">
            <div class="progress-indicator">
                <span>Step 2 of 3</span>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 66%;"></div>
                </div>
                <span>Payment</span>
            </div>
            
            <div class="payment-header">
                <h1>Secure Payment</h1>
                <p><i class="fas fa-lock"></i> Complete your booking securely using 128-bit SSL encryption.</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error slide-in"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success slide-in"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" class="payment-form" id="paymentForm">
                <div class="payment-methods">
                    <h2>Payment Method</h2>
                    <div class="method-options">
                        <input type="radio" name="payment_method" value="credit_card" id="credit_card" checked>
                        <label for="credit_card" class="method-card">
                            <i class="fas fa-credit-card"></i>
                            <span>Credit Card</span>
                        </label>
                        
                        <input type="radio" name="payment_method" value="zaad" id="zaad">
                        <label for="zaad" class="method-card">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Zaad</span>
                        </label>
                        
                        <input type="radio" name="payment_method" value="edahab" id="edahab">
                        <label for="edahab" class="method-card">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Edahab</span>
                        </label>
                        
                        <input type="radio" name="payment_method" value="waafi" id="waafi">
                        <label for="waafi" class="method-card">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Waafi</span>
                        </label>
                        
                        <input type="radio" name="payment_method" value="dahab_plus" id="dahab_plus">
                        <label for="dahab_plus" class="method-card">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Dahab Plus</span>
                        </label>
                    </div>
                </div>
                
                <div id="creditCardForm" class="payment-details-form">
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <div class="input-wrapper">
                            <input type="text" id="card_number" name="card_number" placeholder="0000 0000 0000 0000" maxlength="19">
                            <i class="fas fa-credit-card"></i>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date</label>
                            <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY" maxlength="5">
                        </div>
                        
                        <div class="form-group">
                            <label for="cvc">CVC/CVV</label>
                            <div class="input-wrapper">
                                <input type="text" id="cvc" name="cvc" placeholder="123" maxlength="4">
                                <i class="fas fa-lock"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="cardholder_name">Cardholder Name</label>
                        <input type="text" id="cardholder_name" name="cardholder_name" placeholder="Full name as on card">
                    </div>
                </div>
                
                <div id="mobilePaymentForm" class="payment-details-form" style="display: none;">
                    <div class="form-group">
                        <label for="mobile_number">Mobile Number</label>
                        <input type="tel" id="mobile_number" name="mobile_number" placeholder="+252 XX XXX XXXX">
                    </div>
                    <p class="help-text">Enter your mobile number registered with the payment service.</p>
                </div>
                
                <div class="billing-address">
                    <h2>Billing Address</h2>
                    <label class="checkbox-label">
                        <input type="checkbox" checked>
                        Same as passenger contact information
                    </label>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Street Address</label>
                            <input type="text" placeholder="123 Travel Lane">
                        </div>
                        
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" placeholder="New York">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-pay">
                    <i class="fas fa-lock"></i> Pay Now
                </button>
                
                <p class="terms-text">
                    By clicking Pay Now, you agree to our <a href="#">Terms</a> and <a href="#">Privacy Policy</a>.
                </p>
            </form>
        </div>
        
        <aside class="trip-summary">
            <div class="summary-image">
                <h2>Trip to</h2>
                <h3><?php echo htmlspecialchars($booking_details); ?></h3>
            </div>
            
            <div class="summary-details">
                <div class="detail-item">
                    <i class="fas fa-calendar"></i>
                    <div>
                        <?php if ($booking_type === 'hotel'): ?>
                            <span><?php echo date('M d', strtotime($booking['check_in'])); ?> - <?php echo date('M d', strtotime($booking['check_out'])); ?></span>
                            <small><?php echo (strtotime($booking['check_out']) - strtotime($booking['check_in'])) / 86400; ?> Days, <?php echo (strtotime($booking['check_out']) - strtotime($booking['check_in'])) / 86400 - 1; ?> Nights</small>
                        <?php elseif ($booking_type === 'tour'): ?>
                            <span><?php echo date('M d', strtotime($booking['travel_date'])); ?></span>
                            <small>Travel Date</small>
                        <?php else: ?>
                            <span><?php echo date('M d, Y', strtotime($booking['departure_date'])); ?></span>
                            <small>Departure Date</small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="detail-item">
                    <i class="fas fa-users"></i>
                    <div>
                        <?php if ($booking_type === 'hotel'): ?>
                            <span><?php echo $booking['guests']; ?> Guests</span>
                            <small><?php echo $booking['rooms']; ?> Room</small>
                        <?php elseif ($booking_type === 'tour'): ?>
                            <span><?php echo $booking['travelers']; ?> Travelers</span>
                        <?php else: ?>
                            <span><?php echo $booking['passengers']; ?> Passengers</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="cost-breakdown">
                <div class="cost-item">
                    <span><?php echo ucfirst($booking_type); ?> Booking</span>
                    <span>$<?php echo number_format($total_amount * 0.93, 2); ?></span>
                </div>
                <div class="cost-item">
                    <span>Taxes & Fees</span>
                    <span>$<?php echo number_format($total_amount * 0.07, 2); ?></span>
                </div>
                <div class="cost-item discount">
                    <span>Promo (EARLYBIRD)</span>
                    <span>-$<?php echo number_format($total_amount * 0.05, 2); ?></span>
                </div>
                <div class="cost-total">
                    <span>Total</span>
                    <strong>$<?php echo number_format($total_amount, 2); ?></strong>
                </div>
            </div>
            
            <button type="submit" form="paymentForm" class="btn btn-primary btn-pay-summary">
                <i class="fas fa-lock"></i> Pay Now
            </button>
            
            <div class="security-badge">
                <i class="fas fa-check-circle"></i>
                <span>SSL Secure</span>
                <div class="payment-icons">
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-amex"></i>
                </div>
            </div>
        </aside>
    </div>
</main>

<script>
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const creditCardForm = document.getElementById('creditCardForm');
        const mobilePaymentForm = document.getElementById('mobilePaymentForm');
        
        if (this.value === 'credit_card') {
            creditCardForm.style.display = 'block';
            mobilePaymentForm.style.display = 'none';
        } else {
            creditCardForm.style.display = 'none';
            mobilePaymentForm.style.display = 'block';
        }
    });
});

// Format card number
document.getElementById('card_number')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s/g, '');
    let formatted = value.match(/.{1,4}/g)?.join(' ') || value;
    e.target.value = formatted;
});

// Format expiry date
document.getElementById('expiry_date')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    e.target.value = value;
});
</script>
<?php include '../includes/footer.php'; ?>

