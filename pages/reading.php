<?php
/**
 * Reading Page - replaces pages/ReadingPage.tsx
 */

$lang = $_GET['lang'] ?? 'am';
$surahParam = $_GET['surah'] ?? '1';
$surahIndex = (int)$surahParam;
$ayaParam = $_GET['aya'] ?? null;

if ($surahIndex < 1 || $surahIndex > 114) $surahIndex = 1;

// Find surah in Arabic data
$surahArabic = null;
foreach ($arabicSuras as $s) {
    if ($s['index'] === $surahIndex) { $surahArabic = $s; break; }
}

// Find translation data
$surahTranslation = null;
$translationName = 'Amharic';
if ($lang === 'am') {
    foreach ($amharicSuras as $s) {
        if ($s['index'] === $surahIndex) { $surahTranslation = $s; break; }
    }
} elseif ($dbAvailable) {
    try {
        $stmt = $pdo->prepare("SELECT xml FROM translations WHERE id = ?");
        $stmt->execute([$lang]);
        $transRow = $stmt->fetch();
        if ($transRow) {
            $transSuras = parseQuranXML($transRow['xml']);
            foreach ($transSuras as $s) {
                if ($s['index'] === $surahIndex) { $surahTranslation = $s; break; }
            }
        }
    } catch (Exception $e) {}
}

$currentTranslationId = $lang;

// Get every-ayah reciters
$ayahReciters = array_filter($recitersJson, fn($r) => $r['isEveryAyah']);

if (!$surahArabic) {
    ob_start();
    echo '<div class="text-center py-20 dark:text-white">Surah Not Found in Database</div>';
    $content = ob_get_clean();
    renderLayout('Surah Not Found - Quran.et', $content, 'read', $translations, $currentTranslationId, $donationConfig);
    exit;
}

ob_start();
?>

<div id="reading-container" class="relative min-h-screen bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 transition-colors duration-300">
    
    <!-- Sticky Toolbar -->
    <div class="sticky top-16 z-30 bg-white/90 dark:bg-gray-800/90 backdrop-blur-md border-b border-gray-200 dark:border-gray-700 py-3 px-4 flex items-center shadow-sm transition-colors">
        <div class="flex-1 flex items-center justify-center space-x-3">
            <h1 id="surah-name-display" class="reading-surah-name">surah<?= str_pad($surahArabic['index'], 3, '0', STR_PAD_LEFT) ?></h1>
            <button onclick="toggleSurahFavorite(<?= $surahIndex ?>, '<?= htmlspecialchars($surahArabic['name']) ?>')"
                    class="p-1.5 rounded-lg border transition hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 border-transparent flex-shrink-0"
                    data-fav-id="sura-<?= $surahIndex ?>" title="Favorite Surah">
                <i data-lucide="heart" class="w-[18px] h-[18px]"></i>
            </button>
        </div>
        <div class="flex items-center space-x-2 md:space-x-3 flex-shrink-0">
            <div class="hidden sm:flex items-center space-x-1 border border-slate-200 dark:border-slate-700 bg-gray-50 dark:bg-gray-800 p-1 px-2 rounded-xl text-xs font-semibold shadow-xs">
                <span class="text-gray-500 dark:text-gray-400">ድምፅ አቅራቢ | Qari:</span>
                <select id="ayah-reciter-select" onchange="changeAyahReciter(this.value)" class="bg-transparent border-none outline-none font-bold text-primary max-w-[130px] md:max-w-[150px] cursor-pointer">
                    <option value="AbdulSamad_64kbps_QuranExplorer.Com">Abdul Basit (Default)</option>
                    <?php foreach ($ayahReciters as $r): ?>
                    <option value="<?= htmlspecialchars($r['subfolder']) ?>"><?= htmlspecialchars(preg_replace('/\s*\(Every Ayah[^)]*\)/', '', $r['name'])) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="hidden sm:flex items-center space-x-1 border border-slate-200 dark:border-slate-700 bg-gray-50 dark:bg-gray-800 p-1 px-2 rounded-xl text-xs font-semibold shadow-xs">
                <span class="text-gray-500 dark:text-gray-400">ሁነታ | Mode:</span>
                <select id="ayah-mode-select" onchange="changeAyahPlayMode(this.value)" class="bg-transparent border-none outline-none font-bold text-emerald-600 dark:text-emerald-400 cursor-pointer">
                    <option value="continuous">ተከታታይ (Continuous)</option>
                    <option value="single">አንድ አያህ (One Ayah)</option>
                </select>
            </div>
            <button onclick="toggleSettings()" class="p-2 bg-gray-100 dark:bg-gray-700 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                <i data-lucide="settings" class="w-[18px] h-[18px] text-gray-700 dark:text-gray-200"></i>
            </button>
        </div>
    </div>

    <!-- Mobile reciter/mode bar -->
    <div class="block sm:hidden p-3 bg-slate-50 dark:bg-slate-950 border-b border-gray-100 dark:border-slate-850 px-4 text-xs space-y-2.5">
        <div class="flex justify-between items-center text-slate-500 font-bold uppercase tracking-wider">
            <span>ድምፅ አቅራቢ (Qari)</span>
            <select onchange="changeAyahReciter(this.value)" class="bg-transparent border-none outline-none font-bold text-primary cursor-pointer text-sm">
                <option value="AbdulSamad_64kbps_QuranExplorer.Com">Abdul Basit (Default)</option>
                <?php foreach ($ayahReciters as $r): ?>
                <option value="<?= htmlspecialchars($r['subfolder']) ?>"><?= htmlspecialchars(preg_replace('/\s*\(Every Ayah[^)]*\)/', '', $r['name'])) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex justify-between items-center text-slate-500 font-bold uppercase tracking-wider pt-2 border-t border-slate-100 dark:border-slate-900">
            <span>የአጫዋች ሁነታ (Play Mode)</span>
            <select onchange="changeAyahPlayMode(this.value)" class="bg-transparent border-none outline-none font-bold text-emerald-600 dark:text-emerald-400 cursor-pointer text-sm">
                <option value="continuous">ተከታታይ (Continuous)</option>
                <option value="single">አንድ አያህ (One Ayah)</option>
            </select>
        </div>
    </div>

    <!-- Settings Panel -->
    <div id="settings-panel" class="fixed top-32 right-4 md:right-10 z-40 bg-white dark:bg-gray-800 shadow-2xl rounded-xl p-6 w-80 border border-gray-100 dark:border-gray-700 hidden animate-fade-in text-gray-800 dark:text-gray-100">
        <div class="flex justify-between mb-4">
            <h3 class="font-bold">Display Settings</h3>
            <button onclick="toggleSettings()" class="text-gray-400 hover:text-red-500"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <div class="space-y-6">
            <div>
                <label class="text-sm text-gray-500 dark:text-gray-400 mb-1 block">Arabic Font Size: <span id="arabic-font-size">23px</span></label>
                <input type="range" min="12" max="50" step="1" value="23" oninput="setArabicFontSize(this.value)" class="w-full accent-primary" />
            </div>
            <div>
                <label class="text-sm text-gray-500 dark:text-gray-400 mb-1 block">Translation Font Size: <span id="trans-font-size">21px</span></label>
                <input type="range" min="10" max="40" step="1" value="21" oninput="setTransFontSize(this.value)" class="w-full accent-primary" />
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm">Arabic Visibility</span>
                <input type="checkbox" checked onchange="toggleArabicVisibility(this.checked)" class="accent-primary w-5 h-5" />
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm">Translation Visibility</span>
                <input type="checkbox" checked onchange="toggleTransVisibility(this.checked)" class="accent-primary w-5 h-5" />
            </div>
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400 block mb-2">Paper Texture Mode</span>
                <div class="flex space-x-2">
                    <button id="mode-modern-btn" onclick="setReadingMode('default')" class="flex-1 py-1.5 text-sm rounded border bg-primary text-white border-primary font-bold">Modern</button>
                    <button id="mode-paper-btn" onclick="setReadingMode('paper')" class="flex-1 py-1.5 text-sm rounded border border-gray-300 dark:border-gray-600 font-medium text-gray-600 dark:text-gray-300">Classic</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Ayah Content -->
    <input type="hidden" id="surah-id-display" value="<?= $surahIndex ?>" />
    <input type="hidden" id="total-ayahs" value="<?= count($surahArabic['ayas']) ?>" />
    
    <div class="max-w-4xl mx-auto py-8 px-4">
        <?php foreach ($surahArabic['ayas'] as $idx => $aya):
            $transText = $surahTranslation['ayas'][$idx]['text'] ?? null;
            $ayahFavKey = 'ayah-' . $surahIndex . '-' . $idx;
        ?>
        <div id="ayah-<?= $idx ?>" class="mb-8 p-4 rounded-xl transition duration-500 border-b border-gray-100 dark:border-gray-800">
            <div class="flex justify-between items-start mb-4">
                <div class="flex space-x-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300"><?= $aya['index'] ?></div>
                    <button id="play-btn-<?= $idx ?>" onclick="playAyah(<?= $idx ?>, <?= $surahIndex ?>)" class="text-gray-400 hover:text-primary transition" title="Play verse">
                        <i data-lucide="play" class="w-5 h-5"></i>
                    </button>
                    <div class="relative">
                        <button id="copy-btn-<?= $idx ?>" onclick="showCopyMenu(<?= $idx ?>)" class="text-gray-400 hover:text-primary transition" title="Copy text">
                            <i data-lucide="copy" class="w-5 h-5"></i>
                        </button>
                        <div id="copy-menu-<?= $idx ?>" class="absolute top-full left-0 bg-white dark:bg-gray-800 shadow-xl rounded-lg p-2 z-20 w-48 border border-gray-100 dark:border-gray-700 flex-col mt-2 hidden">
                            <button onclick="copyAyahText(<?= $idx ?>, 'arabic', '<?= htmlspecialchars($aya['text'], ENT_QUOTES) ?>', '<?= htmlspecialchars($transText ?? '', ENT_QUOTES) ?>', <?= $surahIndex ?>)" class="text-left px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 rounded text-gray-700 dark:text-gray-200">Copy Arabic</button>
                            <button onclick="copyAyahText(<?= $idx ?>, 'trans', '<?= htmlspecialchars($aya['text'], ENT_QUOTES) ?>', '<?= htmlspecialchars($transText ?? '', ENT_QUOTES) ?>', <?= $surahIndex ?>)" class="text-left px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 rounded text-gray-700 dark:text-gray-200">Copy Translation</button>
                            <button onclick="copyAyahText(<?= $idx ?>, 'both', '<?= htmlspecialchars($aya['text'], ENT_QUOTES) ?>', '<?= htmlspecialchars($transText ?? '', ENT_QUOTES) ?>', <?= $surahIndex ?>)" class="text-left px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 rounded text-gray-700 dark:text-gray-200">Copy Both</button>
                        </div>
                    </div>
                    <button onclick="toggleAyahFavorite(<?= $surahIndex ?>, '<?= htmlspecialchars($surahArabic['name'], ENT_QUOTES) ?>', <?= $idx ?>, <?= $aya['index'] ?>, '<?= htmlspecialchars($transText ?? '', ENT_QUOTES) ?>')"
                            class="transition text-gray-400 hover:text-red-500" data-fav-id="<?= $ayahFavKey ?>" title="Bookmark Verse">
                        <i data-lucide="heart" class="w-5 h-5"></i>
                    </button>
                    <button onclick="showAyahDetail(<?= $idx ?>)" class="text-gray-400 hover:text-primary transition" title="Verse detail">
                        <i data-lucide="more-horizontal" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Ayah Detail Modal -->
            <div id="ayah-detail-modal-<?= $idx ?>" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-2 sm:p-4 flex">
                <div class="ayah-detail-modal-content bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-6 w-full max-w-lg relative shadow-2xl animate-fade-in max-h-[90vh] overflow-y-auto">
                    <button onclick="hideAyahDetail(<?= $idx ?>)" class="absolute top-4 right-4 text-gray-500 hover:text-red-500"><i data-lucide="x" class="w-6 h-6"></i></button>
                    <h3 class="font-bold mb-4 text-gray-800 dark:text-white">Verse Detail</h3>
                    <p class="arabic text-right mb-4 leading-loose text-gray-900 dark:text-white"><?= htmlspecialchars($aya['text']) ?></p>
                    <div class="mb-4">
                        <label class="text-xs text-gray-400 uppercase font-bold mb-1 block">Quick Language Switch</label>
                        <select onchange="changeDetailTranslation(this.value, <?= $idx ?>)" class="w-full mt-1 p-2 border rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white border-gray-200 dark:border-gray-600">
                            <option value="am" <?= $lang === 'am' ? 'selected' : '' ?>>Amharic</option>
                            <?php foreach ($translations as $t): ?>
                            <option value="<?= htmlspecialchars(is_array($t) ? $t['id'] : $t->id) ?>" <?= $lang === (is_array($t) ? $t['id'] : $t->id) ? 'selected' : '' ?>><?= htmlspecialchars(is_array($t) ? $t['name'] : $t->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <p class="amharic text-gray-700 dark:text-gray-200"><?= htmlspecialchars($transText ?? 'Translation not loaded for this version.') ?></p>
                </div>
            </div>

            <?php if (!empty($aya['bismillah'])): ?>
            <div class="text-center mb-6 arabic text-secondary dark:text-primary opacity-80 animate-fade-in">
                <?= htmlspecialchars($aya['bismillah']) ?>
            </div>
            <?php endif; ?>

            <p class="ayah-arabic-text text-right leading-[2.5] mb-4 text-gray-900 dark:text-white select-all">
                <?= htmlspecialchars($aya['text']) ?>
            </p>

            <p class="ayah-trans-text text-left leading-relaxed text-gray-700 dark:text-gray-300 select-all">
                <?= htmlspecialchars($transText ?? 'This verse translation is not yet in our database.') ?>
            </p>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initReadingPage(<?= $surahIndex ?>);
    if (window.lucide) lucide.createIcons();
    
    // Apply saved font sizes (fix old px values)
    var savedArabicSize = localStorage.getItem('reading_arabic_size');
    var savedTransSize = localStorage.getItem('reading_trans_size');
    if (savedArabicSize !== null) setArabicFontSize(parseFloat(savedArabicSize));
    if (savedTransSize !== null) setTransFontSize(parseFloat(savedTransSize));
});
</script>
<script src="/assets/js/reading.js"></script>

<?php
$content = ob_get_clean();
$pageTitle = 'Surah ' . ($surahArabic['name'] ?? $surahIndex) . ' - Quran.et';
renderLayout($pageTitle, $content, 'read', $translations, $currentTranslationId, $donationConfig);
?>
