<?php
require_once __DIR__ . '/../config/supabase-session.php';
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Supabase database connection
require_once __DIR__ . '/../includes/db_helper.php';

// Get error from URL
$error = isset($_GET['error']) ? urldecode($_GET['error']) : '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        try {
            $db = getDBConnection();
            
            // Query user from Supabase - include role field and disabled status
            $stmt = $db->prepare("SELECT id, email, password, role, is_disabled, disabled_reason, disabled_at, disabled_by FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Direct password comparison (plain text - matches your database)
                if ($password === $user['password']) {
                    // Get user role - ensure it's set correctly from database
                    $role = isset($user['role']) && !empty($user['role']) ? $user['role'] : 'user';
                    
                    // Check if account is disabled (admin only now, no more moderators)
                    $is_disabled = $user['is_disabled'] ?? 0;
                    if ($is_disabled == 1) {
                        // Set disabled info in session for disabled.php to display
                        $_SESSION['disabled_reason'] = $user['disabled_reason'] ?? 'Your account has been disabled by an administrator.';
                        $_SESSION['disabled_at'] = $user['disabled_at'];
                        $_SESSION['disabled_by'] = $user['disabled_by'];
                        
                        // Redirect to disabled page
                        header('Location: disabled.php');
                        exit();
                    }
                    
                    // Get username and ban status from user_info table
                    $userInfoStmt = $db->prepare("SELECT username, is_banned, ban_reason, banned_at, banned_by FROM user_info WHERE user_id = ?");
                    $userInfoStmt->bind_param("i", $user['id']);
                    $userInfoStmt->execute();
                    $userInfoResult = $userInfoStmt->get_result();
                    
                    $username = $user['email']; // Default to email if username not found
                    $is_banned = 0;
                    $ban_reason = '';
                    $banned_at = '';
                    $banned_by = '';
                    
                    if ($userInfoResult && $userInfoResult->num_rows > 0) {
                        $userInfoData = $userInfoResult->fetch_assoc();
                        $username = $userInfoData['username'];
                        $is_banned = $userInfoData['is_banned'] ?? 0;
                        $ban_reason = $userInfoData['ban_reason'] ?? '';
                        $banned_at = $userInfoData['banned_at'] ?? '';
                        $banned_by = $userInfoData['banned_by'] ?? '';
                    }
                    
                    // Check if user is banned
                    if ($is_banned == 1) {
                        // Set ban info in session for banned.php to display
                        $_SESSION['ban_reason'] = $ban_reason;
                        $_SESSION['banned_at'] = $banned_at;
                        $_SESSION['banned_by'] = $banned_by;
                        
                        // Redirect to banned page
                        header('Location: banned.php');
                        exit();
                    }
                    
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['username'] = $username; // Use actual username from user_info table
                    $_SESSION['user_role'] = $role;
                    $_SESSION['authenticated'] = true;
                    $_SESSION['login_time'] = time();
                    
                    // Redirect based on role
                    switch ($role) {
                        case 'admin':
                            header('Location: ../admin/dashboard.php');
                            exit();
                        case 'mod':
                            header('Location: ../mod/dashboard.php');
                            exit();
                        case 'user':
                        default:
                            header('Location: dashboard.php');
                            exit();
                    }
                } else {
                    $error = 'Invalid email or password';
                }
            } else {
                $error = 'Invalid email or password';
            }
            
            $stmt->close();
            if (isset($userInfoStmt)) {
                $userInfoStmt->close();
            }
            $db->close();
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'Database connection failed. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EXPoints • Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/login.css" rel="stylesheet">
    <style>
        /* Full-page loading overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #0a1a4d 0%, #1b378d 50%, #0a1a4d 100%);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            flex-direction: column;
            animation: gradientShift 3s ease infinite;
        }
        
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .loading-overlay.active {
            display: flex;
        }
        
        .loading-stars-container {
            position: relative;
            width: 200px;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Single glowing star with halo */
        .loading-star-main {
            font-size: 5rem;
            position: relative;
            animation: starRotateGlow 2s ease-in-out infinite;
            filter: drop-shadow(0 0 30px rgba(255, 215, 0, 1));
        }
        
        /* Halo rings around star */
        .star-halo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 2px solid rgba(255, 215, 0, 0.4);
            border-radius: 50%;
            animation: haloExpand 2s ease-out infinite;
        }
        
        .star-halo:nth-child(1) {
            width: 120px;
            height: 120px;
            animation-delay: 0s;
        }
        
        .star-halo:nth-child(2) {
            width: 160px;
            height: 160px;
            animation-delay: 0.5s;
        }
        
        .star-halo:nth-child(3) {
            width: 200px;
            height: 200px;
            animation-delay: 1s;
        }
        
        @keyframes starRotateGlow {
            0% {
                transform: rotate(0deg) scale(1);
                filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.8));
            }
            50% {
                transform: rotate(180deg) scale(1.2);
                filter: drop-shadow(0 0 40px rgba(255, 215, 0, 1)) drop-shadow(0 0 60px rgba(255, 215, 0, 0.6));
            }
            100% {
                transform: rotate(360deg) scale(1);
                filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.8));
            }
        }
        
        @keyframes haloExpand {
            0% {
                transform: translate(-50%, -50%) scale(0.8);
                opacity: 0;
                border-width: 3px;
            }
            50% {
                opacity: 0.6;
                border-width: 2px;
            }
            100% {
                transform: translate(-50%, -50%) scale(1.3);
                opacity: 0;
                border-width: 1px;
            }
        }
        
        .loading-text {
            margin-top: 3rem;
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            animation: textFade 2s ease-in-out infinite;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
        }
        
        @keyframes textFade {
            0%, 100% { opacity: 0.7; }
            50% { opacity: 1; }
        }
        
        /* Progress bar container */
        .progress-container {
            width: 400px;
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 2rem;
            position: relative;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #ffd700, #ffed4e, #ffd700);
            background-size: 200% 100%;
            border-radius: 10px;
            animation: progressGlow 2s ease-in-out infinite, progressMove 15s ease-out forwards;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.8), inset 0 0 10px rgba(255, 255, 255, 0.5);
            width: 0%;
        }
        
        @keyframes progressGlow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        @keyframes progressMove {
            0% { width: 0%; }
            95% { width: 95%; }
            100% { width: 100%; }
        }
        
        .progress-text {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-top: 0.5rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-stars-container">
            <div class="star-halo"></div>
            <div class="star-halo"></div>
            <div class="star-halo"></div>
            <div class="loading-star-main">⭐</div>
        </div>
        <div class="loading-text">Loading your dashboard...</div>
        <div class="progress-container">
            <div class="progress-bar"></div>
        </div>
        <div class="progress-text">Please wait...</div>
    </div>

    <!-- Custom Alert -->
    <?php
require_once __DIR__ . '/../config/supabase-session.php'; if ($error): ?>
    <div class="custom-alert alert-dismissible fade show" role="alert">
        <div class="alert-content">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span><?php
require_once __DIR__ . '/../config/supabase-session.php'; echo htmlspecialchars($error); ?></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php
require_once __DIR__ . '/../config/supabase-session.php'; endif; ?>

    <div class="split-screen-container">
        <!-- LEFT SIDE - Blue Welcome Section -->
        <div class="left-side">
            <!-- Back button at top left -->
            <a href="../index.php" class="back-button">
                <i class="bi bi-arrow-left"></i> Back to Home
            </a>
            
            <!-- Logo -->
            <div class="logo-container">
                <img src="../assets/img/EXPoints Logo.png" alt="EXPoints Logo" class="top-logo">
            </div>
            
            <!-- Welcome message and panda -->
            <div class="welcome-content">
                <h1 class="welcome-title">Welcome to EXPoints!</h1>
                <p class="welcome-subtitle" id="rotatingText"></p>
                <img src="../assets/img/Login Panda Controller.png" alt="Login Panda" class="panda-mascot">
            </div>
        </div>

        <!-- RIGHT SIDE - White Login Form -->
        <div class="right-side">
            <div class="login-form-container">
                <h2 class="login-title">Login to Your Account</h2>
                <p class="login-subtitle">Enter your credentials to access your account</p>

                <form method="POST" action="login.php" id="loginForm">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control input-glass" id="email" name="email" required 
                               placeholder="Enter your email">
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control input-glass" id="password" name="password" required
                               placeholder="Enter your password">
                    </div>
                    
                    <button type="submit" class="btn btn-brand w-100 mb-3" id="loginBtn">LOGIN</button>
                    
                    <div class="text-center mb-3">
                        <a href="forgot.php" class="forgot-link">Forgot Password?</a>
                    </div>
                    
                    <div class="auth-divider my-3">OR</div>
                    
                    <button type="button" class="btn btn-google w-100">
                        <span class="g-logo">G</span> 
                        Login with Google
                    </button>
                </form>
                
                <div class="register-link">
                    Don't have an account? <a href="register.php">Register here</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show loading overlay on form submit
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            // Only show loading if fields are filled
            if (email && password) {
                document.getElementById('loadingOverlay').classList.add('active');
            }
        });
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.custom-alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    if (alert) {
                        bootstrap.Alert.getOrCreateInstance(alert).close();
                    }
                }, 5000);
            });
        });
        
        // Rotating welcome text
        const welcomeTexts = [
            "Ready to earn more XP? Jump back in and keep leveling up!",
            "Your next achievement awaits! log in and continue your grind!",
            "Welcome back, gamer! The community's waiting for your next review.",
            "Every login brings you closer to the top. Let's see what you've got!",
            "Log in. Level up. Let's play."
        ];
        
        // Select a random text on page load
        const randomIndex = Math.floor(Math.random() * welcomeTexts.length);
        document.getElementById('rotatingText').textContent = welcomeTexts[randomIndex];
    </script>
</body>
</html>
```
