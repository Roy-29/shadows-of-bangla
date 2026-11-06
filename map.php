<?php
/**
 * SHADOWS OF BANGLA — Interactive Bangladesh Map
 * Full-screen Leaflet Explorer with Real Coordinates
 */

define('APP_INIT', true);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Interactive Map of Bangladesh';
$activePage = 'map';

$pdo = get_db_connection();

$places = [];
$categories = get_categories($pdo);
$divisions = get_bangladesh_divisions();

if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT p.id, p.name, p.slug, p.summary, p.division, p.district, p.latitude, p.longitude, p.cover_image, p.verification_status, c.name as category_name, c.slug as category_slug, c.icon as category_icon 
                             FROM places p 
                             JOIN categories c ON p.category_id = c.id 
                             ORDER BY p.name ASC");
        $places = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Map places query error: ' . $e->getMessage());
    }
}
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<!-- JSON Payload for Leaflet Script -->
<script id="map-places-data" type="application/json">
    <?= json_encode($places, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>
</script>

<!-- Map Page Header -->
<section style="padding: 7rem 0 2rem; background: linear-gradient(180deg, #050608 0%, #11131a 100%); border-bottom: 1px solid var(--border-subtle);">
    <div class="container text-center">
        <span class="section-subtitle">Cartographic Archive</span>
        <h1 class="section-title" style="font-size: 2.5rem; margin-bottom: 0.5rem;">Interactive Map of Bangladesh</h1>
        <p class="section-description">
            Explore verified historical and folklore sites charted across the eight divisions of Bengal. Click any marker for coordinates, field imagery, and historical records.
        </p>
    </div>
</section>

<!-- Full Interactive Map Experience -->
<section style="padding: 2rem 0 4rem;">
    <div class="container">
        <div class="map-container-box">
            <!-- Toolbar -->
            <div class="map-toolbar">
                <div class="map-filters">
                    <select id="map-filter-division" class="map-select">
                        <option value="">All 8 Divisions</option>
                        <?php foreach ($divisions as $div): ?>
                            <option value="<?= e($div) ?>"><?= e($div) ?> Division</option>
                        <?php endforeach; ?>
                    </select>

                    <select id="map-filter-category" class="map-select">
                        <option value="">All Mystery Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text" id="map-search-input" class="map-select" placeholder="Search by name or district..." style="min-width: 220px;">
                </div>

                <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                    <span id="map-markers-count" style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600;">
                        <?= count($places) ?> locations charted
                    </span>
                    <button id="map-reset-btn" class="btn btn-secondary btn-sm" title="Center on Bangladesh">
                        <i class="fa-solid fa-arrows-rotate"></i> Reset View
                    </button>
                    <a href="explore.php" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-list"></i> Grid View
                    </a>
                </div>
            </div>

            <!-- Leaflet Map Canvas -->
            <div id="bangladesh-map" style="height: 680px;"></div>
        </div>

        <!-- Location Directory Quick Tray -->
        <div style="margin-top: 3rem;">
            <h3 style="font-size: 1.25rem; font-family: var(--font-serif); margin-bottom: 1.25rem; color: #fff; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-compass" style="color: var(--accent-red);"></i> Charted Coordinates Index
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;">
                <?php foreach ($places as $place): ?>
                    <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 8px; padding: 1rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                        <div>
                            <h4 style="font-size: 0.95rem; font-family: var(--font-serif); margin-bottom: 0.2rem;">
                                <a href="place.php?slug=<?= e($place['slug']) ?>" style="color: #fff;"><?= e($place['name']) ?></a>
                            </h4>
                            <span style="font-size: 0.75rem; color: var(--accent-red); font-weight: 600;">
                                <?= e($place['district']) ?>, <?= e($place['division']) ?>
                            </span>
                            <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.2rem; font-family: monospace;">
                                <?= number_format($place['latitude'], 4) ?>° N, <?= number_format($place['longitude'], 4) ?>° E
                            </div>
                        </div>
                        <a href="place.php?slug=<?= e($place['slug']) ?>" class="btn-icon" title="View Dossier">
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

