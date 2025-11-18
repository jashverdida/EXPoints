<?php
/**
 * Database Helper - Provides Supabase-compatible connection
 * 
 * This helper automatically detects and uses Supabase when credentials are available,
 * otherwise falls back to MySQL.
 * 
 * Usage: $db = getDBConnection();
 */

function getDBConnection() {
    // Try to use the Connection class (Supabase-compatible)
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
        
        try {
            $conn = \EXPoints\Database\Connection::getInstance()->getConnection();
            return $conn;
        } catch (\Exception $e) {
            error_log("Connection class failed: " . $e->getMessage());
        }
    }
    
    // Fallback to direct MySQL connection
    $host = getenv('DB_HOST') ?: 'localhost';
    $dbname = getenv('DB_NAME') ?: 'expoints_db';
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASS') ?: '';
    
    try {
        $mysqli = new mysqli($host, $username, $password, $dbname);
        if ($mysqli->connect_error) {
            throw new Exception("Connection failed: " . $mysqli->connect_error);
        }
        $mysqli->set_charset('utf8mb4');
        return $mysqli;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}
