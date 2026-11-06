<?php
/**
 * SHADOWS OF BANGLA — Admin Dashboard Overview
 */

define('APP_INIT', true);
$adminPage = 'dashboard';
$adminTitle = 'Archivist Control Room';
$adminSubtitle = 'Overview of documented sanctuaries, manuscript chronicles, and pending submissions.';

require_once __DIR__ . '/header.php';

$stats = [
    'places' => 0,
    'stories' => 0,
    'submissions_pending' => 0,
    'total_views' => 0
];
$recentPlaces = [];
$recentStories = [];
$pendingSubmissions = [];

if ($pdo) {
    try {
        $stats['places'] = (int)$pdo->query("SELECT COUNT(*) FROM places")->fetchColumn();
        $stats['stories'] = (int)$pdo->query("SELECT COUNT(*) FROM stories")->fetchColumn();
        $stats['submissions_pending'] = (int)$pdo->query("SELECT COUNT(*) FROM story_submissions WHERE status = 'pending'")->fetchColumn();
        $placeViews = (int)$pdo->query("SELECT COALESCE(SUM(views_count), 0) FROM places")->fetchColumn();
        $storyViews = (int)$pdo->query("SELECT COALESCE(SUM(views_count), 0) FROM stories")->fetchColumn();
        $stats['total_views'] = $placeViews + $storyViews;

        // Recent places
        $stmtP = $pdo->query("SELECT p.*, c.name as category_name FROM places p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT 5");
        $recentPlaces = $stmtP->fetchAll();

        // Recent stories
        $stmtS = $pdo->query("SELECT s.*, c.name as category_name FROM stories s JOIN categories c ON s.category_id = c.id ORDER BY s.id DESC LIMIT 5");
        $recentStories = $stmtS->fetchAll();

        // Pending submissions
        $stmtSub = $pdo->query("SELECT sub.*, c.name as category_name FROM story_submissions sub JOIN categories c ON sub.category_id = c.id WHERE sub.status = 'pending' ORDER BY sub.created_at DESC LIMIT 5");
        $pendingSubmissions = $stmtSub->fetchAll();
    } catch (PDOException $e) {
        error_log("Dashboard query error: " . $e->getMessage());
    }
}
?>

<!-- Stat Cards -->
<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <div class="stat-icon-wrap">
            <i class="fa-solid fa-monument"></i>
        </div>
        <div class="stat-details">
            <h3><?= number_format($stats['places']) ?></h3>
            <span>Documented Places</span>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="stat-icon-wrap" style="color: #60a5fa; background: rgba(96, 165, 250, 0.15);">
            <i class="fa-solid fa-book-skull"></i>
        </div>
        <div class="stat-details">
            <h3><?= number_format($stats['stories']) ?></h3>
            <span>Manuscript Chronicles</span>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="stat-icon-wrap" style="color: #facc15; background: rgba(250, 204, 21, 0.15);">
            <i class="fa-solid fa-inbox"></i>
        </div>
        <div class="stat-details">
            <h3><?= number_format($stats['submissions_pending']) ?></h3>
            <span>Pending Submissions</span>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="stat-icon-wrap" style="color: #34d399; background: rgba(52, 211, 153, 0.15);">
            <i class="fa-solid fa-eye"></i>
        </div>
        <div class="stat-details">
            <h3><?= number_format($stats['total_views']) ?></h3>
            <span>Total Expeditions</span>
        </div>
    </div>
</div>

<!-- Pending Submissions Action Queue -->
<?php if (!empty($pendingSubmissions)): ?>
<div class="admin-card" style="border-color: rgba(250, 204, 21, 0.4);">
    <div class="admin-card-header">
        <h2 style="color: #facc15;"><i class="fa-solid fa-bell"></i> Action Required: Submissions Awaiting Review</h2>
        <a href="submissions.php" class="btn btn-secondary btn-sm">Review All Inbound</a>
    </div>

    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Submitted</th>
                    <th>Contributor</th>
                    <th>Story Title</th>
                    <th>Location / District</th>
                    <th>Category</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingSubmissions as $sub): ?>
                    <tr>
                        <td><?= format_display_date($sub['created_at']) ?></td>
                        <td><strong><?= e($sub['submitter_name']) ?></strong></td>
                        <td><?= e($sub['story_title']) ?></td>
                        <td><?= e($sub['location_name']) ?> (<?= e($sub['district']) ?>)</td>
                        <td><?= e($sub['category_name']) ?></td>
                        <td style="text-align: right;">
                            <a href="submissions.php?id=<?= $sub['id'] ?>" class="btn btn-primary btn-sm">
                                Review Dossier <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Places & Stories Dual Grid -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <!-- Recent Places -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Recent Locations</h2>
            <a href="places.php" style="color: var(--admin-accent); font-size: 0.82rem; text-decoration: underline;">View All</a>
        </div>

        <div class="admin-table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Location</th>
                        <th>Division</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentPlaces as $rp): ?>
                        <tr>
                            <td>
                                <strong><a href="../place.php?slug=<?= e($rp['slug']) ?>" target="_blank" style="color: #fff;"><?= e($rp['name']) ?></a></strong>
                            </td>
                            <td><?= e($rp['division']) ?></td>
                            <td>
                                <span class="pill-status pill-<?= $rp['verification_status'] === 'VERIFIED HISTORY' ? 'approved' : 'pending' ?>">
                                    <?= e($rp['verification_status']) ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div class="action-btns" style="justify-content: flex-end;">
                                    <a href="edit-place.php?id=<?= $rp['id'] ?>" class="btn-icon" title="Edit Place">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="../place.php?slug=<?= e($rp['slug']) ?>" target="_blank" class="btn-icon" title="Public View">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Stories -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Recent Stories</h2>
            <a href="stories.php" style="color: var(--admin-accent); font-size: 0.82rem; text-decoration: underline;">View All</a>
        </div>

        <div class="admin-table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentStories as $rs): ?>
                        <tr>
                            <td>
                                <strong><a href="../story.php?slug=<?= e($rs['slug']) ?>" target="_blank" style="color: #fff;"><?= e(truncate_words($rs['title'], 38)) ?></a></strong>
                            </td>
                            <td><?= e($rs['category_name']) ?></td>
                            <td>
                                <span class="pill-status pill-<?= $rs['status'] ?>">
                                    <?= e($rs['status']) ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div class="action-btns" style="justify-content: flex-end;">
                                    <a href="edit-story.php?id=<?= $rs['id'] ?>" class="btn-icon" title="Edit Story">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="../story.php?slug=<?= e($rs['slug']) ?>" target="_blank" class="btn-icon" title="Public View">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

