<?php
/**
 * SHADOWS OF BANGLA — Single Story Reader
 * Cinematic Reading Experience with Progress Indicator
 */

define('APP_INIT', true);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db_connection();

$slug = trim($_GET['slug'] ?? '');
$id = (int)($_GET['id'] ?? 0);

if (empty($slug) && $id <= 0) {
    header("Location: stories.php");
    exit;
}

$story = null;
$relatedStories = [];

if ($pdo) {
    try {
        if (!empty($slug)) {
            $stmt = $pdo->prepare("SELECT s.*, c.name as category_name, c.slug as category_slug 
                                   FROM stories s 
                                   JOIN categories c ON s.category_id = c.id 
                                   WHERE s.slug = :slug AND s.status = 'published' LIMIT 1");
            $stmt->execute([':slug' => $slug]);
        } else {
            $stmt = $pdo->prepare("SELECT s.*, c.name as category_name, c.slug as category_slug 
                                   FROM stories s 
                                   JOIN categories c ON s.category_id = c.id 
                                   WHERE s.id = :id AND s.status = 'published' LIMIT 1");
            $stmt->execute([':id' => $id]);
        }
        $story = $stmt->fetch();

        if ($story) {
            // Increment view count
            $upd = $pdo->prepare("UPDATE stories SET views_count = views_count + 1 WHERE id = :id");
            $upd->execute([':id' => $story['id']]);

            // Fetch related stories
            $relStmt = $pdo->prepare("SELECT s.*, c.name as category_name, c.slug as category_slug 
                                      FROM stories s 
                                      JOIN categories c ON s.category_id = c.id 
                                      WHERE s.id != :id AND s.status = 'published' 
                                      ORDER BY s.created_at DESC LIMIT 3");
            $relStmt->execute([':id' => $story['id']]);
            $relatedStories = $relStmt->fetchAll();
        }
    } catch (PDOException $e) {
        error_log('Story query error: ' . $e->getMessage());
    }
}

if (!$story) {
    http_response_code(404);
    require_once __DIR__ . '/404.php';
    exit;
}

$pageTitle = $story['title'];
$pageDesc = $story['excerpt'];
$pageImage = SITE_URL . '/' . $story['cover_image'];
$activePage = 'stories';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Story Reading Header -->
<header style="padding: 9rem 0 4rem; background: linear-gradient(180deg, #050608 0%, #11131a 100%); border-bottom: 1px solid var(--border-subtle); position: relative;">
    <div class="container" style="max-width: 860px;">
        <div style="margin-bottom: 1.5rem;">
            <a href="stories.php" class="btn btn-secondary btn-sm" style="margin-bottom: 1rem;">
                <i class="fa-solid fa-arrow-left"></i> All Chronicles
            </a>
            <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 0.5rem;">
                <span class="category-pill"><?= e($story['category_name']) ?></span>
                <span style="font-size: 0.8rem; color: var(--text-muted);">
                    <i class="fa-regular fa-calendar"></i> <?= format_display_date($story['created_at']) ?>
                </span>
                <span style="font-size: 0.8rem; color: var(--text-muted);" class="reading-time">
                    <i class="fa-regular fa-clock"></i> <?= e($story['reading_time']) ?> min read
                </span>
            </div>
        </div>

        <h1 style="font-size: clamp(2.2rem, 4.5vw, 3.25rem); font-family: var(--font-serif); margin-bottom: 1.5rem; line-height: 1.25;">
            <?= e($story['title']) ?>
        </h1>

        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; border-top: 1px solid var(--border-subtle); padding-top: 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 42px; height: 42px; border-radius: 50%; background: var(--accent-red); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700;">
                    <i class="fa-solid fa-feather"></i>
                </div>
                <div>
                    <div style="font-size: 0.95rem; font-weight: 600; color: #fff;"><?= e($story['author']) ?></div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Special Archive Contributor</div>
                </div>
            </div>

            <div>
                <button onclick="navigator.clipboard.writeText(window.location.href); alert('Chronicle link copied to clipboard!');" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-share-nodes"></i> Share
                </button>
            </div>
        </div>
    </div>
</header>

<!-- Story Main Reading Container -->
<article class="section-padding">
    <div class="container" style="max-width: 860px;">
        <!-- Featured Cover Image -->
        <div style="border-radius: 10px; overflow: hidden; margin-bottom: 3rem; border: 1px solid var(--border-subtle); box-shadow: 0 15px 35px rgba(0,0,0,0.6);">
            <img src="<?= e($story['cover_image']) ?>" alt="<?= e($story['title']) ?>" style="width: 100%; height: auto; max-height: 480px; object-fit: cover;">
        </div>

        <!-- Excerpt Callout -->
        <div style="background: var(--bg-card); border-left: 4px solid var(--accent-red); padding: 1.5rem 2rem; border-radius: 0 8px 8px 0; margin-bottom: 2.5rem; font-style: italic; font-size: 1.15rem; color: var(--fog-grey); line-height: 1.7;">
            <?= e($story['excerpt']) ?>
        </div>

        <!-- Story Rich Content -->
        <div class="prose" style="font-size: 1.15rem; line-height: 1.9; color: var(--text-main);">
            <?= $story['content'] ?>
        </div>

        <!-- Editorial Footnote -->
        <div style="margin-top: 4rem; padding-top: 2rem; border-top: 1px solid var(--border-subtle); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="font-size: 0.85rem; color: var(--text-muted);">
                Published by <strong>Shadows of Bangla Historical Committee</strong>
            </div>
            <a href="submit-story.php" class="btn btn-outline-red btn-sm">
                <i class="fa-solid fa-feather"></i> Submit a Local Story
            </a>
        </div>
    </div>
</article>

<!-- Related Chronicles Section -->
<?php if (!empty($relatedStories)): ?>
<section class="section-padding" style="background: var(--bg-secondary); border-top: 1px solid var(--border-subtle);">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-subtitle">Parallel Echoes</span>
            <h2 class="section-title">Further Chronicles to Explore</h2>
        </div>

        <div class="cards-grid">
            <?php foreach ($relatedStories as $rel): ?>
                <article class="story-card">
                    <div class="card-media">
                        <img src="<?= e($rel['cover_image']) ?>" alt="<?= e($rel['title']) ?>" class="card-image" loading="lazy">
                        <div class="card-media-overlay"></div>
                    </div>
                    <div class="card-body">
                        <div class="story-meta">
                            <span><i class="fa-regular fa-clock"></i> <?= e($rel['reading_time']) ?> min read</span>
                        </div>
                        <h3 class="card-title">
                            <a href="story.php?slug=<?= e($rel['slug']) ?>"><?= e($rel['title']) ?></a>
                        </h3>
                        <p class="card-text">
                            <?= e(truncate_words($rel['excerpt'], 120)) ?>
                        </p>
                        <div class="card-footer">
                            <a href="story.php?slug=<?= e($rel['slug']) ?>" class="btn btn-outline-red btn-sm">
                                Read Chronicle <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

