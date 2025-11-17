<?php
/**
 * Database Installer Script
 * 
 * This script automatically sets up the complete EXPoints database schema.
 * Run this once when setting up a new development environment.
 * 
 * Usage: Run from command line: php database/install.php
 *        Or access via browser: http://localhost/database/install.php
 */

// Prevent accidental re-runs in production
$env = getenv('APP_ENV') ?: 'development';
if ($env === 'production') {
    die("⛔ Installation script is disabled in production environment.\n");
}

// Display as plain text in browser
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain');
}

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║           EXPoints Database Installation Script               ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Load configuration
require_once __DIR__ . '/../vendor/autoload.php';

// Database configuration - update these or use environment variables
$config = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') ?: '',
    'db'   => getenv('DB_NAME') ?: 'expoints_db'
];

echo "📋 Configuration:\n";
echo "   Host: {$config['host']}\n";
echo "   User: {$config['user']}\n";
echo "   Database: {$config['db']}\n\n";

// Step 1: Connect to MySQL (without database selection)
echo "🔌 Step 1: Connecting to MySQL server...\n";
try {
    $conn = new mysqli($config['host'], $config['user'], $config['pass']);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "✅ Connected to MySQL server successfully!\n\n";
} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}

// Step 2: Create database if it doesn't exist
echo "🗄️  Step 2: Creating database '{$config['db']}' if it doesn't exist...\n";
try {
    $sql = "CREATE DATABASE IF NOT EXISTS `{$config['db']}` 
            CHARACTER SET utf8mb4 
            COLLATE utf8mb4_unicode_ci";
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ Database ready!\n\n";
    } else {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    die("❌ Error creating database: " . $e->getMessage() . "\n");
}

// Step 3: Select the database
echo "📂 Step 3: Selecting database...\n";
if (!$conn->select_db($config['db'])) {
    die("❌ Error selecting database: " . $conn->error . "\n");
}
echo "✅ Database selected!\n\n";

// Step 4: Read and execute schema file
echo "📜 Step 4: Loading schema from complete-schema.sql...\n";
$schemaFile = __DIR__ . '/complete-schema.sql';

if (!file_exists($schemaFile)) {
    die("❌ Schema file not found: $schemaFile\n");
}

$schema = file_get_contents($schemaFile);
echo "✅ Schema file loaded!\n\n";

// Step 5: Execute SQL statements
echo "⚙️  Step 5: Creating tables...\n\n";

// Split by semicolon and execute each statement
$statements = array_filter(
    array_map('trim', explode(';', $schema)),
    function($stmt) {
        // Filter out empty statements and comments
        return !empty($stmt) && 
               !preg_match('/^\s*--/', $stmt) && 
               !preg_match('/^\s*\/\*/', $stmt);
    }
);

$successCount = 0;
$errorCount = 0;
$errors = [];

foreach ($statements as $index => $statement) {
    // Extract table name for better output
    if (preg_match('/CREATE TABLE.*?`(\w+)`/i', $statement, $matches)) {
        $tableName = $matches[1];
        echo "   Creating table: $tableName... ";
        
        if ($conn->query($statement)) {
            echo "✅\n";
            $successCount++;
        } else {
            // Check if error is "table already exists" (warning, not fatal)
            if (strpos($conn->error, 'already exists') !== false) {
                echo "⚠️  (already exists)\n";
                $successCount++;
            } else {
                echo "❌\n";
                $errorCount++;
                $errors[] = "Table $tableName: " . $conn->error;
            }
        }
    } else if (preg_match('/ALTER TABLE/i', $statement)) {
        // Silent execution for ALTER statements
        $conn->query($statement);
    }
}

echo "\n";

// Step 6: Verify tables
echo "🔍 Step 6: Verifying installation...\n";
$result = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

echo "✅ Found " . count($tables) . " tables:\n";
foreach ($tables as $table) {
    echo "   • $table\n";
}
echo "\n";

// Step 7: Summary
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                    Installation Summary                        ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "✅ Successful operations: $successCount\n";
if ($errorCount > 0) {
    echo "❌ Errors: $errorCount\n";
    foreach ($errors as $error) {
        echo "   • $error\n";
    }
} else {
    echo "❌ Errors: 0\n";
}
echo "\n";

if ($errorCount === 0) {
    echo "🎉 Database installation completed successfully!\n";
    echo "🚀 You can now start using the EXPoints system.\n";
    echo "\n";
    echo "📝 Next steps:\n";
    echo "   1. Copy .env.example to .env and configure your settings\n";
    echo "   2. Create your first admin user\n";
    echo "   3. Start the development server\n";
} else {
    echo "⚠️  Installation completed with errors. Please review the errors above.\n";
}

$conn->close();

echo "\n✨ Installation script finished.\n";
