<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Simple database connection function
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

// Get database connection
$db = getDBConnection();

if (!$db) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get current user info
$username = $_SESSION['username'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Redirect to dashboard
        header('Location: dashboard.php');
        exit;
        
    case 'POST':
        if (isset($_POST['action']) && $_POST['action'] === 'create') {
            try {
                $game = $_POST['game'] ?? '';
                $title = $_POST['title'] ?? '';
                $content = $_POST['content'] ?? '';
                
                // Validate required fields
                if (empty($game) || empty($title) || empty($content)) {
                    header('Location: dashboard.php?error=' . urlencode('All fields are required'));
                    exit;
                }
                
                $stmt = $db->prepare("INSERT INTO posts (game, title, content, username, user_email) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $game, $title, $content, $username, $user_email);
                $stmt->execute();
                $stmt->close();
                
                header('Location: dashboard.php?success=post_created');
                exit;
            } catch (Exception $e) {
                error_log("Post creation error: " . $e->getMessage());
                header('Location: dashboard.php?error=' . urlencode('Failed to create post'));
                exit;
            }
        }
        elseif (isset($_POST['action']) && $_POST['action'] === 'like_comment') {
            try {
                $comment_id = intval($_POST['comment_id'] ?? 0);
                $unlike = isset($_POST['unlike']) && $_POST['unlike'] === 'true';
                
                if ($comment_id <= 0) {
                    throw new Exception('Invalid comment ID');
                }
                
                $sql = $unlike 
                    ? "UPDATE comments SET likes = GREATEST(likes - 1, 0) WHERE id = ?"
                    : "UPDATE comments SET likes = likes + 1 WHERE id = ?";
                
                $stmt = $db->prepare($sql);
                $stmt->bind_param("i", $comment_id);
                $stmt->execute();
                $stmt->close();
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            } catch (Exception $e) {
                error_log("Like comment error: " . $e->getMessage());
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
        }
        elseif (isset($_POST['action']) && $_POST['action'] === 'add_comment') {
            try {
                $post_id = intval($_POST['post_id'] ?? 0);
                $comment_text = trim($_POST['comment_text'] ?? '');

                if ($post_id <= 0 || empty($comment_text)) {
                    throw new Exception('Invalid post ID or empty comment');
                }

                $stmt = $db->prepare("INSERT INTO comments (post_id, username, user_email, text) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $post_id, $username, $user_email, $comment_text);
                $stmt->execute();
                $stmt->close();
                
                // Update comment count
                $update_stmt = $db->prepare("UPDATE posts SET comments = comments + 1 WHERE id = ?");
                $update_stmt->bind_param("i", $post_id);
                $update_stmt->execute();
                $update_stmt->close();
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Comment added successfully']);
                exit;
            } catch (Exception $e) {
                error_log("Add comment error: " . $e->getMessage());
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
        }
        break;
        
    case 'PUT':
        parse_str(file_get_contents("php://input"), $putData);
        
        if (isset($putData['action']) && $putData['action'] === 'update') {
            try {
                $title = $putData['title'] ?? '';
                $content = $putData['content'] ?? '';
                $id = $putData['id'] ?? 0;
                
                $stmt = $db->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
                $stmt->bind_param("ssi", $title, $content, $id);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Post updated successfully']);
                exit;
            } catch(PDOException $e) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
        }
        break;
        
    case 'DELETE':
        parse_str(file_get_contents("php://input"), $deleteData);
        
        if (isset($deleteData['action']) && $deleteData['action'] === 'delete') {
            try {
                $id = $deleteData['id'] ?? 0;
                $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Post deleted successfully']);
                exit;
            } catch(PDOException $e) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
        }
        break;
}

// If we reach here, redirect to dashboard
header('Location: dashboard.php');
exit;
?>
