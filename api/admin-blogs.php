<?php
require_once __DIR__ . '/../includes/api-header.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];
$blogId = $_GET['id'] ?? null;

try {
    $pdo = Database::getConnection();

    if ($method === 'POST') {
        $data = getJsonInput();
        $title = trim($data['title'] ?? '');
        $content = trim($data['content'] ?? '');

        if (!$title || !$content) {
            http_response_code(400);
            echo json_encode(['error' => 'Title and content are required.']);
            exit;
        }

        $excerpt = trim($data['excerpt'] ?? '');
        if (!$excerpt) {
            $excerpt = mb_substr($content, 0, 100) . '...';
        }
        $image = trim($data['image'] ?? '');
        if (!$image) {
            $image = "https://images.unsplash.com/photo-1585032226651-759b368d7246?auto=format&fit=crop&q=80&w=800";
        }
        $date = date('M j, Y');

        $stmt = $pdo->prepare("INSERT INTO blogs (title, excerpt, content, image, date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $excerpt, $content, $image, $date]);
        $newId = $pdo->lastInsertId();

        echo json_encode([
            'id' => (int)$newId,
            'title' => $title,
            'excerpt' => $excerpt,
            'content' => $content,
            'image' => $image,
            'date' => $date,
        ]);
    } elseif ($method === 'DELETE' && $blogId) {
        $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
        $stmt->execute([$blogId]);
        echo json_encode(['success' => true, 'message' => 'Blog post removed.']);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
