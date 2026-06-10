<?php
require_once __DIR__ . '/../includes/api-header.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';

try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database unavailable']);
    exit;
}

// GET /api/public/data
$blogs = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC")->fetchAll();
$reciters = $pdo->query("SELECT name, subfolder, is_every_ayah as isEveryAyah FROM reciters ORDER BY id")->fetchAll();
$donation = $pdo->query("SELECT * FROM donation_config ORDER BY id DESC LIMIT 1")->fetch();
$translations = $pdo->query("SELECT id, name FROM translations ORDER BY created_at")->fetchAll();

// Convert is_every_ayah from int to bool
foreach ($reciters as &$r) {
    $r['isEveryAyah'] = (bool)$r['isEveryAyah'];
}
unset($r);

$donationConfig = $donation ? [
    'message' => $donation['message'],
    'buttonText' => $donation['button_text'],
    'link' => $donation['link'],
    'enabled' => (bool)$donation['enabled'],
] : [
    'message' => "Help us keep Quran.et free and sustainable for the Ethiopian community.",
    'buttonText' => "Donate Now",
    'link' => "https://example.com/donate",
    'enabled' => true,
];

echo json_encode([
    'blogs' => $blogs,
    'reciters' => $reciters,
    'donationConfig' => $donationConfig,
    'translationsMeta' => $translations,
]);
