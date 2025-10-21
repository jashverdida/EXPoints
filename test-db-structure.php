<?php
// Test database structure
$mysqli = new mysqli('127.0.0.1', 'root', '', 'expoints_db');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "=== CHECKING DATABASE STRUCTURE ===\n\n";

// Check posts table columns
echo "POSTS TABLE COLUMNS:\n";
$result = $mysqli->query("DESCRIBE posts");
while ($row = $result->fetch_assoc()) {
    echo "  - {$row['Field']} ({$row['Type']})\n";
}

echo "\n";

// Check users table columns
echo "USERS TABLE COLUMNS:\n";
$result = $mysqli->query("DESCRIBE users");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "  - {$row['Field']} ({$row['Type']})\n";
    }
} else {
    echo "  ERROR: " . $mysqli->error . "\n";
}

echo "\n";

// Check if user_info table exists
echo "USER_INFO TABLE:\n";
$result = $mysqli->query("SHOW TABLES LIKE 'user_info'");
if ($result->num_rows > 0) {
    echo "  EXISTS - Columns:\n";
    $result = $mysqli->query("DESCRIBE user_info");
    while ($row = $result->fetch_assoc()) {
        echo "    - {$row['Field']} ({$row['Type']})\n";
    }
} else {
    echo "  DOES NOT EXIST\n";
}

echo "\n";

// Try to get a sample post
echo "TESTING POST QUERY:\n";
$result = $mysqli->query("SELECT id, title, username FROM posts LIMIT 1");
if ($result && $result->num_rows > 0) {
    $post = $result->fetch_assoc();
    echo "  Sample post found: ID={$post['id']}, Title={$post['title']}, Username={$post['username']}\n";
    
    // Now try the JOIN query
    $post_id = $post['id'];
    echo "\n  Testing JOIN query for post ID $post_id:\n";
    
    $stmt = $mysqli->prepare("
        SELECT p.id, p.game, p.title, p.content, p.username, p.user_email, 
               p.likes as like_count, p.comments as comment_count, 
               p.created_at, p.updated_at,
               u.profile_picture, u.id as user_id
        FROM posts p
        LEFT JOIN users u ON p.username = u.username
        WHERE p.id = ?
    ");
    
    if (!$stmt) {
        echo "  PREPARE ERROR: " . $mysqli->error . "\n";
    } else {
        $stmt->bind_param("i", $post_id);
        if ($stmt->execute()) {
            $result2 = $stmt->get_result();
            if ($result2->num_rows > 0) {
                $data = $result2->fetch_assoc();
                echo "  SUCCESS! Retrieved data:\n";
                foreach ($data as $key => $value) {
                    echo "    $key: " . ($value ?? 'NULL') . "\n";
                }
            } else {
                echo "  No results returned\n";
            }
        } else {
            echo "  EXECUTE ERROR: " . $stmt->error . "\n";
        }
    }
} else {
    echo "  No posts found in database\n";
}

$mysqli->close();
echo "\n=== TEST COMPLETE ===\n";
?>
