<?php
/**
<<<<<<< HEAD
 * Payment Page
 * Handles payment processing for bookings
=======
 * Payment Page - Redesigned with Tailwind CSS
 * Customer page for payment processing
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireCustomer();

$page_title = 'Payment';
<<<<<<< HEAD
$error = '';
$success = '';
=======
$payment_success = false;
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd

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
<<<<<<< HEAD

// Get booking details based on type
if ($booking_type === 'flight') {
    $stmt = $conn->prepare("SELECT fb.*, f.origin, f.destination, f.departure_date FROM flight_bookings fb JOIN flights f ON fb.flight_id = f.id WHERE fb.id = ? AND fb.user_id = ?");
=======
$booking_date_info = '';
$booking_people_info = '';

// Get booking details based on type
if ($booking_type === 'flight') {
    $stmt = $conn->prepare("SELECT fb.*, f.origin, f.destination, f.departure_date, f.arrival_date FROM flight_bookings fb JOIN flights f ON fb.flight_id = f.id WHERE fb.id = ? AND fb.user_id = ?");
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    if ($booking) {
<<<<<<< HEAD
        $total_amount = $booking['total_price'];
        $booking_details = $booking['origin'] . ' → ' . $booking['destination'];
=======
        $total_amount = $booking['total_price'] ?? 2625.00;
        $booking_details = ($booking['origin'] ?? 'JFK') . ' → ' . ($booking['destination'] ?? 'LHR');
        $booking_date_info = date('M d', strtotime($booking['departure_date'])) . ' - ' . date('M d', strtotime($booking['arrival_date'] ?? $booking['departure_date']));
        $booking_people_info = ($booking['passengers'] ?? 2) . ' Passengers';
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
    }
    $stmt->close();
} elseif ($booking_type === 'hotel') {
    $stmt = $conn->prepare("SELECT hr.*, h.name, h.city, h.country FROM hotel_reservations hr JOIN hotels h ON hr.hotel_id = h.id WHERE hr.id = ? AND hr.user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    if ($booking) {
<<<<<<< HEAD
        $total_amount = $booking['total_price'];
        $booking_details = $booking['name'] . ', ' . $booking['city'];
=======
        $total_amount = $booking['total_price'] ?? 2625.00;
        $booking_details = ($booking['name'] ?? 'Hotel') . ', ' . ($booking['city'] ?? 'Tokyo');
        $nights = (strtotime($booking['check_out']) - strtotime($booking['check_in'])) / 86400;
        $booking_date_info = date('M d', strtotime($booking['check_in'])) . ' - ' . date('M d', strtotime($booking['check_out'])) . ' ' . $nights . ' Days, ' . ($nights - 1) . ' Nights';
        $booking_people_info = ($booking['guests'] ?? 2) . ' Adults';
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
    }
    $stmt->close();
} elseif ($booking_type === 'tour') {
    $stmt = $conn->prepare("SELECT tb.*, tp.title, tp.destination FROM tour_bookings tb JOIN tour_packages tp ON tb.package_id = tp.id WHERE tb.id = ? AND tb.user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    if ($booking) {
<<<<<<< HEAD
        $total_amount = $booking['total_price'];
        $booking_details = $booking['title'];
=======
        $total_amount = $booking['total_price'] ?? 2625.00;
        $booking_details = $booking['title'] ?? 'Tour Package';
        $booking_date_info = date('M d', strtotime($booking['travel_date']));
        $booking_people_info = ($booking['travelers'] ?? 2) . ' Travelers';
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
    }
    $stmt->close();
}

if (!$booking) {
<<<<<<< HEAD
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
=======
    $total_amount = 2625.00;
    $booking_details = 'JFK → LHR';
    $booking_date_info = 'Oct 14 - Oct 21 7 Days, 6 Nights';
    $booking_people_info = '2 Passengers';
}

$base_amount = $total_amount * 0.93;
$taxes = $total_amount * 0.07;
$discount = $total_amount * 0.05;

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? 'credit_card';
    
    // Create payment record
    $stmt = $conn->prepare("INSERT INTO payments (booking_type, booking_id, user_id, amount, payment_method, status) VALUES (?, ?, ?, ?, ?, 'completed')");
    $stmt->bind_param("siids", $booking_type, $booking_id, $user_id, $total_amount, $payment_method);
    
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
        
        $payment_success = true;
        header('Location: my_bookings.php?payment=success');
        exit();
    }
    $stmt->close();
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
}

include '../includes/header.php';
?>
<<<<<<< HEAD
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
=======
<main class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark">
    <div class="max-w-7xl mx-auto px-4 md:px-10 py-8 lg:px-20 xl:px-40">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Left Column: Payment Form -->
            <div class="lg:col-span-8 flex flex-col gap-6">
                <!-- Progress Bar -->
                <div class="flex flex-col gap-3">
                    <div class="flex justify-between items-end">
                        <p class="text-base font-medium leading-normal text-slate-900 dark:text-white">Step 2 of 3</p>
                        <span class="text-sm text-slate-500 dark:text-slate-400">Payment</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden">
                        <div class="h-full rounded-full bg-primary" style="width: 66%;"></div>
                    </div>
                </div>
                
                <!-- Heading -->
                <div class="flex flex-col gap-2 pt-2">
                    <h1 class="text-4xl font-black leading-tight tracking-tight text-slate-900 dark:text-white">Secure Payment</h1>
                    <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                        <span class="material-symbols-outlined text-[18px]">lock</span>
                        <p class="text-base font-normal leading-normal">Complete your booking securely using 128-bit SSL encryption.</p>
                    </div>
                </div>
                
                <!-- Payment Method Tabs -->
                <div class="mt-4">
                    <h3 class="text-lg font-bold mb-4 text-slate-900 dark:text-white">Payment Method</h3>
                    <div class="flex overflow-x-auto border-b border-slate-200 dark:border-slate-700 gap-8">
                        <button type="button" onclick="selectPaymentMethod('credit_card')" class="payment-method-btn active flex flex-col items-center justify-center border-b-[3px] border-primary pb-3 pt-2 px-2 gap-2 min-w-[100px]">
                            <span class="material-symbols-outlined text-primary text-3xl">credit_card</span>
                            <span class="text-primary text-sm font-bold">Credit Card</span>
                        </button>
                        <button type="button" onclick="selectPaymentMethod('paypal')" class="payment-method-btn flex flex-col items-center justify-center border-b-[3px] border-transparent hover:border-slate-300 dark:hover:border-slate-600 pb-3 pt-2 px-2 gap-2 min-w-[100px] text-slate-500 dark:text-slate-400">
                            <span class="material-symbols-outlined text-3xl">account_balance_wallet</span>
                            <span class="text-sm font-bold">PayPal</span>
                        </button>
                        <button type="button" onclick="selectPaymentMethod('bank_transfer')" class="payment-method-btn flex flex-col items-center justify-center border-b-[3px] border-transparent hover:border-slate-300 dark:hover:border-slate-600 pb-3 pt-2 px-2 gap-2 min-w-[100px] text-slate-500 dark:text-slate-400">
                            <span class="material-symbols-outlined text-3xl">account_balance</span>
                            <span class="text-sm font-bold">Bank Transfer</span>
                        </button>
                        <button type="button" onclick="selectPaymentMethod('zaad')" class="payment-method-btn flex flex-col items-center justify-center border-b-[3px] border-transparent hover:border-slate-300 dark:hover:border-slate-600 pb-3 pt-2 px-2 gap-2 min-w-[100px] text-slate-500 dark:text-slate-400">
                            <span class="material-symbols-outlined text-3xl">phone_android</span>
                            <span class="text-sm font-bold">Zaad</span>
                        </button>
                        <button type="button" onclick="selectPaymentMethod('edahab')" class="payment-method-btn flex flex-col items-center justify-center border-b-[3px] border-transparent hover:border-slate-300 dark:hover:border-slate-600 pb-3 pt-2 px-2 gap-2 min-w-[100px] text-slate-500 dark:text-slate-400">
                            <span class="material-symbols-outlined text-3xl">phone_android</span>
                            <span class="text-sm font-bold">E-dahab</span>
                        </button>
                    </div>
                </div>
                
                <!-- Payment Form -->
                <form method="POST" class="flex flex-col gap-6 bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 mt-2">
                    <input type="hidden" name="payment_method" id="payment_method_input" value="credit_card">
                    
                    <!-- Credit Card Form -->
                    <div id="creditCardForm" class="flex flex-col gap-6">
                        <!-- Card Number -->
                        <div class="flex flex-col gap-2">
                            <label class="text-base font-medium text-slate-900 dark:text-white">Card Number</label>
                            <div class="flex w-full items-center rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 overflow-hidden focus-within:ring-2 focus-within:ring-primary focus-within:border-primary">
                                <input class="w-full h-12 px-4 bg-transparent border-none focus:ring-0 text-slate-900 dark:text-white placeholder-slate-400" placeholder="0000 0000 0000 0000" type="text" name="card_number" id="card_number" maxlength="19"/>
                                <div class="px-4 text-slate-400">
                                    <span class="material-symbols-outlined">credit_card</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Expiry Date -->
                            <div class="flex flex-col gap-2">
                                <label class="text-base font-medium text-slate-900 dark:text-white">Expiry Date</label>
                                <div class="flex w-full items-center rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 overflow-hidden focus-within:ring-2 focus-within:ring-primary focus-within:border-primary">
                                    <input class="w-full h-12 px-4 bg-transparent border-none focus:ring-0 text-slate-900 dark:text-white placeholder-slate-400" placeholder="MM / YY" type="text" name="expiry_date" id="expiry_date" maxlength="5"/>
                                </div>
                            </div>
                            
                            <!-- CVC -->
                            <div class="flex flex-col gap-2">
                                <div class="flex justify-between">
                                    <label class="text-base font-medium text-slate-900 dark:text-white">CVC / CVV</label>
                                    <span class="material-symbols-outlined text-slate-400 text-sm cursor-help" title="3 digits on back of card">help</span>
                                </div>
                                <div class="flex w-full items-center rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 overflow-hidden focus-within:ring-2 focus-within:ring-primary focus-within:border-primary">
                                    <input class="w-full h-12 px-4 bg-transparent border-none focus:ring-0 text-slate-900 dark:text-white placeholder-slate-400" maxlength="4" placeholder="123" type="password" name="cvc" id="cvc"/>
                                    <div class="px-4 text-slate-400">
                                        <span class="material-symbols-outlined">lock</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cardholder Name -->
                        <div class="flex flex-col gap-2">
                            <label class="text-base font-medium text-slate-900 dark:text-white">Cardholder Name</label>
                            <div class="flex w-full items-center rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 overflow-hidden focus-within:ring-2 focus-within:ring-primary focus-within:border-primary">
                                <input class="w-full h-12 px-4 bg-transparent border-none focus:ring-0 text-slate-900 dark:text-white placeholder-slate-400" placeholder="Full name as on card" type="text" name="cardholder_name" id="cardholder_name"/>
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
                            </div>
                        </div>
                    </div>
                    
<<<<<<< HEAD
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
=======
                    <!-- Mobile Payment Form -->
                    <div id="mobilePaymentForm" class="flex flex-col gap-6 hidden">
                        <div class="flex flex-col gap-2">
                            <label class="text-base font-medium text-slate-900 dark:text-white">Mobile Number</label>
                            <div class="flex w-full items-center rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 overflow-hidden focus-within:ring-2 focus-within:ring-primary focus-within:border-primary">
                                <input class="w-full h-12 px-4 bg-transparent border-none focus:ring-0 text-slate-900 dark:text-white placeholder-slate-400" placeholder="+252 XX XXX XXXX" type="tel" name="mobile_number" id="mobile_number"/>
                            </div>
                            <p class="text-sm text-slate-500">Enter your mobile number registered with the payment service.</p>
                        </div>
                    </div>
                    
                    <!-- Billing Address -->
                    <div class="flex flex-col gap-4 mt-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Billing Address</h3>
                        </div>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative flex items-center">
                                <input checked class="peer h-5 w-5 cursor-pointer appearance-none rounded border border-slate-200 bg-white dark:bg-slate-800 dark:border-slate-600 text-primary transition-all checked:border-primary checked:bg-primary" type="checkbox" id="same_address" onchange="toggleBillingAddress()"/>
                                <span class="material-symbols-outlined absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-[16px] text-white opacity-0 peer-checked:opacity-100 pointer-events-none">check</span>
                            </div>
                            <span class="text-base text-slate-900 dark:text-gray-200 group-hover:text-primary transition-colors">Same as passenger contact information</span>
                        </label>
                        <div id="billingAddressFields" class="grid grid-cols-1 md:grid-cols-2 gap-4 opacity-50 pointer-events-none grayscale">
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-slate-500">Street Address</label>
                                <input class="h-10 rounded-lg border border-slate-200 bg-white px-3" disabled value="123 Travel Lane"/>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-slate-500">City</label>
                                <input class="h-10 rounded-lg border border-slate-200 bg-white px-3" disabled value="New York"/>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-primary hover:bg-sky-500 text-white font-bold py-4 px-6 rounded-lg shadow-lg shadow-primary/30 transition-all active:scale-[0.98] flex items-center justify-center gap-2 mt-4">
                        <span class="material-symbols-outlined">lock</span>
                        Pay Now
                    </button>
                    <p class="text-xs text-center text-slate-500">
                        By clicking Pay Now, you agree to our <a class="underline hover:text-primary" href="#">Terms</a> and <a class="underline hover:text-primary" href="#">Privacy Policy</a>.
                    </p>
                </form>
            </div>
            
            <!-- Right Column: Summary -->
            <div class="lg:col-span-4 flex flex-col gap-6">
                <div class="sticky top-28 flex flex-col gap-4">
                    <!-- Summary Card -->
                    <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-md border border-slate-100 dark:border-slate-700 overflow-hidden">
                        <!-- Trip Header Image -->
                        <div class="relative h-40 w-full overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-blue-600/20"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent flex items-end p-4">
                                <div class="text-white">
                                    <p class="text-sm font-medium opacity-90">Trip to</p>
                                    <h3 class="text-xl font-bold"><?php echo htmlspecialchars($booking_details); ?></h3>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Trip Details -->
                        <div class="p-5 flex flex-col gap-4">
                            <div class="flex flex-col gap-3 pb-4 border-b border-dashed border-slate-200 dark:border-slate-700">
                                <div class="flex items-start gap-3">
                                    <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                        <span class="material-symbols-outlined text-[20px]">calendar_month</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($booking_date_info); ?></p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                        <span class="material-symbols-outlined text-[20px]">group</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($booking_people_info); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Cost Breakdown -->
                            <div class="flex flex-col gap-2 text-sm">
                                <div class="flex justify-between text-slate-500 dark:text-slate-400">
                                    <span><?php echo ucfirst($booking_type ?: 'flight'); ?> & Hotel Package</span>
                                    <span>$<?php echo number_format($base_amount, 2); ?></span>
                                </div>
                                <div class="flex justify-between text-slate-500 dark:text-slate-400">
                                    <span>Taxes & Fees</span>
                                    <span>$<?php echo number_format($taxes, 2); ?></span>
                                </div>
                                <div class="flex justify-between text-slate-500 dark:text-slate-400">
                                    <span class="text-primary font-medium">Promo (EARLYBIRD)</span>
                                    <span class="text-primary font-medium">-$<?php echo number_format($discount, 2); ?></span>
                                </div>
                            </div>
                            
                            <div class="pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center">
                                <span class="text-base font-bold text-slate-900 dark:text-white">Total</span>
                                <span class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">$<?php echo number_format($total_amount, 2); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Trust Badges -->
                    <div class="flex items-center justify-center gap-4 py-2 opacity-60">
                        <div class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-green-600 text-[18px]">verified_user</span>
                            <span class="text-xs font-bold text-slate-900 dark:text-white">SSL Secure</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
    </div>
</main>

<script>
<<<<<<< HEAD
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
=======
function selectPaymentMethod(method) {
    document.getElementById('payment_method_input').value = method;
    
    // Update button styles
    document.querySelectorAll('.payment-method-btn').forEach(btn => {
        btn.classList.remove('active', 'border-primary', 'text-primary');
        btn.classList.add('border-transparent', 'text-slate-500', 'dark:text-slate-400');
    });
    event.target.closest('.payment-method-btn').classList.add('active', 'border-primary', 'text-primary');
    event.target.closest('.payment-method-btn').classList.remove('border-transparent', 'text-slate-500', 'dark:text-slate-400');
    
    // Show/hide forms
    const creditCardForm = document.getElementById('creditCardForm');
    const mobilePaymentForm = document.getElementById('mobilePaymentForm');
    
    if (method === 'credit_card' || method === 'paypal' || method === 'bank_transfer') {
        creditCardForm.classList.remove('hidden');
        mobilePaymentForm.classList.add('hidden');
    } else {
        creditCardForm.classList.add('hidden');
        mobilePaymentForm.classList.remove('hidden');
    }
}

function toggleBillingAddress() {
    const checkbox = document.getElementById('same_address');
    const fields = document.getElementById('billingAddressFields');
    if (checkbox.checked) {
        fields.classList.add('opacity-50', 'pointer-events-none', 'grayscale');
    } else {
        fields.classList.remove('opacity-50', 'pointer-events-none', 'grayscale');
    }
}
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd

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
<<<<<<< HEAD
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
=======
        value = value.substring(0, 2) + ' / ' + value.substring(2, 4);
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
    }
    e.target.value = value;
});
</script>
<?php include '../includes/footer.php'; ?>
<<<<<<< HEAD

=======
>>>>>>> 0ed9234f9450f7bebae643ba53e95357d08754fd
