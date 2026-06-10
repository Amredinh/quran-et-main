<?php
require_once __DIR__ . '/../includes/api-header.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = Database::getConnection();

    if ($method === 'POST') {
        $data = getJsonInput();
        $name = trim($data['name'] ?? '');
        $subfolder = trim($data['subfolder'] ?? '');
        $isEveryAyah = !empty($data['isEveryAyah']);

        if (!$name || !$subfolder) {
            http_response_code(400);
            echo json_encode(['error' => 'Name and subfolder identifier are required.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO reciters (name, subfolder, is_every_ayah) VALUES (?, ?, ?)");
        $stmt->execute([$name, $subfolder, $isEveryAyah ? 1 : 0]);
        echo json_encode(['name' => $name, 'subfolder' => $subfolder, 'isEveryAyah' => $isEveryAyah]);
    } elseif ($method === 'DELETE') {
        $data = getJsonInput();
        $subfolder = trim($data['subfolder'] ?? '');
        if (!$subfolder) {
            http_response_code(400);
            echo json_encode(['error' => 'Subfolder is required']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM reciters WHERE subfolder = ?");
        $stmt->execute([$subfolder]);
        echo json_encode(['success' => true, 'message' => 'Reciter removed.']);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
