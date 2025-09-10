<?php
session_start();
require_once '../config/firestore.php';

header('Content-Type: application/json');

try {
    $firestoreService = new FirestoreService();
    
    echo "<h2>🔥 EXPoints Firestore Database Test</h2>";
    
    // Test 1: Create a sample user
    echo "<h3>Creating Sample User...</h3>";
    $userResult = $firestoreService->createUser('test-user-123', [
        'email' => 'testuser@example.com',
        'displayName' => 'Gaming Master',
        'avatar' => 'cat1.jpg'
    ]);
    
    if ($userResult['success']) {
        echo "✅ User created successfully<br>";
    } else {
        echo "❌ User creation failed: " . $userResult['error'] . "<br>";
    }
    
    // Test 2: Create a sample review
    echo "<h3>Creating Sample Review...</h3>";
    $reviewResult = $firestoreService->createReview([
        'userId' => 'test-user-123',
        'gameTitle' => 'The Legend of Zelda: Breath of the Wild',
        'rating' => 5,
        'content' => 'Amazing open-world adventure! The exploration and freedom of gameplay is unmatched.',
        'platform' => 'Nintendo Switch'
    ]);
    
    if ($reviewResult['success']) {
        echo "✅ Review created successfully (ID: " . $reviewResult['reviewId'] . ")<br>";
        
        // Test 3: Add a comment to the review
        echo "<h3>Adding Sample Comment...</h3>";
        $commentResult = $firestoreService->addComment($reviewResult['reviewId'], 'test-user-123', 'I totally agree! This game is a masterpiece.');
        
        if ($commentResult['success']) {
            echo "✅ Comment added successfully<br>";
        } else {
            echo "❌ Comment creation failed: " . $commentResult['error'] . "<br>";
        }
    } else {
        echo "❌ Review creation failed: " . $reviewResult['error'] . "<br>";
    }
    
    // Test 4: Fetch reviews to verify
    echo "<h3>Fetching Reviews...</h3>";
    $reviewsResult = $firestoreService->getReviews(5);
    
    if ($reviewsResult['success']) {
        echo "✅ Found " . count($reviewsResult['data']) . " reviews<br>";
        foreach ($reviewsResult['data'] as $review) {
            echo "📝 Review: " . htmlspecialchars($review['gameTitle']) . " (Rating: " . $review['rating'] . "/5)<br>";
        }
    } else {
        echo "❌ Failed to fetch reviews: " . $reviewsResult['error'] . "<br>";
    }
    
    echo "<hr>";
    echo "<p><strong>🎯 Now check your Firebase Console Firestore Database!</strong></p>";
    echo "<p>You should see collections: <code>users</code>, <code>reviews</code>, <code>comments</code></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Connection Error</h3>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Make sure:</strong></p>";
    echo "<ul>";
    echo "<li>Firebase service account key is in the right location</li>";
    echo "<li>Firestore database is created in Firebase Console</li>";
    echo "<li>Project ID matches in service account JSON</li>";
    echo "</ul>";
}
?>
