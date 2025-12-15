<?php

namespace EXPoints\Database;

class Connection {
    private static $instance = null;
    private $conn;
    private $isSupabase = false;

    private function __construct() {
        // Load environment variables from .env file if it exists
        $this->loadEnv();
        
        // Check if Supabase credentials are available
        $supabaseUrl = getenv('SUPABASE_URL');
        $supabaseKey = getenv('SUPABASE_SERVICE_KEY');
        
        if ($supabaseUrl && $supabaseKey && !empty($supabaseUrl) && !empty($supabaseKey)) {
            // Use Supabase
            error_log("Using Supabase connection: " . $supabaseUrl);
            
            try {
                require_once __DIR__ . '/SupabaseConnection.php';
                $this->conn = new SupabaseConnection($supabaseUrl, $supabaseKey);
                $this->isSupabase = true;
                error_log("Supabase connection initialized successfully!");
            } catch (\Exception $e) {
                error_log("Supabase connection failed: " . $e->getMessage());
                // Fall back to MySQL
                $this->connectMySQL();
            }
        } else {
            // Use MySQL
            $this->connectMySQL();
        }
    }
    
    private function connectMySQL() {
        $host = getenv('DB_HOST') ?: 'localhost';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $db = getenv('DB_NAME') ?: 'expoints_db';

        error_log("Attempting MySQL connection - Host: $host, User: $user, Database: $db");
        
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable error reporting
            
            $this->conn = new \mysqli($host, $user, $pass, $db);
            
            if ($this->conn->connect_error) {
                error_log("MySQL Connection Error: " . $this->conn->connect_error);
                throw new \Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            $this->isSupabase = false;
            error_log("MySQL connection successful!");
            
        } catch (\Exception $e) {
            error_log("Database Connection Exception: " . $e->getMessage());
            error_log("Connection Details - Host: $host, User: $user, Database: $db");
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Load environment variables from .env file
     */
    private function loadEnv() {
        $envFile = __DIR__ . '/../../.env';
        
        if (!file_exists($envFile)) {
            return; // .env file not found, will use defaults
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse KEY=VALUE pairs
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                $value = trim($value, '"\'');
                
                // Set environment variable if not already set
                if (!getenv($key)) {
                    putenv("$key=$value");
                }
            }
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
    
    public function isSupabase() {
        return $this->isSupabase;
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}