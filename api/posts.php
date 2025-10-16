<?php
// API endpoint for post operations (Create, Read, Update, Delete, Like)
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

// Database connection
function getDBConnection() {
    $host = '127.0.0.1';
    $dbname = 'expoints_db';
    $username = 'root';
    $password = '';
    
    try {
        $mysqli = new mysqli($host, $username, $password, $dbname);
        if ($mysqli->connect_error) {
            throw new Exception("Connection failed: " . $mysqli->connect_error);
        }
        $mysqli->set_charset('utf8mb4');
        return $mysqli;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}

$db = getDBConnection();
if (!$db) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$userId = $_SESSION['user_id'];

// Get username from user_info
$usernameStmt = $db->prepare("SELECT username FROM user_info WHERE user_id = ?");
$usernameStmt->bind_param("i", $userId);
$usernameStmt->execute();
$usernameResult = $usernameStmt->get_result();
$userInfo = $usernameResult->fetch_assoc();
$username = $userInfo['username'] ?? 'Unknown';
$usernameStmt->close();

switch ($action) {
    case 'create':
        // Create new post
        $input = json_decode(file_get_contents('php://input'), true);
        $title = trim($input['title'] ?? '');
        $content = trim($input['content'] ?? '');
        $game = trim($input['game'] ?? 'General'); // Default to General for now
        
        if (empty($title) || empty($content)) {
            echo json_encode(['success' => false, 'error' => 'Title and content are required']);
            exit();
        }
        
        $stmt = $db->prepare("INSERT INTO posts (user_id, username, game, title, content, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("issss", $userId, $username, $game, $title, $content);
        
        if ($stmt->execute()) {
            $postId = $stmt->insert_id;
            echo json_encode(['success' => true, 'post_id' => $postId, 'message' => 'Post created successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to create post: ' . $stmt->error]);
        }
        $stmt->close();
        break;
        
    case 'update':
        // Update existing post
        $input = json_decode(file_get_contents('php://input'), true);
        $postId = intval($input['post_id'] ?? 0);
        $title = trim($input['title'] ?? '');
        $content = trim($input['content'] ?? '');
        
        if ($postId <= 0 || empty($title) || empty($content)) {
            echo json_encode(['success' => false, 'error' => 'Invalid data']);
            exit();
        }
        
        // Verify user owns this post
        $checkStmt = $db->prepare("SELECT id FROM posts WHERE id = ? AND user_id = ?");
        $checkStmt->bind_param("ii", $postId, $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            echo json_encode(['success' => false, 'error' => 'You do not have permission to edit this post']);
            $checkStmt->close();
            exit();
        }
        $checkStmt->close();
        
        $stmt = $db->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        $stmt->bind_param("ssi", $title, $content, $postId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Post updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update post']);
        }
        $stmt->close();
        break;
        
    case 'delete':
        // Delete post
        $postId = intval($_POST['post_id'] ?? $_GET['post_id'] ?? 0);
        
        if ($postId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid post ID']);
            exit();
        }
        
        // Verify user owns this post
        $checkStmt = $db->prepare("SELECT id FROM posts WHERE id = ? AND user_id = ?");
        $checkStmt->bind_param("ii", $postId, $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            echo json_encode(['success' => false, 'error' => 'You do not have permission to delete this post']);
            $checkStmt->close();
            exit();
        }
        $checkStmt->close();
        
        $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->bind_param("i", $postId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Post deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete post']);
        }
        $stmt->close();
        break;
        
    case 'like':
        // Toggle like on post
        $postId = intval($_POST['post_id'] ?? $_GET['post_id'] ?? 0);
        
        if ($postId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid post ID']);
            exit();
        }
        
        // Check if user already liked this post
        $checkStmt = $db->prepare("SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?");
        $checkStmt->bind_param("ii", $postId, $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            // Unlike - remove the like
            $checkStmt->close();
            $stmt = $db->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $postId, $userId);
            $stmt->execute();
            $liked = false;
        } else {
            // Like - add the like
            $checkStmt->close();
            $stmt = $db->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $postId, $userId);
            $stmt->execute();
            $liked = true;
        }
        
        // Get updated like count
        $countStmt = $db->prepare("SELECT COUNT(*) as like_count FROM post_likes WHERE post_id = ?");
        $countStmt->bind_param("i", $postId);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $likeData = $countResult->fetch_assoc();
        $likeCount = $likeData['like_count'];
        
        echo json_encode([
            'success' => true,
            'liked' => $liked,
            'like_count' => $likeCount
        ]);
        
        $stmt->close();
        $countStmt->close();
        break;
        
    case 'get_posts':
        // Get all posts with like status for current user
        $stmt = $db->prepare("
            SELECT 
                p.id,
                p.game,
                p.title,
                p.content,
                p.username,
                p.user_id,
                p.created_at,
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as like_count,
                (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) as comment_count,
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id AND user_id = ?) as user_liked
            FROM posts p
            ORDER BY p.created_at DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $posts = [];
        while ($row = $result->fetch_assoc()) {
            $row['user_liked'] = (bool)$row['user_liked'];
            $row['is_owner'] = ($row['user_id'] === $userId);
            $posts[] = $row;
        }
        
        echo json_encode(['success' => true, 'posts' => $posts]);
        $stmt->close();
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

$db->close();
?>
