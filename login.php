<?php
/**
 * Login Page
 * Handles user authentication (Admin and Customer)
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

