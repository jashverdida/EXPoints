<?php
/**
 * Debug script to test post loading
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Dashboard Posts Debug</h1>";
echo "<hr>";

// Test 1: Check .env file
echo "<h2>1. Environment Check</h2>";
if (file_exists(__DIR__ . '/.env')) {
    echo "✅ .env file exists<br>";
    $envContent = file_get_contents(__DIR__ . '/.env');
    $hasSupabaseUrl = strpos($envContent, 'SUPABASE_URL') !== false;
    $hasSupabaseKey = strpos($envContent, 'SUPABASE_SERVICE_KEY') !== false;
    
    echo $hasSupabaseUrl ? "✅ SUPABASE_URL found<br>" : "❌ SUPABASE_URL missing<br>";
    echo $hasSupabaseKey ? "✅ SUPABASE_SERVICE_KEY found<br>" : "❌ SUPABASE_SERVICE_KEY missing<br>";
} else {
    echo "❌ .env file NOT found<br>";
}
echo "<hr>";

// Test 2: Load environment
echo "<h2>2. Load Environment Variables</h2>";
try {
    require_once __DIR__ . '/config/env.php';
    echo "✅ env.php loaded<br>";
    
    $supabaseUrl = getenv('SUPABASE_URL');
    $supabaseKey = getenv('SUPABASE_SERVICE_KEY');
    
    echo "SUPABASE_URL: " . ($supabaseUrl ? "Set (" . substr($supabaseUrl, 0, 30) . "...)" : "NOT SET") . "<br>";
    echo "SUPABASE_SERVICE_KEY: " . ($supabaseKey ? "Set (length: " . strlen($supabaseKey) . ")" : "NOT SET") . "<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// Test 3: Database Connection
echo "<h2>3. Database Connection</h2>";
try {
    require_once __DIR__ . '/includes/db_helper.php';
    $db = getDBConnection();
    
    if ($db) {
        echo "✅ Database connection established<br>";
        echo "Connection type: " . get_class($db) . "<br>";
    } else {
        echo "❌ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
echo "<hr>";

// Test 4: Simple Query
if (isset($db) && $db) {
    echo "<h2>4. Test Query: SELECT * FROM posts</h2>";
    try {
        $result = $db->query("SELECT * FROM posts LIMIT 5");
        
        if ($result) {
            echo "✅ Query executed successfully<br>";
            echo "Result type: " . get_class($result) . "<br>";
            
            $posts = [];
            while ($row = $result->fetch_assoc()) {
                $posts[] = $row;
            }
            
            echo "Posts found: " . count($posts) . "<br><br>";
            
            if (count($posts) > 0) {
                echo "<h3>Sample Post Data:</h3>";
                echo "<pre>" . print_r($posts[0], true) . "</pre>";
            } else {
                echo "<strong>⚠️ No posts found in database</strong><br>";
            }
        } else {
            echo "❌ Query returned no result<br>";
        }
    } catch (Exception $e) {
        echo "❌ Query error: " . $e->getMessage() . "<br>";
        echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
    }
    echo "<hr>";
    
    // Test 5: Check for 'hidden' column
    echo "<h2>5. Check Posts Table Schema</h2>";
    try {
        // Try to select with hidden field
        $result = $db->query("SELECT * FROM posts WHERE hidden = 0 LIMIT 1");
        if ($result) {
            echo "✅ 'hidden' column exists<br>";
        }
    } catch (Exception $e) {
        echo "⚠️ 'hidden' column might not exist: " . $e->getMessage() . "<br>";
        echo "Trying without 'hidden' filter...<br>";
        
        try {
            $result = $db->query("SELECT * FROM posts LIMIT 1");
            if ($result) {
                $row = $result->fetch_assoc();
                echo "✅ Posts table accessible<br>";
                echo "Available columns: " . implode(", ", array_keys($row)) . "<br>";
            }
        } catch (Exception $e2) {
            echo "❌ Cannot access posts table: " . $e2->getMessage() . "<br>";
        }
    }
    echo "<hr>";
    
    // Test 6: Check related tables
    echo "<h2>6. Check Related Tables</h2>";
    $tables = ['user_info', 'post_comments', 'post_likes'];
    foreach ($tables as $table) {
        try {
            $result = $db->query("SELECT COUNT(*) as count FROM $table");
            if ($result) {
                $row = $result->fetch_assoc();
                echo "✅ Table '$table': " . $row['count'] . " records<br>";
            }
        } catch (Exception $e) {
            echo "❌ Table '$table' error: " . $e->getMessage() . "<br>";
        }
    }
}

echo "<hr>";
echo "<h2>Debug Complete</h2>";
echo "<p>Check the output above to identify the issue.</p>";
?>
