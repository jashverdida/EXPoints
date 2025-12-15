<?php
session_start();
header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($data['user_id'])) {
    $_SESSION['user_id'] = $data['user_id'];
    $_SESSION['access_token'] = $data['access_token'] ?? null;
    echo json_encode(['success' => true]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No user ID provided']);
}