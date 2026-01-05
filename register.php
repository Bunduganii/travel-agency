<?php
/**
 * Registration Page - Matches Login Design
 * Allows new customers to create an account
 */
require_once 'includes/db.php';
require_once 'includes/auth.php';

$error = '';
$success = '';

// If already logged in, redirect
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Check if email or username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email or username already exists.';
        } else {
            // Hash password and insert user as customer
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, user_type) VALUES (?, ?, ?, ?, ?, 'customer')");
            $stmt->bind_param("sssss", $username, $email, $hashed_password, $full_name, $phone);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! Redirecting to login...';
                header('refresh:2;url=login.php');
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Travel Agency</title>
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
        </div>
        
        <div class="flex-1 flex flex-col justify-center px-8 md:px-12 lg:px-16 py-8 overflow-y-auto">
            <div class="w-full max-w-md mx-auto space-y-8">
                <div class="space-y-2">
                    <h1 class="text-3xl md:text-4xl font-bold leading-tight tracking-tight text-[#111618] dark:text-white">Create Account</h1>
                    <p class="text-base text-gray-500 dark:text-gray-400">Join us and start planning your next adventure.</p>
                </div>
                
                <form method="POST" action="" class="space-y-5">
                    <?php if ($error): ?>
                        <div class="bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-900/20 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg text-sm"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-900/20 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg text-sm"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-[#111618] dark:text-gray-200" for="full_name">Full Name</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <span class="material-symbols-outlined text-[20px]">person</span>
                                </div>
                                <input class="w-full h-12 pl-10 pr-4 rounded-lg bg-white dark:bg-[#1a2c32] border border-[#dce0e5] dark:border-[#2a3c42] text-[#111618] dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all" id="full_name" name="full_name" placeholder="John Doe" type="text" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-[#111618] dark:text-gray-200" for="username">Username</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <span class="material-symbols-outlined text-[20px]">account_circle</span>
                                </div>
                                <input class="w-full h-12 pl-10 pr-4 rounded-lg bg-white dark:bg-[#1a2c32] border border-[#dce0e5] dark:border-[#2a3c42] text-[#111618] dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all" id="username" name="username" placeholder="johndoe" type="text" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-[#111618] dark:text-gray-200" for="email">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <span class="material-symbols-outlined text-[20px]">mail</span>
                            </div>
                            <input class="w-full h-12 pl-10 pr-4 rounded-lg bg-white dark:bg-[#1a2c32] border border-[#dce0e5] dark:border-[#2a3c42] text-[#111618] dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all" id="email" name="email" placeholder="name@example.com" type="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-[#111618] dark:text-gray-200" for="phone">Phone Number</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <span class="material-symbols-outlined text-[20px]">phone</span>
                            </div>
                            <input class="w-full h-12 pl-10 pr-4 rounded-lg bg-white dark:bg-[#1a2c32] border border-[#dce0e5] dark:border-[#2a3c42] text-[#111618] dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all" id="phone" name="phone" placeholder="+1234567890" type="tel" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-[#111618] dark:text-gray-200" for="password">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <span class="material-symbols-outlined text-[20px]">lock</span>
                                </div>
                                <input class="w-full h-12 pl-10 pr-10 rounded-lg bg-white dark:bg-[#1a2c32] border border-[#dce0e5] dark:border-[#2a3c42] text-[#111618] dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all" id="password" name="password" placeholder="Min. 6 characters" type="password" required>
                                <button class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" type="button" onclick="togglePassword('password')">
                                    <span class="material-symbols-outlined text-[20px]" id="toggleIconPassword">visibility</span>
                                </button>
                            </div>
                        </div>
                        
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-[#111618] dark:text-gray-200" for="confirm_password">Confirm Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <span class="material-symbols-outlined text-[20px]">lock</span>
                                </div>
                                <input class="w-full h-12 pl-10 pr-10 rounded-lg bg-white dark:bg-[#1a2c32] border border-[#dce0e5] dark:border-[#2a3c42] text-[#111618] dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all" id="confirm_password" name="confirm_password" placeholder="Confirm password" type="password" required>
                                <button class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" type="button" onclick="togglePassword('confirm_password')">
                                    <span class="material-symbols-outlined text-[20px]" id="toggleIconConfirm">visibility</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full h-12 bg-primary hover:bg-sky-500 text-white font-bold rounded-lg shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-center gap-2">
                        <span>Create Account</span>
                        <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                    </button>
                    
                    <div class="text-center pt-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Already have an account? 
                            <a class="font-bold text-primary hover:text-sky-600 transition-colors" href="login.php">Log in here</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
        
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
    
    <div class="hidden lg:flex lg:w-1/2 relative bg-background-light dark:bg-background-dark">
        <div class="absolute inset-0 bg-cover bg-center transition-opacity duration-700" style="background-image: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1200');"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-background-dark/90 via-background-dark/30 to-transparent"></div>
        <div class="relative z-10 w-full h-full flex flex-col justify-end p-16 pb-20">
            <div class="max-w-lg">
                <div class="flex gap-1 mb-4">
                    <span class="material-symbols-outlined text-yellow-400 fill-current text-[24px]">star</span>
                    <span class="material-symbols-outlined text-yellow-400 fill-current text-[24px]">star</span>
                    <span class="material-symbols-outlined text-yellow-400 fill-current text-[24px]">star</span>
                    <span class="material-symbols-outlined text-yellow-400 fill-current text-[24px]">star</span>
                    <span class="material-symbols-outlined text-yellow-400 fill-current text-[24px]">star</span>
                </div>
                <blockquote class="text-2xl md:text-3xl font-bold text-white leading-snug mb-6">
                    "The best travel experiences start with the right booking platform. Simple, fast, and reliable."
                </blockquote>
                <div class="flex items-center gap-4">
                    <div class="size-12 rounded-full bg-gradient-to-br from-primary to-blue-600 border-2 border-white/20 flex items-center justify-center text-white font-bold text-lg">
                        JD
                    </div>
                    <div>
                        <p class="text-white font-bold text-sm">John Doe</p>
                        <p class="text-gray-300 text-xs uppercase tracking-wider">Travel Enthusiast</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="absolute top-8 right-8 bg-white/10 backdrop-blur-md border border-white/20 px-4 py-2 rounded-full flex items-center gap-2">
            <span class="material-symbols-outlined text-white text-[18px]">location_on</span>
            <span class="text-white text-xs font-semibold tracking-wide">Bali, Indonesia</span>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById('toggleIcon' + (fieldId === 'password' ? 'Password' : 'Confirm'));
    if (field.type === 'password') {
        field.type = 'text';
        icon.textContent = 'visibility_off';
    } else {
        field.type = 'password';
        icon.textContent = 'visibility';
    }
}
</script>
</body>
</html>
