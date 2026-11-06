<?php
/**
 * SHADOWS OF BANGLA — Beyond the Known.
 * Home Portal
 */

define('APP_INIT', true);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Beyond the Known';
$activePage = 'home';

$pdo = get_db_connection();

// Fetch Featured Places
$featuredPlaces = [];
$recentStories = [];
$allPlacesForMap = [];
$categories = [];
$mysteryOfDay = null;
$stats = get_site_statistics($pdo);

if ($pdo) {
    try {
        // Featured places
        $stmt = $pdo->query("SELECT p.*, c.name as category_name, c.slug as category_slug, c.icon as category_icon 
                             FROM places p 
                             JOIN categories c ON p.category_id = c.id 
                             WHERE p.is_featured = 1 
                             ORDER BY p.id ASC LIMIT 6");
        $featuredPlaces = $stmt->fetchAll();

        // If not enough featured, fill up to 6
        if (count($featuredPlaces) < 6) {
            $stmt = $pdo->query("SELECT p.*, c.name as category_name, c.slug as category_slug, c.icon as category_icon 
                                 FROM places p 
                                 JOIN categories c ON p.category_id = c.id 
                                 ORDER BY p.views_count DESC LIMIT 6");
            $featuredPlaces = $stmt->fetchAll();
        }

        // All places for map preview
        $stmtMap = $pdo->query("SELECT p.id, p.name, p.slug, p.summary, p.division, p.district, p.latitude, p.longitude, p.cover_image, c.slug as category_slug 
                                FROM places p 
                                JOIN categories c ON p.category_id = c.id");
        $allPlacesForMap = $stmtMap->fetchAll();

        // Recent published stories
        $stmtStories = $pdo->query("SELECT s.*, c.name as category_name, c.slug as category_slug 
                                    FROM stories s 
                                    JOIN categories c ON s.category_id = c.id 
                                    WHERE s.status = 'published' 
                                    ORDER BY s.created_at DESC LIMIT 3");
        $recentStories = $stmtStories->fetchAll();

        // Categories
        $categories = get_categories($pdo);

        // Mystery of the day
        $mysteryOfDay = get_mystery_of_the_day($pdo);

    } catch (PDOException $e) {
        error_log('Home Query Error: ' . $e->getMessage());
    }
}

// Fallback seed if DB is not yet initialized or empty
if (empty($featuredPlaces)) {
    $featuredPlaces = [
        [
            'id' => 1,
            'name' => 'Panam City (Panam Nagar)',
            'slug' => 'panam-city-sonargaon',
            'summary' => 'A silent 19th-century merchant township of 52 colonnaded brick mansions, abruptly abandoned after historic conflicts.',
            'division' => 'Dhaka',
            'district' => 'Narayanganj',
            'cover_image' => 'assets/images/panam-city.svg',
            'category_name' => 'Abandoned Places',
            'category_slug' => 'abandoned-places',
            'category_icon' => 'fa-house-crack',
            'verification_status' => 'VERIFIED HISTORY',
            'views_count' => 1420
        ],
        [
            'id' => 2,
            'name' => 'Lalbagh Fort & The Unfinished Maze',
            'slug' => 'lalbagh-fort-dhaka',
            'summary' => 'A 17th-century Mughal river fortress shrouding an unfinished tomb and subterranean secret tunnels beneath Old Dhaka.',
            'division' => 'Dhaka',
            'district' => 'Dhaka',
            'cover_image' => 'assets/images/lalbagh-fort.svg',
            'category_name' => 'Haunted Legends',
            'category_slug' => 'haunted-legends',
            'category_icon' => 'fa-ghost',
            'verification_status' => 'VERIFIED HISTORY',
            'views_count' => 2150
        ],
        [
            'id' => 3,
            'name' => 'Mahasthangarh & Parshuram\'s Palace',
            'slug' => 'mahasthangarh-ancient-pundranagara',
            'summary' => 'The 2,500-year-old fortified ruins of Pundranagara, bearing legends of the fish-riding saint and the Well of Life.',
            'division' => 'Rajshahi',
            'district' => 'Bogura',
            'cover_image' => 'assets/images/mahasthangarh.svg',
            'category_name' => 'Historical Mysteries',
            'category_slug' => 'historical-mysteries',
            'category_icon' => 'fa-landmark',
            'verification_status' => 'VERIFIED HISTORY',
            'views_count' => 980
        ]
    ];
    $allPlacesForMap = $featuredPlaces;
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- JSON Payload for Leaflet Map & Random Place Summoner -->
<script id="map-places-data" type="application/json">
    <?= json_encode($allPlacesForMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>
</script>
<script>
    window.SHADOWS_PLACES_CATALOG = <?= json_encode($allPlacesForMap); ?>;
</script>

<!-- Hero Section -->
<section class="hero-section">
    <img src="assets/images/hero-bg.svg" alt="Atmospheric Bangladeshi Mystery" class="hero-backdrop-image">
    <div class="hero-overlay"></div>
    <div class="hero-vignette"></div>

    <div class="container hero-content">
        <div class="hero-badge">
            <i class="fa-solid fa-compass"></i> Documented Lore & Heritage Archive
        </div>
        <h1 class="hero-title">SHADOWS OF BANGLA</h1>
        <p class="hero-tagline">Beyond the Known.</p>
        <p class="hero-quote">
            &ldquo;Every place has a story. Some are better left unexplored.&rdquo;
        </p>

        <div class="hero-cta-group">
            <a href="explore.php" class="btn btn-primary">
                <i class="fa-solid fa-compass"></i> Explore the Unknown
            </a>
            <a href="stories.php" class="btn btn-secondary">
                <i class="fa-solid fa-book-open"></i> Discover the Stories
            </a>
            <button class="btn btn-outline-red btn-summon-random" title="Random place discovery">
                <i class="fa-solid fa-dice-d20"></i> Summon Random Place
            </button>
        </div>

        <div>
            <a href="#featured-places" class="scroll-indicator" aria-label="Scroll down">
                <div class="scroll-mouse">
                    <div class="scroll-wheel"></div>
                </div>
                <span>Descend</span>
            </a>
        </div>
    </div>
</section>

<!-- Random Summoner Strip -->
<section class="discovery-strip">
    <div class="container">
        <div class="summon-box">
            <div class="summon-info">
                <h3>Unravel a Random Enigma</h3>
                <p>Dare to let fate select your next destination across the 8 divisions of Bangladesh.</p>
            </div>
            <div>
                <button class="btn btn-primary btn-summon-random">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Summon the Unknown
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Mystery of the Day Banner -->
<?php if ($mysteryOfDay): ?>
<section class="section-padding" style="padding-bottom: 2rem;">
    <div class="container">
        <div class="mystery-of-day-banner">
            <div class="banner-badge">
                <i class="fa-solid fa-moon"></i> Mystery of the Day &bull; <?= date('F j, Y') ?>
            </div>
            <div class="mystery-grid">
                <div class="mystery-img-wrap">
                    <img src="<?= e($mysteryOfDay['cover_image']) ?>" alt="<?= e($mysteryOfDay['name']) ?>">
                    <div style="position: absolute; top: 1rem; left: 1rem;">
                        <?= render_verification_badge($mysteryOfDay['verification_status']) ?>
                    </div>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--accent-red); font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">
                        <i class="fa-solid fa-location-dot"></i> <?= e($mysteryOfDay['district']) ?>, <?= e($mysteryOfDay['division']) ?> Division
                    </div>
                    <h2 style="font-size: 2rem; margin-bottom: 0.75rem;"><?= e($mysteryOfDay['name']) ?></h2>
                    <p style="color: var(--text-secondary); margin-bottom: 1.5rem; line-height: 1.7;">
                        <?= e($mysteryOfDay['summary']) ?>
                    </p>
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <a href="place.php?slug=<?= e($mysteryOfDay['slug']) ?>" class="btn btn-primary">
                            Enter the Sanctum <i class="fa-solid fa-arrow-right"></i>
                        </a>
                        <button class="bookmark-btn" 
                                data-id="<?= e($mysteryOfDay['id']) ?>" 
                                data-slug="<?= e($mysteryOfDay['slug']) ?>"
                                data-name="<?= e($mysteryOfDay['name']) ?>"
                                data-district="<?= e($mysteryOfDay['district']) ?>"
                                data-division="<?= e($mysteryOfDay['division']) ?>"
                                data-image="<?= e($mysteryOfDay['cover_image']) ?>"
                                data-category="<?= e($mysteryOfDay['category_name']) ?>"
                                title="Bookmark this place">
                            <i class="fa-regular fa-bookmark"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Featured Mysterious Locations Section -->
<section id="featured-places" class="section-padding">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-subtitle">Documented Relics</span>
            <h2 class="section-title">Featured Mysterious Locations</h2>
            <p class="section-description">
                Sites where verified historical architecture intertwines with centuries of oral folklore, forgotten conflicts, and inexplicable legends.
            </p>
        </div>

        <div class="cards-grid">
            <?php foreach ($featuredPlaces as $place): ?>
                <article class="place-card">
                    <div class="card-media">
                        <img src="<?= e($place['cover_image']) ?>" alt="<?= e($place['name']) ?>" class="card-image" loading="lazy">
                        <div class="card-media-overlay"></div>
                        <div class="card-badges">
                            <span class="category-pill">
                                <i class="fa-solid <?= e($place['category_icon'] ?? 'fa-ghost') ?>"></i> <?= e($place['category_name'] ?? 'Mystery') ?>
                            </span>
                            <button class="bookmark-btn" 
                                    data-id="<?= e($place['id']) ?>" 
                                    data-slug="<?= e($place['slug']) ?>"
                                    data-name="<?= e($place['name']) ?>"
                                    data-district="<?= e($place['district']) ?>"
                                    data-division="<?= e($place['division']) ?>"
                                    data-image="<?= e($place['cover_image']) ?>"
                                    data-category="<?= e($place['category_name'] ?? '') ?>"
                                    title="Save to Bookmarks">
                                <i class="fa-regular fa-bookmark"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="card-location">
                            <i class="fa-solid fa-location-dot"></i> <?= e($place['district']) ?>, <?= e($place['division']) ?>
                        </div>
                        <h3 class="card-title">
                            <a href="place.php?slug=<?= e($place['slug']) ?>"><?= e($place['name']) ?></a>
                        </h3>
                        <p class="card-text">
                            <?= e(truncate_words($place['summary'], 130)) ?>
                        </p>
                        <div class="card-footer">
                            <div>
                                <?= render_verification_badge($place['verification_status'] ?? 'LOCAL FOLKLORE') ?>
                            </div>
                            <a href="place.php?slug=<?= e($place['slug']) ?>" class="btn btn-outline-red btn-sm">
                                View Details
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 3.5rem;">
            <a href="explore.php" class="btn btn-secondary">
                View All Documented Places <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Interactive Bangladesh Map Preview Section -->
<section class="section-padding" style="background: var(--bg-secondary); border-top: 1px solid var(--border-subtle); border-bottom: 1px solid var(--border-subtle);">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-subtitle">Cartographic Expedition</span>
            <h2 class="section-title">The Shadows of Bangladesh</h2>
            <p class="section-description">
                Survey verified enigmatic sites across all 8 administrative divisions of Bengal. Click markers to reveal archaeological coordinates and history.
            </p>
        </div>

        <div class="map-container-box">
            <div class="map-toolbar">
                <div class="map-filters">
                    <select id="map-filter-division" class="map-select">
                        <option value="">All 8 Divisions</option>
                        <?php foreach (get_bangladesh_divisions() as $div): ?>
                            <option value="<?= e($div) ?>"><?= e($div) ?> Division</option>
                        <?php endforeach; ?>
                    </select>

                    <select id="map-filter-category" class="map-select">
                        <option value="">All Mystery Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text" id="map-search-input" class="map-select" placeholder="Filter map by keyword...">
                </div>

                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <span id="map-markers-count" style="font-size: 0.8rem; color: var(--text-muted); margin-right: 0.5rem;">
                        <?= count($allPlacesForMap) ?> locations charted
                    </span>
                    <button id="map-reset-btn" class="btn btn-secondary btn-sm" title="Reset View to Bangladesh">
                        <i class="fa-solid fa-arrows-rotate"></i> Reset View
                    </button>
                    <a href="map.php" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-expand"></i> Full Map
                    </a>
                </div>
            </div>

            <!-- Leaflet Container -->
            <div id="bangladesh-map"></div>
        </div>
    </div>
</section>

<!-- Recently Added Stories Section -->
<?php if (!empty($recentStories)): ?>
<section class="section-padding">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-subtitle">Chronicles of the Dark</span>
            <h2 class="section-title">Recently Added Stories</h2>
            <p class="section-description">
                In-depth editorial narratives delving into royal disappearances, abandoned settlements, and the spectral lore of the Bengal delta.
            </p>
        </div>

        <div class="cards-grid">
            <?php foreach ($recentStories as $story): ?>
                <article class="story-card">
                    <div class="card-media">
                        <img src="<?= e($story['cover_image']) ?>" alt="<?= e($story['title']) ?>" class="card-image" loading="lazy">
                        <div class="card-media-overlay"></div>
                        <div class="card-badges">
                            <span class="category-pill"><?= e($story['category_name']) ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="story-meta">
                            <span><i class="fa-regular fa-calendar"></i> <?= format_display_date($story['created_at']) ?></span>
                            <span class="reading-time"><i class="fa-regular fa-clock"></i> <?= e($story['reading_time']) ?> min read</span>
                        </div>
                        <h3 class="card-title">
                            <a href="story.php?slug=<?= e($story['slug']) ?>"><?= e($story['title']) ?></a>
                        </h3>
                        <p class="card-text">
                            <?= e(truncate_words($story['excerpt'], 140)) ?>
                        </p>
                        <div class="card-footer">
                            <span style="font-size: 0.8rem; color: var(--text-muted);">
                                By <?= e($story['author']) ?>
                            </span>
                            <a href="story.php?slug=<?= e($story['slug']) ?>" class="btn btn-outline-red btn-sm">
                                Read Chronicle <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 3.5rem;">
            <a href="stories.php" class="btn btn-secondary">
                View Story Archive <i class="fa-solid fa-book-open"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Categories Section -->
<section class="section-padding" style="background: var(--bg-secondary); border-top: 1px solid var(--border-subtle); border-bottom: 1px solid var(--border-subtle);">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-subtitle">Classifications of the Unknown</span>
            <h2 class="section-title">Explore by Mystery Category</h2>
            <p class="section-description">
                Filter the archives by archaeological mystery, abandoned ruins, oral folklore, or ghostly encounters.
            </p>
        </div>

        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
                <a href="explore.php?category=<?= e($cat['slug']) ?>" class="category-card">
                    <div class="category-icon">
                        <i class="fa-solid <?= e($cat['icon']) ?>"></i>
                    </div>
                    <div class="category-name"><?= e($cat['name']) ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Interactive Mystery Quiz Section -->
<section class="section-padding">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-subtitle">Trial of the Archivist</span>
            <h2 class="section-title">The Enigma Challenge</h2>
            <p class="section-description">
                Test your understanding of Bangladesh’s ancient civilizations, folklore traditions, and documented enigmas.
            </p>
        </div>

        <!-- Rendered via interactions.js -->
        <div id="interactive-quiz-container"></div>
    </div>
</section>

<!-- Statistics Section (Real Database Counts) -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-number" data-target="<?= $stats['total_places'] ?>">0</div>
                <div class="stat-label">Documented Locations</div>
            </div>
            <div class="stat-box">
                <div class="stat-number" data-target="<?= $stats['total_stories'] ?>">0</div>
                <div class="stat-label">Historical Chronicles</div>
            </div>
            <div class="stat-box">
                <div class="stat-number" data-target="<?= $stats['total_divisions'] ?>">0</div>
                <div class="stat-label">Divisions Explored</div>
            </div>
            <div class="stat-box">
                <div class="stat-number" data-target="<?= $stats['total_districts'] ?>">0</div>
                <div class="stat-label">Districts Charted</div>
            </div>
            <div class="stat-box">
                <div class="stat-number" data-target="<?= $stats['total_views'] ?>">0</div>
                <div class="stat-label">Expedition Views</div>
            </div>
        </div>
    </div>
</section>

<!-- Recently Viewed Places Section (Rendered via localStorage in main.js) -->
<section class="section-padding recently-viewed-section" style="padding-top: 3.5rem; padding-bottom: 3.5rem;">
    <div class="container">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; font-family: var(--font-serif); font-size: 1.2rem; color: #fff;">
                <i class="fa-solid fa-clock-rotate-left" style="color: var(--accent-red);"></i> Recently Viewed Sanctuaries
            </div>
            <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Your Journey History</span>
        </div>
        <div id="recently-viewed-tray" style="display: flex; gap: 1rem; overflow-x: auto; padding-bottom: 0.5rem;">
            <!-- Populated via main.js -->
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

