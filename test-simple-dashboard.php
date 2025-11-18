<?php
// Test simplified dashboard query

require_once 'includes/db_helper.php';

echo "Testing simplified dashboard query...\n\n";

$db = getDBConnection();

// Simplified - get posts first, then join data separately
$query = "SELECT * FROM posts WHERE (hidden IS NULL OR hidden = 0) ORDER BY created_at DESC";

echo "Query:\n$query\n\n";

$result = $db->query($query);

if ($result) {
    echo "✅ Query successful!\n";
    echo "Rows: " . $result->num_rows . "\n\n";
    
    $posts = [];
    while ($post = $result->fetch_assoc()) {
        // Get user info separately
        $userQuery = "SELECT profile_picture, exp_points, is_banned FROM user_info WHERE username = '{$post['username']}'";
        $userResult = $db->query($userQuery);
        
        if ($userResult && $userResult->num_rows > 0) {
            $userInfo = $userResult->fetch_assoc();
            if (!$userInfo['is_banned']) {
                $post['profile_picture'] = $userInfo['profile_picture'];
                $post['exp_points'] = $userInfo['exp_points'];
                $posts[] = $post;
            }
        }
    }
    
    echo "Filtered posts: " . count($posts) . "\n\n";
    if (count($posts) > 0) {
        echo "First post:\n";
        print_r($posts[0]);
    }
} else {
    echo "❌ Query failed\n";
}
