<?php
/**
 * Database Backup Script
 * 
 * Creates a backup of the EXPoints database structure and data.
 * Useful before making major changes or testing new features.
 * 
 * Usage: php database/backup.php
 */

// Display as plain text in browser
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain');
}

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║              EXPoints Database Backup Script                   ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Load environment configuration
function loadEnv() {
    $envFile = __DIR__ . '/../.env';
    
    if (!file_exists($envFile)) {
        return;
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

$config = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') ?: '',
    'db'   => getenv('DB_NAME') ?: 'expoints_db'
];

echo "📋 Database: {$config['db']}\n";
echo "🖥️  Host: {$config['host']}\n\n";

// Create backups directory if it doesn't exist
$backupDir = __DIR__ . '/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
    echo "📁 Created backups directory\n";
}

// Generate filename with timestamp
$timestamp = date('Y-m-d_His');
$backupFile = $backupDir . "/expoints_backup_{$timestamp}.sql";

echo "💾 Creating backup...\n";
echo "📄 File: " . basename($backupFile) . "\n\n";

// Build mysqldump command
$command = sprintf(
    'mysqldump --host=%s --user=%s %s %s > "%s" 2>&1',
    escapeshellarg($config['host']),
    escapeshellarg($config['user']),
    $config['pass'] ? '--password=' . escapeshellarg($config['pass']) : '',
    escapeshellarg($config['db']),
    $backupFile
);

// Execute backup
exec($command, $output, $returnCode);

if ($returnCode === 0 && file_exists($backupFile)) {
    $fileSize = filesize($backupFile);
    $fileSizeKB = round($fileSize / 1024, 2);
    
    echo "✅ Backup completed successfully!\n";
    echo "📊 Size: {$fileSizeKB} KB\n";
    echo "📍 Location: {$backupFile}\n\n";
    
    // List recent backups
    echo "📚 Recent backups:\n";
    $backups = glob($backupDir . '/expoints_backup_*.sql');
    rsort($backups);
    $backups = array_slice($backups, 0, 5);
    
    foreach ($backups as $backup) {
        $size = round(filesize($backup) / 1024, 2);
        $date = date('Y-m-d H:i:s', filemtime($backup));
        echo "   • " . basename($backup) . " ({$size} KB) - {$date}\n";
    }
    
    echo "\n💡 To restore this backup, run:\n";
    echo "   mysql -u{$config['user']} ";
    if ($config['pass']) echo "-p ";
    echo "{$config['db']} < " . basename($backupFile) . "\n";
    
} else {
    echo "❌ Backup failed!\n";
    if (!empty($output)) {
        echo "Error output:\n";
        foreach ($output as $line) {
            echo "   $line\n";
        }
    }
    echo "\n💡 Make sure mysqldump is installed and in your PATH\n";
    exit(1);
}

echo "\n✨ Backup script finished.\n";
