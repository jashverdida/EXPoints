<?php
/**
 * Update All Users EXP
 * Run this script to recalculate and update EXP for all users based on their likes
 */

require_once 'includes/ExpSystem.php';

// Database connection
$host = '127.0.0.1';
$dbname = 'expoints_db';
$username = 'root';
$password = '';

try {
    $db = new mysqli($host, $username, $password, $dbname);
    
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }
    
    $db->set_charset('utf8mb4');
    
    echo "=== EXP UPDATE SYSTEM ===\n\n";
    echo "Updating EXP for all users based on likes received...\n\n";
    
    // Get all users
    $query = "SELECT user_id, username FROM user_info ORDER BY user_id";
    $result = $db->query($query);
    
    $updated = 0;
    $results = [];
    
    while ($row = $result->fetch_assoc()) {
        $userId = $row['user_id'];
        $username = $row['username'];
        
        // Update EXP
        $stats = ExpSystem::updateUserExp($db, $userId);
        
        $results[] = [
            'user_id' => $userId,
            'username' => $username,
            'exp' => $stats['exp'],
            'level' => $stats['level'],
            'likes' => $stats['likes']
        ];
        
        $updated++;
    }
    
    // Display results in a table
    echo "╔════════╦══════════════════════╦═════════╦═══════╦═══════╗\n";
    echo "║ ID     ║ Username             ║ Likes   ║ EXP   ║ Level ║\n";
    echo "╠════════╬══════════════════════╬═════════╬═══════╬═══════╣\n";
    
    foreach ($results as $result) {
        printf("║ %-6s ║ %-20s ║ %-7s ║ %-5s ║ %-5s ║\n",
            $result['user_id'],
            substr($result['username'], 0, 20),
            $result['likes'],
            $result['exp'],
            $result['level']
        );
    }
    
    echo "╚════════╩══════════════════════╩═════════╩═══════╩═══════╝\n\n";
    
    echo "✅ Successfully updated EXP for $updated users!\n\n";
    
    echo "EXP Formula:\n";
    echo "  • 1 like = 5 EXP (on posts or comments)\n";
    echo "  • Level 1 → 2: Requires 1 EXP\n";
    echo "  • Level 2+: Requires 10 EXP per level\n\n";
    
    $db->close();
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
