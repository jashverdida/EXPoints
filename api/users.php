<?php
require_once __DIR__ . '/../config/supabase-session.php';
// Prevent any output before JSON response
ob_start();
error_reporting(0);
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
    $firestoreService = new FirestoreService();

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (isset($_GET['id'])) {
                $userId = $_GET['id'];
            } else if (isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
            } else {
                throw new Exception('User ID required');
            }
            
            $result = $firestoreService->getUser($userId);
            
            if ($result['success']) {
                echo json_encode(['success' => true, 'user' => $result['data']]);
            } else {
                throw new Exception($result['error']);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['uid']) || !isset($input['email'])) {
                throw new Exception('UID and email are required');
            }
            
            $result = $firestoreService->createUser($input['uid'], [
                'email' => $input['email'],
                'displayName' => $input['displayName'] ?? null,
                'avatar' => $input['avatar'] ?? 'cat1.jpg'
            ]);
            
            if ($result['success']) {
                http_response_code(201);
                echo json_encode(['success' => true, 'message' => 'User created successfully', 'uid' => $result['uid']]);
            } else {
                throw new Exception($result['error']);
            }
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (isset($_GET['id'])) {
                $userId = $_GET['id'];
            } else if (isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
            } else {
                throw new Exception('User ID required');
            }
            
            if (!$input) {
                throw new Exception('Invalid JSON data');
            }
            
            $allowedFields = ['displayName', 'avatar', 'bio', 'favoriteGames'];
            $updateData = [];
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updateData[$field] = $input[$field];
                }
            }
            
            $result = $firestoreService->updateUser($userId, $updateData);
            
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => 'User updated successfully']);
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
