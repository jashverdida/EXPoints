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

// Check if user has admin role (only admins can unban)
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
$username = $data['username'] ?? '';

// Validate input
if ($user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

if (empty($username)) {
    echo json_encode(['success' => false, 'message' => 'Username is required']);
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
    
    // Check if user is actually banned
    $stmt = $db->prepare("SELECT is_banned FROM user_info WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_info = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user_info) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        $db->close();
        exit;
    }
    
    if ($user_info['is_banned'] != 1) {
        echo json_encode(['success' => false, 'message' => 'User is not banned']);
        $db->close();
        exit;
    }
    
    // Unban the user - reset all ban fields
    $stmt = $db->prepare("UPDATE user_info SET is_banned = 0, ban_reason = NULL, banned_at = NULL, banned_by = NULL WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to unban user: " . $stmt->error);
    }
    
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    
    if ($affected_rows > 0) {
        // Log the moderation action
        $stmt = $db->prepare("INSERT INTO moderation_log (moderator, action, reason, created_at) VALUES (?, 'unban_user', ?, NOW())");
        $log_reason = "User @{$username} unbanned by admin";
        $stmt->bind_param("ss", $admin, $log_reason);
        $stmt->execute();
        $stmt->close();
    }
    
    $db->close();
    
    echo json_encode([
        'success' => true,
        'message' => "User @{$username} has been unbanned successfully. They can now log in again."
    ]);
    
} catch (Exception $e) {
    error_log("Unban user error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error unbanning user',
        'error' => $e->getMessage()
    ]);
    if (isset($db)) {
        $db->close();
    }
}
?>
