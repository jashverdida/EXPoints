<?php
// Prevent any output before JSON response
ob_start();
error_reporti} catch (Exception $e) {
    http_response_code(400);
    
    // Clean output buffer before JSON response
    ob_clean();
    
    echo json_encode(['error' => $e->getMessage()]);
}

// Flush clean output
ob_end_flush();
?>;
ini_set('display_errors', 0);

session_start();
require_once '../config/firestore.php';

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
    $firestoreService = new FirestoreService();
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Get comments for a review
            if (!isset($_GET['reviewId'])) {
                throw new Exception('Review ID is required');
            }
            
            $reviewId = $_GET['reviewId'];
            $result = $firestoreService->getComments($reviewId);
            
            if ($result['success']) {
                echo json_encode(['success' => true, 'comments' => $result['data']]);
            } else {
                throw new Exception($result['error']);
            }
            break;
            
        case 'POST':
            // Add new comment - requires authentication
            if (!isset($_SESSION['user_authenticated']) || !isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Authentication required to post comments']);
                break;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['reviewId']) || !isset($input['content'])) {
                throw new Exception('Review ID and content are required');
            }
            
            if (strlen(trim($input['content'])) < 1) {
                throw new Exception('Comment content cannot be empty');
            }
            
            $reviewId = $input['reviewId'];
            $userId = $_SESSION['user_id'];
            $content = trim($input['content']);
            
            $result = $firestoreService->addComment($reviewId, $userId, $content);
            
            if ($result['success']) {
                http_response_code(201);
                echo json_encode(['success' => true, 'message' => 'Comment added successfully', 'commentId' => $result['commentId']]);
            } else {
                throw new Exception($result['error']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
