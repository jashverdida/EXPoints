<?php
require_once __DIR__ . '/vendor/autoload.php';
use EXPoints\Database\Connection;

try {
    $db = Connection::getInstance()->getConnection();
    
    // First, check if user exists
    $email = 'user@email.com';
    $password = 'user123';
    $role = 'user';
    
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result->fetch_assoc()) {
        // User doesn't exist, create it
        $stmt = $db->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $password, $role);
        if ($stmt->execute()) {
            echo "User created successfully!\n";
        } else {
            echo "Failed to create user\n";
        }
    } else {
        // User exists, update role if needed
        $stmt = $db->prepare("UPDATE users SET role = 'user' WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            echo "User role updated successfully!\n";
        } else {
            echo "Failed to update user role\n";
        }
    }
    
    echo "Setup complete!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>