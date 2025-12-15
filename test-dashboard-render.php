<?php
// Test dashboard with simulated session

session_start();
$_SESSION['authenticated'] = true;
$_SESSION['username'] = 'testuser';
$_SESSION['user_email'] = 'test@example.com';
$_SESSION['user_id'] = 1;

require_once 'includes/db_helper.php';

// Same logic as dashboard
$posts = [];
$db = getDBConnection();

if ($db) {
    $query = "SELECT * FROM posts WHERE hidden = 0 ORDER BY created_at DESC";
    $result = $db->query($query);
    
    if ($result) {
        while ($post = $result->fetch_assoc()) {
            // Get user info
            $userInfoStmt = $db->prepare("SELECT profile_picture, is_banned FROM user_info WHERE username = ?");
            $userInfoStmt->bind_param("s", $post['username']);
            $userInfoStmt->execute();
            $userInfoResult = $userInfoStmt->get_result();
            
            if ($userInfoResult && $userInfoResult->num_rows > 0) {
                $userInfo = $userInfoResult->fetch_assoc();
                if ($userInfo['is_banned']) {
                    $userInfoStmt->close();
                    continue;
                }
                $post['author_profile_picture'] = $userInfo['profile_picture'] ?: '../assets/img/cat1.jpg';
            } else {
                $post['author_profile_picture'] = '../assets/img/cat1.jpg';
            }
            $userInfoStmt->close();
            
            // Get comment count
            $commentsStmt = $db->prepare("SELECT id FROM post_comments WHERE post_id = ?");
            $commentsStmt->bind_param("i", $post['id']);
            $commentsStmt->execute();
            $commentsResult = $commentsStmt->get_result();
            $post['comment_count'] = $commentsResult ? $commentsResult->num_rows : 0;
            $commentsStmt->close();
            
            // Get like count
            $likesStmt = $db->prepare("SELECT id FROM post_likes WHERE post_id = ?");
            $likesStmt->bind_param("i", $post['id']);
            $likesStmt->execute();
            $likesResult = $likesStmt->get_result();
            $post['like_count'] = $likesResult ? $likesResult->num_rows : 0;
            $likesStmt->close();
            
            $posts[] = $post;
        }
    }
}

echo "Total posts loaded: " . count($posts) . "\n\n";
echo "Rendering HTML...\n\n";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Posts Test</title>
    <style>
        .card-post { border: 1px solid #ccc; padding: 15px; margin: 10px 0; }
        .post-header { display: flex; gap: 10px; margin-bottom: 10px; }
        .post-avatar { width: 40px; height: 40px; border-radius: 50%; }
        .handle { font-weight: bold; }
        .game-tag { background: #007bff; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px; }
        .title { margin: 10px 0; }
    </style>
</head>
<body>
    <h1>Dashboard Posts Preview</h1>
    
    <?php if (count($posts) > 0): ?>
        <?php foreach ($posts as $post): ?>
          <div class="card-post" data-post-id="<?php echo $post['id']; ?>">
            <div class="post-header">
              <img src="<?php echo htmlspecialchars($post['author_profile_picture']); ?>" alt="Profile" class="post-avatar">
              <div class="post-info">
                <span class="handle">@<?php echo htmlspecialchars($post['username']); ?></span>
                <span class="timestamp"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
              </div>
            </div>
            <div class="post-body">
              <span class="game-tag"><?php echo htmlspecialchars($post['game']); ?></span>
              <h3 class="title"><?php echo htmlspecialchars($post['title']); ?></h3>
              <p class="content"><?php echo htmlspecialchars($post['content']); ?></p>
            </div>
            <div class="post-actions">
              ❤️ <?php echo $post['like_count'] ?? 0; ?>
              💬 <?php echo $post['comment_count'] ?? 0; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No posts to display yet.</p>
      <?php endif; ?>
</body>
</html>
