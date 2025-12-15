<?php
// Check if any posts are marked as hidden

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
        echo "<h2>Hidden column exists</h2>";
        
        // Get all posts with their hidden status
        $posts = $db->query("SELECT id, title, username, hidden FROM posts ORDER BY id");
        
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Title</th><th>Username</th><th>Hidden</th></tr>";
        
        while ($post = $posts->fetch_assoc()) {
            $hiddenStatus = $post['hidden'] ? 'YES (HIDDEN)' : 'No';
            $style = $post['hidden'] ? 'background-color: #ffcccc;' : '';
            echo "<tr style='$style'>";
            echo "<td>" . htmlspecialchars($post['id']) . "</td>";
            echo "<td>" . htmlspecialchars($post['title']) . "</td>";
            echo "<td>" . htmlspecialchars($post['username']) . "</td>";
            echo "<td><strong>" . $hiddenStatus . "</strong></td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Count hidden posts
        $hiddenCount = $db->query("SELECT COUNT(*) as count FROM posts WHERE hidden = 1")->fetch_assoc();
        echo "<p><strong>Total hidden posts: " . $hiddenCount['count'] . "</strong></p>";
        
        // Provide fix option
        echo "<hr>";
        echo "<h3>Fix Hidden Posts</h3>";
        echo "<p>If you want to unhide all posts, <a href='unhide-all-posts.php'>click here</a></p>";
        
    } else {
        echo "<h2>Hidden column does NOT exist</h2>";
        echo "<p>The posts table doesn't have a 'hidden' column yet.</p>";
    }
    
    $db->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
