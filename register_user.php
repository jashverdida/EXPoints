<?php
// Simple registration handler - just create PHP session and redirect
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

session_start();

// Clean any output buffer and set JSON header
ob_clean();
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    // Get the raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!isset($data['idToken']) || !isset($data['firstName']) || !isset($data['email'])) {
        throw new Exception('Missing required registration data');
    }

    $idToken = $data['idToken'];
    $firstName = $data['firstName'];
    $lastName = $data['lastName'] ?? '';
    $email = $data['email'];
    $uid = $data['uid'];

    // Create PHP session - Firebase Auth handles everything else automatically
    $_SESSION['user_authenticated'] = true;
    $_SESSION['user_id'] = $uid;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $firstName . ' ' . $lastName;
    $_SESSION['user_first_name'] = $firstName;
    $_SESSION['user_last_name'] = $lastName;
    $_SESSION['user_avatar'] = 'cat1.jpg';
    $_SESSION['firebase_token'] = $idToken;
    $_SESSION['auth_provider'] = 'firebase';
    $_SESSION['login_time'] = time();

    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful',
        'redirect' => 'dashboard.php'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    ob_clean();
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

ob_end_flush();
?>
