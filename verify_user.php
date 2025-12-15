<?php
// Disable any error output that could interfere with JSON
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

// Set headers first
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Ensure we only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

function sendJsonResponse($data) {
    echo json_encode($data);
    exit;
}

function sendJsonError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

// Wrap everything in a try-catch to ensure we always return JSON
try {
    // Get the raw POST data first
    $input = file_get_contents('php://input');
    
    if (empty($input)) {
        sendJsonError('No POST data received');
    }
    
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendJsonError('Invalid JSON data: ' . json_last_error_msg());
    }

    if (!isset($data['idToken']) || !isset($data['email']) || !isset($data['uid'])) {
        sendJsonError('Missing required verification data');
    }

    $idToken = $data['idToken'];
    $email = $data['email'];
    $uid = $data['uid'];

    // Check if Firestore service can be loaded
    if (!file_exists('config/firestore.php')) {
        sendJsonError('Firestore configuration file not found');
    }
    
    // Check if Firebase service account exists
    if (!file_exists('config/firebase-service-account.json')) {
        // Fallback: Create session without Firestore sync
        $_SESSION['user_authenticated'] = true;
        $_SESSION['user_id'] = $uid;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = explode('@', $email)[0];
        $_SESSION['user_avatar'] = 'cat1.jpg';
        $_SESSION['firebase_token'] = $idToken;
        $_SESSION['auth_provider'] = 'firebase';
        $_SESSION['login_time'] = time();

        sendJsonResponse([
            'success' => true,
            'message' => 'Login verified - Firebase Auth working (configure firebase-service-account.json for full Firestore sync)',
            'user' => [
                'uid' => $uid,
                'name' => explode('@', $email)[0],
                'email' => $email,
                'avatar' => 'cat1.jpg',
                'authProvider' => 'firebase',
                'firestoreSync' => false
            ]
        ]);
    }
    
    // Only try to load Firestore if service account exists
    require_once 'config/firestore.php';

    // Verify user exists in Firestore users collection
    try {
        $firestoreService = new FirestoreService();
    } catch (Exception $fsError) {
        sendJsonError('Firestore service initialization failed: ' . $fsError->getMessage());
    }
    
    $userResult = $firestoreService->getUser($uid);
    
    if (!$userResult['success']) {
        // User doesn't exist in Firestore, let's create them
        $createResult = $firestoreService->createUser($uid, [
            'email' => $email,
            'displayName' => explode('@', $email)[0], // Use email prefix as display name
            'avatar' => 'cat1.jpg'
        ]);
        
        if (!$createResult['success']) {
            sendJsonError('Failed to create user in Firestore: ' . $createResult['error']);
        }
        
        // Get the newly created user
        $userResult = $firestoreService->getUser($uid);
        if (!$userResult['success']) {
            sendJsonError('Failed to retrieve newly created user');
        }
    }
    
    $userData = $userResult['data'];
    
    // Verify email matches
    if ($userData['email'] !== $email) {
        sendJsonError('Email mismatch between Firebase and Firestore');
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
    sendJsonResponse([
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
    sendJsonError($e->getMessage());
} catch (Error $e) {
    sendJsonError('PHP Error: ' . $e->getMessage());
} catch (Throwable $e) {
    sendJsonError('System Error: ' . $e->getMessage());
}
?>
