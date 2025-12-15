<?php
// Prevent any output before JSON response
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
require_once __DIR__ . '/../includes/db_helper.php';

// Clean any output buffer and set JSON headers
ob_clean();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Cache-Control: no-cache, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $db = getDBConnection();
    
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Get comments for a post
            if (!isset($_GET['post_id'])) {
                throw new Exception('Post ID is required');
            }
            
            $post_id = intval($_GET['post_id']);
            
            $stmt = $db->prepare("SELECT pc.*, ui.username, ui.profile_picture 
                                   FROM post_comments pc 
                                   JOIN user_info ui ON pc.user_id = ui.user_id 
                                   WHERE pc.post_id = ? 
                                   ORDER BY pc.created_at DESC");
            $stmt->bind_param("i", $post_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $comments = [];
            while ($row = $result->fetch_assoc()) {
                $comments[] = $row;
            }
            
            echo json_encode(['success' => true, 'comments' => $comments]);
            break;
            
        case 'POST':
            // Add new comment - requires authentication
            if (!isset($_SESSION['authenticated']) || !isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Authentication required to post comments']);
                break;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['post_id']) || !isset($input['comment'])) {
                throw new Exception('Post ID and comment are required');
            }
            
            if (strlen(trim($input['comment'])) < 1) {
                throw new Exception('Comment content cannot be empty');
            }
            
            $post_id = intval($input['post_id']);
            $user_id = $_SESSION['user_id'];
            $username = $_SESSION['username'] ?? 'Unknown';
            $comment = trim($input['comment']);
            $parent_id = isset($input['parent_id']) ? intval($input['parent_id']) : null;
            
            $stmt = $db->prepare("INSERT INTO post_comments (post_id, user_id, username, comment, parent_id) 
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iissi", $post_id, $user_id, $username, $comment, $parent_id);
            $stmt->execute();
            
            $comment_id = $stmt->insert_id;
            
            // Update comment count on post
            $stmt = $db->prepare("UPDATE posts SET comments = comments + 1 WHERE id = ?");
            $stmt->bind_param("i", $post_id);
            $stmt->execute();
            
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Comment added successfully', 'comment_id' => $comment_id]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    ob_clean();
    echo json_encode(['error' => $e->getMessage()]);
}
?>
