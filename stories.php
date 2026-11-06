<?php
/**
 * SHADOWS OF BANGLA — Mystery Stories Archive
 * Editorial Chronicles of Abandonment, Lore & Royal Enigmas
 */

define('APP_INIT', true);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Chronicles & Mystery Stories';
$activePage = 'stories';

$pdo = get_db_connection();

$search = trim($_GET['search'] ?? '');
$categorySlug = trim($_GET['category'] ?? '');
$categories = get_categories($pdo);

$featuredStory = null;
$stories = [];

if ($pdo) {
    try {
        $where = ["s.status = 'published'"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(s.title LIKE :s1 OR s.excerpt LIKE :s2 OR s.content LIKE :s3)";
            $params[':s1'] = "%{$search}%";
            $params[':s2'] = "%{$search}%";
            $params[':s3'] = "%{$search}%";
        }

        if (!empty($categorySlug)) {
            $where[] = "c.slug = :categorySlug";
            $params[':categorySlug'] = $categorySlug;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        // Fetch lead featured story if no active search
        if (empty($search) && empty($categorySlug)) {
            $featStmt = $pdo->query("SELECT s.*, c.name as category_name, c.slug as category_slug 
                                     FROM stories s 
                                     JOIN categories c ON s.category_id = c.id 
                                     WHERE s.status = 'published' AND s.is_featured = 1 
                                     ORDER BY s.id ASC LIMIT 1");
            $featuredStory = $featStmt->fetch();
        }

        // Fetch all stories
        $sql = "SELECT s.*, c.name as category_name, c.slug as category_slug 
                FROM stories s 
                JOIN categories c ON s.category_id = c.id 
                {$whereClause} 
                ORDER BY s.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $stories = $stmt->fetchAll();

    } catch (PDOException $e) {
        error_log('Stories query error: ' . $e->getMessage());
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Stories Header Banner -->
<section style="padding: 7.5rem 0 3.5rem; background: linear-gradient(180deg, #050608 0%, #11131a 100%); border-bottom: 1px solid var(--border-subtle);">
    <div class="container text-center">
        <span class="section-subtitle">Literary Archive</span>
        <h1 class="section-title" style="font-size: 2.75rem; margin-bottom: 0.75rem;">Chronicles of the Dark</h1>
        <p class="section-description">
            Editorial deep dives into forgotten histories, abandoned dynasties, and the spectral folklore of the Bengal delta.
        </p>
    </div>
</section>

<!-- Filter Strip -->
<section style="background: var(--bg-card); border-bottom: 1px solid var(--border-subtle); padding: 1.25rem 0;">
    <div class="container">
        <form method="GET" action="stories.php" style="display: flex; gap: 1rem; align-items: center; justify-content: space-between; flex-wrap: wrap;">
            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                <a href="stories.php" class="category-pill <?= empty($categorySlug) ? 'active' : '' ?>" style="<?= empty($categorySlug) ? 'border-color: var(--accent-red); color: #fff;' : '' ?>">
                    All Chronicles
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a href="stories.php?category=<?= e($cat['slug']) ?>" class="category-pill" style="<?= $categorySlug === $cat['slug'] ? 'border-color: var(--accent-red); color: #fff;' : '' ?>">
                        <?= e($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <input type="text" name="search" class="form-control" placeholder="Search chronicles..." value="<?= e($search) ?>" style="padding: 0.5rem 1rem; font-size: 0.85rem; width: 220px;">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
            </div>
        </form>
    </div>
</section>

<!-- Lead Featured Story -->
<?php if ($featuredStory && empty($search) && empty($categorySlug)): ?>
<section class="section-padding" style="padding-bottom: 1rem;">
    <div class="container">
        <div style="background: var(--bg-card); border: 1px solid var(--border-accent); border-radius: 12px; overflow: hidden; display: grid; grid-template-columns: 1.2fr 1fr; gap: 0; box-shadow: 0 20px 45px rgba(0,0,0,0.7);">
            <div style="position: relative; height: 100%; min-height: 380px;">
                <img src="<?= e($featuredStory['cover_image']) ?>" alt="<?= e($featuredStory['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                <div style="position: absolute; top: 1.5rem; left: 1.5rem;">
                    <span class="category-pill" style="background: rgba(158, 27, 50, 0.85); color: #fff; font-weight: 700;">
                        FEATURED CHRONICLE
                    </span>
                </div>
            </div>
            <div style="padding: 3rem; display: flex; flex-direction: column; justify-content: center;">
                <div class="story-meta" style="margin-bottom: 1rem;">
                    <span><i class="fa-regular fa-calendar"></i> <?= format_display_date($featuredStory['created_at']) ?></span>
                    <span class="reading-time"><i class="fa-regular fa-clock"></i> <?= e($featuredStory['reading_time']) ?> min read</span>
                </div>
                <h2 style="font-size: 2rem; font-family: var(--font-serif); margin-bottom: 1rem; line-height: 1.3;">
                    <a href="story.php?slug=<?= e($featuredStory['slug']) ?>" style="color: #fff;"><?= e($featuredStory['title']) ?></a>
                </h2>
                <p style="color: var(--text-secondary); margin-bottom: 2rem; line-height: 1.7; font-size: 1.05rem;">
                    <?= e($featuredStory['excerpt']) ?>
                </p>
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 0.85rem; color: var(--text-muted);">
                        By <strong><?= e($featuredStory['author']) ?></strong>
                    </span>
                    <a href="story.php?slug=<?= e($featuredStory['slug']) ?>" class="btn btn-primary">
                        Read Full Story <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Stories Grid -->
<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Archived Manuscripts</span>
            <h2 class="section-title">All Published Chronicles</h2>
        </div>

        <?php if (!empty($stories)): ?>
            <div class="cards-grid">
                <?php foreach ($stories as $st): ?>
                    <article class="story-card">
                        <div class="card-media">
                            <img src="<?= e($st['cover_image']) ?>" alt="<?= e($st['title']) ?>" class="card-image" loading="lazy">
                            <div class="card-media-overlay"></div>
                            <div class="card-badges">
                                <span class="category-pill"><?= e($st['category_name']) ?></span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="story-meta">
                                <span><i class="fa-regular fa-calendar"></i> <?= format_display_date($st['created_at']) ?></span>
                                <span class="reading-time"><i class="fa-regular fa-clock"></i> <?= e($st['reading_time']) ?> min read</span>
                            </div>
                            <h3 class="card-title">
                                <a href="story.php?slug=<?= e($st['slug']) ?>"><?= e($st['title']) ?></a>
                            </h3>
                            <p class="card-text">
                                <?= e(truncate_words($st['excerpt'], 140)) ?>
                            </p>
                            <div class="card-footer">
                                <span style="font-size: 0.8rem; color: var(--text-muted);">
                                    By <?= e($st['author']) ?>
                                </span>
                                <a href="story.php?slug=<?= e($st['slug']) ?>" class="btn btn-outline-red btn-sm">
                                    Read Chronicle <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 4rem 1rem; background: var(--bg-card); border-radius: 8px;">
                <p>No chronicles found matching your query.</p>
                <a href="stories.php" class="btn btn-secondary btn-sm" style="margin-top: 1rem;">View All Chronicles</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

