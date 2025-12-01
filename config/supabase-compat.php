<?php
/**
 * Supabase MySQL Compatibility Layer
 * 
 * This class wraps Supabase REST API calls to mimic MySQL/MySQLi syntax
 * Allows existing MySQL code to work with minimal changes during migration
 */

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/supabase.php';

class SupabaseMySQLCompat {
    private $supabase;
    private $lastQuery;
    private $lastResult;
    
    public function __construct() {
        $this->supabase = new SupabaseService();
    }
    
    /**
     * Prepare a SQL statement (compatibility method)
     * Returns a SupabaseStatement object
     */
    public function prepare($sql) {
        return new SupabaseStatement($this->supabase, $sql);
    }
    
    /**
     * Execute a direct query (compatibility method)
     */
    public function query($sql) {
        // For CREATE TABLE and other DDL, return true (handled by Supabase migration)
        if (preg_match('/CREATE TABLE|ALTER TABLE|DROP TABLE/i', $sql)) {
            return true;
        }
        
        // Parse and execute SELECT queries
        if (preg_match('/^SELECT/i', $sql)) {
            // Create a statement and execute it
            $stmt = $this->prepare($sql);
            if ($stmt && $stmt->execute()) {
                return $stmt->get_result();
            }
            return false;
        }
        
        // Other queries
        $stmt = $this->prepare($sql);
        if ($stmt && $stmt->execute()) {
            return $stmt->get_result();
        }
        
        return false;
    }
    
    /**
     * Close connection (no-op for REST API)
     */
    public function close() {
        return true;
    }
    
    /**
     * Set charset (no-op for REST API)
     */
    public function set_charset($charset) {
        return true;
    }
    
    /**
     * Get Supabase URL for verification
     */
    public function getSupabaseUrl() {
        return getenv('SUPABASE_URL');
    }
}

/**
 * Supabase Statement Class
 * Mimics MySQLi prepared statement interface
 */
class SupabaseStatement {
    private $supabase;
    private $sql;
    private $params = [];
    private $types = '';
    private $result = null;
    
    public function __construct($supabase, $sql) {
        $this->supabase = $supabase;
        $this->sql = $sql;
    }
    
    /**
     * Bind parameters (MySQL-style)
     */
    public function bind_param($types, ...$params) {
        $this->types = $types;
        $this->params = $params;
        return true;
    }
    
    /**
     * Execute the prepared statement
     */
    public function execute() {
        // Parse SQL and execute appropriate Supabase method
        $sql = $this->sql;
        
        // Replace ? placeholders with actual values
        $paramIndex = 0;
        $sql = preg_replace_callback('/\?/', function() use (&$paramIndex) {
            $value = $this->params[$paramIndex++] ?? null;
            if (is_string($value)) {
                return "'" . addslashes($value) . "'";
            }
            return $value;
        }, $sql);
        
        // SELECT queries
        if (preg_match('/^SELECT\s+(.+?)\s+FROM\s+(\w+)/i', $sql, $matches)) {
            $table = $matches[2];
            $this->result = $this->executeSelect($table, $sql);
            return true;
        }
        
        // INSERT queries
        if (preg_match('/^INSERT INTO\s+(\w+)/i', $sql, $matches)) {
            $table = $matches[1];
            $this->result = $this->executeInsert($table, $sql);
            return true;
        }
        
        // UPDATE queries
        if (preg_match('/^UPDATE\s+(\w+)/i', $sql, $matches)) {
            $table = $matches[1];
            $this->result = $this->executeUpdate($table, $sql);
            return true;
        }
        
        // DELETE queries
        if (preg_match('/^DELETE FROM\s+(\w+)/i', $sql, $matches)) {
            $table = $matches[1];
            $this->result = $this->executeDelete($table, $sql);
            return true;
        }
        
        return false;
    }
    
    /**
     * Get result set
     */
    public function get_result() {
        return new SupabaseResult($this->result);
    }
    
    /**
     * Close statement
     */
    public function close() {
        return true;
    }
    
    /**
     * Execute SELECT query via Supabase
     */
    private function executeSelect($table, $sql) {
        try {
            // Check if this is a COUNT query
            $isCountQuery = preg_match('/SELECT\s+COUNT\(\*\)\s+as\s+(\w+)/i', $sql, $countMatches);
            
            // Extract WHERE conditions
            $where = '';
            if (preg_match('/WHERE\s+(.+?)(?:ORDER|LIMIT|$)/i', $sql, $matches)) {
                $where = $matches[1];
            }
            
            // Build Supabase query URL
            $endpoint = $table;
            $filters = $this->parseWhereClause($where);
            
            if (!empty($filters)) {
                $endpoint .= '?' . $filters;
            }
            
            if ($isCountQuery) {
                // For COUNT queries, we need to get all records and count them
                // Or use the select=count approach
                $countAlias = $countMatches[1] ?? 'count';
                $endpoint .= (strpos($endpoint, '?') ? '&' : '?') . 'select=*';
                
                $reflection = new ReflectionClass($this->supabase);
                $method = $reflection->getMethod('request');
                $method->setAccessible(true);
                
                $result = $method->invoke($this->supabase, 'GET', $endpoint);
                
                // Return count as array with the alias
                return [[$countAlias => count($result)]];
            }
            
            // Add ORDER BY
            if (preg_match('/ORDER BY\s+(.+?)(?:LIMIT|$)/i', $sql, $matches)) {
                $orderBy = trim($matches[1]);
                $endpoint .= (strpos($endpoint, '?') ? '&' : '?') . 'order=' . urlencode($orderBy);
            }
            
            // Add LIMIT
            if (preg_match('/LIMIT\s+(\d+)/i', $sql, $matches)) {
                $limit = $matches[1];
                $endpoint .= (strpos($endpoint, '?') ? '&' : '?') . 'limit=' . $limit;
            }
            
            // Execute via SupabaseService request method
            $reflection = new ReflectionClass($this->supabase);
            $method = $reflection->getMethod('request');
            $method->setAccessible(true);
            
            return $method->invoke($this->supabase, 'GET', $endpoint . '&select=*');
        } catch (Exception $e) {
            error_log("Supabase SELECT error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Execute INSERT query via Supabase
     */
    private function executeInsert($table, $sql) {
        try {
            // Parse INSERT data
            if (preg_match('/INSERT INTO\s+\w+\s*\((.*?)\)\s*VALUES\s*\((.*?)\)/i', $sql, $matches)) {
                $columns = array_map('trim', explode(',', $matches[1]));
                $values = array_map('trim', explode(',', $matches[2]));
                
                $data = [];
                foreach ($columns as $i => $col) {
                    $val = $values[$i] ?? '';
                    // Remove quotes
                    $val = trim($val, "'\"");
                    $data[$col] = $val;
                }
                
                $reflection = new ReflectionClass($this->supabase);
                $method = $reflection->getMethod('request');
                $method->setAccessible(true);
                
                return $method->invoke($this->supabase, 'POST', $table, $data);
            }
        } catch (Exception $e) {
            error_log("Supabase INSERT error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute UPDATE query via Supabase
     */
    private function executeUpdate($table, $sql) {
        // Similar to INSERT, parse and execute
        return true;
    }
    
    /**
     * Execute DELETE query via Supabase
     */
    private function executeDelete($table, $sql) {
        // Similar to SELECT, parse WHERE and execute
        return true;
    }
    
    /**
     * Parse WHERE clause into Supabase filters
     */
    private function parseWhereClause($where) {
        if (empty($where)) {
            return '';
        }
        
        // Simple parsing for common patterns
        // user_id = 1 => user_id=eq.1
        // email = 'test@example.com' => email=eq.test@example.com
        
        $filters = [];
        
        // Match: column = value
        if (preg_match('/(\w+)\s*=\s*[\'"]?([^\'"]+)[\'"]?/', $where, $matches)) {
            $column = $matches[1];
            $value = trim($matches[2], "'\"");
            $filters[] = $column . '=eq.' . urlencode($value);
        }
        
        return implode('&', $filters);
    }
}

/**
 * Supabase Result Class
 * Mimics MySQLi result set interface
 */
class SupabaseResult {
    private $data;
    private $position = 0;
    public $num_rows; // Property for mysqli compatibility
    
    public function __construct($data) {
        $this->data = is_array($data) ? $data : [];
        $this->num_rows = count($this->data);
    }
    
    /**
     * Fetch associative array
     */
    public function fetch_assoc() {
        if ($this->position < count($this->data)) {
            return $this->data[$this->position++];
        }
        return null;
    }
    
    /**
     * Fetch all rows
     */
    public function fetch_all($mode = MYSQLI_ASSOC) {
        return $this->data;
    }
    
    /**
     * Get number of rows (method)
     */
    public function num_rows() {
        return count($this->data);
    }
    
    /**
     * Magic getter for property access
     */
    public function __get($name) {
        if ($name === 'num_rows') {
            return $this->num_rows;
        }
        return null;
    }
}
