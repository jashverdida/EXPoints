<?php
// Start session
require_once __DIR__ . '/../config/session.php';
startSecureSession();

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

$user_id = intval($data['user_id'] ?? 0);

// Validate input
if ($user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

$db = getDBConnection();
if (!$db) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    
    // Get admin username
    $admin = $_SESSION['username'] ?? 'Administrator';
    
    // Check if user is a moderator
    $stmt = $db->prepare("SELECT email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        $db->close();
        exit;
    }
    
    if ($user['role'] !== 'mod') {
        echo json_encode(['success' => false, 'message' => 'User is not a moderator']);
        $db->close();
        exit;
    }
    
    // Delete from user_info first (foreign key)
    $stmt = $db->prepare("DELETE FROM user_info WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Delete the moderator account
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete moderator: " . $stmt->error);
    }
    $stmt->close();
    
    // Log the action
    $stmt = $db->prepare("INSERT INTO moderation_log (moderator, action, reason, created_at) VALUES (?, 'delete_moderator', ?, NOW())");
    $log_reason = "Deleted moderator account: {$user['email']}";
    $stmt->bind_param("ss", $admin, $log_reason);
    $stmt->execute();
    $stmt->close();
    
    $db->close();
    
    echo json_encode([
        'success' => true,
        'message' => "Moderator {$user['email']} has been deleted successfully."
    ]);
    
} catch (Exception $e) {
    error_log("Delete moderator error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting moderator',
        'error' => $e->getMessage()
    ]);
    if (isset($db)) {
        $db->close();
    }
}
?>
