<?php
session_start();
$_SESSION['authenticated'] = true;

$post_id = 3;

$db = new mysqli('127.0.0.1', 'root', '', 'expoints_db');
$db->set_charset('utf8mb4');

// Fetch post
$query = "SELECT id, game, title, content, username, user_id, likes, comments, created_at, updated_at FROM posts WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

echo "Post data:\n";
print_r($post);

// Rename fields
$post['like_count'] = $post['likes'];
$post['comment_count'] = $post['comments'];

// Get user info
if (!empty($post['user_id'])) {
    echo "\nTrying by user_id: {$post['user_id']}\n";
    $user_stmt = $db->prepare("SELECT profile_picture, exp_points FROM user_info WHERE user_id = ?");
    $user_stmt->bind_param("i", $post['user_id']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    if ($user_result->num_rows > 0) {
        $user_info = $user_result->fetch_assoc();
        echo "Found user info:\n";
        print_r($user_info);
        $post['profile_picture'] = $user_info['profile_picture'];
        $post['exp_points'] = $user_info['exp_points'];
    }
    $user_stmt->close();
}

if (empty($post['profile_picture']) && !empty($post['username'])) {
    echo "\nTrying by username: {$post['username']}\n";
    $user_stmt = $db->prepare("SELECT profile_picture, exp_points FROM user_info WHERE username = ?");
    $user_stmt->bind_param("s", $post['username']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    if ($user_result->num_rows > 0) {
        $user_info = $user_result->fetch_assoc();
        echo "Found user info:\n";
        print_r($user_info);
        $post['profile_picture'] = $user_info['profile_picture'];
        $post['exp_points'] = $user_info['exp_points'];
    }
    $user_stmt->close();
}

echo "\n\nFinal post object:\n";
print_r($post);

$db->close();
?>
