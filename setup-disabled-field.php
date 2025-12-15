<?php
// Add disabled fields to users table
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
    
    // Check if is_disabled column already exists
    $result = $db->query("SHOW COLUMNS FROM users LIKE 'is_disabled'");
    
    if ($result->num_rows > 0) {
        echo "is_disabled field already exists in users table.\n";
    } else {
        echo "Adding is_disabled field to users table...\n";
        
        // Add is_disabled field
        $sql = "ALTER TABLE users 
                ADD COLUMN is_disabled TINYINT(1) DEFAULT 0 AFTER role,
                ADD COLUMN disabled_reason TEXT DEFAULT NULL AFTER is_disabled,
                ADD COLUMN disabled_at DATETIME DEFAULT NULL AFTER disabled_reason,
                ADD COLUMN disabled_by VARCHAR(255) DEFAULT NULL AFTER disabled_at";
        
        if ($db->query($sql)) {
            echo "✓ Successfully added is_disabled, disabled_reason, disabled_at, and disabled_by fields!\n";
            
            // Update existing records
            $db->query("UPDATE users SET is_disabled = 0 WHERE is_disabled IS NULL");
            echo "✓ Updated existing records to is_disabled = 0\n";
        } else {
            throw new Exception("Error adding field: " . $db->error);
        }
    }
    
    // Show the updated structure
    echo "\nCurrent users table structure:\n";
    $result = $db->query("DESCRIBE users");
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
