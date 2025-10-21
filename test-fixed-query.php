<?php
// Test the fixed query
$mysqli = new mysqli('127.0.0.1', 'root', '', 'expoints_db');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "Testing FIXED query:\n\n";

$post_id = 1;

$stmt = $mysqli->prepare("
    SELECT p.id, p.game, p.title, p.content, p.username, 
           p.likes as like_count, p.comments as comment_count, 
           p.created_at, p.updated_at, p.user_id,
           ui.profile_picture, ui.exp_points
    FROM posts p
    LEFT JOIN user_info ui ON p.user_id = ui.user_id
    WHERE p.id = ?
");

if (!$stmt) {
    echo "PREPARE ERROR: " . $mysqli->error . "\n";
} else {
    $stmt->bind_param("i", $post_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            echo "SUCCESS! Retrieved data for post ID $post_id:\n\n";
            foreach ($data as $key => $value) {
                echo "  $key: " . ($value ?? 'NULL') . "\n";
            }
        } else {
            echo "No results returned\n";
        }
    } else {
        echo "EXECUTE ERROR: " . $stmt->error . "\n";
    }
}

$mysqli->close();
?>
