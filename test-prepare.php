<?php
// Test prepare method

require_once 'vendor/autoload.php';

use EXPoints\Database\Connection;

try {
    $conn = Connection::getInstance()->getConnection();
    echo "Connection class: " . get_class($conn) . "\n";
    
    if (method_exists($conn, 'prepare')) {
        echo "✅ prepare() method exists\n";
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        echo "✅ prepare() executed successfully\n";
        echo "Statement class: " . get_class($stmt) . "\n";
    } else {
        echo "❌ prepare() method NOT found\n";
        echo "Available methods:\n";
        print_r(get_class_methods($conn));
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
