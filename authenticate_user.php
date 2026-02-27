<?php
require_once __DIR__ . '/config/supabase-session.php';
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

    if (!isset($data['email']) || !isset($data['password'])) {
        throw new Exception('Email and password are required');
    }

    $email = trim($data['email']);
    $password = $data['password'];

    if (empty($email) || empty($password)) {
        throw new Exception('Email and password cannot be empty');
    }

    // Authenticate user through Firestore
    $firestoreService = new FirestoreService();
    $result = $firestoreService->authenticateUser($email, $password);
    
    if ($result['success']) {
        $user = $result['user'];
        
        // Create PHP session for authenticated user
        $_SESSION['user_authenticated'] = true;
        $_SESSION['user_id'] = $user['uid'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['displayName'];
        $_SESSION['user_first_name'] = $user['firstName'] ?? '';
        $_SESSION['user_last_name'] = $user['lastName'] ?? '';
        $_SESSION['user_avatar'] = $user['avatar'] ?? 'cat1.jpg';
        $_SESSION['login_time'] = time();

        // Success response
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'uid' => $user['uid'],
                'name' => $user['displayName'],
                'email' => $user['email'],
                'avatar' => $user['avatar'] ?? 'cat1.jpg'
            ]
        ]);
    } else {
        throw new Exception($result['error']);
    }

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
