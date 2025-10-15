<?php

namespace EXPoints\Database;

class Connection {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $host = 'localhost';
        $user = 'root';
        $pass = '';
        $db = 'expoints_db';

        error_log("Attempting database connection - Host: $host, User: $user, Database: $db");
        
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable error reporting
            
            $this->conn = new \mysqli($host, $user, $pass, $db);
            
            if ($this->conn->connect_error) {
                error_log("MySQL Connection Error: " . $this->conn->connect_error);
                throw new \Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            error_log("Database connection successful!");
            
        } catch (\Exception $e) {
            error_log("Database Connection Exception: " . $e->getMessage());
            error_log("Connection Details - Host: $host, User: $user, Database: $db");
            throw new \Exception("Database connection failed: " . $e->getMessage());
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

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}