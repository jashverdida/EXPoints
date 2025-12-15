<?php
/**
 * Fix Admin Role Script
 * Ensures admin user has correct role value in Supabase
 */

require_once __DIR__ . '/includes/db_helper.php';

echo "Fixing admin roles in Supabase...\n\n";

$db = getDBConnection();

if (!$db) {
    die("ERROR: Could not connect to database\n");
}

// Check current users and their roles
echo "Current users:\n";
echo str_repeat("-", 80) . "\n";

$result = $db->query("SELECT id, email, role FROM users ORDER BY id");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $roleDisplay = $row['role'] ?? 'NULL';
        echo sprintf("ID: %d | Email: %s | Role: %s\n", 
            $row['id'], 
            $row['email'], 
            $roleDisplay
        );
    }
} else {
    echo "No users found!\n";
}

echo "\n" . str_repeat("-", 80) . "\n\n";

// Ask which user should be admin
echo "Enter the email of the user who should be ADMIN: ";
$adminEmail = trim(fgets(STDIN));

if (empty($adminEmail)) {
    die("No email provided. Exiting.\n");
}

// Update the user's role to admin
$stmt = $db->prepare("UPDATE users SET role = ? WHERE email = ?");
$adminRole = 'admin';
$stmt->bind_param("ss", $adminRole, $adminEmail);

if ($stmt->execute()) {
    echo "\n✅ SUCCESS! Updated $adminEmail to admin role.\n";
    
    // Verify the change
    $verifyStmt = $db->prepare("SELECT id, email, role FROM users WHERE email = ?");
    $verifyStmt->bind_param("s", $adminEmail);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();
    
    if ($verifyResult && $verifyResult->num_rows > 0) {
        $verifiedUser = $verifyResult->fetch_assoc();
        echo "\nVerified user details:\n";
        echo "ID: " . $verifiedUser['id'] . "\n";
        echo "Email: " . $verifiedUser['email'] . "\n";
        echo "Role: " . $verifiedUser['role'] . "\n";
    }
    
    $verifyStmt->close();
} else {
    echo "\n❌ ERROR: Failed to update user role.\n";
    echo "Error: " . $stmt->error . "\n";
}

$stmt->close();
$db->close();

echo "\nDone!\n";
?>
