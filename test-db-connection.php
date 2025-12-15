<?php
require_once 'includes/db_helper.php';

echo "Testing database connection...\n";

$db = getDBConnection();

if ($db) {
    echo "✅ Connection successful!\n";
    echo "Database type: " . get_class($db) . "\n";
} else {
    echo "❌ Connection failed!\n";
}

