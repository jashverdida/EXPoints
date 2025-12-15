<?php
/**
 * Test Supabase Connection
 * Run this to verify your .env configuration
 */

require_once 'config/env.php';

echo "🔍 Testing Supabase Configuration...\n\n";

$url = getenv('SUPABASE_URL');
$anonKey = getenv('SUPABASE_ANON_KEY');
$serviceKey = getenv('SUPABASE_SERVICE_KEY');

// Check if credentials are loaded
if ($url && $anonKey && $serviceKey) {
    echo "✅ SUPABASE_URL: " . $url . "\n";
    echo "✅ SUPABASE_ANON_KEY: " . substr($anonKey, 0, 30) . "...\n";
    echo "✅ SUPABASE_SERVICE_KEY: " . substr($serviceKey, 0, 30) . "...\n\n";
    echo "✅ Configuration loaded successfully!\n\n";
} else {
    echo "❌ Missing Supabase credentials in .env file\n";
    if (!$url) echo "   - SUPABASE_URL is missing\n";
    if (!$anonKey) echo "   - SUPABASE_ANON_KEY is missing\n";
    if (!$serviceKey) echo "   - SUPABASE_SERVICE_KEY is missing\n";
    exit(1);
}

// Test Supabase connection
echo "🔗 Testing Supabase API connection...\n";

try {
    require_once 'config/supabase.php';
    $supabase = new SupabaseService();
    echo "✅ SupabaseService initialized successfully!\n\n";
    echo "🎉 Everything is working! Ready to proceed with database setup.\n";
} catch (Exception $e) {
    echo "❌ Error initializing SupabaseService: " . $e->getMessage() . "\n";
    exit(1);
}
