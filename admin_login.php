<?php
/**
 * Admin Login Page
 * Dedicated login page for administrators and staff
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
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Query admin user from database
        $stmt = $conn->prepare("SELECT id, username, email, password, full_name, user_type FROM users WHERE email = ? AND user_type = 'admin'");
        if (!$stmt) {
            $error = 'Database error. Please try again.';
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verify password
                $password_valid = password_verify($password, $user['password']);
                
                if ($password_valid) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['user_type'] = $user['user_type'];
                    
                    // Redirect to admin dashboard
                    header('Location: admin/admin_dashboard.php');
                    exit();
                } else {
                    $error = 'Invalid email or password.';
                }
            } else {
                $error = 'Invalid email or password. Admin account not found.';
            }
            
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Travel Agency</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Theme Configuration -->
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#0db9f2",
                        "primary-dark": "#0a9acb",
                        "background-light": "#f5f8f8",
                        "background-dark": "#101e22",
                        "surface-light": "#ffffff",
                        "surface-dark": "#1a2c32",
                        "text-main": "#111618",
                        "text-secondary": "#60808a",
                        "text-main-dark": "#e0e6e8",
                        "text-secondary-dark": "#94aab2",
                    },
                    fontFamily: {
                        "display": ["Plus Jakarta Sans", "sans-serif"],
                    },
                    borderRadius: {
                        "xl": "1rem",
                        "2xl": "1.5rem",
                    },
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
        
        /* Animations */
        @keyframes float {
            0%, 100% {
                transform: translateY(0px) translateX(0px);
            }
            33% {
                transform: translateY(-20px) translateX(10px);
            }
            66% {
                transform: translateY(10px) translateX(-15px);
            }
        }
        
        @keyframes float-delayed {
            0%, 100% {
                transform: translateY(0px) translateX(0px);
            }
            50% {
                transform: translateY(-30px) translateX(-10px);
            }
        }
        
        @keyframes float-slow {
            0%, 100% {
                transform: translateY(0px) translateX(0px);
            }
            50% {
                transform: translateY(15px) translateX(20px);
            }
        }
        
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes bounce-slow {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }
        
        @keyframes rotate-slow {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        .animate-float-delayed {
            animation: float-delayed 8s ease-in-out infinite;
        }
        
        .animate-float-slow {
            animation: float-slow 10s ease-in-out infinite;
        }
        
        .animate-float-delayed-2 {
            animation: float-delayed 7s ease-in-out infinite 1s;
        }
        
        .animate-fade-in {
            animation: fade-in 1s ease-out;
        }
        
        .animate-slide-up {
            animation: slide-up 0.8s ease-out;
        }
        
        .animate-slide-up-delayed {
            animation: slide-up 0.8s ease-out 0.2s both;
        }
        
        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        .animate-bounce-slow {
            animation: bounce-slow 2s ease-in-out infinite;
        }
        
        .animate-rotate-slow {
            animation: rotate-slow 20s linear infinite;
        }
        
        .animate-fade-in-delayed-1 {
            animation: fade-in 0.8s ease-out 0.3s both;
        }
        
        .animate-fade-in-delayed-2 {
            animation: fade-in 0.8s ease-out 0.4s both;
        }
        
        .animate-fade-in-delayed-3 {
            animation: fade-in 0.8s ease-out 0.5s both;
        }
        
        .animate-fade-in-delayed-4 {
            animation: fade-in 0.8s ease-out 0.6s both;
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
            <a href="login.php" class="text-sm text-text-secondary dark:text-text-secondary-dark hover:text-primary transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Customer Login
            </a>
        </div>
        
        <div class="flex-1 flex flex-col justify-center px-8 md:px-12 lg:px-16 py-8 overflow-y-auto">
            <div class="w-full max-w-md mx-auto space-y-8">
                <div class="space-y-2">
                    <h1 class="text-3xl md:text-4xl font-bold leading-tight tracking-tight text-[#111618] dark:text-white">Admin Portal</h1>
                    <p class="text-base text-gray-500 dark:text-gray-400">Staff and administrator login. Enter your credentials to access the admin dashboard.</p>
                </div>
                
                <form method="POST" action="" class="space-y-5" id="adminLoginForm">
                    <?php if ($error): ?>
                        <div class="bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-900/20 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg text-sm">
                            <strong>❌ Error:</strong> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-900/20 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg text-sm">
                            <strong>✓ Success:</strong> <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-semibold text-[#111618] dark:text-white">Email Address</label>
                        <input type="email" id="email" name="email" required autocomplete="email" placeholder="Enter your admin email" class="w-full px-4 py-3 bg-white dark:bg-[#1a2c32] border border-[#e0e6e8] dark:border-[#2a3c42] rounded-lg text-[#111618] dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    
                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-semibold text-[#111618] dark:text-white">Password</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Enter your password" class="w-full px-4 py-3 bg-white dark:bg-[#1a2c32] border border-[#e0e6e8] dark:border-[#2a3c42] rounded-lg text-[#111618] dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all pr-12">
                            <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#111618] dark:hover:text-white transition-colors">
                                <span class="material-symbols-outlined text-[20px]" id="passwordToggleIcon">visibility</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                        </label>
                        <a href="#" class="text-sm text-primary hover:text-primary-dark font-semibold">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-3 px-4 rounded-lg transition-all shadow-lg shadow-primary/20 hover:shadow-xl hover:shadow-primary/30">
                        Log In
                    </button>
                </form>
                
                <div class="text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Not an admin? 
                        <a href="login.php" class="text-primary hover:text-primary-dark font-semibold">Go to Customer Login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right side animated image/design -->
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-primary via-primary-dark to-blue-600">
        <!-- Animated Background Image -->
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80'); opacity: 0.3;"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-primary/80 via-primary-dark/80 to-blue-600/80"></div>
        
        <!-- Animated Floating Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <!-- Floating circles -->
            <div class="absolute top-20 left-20 w-32 h-32 bg-white/10 rounded-full blur-xl animate-float"></div>
            <div class="absolute top-40 right-32 w-24 h-24 bg-white/15 rounded-full blur-lg animate-float-delayed"></div>
            <div class="absolute bottom-32 left-32 w-40 h-40 bg-white/5 rounded-full blur-2xl animate-float-slow"></div>
            <div class="absolute bottom-20 right-20 w-28 h-28 bg-white/10 rounded-full blur-xl animate-float-delayed-2"></div>
        </div>
        
        <!-- Content Overlay -->
        <div class="relative z-10 flex flex-col justify-center items-center text-white p-12 space-y-8 w-full">
            <div class="text-center space-y-4 animate-fade-in">
                <div class="size-20 mx-auto bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm animate-pulse-slow shadow-2xl">
                    <span class="material-symbols-outlined text-5xl animate-bounce-slow">admin_panel_settings</span>
                </div>
                <h2 class="text-3xl font-bold animate-slide-up">Administrator Access</h2>
                <p class="text-lg text-white/90 max-w-md animate-slide-up-delayed">Manage bookings, view reports, and control system settings from the admin dashboard.</p>
            </div>
            <div class="grid grid-cols-2 gap-4 max-w-md w-full mt-8">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20 hover:bg-white/20 transition-all duration-300 hover:scale-105 animate-fade-in-delayed-1">
                    <span class="material-symbols-outlined text-3xl mb-2 block animate-rotate-slow">analytics</span>
                    <p class="text-sm font-semibold">Analytics</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20 hover:bg-white/20 transition-all duration-300 hover:scale-105 animate-fade-in-delayed-2">
                    <span class="material-symbols-outlined text-3xl mb-2 block animate-rotate-slow">settings</span>
                    <p class="text-sm font-semibold">Settings</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20 hover:bg-white/20 transition-all duration-300 hover:scale-105 animate-fade-in-delayed-3">
                    <span class="material-symbols-outlined text-3xl mb-2 block animate-rotate-slow">people</span>
                    <p class="text-sm font-semibold">Users</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20 hover:bg-white/20 transition-all duration-300 hover:scale-105 animate-fade-in-delayed-4">
                    <span class="material-symbols-outlined text-3xl mb-2 block animate-rotate-slow">receipt_long</span>
                    <p class="text-sm font-semibold">Reports</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('passwordToggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.textContent = 'visibility_off';
    } else {
        passwordInput.type = 'password';
        toggleIcon.textContent = 'visibility';
    }
}
</script>
</body>
</html>

