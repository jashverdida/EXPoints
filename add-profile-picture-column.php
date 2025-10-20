<?php
require 'config/database.php';

$conn = new mysqli('localhost', 'root', '', 'expoints_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if profile_picture column exists
$result = $conn->query("SHOW COLUMNS FROM user_info LIKE 'profile_picture'");

if ($result->num_rows == 0) {
    // Add the column
    $sql = "ALTER TABLE user_info ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL";
    if ($conn->query($sql) === TRUE) {
        echo "✅ Added profile_picture column to user_info table\n";
    } else {
        echo "❌ Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "✅ profile_picture column already exists\n";
}

$conn->close();
