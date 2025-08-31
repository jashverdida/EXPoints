<?php
// Simple API test script to verify all endpoints are working

echo "<h1>EXPoints API Test</h1>";

// Test 1: Check if Firestore service can be loaded
echo "<h2>Test 1: Firestore Service Loading</h2>";
try {
    require_once 'config/firestore.php';
    $firestore = new FirestoreService();
    echo "✅ FirestoreService loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Error loading FirestoreService: " . $e->getMessage() . "<br>";
}

// Test 2: Check API endpoints accessibility
echo "<h2>Test 2: API Endpoints</h2>";

$endpoints = [
    'users' => 'api/users.php',
    'reviews' => 'api/reviews.php', 
    'comments' => 'api/comments.php?reviewId=test'
];

foreach ($endpoints as $name => $endpoint) {
    $url = "http://localhost/EXPoints/$endpoint";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "✅ $name endpoint responding with valid JSON<br>";
        } else {
            echo "⚠️ $name endpoint responding but invalid JSON<br>";
        }
    } else {
        echo "❌ $name endpoint not accessible<br>";
    }
}

// Test 3: Check Firebase config
echo "<h2>Test 3: Firebase Configuration</h2>";
if (file_exists('config/firebase-service-account.json')) {
    echo "✅ Firebase service account file exists<br>";
    
    $config = json_decode(file_get_contents('config/firebase-service-account.json'), true);
    if ($config && isset($config['project_id'])) {
        echo "✅ Firebase project ID: " . $config['project_id'] . "<br>";
    } else {
        echo "❌ Invalid Firebase service account file<br>";
    }
} else {
    echo "❌ Firebase service account file missing<br>";
}

echo "<h2>Test 4: Registration Page</h2>";
echo "<a href='register.html' target='_blank'>🔗 Test Registration</a><br>";

echo "<h2>Test 5: Dashboard</h2>";
echo "<a href='dashboard.php' target='_blank'>🔗 Test Dashboard</a><br>";

echo "<br><strong>All core components have been updated and integrated. The system is ready for testing!</strong>";
?>
