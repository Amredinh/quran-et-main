<?php
require_once __DIR__ . '/../includes/api-header.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

$transId = $_GET['id'] ?? '';
if (!$transId) {
    http_response_code(400);
    echo json_encode(['error' => 'Translation ID required']);
    exit;
}

try {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT id, name, xml FROM translations WHERE id = ?");
    $stmt->execute([$transId]);
    $trans = $stmt->fetch();

    if ($trans) {
        echo json_encode($trans);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Translation not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
