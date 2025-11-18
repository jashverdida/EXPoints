<?php
// Test the new dashboard approach

require_once 'includes/db_helper.php';

echo "Testing new dashboard approach...\n\n";

$db = getDBConnection();
$posts = [];

// Simplified query for Supabase
$query = "SELECT * FROM posts WHERE hidden = 0 ORDER BY created_at DESC";
$result = $db->query($query);

if ($result) {
    echo "✅ Main query successful! Rows: " . $result->num_rows . "\n\n";
    
    while ($post = $result->fetch_assoc()) {
        echo "Processing post ID: {$post['id']}...\n";
        
        // Get user info
        $userInfoStmt = $db->prepare("SELECT profile_picture, exp_points, is_banned FROM user_info WHERE username = ?");
        $userInfoStmt->bind_param("s", $post['username']);
        $userInfoStmt->execute();
        $userInfoResult = $userInfoStmt->get_result();
        
        if ($userInfoResult && $userInfoResult->num_rows > 0) {
            $userInfo = $userInfoResult->fetch_assoc();
            if ($userInfo['is_banned']) {
                echo "  Skipping (user banned)\n";
                $userInfoStmt->close();
                continue;
            }
            $post['author_profile_picture'] = $userInfo['profile_picture'] ?: '../assets/img/cat1.jpg';
        } else {
            $post['author_profile_picture'] = '../assets/img/cat1.jpg';
        }
        $userInfoStmt->close();
        
        // Get comment count
        $commentsStmt = $db->prepare("SELECT COUNT(*) as count FROM post_comments WHERE post_id = ?");
        $commentsStmt->bind_param("i", $post['id']);
        $commentsStmt->execute();
        $commentsResult = $commentsStmt->get_result();
        if ($commentsResult) {
            $countData = $commentsResult->fetch_assoc();
            $post['comment_count'] = $countData['count'];
        }
        $commentsStmt->close();
        
        // Get like count
        $likesStmt = $db->prepare("SELECT COUNT(*) as count FROM post_likes WHERE post_id = ?");
        $likesStmt->bind_param("i", $post['id']);
        $likesStmt->execute();
        $likesResult = $likesStmt->get_result();
        if ($likesResult) {
            $countData = $likesResult->fetch_assoc();
            $post['like_count'] = $countData['count'];
        }
        $likesStmt->close();
        
        echo "  ✅ Post processed successfully\n";
        $posts[] = $post;
    }
    
    echo "\n\nTotal posts loaded: " . count($posts) . "\n\n";
    if (count($posts) > 0) {
        echo "Sample post:\n";
        print_r($posts[0]);
    }
} else {
    echo "❌ Main query failed\n";
}
