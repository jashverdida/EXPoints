<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'expoints_db');

$result = $mysqli->query('SELECT id, username FROM posts WHERE username != "YourUsername" LIMIT 1');
$post = $result->fetch_assoc();

echo "Testing with post ID: {$post['id']}, username: {$post['username']}\n\n";

// Now test the full query
$post_id = $post['id'];
$stmt = $mysqli->prepare("
    SELECT p.id, p.game, p.title, p.content, p.username, 
           p.likes as like_count, p.comments as comment_count, 
           p.created_at, p.updated_at, p.user_id,
           COALESCE(ui1.profile_picture, ui2.profile_picture) as profile_picture,
           COALESCE(ui1.exp_points, ui2.exp_points, 0) as exp_points
    FROM posts p
    LEFT JOIN user_info ui1 ON p.user_id = ui1.user_id
    LEFT JOIN user_info ui2 ON p.username COLLATE utf8mb4_unicode_ci = ui2.username COLLATE utf8mb4_unicode_ci
    WHERE p.id = ?
");

$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo "Results:\n";
foreach($data as $k => $v) {
    echo "  $k: " . ($v ?? 'NULL') . "\n";
}
?>
