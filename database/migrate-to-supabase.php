<?php
/**
 * MySQL to Supabase Data Migration Script
 * 
 * This script exports data from your local MySQL database
 * and imports it into Supabase (PostgreSQL).
 * 
 * Prerequisites:
 * 1. Run database/supabase-schema.sql in Supabase SQL Editor first
 * 2. Add SUPABASE_URL and SUPABASE_SERVICE_KEY to your .env file
 * 
 * Usage: php database/migrate-to-supabase.php
 */

// Display as plain text in browser
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain');
}

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║         EXPoints: MySQL → Supabase Migration Script           ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Load environment configuration
function loadEnv() {
    $envFile = __DIR__ . '/../.env';
    
    if (!file_exists($envFile)) {
        echo "❌ .env file not found! Please create one from .env.example\n";
        exit(1);
    }
    
    // Read file content and handle different encodings
    $content = file_get_contents($envFile);
    
    // Remove BOM if present
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
    
    // Split by various line endings
    $lines = preg_split('/\r\n|\r|\n/', $content);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Remove quotes if present
            $value = trim($value, '"\'');
            if (!empty($key) && !getenv($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

loadEnv();

// Configuration
$mysqlConfig = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') ?: '',
    'db'   => getenv('DB_NAME') ?: 'expoints_db'
];

$supabaseUrl = getenv('SUPABASE_URL');
$supabaseKey = getenv('SUPABASE_SERVICE_KEY'); // Use service role key for admin operations

if (empty($supabaseUrl) || empty($supabaseKey)) {
    echo "❌ Error: Supabase credentials not found in .env file!\n\n";
    echo "Please add these lines to your .env file:\n";
    echo "SUPABASE_URL=https://xxxxx.supabase.co\n";
    echo "SUPABASE_SERVICE_KEY=eyJhbGc...\n\n";
    exit(1);
}

echo "📋 Configuration:\n";
echo "   MySQL: {$mysqlConfig['host']}/{$mysqlConfig['db']}\n";
echo "   Supabase: " . parse_url($supabaseUrl, PHP_URL_HOST) . "\n\n";

// Helper function to call Supabase REST API
function supabaseRequest($method, $table, $data = null, $select = null) {
    global $supabaseUrl, $supabaseKey;
    
    $url = "$supabaseUrl/rest/v1/$table";
    if ($select) {
        $url .= "?select=$select";
    }
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $supabaseKey",
        "Authorization: Bearer $supabaseKey",
        "Content-Type: application/json",
        "Prefer: return=representation"
    ]);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    } else {
        echo "   ⚠️ Warning: HTTP $httpCode - $response\n";
        return false;
    }
}

// Connect to MySQL
echo "🔌 Connecting to MySQL...\n";
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysql = new mysqli(
        $mysqlConfig['host'],
        $mysqlConfig['user'],
        $mysqlConfig['pass'],
        $mysqlConfig['db']
    );
    
    $mysql->set_charset("utf8mb4");
    echo "✅ Connected to MySQL\n\n";
} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}

// Migration steps
$tables = [
    'users' => ['id', 'email', 'password', 'role', 'is_disabled', 'disabled_reason', 'disabled_at', 'disabled_by', 'created_at'],
    'user_info' => ['id', 'user_id', 'username', 'first_name', 'middle_name', 'last_name', 'suffix', 'bio', 'profile_picture', 'exp_points', 'is_banned', 'ban_reason', 'banned_at', 'banned_by', 'created_at'],
    'posts' => ['id', 'user_id', 'game', 'title', 'content', 'username', 'likes', 'comments', 'hidden', 'created_at', 'updated_at'],
    'post_likes' => ['id', 'post_id', 'user_id', 'created_at'],
    'post_comments' => ['id', 'parent_comment_id', 'post_id', 'user_id', 'username', 'comment', 'like_count', 'reply_count', 'created_at'],
    'comment_likes' => ['id', 'comment_id', 'user_id', 'created_at'],
    'post_bookmarks' => ['id', 'post_id', 'user_id', 'created_at'],
    'moderation_log' => ['id', 'post_id', 'moderator', 'action', 'reason', 'created_at'],
    'ban_reviews' => ['id', 'username', 'post_id', 'flagged_by', 'reason', 'status', 'reviewed_by', 'reviewed_at', 'created_at'],
    'comments' => ['id', 'post_id', 'username', 'text', 'created_at']
];

$totalMigrated = 0;

foreach ($tables as $table => $columns) {
    echo "📦 Migrating table: $table\n";
    
    // Fetch data from MySQL
    $result = $mysql->query("SELECT * FROM $table");
    
    if (!$result) {
        echo "   ⚠️  Table not found or empty, skipping...\n\n";
        continue;
    }
    
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    
    if (empty($rows)) {
        echo "   ℹ️  No data to migrate\n\n";
        continue;
    }
    
    echo "   Found " . count($rows) . " rows\n";
    
    // Insert into Supabase in batches
    $batchSize = 100;
    $batches = array_chunk($rows, $batchSize);
    $successCount = 0;
    
    foreach ($batches as $batchIndex => $batch) {
        echo "   Inserting batch " . ($batchIndex + 1) . "/" . count($batches) . "...";
        
        $response = supabaseRequest('POST', $table, $batch);
        
        if ($response !== false) {
            $count = is_array($response) ? count($response) : count($batch);
            $successCount += $count;
            echo " ✅ $count rows\n";
        } else {
            echo " ⚠️  Failed\n";
        }
    }
    
    echo "   ✅ Migrated $successCount rows from $table\n\n";
    $totalMigrated += $successCount;
}

$mysql->close();

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                    Migration Complete!                         ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "✅ Total rows migrated: $totalMigrated\n\n";

echo "🎯 Next Steps:\n";
echo "1. Go to Supabase → Table Editor to verify your data\n";
echo "2. Run: php database/test-supabase.php\n";
echo "3. Update your app to use Supabase (already done!)\n";
echo "4. Test all features in your application\n\n";

echo "💡 Tip: Your app will now use Supabase automatically!\n";
echo "   The Connection.php class detects Supabase credentials.\n\n";

echo "✨ Migration script finished.\n";
