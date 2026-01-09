<?php
/**
 * Customer Login Page
 * Handles customer authentication only
 */
require_once 'includes/db.php';
require_once 'includes/auth.php';

$error = '';
$success = '';

// If already logged in, redirect to appropriate dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/admin_dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user_type = $_POST['user_type'] ?? 'customer';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Query user from database
        $stmt = $conn->prepare("SELECT id, username, email, password, full_name, user_type FROM users WHERE email = ? AND user_type = ?");
        $stmt->bind_param("ss", $email, $user_type);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password (using password_verify for hashed passwords)
            // Also check plain text password as fallback for legacy accounts
            $password_valid = false;
            
            // Check if password is hashed (starts with $2y$ or $2a$ or $2b$)
            if (preg_match('/^\$2[ayb]\$/', $user['password'])) {
                // Password is hashed, use password_verify
                $password_valid = password_verify($password, $user['password']);
            } else {
                // Password is plain text (legacy), compare directly
                $password_valid = ($password === $user['password']);
            }
            
            if ($password_valid) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_type'] = $user['user_type'];
                
                // Redirect based on user type
                if ($user['user_type'] === 'admin') {
                    header('Location: admin/admin_dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password. Please check your email and user type selection.';
        }
        $stmt->close();
    // ========== COMPREHENSIVE PHP DEBUGGING ==========
    error_log("========================================");
    error_log("LOGIN FORM SUBMIT - PHP HANDLER STARTED");
    error_log("========================================");
    error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
    error_log("POST data received: " . print_r($_POST, true));
    error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user_type = 'customer'; // Customer login only
    
    error_log("--- EXTRACTED VALUES ---");
    error_log("Email (trimmed): '$email' (length: " . strlen($email) . ")");
    error_log("Password: " . (empty($password) ? 'EMPTY' : 'SET (' . strlen($password) . ' chars)'));
    error_log("User Type: '$user_type'");
    error_log("Email empty? " . (empty($email) ? 'YES' : 'NO'));
    error_log("Password empty? " . (empty($password) ? 'YES' : 'NO'));
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
        error_log("ERROR: Empty fields detected");
        error_log("Email empty: " . (empty($email) ? 'YES' : 'NO'));
        error_log("Password empty: " . (empty($password) ? 'YES' : 'NO'));
    } else {
        error_log("--- VALIDATION PASSED - PROCEEDING TO DATABASE ---");
        // Query user from database - try with user_type first
        error_log("--- DATABASE QUERY 1: With user_type filter ---");
        error_log("SQL: SELECT ... WHERE email = ? AND user_type = ?");
        error_log("Parameters: email='$email', user_type='$user_type'");
        
        $stmt = $conn->prepare("SELECT id, username, email, password, full_name, user_type FROM users WHERE email = ? AND user_type = ?");
        if (!$stmt) {
            error_log("ERROR: Failed to prepare statement: " . $conn->error);
            $error = 'Database error. Please try again.';
        } else {
            $stmt->bind_param("ss", $email, $user_type);
            $stmt->execute();
            $result = $stmt->get_result();
            
            error_log("Query 1 Result: " . $result->num_rows . " row(s) found");
            
            // If not found with user_type, try without user_type filter (for flexibility)
            if ($result->num_rows === 0 && $user_type === 'admin') {
                error_log("--- DATABASE QUERY 2: Without user_type filter (admin fallback) ---");
                $stmt->close();
                $stmt = $conn->prepare("SELECT id, username, email, password, full_name, user_type FROM users WHERE email = ?");
                if (!$stmt) {
                    error_log("ERROR: Failed to prepare statement 2: " . $conn->error);
                    $error = 'Database error. Please try again.';
                } else {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    error_log("Query 2 Result: " . $result->num_rows . " row(s) found");
                }
            }
            
            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                error_log("--- USER FOUND IN DATABASE ---");
                error_log("User ID: " . $user['id']);
                error_log("Username: " . $user['username']);
                error_log("Email: " . $user['email']);
                error_log("Full Name: " . $user['full_name']);
                error_log("User Type in DB: '" . $user['user_type'] . "'");
                error_log("User Type Requested: '$user_type'");
                error_log("Password Hash (first 30 chars): " . substr($user['password'], 0, 30) . "...");
                
                // Verify password (using password_verify for hashed passwords)
                error_log("--- PASSWORD VERIFICATION ---");
                error_log("Input password length: " . strlen($password));
                error_log("Stored hash length: " . strlen($user['password']));
                
                $password_valid = password_verify($password, $user['password']);
                error_log("password_verify() result: " . ($password_valid ? "TRUE (PASS)" : "FALSE (FAIL)"));
                
                if ($user && $password_valid) {
                    // Double-check user_type matches if admin login was attempted
                    if ($user_type === 'admin' && $user['user_type'] !== 'admin') {
                        $error = 'Access denied. Admin credentials required. User type in database: ' . $user['user_type'];
                        error_log("ERROR: User type mismatch - Requested: '$user_type', Found: '" . $user['user_type'] . "'");
                    } else {
                        // Set session variables
                        error_log("--- SETTING SESSION VARIABLES ---");
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['user_type'] = $user['user_type'];
                        
                        error_log("Session set - user_id: " . $_SESSION['user_id']);
                        error_log("Session set - user_type: " . $_SESSION['user_type']);
                        
                        $redirect_url = ($user['user_type'] === 'admin') ? 'admin/admin_dashboard.php' : 'index.php';
                        error_log("--- REDIRECTING ---");
                        error_log("Redirect URL: $redirect_url");
                        error_log("User type: " . $user['user_type']);
                        
                        // Redirect based on user type
                        if ($user['user_type'] === 'admin') {
                            header('Location: admin/admin_dashboard.php');
                        } else {
                            header('Location: index.php');
                        }
                        error_log("========================================");
                        error_log("LOGIN SUCCESS - REDIRECT SENT");
                        error_log("========================================");
                        exit();
                    }
                } else {
                    $error = 'Invalid email or password.';
                    error_log("ERROR: Password verification FAILED");
                    error_log("User exists but password doesn't match");
                }
            } else {
                $error = 'Invalid email or password. No user found with email: ' . htmlspecialchars($email) . ' and user_type: ' . htmlspecialchars($user_type);
                error_log("ERROR: No user found in database");
                error_log("Searched for: email='$email', user_type='$user_type'");
                error_log("Rows found: " . ($result ? $result->num_rows : 'NULL'));
            }
            
            if ($stmt) {
                $stmt->close();
            }
        }
    }
    
    error_log("========================================");
    error_log("LOGIN FAILED - ERROR: " . ($error ?? 'Unknown error'));
    error_log("========================================");
    
    // Output debug info to page if error occurred
    if (!empty($error)) {
        $debug_output = "PHP DEBUG: " . $error;
        if (isset($_POST['email'])) {
            $debug_output .= " | Email received: " . htmlspecialchars($_POST['email']);
        }
        if (isset($_POST['user_type'])) {
            $debug_output .= " | User Type received: " . htmlspecialchars($_POST['user_type']);
        } else {
            $debug_output .= " | User Type: NOT RECEIVED IN POST";
        }
        error_log($debug_output);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Travel Agency</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-left">
            <div class="login-content fade-in">
                <div class="logo">
                    <i class="fas fa-gem"></i>
                    <span>Travel Agency</span>
                </div>
                <h1>Welcome Back</h1>
                <p>Please enter your details to access your booking dashboard.</p>
                
                <form method="POST" action="" class="login-form">
                    <div class="user-type-toggle">
                        <input type="radio" name="user_type" value="customer" id="customer" checked>
                        <label for="customer" class="toggle-btn">Customer</label>
                        
                        <input type="radio" name="user_type" value="admin" id="admin">
                        <label for="admin" class="toggle-btn">Staff/Agent</label>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error slide-in"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="name@example.com" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Enter your password" required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-login">
                        Log In <i class="fas fa-arrow-right"></i>
                    </button>
                    
                    <div class="divider">
                        <span>Or continue with</span>
                    </div>
                    
                    <div class="social-login">
                        <button type="button" class="social-btn">
                            <i class="fab fa-google"></i> Google
                        </button>
                        <button type="button" class="social-btn">
                            <i class="fab fa-microsoft"></i> Microsoft
                        </button>
                    </div>
                    
                    <p class="signup-link">
                        Don't have an account? <a href="register.php">Sign up for free</a>
                    </p>
                </form>
                
                <div class="login-footer">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <span><i class="fas fa-lock"></i> Secure SSL Encrypted</span>
                </div>
            </div>
        </div>
        
        <div class="login-right">
            <div class="login-image">
                <div class="location-tag">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Maldives, Indian Ocean</span>
                </div>
                <div class="testimonial">
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p>"The booking platform has completely transformed how we manage our agency. Efficient, beautiful, and reliable."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar"></div>
                        <div>
                            <strong>Sarah Jenkins</strong>
                            <span>SENIOR TRAVEL AGENT</span>
                        </div>
<html class="light" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Agency - Login</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "primary": "#0db9f2",
              "background-light": "#f5f8f8",
              "background-dark": "#101e22",
            },
            fontFamily: {
              "display": ["Plus Jakarta Sans", "Noto Sans", "sans-serif"]
            },
            borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
          },
        },
      }
    </script>
    <style>
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="font-display bg-background-light dark:bg-background-dark min-h-screen w-full flex text-[#111618] dark:text-white transition-colors duration-200">
<div class="flex w-full min-h-screen shadow-2xl overflow-hidden">
    <div class="w-full lg:w-1/2 flex flex-col relative z-10 bg-white dark:bg-background-dark border-r border-[#f0f3f5] dark:border-[#1e2d32]">
        <div class="px-8 py-6 md:px-12 lg:px-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="size-8 text-primary">
                    <svg class="w-full h-full" fill="none" viewbox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_6_535)">
                            <path clip-rule="evenodd" d="M47.2426 24L24 47.2426L0.757355 24L24 0.757355L47.2426 24ZM12.2426 21H35.7574L24 9.24264L12.2426 21Z" fill="currentColor" fill-rule="evenodd"></path>
                        </g>
                        <defs>
                            <clippath id="clip0_6_535"><rect fill="white" height="48" width="48"></rect></clippath>
                        </defs>
                    </svg>
                </div>
                <span class="text-xl font-bold tracking-tight text-[#111618] dark:text-white">Travel Agency</span>
            </div>
            <a href="admin_login.php" class="text-sm text-text-secondary dark:text-text-secondary-dark hover:text-primary transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
                Admin Login
            </a>
        </div>
        
        <div class="flex-1 flex flex-col justify-center px-8 md:px-12 lg:px-16 py-8 overflow-y-auto">
            <div class="w-full max-w-md mx-auto space-y-8">
                <div class="space-y-2">
                    <h1 class="text-3xl md:text-4xl font-bold leading-tight tracking-tight text-[#111618] dark:text-white">Welcome Back</h1>
                    <p class="text-base text-gray-500 dark:text-gray-400">Please enter your details to access your booking dashboard.</p>
                </div>
                
                <!-- Hidden input to ensure user_type is always customer -->
                <input type="hidden" name="user_type" value="customer">
                
                <form method="POST" action="" class="space-y-5" id="loginForm">
                    <?php if ($error): ?>
                        <div class="bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-900/20 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg text-sm">
                            <strong>❌ Error:</strong> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-[#111618] dark:text-gray-200" for="email">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <span class="material-symbols-outlined text-[20px]">mail</span>
                            </div>
                            <input class="w-full h-12 pl-10 pr-4 rounded-lg bg-white dark:bg-[#1a2c32] border border-[#dce0e5] dark:border-[#2a3c42] text-[#111618] dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all" id="email" name="email" placeholder="name@example.com" type="email" required>
                        </div>
                    </div>
                    
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-semibold text-[#111618] dark:text-gray-200" for="password">Password</label>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <span class="material-symbols-outlined text-[20px]">lock</span>
                            </div>
                            <input class="w-full h-12 pl-10 pr-10 rounded-lg bg-white dark:bg-[#1a2c32] border border-[#dce0e5] dark:border-[#2a3c42] text-[#111618] dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all" id="password" name="password" placeholder="Enter your password" type="password" required>
                            <button class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" type="button" onclick="togglePassword()">
                                <span class="material-symbols-outlined text-[20px]" id="toggleIcon">visibility</span>
                            </button>
                        </div>
                        <div class="flex justify-end pt-1">
                            <a class="text-sm font-medium text-primary hover:text-sky-600 transition-colors" href="#">Forgot Password?</a>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full h-12 bg-primary hover:bg-sky-500 text-white font-bold rounded-lg shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-center gap-2">
                        <span>Log In</span>
                        <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                    </button>
                    
                    <div class="relative flex items-center py-2">
                        <div class="flex-grow border-t border-gray-200 dark:border-gray-700"></div>
                        <span class="flex-shrink-0 mx-4 text-gray-400 text-sm">Or continue with</span>
                        <div class="flex-grow border-t border-gray-200 dark:border-gray-700"></div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <button type="button" class="flex items-center justify-center gap-2 h-11 rounded-lg border border-[#dce0e5] dark:border-[#2a3c42] bg-white dark:bg-[#1a2c32] hover:bg-gray-50 dark:hover:bg-[#23353b] transition-colors text-sm font-semibold text-[#111618] dark:text-white">
                            <svg class="w-5 h-5" fill="currentColor" viewbox="0 0 24 24">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"></path>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
                            </svg>
                            Google
                        </button>
                        <button type="button" class="flex items-center justify-center gap-2 h-11 rounded-lg border border-[#dce0e5] dark:border-[#2a3c42] bg-white dark:bg-[#1a2c32] hover:bg-gray-50 dark:hover:bg-[#23353b] transition-colors text-sm font-semibold text-[#111618] dark:text-white">
                            <svg class="w-5 h-5" fill="currentColor" viewbox="0 0 23 23">
                                <path d="M1 1h10v10H1z" fill="#f35325"></path><path d="M12 1h10v10H12z" fill="#81bc06"></path><path d="M1 12h10v10H1z" fill="#05a6f0"></path><path d="M12 12h10v10H12z" fill="#ffba08"></path>
                            </svg>
                            Microsoft
                        </button>
                    </div>
                    
                    <div class="text-center pt-2 space-y-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Don't have an account? 
                            <a class="font-bold text-primary hover:text-sky-600 transition-colors" href="register.php">Sign up for free</a>
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Staff or Administrator? 
                            <a class="font-bold text-primary hover:text-sky-600 transition-colors" href="admin_login.php">Admin Login</a>
                        </p>
                    </div>
                </form>
                
                <div class="px-8 py-6 flex flex-wrap justify-between items-center text-xs text-gray-400 dark:text-gray-500 gap-4">
                    <div class="flex gap-4">
                        <a class="hover:text-gray-600 dark:hover:text-gray-300" href="#">Privacy Policy</a>
                        <a class="hover:text-gray-600 dark:hover:text-gray-300" href="#">Terms of Service</a>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">lock</span>
                        <span>Secure SSL Encrypted</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>

    <div class="hidden lg:flex lg:w-1/2 relative bg-background-light dark:bg-background-dark">
        <div class="absolute inset-0 bg-cover bg-center transition-opacity duration-700" style="background-image: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1200');"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-background-dark/90 via-background-dark/30 to-transparent"></div>
        <div class="relative z-10 w-full h-full flex flex-col justify-end p-16 pb-20">
            <div class="max-w-lg">
                <div class="flex gap-1 mb-4">
                    <span class="material-symbols-outlined text-yellow-400 fill-current text-[24px]" style="font-variation-settings: 'FILL' 1;">star</span>
                    <span class="material-symbols-outlined text-yellow-400 fill-current text-[24px]" style="font-variation-settings: 'FILL' 1;">star</span>
                    <span class="material-symbols-outlined text-yellow-400 fill-current text-[24px]" style="font-variation-settings: 'FILL' 1;">star</span>
                    <span class="material-symbols-outlined text-yellow-400 fill-current text-[24px]" style="font-variation-settings: 'FILL' 1;">star</span>
                    <span class="material-symbols-outlined text-yellow-400 fill-current text-[24px]" style="font-variation-settings: 'FILL' 1;">star</span>
                </div>
                <blockquote class="text-2xl md:text-3xl font-bold text-white leading-snug mb-6">
                    "The booking platform has completely transformed how we manage our agency. Efficient, beautiful, and reliable."
                </blockquote>
                <div class="flex items-center gap-4">
                    <div class="size-12 rounded-full bg-cover bg-center border-2 border-white/20" style="background-image: url('https://ui-avatars.com/api/?name=Sarah+Jenkins&background=0db9f2&color=fff');"></div>
                    <div>
                        <p class="text-white font-bold text-sm">Sarah Jenkins</p>
                        <p class="text-gray-300 text-xs uppercase tracking-wider">Senior Travel Agent</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="absolute top-8 right-8 bg-white/10 backdrop-blur-md border border-white/20 px-4 py-2 rounded-full flex items-center gap-2">
            <span class="material-symbols-outlined text-white text-[18px]">location_on</span>
            <span class="text-white text-xs font-semibold tracking-wide">Maldives, Indian Ocean</span>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.textContent = 'visibility_off';
    } else {
        passwordInput.type = 'password';
        toggleIcon.textContent = 'visibility';
    }
}

// Handle form submission
document.addEventListener('DOMContentLoaded', function() {
    // ========== FORM SUBMIT HANDLING ==========
    const loginForm = document.getElementById('loginForm');
    const submitButton = loginForm ? loginForm.querySelector('button[type="submit"]') : null;
    
    if (!loginForm) {
        console.error('✗ CRITICAL ERROR: Login form not found! ID: loginForm');
        return;
    }
    
    console.log('✓ Login form found');
    console.log('✓ Submit button found:', submitButton ? 'YES' : 'NO');
    
    // 1. VERIFY SUBMIT BUTTON CLICK
    if (submitButton) {
        submitButton.addEventListener('click', function(e) {
            console.log('\n========================================');
            console.log('STEP 1: SUBMIT BUTTON CLICKED');
            console.log('========================================');
            console.log('Button element:', this);
            console.log('Button type:', this.type);
            console.log('Event type:', e.type);
            console.log('Event target:', e.target);
        });
    }
    
    // 2. VERIFY FORM SUBMIT EVENT
    loginForm.addEventListener('submit', function(e) {
        console.log('\n========================================');
        console.log('STEP 2: FORM SUBMIT EVENT FIRED');
        console.log('========================================');
        console.log('Form element:', this);
        console.log('Form method:', this.method);
        console.log('Form action:', this.action || 'CURRENT PAGE');
        console.log('Event type:', e.type);
        console.log('Event defaultPrevented:', e.defaultPrevented);
        
        // 3. CHECK FORM DATA BEFORE SUBMISSION
        console.log('\n--- STEP 3: READING FORM DATA FROM DOM ---');
        
        // Get email
        const emailInput = document.getElementById('email');
        const email = emailInput ? emailInput.value.trim() : '';
        console.log('Email input element:', emailInput ? 'FOUND' : 'NOT FOUND');
        console.log('Email value (raw):', emailInput ? emailInput.value : 'N/A');
        console.log('Email value (trimmed):', email);
        console.log('Email empty?', email === '' ? 'YES' : 'NO');
        console.log('Email length:', email.length);
        
        // Get password
        const passwordInput = document.getElementById('password');
        const password = passwordInput ? passwordInput.value : '';
        console.log('Password input element:', passwordInput ? 'FOUND' : 'NOT FOUND');
        console.log('Password value:', password ? 'SET (' + password.length + ' chars)' : 'EMPTY');
        console.log('Password empty?', password === '' ? 'YES' : 'NO');
        
        // Get user_type (always customer for this page)
        console.log('\n--- STEP 4: USER_TYPE (Customer Login) ---');
        const formData = new FormData(this);
        const userType = formData.get('user_type') || 'customer';
        console.log('User Type:', userType, '(always customer for customer login page)');
        
        // 5. VALIDATE ALL DATA
        console.log('\n--- STEP 5: DATA VALIDATION ---');
        const validationErrors = [];
        
        if (!email || email === '') {
            validationErrors.push('Email is empty');
        }
        if (!password || password === '') {
            validationErrors.push('Password is empty');
        }
        // User type is always 'customer' for this page, no need to validate
        
        if (validationErrors.length > 0) {
            console.error('✗ VALIDATION ERRORS:', validationErrors);
        } else {
            console.log('✓ All fields validated');
        }
        
        // 6. LOG COMPLETE FORM DATA
        console.log('\n--- STEP 6: COMPLETE FORM DATA ---');
        const formDataObj = {};
        for (let [key, value] of formData.entries()) {
            formDataObj[key] = value;
            if (key === 'password') {
                console.log(`  ${key}:`, '***' + value.slice(-2) + ' (' + value.length + ' chars)');
            } else {
                console.log(`  ${key}:`, value);
            }
        }
        
        // 7. LOG REQUEST DETAILS
        console.log('\n--- STEP 7: REQUEST DETAILS ---');
        console.log('Request Method:', this.method);
        console.log('Request URL:', window.location.href);
        console.log('Request Headers:', 'application/x-www-form-urlencoded (default for POST)');
        console.log('Request Body (FormData):', formDataObj);
        
        // 9. FINAL SUMMARY
        console.log('\n--- STEP 8: FINAL SUMMARY ---');
        console.log('Email:', email);
        console.log('Password:', password ? 'SET' : 'EMPTY');
        console.log('User Type:', userType);
        console.log('All Data Valid?', validationErrors.length === 0 ? 'YES ✓' : 'NO ✗');
        
        console.log('→ Customer login attempt');
        
        // 10. Final form submission
        console.log('\n--- STEP 9: FORM SUBMISSION ---');
        console.log('Allowing form to submit naturally (no preventDefault)');
        console.log('Form will POST to:', this.action || window.location.href);
        console.log('User type being submitted: customer');
        console.log('========================================\n');
        
        // Show loading state
        if (submitButton) {
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span>Processing...</span>';
            console.log('Submit button disabled and text changed to "Processing..."');
        }
    });
    
    console.log('=== COMPREHENSIVE DEBUG SETUP COMPLETE ===');
    console.log('Ready to debug login form submission');
    console.log('Click "Log In" button to see full debug output');
});
</script>
</body>
</html>


