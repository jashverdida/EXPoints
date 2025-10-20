<?php
require 'config/database.php';

$conn = new mysqli('localhost', 'root', '', 'expoints_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Setting up comment features...\n";

// Create comment_likes table
$sql = "CREATE TABLE IF NOT EXISTS comment_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    comment_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_comment_like (comment_id, user_id),
    FOREIGN KEY (comment_id) REFERENCES post_comments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_comment_id (comment_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql)) {
    echo "✅ comment_likes table created\n";
} else {
    echo "Note: " . $conn->error . "\n";
}

// Check and add parent_comment_id column
$result = $conn->query("SHOW COLUMNS FROM post_comments LIKE 'parent_comment_id'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE post_comments ADD COLUMN parent_comment_id INT DEFAULT NULL AFTER id";
    if ($conn->query($sql)) {
        echo "✅ Added parent_comment_id column\n";
    }
} else {
    echo "✓ parent_comment_id column already exists\n";
}

// Check and add like_count column
$result = $conn->query("SHOW COLUMNS FROM post_comments LIKE 'like_count'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE post_comments ADD COLUMN like_count INT DEFAULT 0 AFTER comment";
    if ($conn->query($sql)) {
        echo "✅ Added like_count column\n";
    }
} else {
    echo "✓ like_count column already exists\n";
}

// Check and add reply_count column
$result = $conn->query("SHOW COLUMNS FROM post_comments LIKE 'reply_count'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE post_comments ADD COLUMN reply_count INT DEFAULT 0 AFTER like_count";
    if ($conn->query($sql)) {
        echo "✅ Added reply_count column\n";
    }
} else {
    echo "✓ reply_count column already exists\n";
}

echo "\n✅ Database setup complete!\n";

$conn->close();
