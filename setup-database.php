<?php
session_start();
require_once 'config/firestore.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>EXPoints Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        .success { color: #28a745; } .error { color: #dc3545; } .info { color: #17a2b8; }
        .collection { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; }
        code { background: #e9ecef; padding: 2px 4px; border-radius: 3px; }
    </style>
</head>
<body>

<h1>🎮 EXPoints Firestore Database Setup</h1>
<p>Creating collections and sample data for your gaming review platform...</p>

<?php
try {
    $firestoreService = new FirestoreService();
    
    echo '<div class="collection">';
    echo '<h2>👥 Creating Users Collection</h2>';
    
    // Create sample users
    $sampleUsers = [
        [
            'uid' => 'user_gaming_master_001',
            'data' => [
                'email' => 'gamingmaster@example.com',
                'displayName' => 'Gaming Master',
                'avatar' => 'cat1.jpg'
            ]
        ],
        [
            'uid' => 'user_retro_gamer_002', 
            'data' => [
                'email' => 'retrogamer@example.com',
                'displayName' => 'Retro Gamer',
                'avatar' => 'beluga.jpg'
            ]
        ],
        [
            'uid' => 'user_indie_lover_003',
            'data' => [
                'email' => 'indielover@example.com', 
                'displayName' => 'Indie Game Lover',
                'avatar' => 'lara.jpg'
            ]
        ]
    ];
    
    foreach ($sampleUsers as $user) {
        $result = $firestoreService->createUser($user['uid'], $user['data']);
        if ($result['success']) {
            echo '<span class="success">✅ Created user: ' . htmlspecialchars($user['data']['displayName']) . '</span><br>';
        } else {
            echo '<span class="error">❌ Failed to create user: ' . htmlspecialchars($result['error']) . '</span><br>';
        }
    }
    echo '</div>';
    
    echo '<div class="collection">';
    echo '<h2>📝 Creating Reviews Collection</h2>';
    
    // Create sample reviews
    $sampleReviews = [
        [
            'userId' => 'user_gaming_master_001',
            'gameTitle' => 'The Legend of Zelda: Breath of the Wild',
            'rating' => 5,
            'content' => 'Absolutely incredible open-world adventure! The freedom of exploration and creative problem-solving makes this a masterpiece. Every corner of Hyrule tells a story.',
            'platform' => 'Nintendo Switch'
        ],
        [
            'userId' => 'user_retro_gamer_002',
            'gameTitle' => 'Hollow Knight',
            'rating' => 5,
            'content' => 'Beautiful hand-drawn art style with challenging but fair gameplay. The atmosphere is incredibly immersive and the boss fights are legendary.',
            'platform' => 'PC'
        ],
        [
            'userId' => 'user_indie_lover_003',
            'gameTitle' => 'Hades',
            'rating' => 4,
            'content' => 'Fantastic roguelike with amazing storytelling. Each run feels meaningful and the character development is top-notch.',
            'platform' => 'PlayStation 5'
        ],
        [
            'userId' => 'user_gaming_master_001',
            'gameTitle' => 'Red Dead Redemption 2',
            'rating' => 5,
            'content' => 'An absolute masterpiece of storytelling and world-building. The attention to detail is unmatched and the characters feel real.',
            'platform' => 'Xbox Series X'
        ],
        [
            'userId' => 'user_retro_gamer_002',
            'gameTitle' => 'Celeste',
            'rating' => 4,
            'content' => 'Challenging platformer with a beautiful message about mental health. The mechanics are tight and the music is phenomenal.',
            'platform' => 'PC'
        ]
    ];
    
    $reviewIds = [];
    foreach ($sampleReviews as $review) {
        $result = $firestoreService->createReview($review);
        if ($result['success']) {
            $reviewIds[] = $result['reviewId'];
            echo '<span class="success">✅ Created review: ' . htmlspecialchars($review['gameTitle']) . ' (Rating: ' . $review['rating'] . '/5)</span><br>';
        } else {
            echo '<span class="error">❌ Failed to create review: ' . htmlspecialchars($result['error']) . '</span><br>';
        }
    }
    echo '</div>';
    
    echo '<div class="collection">';
    echo '<h2>💬 Creating Comments Collection</h2>';
    
    // Create sample comments for the first few reviews
    if (count($reviewIds) > 0) {
        $sampleComments = [
            [
                'reviewId' => $reviewIds[0],
                'userId' => 'user_retro_gamer_002',
                'content' => 'Totally agree! The exploration in this game is unmatched. I spent hours just climbing mountains!'
            ],
            [
                'reviewId' => $reviewIds[0],
                'userId' => 'user_indie_lover_003', 
                'content' => 'The physics engine in this game is so satisfying. Every solution feels earned.'
            ],
            [
                'reviewId' => $reviewIds[1],
                'userId' => 'user_gaming_master_001',
                'content' => 'Hollow Knight really is a masterclass in atmospheric design. The music gives me chills!'
            ],
            [
                'reviewId' => $reviewIds[2],
                'userId' => 'user_gaming_master_001',
                'content' => 'The voice acting in Hades is incredible. Every character feels unique and memorable.'
            ]
        ];
        
        foreach ($sampleComments as $comment) {
            $result = $firestoreService->addComment($comment['reviewId'], $comment['userId'], $comment['content']);
            if ($result['success']) {
                echo '<span class="success">✅ Added comment by user: ' . htmlspecialchars($comment['userId']) . '</span><br>';
            } else {
                echo '<span class="error">❌ Failed to add comment: ' . htmlspecialchars($result['error']) . '</span><br>';
            }
        }
    }
    echo '</div>';
    
    echo '<div class="collection">';
    echo '<h2>❤️ Creating Likes Collection</h2>';
    
    // Create sample likes for reviews
    if (count($reviewIds) > 0) {
        $sampleLikes = [
            ['reviewId' => $reviewIds[0], 'userId' => 'user_retro_gamer_002'],
            ['reviewId' => $reviewIds[0], 'userId' => 'user_indie_lover_003'],
            ['reviewId' => $reviewIds[1], 'userId' => 'user_gaming_master_001'],
            ['reviewId' => $reviewIds[1], 'userId' => 'user_indie_lover_003'],
            ['reviewId' => $reviewIds[2], 'userId' => 'user_gaming_master_001']
        ];
        
        foreach ($sampleLikes as $like) {
            $result = $firestoreService->likeReview($like['reviewId'], $like['userId']);
            if ($result['success']) {
                echo '<span class="success">✅ Added like from user: ' . htmlspecialchars($like['userId']) . '</span><br>';
            } else {
                echo '<span class="info">ℹ️ Like result: ' . htmlspecialchars($result['error']) . '</span><br>';
            }
        }
    }
    echo '</div>';
    
    echo '<hr>';
    echo '<h2>🎯 Database Setup Complete!</h2>';
    echo '<p><strong>Now check your Firebase Console:</strong></p>';
    echo '<ul>';
    echo '<li>Go to <strong>Firestore Database → Data</strong></li>';
    echo '<li>You should see these collections:</li>';
    echo '<ul>';
    echo '<li><code>users</code> - 3 sample users with profiles</li>';
    echo '<li><code>reviews</code> - 5 game reviews with ratings</li>';
    echo '<li><code>comments</code> - Comments on reviews</li>';
    echo '<li><code>likes</code> - Like tracking system</li>';
    echo '</ul>';
    echo '</ul>';
    
    echo '<h3>🚀 Next Steps:</h3>';
    echo '<ol>';
    echo '<li>Visit your <strong>Firebase Console</strong> to see the collections</li>';
    echo '<li>Test the <strong>EXPoints dashboard</strong>: <a href="../dashboard.php">dashboard.php</a></li>';
    echo '<li>Try <strong>registering</strong> a new user: <a href="../register.php">register.php</a></li>';
    echo '</ol>';
    
} catch (Exception $e) {
    echo '<div class="error">';
    echo '<h2>❌ Database Setup Error</h2>';
    echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>Possible causes:</strong></p>';
    echo '<ul>';
    echo '<li>Firebase service account key not configured</li>';
    echo '<li>Firestore database not created yet</li>';
    echo '<li>Project ID mismatch</li>';
    echo '<li>Network connectivity issues</li>';
    echo '</ul>';
    echo '<p><strong>Make sure:</strong></p>';
    echo '<ol>';
    echo '<li>Firestore database is created in Firebase Console</li>';
    echo '<li>Service account key is at: <code>config/firebase-service-account.json</code></li>';
    echo '<li>Project ID matches in the service account JSON</li>';
    echo '</ol>';
    echo '</div>';
}
?>

</body>
</html>
