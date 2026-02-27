<?php
require_once __DIR__ . '/config/supabase-session.php';
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON header
header('Content-Type: application/json');

// Supabase database connection
require_once __DIR__ . '/includes/db_helper.php';

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid request data']);
    exit();
}

// Extract data
$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');
$firstName = trim($data['firstName'] ?? '');
$middleName = trim($data['middleName'] ?? '');
$lastName = trim($data['lastName'] ?? '');
$suffix = trim($data['suffix'] ?? '');
$username = trim($data['username'] ?? '');

// Validate required fields
if (empty($email) || empty($password) || empty($firstName) || empty($lastName) || empty($username)) {
    echo json_encode(['success' => false, 'error' => 'All required fields must be filled']);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email format']);
    exit();
}

// Validate password length
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters']);
    exit();
}

// Validate username (alphanumeric, underscore, 3-20 characters)
if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    echo json_encode(['success' => false, 'error' => 'Username must be 3-20 characters (letters, numbers, underscore only)']);
    exit();
}

// Get database connection
$db = getDBConnection();

if (!$db) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

try {
    // Check if email already exists
    $checkEmailStmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailResult = $checkEmailStmt->get_result();
    
    if ($checkEmailResult && $checkEmailResult->num_rows() > 0) {
        echo json_encode(['success' => false, 'error' => 'Email already registered']);
        exit();
    }
    
    // Check if username already exists
    $checkUsernameStmt = $db->prepare("SELECT id FROM user_info WHERE username = ?");
    $checkUsernameStmt->bind_param("s", $username);
    $checkUsernameStmt->execute();
    $checkUsernameResult = $checkUsernameStmt->get_result();
    
    if ($checkUsernameResult && $checkUsernameResult->num_rows() > 0) {
        echo json_encode(['success' => false, 'error' => 'Username already taken']);
        exit();
    }
    
    // Insert into users table (role defaults to 'user')
    $insertUserStmt = $db->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'user')");
    $insertUserStmt->bind_param("ss", $email, $password);
    
    if (!$insertUserStmt->execute()) {
        throw new Exception("Failed to create user account");
    }
    
    $userId = $insertUserStmt->insert_id;
    
    // Insert into user_info table
    $insertInfoStmt = $db->prepare("INSERT INTO user_info (user_id, username, first_name, middle_name, last_name, suffix, exp_points) VALUES (?, ?, ?, ?, ?, ?, 0)");
    $insertInfoStmt->bind_param("isssss", $userId, $username, $firstName, $middleName, $lastName, $suffix);
    
    if (!$insertInfoStmt->execute()) {
        throw new Exception("Failed to create user profile");
    }
    
    // Set session variables for the new user
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['username'] = $username;
    $_SESSION['user_role'] = 'user';
    $_SESSION['authenticated'] = true;
    $_SESSION['login_time'] = time();
    
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful',
        'user_id' => $userId,
        'username' => $username
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $db->rollback();
    $db->close();
    
    error_log("Registration error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
