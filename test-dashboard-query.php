<?php
// Test the dashboard query

require_once 'includes/db_helper.php';

echo "Testing dashboard query...\n\n";

$db = getDBConnection();

// Same query as dashboard.php
$query = "SELECT p.id, p.game, p.title, p.content, p.username, p.created_at, p.likes, p.comments,
                  ui.profile_picture, ui.exp_points 
                  FROM posts p 
                  LEFT JOIN user_info ui ON p.username = ui.username
                  WHERE (p.hidden IS NULL OR p.hidden = 0)
                  AND (ui.is_banned IS NULL OR ui.is_banned = 0)
                  ORDER BY p.created_at DESC";

echo "Query:\n$query\n\n";

$result = $db->query($query);

if ($result) {
    echo "✅ Query successful!\n";
    echo "Rows: " . $result->num_rows . "\n\n";
    
    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
        echo "First post:\n";
        print_r($post);
    }
} else {
    echo "❌ Query failed\n";
}
