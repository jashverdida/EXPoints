<?php
require_once __DIR__ . '/../config/supabase-session.php';
/**
 * Update User EXP API Endpoint
 * Calculates and updates user EXP based on likes received
 */

session_start();
header('Content-Type: application/json');

require_once '../includes/ExpSystem.php';

// Database connection


try {
    $db = getDBConnection();
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'update_exp':
            // Update EXP for a specific user
            if (!isset($_GET['user_id'])) {
                throw new Exception("User ID is required");
            }
            
            $userId = intval($_GET['user_id']);
            $stats = ExpSystem::updateUserExp($db, $userId);
            
            echo json_encode([
                'success' => true,
                'exp' => $stats['exp'],
                'level' => $stats['level'],
                'likes' => $stats['likes']
            ]);
            break;
            
        case 'get_stats':
            // Get current stats for a user
            if (!isset($_GET['user_id'])) {
                throw new Exception("User ID is required");
            }
            
            $userId = intval($_GET['user_id']);
            $stats = ExpSystem::getUserStats($db, $userId);
            
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            break;
            
        case 'update_all':
            // Update EXP for all users (admin function)
            $query = "SELECT user_id FROM user_info";
            $result = $db->query($query);
            
            $updated = 0;
            while ($row = $result->fetch_assoc()) {
                ExpSystem::updateUserExp($db, $row['user_id']);
                $updated++;
            }
            
            echo json_encode([
                'success' => true,
                'message' => "Updated EXP for $updated users",
                'updated_count' => $updated
            ]);
            break;
            
        default:
            throw new Exception("Invalid action");
    }
    
    
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
