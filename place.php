<?php
/**
 * SHADOWS OF BANGLA — Place Details Dossier
 * Historical Background, Folklore, Coordinates & Archival Evidence
 */

define('APP_INIT', true);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db_connection();

$slug = trim($_GET['slug'] ?? '');
$id = (int)($_GET['id'] ?? 0);

if (empty($slug) && $id <= 0) {
    header("Location: explore.php");
    exit;
}

$place = null;
$galleryImages = [];
$relatedPlaces = [];

if ($pdo) {
    try {
        if (!empty($slug)) {
            $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug, c.icon as category_icon 
                                   FROM places p 
                                   JOIN categories c ON p.category_id = c.id 
                                   WHERE p.slug = :slug LIMIT 1");
            $stmt->execute([':slug' => $slug]);
        } else {
            $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug, c.icon as category_icon 
                                   FROM places p 
                                   JOIN categories c ON p.category_id = c.id 
                                   WHERE p.id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
        }
        $place = $stmt->fetch();

        if ($place) {
            // Increment view counter
            $upd = $pdo->prepare("UPDATE places SET views_count = views_count + 1 WHERE id = :id");
            $upd->execute([':id' => $place['id']]);

            // Fetch gallery images
            $imgStmt = $pdo->prepare("SELECT * FROM place_images WHERE place_id = :id ORDER BY is_primary DESC, id ASC");
            $imgStmt->execute([':id' => $place['id']]);
            $galleryImages = $imgStmt->fetchAll();

            // Fetch related places in the same division or category
            $relStmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug, c.icon as category_icon 
                                      FROM places p 
                                      JOIN categories c ON p.category_id = c.id 
                                      WHERE p.id != :id AND (p.division = :division OR p.category_id = :catId) 
                                      ORDER BY p.views_count DESC LIMIT 3");
            $relStmt->execute([
                ':id' => $place['id'],
                ':division' => $place['division'],
                ':catId' => $place['category_id']
            ]);
            $relatedPlaces = $relStmt->fetchAll();
        }
    } catch (PDOException $e) {
        error_log('Place query error: ' . $e->getMessage());
    }
}

if (!$place) {
    // 404 handler
    http_response_code(404);
    require_once __DIR__ . '/404.php';
    exit;
}

$pageTitle = $place['name'];
$pageDesc = $place['summary'];
$pageImage = SITE_URL . '/' . $place['cover_image'];
$activePage = 'explore';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hidden element for localStorage recently-viewed tracker in main.js -->
<div id="current-place-data" 
     data-id="<?= e($place['id']) ?>" 
     data-slug="<?= e($place['slug']) ?>" 
     data-name="<?= e($place['name']) ?>" 
     data-image="<?= e($place['cover_image']) ?>" 
     data-district="<?= e($place['district']) ?>" 
     data-division="<?= e($place['division']) ?>" 
     style="display: none;"></div>

<!-- Cinematic Place Hero -->
<section class="place-hero">
    <img src="<?= e($place['cover_image']) ?>" alt="<?= e($place['name']) ?>" class="place-hero-img">
    <div class="place-hero-overlay"></div>

    <div class="container" style="position: relative; z-index: 10;">
        <div style="margin-bottom: 1.25rem;">
            <a href="explore.php" class="btn btn-secondary btn-sm" style="margin-bottom: 1rem;">
                <i class="fa-solid fa-arrow-left"></i> Back to Explore
            </a>
            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                <span class="category-pill">
                    <i class="fa-solid <?= e($place['category_icon']) ?>"></i> <?= e($place['category_name']) ?>
                </span>
                <?= render_verification_badge($place['verification_status']) ?>
            </div>
        </div>

        <h1 style="font-size: clamp(2rem, 5vw, 3.5rem); margin-bottom: 0.75rem; text-shadow: 0 4px 25px rgba(0,0,0,0.8);">
            <?= e($place['name']) ?>
        </h1>

        <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; font-size: 0.95rem; color: var(--fog-grey);">
            <div>
                <i class="fa-solid fa-location-dot" style="color: var(--accent-red);"></i> 
                <?= e($place['district']) ?>, <?= e($place['division']) ?> Division
            </div>
            <div>
                <i class="fa-solid fa-compass" style="color: var(--accent-red);"></i> 
                <?= number_format($place['latitude'], 4) ?>° N, <?= number_format($place['longitude'], 4) ?>° E
            </div>
            <div>
                <i class="fa-regular fa-eye" style="color: var(--accent-red);"></i> 
                <?= number_format($place['views_count']) ?> Explorations
            </div>
        </div>
    </div>
</section>

<!-- Place Details Body -->
<section class="section-padding">
    <div class="container">
        <div class="place-details-grid">
            <!-- Left Column: Core Archival Records -->
            <div class="details-main">
                <!-- Summary / Overview -->
                <div class="content-box">
                    <h2><i class="fa-solid fa-landmark-dome text-red"></i> The Mystery at a Glance</h2>
                    <p class="prose" style="font-size: 1.15rem; color: #fff; line-height: 1.7;">
                        <?= nl2br(e($place['description'])) ?>
                    </p>
                </div>

                <!-- Verified Historical Background -->
                <div class="content-box">
                    <h2>
                        <i class="fa-solid fa-shield-halved text-red"></i> Verified History
                        <span style="font-size: 0.75rem; margin-left: auto; color: #34d399; font-family: var(--font-sans); font-weight: 600;">
                            DOCUMENTED RECORD
                        </span>
                    </h2>
                    <div class="prose">
                        <?= nl2br(e($place['historical_background'])) ?>
                    </div>
                </div>

                <!-- Local Legends & Oral Traditions -->
                <div class="content-box">
                    <h2>
                        <i class="fa-solid fa-scroll text-red"></i> Local Folklore & Oral Traditions
                        <span style="font-size: 0.75rem; margin-left: auto; color: #fbbf24; font-family: var(--font-sans); font-weight: 600;">
                            CULTURAL MEMORY
                        </span>
                    </h2>
                    <div class="prose">
                        <?= nl2br(e($place['local_legends'])) ?>
                    </div>
                </div>

                <!-- Paranormal Claims & Anecdotes -->
                <?php if (!empty($place['paranormal_claims'])): ?>
                <div class="content-box">
                    <h2>
                        <i class="fa-solid fa-eye-slash text-red"></i> Alleged Paranormal Claims
                        <span style="font-size: 0.75rem; margin-left: auto; color: #ff8598; font-family: var(--font-sans); font-weight: 600;">
                            UNVERIFIED CLAIMS
                        </span>
                    </h2>
                    <div class="prose">
                        <blockquote style="border-left: 3px solid var(--accent-red); padding-left: 1.25rem; font-style: italic; color: var(--fog-grey); margin-bottom: 1rem;">
                            Notice: Paranormal accounts are recorded here as oral cultural folklore and witness statements. They are not presented as empirically proven scientific facts.
                        </blockquote>
                        <?= nl2br(e($place['paranormal_claims'])) ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Why The Place Remains Mysterious -->
                <?php if (!empty($place['why_mysterious'])): ?>
                <div class="content-box">
                    <h2><i class="fa-solid fa-circle-question text-red"></i> Why This Sanctuary Endures</h2>
                    <div class="prose">
                        <?= nl2br(e($place['why_mysterious'])) ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Gallery Section -->
                <?php if (!empty($galleryImages)): ?>
                <div class="content-box">
                    <h2><i class="fa-solid fa-camera-retro text-red"></i> Field Imagery & Archives</h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1rem; margin-top: 1rem;">
                        <?php foreach ($galleryImages as $img): ?>
                            <div style="border-radius: 8px; overflow: hidden; border: 1px solid var(--border-subtle); background: var(--bg-secondary);">
                                <img src="<?= e($img['image_url']) ?>" alt="<?= e($img['caption'] ?: $place['name']) ?>" style="width: 100%; height: 180px; object-fit: cover;">
                                <?php if (!empty($img['caption'])): ?>
                                    <div style="padding: 0.65rem; font-size: 0.75rem; color: var(--text-muted); line-height: 1.4;">
                                        <?= e($img['caption']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- References & Archival Sources -->
                <?php if (!empty($place['sources'])): ?>
                <div class="content-box" style="background: rgba(17, 19, 26, 0.7);">
                    <h3 style="font-size: 1.1rem;"><i class="fa-solid fa-book-bookmark text-red"></i> References & Archival Citations</h3>
                    <div style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.6; font-family: monospace;">
                        <?= nl2br(e($place['sources'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Sidebar, Mini-map, Metadata & Actions -->
            <aside class="details-sidebar">
                <div class="sidebar-sticky">
                    <!-- Quick Actions Card -->
                    <div class="content-box" style="margin-bottom: 1.5rem; text-align: center;">
                        <button class="btn btn-primary bookmark-btn" 
                                style="width: 100%; margin-bottom: 0.75rem;"
                                data-id="<?= e($place['id']) ?>" 
                                data-slug="<?= e($place['slug']) ?>"
                                data-name="<?= e($place['name']) ?>"
                                data-district="<?= e($place['district']) ?>"
                                data-division="<?= e($place['division']) ?>"
                                data-image="<?= e($place['cover_image']) ?>"
                                data-category="<?= e($place['category_name']) ?>">
                            <i class="fa-regular fa-bookmark"></i> Bookmark Location
                        </button>

                        <button onclick="navigator.clipboard.writeText(window.location.href); alert('Dossier link copied to clipboard!');" class="btn btn-secondary btn-sm" style="width: 100%;">
                            <i class="fa-solid fa-share-nodes"></i> Share Dossier
                        </button>
                    </div>

                    <!-- Mini Leaflet Map -->
                    <div class="content-box" style="margin-bottom: 1.5rem; padding: 1.5rem;">
                        <h3 style="font-size: 1.1rem; margin-bottom: 1rem;"><i class="fa-solid fa-map-pin text-red"></i> Geographic Coordinates</h3>
                        <div id="place-mini-map" 
                             data-lat="<?= (float)$place['latitude'] ?>" 
                             data-lng="<?= (float)$place['longitude'] ?>" 
                             data-name="<?= e($place['name']) ?>"></div>
                        
                        <div style="margin-top: 1rem; font-size: 0.82rem; color: var(--text-muted); line-height: 1.5;">
                            <div><strong>District:</strong> <?= e($place['district']) ?></div>
                            <div><strong>Division:</strong> <?= e($place['division']) ?></div>
                            <?php if (!empty($place['address'])): ?>
                                <div><strong>Address:</strong> <?= e($place['address']) ?></div>
                            <?php endif; ?>
                            <div style="margin-top: 0.5rem;">
                                <a href="https://www.google.com/maps?q=<?= (float)$place['latitude'] ?>,<?= (float)$place['longitude'] ?>" target="_blank" rel="noopener" style="color: var(--accent-red); font-weight: 600;">
                                    Open in Google Maps <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Related Sanctuaries -->
                    <?php if (!empty($relatedPlaces)): ?>
                    <div class="content-box">
                        <h3 style="font-size: 1.1rem; margin-bottom: 1rem;"><i class="fa-solid fa-compass text-red"></i> Related Sanctuaries</h3>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php foreach ($relatedPlaces as $rel): ?>
                                <a href="place.php?slug=<?= e($rel['slug']) ?>" style="display: flex; gap: 0.85rem; align-items: center; text-decoration: none;">
                                    <img src="<?= e($rel['cover_image']) ?>" alt="<?= e($rel['name']) ?>" style="width: 60px; height: 60px; border-radius: 6px; object-fit: cover; flex-shrink: 0;">
                                    <div>
                                        <div style="font-size: 0.9rem; font-weight: 600; color: #fff; font-family: var(--font-serif);"><?= e($rel['name']) ?></div>
                                        <div style="font-size: 0.72rem; color: var(--accent-red);"><?= e($rel['district']) ?>, <?= e($rel['division']) ?></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

