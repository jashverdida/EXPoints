<?php
// Simple registration handler for testing JSON responses
ob_start();

// Disable error display to prevent HTML output
error_reporting(0);
ini_set('display_errors', 0);

// Set JSON headers immediately
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    session_start();

    // Get the raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!isset($data['idToken']) || !isset($data['firstName']) || !isset($data['email'])) {
        throw new Exception('Missing required registration data');
    }

    $idToken = $data['idToken'];
    $firstName = $data['firstName'];
    $lastName = $data['lastName'] ?? '';
    $middleName = $data['middleName'] ?? '';
    $suffix = $data['suffix'] ?? '';
    $email = $data['email'];
    $uid = $data['uid'];

    // For testing: just create session without Firestore
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

    // Clean output buffer and send success response
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful (Firestore disabled for testing)',
        'user_data' => [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'middleName' => $middleName,
            'suffix' => $suffix,
            'email' => $email,
            'uid' => $uid
        ],
        'redirect' => 'dashboard.php'
    ]);

} catch (Exception $e) {
    // Clean output buffer and send error response
    ob_clean();
    http_response_code(400);
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

ob_end_flush();
exit;
?>
