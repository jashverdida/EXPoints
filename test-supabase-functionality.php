<?php
// Test Supabase connection and post functionality

require_once 'includes/db_helper.php';

echo "====================================\n";
echo "Supabase Functionality Test\n";
echo "====================================\n\n";

// Test 1: Database Connection
echo "Test 1: Database Connection\n";
$db = getDBConnection();
if ($db) {
    echo "✅ Database connection successful\n\n";
} else {
    echo "❌ Database connection failed\n";
    exit(1);
}

// Test 2: Count Posts
echo "Test 2: Fetching Posts\n";
$result = $db->query("SELECT COUNT(*) as count FROM posts");
if ($result) {
    $row = $result->fetch_assoc();
    echo "✅ Found {$row['count']} posts\n\n";
} else {
    echo "❌ Failed to query posts\n\n";
}

// Test 3: Fetch Sample Post
echo "Test 3: Fetching Sample Post\n";
$result = $db->query("SELECT id, title, username, game FROM posts LIMIT 1");
if ($result && $result->num_rows > 0) {
    $post = $result->fetch_assoc();
    echo "✅ Sample post:\n";
    echo "   ID: {$post['id']}\n";
    echo "   Title: {$post['title']}\n";
    echo "   Author: {$post['username']}\n";
    echo "   Game: {$post['game']}\n\n";
} else {
    echo "❌ No posts found or query failed\n\n";
}

// Test 4: Count Likes
echo "Test 4: Fetching Likes\n";
$result = $db->query("SELECT COUNT(*) as count FROM post_likes");
if ($result) {
    $row = $result->fetch_assoc();
    echo "✅ Found {$row['count']} likes\n\n";
} else {
    echo "❌ Failed to query likes\n\n";
}

// Test 5: Count Comments
echo "Test 5: Fetching Comments\n";
$result = $db->query("SELECT COUNT(*) as count FROM post_comments");
if ($result) {
    $row = $result->fetch_assoc();
    echo "✅ Found {$row['count']} comments\n\n";
} else {
    echo "❌ Failed to query comments\n\n";
}

// Test 6: Count Users
echo "Test 6: Fetching Users\n";
$result = $db->query("SELECT COUNT(*) as count FROM users");
if ($result) {
    $row = $result->fetch_assoc();
    echo "✅ Found {$row['count']} users\n\n";
} else {
    echo "❌ Failed to query users\n\n";
}

echo "====================================\n";
echo "All Tests Complete!\n";
echo "====================================\n";
