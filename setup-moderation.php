<?php
// Setup moderation tables

$mysqli = new mysqli('127.0.0.1', 'root', '', 'expoints_db');

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

echo "Setting up moderation tables...\n\n";

// Add hidden column to posts table
$query = "ALTER TABLE posts ADD COLUMN IF NOT EXISTS hidden TINYINT(1) DEFAULT 0";
if ($mysqli->query($query)) {
    echo "✓ Added 'hidden' column to posts table\n";
} else {
    echo "Note: " . $mysqli->error . "\n";
}

// Add index for hidden column
$query = "ALTER TABLE posts ADD INDEX IF NOT EXISTS idx_hidden (hidden)";
if ($mysqli->query($query)) {
    echo "✓ Added index for hidden column\n";
} else {
    echo "Note: " . $mysqli->error . "\n";
}

// Create moderation_log table
$query = "CREATE TABLE IF NOT EXISTS moderation_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    moderator VARCHAR(100) NOT NULL,
    action VARCHAR(50) NOT NULL,
    reason TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_post_id (post_id),
    INDEX idx_moderator (moderator),
    INDEX idx_created_at (created_at)
)";

if ($mysqli->query($query)) {
    echo "✓ Created moderation_log table\n";
} else {
    echo "Error: " . $mysqli->error . "\n";
}

// Create ban_reviews table
$query = "CREATE TABLE IF NOT EXISTS ban_reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    post_id INT,
    flagged_by VARCHAR(100) NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reviewed_by VARCHAR(100),
    reviewed_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_status (status),
    INDEX idx_flagged_by (flagged_by),
    INDEX idx_created_at (created_at)
)";

if ($mysqli->query($query)) {
    echo "✓ Created ban_reviews table\n";
} else {
    echo "Error: " . $mysqli->error . "\n";
}

echo "\n✅ Moderation tables setup complete!\n";

$mysqli->close();
?>
