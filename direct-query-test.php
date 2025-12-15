<?php
session_start();
require_once __DIR__ . '/includes/db_helper.php';

$userId = $_SESSION['user_id'] ?? null;

echo "<h1>Direct Database Query Test</h1>";

$db = getDBConnection();

echo "<h2>Query: SELECT username FROM user_info WHERE user_id = $userId</h2>";

// Exact same query as dashboard.php
$stmt = $db->prepare("SELECT username, profile_picture FROM user_info WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo "<div style='background: yellow; padding: 20px; border: 3px solid red;'>";
    echo "<h3>Result from database:</h3>";
    echo "<p><strong>Username:</strong> " . $row['username'] . "</p>";
    echo "<p><strong>Profile Picture:</strong> " . $row['profile_picture'] . "</p>";
    echo "</div>";
} else {
    echo "<p style='color: red;'>NO RESULT</p>";
}

echo "<h2>All Records in user_info for user_id = $userId</h2>";
$allRecords = $db->query("SELECT * FROM user_info WHERE user_id = $userId");

echo "<table border='1' cellpadding='10'>";
$first = true;
while ($row = $allRecords->fetch_assoc()) {
    if ($first) {
        echo "<tr>";
        foreach (array_keys($row) as $key) {
            echo "<th>$key</th>";
        }
        echo "</tr>";
        $first = false;
    }
    echo "<tr>";
    foreach ($row as $value) {
        echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
    }
    echo "</tr>";
}
echo "</table>";

echo "<h2>Connection Info</h2>";
echo "<pre>";
echo "DB Object: " . get_class($db) . "\n";
echo "</pre>";
?>
