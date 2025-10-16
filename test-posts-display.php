<?php
// Test script to check posts in database
session_start();

$host = '127.0.0.1';
$dbname = 'expoints_db';
$username = 'root';
$password = '';

$mysqli = new mysqli($host, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "<h2>Posts Table Contents:</h2>";
$result = $mysqli->query("SELECT * FROM posts ORDER BY created_at DESC");

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Username</th><th>Game</th><th>Title</th><th>Content</th><th>Created</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . ($row['user_id'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['game']) . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . substr(htmlspecialchars($row['content']), 0, 50) . "...</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><strong>Total posts: " . $result->num_rows . "</strong></p>";
} else {
    echo "<p>No posts found in database.</p>";
}

echo "<hr><h2>Testing API (logged in as user):</h2>";

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p>Email: " . $_SESSION['user_email'] . "</p>";
    
    // Test the get_posts API
    $userId = $_SESSION['user_id'];
    $stmt = $mysqli->prepare("
        SELECT 
            p.id,
            p.game,
            p.title,
            p.content,
            p.username,
            p.user_id,
            p.created_at,
            (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as like_count,
            (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) as comment_count,
            (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id AND user_id = ?) as user_liked
        FROM posts p
        ORDER BY p.created_at DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $row['user_liked'] = (bool)$row['user_liked'];
        $row['is_owner'] = ($row['user_id'] === $userId);
        $posts[] = $row;
    }
    
    echo "<h3>API would return:</h3>";
    echo "<pre>" . json_encode(['success' => true, 'posts' => $posts], JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "<p style='color:red;'>Not logged in! Please log in first.</p>";
    echo "<p><a href='user/login.php'>Go to Login</a></p>";
}

$mysqli->close();
?>
