<?php
require_once __DIR__ . '/vendor/autoload.php';
use EXPoints\Database\Connection;

try {
    $db = Connection::getInstance()->getConnection();
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'mod', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        username VARCHAR(100) UNIQUE,
        verified BOOLEAN DEFAULT FALSE,
        INDEX email_index (email)
    )";
    $db->query($sql);
    
    // Create posts table
    $sql = "CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        game VARCHAR(255) NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        username VARCHAR(100) NOT NULL,
        likes INT DEFAULT 0,
        comments INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $db->query($sql);

    // Create comments table
    $sql = "CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        username VARCHAR(100) NOT NULL,
        text TEXT NOT NULL,
        likes INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
    )";
    $db->query($sql);

    echo "Tables created successfully!";
} catch(Exception $e) {
    die("Setup failed: " . $e->getMessage());
}