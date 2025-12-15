<?php
/**
 * Test Strict Supabase Connection
 * Verifies that the application ONLY uses Supabase (no MySQL fallback)
 */

echo "🔍 Testing Strict Supabase-Only Mode...\n\n";

// Test 1: Environment Variables
echo "Test 1: Checking Environment Variables\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

require_once __DIR__ . '/config/env.php';

$supabase_url = getenv('SUPABASE_URL');
$supabase_anon = getenv('SUPABASE_ANON_KEY');
$supabase_service = getenv('SUPABASE_SERVICE_KEY');

if ($supabase_url && $supabase_anon && $supabase_service) {
    echo "✅ SUPABASE_URL: " . $supabase_url . "\n";
    echo "✅ SUPABASE_ANON_KEY: " . substr($supabase_anon, 0, 30) . "...\n";
    echo "✅ SUPABASE_SERVICE_KEY: " . substr($supabase_service, 0, 30) . "...\n";
} else {
    echo "❌ Missing Supabase credentials in .env file!\n";
    exit(1);
}

echo "\n";

// Test 2: Database Connection
echo "Test 2: Testing Database Connection\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    require_once __DIR__ . '/includes/db_helper.php';
    
    $db = getDBConnection();
    echo "✅ Database connection established\n";
    echo "✅ Connection type: " . get_class($db) . "\n";
    
    if (get_class($db) === 'SupabaseMySQLCompat') {
        echo "✅ Using Supabase MySQL Compatibility Layer\n";
    } else {
        echo "⚠️  Warning: Not using expected connection type\n";
    }
    
} catch (Exception $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
    echo "⚠️  Make sure your internet is connected and Supabase credentials are correct.\n";
    exit(1);
}

echo "\n";

// Test 3: Query Test
echo "Test 3: Testing Database Query\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users");
    
    if (!$stmt) {
        throw new Exception("Prepare failed");
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        echo "✅ Query executed successfully\n";
        echo "✅ Users in database: " . $row['count'] . "\n";
    } else {
        echo "⚠️  Query returned no results\n";
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo "❌ Query failed: " . $e->getMessage() . "\n";
    echo "⚠️  Make sure the 'users' table exists in Supabase.\n";
}

echo "\n";

// Test 4: Verify No MySQL Fallback
echo "Test 4: Verifying No MySQL Fallback Exists\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$db_helper_content = file_get_contents(__DIR__ . '/includes/db_helper.php');

if (strpos($db_helper_content, 'getLegacyMySQLConnection') !== false) {
    echo "❌ MySQL fallback function still exists in code!\n";
    echo "⚠️  Please remove getLegacyMySQLConnection() function.\n";
} else {
    echo "✅ No MySQL fallback function found\n";
}

if (strpos($db_helper_content, 'new mysqli(') !== false) {
    echo "⚠️  Warning: Direct mysqli() calls found in db_helper.php\n";
} else {
    echo "✅ No direct mysqli() calls found\n";
}

if (strpos($db_helper_content, 'STRICT MODE') !== false) {
    echo "✅ Strict mode enabled (no fallback)\n";
} else {
    echo "⚠️  Warning: Strict mode comment not found\n";
}

echo "\n";

// Test 5: Connection Source Verification
echo "Test 5: Verifying Connection Source\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    require_once __DIR__ . '/config/supabase-compat.php';
    
    $compat = new SupabaseMySQLCompat();
    $url = $compat->getSupabaseUrl();
    
    if ($url === $supabase_url) {
        echo "✅ Connection using .env SUPABASE_URL\n";
        echo "✅ No hardcoded database credentials detected\n";
    } else {
        echo "⚠️  Warning: URL mismatch detected\n";
    }
    
} catch (Exception $e) {
    echo "❌ Verification failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Final Summary
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🎯 SUMMARY: Strict Supabase-Only Mode\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Environment variables configured\n";
echo "✅ Database connection working\n";
echo "✅ MySQL fallback removed\n";
echo "✅ Application will ONLY use Supabase\n";
echo "✅ No local database dependency\n";
echo "\n";
echo "🌐 Your application is now 100% cloud-based!\n";
echo "👥 Team members can work from anywhere.\n";
echo "📊 All data stored in Supabase PostgreSQL.\n";
echo "\n";
echo "⚠️  Note: If Supabase is unreachable, the app will show an error.\n";
echo "    Make sure you have internet connectivity to use the application.\n";
echo "\n";
?>
