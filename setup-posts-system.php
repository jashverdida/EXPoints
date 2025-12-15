<?php
// Setup script to create necessary database tables
// Run this file in your browser: http://localhost:8000/setup-posts-system.php

// Database connection
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
    
    echo "<h2>Setting up posts system tables...</h2>";
    echo "<pre>";
    
    // 1. Create post_likes table
    echo "\n1. Creating post_likes table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `post_likes` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `post_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_like` (`post_id`, `user_id`),
      KEY `post_id` (`post_id`),
      KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($mysqli->query($sql)) {
        echo "✓ post_likes table created successfully\n";
    } else {
        echo "✗ Error creating post_likes: " . $mysqli->error . "\n";
    }
    
    // 2. Create post_comments table
    echo "\n2. Creating post_comments table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `post_comments` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `post_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `username` varchar(50) DEFAULT NULL,
      `comment` text NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `post_id` (`post_id`),
      KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($mysqli->query($sql)) {
        echo "✓ post_comments table created successfully\n";
    } else {
        echo "✗ Error creating post_comments: " . $mysqli->error . "\n";
    }
    
    // 3. Create post_bookmarks table
    echo "\n3. Creating post_bookmarks table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `post_bookmarks` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `post_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_bookmark` (`post_id`, `user_id`),
      KEY `post_id` (`post_id`),
      KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($mysqli->query($sql)) {
        echo "✓ post_bookmarks table created successfully\n";
    } else {
        echo "✗ Error creating post_bookmarks: " . $mysqli->error . "\n";
    }
    
    // Verify tables exist
    echo "\n4. Verifying tables...\n";
    $tables = ['post_likes', 'post_comments', 'post_bookmarks'];
    foreach ($tables as $table) {
        $result = $mysqli->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "✓ $table exists\n";
        } else {
            echo "✗ $table does NOT exist\n";
        }
    }
    
    // Check if there's any existing data
    echo "\n5. Checking existing data...\n";
    $result = $mysqli->query("SELECT COUNT(*) as count FROM post_likes");
    $row = $result->fetch_assoc();
    echo "- post_likes: {$row['count']} records\n";
    
    $result = $mysqli->query("SELECT COUNT(*) as count FROM post_comments");
    $row = $result->fetch_assoc();
    echo "- post_comments: {$row['count']} records\n";
    
    $result = $mysqli->query("SELECT COUNT(*) as count FROM post_bookmarks");
    $row = $result->fetch_assoc();
    echo "- post_bookmarks: {$row['count']} records\n";
    
    echo "\n✅ Setup completed successfully!\n";
    echo "\nYou can now:\n";
    echo "1. Like posts (likes will be saved to database)\n";
    echo "2. Comment on posts (comments will be saved to database)\n";
    echo "3. Bookmark posts (bookmarks will be saved to database)\n";
    echo "4. Visit bookmarks page to see your saved posts\n";
    
    echo "</pre>";
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<pre>";
    echo "Error: " . $e->getMessage();
    echo "</pre>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Posts System Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        pre {
            background: #2d2d2d;
            color: #f0f0f0;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
        }
        h2 {
            color: #333;
        }
    </style>
</head>
<body>
    <p><a href="user/dashboard.php">← Back to Dashboard</a></p>
</body>
</html>
