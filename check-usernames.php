<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'expoints_db');

echo "Checking user_info for 'YourUsername':\n";
$result = $mysqli->query("SELECT username, profile_picture, user_id FROM user_info WHERE username = 'YourUsername'");
if($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    print_r($row);
} else {
    echo "No user_info found for 'YourUsername'\n";
}

echo "\n\nAll usernames in user_info:\n";
$result = $mysqli->query("SELECT user_id, username, profile_picture FROM user_info LIMIT 10");
while($row = $result->fetch_assoc()) {
    echo "  user_id: {$row['user_id']}, username: {$row['username']}, profile_picture: " . ($row['profile_picture'] ?? 'NULL') . "\n";
}

echo "\n\nChecking posts.username values:\n";
$result = $mysqli->query("SELECT DISTINCT username FROM posts LIMIT 10");
while($row = $result->fetch_assoc()) {
    echo "  - {$row['username']}\n";
}
?>
