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

// Check if user has mod or admin role
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['mod', 'admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized - Admin or Moderator access required']);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$review_id = intval($data['review_id'] ?? 0);
$action = $data['action'] ?? '';

// Validate input
if ($review_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid review ID']);
    exit;
}

if (!in_array($action, ['approved', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// Database connection
$host = '127.0.0.1';
$dbname = 'expoints_db';
$username = 'root';
$password = '';

try {
    $db = new mysqli($host, $username, $password, $dbname);
    
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }
    
    $db->set_charset('utf8mb4');
    
    // Get reviewer username
    $reviewer = $_SESSION['username'] ?? 'Unknown';
    
    // Update the ban review status
    $stmt = $db->prepare("UPDATE ban_reviews SET status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssi", $action, $reviewer, $review_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update review: " . $stmt->error);
    }
    
    $stmt->close();
    
    // If approved, ban the user
    if ($action === 'approved') {
        // Get the username and reason from the review
        $stmt = $db->prepare("SELECT username, reason FROM ban_reviews WHERE id = ?");
        $stmt->bind_param("i", $review_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $review = $result->fetch_assoc();
        $stmt->close();
        
        if ($review) {
            $banned_username = $review['username'];
            $ban_reason = $review['reason'];
            
            // Update user_info table to mark user as banned
            $stmt = $db->prepare("UPDATE user_info SET is_banned = 1, ban_reason = ?, banned_at = NOW(), banned_by = ? WHERE username = ?");
            $stmt->bind_param("sss", $ban_reason, $reviewer, $banned_username);
            $stmt->execute();
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
            
            if ($affected_rows > 0) {
                // Log the moderation action
                $stmt = $db->prepare("INSERT INTO moderation_log (moderator, action, reason, created_at) VALUES (?, 'ban_user', ?, NOW())");
                $log_reason = "User @{$banned_username} banned: " . $ban_reason;
                $stmt->bind_param("ss", $reviewer, $log_reason);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    
    $db->close();
    
    $message = $action === 'approved' 
        ? 'Ban approved successfully. User has been banned.' 
        : 'Review rejected. User flag has been cleared.';
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    error_log("Review ban error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error processing review',
        'error' => $e->getMessage()
    ]);
}
?>
