<?php
/**
 * Payment Page - Redesigned with Tailwind CSS
 * Customer page for payment processing
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireCustomer();

$page_title = 'Payment';
$payment_success = false;
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
$booking_date_info = '';
$booking_people_info = '';

// Get booking details based on type
if ($booking_type === 'flight') {
    $stmt = $conn->prepare("SELECT fb.*, f.origin, f.destination, f.departure_date, f.arrival_date FROM flight_bookings fb JOIN flights f ON fb.flight_id = f.id WHERE fb.id = ? AND fb.user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    if ($booking) {
        $total_amount = $booking['total_price'] ?? 2625.00;
        $booking_details = ($booking['origin'] ?? 'JFK') . ' → ' . ($booking['destination'] ?? 'LHR');
        $booking_date_info = date('M d', strtotime($booking['departure_date'])) . ' - ' . date('M d', strtotime($booking['arrival_date'] ?? $booking['departure_date']));
        $booking_people_info = ($booking['passengers'] ?? 2) . ' Passengers';
    }
    $stmt->close();
} elseif ($booking_type === 'hotel') {
    $stmt = $conn->prepare("SELECT hr.*, h.name, h.city, h.country FROM hotel_reservations hr JOIN hotels h ON hr.hotel_id = h.id WHERE hr.id = ? AND hr.user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    if ($booking) {
        $total_amount = $booking['total_price'] ?? 2625.00;
        $booking_details = ($booking['name'] ?? 'Hotel') . ', ' . ($booking['city'] ?? 'Tokyo');
        $nights = (strtotime($booking['check_out']) - strtotime($booking['check_in'])) / 86400;
        $booking_date_info = date('M d', strtotime($booking['check_in'])) . ' - ' . date('M d', strtotime($booking['check_out'])) . ' ' . $nights . ' Days, ' . ($nights - 1) . ' Nights';
        $booking_people_info = ($booking['guests'] ?? 2) . ' Adults';
    }
    $stmt->close();
} elseif ($booking_type === 'tour') {
    $stmt = $conn->prepare("SELECT tb.*, tp.title, tp.destination FROM tour_bookings tb JOIN tour_packages tp ON tb.package_id = tp.id WHERE tb.id = ? AND tb.user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    if ($booking) {
        $total_amount = $booking['total_price'] ?? 2625.00;
        $booking_details = $booking['title'] ?? 'Tour Package';
        $booking_date_info = date('M d', strtotime($booking['travel_date']));
        $booking_people_info = ($booking['travelers'] ?? 2) . ' Travelers';
    }
    $stmt->close();
}

if (!$booking) {
    header('Location: my_bookings.php');
    exit();
}

$base_amount = $total_amount * 0.93;
$taxes = $total_amount * 0.07;
$discount = $total_amount * 0.05;

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? 'credit_card';
    
    // Generate transaction ID
    $transaction_id = 'TXN' . time() . rand(1000, 9999);
    
    // Create payment record
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
        
        $payment_success = true;
        header('Location: my_bookings.php?payment=success&type=' . $booking_type);
        exit();
    } else {
        $error = 'Payment processing failed. Please try again.';
    }
    $stmt->close();
}

include '../includes/header.php';
?>
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
                                    <input class="w-full h-12 px-4 bg-transparent border-none focus:ring-0 text-slate-900 dark:text-white placeholder-slate-400" placeholder="MM / YY" type="text" name="expiry_date" id="expiry_date" maxlength="7"/>
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
                            </div>
                        </div>
                    </div>
                    
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
    </div>
</main>

<script>
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

// Format card number
document.getElementById('card_number')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s/g, '');
    let formatted = value.match(/.{1,4}/g)?.join(' ') || value;
    e.target.value = formatted;
});

// Format expiry date (MM/YY) - simple forward typing
document.getElementById('expiry_date')?.addEventListener('input', function(e) {
    const input = e.target;
    // Keep only digits
    let digits = input.value.replace(/\D/g, '');

    // Limit to 4 digits total (MMYY)
    if (digits.length > 4) {
        digits = digits.substring(0, 4);
    }

    // Build formatted value
    let formatted = '';
    if (digits.length === 0) {
        formatted = '';
    } else if (digits.length <= 2) {
        // Month only
        let month = digits;
        if (month.length === 2) {
            const m = parseInt(month, 10);
            if (m > 12) month = '12';
            if (m < 1) month = '01';
        }
        formatted = month;
    } else {
        // Month + year
        let month = digits.substring(0, 2);
        const m = parseInt(month, 10);
        if (m > 12) month = '12';
        if (m < 1) month = '01';
        const year = digits.substring(2);
        formatted = month + ' / ' + year;
    }

    input.value = formatted;
    // Always keep cursor at end so typing is forward only
    input.setSelectionRange(formatted.length, formatted.length);
});

// Validate on blur - ensure 2-digit year
document.getElementById('expiry_date')?.addEventListener('blur', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length === 1) {
        // Single digit - clear it
        e.target.value = '';
    } else if (value.length === 2) {
        // Only month - keep it as is
        let month = value;
        if (parseInt(month) > 12) {
            month = '12';
        }
        if (parseInt(month) < 1) {
            month = '01';
        }
        e.target.value = month + ' / ';
    } else if (value.length === 3) {
        // Incomplete year - remove last digit
        let month = value.substring(0, 2);
        if (parseInt(month) > 12) {
            month = '12';
        }
        if (parseInt(month) < 1) {
            month = '01';
        }
        e.target.value = month + ' / ';
    } else if (value.length === 4) {
        // Complete - format properly
        let month = value.substring(0, 2);
        let year = value.substring(2, 4);
        if (parseInt(month) > 12) {
            month = '12';
        }
        if (parseInt(month) < 1) {
            month = '01';
        }
        e.target.value = month + ' / ' + year;
    }
});

// Allow backspace and navigation keys to work properly
document.getElementById('expiry_date')?.addEventListener('keydown', function(e) {
    // Allow backspace, delete, tab, escape, enter
    if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
        // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
        (e.keyCode === 65 && e.ctrlKey === true) ||
        (e.keyCode === 67 && e.ctrlKey === true) ||
        (e.keyCode === 86 && e.ctrlKey === true) ||
        (e.keyCode === 88 && e.ctrlKey === true) ||
        // Allow home, end, left, right, up, down
        (e.keyCode >= 35 && e.keyCode <= 40)) {
        return;
    }
    // Allow numbers (both main keyboard and numpad)
    if ((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 96 && e.keyCode <= 105)) {
        return; // Allow number input
    }
    // Block all other keys
    e.preventDefault();
});
</script>
<?php include '../includes/footer.php'; ?>
