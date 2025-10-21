<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON header
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Check if user has admin role
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized - Admin access required']);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

// Validate input
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

// Database connection
$host = '127.0.0.1';
$dbname = 'expoints_db';
$username = 'root';
$db_password = '';

try {
    $db = new mysqli($host, $username, $db_password, $dbname);
    
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }
    
    $db->set_charset('utf8mb4');
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        $stmt->close();
        $db->close();
        exit;
    }
    $stmt->close();
    
    // Create moderator account (plain text password to match your existing system)
    $role = 'mod';
    $stmt = $db->prepare("INSERT INTO users (email, password, role, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $email, $password, $role);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create moderator: " . $stmt->error);
    }
    
    $new_user_id = $db->insert_id;
    $stmt->close();
    
    // Create user_info entry with default values
    $default_username = 'mod_' . $new_user_id;
    $stmt = $db->prepare("INSERT INTO user_info (user_id, username, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $new_user_id, $default_username);
    $stmt->execute();
    $stmt->close();
    
    // Log the action
    $admin = $_SESSION['username'] ?? 'Administrator';
    $stmt = $db->prepare("INSERT INTO moderation_log (moderator, action, reason, created_at) VALUES (?, 'create_moderator', ?, NOW())");
    $log_reason = "Created new moderator account: {$email}";
    $stmt->bind_param("ss", $admin, $log_reason);
    $stmt->execute();
    $stmt->close();
    
    $db->close();
    
    echo json_encode([
        'success' => true,
        'message' => "Moderator account created successfully! Email: {$email}"
    ]);
    
} catch (Exception $e) {
    error_log("Create moderator error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error creating moderator',
        'error' => $e->getMessage()
    ]);
    if (isset($db)) {
        $db->close();
    }
}
?>
