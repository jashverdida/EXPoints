<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

header('Content-Type: application/json');

// Supabase database connection
require_once __DIR__ . '/../includes/db_helper.php';

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid request data']);
    exit();
}

$userId = $_SESSION['user_id'];
$db = getDBConnection();

if (!$db) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

try {
    $db->begin_transaction();

    // Handle profile picture upload
    $profilePicture = null;
    if (!empty($data['avatar']) && strpos($data['avatar'], 'data:image') === 0) {
        // Save base64 image
        $imageData = $data['avatar'];
        $imageParts = explode(',', $imageData);
        if (count($imageParts) === 2) {
            $imageBase64 = base64_decode($imageParts[1]);
            $imageName = 'profile_' . $userId . '_' . time() . '.png';
            $uploadDir = '../assets/img/profiles/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $imagePath = $uploadDir . $imageName;
            file_put_contents($imagePath, $imageBase64);
            $profilePicture = '../assets/img/profiles/' . $imageName;
        }
    }

    // Update user_info table
    $updateFields = [];
    $params = [];
    $types = '';

    if (!empty($data['bio'])) {
        $updateFields[] = "bio = ?";
        $params[] = $data['bio'];
        $types .= 's';
    }

    if ($profilePicture) {
        $updateFields[] = "profile_picture = ?";
        $params[] = $profilePicture;
        $types .= 's';
    }

    if (!empty($updateFields)) {
        $sql = "UPDATE user_info SET " . implode(', ', $updateFields) . " WHERE user_id = ?";
        $params[] = $userId;
        $types .= 'i';

        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stmt->close();
    }

    // Handle best posts selection
    if (!empty($data['best_posts']) && is_array($data['best_posts'])) {
        // For now, we'll just log this
        // In a full implementation, you'd save this to a separate table
        error_log("Best posts selected: " . implode(',', $data['best_posts']));
    }

    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully'
    ]);

} catch (Exception $e) {
    $db->rollback();
    error_log("Profile save error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to save profile: ' . $e->getMessage()
    ]);
}

$db->close();
?>
