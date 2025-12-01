<?php
session_start();
require_once '../includes/db_helper.php';
require_once '../includes/exp_system.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

try {
    $db = getDBConnection();
    
    // Get pagination parameters
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 6;
    
    // Fetch posts with offset
    $query = "SELECT * FROM posts WHERE hidden = 0 ORDER BY created_at DESC LIMIT " . $limit . " OFFSET " . $offset;
    $result = $db->query($query);
    
    if (!$result) {
        throw new Exception("Failed to fetch posts");
    }
    
    $postsData = [];
    while ($post = $result->fetch_assoc()) {
        $postsData[] = $post;
    }
    
    // If no posts found, return empty array
    if (empty($postsData)) {
        echo json_encode([
            'success' => true,
            'posts' => [],
            'has_more' => false
        ]);
        exit;
    }
    
    // Build maps for batch lookups (same optimization as dashboard)
    $userInfoMap = [];
    $commentCountMap = [];
    $likeCountMap = [];
    
    // Collect all unique usernames
    $uniqueUsernames = array_unique(array_column($postsData, 'username'));
    
    // Fetch all user info
    foreach ($uniqueUsernames as $username) {
        $userInfoStmt = $db->prepare("SELECT username, profile_picture, exp_points, is_banned FROM user_info WHERE username = ?");
        $userInfoStmt->bind_param("s", $username);
        $userInfoStmt->execute();
        $userInfoResult = $userInfoStmt->get_result();
        
        if ($userInfoResult && $userInfoResult->num_rows > 0) {
            $userInfo = $userInfoResult->fetch_assoc();
            $userInfoMap[$username] = $userInfo;
        }
        $userInfoStmt->close();
    }
    
    // Get comment and like counts
    foreach ($postsData as $post) {
        $postId = $post['id'];
        
        // Comment count
        $commentStmt = $db->prepare("SELECT COUNT(*) as count FROM post_comments WHERE post_id = ?");
        $commentStmt->bind_param("i", $postId);
        $commentStmt->execute();
        $commentResult = $commentStmt->get_result();
        $commentRow = $commentResult->fetch_assoc();
        $commentCountMap[$postId] = (int)($commentRow['count'] ?? 0);
        $commentStmt->close();
        
        // Like count
        $likeStmt = $db->prepare("SELECT COUNT(*) as count FROM post_likes WHERE post_id = ?");
        $likeStmt->bind_param("i", $postId);
        $likeStmt->execute();
        $likeResult = $likeStmt->get_result();
        $likeRow = $likeResult->fetch_assoc();
        $likeCountMap[$postId] = (int)($likeRow['count'] ?? 0);
        $likeStmt->close();
    }
    
    // Process posts with cached data
    $processedPosts = [];
    foreach ($postsData as $post) {
        $userInfo = $userInfoMap[$post['username']] ?? null;
        
        // Skip banned users
        if ($userInfo && $userInfo['is_banned']) {
            continue;
        }
        
        $post['author_profile_picture'] = $userInfo['profile_picture'] ?? '../assets/img/cat1.jpg';
        $post['exp_points'] = $userInfo['exp_points'] ?? 0;
        $post['comment_count'] = $commentCountMap[$post['id']] ?? 0;
        $post['like_count'] = $likeCountMap[$post['id']] ?? 0;
        
        $processedPosts[] = $post;
    }
    
    // Check if there are more posts
    $checkQuery = "SELECT COUNT(*) as total FROM posts WHERE hidden = 0";
    $checkResult = $db->query($checkQuery);
    $totalRow = $checkResult->fetch_assoc();
    $totalPosts = (int)$totalRow['total'];
    $hasMore = ($offset + $limit) < $totalPosts;
    
    echo json_encode([
        'success' => true,
        'posts' => $processedPosts,
        'has_more' => $hasMore,
        'total' => $totalPosts
    ]);
    
} catch (Exception $e) {
    error_log("Load more posts error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
