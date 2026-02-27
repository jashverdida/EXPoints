<?php
require_once __DIR__ . '/../config/supabase-session.php';
session_start();

// Check authentication
if (!isset($_SESSION['user_email']) || !isset($_SESSION['user_role'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';
use EXPoints\Database\Connection;

try {
    $db = Connection::getInstance()->getConnection();
} catch (Exception $e) {
    error_log('Database connection error: ' . $e->getMessage());
    header('Location: login.php?error=' . urlencode('Database connection failed'));
    exit();
}

// Verify user role matches the expected role (if specified)
function verifyRole($requiredRole) {
    if ($_SESSION['user_role'] !== $requiredRole) {
        header('Location: login.php?error=' . urlencode('Unauthorized access'));
        exit();
    }
}
