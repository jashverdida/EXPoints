<?php
// Debug simple query

require_once 'includes/db_helper.php';

echo "Testing simple SELECT...\n\n";

$db = getDBConnection();

$query = "SELECT id, title FROM posts ORDER BY created_at DESC";
echo "Query: $query\n\n";

$result = $db->query($query);

if ($result) {
    echo "✅ Query successful!\n";
    echo "Rows: " . $result->num_rows . "\n\n";
    
    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
        print_r($post);
    }
} else {
    echo "❌ Query failed\n";
}
