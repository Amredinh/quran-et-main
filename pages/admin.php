<?php
/**
 * Admin Dashboard - replaces public/admin.html + Express admin routes
 * Complete admin panel with all CRUD operations.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/quran.php';

// Handle session-based auth
$isAuthenticated = false;
$authError = '';

// Check for session auth
if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true) {
    $isAuthenticated = true;
}

// Check for token-based auth (API-like, via cookie)
$token = $_COOKIE['admin_secret_token'] ?? '';
if (!$isAuthenticated && $token && hash_equals(ADMIN_SECRET_KEY, $token)) {
    $_SESSION['admin_authenticated'] = true;
    $isAuthenticated = true;
}

// Handle login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $key = $_POST['key'] ?? '';
    if (hash_equals(ADMIN_SECRET_KEY, $key)) {
        $_SESSION['admin_authenticated'] = true;
        setcookie('admin_secret_token', $key, time() + 86400 * 30, '/', '', false, true);
        $isAuthenticated = true;
    } else {
        $authError = 'Invalid admin key';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_authenticated']);
    setcookie('admin_secret_token', '', time() - 3600, '/');
    $isAuthenticated = false;
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quran.et - Control Room</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              primary: '#10B981',
              secondary: '#111827',
              accent: '#F59E0B'
            }
          }
        }
      }
    </script>
    <style>
      ::-webkit-scrollbar { width: 8px; height: 8px; }
      ::-webkit-scrollbar-track { background: #f1f1f1; }
      ::-webkit-scrollbar-thumb { background: #10B981; border-radius: 4px; }
      .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
      @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col font-sans">

<?php if (!$isAuthenticated): ?>

<!-- Auth Screen -->
<div id="auth-screen" class="fixed inset-0 bg-slate-950 flex flex-col items-center justify-center p-4 z-50">
    <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md border border-slate-100 text-center space-y-6">
        <div class="bg-red-50 w-16 h-16 rounded-2xl flex items-center justify-center mx-auto text-red-500">
            <i data-lucide="lock" class="w-8 h-8"></i>
        </div>
        <div>
            <h1 class="text-2xl font-black text-slate-800">Control Access Gate</h1>
            <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider">Secret Admin Workspace Required</p>
        </div>
        <form method="POST" action="/admin" class="space-y-4">
            <input type="hidden" name="action" value="login" />
            <input type="password" name="key" placeholder="Enter Admin Access Key"
                   class="w-full p-4 border border-slate-250 rounded-xl bg-slate-50 text-slate-800 outline-none focus:border-primary font-bold text-center text-lg placeholder-slate-400" />
            <button type="submit" class="w-full bg-emerald-600 text-white py-4 rounded-xl font-extrabold hover:bg-emerald-700 transition shadow-lg shadow-emerald-500/20">
                Authenticate
            </button>
        </form>
        <?php if ($authError): ?>
        <p class="text-xs text-red-500 font-bold"><?= htmlspecialchars($authError) ?></p>
        <?php endif; ?>
    </div>
</div>
<script>
    lucide.createIcons();
</script>
</body>
</html>

<?php else: 

// Load data from DB
try {
    require_once __DIR__ . '/../db.php';
    Database::init();
    $pdo = Database::getConnection();
    
    $blogs = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC")->fetchAll();
    $translations = $pdo->query("SELECT id, name, xml FROM translations ORDER BY created_at")->fetchAll();
    $reciters = $pdo->query("SELECT * FROM reciters ORDER BY id")->fetchAll();
    $donation = $pdo->query("SELECT * FROM donation_config ORDER BY id DESC LIMIT 1")->fetch();
} catch (Exception $e) {
    $blogs = [];
    $translations = [];
    $reciters = [];
    $donation = null;
}

$donationConfig = $donation ?: ['message' => '', 'button_text' => 'Donate Now', 'link' => 'https://example.com/donate', 'enabled' => 1];
?>

<!-- Dashboard -->
<div id="dashboard-screen" class="flex-grow flex flex-col">
    <!-- Header -->
    <header class="bg-slate-900 text-white border-b border-slate-800 sticky top-0 z-40 shadow-md">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="text-xl font-black tracking-tight flex items-center gap-2">
                    <span class="p-1 px-2.5 bg-emerald-500 text-slate-950 rounded-lg text-sm font-black">HQ</span>
                    Quran.et <span class="text-emerald-400 font-normal text-sm">Control Console</span>
                </span>
            </div>
            <div>
                <a href="/admin?logout=1" class="px-3.5 py-1.5 border border-slate-700 hover:border-red-500/50 hover:bg-red-500/10 rounded-lg text-xs font-bold text-slate-400 hover:text-red-400 transition inline-flex items-center gap-1.5">
                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i> Log Out
                </a>
            </div>
        </div>
    </header>

    <!-- Tabs -->
    <div class="border-b border-slate-200 bg-white">
        <div class="max-w-7xl mx-auto px-4 flex space-x-8 overflow-x-auto">
            <button onclick="setActiveTab('translations')" class="tab-btn px-1 py-4 border-b-2 border-emerald-500 font-extrabold text-sm tracking-tight text-emerald-600 transition" id="tab-translations">
                <i data-lucide="globe" class="w-4 h-4 inline"></i> Translation XMLs
            </button>
            <button onclick="setActiveTab('blogs')" class="tab-btn px-1 py-4 border-b-2 border-transparent text-slate-400 hover:text-slate-600 font-semibold text-sm transition" id="tab-blogs">
                <i data-lucide="file-text" class="w-4 h-4 inline"></i> Blog Archive
            </button>
            <button onclick="setActiveTab('reciters')" class="tab-btn px-1 py-4 border-b-2 border-transparent text-slate-400 hover:text-slate-600 font-semibold text-sm transition" id="tab-reciters">
                <i data-lucide="music" class="w-4 h-4 inline"></i> Audio Reciters
            </button>
            <button onclick="setActiveTab('donations')" class="tab-btn px-1 py-4 border-b-2 border-transparent text-slate-400 hover:text-slate-600 font-semibold text-sm transition" id="tab-donations">
                <i data-lucide="heart" class="w-4 h-4 inline"></i> Support & Donations
            </button>
        </div>
    </div>

    <!-- Alert Box -->
    <div class="max-w-7xl mx-auto w-full px-4 mt-6">
        <div id="alert-box" class="hidden p-4 rounded-xl flex items-center shadow-md animate-fade-in">
            <span id="alert-text" class="font-bold text-sm"></span>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8 flex-grow w-full">
        
        <!-- Translations Tab -->
        <section id="pane-translations" class="tab-pane space-y-8">
            <div class="grid md:grid-cols-3 gap-8">
                <div class="md:col-span-1 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="upload" class="text-emerald-500 w-5 h-5"></i> Add Translation XML
                    </h2>
                    <p class="text-xs text-slate-400">Upload standard translation XML files to instantly register indigenous languages.</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Language Name</label>
                            <input type="text" id="trans-name-input" placeholder="e.g. Oromo" class="w-full p-3 border border-slate-200 rounded-xl outline-none focus:border-primary text-sm font-bold bg-slate-50" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Language Code (Slug)</label>
                            <input type="text" id="trans-code-input" placeholder="e.g. orom" class="w-full p-3 border border-slate-200 rounded-xl outline-none focus:border-primary text-sm font-mono bg-slate-50" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Quran XML Source File</label>
                            <div class="relative border-2 border-dashed border-slate-200 hover:border-primary rounded-xl p-6 text-center cursor-pointer bg-slate-50 transition">
                                <input type="file" id="trans-file-input" accept=".xml" onchange="readXMLFile(event)" class="absolute inset-0 opacity-0 cursor-pointer" />
                                <div class="pointer-events-none space-y-1">
                                    <i data-lucide="file-code" class="w-8 h-8 text-slate-400 mx-auto"></i>
                                    <p id="xml-label" class="text-xs font-bold text-slate-500">Browse XML file</p>
                                </div>
                            </div>
                        </div>
                        <button onclick="deployTranslation()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-xl font-bold transition shadow-md shadow-emerald-500/10">
                            Deploy Translation XML
                        </button>
                    </div>
                </div>
                <div class="md:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="database" class="text-emerald-500 w-5 h-5"></i> Active Languages
                    </h2>
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-250">
                            <thead class="bg-slate-50 text-slate-500 text-xs font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-3.5 text-left">Language</th>
                                    <th class="px-6 py-3.5 text-left">Slug</th>
                                    <th class="px-6 py-3.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="translations-list-rows" class="divide-y divide-slate-200 bg-white text-sm font-semibold">
                                <?php if (empty($translations)): ?>
                                <tr><td colspan="3" class="p-8 text-center text-slate-400 font-bold">No translations added to database. AM (Amharic Default) is running from codebase.</td></tr>
                                <?php else: ?>
                                <?php foreach ($translations as $t): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-slate-800"><?= htmlspecialchars($t['name']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-slate-500 font-mono text-xs uppercase"><?= htmlspecialchars($t['id']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <button onclick="deleteTranslation('<?= htmlspecialchars($t['id'], ENT_QUOTES) ?>')" class="text-red-500 hover:text-red-700 transition font-bold text-xs bg-red-50 hover:bg-red-100 p-1.5 px-3 rounded-lg inline-flex items-center gap-1.5">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Blogs Tab -->
        <section id="pane-blogs" class="tab-pane hidden space-y-8">
            <div class="grid md:grid-cols-3 gap-8">
                <div class="md:col-span-1 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="plus-circle" class="text-emerald-500 w-5 h-5"></i> Write Article
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Article Title</label>
                            <input type="text" id="blog-title-input" placeholder="Title of post" class="w-full p-3 border border-slate-200 rounded-xl outline-none focus:border-primary text-sm bg-slate-50" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Banner Image URL</label>
                            <input type="text" id="blog-image-input" placeholder="https://images.unsplash..." class="w-full p-3 border border-slate-200 rounded-xl outline-none focus:border-primary text-sm bg-slate-50" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Snippet Summary</label>
                            <input type="text" id="blog-excerpt-input" placeholder="Highlight summary" class="w-full p-3 border border-slate-200 rounded-xl outline-none focus:border-primary text-sm bg-slate-50" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Detailed Content</label>
                            <textarea id="blog-content-input" rows="5" placeholder="Write full insight content here..." class="w-full p-3 border border-slate-200 rounded-xl outline-none focus:border-primary text-sm bg-slate-50"></textarea>
                        </div>
                        <button onclick="publishBlog()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-xl font-bold transition shadow-md shadow-emerald-500/10">
                            Publish Article
                        </button>
                    </div>
                </div>
                <div class="md:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="book-open" class="text-emerald-500 w-5 h-5"></i> Articles Archive
                    </h2>
                    <div id="blogs-archive-list" class="divide-y divide-slate-100">
                        <?php if (empty($blogs)): ?>
                        <p class="p-8 text-center text-slate-400 font-bold">Empty archives</p>
                        <?php else: ?>
                        <?php foreach ($blogs as $b): ?>
                        <div class="py-4 flex items-center justify-between gap-4">
                            <div class="flex items-center space-x-4">
                                <img src="<?= htmlspecialchars($b['image']) ?>" class="w-12 h-12 rounded-lg object-cover" />
                                <div>
                                    <h4 class="font-bold text-slate-800 leading-tight"><?= htmlspecialchars($b['title']) ?></h4>
                                    <span class="text-[10px] uppercase text-slate-400"><?= htmlspecialchars($b['date']) ?> | <?= htmlspecialchars($b['excerpt']) ?></span>
                                </div>
                            </div>
                            <button onclick="deleteBlog(<?= (int)$b['id'] ?>)" class="text-red-500 hover:text-red-700 font-bold text-xs bg-red-50 hover:bg-red-100 p-2 rounded-lg">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Reciters Tab -->
        <section id="pane-reciters" class="tab-pane hidden space-y-8">
            <div class="grid md:grid-cols-3 gap-8">
                <div class="md:col-span-1 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="user-plus" class="text-emerald-500 w-5 h-5"></i> Add Reciter
                    </h2>
                    <p class="text-xs text-slate-400">Add dynamic recitation channels hosted by EveryAyah.com or standard Surah recitation pools.</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Reciter Display Name</label>
                            <input type="text" id="reciter-name-input" placeholder="e.g. Abdullah Basfar" class="w-full p-3 border border-slate-200 rounded-xl outline-none focus:border-primary text-sm font-bold bg-slate-50" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Subfolder ID / Directory</label>
                            <input type="text" id="reciter-folder-input" placeholder="e.g. Abdullah_Basfar_32kbps" class="w-full p-3 border border-slate-200 rounded-xl outline-none focus:border-primary text-sm font-mono bg-slate-50" />
                        </div>
                        <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-xl border border-slate-150">
                            <div>
                                <span class="text-xs font-bold text-slate-800">EveryAyah.com Format</span>
                                <p class="text-[10px] text-slate-400">Plays ayahs step-by-step</p>
                            </div>
                            <input type="checkbox" id="reciter-type-input" checked class="w-5 h-5 accent-primary" />
                        </div>
                        <button onclick="addReciter()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-xl font-bold transition shadow-md shadow-emerald-500/10">
                            Register Reciter
                        </button>
                    </div>
                </div>
                <div class="md:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="music" class="text-emerald-500 w-5 h-5"></i> Audio Stream Registers
                    </h2>
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-250">
                            <thead class="bg-slate-50 text-slate-500 text-xs font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-3.5 text-left">Reciter</th>
                                    <th class="px-6 py-3.5 text-left">Subfolder ID</th>
                                    <th class="px-6 py-3.5 text-left">Scope</th>
                                    <th class="px-6 py-3.5 text-right">Delete</th>
                                </tr>
                            </thead>
                            <tbody id="reciters-list-rows" class="divide-y divide-slate-200 bg-white text-sm font-semibold">
                                <?php foreach ($reciters as $r): ?>
                                <tr>
                                    <td class="px-6 py-4 text-slate-800"><?= htmlspecialchars($r['name']) ?></td>
                                    <td class="px-6 py-4 text-slate-500 font-mono text-xs"><?= htmlspecialchars($r['subfolder']) ?></td>
                                    <td class="px-6 py-4">
                                        <span class="p-1 px-2 text-[10px] uppercase font-black rounded-md <?= $r['is_every_ayah'] ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' ?>">
                                            <?= $r['is_every_ayah'] ? 'Every Ayah' : 'Surah Level' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button onclick="deleteReciter('<?= htmlspecialchars($r['subfolder'], ENT_QUOTES) ?>')" class="text-red-500 hover:text-red-700 font-bold text-xs bg-red-50 hover:bg-red-100 p-1.5 px-3 rounded-lg inline-flex items-center gap-1.5 ml-auto">
                                            <i data-lucide="trash" class="w-3.5 h-3.5"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Donations Tab -->
        <section id="pane-donations" class="tab-pane hidden space-y-8">
            <div class="max-w-3xl bg-white p-8 rounded-3xl border border-slate-200 shadow-sm space-y-8">
                <div class="flex items-center space-x-3">
                    <div class="p-3.5 bg-red-50 text-red-500 rounded-2xl shadow-inner">
                        <i data-lucide="heart" class="w-6 h-6 fill-current"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-extrabold text-slate-800">Support & Donation Settings</h2>
                        <p class="text-xs text-slate-400">Configure global support overlays & donation CTA paths.</p>
                    </div>
                </div>
                <div class="space-y-6">
                    <div class="flex items-center justify-between p-5 bg-slate-50 rounded-2xl border border-slate-100">
                        <div>
                            <label class="font-bold text-slate-800 block">Enable Live Modal Overlay</label>
                            <span class="text-xs text-slate-400">Triggers a support prompt after a few seconds of initial visiting</span>
                        </div>
                        <input type="checkbox" id="donation-toggle" class="w-6 h-6 accent-primary" <?= $donationConfig['enabled'] ? 'checked' : '' ?> />
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Request Message</label>
                            <textarea id="donation-message-input" rows="3" class="w-full p-4 border border-slate-200 rounded-2xl outline-none focus:border-primary bg-slate-50 text-slate-700"><?= htmlspecialchars($donationConfig['message']) ?></textarea>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Button CTA Text</label>
                                <input type="text" id="donation-btn-input" value="<?= htmlspecialchars($donationConfig['button_text']) ?>" class="w-full p-4 border border-slate-200 rounded-2xl outline-none focus:border-primary bg-slate-50 font-bold" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Target Destination URL</label>
                                <input type="text" id="donation-link-input" value="<?= htmlspecialchars($donationConfig['link']) ?>" class="w-full p-4 border border-slate-200 rounded-2xl outline-none focus:border-primary bg-slate-50 font-mono text-sm" />
                            </div>
                        </div>
                    </div>
                    <button onclick="updateDonationConfig()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-8 py-3.5 rounded-xl transition shadow-lg shadow-emerald-500/20">
                        Save Donation Settings
                    </button>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
let xmlPayload = "";
const ADMIN_TOKEN = "<?= htmlspecialchars($token ?: '', ENT_QUOTES) ?>";

function triggerAlert(msg, type) {
    const box = document.getElementById('alert-box');
    const text = document.getElementById('alert-text');
    text.innerText = msg;
    box.className = 'p-4 rounded-xl flex items-center shadow-md animate-fade-in ' + (type === 'error' ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700');
    box.classList.remove('hidden');
    setTimeout(() => box.classList.add('hidden'), 4000);
}

function setActiveTab(tabId) {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.className = 'tab-btn px-1 py-4 border-b-2 border-transparent text-slate-400 hover:text-slate-600 font-semibold text-sm cursor-pointer transition';
    });
    document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.add('hidden'));
    const activeBtn = document.getElementById('tab-' + tabId);
    if (activeBtn) activeBtn.className = 'tab-btn px-1 py-4 border-b-2 border-emerald-500 font-extrabold text-sm tracking-tight text-emerald-600 cursor-pointer transition';
    const activePane = document.getElementById('pane-' + tabId);
    if (activePane) activePane.classList.remove('hidden');
}

async function apiFetch(url, options) {
    const token = ADMIN_TOKEN || localStorage.getItem('admin_secret_token') || '';
    const headers = {
        'Authorization': 'Bearer ' + token,
        'x-admin-token': token,
        'Content-Type': 'application/json',
    };
    const res = await fetch(url, { ...options, headers });
    if (!res.ok) {
        const err = await res.json().catch(() => ({ error: 'Request failed' }));
        throw new Error(err.error || 'HTTP ' + res.status);
    }
    return res.json();
}

function readXMLFile(e) {
    const file = e.target.files[0];
    if (!file) return;
    document.getElementById('xml-label').innerText = 'Loading ' + file.name + '...';
    const reader = new FileReader();
    reader.onload = function(evt) {
        xmlPayload = evt.target.result;
        document.getElementById('xml-label').innerText = 'XML Loaded: ' + file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
        triggerAlert('Quran Translation XML parsed. Ready to deploy.');
    };
    reader.readAsText(file);
}

async function deployTranslation() {
    const name = document.getElementById('trans-name-input').value;
    const slug = document.getElementById('trans-code-input').value.trim().toLowerCase();
    if (!name || !slug || !xmlPayload) {
        triggerAlert('Please feed name, slug code and XML file structure.', 'error');
        return;
    }
    try {
        await apiFetch('/api/admin/translations', {
            method: 'POST',
            body: JSON.stringify({ id: slug, name: name, xml: xmlPayload })
        });
        triggerAlert('Translation XML for ' + name + ' has been deployed live!');
        document.getElementById('trans-name-input').value = '';
        document.getElementById('trans-code-input').value = '';
        xmlPayload = '';
        document.getElementById('xml-label').innerText = 'Browse XML file';
        location.reload();
    } catch (e) {
        triggerAlert('Error deploying translation: ' + e.message, 'error');
    }
}

async function deleteTranslation(id) {
    if (!confirm('Are you sure you want to permanently delete this translation XML dataset?')) return;
    try {
        await apiFetch('/api/admin/translations/' + encodeURIComponent(id), { method: 'DELETE' });
        triggerAlert('Translation removed.');
        location.reload();
    } catch (e) {
        triggerAlert('Error: ' + e.message, 'error');
    }
}

async function publishBlog() {
    const title = document.getElementById('blog-title-input').value;
    const image = document.getElementById('blog-image-input').value;
    const excerpt = document.getElementById('blog-excerpt-input').value;
    const content = document.getElementById('blog-content-input').value;
    if (!title || !content) {
        triggerAlert('Title and detailed content cannot be empty assets.', 'error');
        return;
    }
    try {
        await apiFetch('/api/admin/blogs', {
            method: 'POST',
            body: JSON.stringify({ title, image, excerpt, content })
        });
        triggerAlert('Blog post has been published!');
        document.getElementById('blog-title-input').value = '';
        document.getElementById('blog-image-input').value = '';
        document.getElementById('blog-excerpt-input').value = '';
        document.getElementById('blog-content-input').value = '';
        location.reload();
    } catch (e) {
        triggerAlert('Error: ' + e.message, 'error');
    }
}

async function deleteBlog(id) {
    if (!confirm('Are you sure you want to delete this blog post?')) return;
    try {
        await apiFetch('/api/admin/blogs/' + id, { method: 'DELETE' });
        triggerAlert('Blog article has been deleted.');
        location.reload();
    } catch (e) {
        triggerAlert('Error: ' + e.message, 'error');
    }
}

async function addReciter() {
    const name = document.getElementById('reciter-name-input').value;
    const subfolder = document.getElementById('reciter-folder-input').value.trim();
    const isEveryAyah = document.getElementById('reciter-type-input').checked;
    if (!name || !subfolder) {
        triggerAlert('Both reciter display name and subfolder ID are required.', 'error');
        return;
    }
    try {
        await apiFetch('/api/admin/reciters', {
            method: 'POST',
            body: JSON.stringify({ name, subfolder, isEveryAyah })
        });
        triggerAlert('Reciter ' + name + ' successfully launched!');
        document.getElementById('reciter-name-input').value = '';
        document.getElementById('reciter-folder-input').value = '';
        location.reload();
    } catch (e) {
        triggerAlert('Error: ' + e.message, 'error');
    }
}

async function deleteReciter(subfolder) {
    if (!confirm('Are you sure you want to delete this reciter?')) return;
    try {
        await apiFetch('/api/admin/reciters', {
            method: 'DELETE',
            body: JSON.stringify({ subfolder })
        });
        triggerAlert('Reciter deleted.');
        location.reload();
    } catch (e) {
        triggerAlert('Error: ' + e.message, 'error');
    }
}

async function updateDonationConfig() {
    const enabled = document.getElementById('donation-toggle').checked;
    const message = document.getElementById('donation-message-input').value;
    const buttonText = document.getElementById('donation-btn-input').value;
    const link = document.getElementById('donation-link-input').value;
    try {
        await apiFetch('/api/admin/donation', {
            method: 'PUT',
            body: JSON.stringify({ message, buttonText, link, enabled })
        });
        triggerAlert('Support overlay configuration saved and deployed.');
    } catch (e) {
        triggerAlert('Error: ' + e.message, 'error');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();
});
</script>

</body>
</html>
<?php endif; ?>
