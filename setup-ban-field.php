<?php
// Add banned fields to user_info table
$host = '127.0.0.1';
$dbname = 'expoints_db';
$username = 'root';
$password = '';

try {
    $db = new mysqli($host, $username, $password, $dbname);
    
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }
    
    echo "Connected to database successfully.\n\n";
    
    // Check if is_banned column already exists
    $result = $db->query("SHOW COLUMNS FROM user_info LIKE 'is_banned'");
    
    if ($result->num_rows > 0) {
        echo "is_banned field already exists in user_info table.\n";
    } else {
        echo "Adding is_banned field to user_info table...\n";
        
        // Add is_banned field
        $sql = "ALTER TABLE user_info 
                ADD COLUMN is_banned TINYINT(1) DEFAULT 0 AFTER exp_points,
                ADD COLUMN ban_reason TEXT DEFAULT NULL AFTER is_banned,
                ADD COLUMN banned_at DATETIME DEFAULT NULL AFTER ban_reason,
                ADD COLUMN banned_by VARCHAR(255) DEFAULT NULL AFTER banned_at";
        
        if ($db->query($sql)) {
            echo "✓ Successfully added is_banned, ban_reason, banned_at, and banned_by fields!\n";
            
            // Update existing records
            $db->query("UPDATE user_info SET is_banned = 0 WHERE is_banned IS NULL");
            echo "✓ Updated existing records to is_banned = 0\n";
        } else {
            throw new Exception("Error adding field: " . $db->error);
        }
    }
    
    // Show the updated structure
    echo "\nCurrent user_info table structure:\n";
    $result = $db->query("DESCRIBE user_info");
    while ($row = $result->fetch_assoc()) {
        echo "  - {$row['Field']} ({$row['Type']})\n";
    }
    
    $db->close();
    echo "\n✓ Database setup complete!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
