<?php
// Simple file-based storage for posts (temporary solution for activity)
$postsFile = 'data/posts.json';

// Ensure data directory exists
if (!file_exists('data')) {
    mkdir('data', 0777, true);
}

// Initialize posts file if it doesn't exist
if (!file_exists($postsFile)) {
    file_put_contents($postsFile, json_encode([]));
}

// Function to get all posts
function getPosts() {
    global $postsFile;
    $posts = json_decode(file_get_contents($postsFile), true);
    return $posts ? $posts : [];
}

// Function to save posts
function savePosts($posts) {
    global $postsFile;
    file_put_contents($postsFile, json_encode($posts, JSON_PRETTY_PRINT));
}

// Function to get next ID
function getNextId() {
    $posts = getPosts();
    if (empty($posts)) {
        return 1;
    }
    return max(array_column($posts, 'id')) + 1;
}

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Display posts - redirect to dashboard
        header('Location: dashboard.php');
        exit;
        break;
        
    case 'POST':
        // Create new post
        if (isset($_POST['action']) && $_POST['action'] === 'create') {
            $game = $_POST['game'] ?? '';
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            $username = $_POST['username'] ?? 'YourUsername';
            
            if (!empty($game) && !empty($title) && !empty($content)) {
                $posts = getPosts();
                $newPost = [
                    'id' => getNextId(),
                    'game' => $game,
                    'title' => $title,
                    'content' => $content,
                    'username' => $username,
                    'likes' => 0,
                    'comments' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $posts[] = $newPost;
                savePosts($posts);
                
                // Redirect back to dashboard with success message
                header('Location: dashboard.php?success=post_created');
                exit;
            } else {
                header('Location: dashboard.php?error=missing_fields');
                exit;
            }
        }
        break;
        
    case 'PUT':
        // Update existing post
        parse_str(file_get_contents("php://input"), $putData);
        
        if (isset($putData['action']) && $putData['action'] === 'update') {
            $id = intval($putData['id'] ?? 0);
            $title = $putData['title'] ?? '';
            $content = $putData['content'] ?? '';
            
            if ($id > 0 && !empty($title) && !empty($content)) {
                $posts = getPosts();
                foreach ($posts as &$post) {
                    if ($post['id'] == $id) {
                        $post['title'] = $title;
                        $post['content'] = $content;
                        $post['updated_at'] = date('Y-m-d H:i:s');
                        break;
                    }
                }
                savePosts($posts);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Post updated successfully']);
                exit;
            }
        }
        break;
        
    case 'DELETE':
        // Delete post
        parse_str(file_get_contents("php://input"), $deleteData);
        
        if (isset($deleteData['action']) && $deleteData['action'] === 'delete') {
            $id = intval($deleteData['id'] ?? 0);
            
            if ($id > 0) {
                $posts = getPosts();
                $posts = array_filter($posts, function($post) use ($id) {
                    return $post['id'] != $id;
                });
                savePosts(array_values($posts));
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Post deleted successfully']);
                exit;
            }
        }
        break;
        
    default:
        header('HTTP/1.1 405 Method Not Allowed');
        echo 'Method not allowed';
        exit;
}

// If we reach here, redirect to dashboard
header('Location: dashboard.php');
exit;
?>
