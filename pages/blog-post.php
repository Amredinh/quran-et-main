<?php
/**
 * Blog Post Page - replaces pages/BlogPost.tsx
 */

$currentTranslationId = $_COOKIE['active_lang'] ?? 'am';
$postId = (int)$_GET['id'] ?? 0;

// Find post
$post = null;
foreach ($blogs as $p) {
    $pId = is_array($p) ? (int)$p['id'] : (int)$p->id;
    if ($pId === $postId) {
        $post = $p;
        break;
    }
}

ob_start();
?>

<div class="max-w-4xl mx-auto py-8 animate-fade-in">
    <a href="/blog" class="group inline-flex items-center text-gray-500 dark:text-gray-400 hover:text-primary mb-8 font-bold transition">
        <i data-lucide="arrow-left" class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform"></i> Back to Insights
    </a>

    <?php if ($post):
        $pTitle = is_array($post) ? $post['title'] : $post->title;
        $pImage = is_array($post) ? $post['image'] : $post->image;
        $pDate = is_array($post) ? $post['date'] : $post->date;
        $pContent = is_array($post) ? $post['content'] : $post->content;
    ?>
    <article class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden mb-12">
        <div class="h-64 md:h-[400px] w-full overflow-hidden relative">
            <img src="<?= htmlspecialchars($pImage) ?>" alt="<?= htmlspecialchars($pTitle) ?>" class="w-full h-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end p-8 md:p-12">
                <div>
                    <div class="flex items-center space-x-4 text-xs font-bold text-white/80 mb-3 uppercase tracking-widest">
                        <span class="flex items-center"><i data-lucide="calendar" class="w-[14px] h-[14px] mr-1"></i> <?= htmlspecialchars($pDate) ?></span>
                        <span class="flex items-center"><i data-lucide="user" class="w-[14px] h-[14px] mr-1"></i> Staff Writer</span>
                    </div>
                    <h1 class="text-3xl md:text-5xl font-extrabold text-white leading-tight"><?= htmlspecialchars($pTitle) ?></h1>
                </div>
            </div>
        </div>
        <div class="p-8 md:p-12">
            <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-[1.8] text-lg">
                <?php foreach (explode("\n", $pContent) as $i => $para): ?>
                <p class="mb-6"><?= htmlspecialchars($para) ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    </article>

    <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-8 md:p-10 shadow-xl border border-gray-100 dark:border-gray-700">
        <div class="flex items-center space-x-3 mb-8">
            <i data-lucide="message-square" class="text-primary w-6 h-6"></i>
            <h3 class="font-extrabold text-2xl text-gray-800 dark:text-white">Join the Conversation</h3>
        </div>
        <div class="space-y-6">
            <div class="bg-gray-50 dark:bg-gray-900/50 p-6 rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-3">
                    <p class="font-extrabold text-gray-900 dark:text-white">Admin Team</p>
                    <span class="text-[10px] uppercase font-bold text-gray-400">Featured</span>
                </div>
                <p class="text-gray-600 dark:text-gray-400 italic">This platform is built for you. We appreciate your feedback and reflections on this article.</p>
            </div>
            <div class="mt-8 space-y-4">
                <p class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest ml-1">Write a response</p>
                <div class="flex flex-col sm:flex-row gap-3">
                    <input type="text" placeholder="Share your thoughts..." class="flex-1 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white outline-none focus:border-primary transition-all" />
                    <button class="bg-primary text-white px-8 py-4 rounded-xl font-extrabold hover:bg-green-600 transition shadow-lg shadow-green-500/20">Post</button>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="text-center py-20">
        <h2 class="text-2xl font-bold text-gray-400 mb-4">Article Not Found</h2>
        <a href="/blog" class="text-primary font-bold hover:underline">Return to Blog</a>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.lucide) lucide.createIcons();
});
</script>

<?php
$content = ob_get_clean();
$title = $post ? (is_array($post) ? $post['title'] : $post->title) . ' - Quran.et Blog' : 'Blog Post - Quran.et';
renderLayout($title, $content, 'blog-post', $translations, $currentTranslationId, $donationConfig);
?>
