<?php
/**
 * Audio Page - replaces pages/AudioPage.tsx
 */

$currentTranslationId = $_COOKIE['active_lang'] ?? 'am';
$surahReciters = array_filter($recitersJson, fn($r) => !$r['isEveryAyah']);
$activeReciters = !empty($surahReciters) ? $surahReciters : $recitersJson;

ob_start();
?>

<div class="space-y-10 pb-32 animate-fade-in">
    <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 transition-colors">
        <div class="flex items-center space-x-4 mb-8">
            <div class="p-3 bg-primary/10 rounded-2xl">
                <i data-lucide="play" class="text-primary fill-current w-6 h-6"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-secondary dark:text-white">Audio Library</h1>
        </div>
        <div class="grid md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="block text-sm font-bold text-gray-500 dark:text-gray-400 ml-1 uppercase tracking-widest">Select Qari</label>
                <div class="relative">
                    <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 text-primary w-5 h-5"></i>
                    <select id="reciter-select" onchange="changeReciter(this.value)" class="w-full pl-12 pr-4 py-4 border border-gray-200 dark:border-gray-600 rounded-2xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none font-bold transition-all appearance-none cursor-pointer">
                        <?php foreach ($activeReciters as $r): ?>
                        <option value="<?= htmlspecialchars($r['name']) ?>" data-subfolder="<?= htmlspecialchars($r['subfolder']) ?>"><?= htmlspecialchars($r['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="space-y-2">
                <label class="block text-sm font-bold text-gray-500 dark:text-gray-400 ml-1 uppercase tracking-widest">Quick Search</label>
                <div class="relative">
                    <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 w-5 h-5"></i>
                    <input type="text" id="surah-search" placeholder="Surah name or number..." oninput="filterSurahs(this.value)"
                           class="w-full pl-12 pr-4 py-4 border border-gray-200 dark:border-gray-600 rounded-2xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all font-medium" />
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden transition-colors">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 divide-y md:divide-y-0 md:gap-px bg-gray-100 dark:bg-gray-700">
            <?php foreach ($surahNamesEn as $idx => $name): ?>
            <div class="surah-item bg-white dark:bg-gray-800 p-5 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors group"
                 data-id="<?= $idx + 1 ?>" data-name="<?= htmlspecialchars($name) ?>">
                <div class="flex items-center space-x-4">
                    <span class="text-gray-300 dark:text-gray-600 font-black text-xl w-10"><?= $idx + 1 ?></span>
                    <span class="font-extrabold text-gray-800 dark:text-gray-100 text-lg"><?= htmlspecialchars($name) ?></span>
                </div>
                <button onclick="playSurahAudioFromPage(<?= $idx + 1 ?>, '<?= htmlspecialchars($name, ENT_QUOTES) ?>')"
                        class="p-3 bg-primary/5 text-primary rounded-2xl group-hover:bg-primary group-hover:text-white transition shadow-sm">
                    <i data-lucide="play" class="w-[22px] h-[22px] fill-current ml-0.5"></i>
                </button>
            </div>
            <?php endforeach; ?>
        </div>
        <div id="no-results" class="p-16 text-center text-gray-400 font-bold hidden">No results found.</div>
    </div>
</div>

<!-- Auto-play data (hidden, used by JS) -->
<?php $autoPlayId = $_GET['autoplay'] ?? null; $autoPlayName = $_GET['name'] ?? null; ?>
<?php if ($autoPlayId): ?>
<div id="auto-play-data" data-surah-id="<?= (int)$autoPlayId ?>" data-surah-name="<?= htmlspecialchars($autoPlayName ?: ($surahNamesEn[(int)$autoPlayId - 1] ?? ''), ENT_QUOTES) ?>"></div>
<?php endif; ?>

<script>
const SURAH_NAMES_EN = <?= json_encode($surahNamesEn) ?>;
document.addEventListener('DOMContentLoaded', function() {
    initAudioPage();
    if (window.lucide) lucide.createIcons();
});
</script>
<script src="/assets/js/audio.js"></script>

<?php
$content = ob_get_clean();
renderLayout('Audio Library - Quran.et', $content, 'audio', $translations, $currentTranslationId, $donationConfig);
?>
