<?php
/**
 * Quick .env checker
 * Run this first to make sure your environment is set up
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>EXPoints - .env Setup Check</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        h1 { color: #333; margin-top: 0; }
        .status { padding: 15px; margin: 10px 0; border-radius: 8px; }
        .success { background: #4CAF50; color: white; }
        .error { background: #f44336; color: white; }
        .warning { background: #ff9800; color: white; }
        .info { background: #2196F3; color: white; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; font-family: 'Courier New', monospace; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .btn:hover { background: #764ba2; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 .env Setup Checker</h1>
        
        <?php
        $envPath = __DIR__ . '/.env';
        $examplePath = __DIR__ . '/.env.example';
        
        // Check if .env exists
        if (!file_exists($envPath)) {
            echo '<div class="status error">';
            echo '<strong>❌ .env file NOT found!</strong><br><br>';
            echo 'Expected location: <code>' . $envPath . '</code><br><br>';
            echo '<strong>Solution:</strong>';
            echo '<ol>';
            echo '<li>Create a file named <code>.env</code> in your project root</li>';
            echo '<li>Copy the contents from <code>.env.example</code></li>';
            echo '<li>Fill in your Supabase credentials</li>';
            echo '</ol>';
            
            if (file_exists($examplePath)) {
                echo '<div class="info" style="margin-top: 15px;">ℹ️ You have a .env.example file. Copy it to .env and edit the values.</div>';
            }
            
            echo '</div>';
            
            echo '<h2>📝 What your .env file should look like:</h2>';
            echo '<pre>SUPABASE_URL=https://xxxxxxxxxxxxx.supabase.co
SUPABASE_SERVICE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJz...
(very long key, 100+ characters)</pre>';
            
        } else {
            echo '<div class="status success">';
            echo '✅ .env file exists!';
            echo '</div>';
            
            // Load and check contents
            $envContent = file_get_contents($envPath);
            $lines = explode("\n", $envContent);
            
            $hasUrl = false;
            $hasKey = false;
            $urlValue = '';
            $keyLength = 0;
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (strpos($line, 'SUPABASE_URL=') === 0) {
                    $hasUrl = true;
                    $urlValue = trim(str_replace('SUPABASE_URL=', '', $line), '"\'');
                }
                if (strpos($line, 'SUPABASE_SERVICE_KEY=') === 0) {
                    $hasKey = true;
                    $keyValue = trim(str_replace('SUPABASE_SERVICE_KEY=', '', $line), '"\'');
                    $keyLength = strlen($keyValue);
                }
            }
            
            // Check SUPABASE_URL
            echo '<h2>SUPABASE_URL</h2>';
            if ($hasUrl && !empty($urlValue) && strpos($urlValue, 'supabase.co') !== false) {
                echo '<div class="status success">';
                echo '✅ SUPABASE_URL is set correctly<br>';
                echo 'Value: <code>' . substr($urlValue, 0, 40) . '...</code>';
                echo '</div>';
            } elseif ($hasUrl && !empty($urlValue)) {
                echo '<div class="status warning">';
                echo '⚠️ SUPABASE_URL is set but might be incorrect<br>';
                echo 'Current value: <code>' . htmlspecialchars($urlValue) . '</code><br>';
                echo 'Should look like: <code>https://xxxxx.supabase.co</code>';
                echo '</div>';
            } else {
                echo '<div class="status error">';
                echo '❌ SUPABASE_URL is missing or empty<br>';
                echo 'Add this line to your .env file:<br>';
                echo '<code>SUPABASE_URL=https://your-project.supabase.co</code>';
                echo '</div>';
            }
            
            // Check SUPABASE_SERVICE_KEY
            echo '<h2>SUPABASE_SERVICE_KEY</h2>';
            if ($hasKey && $keyLength > 100) {
                echo '<div class="status success">';
                echo '✅ SUPABASE_SERVICE_KEY is set<br>';
                echo 'Key length: ' . $keyLength . ' characters (looks good!)';
                echo '</div>';
            } elseif ($hasKey && $keyLength > 0) {
                echo '<div class="status warning">';
                echo '⚠️ SUPABASE_SERVICE_KEY seems too short<br>';
                echo 'Current length: ' . $keyLength . ' characters<br>';
                echo 'Expected: 100+ characters<br><br>';
                echo '<strong>Make sure you copied the <u>service_role</u> key, not the anon key!</strong>';
                echo '</div>';
            } else {
                echo '<div class="status error">';
                echo '❌ SUPABASE_SERVICE_KEY is missing or empty<br>';
                echo 'Add this line to your .env file:<br>';
                echo '<code>SUPABASE_SERVICE_KEY=your-service-role-key-here</code>';
                echo '</div>';
            }
            
            // Overall status
            echo '<hr>';
            if ($hasUrl && $hasKey && $keyLength > 100 && strpos($urlValue, 'supabase.co') !== false) {
                echo '<div class="status success">';
                echo '<h2 style="margin-top: 0;">🎉 All Set!</h2>';
                echo 'Your .env file is configured correctly.<br><br>';
                echo '<strong>Next steps:</strong>';
                echo '<ol>';
                echo '<li><a href="test-posts-simple.php" style="color: white;">Run the full diagnostic test</a></li>';
                echo '<li><a href="user/dashboard.php" style="color: white;">Go to dashboard</a></li>';
                echo '</ol>';
                echo '</div>';
            } else {
                echo '<div class="status warning">';
                echo '<h2 style="margin-top: 0;">⚠️ Configuration Incomplete</h2>';
                echo 'Please fix the issues above and refresh this page.';
                echo '</div>';
            }
        }
        ?>
        
        <hr>
        <h2>🔑 Where to Find Your Supabase Credentials</h2>
        <ol>
            <li>Go to <a href="https://supabase.com/dashboard" target="_blank">supabase.com/dashboard</a></li>
            <li>Select your project</li>
            <li>Click on <strong>Settings</strong> (gear icon) in the left sidebar</li>
            <li>Click on <strong>API</strong></li>
            <li>Copy:
                <ul>
                    <li><strong>Project URL</strong> → Use for SUPABASE_URL</li>
                    <li><strong>service_role key</strong> (expand "Project API keys") → Use for SUPABASE_SERVICE_KEY</li>
                </ul>
            </li>
        </ol>
        
        <a href="test-posts-simple.php" class="btn">Next: Run Full Diagnostic Test →</a>
    </div>
</body>
</html>
