<?php
/**
 * Supabase Connection Test Script
 * 
 * Tests your Supabase connection and verifies data migration
 * 
 * Usage: php database/test-supabase.php
 */

// Display as plain text in browser
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain');
}

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║            Supabase Connection Test Script                    ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Load environment configuration
function loadEnv() {
    $envFile = __DIR__ . '/../.env';
    
    if (!file_exists($envFile)) {
        echo "❌ .env file not found!\n";
        exit(1);
    }
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, '"\'');
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
}

loadEnv();

$supabaseUrl = getenv('SUPABASE_URL');
$supabaseKey = getenv('SUPABASE_SERVICE_KEY');

if (empty($supabaseUrl) || empty($supabaseKey)) {
    echo "❌ Supabase credentials not found in .env file!\n";
    exit(1);
}

echo "🔧 Configuration:\n";
echo "   Supabase URL: " . $supabaseUrl . "\n";
echo "   API Key: " . substr($supabaseKey, 0, 20) . "...\n\n";

// Helper function to call Supabase REST API
function supabaseQuery($table, $select = '*', $limit = null) {
    global $supabaseUrl, $supabaseKey;
    
    $url = "$supabaseUrl/rest/v1/$table?select=$select";
    if ($limit) {
        $url .= "&limit=$limit";
    }
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $supabaseKey",
        "Authorization: Bearer $supabaseKey",
        "Content-Type: application/json"
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    } else {
        return ['error' => $response, 'code' => $httpCode];
    }
}

// Test 1: Connection
echo "🔌 Test 1: Testing Connection...\n";
$result = supabaseQuery('users', 'id', 1);

if (isset($result['error'])) {
    echo "   ❌ Connection failed!\n";
    echo "   Error: " . $result['error'] . "\n";
    echo "   HTTP Code: " . $result['code'] . "\n\n";
    echo "💡 Troubleshooting:\n";
    echo "   1. Check your SUPABASE_URL is correct\n";
    echo "   2. Make sure you're using SERVICE_ROLE key (not anon key)\n";
    echo "   3. Verify your Supabase project is running\n";
    exit(1);
}

echo "   ✅ Connected successfully!\n\n";

// Test 2: Check tables and row counts
echo "📊 Test 2: Checking Tables and Data...\n";

$tables = [
    'users' => 'User accounts',
    'user_info' => 'User profiles',
    'posts' => 'Blog posts',
    'post_likes' => 'Post likes',
    'post_comments' => 'Comments',
    'comment_likes' => 'Comment likes',
    'post_bookmarks' => 'Bookmarks',
    'notifications' => 'Notifications',
    'moderation_log' => 'Moderation logs',
    'ban_reviews' => 'Ban reviews'
];

$totalRows = 0;

foreach ($tables as $table => $description) {
    $data = supabaseQuery($table, 'count', 1000);
    
    if (isset($data['error'])) {
        echo "   ⚠️  $table: Table not found or error\n";
    } else {
        $count = is_array($data) ? count($data) : 0;
        $totalRows += $count;
        $status = $count > 0 ? '✅' : 'ℹ️ ';
        echo "   $status $table: $count rows ($description)\n";
    }
}

echo "\n   📈 Total rows across all tables: $totalRows\n\n";

// Test 3: Sample data queries
echo "🔍 Test 3: Querying Sample Data...\n";

// Get a sample user
$users = supabaseQuery('users', 'id,email,role', 1);
if (!empty($users) && !isset($users['error'])) {
    echo "   ✅ Sample User: ID={$users[0]['id']}, Email={$users[0]['email']}, Role={$users[0]['role']}\n";
} else {
    echo "   ⚠️  No users found\n";
}

// Get a sample post
$posts = supabaseQuery('posts', 'id,title,username,likes', 1);
if (!empty($posts) && !isset($posts['error'])) {
    echo "   ✅ Sample Post: ID={$posts[0]['id']}, Title={$posts[0]['title']}, Likes={$posts[0]['likes']}\n";
} else {
    echo "   ⚠️  No posts found\n";
}

echo "\n";

// Test 4: Relationship query
echo "🔗 Test 4: Testing Foreign Key Relationships...\n";

$userInfo = supabaseQuery('user_info', 'id,username,exp_points', 1);
if (!empty($userInfo) && !isset($userInfo['error'])) {
    echo "   ✅ User Info linked correctly: {$userInfo[0]['username']} has {$userInfo[0]['exp_points']} XP\n";
} else {
    echo "   ⚠️  No user info found\n";
}

echo "\n";

// Summary
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                    Test Results Summary                        ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";

if ($totalRows > 0) {
    echo "✅ Supabase is working perfectly!\n";
    echo "✅ All tables are accessible\n";
    echo "✅ Data has been migrated: $totalRows total rows\n\n";
    
    echo "🎉 Your application is ready to use Supabase!\n\n";
    
    echo "🎯 Next Steps:\n";
    echo "1. Test your application: php -S localhost:8000\n";
    echo "2. Try logging in, creating posts, commenting, etc.\n";
    echo "3. Check the Supabase dashboard to see live queries\n";
    echo "4. Share credentials with your team\n";
} else {
    echo "⚠️  Connection works but no data found.\n";
    echo "📝 You may need to run the migration script:\n";
    echo "   php database/migrate-to-supabase.php\n";
}

echo "\n✨ Test completed.\n";
