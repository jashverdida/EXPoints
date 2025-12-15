<?php
// Debug Supabase COUNT query

require_once 'includes/db_helper.php';

echo "Testing COUNT query...\n\n";

$db = getDBConnection();

// Enable error reporting
error_reporting(E_ALL);

// Try simple count
$result = $db->query("SELECT id FROM posts LIMIT 1");
echo "Simple query result: " . ($result ? "Success" : "Failed") . "\n";
echo "Number of rows: " . $result->num_rows . "\n\n";

// Try using Supabase count in URL directly
echo "Now let's see if we can just count rows...\n";
$result = $db->query("SELECT * FROM posts");
echo "Total posts found: " . $result->num_rows . "\n";
