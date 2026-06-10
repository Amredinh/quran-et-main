<?php
require_once __DIR__ . '/../includes/api-header.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'PUT') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $pdo = Database::getConnection();
    $data = getJsonInput();

    // Get existing config
    $existing = $pdo->query("SELECT * FROM donation_config ORDER BY id DESC LIMIT 1")->fetch();

    $message = $data['message'] ?? ($existing['message'] ?? '');
    $buttonText = $data['buttonText'] ?? ($existing['button_text'] ?? '');
    $link = $data['link'] ?? ($existing['link'] ?? '');
    $enabled = isset($data['enabled']) ? ($data['enabled'] ? 1 : 0) : ($existing['enabled'] ?? 1);

    if ($existing) {
        $stmt = $pdo->prepare("UPDATE donation_config SET message = ?, button_text = ?, link = ?, enabled = ? WHERE id = ?");
        $stmt->execute([$message, $buttonText, $link, $enabled, $existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO donation_config (message, button_text, link, enabled) VALUES (?, ?, ?, ?)");
        $stmt->execute([$message, $buttonText, $link, $enabled]);
    }

    echo json_encode([
        'message' => $message,
        'buttonText' => $buttonText,
        'link' => $link,
        'enabled' => (bool)$enabled,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
