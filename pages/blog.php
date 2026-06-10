<?php
/**
 * Blog Listing Page - replaces pages/BlogPage.tsx
 */

$currentTranslationId = $_COOKIE['active_lang'] ?? 'am';

ob_start();
?>

<div class="max-w-4xl mx-auto space-y-8 animate-fade-in">
    <div class="text-center mb-16">
        <h1 class="text-4xl font-extrabold text-secondary dark:text-white mb-4">Blog & Insights</h1>
        <p class="text-gray-500 dark:text-gray-400">Deep reflections and educational content on the Holy Quran.</p>
    </div>
    
    <?php if (!empty($blogs)): ?>
    <?php foreach ($blogs as $post):
        $postId = is_array($post) ? $post['id'] : $post->id;
        $postTitle = is_array($post) ? $post['title'] : $post->title;
        $postImage = is_array($post) ? $post['image'] : $post->image;
        $postDate = is_array($post) ? $post['date'] : $post->date;
        $postExcerpt = is_array($post) ? $post['excerpt'] : $post->excerpt;
    ?>
    <article class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden flex flex-col md:flex-row hover:shadow-2xl transition-all duration-300 group">
        <div class="md:w-1/3 h-48 md:h-auto overflow-hidden">
            <img src="<?= htmlspecialchars($postImage) ?>" alt="<?= htmlspecialchars($postTitle) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-700" />
        </div>
        <div class="p-6 md:p-8 flex-1 flex flex-col justify-center">
            <div class="flex items-center space-x-4 text-xs font-bold text-gray-400 dark:text-gray-500 mb-3 uppercase tracking-widest">
                <span class="flex items-center"><i data-lucide="calendar" class="w-[14px] h-[14px] mr-1"></i> <?= htmlspecialchars($postDate) ?></span>
                <span class="flex items-center"><i data-lucide="user" class="w-[14px] h-[14px] mr-1"></i> Editor</span>
            </div>
            <h2 class="text-2xl font-extrabold text-gray-800 dark:text-gray-100 mb-3 group-hover:text-primary cursor-pointer transition-colors"
                onclick="window.location='/blog/<?= (int)$postId ?>'">
                <?= htmlspecialchars($postTitle) ?>
            </h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6 leading-relaxed line-clamp-2"><?= htmlspecialchars($postExcerpt) ?></p>
            <a href="/blog/<?= (int)$postId ?>"
               class="bg-gray-50 dark:bg-gray-700 text-primary font-bold px-6 py-2 rounded-xl hover:bg-primary hover:text-white self-start flex items-center transition-all">
                Read More <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
            </a>
        </div>
    </article>
    <?php endforeach; ?>
    <?php else: ?>
    <div class="text-center py-20 text-gray-400 font-bold border-2 border-dashed rounded-3xl">
        No articles published yet.
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
renderLayout('Blog & Insights - Quran.et', $content, 'blog', $translations, $currentTranslationId, $donationConfig);
?>
