<?php
/**
 * SHADOWS OF BANGLA — Explore Places
 * Search, Multi-Filter, Sorting & Pagination
 */

define('APP_INIT', true);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Explore Documented Places';
$activePage = 'explore';

$pdo = get_db_connection();

// Filter inputs
$search = trim($_GET['search'] ?? '');
$division = trim($_GET['division'] ?? '');
$district = trim($_GET['district'] ?? '');
$categorySlug = trim($_GET['category'] ?? '');
$sort = trim($_GET['sort'] ?? 'newest');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;

$places = [];
$totalPlaces = 0;
$categories = get_categories($pdo);
$divisions = get_bangladesh_divisions();
$districtsByDiv = get_districts_by_division();

if ($pdo) {
    try {
        // Base query
        $where = [];
        $params = [];

        if (!empty($search)) {
            $where[] = "(p.name LIKE :s1 OR p.summary LIKE :s2 OR p.district LIKE :s3 OR p.historical_background LIKE :s4)";
            $params[':s1'] = "%{$search}%";
            $params[':s2'] = "%{$search}%";
            $params[':s3'] = "%{$search}%";
            $params[':s4'] = "%{$search}%";
        }

        if (!empty($division)) {
            $where[] = "p.division = :division";
            $params[':division'] = $division;
        }

        if (!empty($district)) {
            $where[] = "p.district = :district";
            $params[':district'] = $district;
        }

        if (!empty($categorySlug)) {
            $where[] = "c.slug = :categorySlug";
            $params[':categorySlug'] = $categorySlug;
        }

        $whereClause = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        // Total count
        $countSql = "SELECT COUNT(*) FROM places p JOIN categories c ON p.category_id = c.id {$whereClause}";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalPlaces = (int)$countStmt->fetchColumn();

        // Sorting
        $orderBy = 'p.created_at DESC';
        if ($sort === 'alpha') {
            $orderBy = 'p.name ASC';
        } elseif ($sort === 'views') {
            $orderBy = 'p.views_count DESC';
        } elseif ($sort === 'random') {
            $orderBy = 'RAND()';
        }

        // Fetch paginated results
        $dataSql = "SELECT p.*, c.name as category_name, c.slug as category_slug, c.icon as category_icon 
                    FROM places p 
                    JOIN categories c ON p.category_id = c.id 
                    {$whereClause} 
                    ORDER BY {$orderBy} 
                    LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($dataSql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $places = $stmt->fetchAll();

    } catch (PDOException $e) {
        error_log('Explore Query Error: ' . $e->getMessage());
    }
}

$totalPages = max(1, (int)ceil($totalPlaces / $perPage));

require_once __DIR__ . '/includes/header.php';
?>

<!-- Explore Header Banner -->
<section style="padding: 7.5rem 0 3.5rem; background: linear-gradient(180deg, #050608 0%, #11131a 100%); border-bottom: 1px solid var(--border-subtle); position: relative;">
    <div class="container text-center">
        <span class="section-subtitle">Discovery Compendium</span>
        <h1 class="section-title" style="font-size: 2.75rem; margin-bottom: 0.75rem;">Documented Sanctuaries</h1>
        <p class="section-description">
            Explore 14 verified locations across Bangladesh. Filter by division, classification, or archaeological status.
        </p>
    </div>
</section>

<!-- Filter Toolbar -->
<section style="background: var(--bg-card); border-bottom: 1px solid var(--border-subtle); padding: 1.5rem 0; position: sticky; top: 70px; z-index: 100; backdrop-filter: blur(10px);">
    <div class="container">
        <form method="GET" action="explore.php" id="explore-filter-form">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) 1fr auto; gap: 0.75rem; align-items: center;">
                <!-- Search Input -->
                <input type="text" name="search" class="form-control" placeholder="Search by place or town..." value="<?= e($search) ?>">

                <!-- Division Select -->
                <select name="division" class="map-select" onchange="this.form.submit()">
                    <option value="">All Divisions</option>
                    <?php foreach ($divisions as $div): ?>
                        <option value="<?= e($div) ?>" <?= $division === $div ? 'selected' : '' ?>><?= e($div) ?></option>
                    <?php endforeach; ?>
                </select>

                <!-- Category Select -->
                <select name="category" class="map-select" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= e($cat['slug']) ?>" <?= $categorySlug === $cat['slug'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <!-- Sorting Select -->
                <select name="sort" class="map-select" onchange="this.form.submit()">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Sort: Newest First</option>
                    <option value="alpha" <?= $sort === 'alpha' ? 'selected' : '' ?>>Sort: Alphabetical (A-Z)</option>
                    <option value="views" <?= $sort === 'views' ? 'selected' : '' ?>>Sort: Most Explored</option>
                    <option value="random" <?= $sort === 'random' ? 'selected' : '' ?>>Sort: Random Order</option>
                </select>

                <!-- Submit / Reset Actions -->
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-filter"></i> Apply
                    </button>
                    <?php if (!empty($search) || !empty($division) || !empty($categorySlug) || $sort !== 'newest'): ?>
                        <a href="explore.php" class="btn btn-secondary btn-sm" title="Clear all filters">
                            <i class="fa-solid fa-xmark"></i> Reset
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Places Grid Content -->
<section class="section-padding">
    <div class="container">
        <!-- Results Counter & Action Bar -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
            <div style="font-size: 0.95rem; color: var(--text-secondary);">
                Showing <strong><?= count($places) ?></strong> of <strong><?= $totalPlaces ?></strong> documented mysterious sites
                <?php if (!empty($division)): ?> in <strong><?= e($division) ?> Division</strong><?php endif; ?>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <a href="map.php" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-map-location-dot"></i> View on Interactive Map
                </a>
            </div>
        </div>

        <?php if (!empty($places)): ?>
            <div class="cards-grid">
                <?php foreach ($places as $place): ?>
                    <article class="place-card">
                        <div class="card-media">
                            <img src="<?= e($place['cover_image']) ?>" alt="<?= e($place['name']) ?>" class="card-image" loading="lazy">
                            <div class="card-media-overlay"></div>
                            <div class="card-badges">
                                <span class="category-pill">
                                    <i class="fa-solid <?= e($place['category_icon'] ?? 'fa-ghost') ?>"></i> <?= e($place['category_name']) ?>
                                </span>
                                <button class="bookmark-btn" 
                                        data-id="<?= e($place['id']) ?>" 
                                        data-slug="<?= e($place['slug']) ?>"
                                        data-name="<?= e($place['name']) ?>"
                                        data-district="<?= e($place['district']) ?>"
                                        data-division="<?= e($place['division']) ?>"
                                        data-image="<?= e($place['cover_image']) ?>"
                                        data-category="<?= e($place['category_name']) ?>"
                                        title="Bookmark place">
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
                                    <?= render_verification_badge($place['verification_status']) ?>
                                </div>
                                <a href="place.php?slug=<?= e($place['slug']) ?>" class="btn btn-outline-red btn-sm">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 4rem;">
                    <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-secondary btn-sm">
                            <i class="fa-solid fa-arrow-left"></i> Previous
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                           class="btn <?= $i === $page ? 'btn-primary' : 'btn-secondary' ?> btn-sm" style="min-width: 40px;">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-secondary btn-sm">
                            Next <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div style="text-align: center; padding: 5rem 1rem; background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 12px;">
                <i class="fa-solid fa-ghost" style="font-size: 3.5rem; color: var(--accent-red); margin-bottom: 1.5rem; opacity: 0.6;"></i>
                <h3 style="font-size: 1.6rem; font-family: var(--font-serif); margin-bottom: 0.75rem;">No Sanctuaries Emerged from the Fog</h3>
                <p style="color: var(--text-secondary); max-width: 500px; margin: 0 auto 1.75rem;">
                    We could not locate any recorded mysterious locations matching your specified parameters.
                </p>
                <a href="explore.php" class="btn btn-primary">
                    <i class="fa-solid fa-rotate-left"></i> Reset All Filters
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

