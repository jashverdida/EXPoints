<?php
require_once __DIR__ . '/vendor/autoload.php';
use EXPoints\Database\Connection;

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $db = Connection::getInstance()->getConnection();
    if ($db) {
        echo "Successfully connected to database!\n";
        
        // Test query
        $result = $db->query("SHOW TABLES");
        if ($result) {
            echo "\nTables in database:\n";
            while ($row = $result->fetch_array()) {
                echo "- " . $row[0] . "\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>