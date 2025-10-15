<?php
require_once __DIR__ . '/vendor/autoload.php';
use EXPoints\Database\Connection;

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $db = Connection::getInstance()->getConnection();
    $email = 'user@email.com';
    
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        echo "User found:\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Role: " . $user['role'] . "\n";
        echo "ID: " . $user['id'] . "\n";
    } else {
        echo "User not found!";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>