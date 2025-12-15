<?php
/**
 * Quick Database Connection Test
 * This script tests the connection to expoints_db database
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>EXPoints Database Connection Test</h2>";
echo "<hr>";

// Database configuration
$host = '127.0.0.1';
$dbname = 'expoints_db';
$username = 'root';
$password = '';

echo "<h3>Configuration:</h3>";
echo "<ul>";
echo "<li><strong>Host:</strong> $host</li>";
echo "<li><strong>Database:</strong> $dbname</li>";
echo "<li><strong>Username:</strong> $username</li>";
echo "<li><strong>Password:</strong> " . (empty($password) ? '(empty)' : '***') . "</li>";
echo "</ul>";

echo "<hr>";
echo "<h3>Connection Test:</h3>";

try {
    // Attempt connection
    $mysqli = new mysqli($host, $username, $password, $dbname);
    
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "<p style='color: green;'>✅ <strong>SUCCESS!</strong> Connected to database successfully!</p>";
    
    // Test query - check if users table exists
    echo "<hr>";
    echo "<h3>Database Structure Test:</h3>";
    
    $result = $mysqli->query("SHOW TABLES");
    echo "<p><strong>Tables in database:</strong></p>";
    echo "<ul>";
    $tables = [];
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
        echo "<li>" . htmlspecialchars($row[0]) . "</li>";
    }
    echo "</ul>";
    
    // Check users table
    if (in_array('users', $tables)) {
        echo "<hr>";
        echo "<h3>Users Table Test:</h3>";
        
        // Show table structure
        $result = $mysqli->query("DESCRIBE users");
        echo "<p><strong>Table Structure:</strong></p>";
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($row['Field']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        $result = $mysqli->query("SELECT COUNT(*) as count FROM users");
        $row = $result->fetch_assoc();
        echo "<p>✅ Users table found with <strong>" . $row['count'] . "</strong> users</p>";
        
        // Show sample user (without password) - dynamically get columns
        $result = $mysqli->query("DESCRIBE users");
        $columns = [];
        while ($col = $result->fetch_assoc()) {
            if ($col['Field'] !== 'password') {
                $columns[] = $col['Field'];
            }
        }
        
        $columnsList = implode(', ', $columns);
        $result = $mysqli->query("SELECT $columnsList FROM users LIMIT 3");
        echo "<p><strong>Sample users:</strong></p>";
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr>";
        foreach ($columns as $col) {
            echo "<th>" . htmlspecialchars($col) . "</th>";
        }
        echo "</tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($columns as $col) {
                echo "<td>" . htmlspecialchars($row[$col] ?? 'N/A') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ Warning: 'users' table not found. Please create it first.</p>";
    }
    
    // Check posts table
    if (in_array('posts', $tables)) {
        echo "<hr>";
        echo "<h3>Posts Table Test:</h3>";
        
        $result = $mysqli->query("SELECT COUNT(*) as count FROM posts");
        $row = $result->fetch_assoc();
        echo "<p>✅ Posts table found with <strong>" . $row['count'] . "</strong> posts</p>";
    } else {
        echo "<hr>";
        echo "<h3>Posts Table:</h3>";
        echo "<p style='color: blue;'>ℹ️ Posts table will be auto-created on first login</p>";
    }
    
    // Check comments table
    if (in_array('comments', $tables)) {
        echo "<hr>";
        echo "<h3>Comments Table Test:</h3>";
        
        $result = $mysqli->query("SELECT COUNT(*) as count FROM comments");
        $row = $result->fetch_assoc();
        echo "<p>✅ Comments table found with <strong>" . $row['count'] . "</strong> comments</p>";
    } else {
        echo "<hr>";
        echo "<h3>Comments Table:</h3>";
        echo "<p style='color: blue;'>ℹ️ Comments table will be auto-created on first login</p>";
    }
    
    $mysqli->close();
    
    echo "<hr>";
    echo "<h3>Overall Status:</h3>";
    echo "<p style='color: green; font-size: 18px;'><strong>✅ ALL TESTS PASSED!</strong></p>";
    echo "<p>Your database is ready. You can now:</p>";
    echo "<ol>";
    echo "<li>Navigate to <a href='user/login.php'>user/login.php</a></li>";
    echo "<li>Login with credentials from your users table</li>";
    echo "<li>Access the dashboard</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>ERROR!</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    
    echo "<hr>";
    echo "<h3>Troubleshooting:</h3>";
    echo "<ol>";
    echo "<li>Make sure MySQL/MariaDB is running</li>";
    echo "<li>Check if database 'expoints_db' exists</li>";
    echo "<li>Verify database credentials are correct</li>";
    echo "<li>Check if you have proper permissions</li>";
    echo "</ol>";
    
    echo "<h4>To create the database:</h4>";
    echo "<pre>CREATE DATABASE IF NOT EXISTS expoints_db;</pre>";
}
?>

<hr>
<p><em>Test completed at <?php echo date('Y-m-d H:i:s'); ?></em></p>
