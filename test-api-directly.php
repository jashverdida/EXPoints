<?php
// Test the new API directly
session_start();
$_SESSION['authenticated'] = true; // Simulate auth

// Include the API
$_GET['id'] = 3; // Test with post ID 3 (EijayWasHere)

ob_start();
include 'api/get_post.php';
$output = ob_get_clean();

echo "API Output:\n";
echo $output;
echo "\n\nDecoded:\n";
$data = json_decode($output, true);
print_r($data);
?>
