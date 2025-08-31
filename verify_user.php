<?php
// Prevent any output before JSON response
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once 'config/firestore.php';

// Clean any output buffer and set JSON headers
ob_clean();
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    // Get the raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!isset($data['idToken']) || !isset($data['email']) || !isset($data['uid'])) {
        throw new Exception('Missing required verification data');
    }

    $idToken = $data['idToken'];
    $email = $data['email'];
    $uid = $data['uid'];

    // Verify user exists in Firestore users collection
    $firestoreService = new FirestoreService();
    $userResult = $firestoreService->getUser($uid);
    
    if (!$userResult['success']) {
        throw new Exception('User not found in Firestore database');
    }
    
    $userData = $userResult['data'];
    
    // Verify email matches
    if ($userData['email'] !== $email) {
        throw new Exception('Email mismatch between Firebase and Firestore');
    }

    // Create synchronized PHP session
    $_SESSION['user_authenticated'] = true;
    $_SESSION['user_id'] = $uid;
    $_SESSION['user_email'] = $userData['email'];
    $_SESSION['user_name'] = $userData['displayName'];
    $_SESSION['user_first_name'] = $userData['firstName'] ?? '';
    $_SESSION['user_last_name'] = $userData['lastName'] ?? '';
    $_SESSION['user_avatar'] = $userData['avatar'] ?? 'cat1.jpg';
    $_SESSION['firebase_token'] = $idToken;
    $_SESSION['auth_provider'] = 'firebase';
    $_SESSION['login_time'] = time();

    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Login verified - Firebase Auth synchronized with Firestore',
        'user' => [
            'uid' => $uid,
            'name' => $userData['displayName'],
            'email' => $userData['email'],
            'avatar' => $userData['avatar'] ?? 'cat1.jpg',
            'authProvider' => 'firebase',
            'firestoreSync' => true
        ]
    ]);

} catch (Exception $e) {
    // Error response
    http_response_code(400);
    
    // Clean output buffer before JSON response
    ob_clean();
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// Flush clean output
ob_end_flush();
?>
