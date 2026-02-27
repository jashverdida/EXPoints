<?php
require_once __DIR__ . '/../config/supabase-session.php';
// Prevent any output before JSON response
ob_start();
error_reporting(0);
ini_set('displ} catch (Exception $e) {
    http_response_code(400);
    
    // Clean output buffer before JSON response
    ob_clean();
    
    echo json_encode(['error' => $e->getMessage()]);
}

// Flush clean output
ob_end_flush();
?>rors', 0);

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
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $result = $firestoreService->getReviews($limit);
            
            if ($result['success']) {
                echo json_encode(['success' => true, 'reviews' => $result['data']]);
            } else {
                throw new Exception($result['error']);
            }
            break;
            
        case 'POST':
            if (!isset($_SESSION['user_authenticated']) || !isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Authentication required to post reviews']);
                break;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['gameTitle']) || !isset($input['rating']) || !isset($input['content'])) {
                throw new Exception('Game title, rating, and content are required');
            }
            
            if ($input['rating'] < 1 || $input['rating'] > 5) {
                throw new Exception('Rating must be between 1 and 5');
            }
            
            $reviewData = [
                'userId' => $_SESSION['user_id'],
                'gameTitle' => $input['gameTitle'],
                'rating' => $input['rating'],
                'content' => $input['content'],
                'platform' => $input['platform'] ?? null
            ];
            
            $result = $firestoreService->createReview($reviewData);
            
            if ($result['success']) {
                http_response_code(201);
                echo json_encode(['success' => true, 'message' => 'Review created successfully', 'reviewId' => $result['reviewId']]);
            } else {
                throw new Exception($result['error']);
            }
            break;
            
        case 'PUT':
            if (!isset($_SESSION['user_authenticated']) || !isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Authentication required to like reviews']);
                break;
            }
            
            if (!isset($_GET['id'])) {
                throw new Exception('Review ID is required');
            }
            
            $reviewId = $_GET['id'];
            $userId = $_SESSION['user_id'];
            
            $result = $firestoreService->likeReview($reviewId, $userId);
            
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => 'Review liked successfully']);
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
