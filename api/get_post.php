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

// Get post ID from request
$post_id = intval($_GET['id'] ?? 0);

if ($post_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
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
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed', 'error' => $e->getMessage()]);
    exit;
}

// Fetch post from posts table
try {
    $query = "SELECT id, game, title, content, username, user_id, likes, comments, created_at, updated_at FROM posts WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $db->error);
    }
    
    $stmt->bind_param("i", $post_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Post not found']);
        $stmt->close();
        $db->close();
        exit;
    }
    
    $post = $result->fetch_assoc();
    $stmt->close();
    
    // Get actual like count from post_likes table
    $like_stmt = $db->prepare("SELECT COUNT(*) as count FROM post_likes WHERE post_id = ?");
    $like_stmt->bind_param("i", $post_id);
    $like_stmt->execute();
    $like_result = $like_stmt->get_result();
    $like_count = 0;
    if ($like_result->num_rows > 0) {
        $like_row = $like_result->fetch_assoc();
        $like_count = $like_row['count'];
    }
    $like_stmt->close();
    
    // Get actual comment count from comments table
    $comment_stmt = $db->prepare("SELECT COUNT(*) as count FROM comments WHERE post_id = ?");
    $comment_stmt->bind_param("i", $post_id);
    $comment_stmt->execute();
    $comment_result = $comment_stmt->get_result();
    $comment_count = 0;
    if ($comment_result->num_rows > 0) {
        $comment_row = $comment_result->fetch_assoc();
        $comment_count = $comment_row['count'];
    }
    $comment_stmt->close();
    
    // Set the actual counts
    $post['like_count'] = $like_count;
    $post['comment_count'] = $comment_count;
    unset($post['likes']);
    unset($post['comments']);
    
    // Get user info from user_info table
    $profile_picture = null;
    $exp_points = 0;
    
    // Try to get user info by user_id first
    if (!empty($post['user_id'])) {
        $user_stmt = $db->prepare("SELECT profile_picture, exp_points FROM user_info WHERE user_id = ?");
        $user_stmt->bind_param("i", $post['user_id']);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        if ($user_result->num_rows > 0) {
            $user_info = $user_result->fetch_assoc();
            $profile_picture = $user_info['profile_picture'];
            $exp_points = $user_info['exp_points'];
        }
        $user_stmt->close();
    }
    
    // If no user_id or no match, try by username
    if (empty($profile_picture) && !empty($post['username'])) {
        $user_stmt = $db->prepare("SELECT profile_picture, exp_points FROM user_info WHERE username = ?");
        $user_stmt->bind_param("s", $post['username']);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        if ($user_result->num_rows > 0) {
            $user_info = $user_result->fetch_assoc();
            $profile_picture = $user_info['profile_picture'];
            $exp_points = $user_info['exp_points'];
        }
        $user_stmt->close();
    }
    
    // Set profile picture (use default if none found)
    $post['profile_picture'] = !empty($profile_picture) ? $profile_picture : '../assets/img/default-avatar.png';
    $post['exp_points'] = $exp_points;
    
    $db->close();
    
    echo json_encode([
        'success' => true,
        'post' => $post
    ]);
    
} catch (Exception $e) {
    error_log("Get post error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error fetching post',
        'error' => $e->getMessage()
    ]);
    if (isset($db)) {
        $db->close();
    }
}
?>
