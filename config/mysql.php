<?php
namespace EXPoints\Database;

class MySQLConnection {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $host = 'localhost';
        $dbname = 'expoints';
        $username = 'root';
        $password = '';
        
        $this->connection = new \mysqli($host, $username, $password, $dbname);
        
        if ($this->connection->connect_error) {
            throw new \Exception("Connection failed: " . $this->connection->connect_error);
        }
        
        $this->connection->set_charset('utf8mb4');
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    private function __clone() {}
    
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }
}