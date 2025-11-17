<?php
// Setup notifications table
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
    
    echo "<h2>Setting up Notifications Table</h2>";
    
    // Create notifications table
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type ENUM('like', 'comment', 'level_up') NOT NULL,
        message TEXT NOT NULL,
        post_id INT DEFAULT NULL,
        from_user_id INT DEFAULT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_is_read (is_read),
        INDEX idx_created_at (created_at),
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    if ($mysqli->query($createTableSQL)) {
        echo "<p style='color: green;'>✓ Notifications table created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating notifications table: " . $mysqli->error . "</p>";
    }
    
    echo "<h3>Setup Complete!</h3>";
    echo "<p>The notifications system is now ready to use.</p>";
    echo "<p><a href='user/dashboard.php'>Go to Dashboard</a></p>";
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
