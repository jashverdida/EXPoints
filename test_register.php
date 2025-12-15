<?php
// Simple diagnostic version of register_user.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP Script Started\n";

try {
    echo "Testing basic functionality\n";
    
    // Test if file exists
    if (file_exists('config/firestore.php')) {
        echo "✅ firestore.php exists\n";
    } else {
        echo "❌ firestore.php NOT found\n";
    }
    
    // Test if we can include the file
    require_once 'config/firestore.php';
    echo "✅ firestore.php included successfully\n";
    
    // Test if class exists
    if (class_exists('FirestoreService')) {
        echo "✅ FirestoreService class exists\n";
    } else {
        echo "❌ FirestoreService class NOT found\n";
    }
    
    // Test if we can create instance
    $service = new FirestoreService();
    echo "✅ FirestoreService instance created\n";
    
    // Test JSON output
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'All tests passed']);
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "❌ Line: " . $e->getLine() . "\n";
    echo "❌ File: " . $e->getFile() . "\n";
}
?>
