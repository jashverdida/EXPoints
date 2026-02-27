<?php
require_once __DIR__ . '/config/supabase-session.php';
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Enable CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Get the raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!isset($data['idToken'])) {
        throw new Exception('ID Token is required');
    }

    $idToken = $data['idToken'];

    // TODO: Verify the Firebase ID token with Firebase Admin SDK
    // For now, we'll do basic validation and create a session
    
    // Basic validation (you should replace this with proper Firebase verification)
    if (strlen($idToken) < 10) {
        throw new Exception('Invalid token format');
    }

    // For demonstration, we'll extract email from the token (this is not secure)
    // In production, use Firebase Admin SDK to verify and decode the token
    
    // Create session for authenticated user
    $_SESSION['user_authenticated'] = true;
    $_SESSION['firebase_token'] = $idToken;
    $_SESSION['login_time'] = time();
    
    // Try to get user info (basic approach)
    if (isset($data['email'])) {
        $_SESSION['user_email'] = $data['email'];
    }
    if (isset($data['uid'])) {
        $_SESSION['user_id'] = $data['uid'];
    }

    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Authentication successful',
        'redirect' => 'dashboard.php'
    ]);

} catch (Exception $e) {
    // Error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
