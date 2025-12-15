<!DOCTYPE html>
<html>
<head>
    <title>EXPoints - System Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-box {
            background: white;
            padding: 20px;
            margin: 10px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        h1 { color: #333; }
        h2 { color: #666; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>🎮 EXPoints System Test</h1>
    
    <?php
    echo "<div class='test-box'>";
    echo "<h2>1. PHP Version Check</h2>";
    if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
        echo "<p class='success'>✅ PHP " . PHP_VERSION . " (Compatible)</p>";
    } else {
        echo "<p class='error'>❌ PHP " . PHP_VERSION . " (Needs 7.4+)</p>";
    }
    echo "</div>";
    
    echo "<div class='test-box'>";
    echo "<h2>2. Required Extensions</h2>";
    $required = ['mysqli', 'pdo', 'json', 'mbstring', 'openssl'];
    foreach ($required as $ext) {
        if (extension_loaded($ext)) {
            echo "<p class='success'>✅ $ext</p>";
        } else {
            echo "<p class='error'>❌ $ext (Missing)</p>";
        }
    }
    echo "</div>";
    
    echo "<div class='test-box'>";
    echo "<h2>3. Environment File</h2>";
    if (file_exists('.env')) {
        echo "<p class='success'>✅ .env file exists</p>";
        
        // Load and check .env
        require_once 'config/env.php';
        
        $hasUrl = !empty(getenv('SUPABASE_URL'));
        $hasKey = !empty(getenv('SUPABASE_SERVICE_KEY'));
        
        if ($hasUrl && $hasKey) {
            echo "<p class='success'>✅ Supabase credentials configured</p>";
            echo "<p>URL: " . getenv('SUPABASE_URL') . "</p>";
        } else {
            echo "<p class='error'>❌ Missing Supabase credentials in .env</p>";
        }
    } else {
        echo "<p class='error'>❌ .env file not found</p>";
        echo "<p>Copy .env.example to .env and configure</p>";
    }
    echo "</div>";
    
    echo "<div class='test-box'>";
    echo "<h2>4. Supabase Connection Test</h2>";
    
    try {
        require_once 'config/supabase.php';
        $supabase = new SupabaseService();
        echo "<p class='success'>✅ SupabaseService initialized</p>";
        
        // Test a simple query
        try {
            $result = $supabase->query('users', ['select' => 'id', 'limit' => 1]);
            echo "<p class='success'>✅ Database connection successful</p>";
            echo "<p>Connection working! Can query users table.</p>";
        } catch (Exception $e) {
            echo "<p class='warning'>⚠️ SupabaseService loaded but query failed</p>";
            echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ SupabaseService error</p>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
    echo "</div>";
    
    echo "<div class='test-box'>";
    echo "<h2>5. Directory Structure</h2>";
    $dirs = ['user', 'api', 'assets', 'config', 'includes', 'admin'];
    foreach ($dirs as $dir) {
        if (is_dir($dir)) {
            echo "<p class='success'>✅ /$dir</p>";
        } else {
            echo "<p class='error'>❌ /$dir (Missing)</p>";
        }
    }
    echo "</div>";
    
    echo "<div class='test-box'>";
    echo "<h2>6. Key Files</h2>";
    $files = [
        'user/dashboard.php' => 'Main Dashboard',
        'user/login.php' => 'Login Page',
        'api/posts.php' => 'Posts API',
        'includes/db_helper.php' => 'Database Helper',
        'assets/css/index.css' => 'Main CSS'
    ];
    foreach ($files as $file => $desc) {
        if (file_exists($file)) {
            echo "<p class='success'>✅ $file ($desc)</p>";
        } else {
            echo "<p class='error'>❌ $file ($desc) (Missing)</p>";
        }
    }
    echo "</div>";
    
    echo "<div class='test-box'>";
    echo "<h2>7. Session Test</h2>";
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    echo "<p class='success'>✅ Sessions working</p>";
    echo "<p>Session ID: " . session_id() . "</p>";
    echo "</div>";
    
    echo "<div class='test-box'>";
    echo "<h2>✅ System Status</h2>";
    echo "<p><strong>Your EXPoints system is ready!</strong></p>";
    echo "<ul>";
    echo "<li><a href='user/index.php'>Go to Landing Page</a></li>";
    echo "<li><a href='user/login.php'>Login Page</a></li>";
    echo "<li><a href='user/register.php'>Register Page</a></li>";
    echo "</ul>";
    echo "<p style='color: #666; font-size: 14px;'>If you see any ❌ errors above, check SETUP_AND_RUN.md for solutions.</p>";
    echo "</div>";
    ?>
</body>
</html>
