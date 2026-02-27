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
$action = $data['action'] ?? '';
$reason = trim($data['reason'] ?? '');

// Validate input
if ($user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

if (!in_array($action, ['disable', 'enable'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

if ($action === 'disable' && empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'Reason is required to disable a moderator']);
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
    $stmt = $db->prepare("SELECT email, role, is_disabled FROM users WHERE id = ?");
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
    
    if ($action === 'disable') {
        if ($user['is_disabled'] == 1) {
            echo json_encode(['success' => false, 'message' => 'Moderator is already disabled']);
            $db->close();
            exit;
        }
        
        // Disable the moderator
        $stmt = $db->prepare("UPDATE users SET is_disabled = 1, disabled_reason = ?, disabled_at = NOW(), disabled_by = ? WHERE id = ?");
        $stmt->bind_param("ssi", $reason, $admin, $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to disable moderator: " . $stmt->error);
        }
        $stmt->close();
        
        // Log the action
        $stmt = $db->prepare("INSERT INTO moderation_log (moderator, action, reason, created_at) VALUES (?, 'disable_moderator', ?, NOW())");
        $log_reason = "Disabled moderator {$user['email']}: {$reason}";
        $stmt->bind_param("ss", $admin, $log_reason);
        $stmt->execute();
        $stmt->close();
        
        $message = "Moderator {$user['email']} has been disabled successfully.";
        
    } else { // enable
        if ($user['is_disabled'] != 1) {
            echo json_encode(['success' => false, 'message' => 'Moderator is not disabled']);
            $db->close();
            exit;
        }
        
        // Enable the moderator
        $stmt = $db->prepare("UPDATE users SET is_disabled = 0, disabled_reason = NULL, disabled_at = NULL, disabled_by = NULL WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to enable moderator: " . $stmt->error);
        }
        $stmt->close();
        
        // Log the action
        $stmt = $db->prepare("INSERT INTO moderation_log (moderator, action, reason, created_at) VALUES (?, 'enable_moderator', ?, NOW())");
        $log_reason = "Enabled moderator {$user['email']}";
        $stmt->bind_param("ss", $admin, $log_reason);
        $stmt->execute();
        $stmt->close();
        
        $message = "Moderator {$user['email']} has been enabled successfully.";
    }
    
    $db->close();
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    error_log("Toggle moderator error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error updating moderator status',
        'error' => $e->getMessage()
    ]);
    if (isset($db)) {
        $db->close();
    }
}
?>
