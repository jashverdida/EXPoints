<?php
session_start();

echo "<h2>Current Session Debug</h2>";
echo "<pre>";
echo "Session User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "Session Username: " . ($_SESSION['username'] ?? 'NOT SET') . "\n";
echo "Session Email: " . ($_SESSION['user_email'] ?? 'NOT SET') . "\n";
echo "</pre>";

require_once __DIR__ . '/includes/db_helper.php';

$db = getDBConnection();

echo "<h2>All Users in Database</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>User ID</th><th>Email</th><th>Username (from user_info)</th></tr>";

$result = $db->query("SELECT u.id, u.email, ui.username FROM users u LEFT JOIN user_info ui ON u.id = ui.user_id ORDER BY u.id");

while ($row = $result->fetch_assoc()) {
    $highlight = '';
    if (isset($_SESSION['user_id']) && $row['id'] == $_SESSION['user_id']) {
        $highlight = ' style="background-color: yellow; font-weight: bold;"';
    }
    echo "<tr$highlight>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['email'] . "</td>";
    echo "<td>" . ($row['username'] ?? 'NO USERNAME') . "</td>";
    echo "</tr>";
}

echo "</table>";

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    echo "<h2>Current Logged In User Details (user_id = $userId)</h2>";
    
    $stmt = $db->prepare("SELECT * FROM user_info WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>NO user_info found for user_id = $userId!</p>";
    }
}

echo "<br><br><a href='user/logout.php' style='padding: 10px 20px; background: red; color: white; text-decoration: none; border-radius: 5px;'>LOGOUT</a>";
?>
