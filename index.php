<?php
/**
 * Quran.et - Main Front Controller (Router)
 * Handles all frontend page requests.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/quran.php';

// Only initialize DB if API is being called (lazy load)
// For frontend pages, we embed data directly

$page = $_GET['page'] ?? 'home';

// Load shared data for header/footer
$dbAvailable = false;
try {
    require_once __DIR__ . '/db.php';
    Database::init();
    $dbAvailable = true;
    $pdo = Database::getConnection();
} catch (Exception $e) {
    $dbAvailable = false;
}

// Get data for all pages (from DB or defaults)
if ($dbAvailable) {
    try {
        $blogs = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC")->fetchAll();
        $reciters = $pdo->query("SELECT id, name, subfolder, is_every_ayah as isEveryAyah FROM reciters ORDER BY id")->fetchAll();
        $donationRow = $pdo->query("SELECT * FROM donation_config ORDER BY id DESC LIMIT 1")->fetch();
    } catch (Exception $e) {
        $blogs = [];
        $reciters = [];
        $donationRow = null;
    }
} else {
    $blogs = [];
    $reciters = [];
    $donationRow = null;
}

// Ensure at least default reciters exist if DB not available
if (empty($reciters)) {
    $reciters = [
        ['name' => 'Abdulbasit Abdulsamad (Sura Recitation)', 'subfolder' => 'basit', 'isEveryAyah' => false],
        ['name' => 'Maher Al-Meaqli (Sura Recitation)', 'subfolder' => 'maher', 'isEveryAyah' => false],
        ['name' => 'Mishary Alafasi (Sura Recitation)', 'subfolder' => 'afs', 'isEveryAyah' => false],
        ['name' => 'Saad Al-Ghamdi (Sura Recitation)', 'subfolder' => 's_gmd', 'isEveryAyah' => false],
        ['name' => 'Abdulrahman Al-Sudaes (Sura Recitation)', 'subfolder' => 'sds', 'isEveryAyah' => false],
        ['name' => 'Abdul Basit (Every Ayah - AbdulSamad_64kbps)', 'subfolder' => 'AbdulSamad_64kbps_QuranExplorer.Com', 'isEveryAyah' => true],
        ['name' => 'Mishary Alafasy (Every Ayah - Alafasy_128kbps)', 'subfolder' => 'Alafasy_128kbps', 'isEveryAyah' => true],
        ['name' => 'Saad Al-Ghamdi (Every Ayah - Ghamadi_40kbps)', 'subfolder' => 'Ghamadi_40kbps', 'isEveryAyah' => true],
        ['name' => 'Mahmoud Al-Husary (Every Ayah - Husary_128kbps)', 'subfolder' => 'Husary_128kbps', 'isEveryAyah' => true],
        ['name' => 'Maher Al-Muaiqly (Every Ayah - Maher_AlMuaiqly_64kbps)', 'subfolder' => 'Maher_AlMuaiqly_64kbps', 'isEveryAyah' => true],
    ];
}

$donationConfig = $donationRow ? [
    'message' => $donationRow['message'],
    'buttonText' => $donationRow['button_text'],
    'link' => $donationRow['link'],
    'enabled' => (bool)$donationRow['enabled'],
] : [
    'message' => "Help us keep Quran.et free and sustainable for the Ethiopian community.",
    'buttonText' => "Donate Now",
    'link' => "https://example.com/donate",
    'enabled' => true,
];

$translations = [];
if ($dbAvailable) {
    try {
        $translations = $pdo->query("SELECT id, name FROM translations ORDER BY created_at")->fetchAll();
    } catch (Exception $e) {}
}

// Transform reciters for JSON output
$recitersJson = array_map(function($r) {
    return [
        'name' => is_array($r) ? $r['name'] : $r->name,
        'subfolder' => is_array($r) ? $r['subfolder'] : $r->subfolder,
        'isEveryAyah' => (bool)(is_array($r) ? $r['isEveryAyah'] : $r->is_every_ayah),
    ];
}, $reciters);

// Load Quran data
$arabicSuras = loadArabicQuran();
$amharicSuras = loadAmharicQuran();

// Default blog posts
$defaultBlogs = [
    ['id' => 1, 'title' => "Understanding the Revelation of Surah Al-Alaq", 'excerpt' => "The first revealed surah holds deep significance...", 'content' => "The revelation of Surah Al-Alaq marks the beginning of the Quranic journey. 'Iqra' or 'Read' was the first command given to Prophet Muhammad (PBUH) in the cave of Hira. This command highlights the supreme importance of knowledge, education, and literacy in the Islamic faith. Understanding this surah allows believers to appreciate the value of learning as a gateway to spiritual enlightenment and a closer relationship with the Creator.", 'image' => "https://images.unsplash.com/photo-1585032226651-759b368d7246?auto=format&fit=crop&q=80&w=800", 'date' => "Oct 24, 2023"],
    ['id' => 2, 'title' => "The Importance of Tajweed", 'excerpt' => "Why reciting the Quran correctly matters.", 'content' => "Tajweed is the set of rules governing the pronunciation of Quranic letters. It is not merely about aesthetics; it is about preserving the divine message exactly as it was revealed. Incorrect pronunciation can, in some cases, alter the meaning of the words entirely. By studying Tajweed, believers ensure they are honoring the text and fulfilling the duty of 'reciting the Quran in slow, measured tones' as commanded in Surah Al-Muzzammil.", 'image' => "https://images.unsplash.com/photo-1542810634-71277d95dcbb?auto=format&fit=crop&q=80&w=800", 'date' => "Oct 20, 2023"]
];

if (empty($blogs)) {
    $blogs = $defaultBlogs;
}

$surahMeta = getSurahMetadata();
$revelationOrder = getRevelationOrder();
$surahNamesEn = getSurahNamesEn();

$surahMeaningFile = __DIR__ . '/surah_meaning.json';
$surahMeanings = [];
if (file_exists($surahMeaningFile)) {
    $content = file_get_contents($surahMeaningFile);
    $parsed = json_decode($content, true);
    if (is_array($parsed)) {
        $surahMeanings = $parsed;
    }
}

// Render page based on route
switch ($page) {
    case 'home':
        require __DIR__ . '/pages/home.php';
        break;
    case 'read':
        require __DIR__ . '/pages/reading.php';
        break;
    case 'audio':
        require __DIR__ . '/pages/audio.php';
        break;
    case 'blog':
        require __DIR__ . '/pages/blog.php';
        break;
    case 'blog-post':
        require __DIR__ . '/pages/blog-post.php';
        break;
    default:
        require __DIR__ . '/pages/home.php';
}
