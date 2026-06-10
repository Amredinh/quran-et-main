<?php
require_once __DIR__ . '/../includes/api-header.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

try {
    $pdo = Database::getConnection();

    $blogs = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC")->fetchAll();
    $translations = $pdo->query("SELECT id, name, xml FROM translations ORDER BY created_at")->fetchAll();
    $reciters = $pdo->query("SELECT name, subfolder, is_every_ayah as isEveryAyah FROM reciters ORDER BY id")->fetchAll();
    $donation = $pdo->query("SELECT * FROM donation_config ORDER BY id DESC LIMIT 1")->fetch();

    foreach ($reciters as &$r) {
        $r['isEveryAyah'] = (bool)$r['isEveryAyah'];
    }
    unset($r);

    $donationConfig = $donation ? [
        'message' => $donation['message'],
        'buttonText' => $donation['button_text'],
        'link' => $donation['link'],
        'enabled' => (bool)$donation['enabled'],
    ] : null;

    echo json_encode([
        'blogs' => $blogs,
        'translations' => $translations,
        'reciters' => $reciters,
        'donationConfig' => $donationConfig,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
