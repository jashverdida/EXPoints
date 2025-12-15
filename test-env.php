<?php
// Quick test to check if .env is being read correctly

function loadEnv() {
    $envFile = __DIR__ . '/.env';
    
    if (!file_exists($envFile)) {
        echo "❌ .env file not found at: $envFile\n";
        return;
    }
    
    echo "✅ .env file found at: $envFile\n\n";
    
    // Read entire file content
    $content = file_get_contents($envFile);
    
    // Remove BOM if present
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
    
    // Split by various line endings
    $lines = preg_split('/\r\n|\r|\n/', $content);
    
    echo "Reading .env file...\n";
    echo "Found " . count($lines) . " lines\n";
    echo "==================\n";
    
    foreach ($lines as $lineNum => $line) {
        $line = trim($line);
        
        // Skip empty lines and comments
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE pairs
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            // Set environment variable
            if (!empty($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                
                // Show what we found (mask sensitive data)
                if (strpos($key, 'SUPABASE') !== false) {
                    if (strpos($key, 'URL') !== false) {
                        echo "Line " . ($lineNum + 1) . ": $key = $value\n";
                    } else {
                        $masked = strlen($value) > 20 ? substr($value, 0, 20) . '...' : $value;
                        echo "Line " . ($lineNum + 1) . ": $key = $masked\n";
                    }
                } elseif (strpos($key, 'DB_') !== false) {
                    echo "Line " . ($lineNum + 1) . ": $key = " . ($value ?: '(empty)') . "\n";
                }
            }
        }
    }
    
    echo "\n==================\n";
    echo "Testing getenv():\n";
    echo "==================\n";
    
    $supabaseUrl = getenv('SUPABASE_URL');
    $supabaseKey = getenv('SUPABASE_SERVICE_KEY');
    
    if ($supabaseUrl) {
        echo "✅ SUPABASE_URL: $supabaseUrl\n";
    } else {
        echo "❌ SUPABASE_URL: Not found\n";
    }
    
    if ($supabaseKey) {
        $masked = substr($supabaseKey, 0, 20) . '...';
        echo "✅ SUPABASE_SERVICE_KEY: $masked\n";
    } else {
        echo "❌ SUPABASE_SERVICE_KEY: Not found\n";
    }
}

loadEnv();

echo "\n🎯 Summary:\n";
echo "===========\n";

if (getenv('SUPABASE_URL') && getenv('SUPABASE_SERVICE_KEY')) {
    echo "✅ Supabase credentials are properly configured!\n";
    echo "✅ You can now run: php database/migrate-to-supabase.php\n";
} else {
    echo "❌ Supabase credentials are missing or incorrectly formatted.\n";
    echo "\n💡 Make sure your .env file has:\n";
    echo "SUPABASE_URL=https://your-project.supabase.co\n";
    echo "SUPABASE_SERVICE_KEY=eyJhbGc...\n";
    echo "\nMake sure there are NO spaces around the = sign!\n";
}
