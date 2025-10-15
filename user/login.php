<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple database connection
function getDBConnection() {
    $host = '127.0.0.1';
    $dbname = 'expoints_db';
    $username = 'root';
    $password = '';
    
    try {
        $mysqli = new mysqli($host, $username, $password, $dbname);
        
        if ($mysqli->connect_error) {
            throw new Exception("Connection failed: " . $mysqli->connect_error);
        }
        
        $mysqli->set_charset('utf8mb4');
        return $mysqli;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}

// Get error from URL
$error = isset($_GET['error']) ? urldecode($_GET['error']) : '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        $db = getDBConnection();
        
        if (!$db) {
            $error = 'Database connection failed. Please try again later.';
        } else {
            // Query user from database - include role field
            $stmt = $db->prepare("SELECT id, email, password, role FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Direct password comparison (plain text - matches your database)
                if ($password === $user['password']) {
                    // Get user role (default to 'user' if not set)
                    $role = $user['role'] ?? 'user';
                    
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['username'] = $user['email']; // Use email as username
                    $_SESSION['user_role'] = $role;
                    $_SESSION['authenticated'] = true;
                    $_SESSION['login_time'] = time();
                    
                    // Close connection
                    $stmt->close();
                    $db->close();
                    
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
            $db->close();
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
</head>
<body>
    <!-- Custom Alert -->
    <?php if ($error): ?>
    <div class="custom-alert alert-dismissible fade show" role="alert">
        <div class="alert-content">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>

    <div class="split-screen-container">
        <!-- LEFT SIDE - Blue Welcome Section -->
        <div class="left-side">
            <!-- Logo at top left -->
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

                <form method="POST" action="login.php">
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
                    
                    <button type="submit" class="btn btn-brand w-100 mb-3">LOGIN</button>
                    
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
