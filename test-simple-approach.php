<?php
// Test simplified approach with row counting

require_once 'includes/db_helper.php';

echo "Testing simplified approach...\n\n";

$db = getDBConnection();
$posts = [];

$query = "SELECT * FROM posts WHERE hidden = 0 ORDER BY created_at DESC";
$result = $db->query($query);

if ($result) {
    echo "✅ Main query successful! Rows: " . $result->num_rows . "\n\n";
    
    $count = 0;
    while (($post = $result->fetch_assoc()) && $count < 3) {
        echo "Processing post ID: {$post['id']}...\n";
        
        // Get user info
        $userInfoStmt = $db->prepare("SELECT profile_picture, is_banned FROM user_info WHERE username = ?");
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
        
        // Get comment count - just count rows
        $commentsStmt = $db->prepare("SELECT id FROM post_comments WHERE post_id = ?");
        $commentsStmt->bind_param("i", $post['id']);
        $commentsStmt->execute();
        $commentsResult = $commentsStmt->get_result();
        $post['comment_count'] = $commentsResult ? $commentsResult->num_rows : 0;
        $commentsStmt->close();
        
        // Get like count - just count rows
        $likesStmt = $db->prepare("SELECT id FROM post_likes WHERE post_id = ?");
        $likesStmt->bind_param("i", $post['id']);
        $likesStmt->execute();
        $likesResult = $likesStmt->get_result();
        $post['like_count'] = $likesResult ? $likesResult->num_rows : 0;
        $likesStmt->close();
        
        echo "  ✅ Post processed: {$post['comment_count']} comments, {$post['like_count']} likes\n";
        $posts[] = $post;
        $count++;
    }
    
    echo "\n\nTotal posts loaded: " . count($posts) . "\n";
} else {
    echo "❌ Main query failed\n";
}
