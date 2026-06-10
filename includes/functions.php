<?php
/**
 * Shared helper functions for rendering pages
 */

function renderHeader(string $title = 'Quran.et - Ethiopian Quran Portal', array $extraMeta = []): void {
    $metaTags = '';
    foreach ($extraMeta as $key => $value) {
        $metaTags .= '<meta name="' . htmlspecialchars($key) . '" content="' . htmlspecialchars($value) . '" />' . "\n";
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title) ?></title>
    <meta name="description" content="Explore the Holy Quran with Amharic translations, audio recitations, and educational articles. Quran.et - Ethiopian Quran Portal." />
    <meta name="keywords" content="Quran, Amharic, Ethiopia, Quran translation, Islamic, recitation, Ethiopian Quran" />
    <meta property="og:title" content="<?= htmlspecialchars($title) ?>" />
    <meta property="og:description" content="Explore the Holy Quran with Amharic translations and audio recitations." />
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Quran.et" />
    <link rel="icon" type="image/webp" href="/logo.webp" />
    <script>
      // Dark mode: apply immediately before paint to prevent FOUC
      (function() {
        var saved = localStorage.getItem('theme');
        if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
          document.documentElement.classList.add('dark');
        }
      })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            colors: {
              primary: '#10B981',
              secondary: '#064E3B',
              accent: '#F59E0B',
              dark: '#1F2937',
              light: '#F3F4F6'
            },
            fontFamily: {
              arabic: ['UthmanicHafs', '"Traditional Arabic"', 'serif'],
              amharic: ['AbyssinicaSIL', '"Noto Sans Ethiopic"', 'serif'],
              sans: ['Inter', 'sans-serif'],
            }
          }
        }
      }
    </script>
    <link rel="stylesheet" href="/assets/css/custom.css" />
    <?= $metaTags ?>
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
<?php
}

function renderFooter(): void {
?>
    <footer class="bg-secondary dark:bg-gray-950 text-white py-12 transition-colors duration-300 border-t border-white/5">
        <div class="container mx-auto px-4 text-center">
            <div class="flex justify-center items-center space-x-6 mb-8">
                <a href="/" class="p-3 bg-white/5 hover:bg-white/10 rounded-full transition"><i data-lucide="home" class="w-[22px] h-[22px]"></i></a>
                <a href="/audio" class="p-3 bg-white/5 hover:bg-white/10 rounded-full transition"><i data-lucide="mic" class="w-[22px] h-[22px]"></i></a>
                <a href="/blog" class="p-3 bg-white/5 hover:bg-white/10 rounded-full transition"><i data-lucide="file-text" class="w-[22px] h-[22px]"></i></a>
            </div>
            <div class="max-w-md mx-auto mb-6">
                <img src="/logo.webp" alt="Logo" class="w-16 h-16 mx-auto mb-4 opacity-80" />
                <h4 class="font-bold text-lg mb-2">Quran.et - የኢትዮጵያ ቁርኣን ፖርታል</h4>
                <p class="text-gray-400 text-sm">Spreading the message of the Quran in native Ethiopian languages with modern technology.</p>
            </div>
            <p class="text-gray-500 text-xs mt-8">&copy; <?= date('Y') ?> Quran.et. Crafted with faith.</p>
        </div>
    </footer>

    <script src="/assets/js/app.js"></script>
    <script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
</body>
</html>
<?php
}

function renderDonationModal(array $config): void {
    if (!$config['enabled']) return;
?>
    <div id="donation-modal" class="fixed inset-0 z-[100] hidden items-end sm:items-center justify-center p-4 bg-black/40 backdrop-blur-sm animate-fade-in">
        <div class="bg-white dark:bg-gray-800 w-full max-w-md rounded-2xl shadow-2xl p-6 sm:p-8 relative border border-gray-100 dark:border-gray-700">
            <button onclick="dismissDonation()" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
            <div class="w-16 h-16 bg-red-50 dark:bg-red-900/20 rounded-full flex items-center justify-center mb-6">
                <i data-lucide="heart" class="text-red-500 fill-red-500 w-8 h-8"></i>
            </div>
            <h3 class="text-2xl font-bold mb-3 text-gray-800 dark:text-white">Support Quran.et</h3>
            <p class="text-gray-600 dark:text-gray-300 mb-6 leading-relaxed">
                <?= htmlspecialchars($config['message']) ?>
            </p>
            <div class="flex flex-col gap-3">
                <a href="<?= htmlspecialchars($config['link']) ?>" target="_blank" rel="noopener noreferrer"
                   class="bg-primary hover:bg-green-600 text-white text-center py-3.5 rounded-xl font-bold transition shadow-lg shadow-green-500/20">
                    <?= htmlspecialchars($config['buttonText']) ?>
                </a>
                <button onclick="dismissDonation()" class="text-gray-500 dark:text-gray-400 font-medium hover:text-gray-700 dark:hover:text-gray-200 py-2 transition">
                    Maybe Later
                </button>
            </div>
        </div>
    </div>
<?php
}

function renderGlobalAudioPlayer(): void {
?>
    <div id="global-audio-player" class="fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md border-t border-gray-200 dark:border-gray-700 shadow-[0_-10px_30px_-5px_rgba(0,0,0,0.1)] p-4 md:p-6 z-[60] hidden animate-fade-in transition-all">
        <audio id="global-audio"></audio>
        <div class="container mx-auto flex flex-col md:flex-row items-center gap-4">
            <div class="w-full md:w-1/4 flex items-center justify-between">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <div id="player-surah-id" class="w-12 h-12 bg-primary rounded-xl flex items-center justify-center text-white font-bold flex-shrink-0 shadow-lg shadow-green-500/20">1</div>
                    <div class="truncate">
                        <h4 id="player-surah-name" class="font-bold text-gray-800 dark:text-white truncate">Al-Fatiha</h4>
                        <p id="player-reciter-name" class="text-xs text-gray-500 dark:text-gray-400 truncate font-medium">Reciter</p>
                    </div>
                </div>
                <button onclick="stopAudio()" class="md:hidden p-2 text-gray-400 hover:text-red-500 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="flex-1 w-full flex flex-col items-center space-y-2">
                <div class="flex items-center space-x-6">
                    <button id="player-repeat-btn" onclick="toggleRepeatMode()" class="transition text-gray-400 hover:text-primary">
                        <i data-lucide="repeat" class="w-[18px] h-[18px]"></i>
                    </button>
                    <button onclick="playerPrev()" class="text-gray-500 hover:text-primary transition">
                        <i data-lucide="skip-back" class="w-[22px] h-[22px] fill-current"></i>
                    </button>
                    <button id="player-play-btn" onclick="togglePlay()" class="p-4 bg-primary text-white rounded-2xl hover:scale-110 transition shadow-xl shadow-green-500/30">
                        <i data-lucide="pause" class="w-6 h-6 fill-current"></i>
                    </button>
                    <button onclick="playerNext()" class="text-gray-500 hover:text-primary transition">
                        <i data-lucide="skip-forward" class="w-[22px] h-[22px] fill-current"></i>
                    </button>
                    <button onclick="stopAudio()" class="hidden md:block text-gray-400 hover:text-red-500 transition">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <div class="w-full flex items-center space-x-3">
                    <span id="player-current-time" class="text-[10px] font-bold text-gray-400 tabular-nums w-8 text-right">0:00</span>
                    <div class="relative flex-1 group py-2 cursor-pointer">
                        <input id="player-progress" type="range" min="0" max="100" step="0.1" value="0"
                               oninput="seekAudio(this.value)"
                               class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-primary group-hover:h-2 transition-all" />
                    </div>
                    <span id="player-duration" class="text-[10px] font-bold text-gray-400 tabular-nums w-8">0:00</span>
                </div>
            </div>
            <div class="hidden md:flex w-1/4 justify-end items-center space-x-3">
                <i data-lucide="volume-2" class="w-[18px] h-[18px] text-gray-400"></i>
                <input type="range" class="w-24 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-primary"
                       oninput="setVolume(this.value)" value="80" />
            </div>
        </div>
    </div>
<?php
}

function renderLayout(string $title, string $content, string $currentPage, array $translations = [], string $currentTranslationId = 'am', array $donationConfig = []): void {
    renderHeader($title);
?>
    <div class="min-h-screen flex flex-col bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 font-sans transition-colors duration-300">
        <header class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-40 transition-colors duration-300 border-b border-gray-100 dark:border-gray-700">
            <div class="container mx-auto px-4 h-16 flex items-center justify-between">
                <a href="/" class="flex items-center space-x-2">
                    <img src="/logo.webp" alt="Quran.et Logo" class="w-10 h-10 object-contain rounded-full shadow-sm" />
                    <span class="text-xl font-extrabold text-secondary dark:text-white hidden sm:block tracking-tight">Quran.et</span>
                </a>
                <div class="flex items-center space-x-4 md:space-x-6">
                    <nav class="hidden md:flex space-x-6">
                        <a href="/" class="<?= $currentPage === 'home' ? 'text-primary font-bold' : 'text-gray-600 dark:text-gray-300 hover:text-primary dark:hover:text-primary transition-colors' ?>">Home</a>
                        <a href="/blog" class="<?= $currentPage === 'blog' || $currentPage === 'blog-post' ? 'text-primary font-bold' : 'text-gray-600 dark:text-gray-300 hover:text-primary dark:hover:text-primary transition-colors' ?>">Blog</a>
                        <a href="/audio" class="<?= $currentPage === 'audio' ? 'text-primary font-bold' : 'text-gray-600 dark:text-gray-300 hover:text-primary dark:hover:text-primary transition-colors' ?>">Audio</a>
                    </nav>
                    <div class="flex items-center space-x-3">
                        <div class="relative group">
                            <div class="flex items-center cursor-pointer p-1.5 px-3 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                                <i data-lucide="globe" class="w-[18px] h-[18px] mr-2 text-primary"></i>
                                <span class="uppercase text-xs font-bold"><?= htmlspecialchars($currentTranslationId) ?></span>
                            </div>
                            <div class="absolute right-0 top-full mt-2 w-40 bg-white dark:bg-gray-800 border dark:border-gray-700 shadow-xl rounded-xl overflow-hidden hidden group-hover:block animate-fade-in">
                                <button onclick="setTranslationId('am')" class="block w-full text-left px-4 py-2.5 text-sm hover:bg-primary/10 transition <?= $currentTranslationId === 'am' ? 'text-primary font-bold bg-primary/5' : 'text-gray-700 dark:text-gray-300' ?>">Amharic</button>
                                <?php foreach ($translations as $t): ?>
                                <button onclick="setTranslationId('<?= htmlspecialchars(is_array($t) ? $t['id'] : $t->id) ?>')" class="block w-full text-left px-4 py-2.5 text-sm hover:bg-primary/10 transition <?= $currentTranslationId === (is_array($t) ? $t['id'] : $t->id) ? 'text-primary font-bold bg-primary/5' : 'text-gray-700 dark:text-gray-300' ?>"><?= htmlspecialchars(is_array($t) ? $t['name'] : $t->name) ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <button onclick="toggleDarkMode()" class="p-2 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 transition-colors" aria-label="Toggle Dark Mode">
                            <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
                            <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-grow container mx-auto px-4 py-6 md:py-10">
            <?= $content ?>
        </main>

        <?php renderDonationModal($donationConfig); ?>
        <?php renderGlobalAudioPlayer(); ?>
        <?php renderFooter(); ?>
    </div>
<?php
}

function renderNavLinks(string $currentPage): void {
    // Used inside header - now handled in renderLayout
}
