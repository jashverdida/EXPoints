<?php
/**
 * Quick Check - Verify user roles in Supabase
 */

require_once __DIR__ . '/includes/db_helper.php';

$db = getDBConnection();

if (!$db) {
    die("❌ Database connection failed\n");
}

echo "Checking user roles in Supabase...\n\n";

$result = $db->query("SELECT id, email, role FROM users ORDER BY id");

if ($result && $result->num_rows > 0) {
    echo "ID  | Email                    | Role\n";
    echo str_repeat("-", 60) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        printf("%-3d | %-24s | %s\n", 
            $row['id'], 
            $row['email'], 
            $row['role'] ?? 'NULL'
        );
    }
} else {
    echo "No users found!\n";
}

$db->close();
?>
