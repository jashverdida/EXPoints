<?php
// Simple JSON test endpoint
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

echo json_encode([
    'success' => true,
    'message' => 'JSON endpoint is working correctly',
    'timestamp' => date('Y-m-d H:i:s'),
    'test' => 'This should be valid JSON'
]);
exit;
?>
