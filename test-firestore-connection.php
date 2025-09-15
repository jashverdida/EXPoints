<?php
// Test Firestore Connection and User Creation
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Firestore Connection Test</h1>";

try {
    // Check if service account exists
    if (!file_exists('config/firebase-service-account.json')) {
        throw new Exception('Firebase service account file not found. Please run setup-firebase.php first.');
    }

    // Load FirestoreService
    require_once 'config/firestore.php';
    
    if (!class_exists('FirestoreService')) {
        throw new Exception('FirestoreService class not found');
    }

    echo "<p style='color: green;'>✅ FirestoreService class loaded</p>";

    // Create instance
    $firestore = new FirestoreService();
    echo "<p style='color: green;'>✅ FirestoreService instance created</p>";

    // Test user creation with sample data
    $testUserData = [
        'email' => 'test@example.com',
        'firstName' => 'Test',
        'lastName' => 'User',
        'middleName' => 'M',
        'suffix' => 'Jr',
        'uid' => 'test-uid-' . time(),
        'createdAt' => new DateTime(),
        'updatedAt' => new DateTime()
    ];

    echo "<p>Testing user creation with sample data...</p>";
    
    $result = $firestore->createUser($testUserData['uid'], $testUserData);
    
    if ($result['success']) {
        echo "<p style='color: green;'>✅ Test user created successfully in Firestore</p>";
        echo "<p>User ID: " . htmlspecialchars($result['uid']) . "</p>";
        echo "<p>Method: " . htmlspecialchars($result['method']) . "</p>";
        
        // Test retrieving the user
        echo "<p>Testing user retrieval...</p>";
        $userResult = $firestore->getUser($testUserData['uid']);
        
        if ($userResult['success']) {
            echo "<p style='color: green;'>✅ User retrieved successfully from Firestore</p>";
            echo "<pre>" . htmlspecialchars(json_encode($userResult['data'], JSON_PRETTY_PRINT)) . "</pre>";
        } else {
            echo "<p style='color: orange;'>⚠️ User retrieval failed: " . htmlspecialchars($userResult['error']) . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Test user creation failed: " . htmlspecialchars($result['error']) . "</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>File: " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>If all tests pass, your Firestore is properly configured</li>";
echo "<li>Try registering a new user through the registration form</li>";
echo "<li>Check your Firestore console to see the new user document</li>";
echo "</ol>";

echo "<p><a href='setup-firebase.php'>← Back to Setup</a> | <a href='register.php'>→ Test Registration</a></p>";
?>
