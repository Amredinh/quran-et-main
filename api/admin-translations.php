<?php
require_once __DIR__ . '/../includes/api-header.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];
$transId = $_GET['id'] ?? null;

try {
    $pdo = Database::getConnection();

    if ($method === 'POST') {
        $data = getJsonInput();
        $id = trim($data['id'] ?? '');
        $name = trim($data['name'] ?? '');
        $xml = $data['xml'] ?? '';

        if (!$id || !$name || !$xml) {
            http_response_code(400);
            echo json_encode(['error' => 'ID, Language Name, and XML content are required.']);
            exit;
        }

        // Upsert
        $stmt = $pdo->prepare("DELETE FROM translations WHERE id = ?");
        $stmt->execute([$id]);
        $stmt = $pdo->prepare("INSERT INTO translations (id, name, xml) VALUES (?, ?, ?)");
        $stmt->execute([$id, $name, $xml]);

        echo json_encode(['success' => true, 'message' => "Translation for {$name} added successfully."]);
    } elseif ($method === 'DELETE' && $transId) {
        $stmt = $pdo->prepare("DELETE FROM translations WHERE id = ?");
        $stmt->execute([$transId]);
        echo json_encode(['success' => true, 'message' => 'Translation removed.']);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
