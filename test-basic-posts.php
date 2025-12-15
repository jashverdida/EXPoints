<?php
// Test ultra-simple query

require_once 'includes/db_helper.php';

echo "Testing ultra-simple query...\n\n";

$db = getDBConnection();

// Most basic query possible
$query = "SELECT * FROM posts ORDER BY created_at DESC";

echo "Query:\n$query\n\n";

$result = $db->query($query);

if ($result) {
    echo "✅ Query successful!\n";
    echo "Rows: " . $result->num_rows . "\n\n";
    
    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
        echo "First post:\n";
        print_r($post);
    }
} else {
    echo "❌ Query failed\n";
}
