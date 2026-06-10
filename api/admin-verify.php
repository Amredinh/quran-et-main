<?php
require_once __DIR__ . '/../includes/api-header.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

$data = getJsonInput();
$key = $data['key'] ?? '';

if (hash_equals(ADMIN_SECRET_KEY, $key)) {
    echo json_encode(['success' => true, 'message' => 'Authentication successful']);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid admin key']);
}
