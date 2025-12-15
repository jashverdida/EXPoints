<?php

namespace EXPoints\Database;

/**
 * Supabase Database Connection Wrapper
 * 
 * Provides a mysqli-like interface for Supabase REST API
 * This allows your existing code to work with minimal changes
 */
class SupabaseConnection {
    private $url;
    private $key;
    private $lastResult = null;
    
    public function __construct($url, $key) {
        $this->url = rtrim($url, '/');
        $this->key = $key;
    }
    
    /**
     * Execute a query (simplified interface)
     * 
     * Supports basic SELECT, INSERT, UPDATE, DELETE operations
     * Note: This is a compatibility layer - complex queries may need custom handling
     */
    public function query($sql) {
        // Parse the SQL query to determine operation
        $sql = trim($sql);
        $operation = strtoupper(substr($sql, 0, 6));
        
        try {
            switch ($operation) {
                case 'SELECT':
                    return $this->handleSelect($sql);
                case 'INSERT':
                    return $this->handleInsert($sql);
                case 'UPDATE':
                    return $this->handleUpdate($sql);
                case 'DELETE':
                    return $this->handleDelete($sql);
                case 'CREATE':
                case 'ALTER ':
                case 'DROP T':
                    // DDL operations - not supported via REST API
                    error_log("DDL operation detected: $operation - Use Supabase SQL Editor");
                    return true;
                default:
                    error_log("Unsupported SQL operation: $operation");
                    return false;
            }
        } catch (\Exception $e) {
            error_log("Supabase query error: " . $e->getMessage());
            $this->connect_error = $e->getMessage();
            return false;
        }
    }
    
    /**
     * Handle SELECT queries
     */
    private function handleSelect($sql) {
        // Extract table name and conditions
        if (!preg_match('/FROM\s+`?(\w+)`?/i', $sql, $tableMatch)) {
            error_log("Failed to parse table name from SQL: $sql");
            throw new \Exception("Could not parse table name from: $sql");
        }
        
        $table = $tableMatch[1];
        $url = "{$this->url}/rest/v1/{$table}";
        
        // Check if this is a COUNT query
        $isCount = preg_match('/SELECT\s+COUNT\s*\(/i', $sql);
        
        // Extract SELECT fields
        $select = '*';
        if (preg_match('/SELECT\s+(.*?)\s+FROM/i', $sql, $selectMatch)) {
            $fields = trim($selectMatch[1]);
            if ($isCount) {
                // For COUNT queries, we'll use Supabase's count feature
                $select = '*';
            } elseif ($fields !== '*') {
                // Clean up field list (remove spaces, aliases)
                $select = preg_replace('/\s+as\s+\w+/i', '', $fields);
                $select = str_replace(' ', '', $select);
            }
        }
        
        // URL encode the select parameter
        $url .= "?select=" . urlencode($select);
        
        // Add count header if this is a COUNT query
        $headers = [];
        if ($isCount) {
            $headers['Prefer'] = 'count=exact';
        }
        
        // Extract WHERE conditions
        if (preg_match('/WHERE\s+(.*?)(?:\s+ORDER|\s+LIMIT|\s+GROUP|$)/is', $sql, $whereMatch)) {
            $where = $this->parseWhere($whereMatch[1]);
            if ($where) {
                $url .= '&' . $where;
            }
        }
        
        // Extract ORDER BY
        if (preg_match('/ORDER\s+BY\s+(.*?)(?:\s+LIMIT|$)/is', $sql, $orderMatch)) {
            $order = $this->parseOrderBy($orderMatch[1]);
            if ($order) {
                $url .= '&' . $order;
            }
        }
        
        // Extract LIMIT
        if (preg_match('/LIMIT\s+(\d+)(?:\s+OFFSET\s+(\d+))?/i', $sql, $limitMatch)) {
            $url .= '&limit=' . $limitMatch[1];
            if (isset($limitMatch[2])) {
                $url .= '&offset=' . $limitMatch[2];
            }
        }
        
        $response = $this->request('GET', $url, null, $headers);
        
        // If COUNT query, format the response
        if ($isCount) {
            if (isset($response['count'])) {
                $response = [['count' => $response['count']]];
            } else if (isset($response['data'])) {
                $response = [['count' => count($response['data'])]];
            }
        } else if (isset($response['data'])) {
            $response = $response['data'];
        }
        
        // Create a result object that mimics mysqli_result
        $this->lastResult = new SupabaseResult($response);
        return $this->lastResult;
    }
    
    /**
     * Handle INSERT queries
     */
    private function handleInsert($sql) {
        // Extract table and values
        if (!preg_match('/INSERT\s+INTO\s+`?(\w+)`?\s*\((.*?)\)\s*VALUES\s*\((.*?)\)/is', $sql, $match)) {
            throw new \Exception("Could not parse INSERT: $sql");
        }
        
        $table = $match[1];
        $columns = array_map('trim', explode(',', str_replace('`', '', $match[2])));
        $values = $this->parseValues($match[3]);
        
        $data = array_combine($columns, $values);
        
        $url = "{$this->url}/rest/v1/{$table}";
        $response = $this->request('POST', $url, [$data]);
        
        return !empty($response);
    }
    
    /**
     * Handle UPDATE queries
     */
    private function handleUpdate($sql) {
        // Extract table, SET clause, and WHERE
        if (!preg_match('/UPDATE\s+`?(\w+)`?\s+SET\s+(.*?)(?:\s+WHERE\s+(.*?))?$/is', $sql, $match)) {
            throw new \Exception("Could not parse UPDATE: $sql");
        }
        
        $table = $match[1];
        $setClause = $match[2];
        $whereClause = isset($match[3]) ? $match[3] : null;
        
        // Parse SET values
        $data = [];
        $setPairs = explode(',', $setClause);
        foreach ($setPairs as $pair) {
            if (preg_match('/`?(\w+)`?\s*=\s*(.+)/', trim($pair), $pairMatch)) {
                $key = $pairMatch[1];
                $value = $this->parseValue($pairMatch[2]);
                $data[$key] = $value;
            }
        }
        
        $url = "{$this->url}/rest/v1/{$table}";
        
        if ($whereClause) {
            $where = $this->parseWhere($whereClause);
            if ($where) {
                $url .= '?' . $where;
            }
        }
        
        $response = $this->request('PATCH', $url, $data);
        return !empty($response);
    }
    
    /**
     * Handle DELETE queries
     */
    private function handleDelete($sql) {
        if (!preg_match('/DELETE\s+FROM\s+`?(\w+)`?(?:\s+WHERE\s+(.*))?$/is', $sql, $match)) {
            throw new \Exception("Could not parse DELETE: $sql");
        }
        
        $table = $match[1];
        $whereClause = isset($match[2]) ? $match[2] : null;
        
        $url = "{$this->url}/rest/v1/{$table}";
        
        if ($whereClause) {
            $where = $this->parseWhere($whereClause);
            if ($where) {
                $url .= '?' . $where;
            }
        }
        
        $response = $this->request('DELETE', $url);
        return true;
    }
    
    /**
     * Parse WHERE clause to Supabase filters
     */
    private function parseWhere($where) {
        $where = trim($where);
        $filters = [];
        
        // Handle simple conditions: column = value
        if (preg_match('/`?(\w+)`?\s*=\s*(.+)/', $where, $match)) {
            $column = $match[1];
            $value = $this->parseValue($match[2]);
            return "{$column}=eq.{$value}";
        }
        
        // Handle LIKE
        if (preg_match('/`?(\w+)`?\s+LIKE\s+(.+)/i', $where, $match)) {
            $column = $match[1];
            $value = $this->parseValue($match[2]);
            $value = str_replace(['%', '_'], ['*', '?'], $value);
            return "{$column}=like.{$value}";
        }
        
        // Handle IS NULL / IS NOT NULL
        if (preg_match('/`?(\w+)`?\s+IS\s+(NOT\s+)?NULL/i', $where, $match)) {
            $column = $match[1];
            $operator = isset($match[2]) ? 'not.is' : 'is';
            return "{$column}={$operator}.null";
        }
        
        return '';
    }
    
    /**
     * Parse ORDER BY clause
     */
    private function parseOrderBy($order) {
        $order = trim($order);
        if (preg_match('/`?(\w+)`?\s*(ASC|DESC)?/i', $order, $match)) {
            $column = $match[1];
            $direction = isset($match[2]) && strtoupper($match[2]) === 'DESC' ? '.desc' : '';
            return "order={$column}{$direction}";
        }
        return '';
    }
    
    /**
     * Parse a single value
     */
    private function parseValue($value) {
        $value = trim($value);
        
        // Remove quotes
        if (preg_match('/^(["\'])(.*)\1$/', $value, $match)) {
            return $match[2];
        }
        
        // Return as-is for numbers, NULL, etc.
        return $value;
    }
    
    /**
     * Parse VALUES clause
     */
    private function parseValues($values) {
        $result = [];
        $parts = explode(',', $values);
        
        foreach ($parts as $part) {
            $result[] = $this->parseValue(trim($part));
        }
        
        return $result;
    }
    
    /**
     * Make HTTP request to Supabase
     */
    private function request($method, $url, $data = null, $customHeaders = []) {
        $ch = curl_init($url);
        
        $headers = [
            "apikey: {$this->key}",
            "Authorization: Bearer {$this->key}",
            "Content-Type: application/json",
            "Prefer: return=representation"
        ];
        
        // Merge custom headers
        foreach ($customHeaders as $key => $value) {
            $headers[] = "{$key}: {$value}";
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true); // Include headers in output
        
        if ($data && in_array($method, ['POST', 'PATCH', 'PUT'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        
        if (curl_errno($ch)) {
            throw new \Exception("cURL Error: " . curl_error($ch));
        }
        
        // Extract headers and body
        $headerStr = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        
        curl_close($ch);
        
        if ($httpCode >= 400) {
            error_log("Supabase API Error [$httpCode]: $body");
            throw new \Exception("API Error: $body");
        }
        
        $result = json_decode($body, true) ?: [];
        
        // Extract count from headers if present (for COUNT queries)
        if (preg_match('/content-range:\s*\*\/(\d+)/i', $headerStr, $match)) {
            $result = ['data' => $result, 'count' => (int)$match[1]];
        }
        
        return $result;
    }
    
    /**
     * Get last insert ID (Supabase returns the inserted row)
     */
    public function insert_id() {
        if ($this->lastResult && is_array($this->lastResult)) {
            return $this->lastResult[0]['id'] ?? 0;
        }
        return 0;
    }
    
    /**
     * Prepare statement (compatibility wrapper)
     * Returns a SupabaseStatement that mimics mysqli_stmt
     */
    public function prepare($sql) {
        return new SupabaseStatement($this, $sql);
    }
    
    /**
     * Execute a prepared statement
     */
    public function executePrepared($sql, $params) {
        // Replace ? placeholders with actual values
        foreach ($params as $param) {
            $value = is_string($param) ? "'" . addslashes($param) . "'" : $param;
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        return $this->query($sql);
    }
    
    /**
     * Close connection (no-op for HTTP)
     */
    public function close() {
        // Nothing to close for HTTP connections
    }
}

/**
 * Result wrapper to mimic mysqli_result
 */
class SupabaseResult {
    private $data;
    private $position = 0;
    public $num_rows; // Make it accessible as property
    
    public function __construct($data) {
        $this->data = is_array($data) ? $data : [];
        $this->num_rows = count($this->data); // Set property
    }
    
    public function fetch_assoc() {
        if ($this->position < count($this->data)) {
            return $this->data[$this->position++];
        }
        return null;
    }
    
    public function fetch_array() {
        return $this->fetch_assoc();
    }
    
    public function fetch_all($mode = MYSQLI_ASSOC) {
        return $this->data;
    }
    
    // Keep method for backwards compatibility
    public function num_rows() {
        return $this->num_rows;
    }
}

/**
 * Statement wrapper to mimic mysqli_stmt
 */
class SupabaseStatement {
    private $connection;
    private $sql;
    private $params = [];
    private $types = '';
    private $result = null;
    
    public function __construct($connection, $sql) {
        $this->connection = $connection;
        $this->sql = $sql;
    }
    
    public function bind_param($types, &...$params) {
        $this->types = $types;
        $this->params = $params;
        return true;
    }
    
    public function execute() {
        try {
            $this->result = $this->connection->executePrepared($this->sql, $this->params);
            return $this->result !== false;
        } catch (\Exception $e) {
            error_log("Statement execution error: " . $e->getMessage());
            return false;
        }
    }
    
    public function get_result() {
        return $this->result;
    }
    
    public function close() {
        // No-op for HTTP-based connections
        return true;
    }
    
    public function fetch() {
        if ($this->result && method_exists($this->result, 'fetch_assoc')) {
            return $this->result->fetch_assoc();
        }
        return null;
    }
}
