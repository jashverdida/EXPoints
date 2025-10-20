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
        $game = trim($input['game'] ?? '');
        
        if (empty($title) || empty($content) || empty($game)) {
            echo json_encode(['success' => false, 'error' => 'Title, content, and game are required']);
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
        // Get all posts with like status for current user and author profile pictures
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
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id AND user_id = ?) as user_liked,
                (SELECT COUNT(*) FROM post_bookmarks WHERE post_id = p.id AND user_id = ?) as user_bookmarked,
                ui.profile_picture as author_profile_picture,
                ui.exp_points
            FROM posts p
            LEFT JOIN user_info ui ON p.user_id = ui.user_id
            ORDER BY p.created_at DESC
        ");
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $posts = [];
        while ($row = $result->fetch_assoc()) {
            $row['user_liked'] = (bool)$row['user_liked'];
            $row['user_bookmarked'] = (bool)$row['user_bookmarked'];
            $row['is_owner'] = ($row['user_id'] === $userId);
            $row['exp_points'] = (int)($row['exp_points'] ?? 0);
            // Set default profile picture if none exists
            if (empty($row['author_profile_picture'])) {
                $row['author_profile_picture'] = '../assets/img/cat1.jpg';
            }
            $posts[] = $row;
        }
        
        echo json_encode(['success' => true, 'posts' => $posts]);
        $stmt->close();
        break;
    
    case 'bookmark':
        // Toggle bookmark on post
        $postId = intval($_POST['post_id'] ?? $_GET['post_id'] ?? 0);
        
        if ($postId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid post ID']);
            exit();
        }
        
        // Check if user already bookmarked this post
        $checkStmt = $db->prepare("SELECT id FROM post_bookmarks WHERE post_id = ? AND user_id = ?");
        $checkStmt->bind_param("ii", $postId, $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            // Remove bookmark
            $checkStmt->close();
            $stmt = $db->prepare("DELETE FROM post_bookmarks WHERE post_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $postId, $userId);
            $stmt->execute();
            $bookmarked = false;
        } else {
            // Add bookmark
            $checkStmt->close();
            $stmt = $db->prepare("INSERT INTO post_bookmarks (post_id, user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $postId, $userId);
            $stmt->execute();
            $bookmarked = true;
        }
        
        echo json_encode([
            'success' => true,
            'bookmarked' => $bookmarked
        ]);
        
        $stmt->close();
        break;
    
    case 'get_bookmarked_posts':
        // Get all bookmarked posts for current user
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
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id AND user_id = ?) as user_liked,
                1 as user_bookmarked,
                pb.created_at as bookmarked_at
            FROM posts p
            INNER JOIN post_bookmarks pb ON p.id = pb.post_id
            WHERE pb.user_id = ?
            ORDER BY pb.created_at DESC
        ");
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $posts = [];
        while ($row = $result->fetch_assoc()) {
            $row['user_liked'] = (bool)$row['user_liked'];
            $row['user_bookmarked'] = true;
            $row['is_owner'] = ($row['user_id'] === $userId);
            $posts[] = $row;
        }
        
        echo json_encode(['success' => true, 'posts' => $posts]);
        $stmt->close();
        break;
    
    case 'get_comments':
        // Get comments for a specific post with commenter profile pictures
        $postId = intval($_GET['post_id'] ?? 0);
        
        if ($postId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid post ID']);
            exit();
        }
        
        $stmt = $db->prepare("
            SELECT 
                pc.id,
                pc.comment,
                pc.username,
                pc.user_id,
                pc.created_at,
                pc.like_count,
                pc.reply_count,
                ui.profile_picture as commenter_profile_picture,
                ui.exp_points,
                (SELECT COUNT(*) FROM comment_likes WHERE comment_id = pc.id AND user_id = ?) as user_liked
            FROM post_comments pc
            LEFT JOIN user_info ui ON pc.user_id = ui.user_id
            WHERE pc.post_id = ? AND pc.parent_comment_id IS NULL
            ORDER BY pc.created_at ASC
        ");
        $stmt->bind_param("ii", $userId, $postId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $comments = [];
        while ($row = $result->fetch_assoc()) {
            $row['is_owner'] = ($row['user_id'] === $userId);
            $row['user_liked'] = (bool)$row['user_liked'];
            $row['exp_points'] = (int)($row['exp_points'] ?? 0);
            // Set default profile picture if none exists
            if (empty($row['commenter_profile_picture'])) {
                $row['commenter_profile_picture'] = '../assets/img/cat1.jpg';
            }
            $comments[] = $row;
        }
        
        echo json_encode(['success' => true, 'comments' => $comments]);
        $stmt->close();
        break;
    
    case 'add_comment':
        // Add a comment to a post
        $postId = intval($_POST['post_id'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        
        if ($postId <= 0 || empty($comment)) {
            echo json_encode(['success' => false, 'error' => 'Invalid data']);
            exit();
        }
        
        $stmt = $db->prepare("INSERT INTO post_comments (post_id, user_id, username, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiss", $postId, $userId, $username, $comment);
        
        if ($stmt->execute()) {
            $commentId = $stmt->insert_id;
            echo json_encode(['success' => true, 'comment_id' => $commentId, 'message' => 'Comment added successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to add comment']);
        }
        $stmt->close();
        break;
    
    case 'like_comment':
        // Toggle like on a comment
        $commentId = intval($_POST['comment_id'] ?? $_GET['comment_id'] ?? 0);
        
        if ($commentId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid comment ID']);
            exit();
        }
        
        // Check if user already liked this comment
        $checkStmt = $db->prepare("SELECT id FROM comment_likes WHERE comment_id = ? AND user_id = ?");
        $checkStmt->bind_param("ii", $commentId, $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            // Remove like
            $checkStmt->close();
            $stmt = $db->prepare("DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $commentId, $userId);
            $stmt->execute();
            
            // Decrement like count
            $db->query("UPDATE post_comments SET like_count = GREATEST(like_count - 1, 0) WHERE id = $commentId");
            
            $liked = false;
        } else {
            // Add like
            $checkStmt->close();
            $stmt = $db->prepare("INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $commentId, $userId);
            $stmt->execute();
            
            // Increment like count
            $db->query("UPDATE post_comments SET like_count = like_count + 1 WHERE id = $commentId");
            
            $liked = true;
        }
        
        // Get updated like count
        $countResult = $db->query("SELECT like_count FROM post_comments WHERE id = $commentId");
        $countRow = $countResult->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'liked' => $liked,
            'like_count' => $countRow['like_count']
        ]);
        $stmt->close();
        break;
    
    case 'add_reply':
        // Add a reply to a comment
        $parentCommentId = intval($_POST['parent_comment_id'] ?? 0);
        $postId = intval($_POST['post_id'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        
        if ($parentCommentId <= 0 || $postId <= 0 || empty($comment)) {
            echo json_encode(['success' => false, 'error' => 'Invalid data']);
            exit();
        }
        
        $stmt = $db->prepare("INSERT INTO post_comments (post_id, user_id, username, comment, parent_comment_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iissi", $postId, $userId, $username, $comment, $parentCommentId);
        
        if ($stmt->execute()) {
            $replyId = $stmt->insert_id;
            
            // Increment reply count on parent comment
            $db->query("UPDATE post_comments SET reply_count = reply_count + 1 WHERE id = $parentCommentId");
            
            echo json_encode(['success' => true, 'reply_id' => $replyId, 'message' => 'Reply added successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to add reply']);
        }
        $stmt->close();
        break;
    
    case 'get_replies':
        // Get replies for a specific comment
        $parentCommentId = intval($_GET['parent_comment_id'] ?? 0);
        
        if ($parentCommentId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid comment ID']);
            exit();
        }
        
        $stmt = $db->prepare("
            SELECT 
                pc.id,
                pc.comment,
                pc.username,
                pc.user_id,
                pc.created_at,
                pc.like_count,
                ui.profile_picture as commenter_profile_picture,
                (SELECT COUNT(*) FROM comment_likes WHERE comment_id = pc.id AND user_id = ?) as user_liked
            FROM post_comments pc
            LEFT JOIN user_info ui ON pc.user_id = ui.user_id
            WHERE pc.parent_comment_id = ?
            ORDER BY pc.created_at ASC
        ");
        $stmt->bind_param("ii", $userId, $parentCommentId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $replies = [];
        while ($row = $result->fetch_assoc()) {
            $row['is_owner'] = ($row['user_id'] === $userId);
            $row['user_liked'] = (bool)$row['user_liked'];
            // Set default profile picture if none exists
            if (empty($row['commenter_profile_picture'])) {
                $row['commenter_profile_picture'] = '../assets/img/cat1.jpg';
            }
            $replies[] = $row;
        }
        
        echo json_encode(['success' => true, 'replies' => $replies]);
        $stmt->close();
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

$db->close();
?>
