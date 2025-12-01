<?php
/**
 * Simple test to check if posts can be loaded
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Posts Loading Test</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .info { color: blue; }
        pre { background: #fff; padding: 10px; border: 1px solid #ddd; overflow: auto; }
        h2 { border-bottom: 2px solid #333; padding-bottom: 5px; }
    </style>
</head>
<body>
    <h1>🔍 EXPoints Posts Loading Test</h1>
    
    <?php
    // Test 1: Environment variables
    echo "<h2>1. Environment Variables</h2>";
    try {
        require_once __DIR__ . '/config/env.php';
        
        $supabaseUrl = getenv('SUPABASE_URL');
        $supabaseKey = getenv('SUPABASE_SERVICE_KEY');
        
        if ($supabaseUrl && $supabaseKey) {
            echo "<p class='success'>✅ Supabase credentials loaded</p>";
            echo "<p class='info'>URL: " . substr($supabaseUrl, 0, 40) . "...</p>";
            echo "<p class='info'>Key length: " . strlen($supabaseKey) . " characters</p>";
        } else {
            echo "<p class='error'>❌ Supabase credentials NOT loaded</p>";
            echo "<p>SUPABASE_URL: " . ($supabaseUrl ? "SET" : "NOT SET") . "</p>";
            echo "<p>SUPABASE_SERVICE_KEY: " . ($supabaseKey ? "SET" : "NOT SET") . "</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    }
    
    // Test 2: Database connection
    echo "<h2>2. Database Connection</h2>";
    try {
        require_once __DIR__ . '/includes/db_helper.php';
        $db = getDBConnection();
        
        if ($db) {
            echo "<p class='success'>✅ Database connection successful</p>";
            echo "<p class='info'>Connection class: " . get_class($db) . "</p>";
        } else {
            echo "<p class='error'>❌ Database connection failed</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
    // Test 3: Query posts
    if (isset($db) && $db) {
        echo "<h2>3. Query Posts Table</h2>";
        try {
            // Try without WHERE first
            echo "<h3>Test 3a: SELECT * FROM posts (no filters)</h3>";
            $result = $db->query("SELECT * FROM posts LIMIT 5");
            
            if ($result) {
                echo "<p class='success'>✅ Query executed</p>";
                
                $posts = [];
                while ($row = $result->fetch_assoc()) {
                    $posts[] = $row;
                }
                
                echo "<p class='info'>Found " . count($posts) . " posts</p>";
                
                if (count($posts) > 0) {
                    echo "<h4>First Post:</h4>";
                    echo "<pre>" . print_r($posts[0], true) . "</pre>";
                } else {
                    echo "<p class='warning'>⚠️ No posts in database</p>";
                }
            } else {
                echo "<p class='error'>❌ Query failed</p>";
            }
            
            // Try with WHERE hidden = 0
            echo "<h3>Test 3b: SELECT * FROM posts WHERE hidden = 0</h3>";
            $result2 = $db->query("SELECT * FROM posts WHERE hidden = 0 LIMIT 5");
            
            if ($result2) {
                echo "<p class='success'>✅ Query with WHERE executed</p>";
                
                $posts2 = [];
                while ($row = $result2->fetch_assoc()) {
                    $posts2[] = $row;
                }
                
                echo "<p class='info'>Found " . count($posts2) . " non-hidden posts</p>";
            } else {
                echo "<p class='error'>❌ Query with WHERE failed</p>";
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>❌ Query error: " . $e->getMessage() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
        
        // Test 4: Check other tables
        echo "<h2>4. Check Related Tables</h2>";
        $tables = ['users', 'user_info', 'post_likes', 'post_comments'];
        
        foreach ($tables as $table) {
            try {
                $result = $db->query("SELECT COUNT(*) as count FROM $table");
                if ($result) {
                    $row = $result->fetch_assoc();
                    echo "<p class='success'>✅ $table: " . $row['count'] . " records</p>";
                }
            } catch (Exception $e) {
                echo "<p class='error'>❌ $table: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // Test 5: Check PHP error log location
    echo "<h2>5. PHP Error Log</h2>";
    echo "<p class='info'>Error log file: " . ini_get('error_log') . "</p>";
    echo "<p class='info'>Check this file for detailed error messages</p>";
    ?>
    
    <hr>
    <h2>Summary</h2>
    <p>If you see errors above, please:</p>
    <ol>
        <li>Make sure your <code>.env</code> file exists with proper Supabase credentials</li>
        <li>Check that posts exist in your Supabase database</li>
        <li>Look at the PHP error log for detailed messages</li>
    </ol>
    
    <p><a href="user/dashboard.php">← Back to Dashboard</a></p>
</body>
</html>
