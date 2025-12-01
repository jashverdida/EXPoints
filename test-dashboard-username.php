<?php
session_start();

echo "<h1>Dashboard Username Debug</h1>";

// Get session data
$sessionUserId = $_SESSION['user_id'] ?? null;
$sessionUsername = $_SESSION['username'] ?? 'NOT SET';
$sessionEmail = $_SESSION['user_email'] ?? 'NOT SET';

echo "<h2>1. Session Data</h2>";
echo "<pre>";
echo "Session User ID: $sessionUserId\n";
echo "Session Username: $sessionUsername\n";
echo "Session Email: $sessionEmail\n";
echo "</pre>";

// Query database
require_once __DIR__ . '/includes/db_helper.php';
$db = getDBConnection();

echo "<h2>2. Database Query for user_id = $sessionUserId</h2>";

if ($sessionUserId) {
    $stmt = $db->prepare("SELECT username, profile_picture FROM user_info WHERE user_id = ?");
    $stmt->bind_param("i", $sessionUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo "<pre>";
        echo "✅ Found in database:\n";
        echo "Username: " . $row['username'] . "\n";
        echo "Profile Picture: " . $row['profile_picture'] . "\n";
        echo "</pre>";
        
        $fetchedUsername = $row['username'];
    } else {
        echo "<p style='color: red;'>❌ NO user_info record found for user_id = $sessionUserId</p>";
        $fetchedUsername = "NOT FOUND";
    }
} else {
    echo "<p style='color: red;'>❌ No user_id in session</p>";
    $fetchedUsername = "NO USER ID";
}

echo "<h2>3. What Dashboard.php Should Display</h2>";
echo "<div style='background: #f0f0f0; padding: 20px; border: 2px solid #333;'>";
echo "<p><strong>Welcome message should say:</strong> Welcome, $fetchedUsername!</p>";
echo "<p><strong>Post box should say:</strong> What's on your mind, @$fetchedUsername?</p>";
echo "</div>";

echo "<h2>4. All user_info Records</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>user_id</th><th>username</th></tr>";

$allUsers = $db->query("SELECT user_id, username FROM user_info ORDER BY user_id");
while ($row = $allUsers->fetch_assoc()) {
    $highlight = ($row['user_id'] == $sessionUserId) ? ' style="background: yellow;"' : '';
    echo "<tr$highlight><td>" . $row['user_id'] . "</td><td>" . $row['username'] . "</td></tr>";
}
echo "</table>";

echo "<br><br>";
echo "<a href='user/dashboard.php' style='padding: 10px 20px; background: blue; color: white; text-decoration: none;'>Go to Dashboard</a> ";
echo "<a href='user/logout.php' style='padding: 10px 20px; background: red; color: white; text-decoration: none;'>Logout</a>";
?>
