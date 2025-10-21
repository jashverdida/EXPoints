<?php
// Unhide all posts that are currently hidden

require_once 'config.php';

try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }
    
    $db->set_charset('utf8mb4');
    
    // Check if hidden column exists
    $result = $db->query("SHOW COLUMNS FROM posts LIKE 'hidden'");
    
    if ($result->num_rows > 0) {
        // Count hidden posts before
        $beforeCount = $db->query("SELECT COUNT(*) as count FROM posts WHERE hidden = 1")->fetch_assoc();
        
        // Unhide all posts
        $db->query("UPDATE posts SET hidden = 0 WHERE hidden = 1");
        
        // Count after
        $afterCount = $db->query("SELECT COUNT(*) as count FROM posts WHERE hidden = 1")->fetch_assoc();
        
        echo "<h2>✅ Success!</h2>";
        echo "<p>Unhidden <strong>" . $beforeCount['count'] . "</strong> posts.</p>";
        echo "<p>Hidden posts remaining: <strong>" . $afterCount['count'] . "</strong></p>";
        echo "<p><a href='check-hidden-posts.php'>Check status</a> | <a href='user/dashboard.php'>Go to Dashboard</a></p>";
        
    } else {
        echo "<h2>❌ Error</h2>";
        echo "<p>The posts table doesn't have a 'hidden' column.</p>";
    }
    
    $db->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
