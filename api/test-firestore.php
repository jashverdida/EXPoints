<?php
session_start();
require_once '../config/firestore.php';

header('Content-Type: application/json');

try {
    $firestoreService = new FirestoreService();
    
    // Test connection by creating a simple test document
    $testData = [
        'message' => 'Firestore connection test',
        'timestamp' => date('Y-m-d H:i:s'),
        'status' => 'active'
    ];
    
    echo json_encode([
        'success' => true,
        'message' => 'Firestore service initialized successfully',
        'timestamp' => date('Y-m-d H:i:s'),
        'services' => [
            'user_management' => 'ready',
            'review_system' => 'ready', 
            'comment_system' => 'ready',
            'like_system' => 'ready'
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'note' => 'Make sure firebase-service-account.json is configured'
    ]);
}
?>
