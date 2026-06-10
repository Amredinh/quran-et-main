<?php
/**
 * Home Page - replaces pages/Home.tsx
 */

$currentTranslationId = $_COOKIE['active_lang'] ?? 'am';
$lastReadCookie = $_COOKIE['lastRead'] ?? null;
$lastRead = $lastReadCookie ? json_decode($lastReadCookie, true) : null;

$recommendedId = (int)date('w') === 5 ? 18 : 1;
$recommendedMeta = $surahMeta[$recommendedId - 1] ?? null;

$isLangAmharic = $currentTranslationId === 'am';

ob_start();
?>

<div class="space-y-12 animate-fade-in">
    <!-- Continue Reading / Start Banner -->
    <section>
        <?php if ($lastRead && isset($lastRead['surahId'])): ?>
        <div class="bg-amber-50 dark:bg-amber-900/30 border-l-4 border-amber-500 p-6 rounded-r-2xl flex justify-between items-center shadow-lg shadow-amber-500/5 transition-colors">
            <div>
                <h3 class="text-amber-800 dark:text-amber-400 font-bold text-lg">ቀጥል ማንበብ | Continue Reading</h3>
                <p class="text-amber-700 dark:text-amber-300">የመጨረሻው የተነበበው ሱራ፡ <?= htmlspecialchars($lastRead['name']) ?></p>
            </div>
            <a href="/<?= htmlspecialchars($currentTranslationId) ?>/<?= (int)$lastRead['surahId'] ?>" class="bg-amber-500 text-white px-6 py-2.5 rounded-xl hover:bg-amber-600 transition shadow-md font-bold text-sm">
                ማንበብ ቀጥል (Resume)
            </a>
        </div>
        <?php else: ?>
        <div class="bg-primary/5 dark:bg-primary/10 border-l-4 border-primary p-6 rounded-r-2xl flex justify-between items-center shadow-lg transition-colors">
            <div>
                <h3 class="text-primary font-bold text-lg">መግቢያ | Start Studying</h3>
                <p class="text-gray-600 dark:text-gray-300">በሱረቱል አል-ፋቲሃ (መክፈቻው) ይጀምሩ።</p>
            </div>
            <a href="/<?= htmlspecialchars($currentTranslationId) ?>/1" class="bg-primary text-white px-6 py-2.5 rounded-xl hover:bg-green-600 transition shadow-md font-bold text-sm">
                አሁን ጀምር (Start Now)
            </a>
        </div>
        <?php endif; ?>
    </section>

    <!-- Hero / Gateway Section -->
    <section class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-2xl overflow-hidden p-8 md:p-16 relative border border-gray-100 dark:border-gray-700 transition-colors">
        <div class="absolute top-0 right-0 w-80 h-80 bg-primary/5 rounded-full -mr-20 -mt-20 z-0"></div>
        <div class="relative z-10 max-w-4xl mx-auto text-center space-y-8">
            <div class="flex flex-col items-center">
                <img src="/logo.webp" alt="Logo" class="w-24 h-24 mb-6 drop-shadow-xl" />
                <h1 class="text-4xl md:text-6xl font-extrabold text-secondary dark:text-white tracking-tight leading-tight">
                    የኢትዮጵያ <span class="text-primary">ቁርአን</span> ፖርታል
                </h1>
                <p class="text-gray-500 dark:text-gray-400 text-lg md:text-xl max-w-2xl mx-auto mt-4">
                    በአማርኛ እና በሌሎች የሀገራችን ቋንቋዎች የተተረጎሙ የቁርኣን አንቀጾች መከታተያ፣ መማሪያና ማድመጫ መድረክ።
                </p>
            </div>
            <form action="/<?= htmlspecialchars($currentTranslationId) ?>/1" method="GET" onsubmit="return handleHeroOpen(event)" class="bg-gray-50 dark:bg-gray-700/50 p-3 md:p-5 rounded-[2rem] shadow-inner grid grid-cols-1 md:grid-cols-12 gap-3 items-stretch">
                <div class="md:col-span-4 relative bg-white dark:bg-gray-700 rounded-2xl border border-gray-200 dark:border-gray-600">
                    <select id="hero-surah-select" onchange="document.getElementById('hero-surah-num').value = this.value" class="w-full h-full px-4 py-3.5 rounded-2xl outline-none appearance-none bg-transparent text-gray-800 dark:text-white cursor-pointer font-medium">
                        <option value="" disabled selected class="bg-white dark:bg-gray-800">ምረጥ ሱራ (Select Surah)</option>
                        <?php foreach ($surahNamesEn as $idx => $name): ?>
                        <option value="<?= $idx + 1 ?>" class="bg-white dark:bg-gray-800"><?= $idx + 1 ?>. <?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none w-[18px] h-[18px]"></i>
                </div>
                <div class="md:col-span-2 bg-white dark:bg-gray-700 rounded-2xl border border-gray-200 dark:border-gray-600 flex items-center px-4">
                    <span class="text-gray-400 mr-2 text-sm font-bold">#</span>
                    <input id="hero-surah-num" type="number" placeholder="No." min="1" max="114"
                           class="w-full bg-transparent outline-none text-gray-800 dark:text-white placeholder-gray-400 font-bold" />
                </div>
                <div class="md:col-span-2 bg-white dark:bg-gray-700 rounded-2xl border border-gray-200 dark:border-gray-600 flex items-center px-4">
                    <span class="text-gray-400 mr-2 text-sm font-bold">Aya</span>
                    <input id="hero-ayah-num" type="number" placeholder="All" min="1"
                           class="w-full bg-transparent outline-none text-gray-800 dark:text-white placeholder-gray-400 font-bold" />
                </div>
                <div class="md:col-span-2 relative bg-white dark:bg-gray-700 rounded-2xl border border-gray-200 dark:border-gray-600">
                    <select id="hero-lang-select" class="w-full h-full px-4 py-3.5 rounded-2xl outline-none appearance-none bg-transparent text-gray-800 dark:text-white cursor-pointer font-bold">
                        <option value="am" <?= $currentTranslationId === 'am' ? 'selected' : '' ?> class="bg-white dark:bg-gray-800">Amharic</option>
                        <?php foreach ($translations as $t): ?>
                        <option value="<?= htmlspecialchars(is_array($t) ? $t['id'] : $t->id) ?>" <?= $currentTranslationId === (is_array($t) ? $t['id'] : $t->id) ? 'selected' : '' ?> class="bg-white dark:bg-gray-800"><?= htmlspecialchars(is_array($t) ? $t['name'] : $t->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i data-lucide="globe" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none w-[18px] h-[18px]"></i>
                </div>
                <button type="submit" class="md:col-span-2 bg-primary text-white py-3.5 rounded-2xl hover:bg-green-600 transition font-extrabold shadow-lg shadow-green-500/20 flex justify-center items-center cursor-pointer">
                    ክፈት (Open)
                </button>
            </form>
        </div>
    </section>

    <!-- Daily Recommendation + Recent Blog -->
    <section class="grid md:grid-cols-2 gap-8">
        <div class="bg-gradient-to-br from-secondary to-green-800 text-white p-8 md:p-10 rounded-3xl shadow-2xl relative overflow-hidden group">
            <div class="relative z-10">
                <span class="inline-block px-3 py-1 bg-white/20 rounded-full text-xs font-bold uppercase tracking-widest mb-4 backdrop-blur-md">ዕለታዊ ምክር | Daily Recommendation</span>
                <?php if ($recommendedMeta): ?>
                <h2 class="text-3xl font-bold mb-4">ሱረቱል <?= $isLangAmharic ? htmlspecialchars($recommendedMeta['nameAmh']) : htmlspecialchars($recommendedMeta['nameEn']) ?></h2>
                <?php endif; ?>
                <p class="text-white/70 mb-8 max-w-sm leading-relaxed">ቀንዎን በቁርኣን አስተንትኖ እና በውብ ድምጽ ያሸብርቁ።</p>
                <div class="flex space-x-4">
                    <a href="/<?= htmlspecialchars($currentTranslationId) ?>/<?= $recommendedId ?>" class="flex items-center space-x-2 bg-white text-secondary hover:bg-white/90 px-6 py-3 rounded-xl font-bold transition shadow-xl text-sm">
                        <i data-lucide="book-open" class="w-[18px] h-[18px]"></i> <span>አንብብ | Read</span>
                    </a>
                    <a href="/audio" class="flex items-center space-x-2 bg-white/10 hover:bg-white/20 px-6 py-3 rounded-xl font-bold transition backdrop-blur-md border border-white/20 text-sm">
                        <i data-lucide="headphones" class="w-[18px] h-[18px]"></i> <span>አድምጥ | Listen</span>
                    </a>
                </div>
            </div>
            <div class="absolute -bottom-10 -right-10 text-white/5 transition group-hover:scale-110 duration-700">
                <i data-lucide="book-open" class="w-[300px] h-[300px]"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-8 md:p-10 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 flex flex-col justify-between transition-colors">
            <div>
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">የቅርብ ጊዜ ፅሁፎች</h2>
                    <a href="/blog" class="text-primary hover:text-green-600 flex items-center text-sm font-bold">ሁሉም ፅሁፎች <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i></a>
                </div>
                <?php if (!empty($blogs)): ?>
                <?php $firstBlog = reset($blogs); ?>
                <div class="group cursor-pointer" onclick="window.location='/blog/<?= (int)(is_array($firstBlog) ? $firstBlog['id'] : $firstBlog->id) ?>'">
                    <h3 class="font-bold text-2xl text-gray-800 dark:text-gray-100 group-hover:text-primary transition leading-tight"><?= htmlspecialchars(is_array($firstBlog) ? $firstBlog['title'] : $firstBlog->title) ?></h3>
                    <p class="text-gray-500 dark:text-gray-400 text-base line-clamp-3 mt-4 leading-relaxed"><?= htmlspecialchars(is_array($firstBlog) ? $firstBlog['excerpt'] : $firstBlog->excerpt) ?></p>
                </div>
                <?php elseif (!empty($defaultBlogs)): ?>
                <div class="group cursor-pointer" onclick="window.location='/blog/1'">
                    <h3 class="font-bold text-2xl text-gray-800 dark:text-gray-100 group-hover:text-primary transition leading-tight"><?= htmlspecialchars($defaultBlogs[0]['title']) ?></h3>
                    <p class="text-gray-500 dark:text-gray-400 text-base line-clamp-3 mt-4 leading-relaxed"><?= htmlspecialchars($defaultBlogs[0]['excerpt']) ?></p>
                </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($blogs)): ?>
            <a href="/blog/<?= (int)(is_array($firstBlog) ? $firstBlog['id'] : $firstBlog->id) ?>" class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-6 py-3 rounded-xl font-bold hover:bg-primary hover:text-white transition self-start mt-6">ማንበብ ቀጥል</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Surah Grid -->
    <section>
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-8 border-b border-gray-150 dark:border-gray-800 pb-4">
            <div>
                <h2 class="text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight">የሱራዎች መውጫ | Qur'an Chapters</h2>
                <p class="text-xs text-slate-400 font-bold mt-1 uppercase tracking-tight">Browse and index total chapters of the Noble Qur'an.</p>
            </div>
            <div class="flex space-x-2 bg-gray-100 dark:bg-gray-800 p-1.5 rounded-xl border border-gray-200 dark:border-gray-700">
                <button onclick="setSortOrder('regular')" id="sort-regular" class="px-6 py-2 rounded-lg text-sm transition font-bold bg-white dark:bg-gray-700 shadow-md text-primary">በቅደም ተከተል (Sequential)</button>
                <button onclick="setSortOrder('revelation')" id="sort-revelation" class="px-6 py-2 rounded-lg text-sm transition font-bold text-gray-500 hover:text-primary">በወረደበት ቅደም ተከተል (Revelation)</button>
            </div>
        </div>

        <div id="surah-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 border-t border-b border-slate-100 dark:border-slate-800/80 mt-10">
            <?php foreach ($surahMeta as $surah): ?>
            <a href="/<?= htmlspecialchars($currentTranslationId) ?>/<?= $surah['id'] ?>"
               class="surah-grid-item group flex items-center justify-between p-6 hover:bg-slate-50/20 dark:hover:bg-slate-850/20 transition-all duration-300 border-b border-slate-100 dark:border-slate-850/60 md:border-r"
               data-id="<?= $surah['id'] ?>">
                <div class="flex items-center space-x-4">
                    <div class="relative w-11 h-11 flex items-center justify-center flex-shrink-0 select-none">
                        <div class="absolute inset-0 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-sm transform rotate-45 group-hover:border-primary/50 transition duration-300"></div>
                        <div class="absolute inset-0 border border-slate-200 dark:border-slate-700 bg-transparent rounded-sm transform rotate-0 scale-[0.88] group-hover:border-primary/30 transition duration-300"></div>
                        <span class="relative z-10 text-xs font-bold text-slate-500 dark:text-slate-400 group-hover:text-primary transition duration-300"><?= $surah['id'] ?></span>
                    </div>
                    <div class="space-y-1">
                        <span class="block amharic text-slate-800 dark:text-slate-200 leading-none group-hover:text-primary transition duration-300 pr-1 select-none"><?= $isLangAmharic ? htmlspecialchars($surah['nameAmh']) : htmlspecialchars($surah['nameEn']) ?></span>
                        <div class="flex items-center space-x-1.5 text-xs text-slate-400 dark:text-slate-500 font-bold select-none">
                            <i data-lucide="book-open" class="w-[13px] h-[13px] text-sky-400 dark:text-sky-500 fill-sky-200/10"></i>
                            <span><?= $surah['ayahCount'] ?> Verse</span>
                        </div>
                    </div>
                </div>
                <div class="text-right pl-2">
                    <h4 class="text-[#2d3748] dark:text-slate-100 group-hover:text-primary transition-colors duration-300 leading-none grid-arabic">
                        surah<?= str_pad($surah['id'], 3, '0', STR_PAD_LEFT) ?>
                    </h4>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<script>
const SURAH_METADATA = <?= json_encode($surahMeta) ?>;
const REVELATION_ORDER = <?= json_encode($revelationOrder) ?>;

function setSortOrder(order) {
    const grid = document.getElementById('surah-grid');
    if (!grid) return;
    
    const items = Array.from(grid.querySelectorAll('.surah-grid-item'));
    const sorted = order === 'regular'
        ? items.sort((a, b) => parseInt(a.dataset.id) - parseInt(b.dataset.id))
        : REVELATION_ORDER.map(id => items.find(el => parseInt(el.dataset.id) === id)).filter(Boolean);
    
    grid.innerHTML = '';
    sorted.forEach(el => grid.appendChild(el));
    
    document.getElementById('sort-regular').className = 'px-6 py-2 rounded-lg text-sm transition font-bold ' + (order === 'regular' ? 'bg-white dark:bg-gray-700 shadow-md text-primary' : 'text-gray-500 hover:text-primary');
    document.getElementById('sort-revelation').className = 'px-6 py-2 rounded-lg text-sm transition font-bold ' + (order === 'revelation' ? 'bg-white dark:bg-gray-700 shadow-md text-primary' : 'text-gray-500 hover:text-primary');
}

function handleHeroOpen(event) {
    event.preventDefault();
    const surah = document.getElementById('hero-surah-num').value || document.getElementById('hero-surah-select').value;
    const ayah = document.getElementById('hero-ayah-num').value;
    const lang = document.getElementById('hero-lang-select').value || 'am';
    if (!surah || surah < 1 || surah > 114) return false;
    window.location.href = '/' + lang + '/' + surah + (ayah ? '?aya=' + ayah : '');
    return false;
}

document.addEventListener('DOMContentLoaded', function() {
    if (window.lucide) lucide.createIcons();
});
</script>

<?php
$content = ob_get_clean();
renderLayout('Quran.et - Ethiopian Quran Portal', $content, 'home', $translations, $currentTranslationId, $donationConfig);
?>
