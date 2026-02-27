<?php
require_once __DIR__ . '/../config/session.php';
startSecureSession();
header('Content-Type: application/json');

require_once '../includes/NotificationSystem.php';

// Check if user is authenticated
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}



$db = getDBConnection();
if (!$db) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    echo json_encode(['success' => false, 'error' => 'User ID not found']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_notifications':
        $notifications = NotificationSystem::getUnreadNotifications($db, $userId);
        $count = NotificationSystem::getUnreadCount($db, $userId);
        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'count' => $count
        ]);
        break;
        
    case 'get_count':
        $count = NotificationSystem::getUnreadCount($db, $userId);
        echo json_encode([
            'success' => true,
            'count' => $count
        ]);
        break;
        
    case 'mark_read':
        $notificationId = $_POST['notification_id'] ?? null;
        if (!$notificationId) {
            echo json_encode(['success' => false, 'error' => 'Notification ID required']);
            break;
        }
        
        $result = NotificationSystem::markAsRead($db, $notificationId);
        echo json_encode(['success' => $result]);
        break;
        
    case 'mark_all_read':
        $result = NotificationSystem::markAllAsRead($db, $userId);
        echo json_encode(['success' => $result]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

$db->close();
