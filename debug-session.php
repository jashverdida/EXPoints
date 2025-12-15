<?php
session_start();
require_once __DIR__ . '/includes/db_helper.php';

echo "<h2>Session Debug Info</h2>";
echo "<pre>";
echo "Session Username: " . ($_SESSION['username'] ?? 'NOT SET') . "\n";
echo "Session User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "Session Email: " . ($_SESSION['user_email'] ?? 'NOT SET') . "\n";
echo "\n";

$db = getDBConnection();
if ($db && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    
    echo "<h3>Database user_info for user_id = $userId:</h3>";
    $stmt = $db->prepare("SELECT user_id, username, first_name, last_name FROM user_info WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo "User ID: " . $row['user_id'] . "\n";
        echo "Username: " . $row['username'] . "\n";
        echo "First Name: " . $row['first_name'] . "\n";
        echo "Last Name: " . $row['last_name'] . "\n";
    } else {
        echo "No user_info found for user_id = $userId\n";
    }
    
    echo "\n<h3>Database users table for user_id = $userId:</h3>";
    $stmt2 = $db->prepare("SELECT id, email FROM users WHERE id = ?");
    $stmt2->bind_param("i", $userId);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    
    if ($row2 = $result2->fetch_assoc()) {
        echo "User ID: " . $row2['id'] . "\n";
        echo "Email: " . $row2['email'] . "\n";
    }
}

echo "</pre>";

echo "<br><br><a href='user/logout.php'>Logout and Re-login</a>";
?>
