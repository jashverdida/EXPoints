<?php
// Test Supabase connection and data
require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $supabase = app(\App\Services\SupabaseService::class);
    
    echo "<h1>Testing Supabase Connection</h1>";
    
    // Test configuration
    echo "<h2>Configuration:</h2>";
    echo "URL: " . config('supabase.url') . "<br>";
    echo "Has Anon Key: " . (config('supabase.anon_key') ? 'Yes' : 'No') . "<br>";
    echo "Has Service Key: " . (config('supabase.service_key') ? 'Yes' : 'No') . "<br><br>";
    
    // Test posts count
    echo "<h2>Posts Count:</h2>";
    $count = $supabase->count('posts');
    echo "Total posts: $count<br><br>";
    
    // Test fetching posts
    echo "<h2>Fetching Posts:</h2>";
    $posts = $supabase->select('posts', '*', [], ['order' => 'created_at.desc', 'limit' => 5]);
    echo "Fetched " . count($posts) . " posts<br>";
    echo "<pre>" . print_r($posts, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<h2 style='color:red'>Error:</h2>";
    echo $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
