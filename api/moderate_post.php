<?php
require_once __DIR__ . '/../config/supabase-session.php';
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Check authentication and mod role
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden - Admin access required']);
    exit();
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['post_id']) || !isset($data['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

$post_id = (int)$data['post_id'];
$action = $data['action'];
$reason = $data['reason'] ?? '';
$mod_username = $_SESSION['username'] ?? 'Unknown';

// Database connection


$db = getDBConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

try {
    if ($action === 'hide') {
        // Hide the post (add hidden column if it doesn't exist)
        $db->query("ALTER TABLE posts ADD COLUMN IF NOT EXISTS hidden TINYINT(1) DEFAULT 0");
        
        $stmt = $db->prepare("UPDATE posts SET hidden = 1 WHERE id = ?");
        $stmt->bind_param("i", $post_id);
        
        if ($stmt->execute()) {
            // Log the moderation action
            $log_stmt = $db->prepare("INSERT INTO moderation_log (post_id, moderator, action, reason, created_at) VALUES (?, ?, 'hide', ?, NOW())");
            $log_stmt->bind_param("iss", $post_id, $mod_username, $reason);
            $log_stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Post hidden successfully']);
        } else {
            throw new Exception("Failed to hide post");
        }
        
    } elseif ($action === 'unhide') {
        // Unhide the post
        $stmt = $db->prepare("UPDATE posts SET hidden = 0 WHERE id = ?");
        $stmt->bind_param("i", $post_id);
        
        if ($stmt->execute()) {
            // Log the moderation action
            $log_stmt = $db->prepare("INSERT INTO moderation_log (post_id, moderator, action, reason, created_at) VALUES (?, ?, 'unhide', ?, NOW())");
            $log_stmt->bind_param("iss", $post_id, $mod_username, $reason);
            $log_stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Post unhidden successfully']);
        } else {
            throw new Exception("Failed to unhide post");
        }
        
    } elseif ($action === 'flag_ban') {
        // Flag user for ban review (add to ban_reviews table)
        // First, get the post author
        $post_stmt = $db->prepare("SELECT username FROM posts WHERE id = ?");
        $post_stmt->bind_param("i", $post_id);
        $post_stmt->execute();
        $post_result = $post_stmt->get_result();
        
        if ($post_result->num_rows === 0) {
            throw new Exception("Post not found");
        }
        
        $post_data = $post_result->fetch_assoc();
        $flagged_username = $post_data['username'];
        
        // Insert into ban_reviews table
        $stmt = $db->prepare("INSERT INTO ban_reviews (username, post_id, flagged_by, reason, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("siss", $flagged_username, $post_id, $mod_username, $reason);
        
        if ($stmt->execute()) {
            // Log the moderation action
            $log_stmt = $db->prepare("INSERT INTO moderation_log (post_id, moderator, action, reason, created_at) VALUES (?, ?, 'flag_ban', ?, NOW())");
            $log_stmt->bind_param("iss", $post_id, $mod_username, $reason);
            $log_stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'User flagged for ban review']);
        } else {
            throw new Exception("Failed to flag user for ban review");
        }
        
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}


?>
