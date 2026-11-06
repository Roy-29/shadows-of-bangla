<?php
/**
 * SHADOWS OF BANGLA — Manage Places
 */

define('APP_INIT', true);
$adminPage = 'places';
$adminTitle = 'Manage Places';
$adminSubtitle = 'View, filter, edit, or purge documented historical and folkloric locations.';

require_once __DIR__ . '/header.php';

// Handle Place Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_place') {
    $csrf = $_POST['csrf_token'] ?? '';
    $deleteId = (int)($_POST['place_id'] ?? 0);

    if (!verify_csrf_token($csrf)) {
        $_SESSION['flash_error'] = 'Security verification failed. Please try again.';
    } elseif ($deleteId > 0 && $pdo) {
        try {
            $delStmt = $pdo->prepare("DELETE FROM places WHERE id = :id");
            $delStmt->execute([':id' => $deleteId]);
            $_SESSION['flash_success'] = 'Sanctuary successfully purged from the archives.';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Error removing location: ' . $e->getMessage();
        }
    }
    header("Location: places.php");
    exit;
}

$search = trim($_GET['search'] ?? '');
$division = trim($_GET['division'] ?? '');
$places = [];

if ($pdo) {
    try {
        $where = [];
        $params = [];

        if (!empty($search)) {
            $where[] = "(p.name LIKE :s1 OR p.district LIKE :s2 OR p.summary LIKE :s3)";
            $params[':s1'] = "%{$search}%";
            $params[':s2'] = "%{$search}%";
            $params[':s3'] = "%{$search}%";
        }
        if (!empty($division)) {
            $where[] = "p.division = :div";
            $params[':div'] = $division;
        }

        $whereClause = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';
        $sql = "SELECT p.*, c.name as category_name, c.icon as category_icon 
                FROM places p 
                JOIN categories c ON p.category_id = c.id 
                {$whereClause} 
                ORDER BY p.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $places = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Places admin query error: " . $e->getMessage());
    }
}
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h2>Documented Locations (<?= count($places) ?>)</h2>
        <a href="add-place.php" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Add New Location
        </a>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="places.php" style="display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
        <input type="text" name="search" class="admin-input" placeholder="Search by name, district..." value="<?= e($search) ?>" style="max-width: 280px;">
        <select name="division" class="admin-select" onchange="this.form.submit()" style="max-width: 200px;">
            <option value="">All Divisions</option>
            <?php foreach (get_bangladesh_divisions() as $div): ?>
                <option value="<?= e($div) ?>" <?= $division === $div ? 'selected' : '' ?>><?= e($div) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
        <?php if (!empty($search) || !empty($division)): ?>
            <a href="places.php" class="btn btn-secondary btn-sm">Reset</a>
        <?php endif; ?>
    </form>

    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 60px;">Cover</th>
                    <th>Location Name</th>
                    <th>Division & District</th>
                    <th>Category</th>
                    <th>Verification</th>
                    <th>Coordinates</th>
                    <th>Views</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($places)): ?>
                    <?php foreach ($places as $p): ?>
                        <tr>
                            <td>
                                <img src="../<?= e($p['cover_image']) ?>" alt="" style="width: 48px; height: 48px; border-radius: 4px; object-fit: cover;">
                            </td>
                            <td>
                                <strong><a href="../place.php?slug=<?= e($p['slug']) ?>" target="_blank" style="color: #fff;"><?= e($p['name']) ?></a></strong>
                                <div style="font-size: 0.75rem; color: var(--admin-muted); font-family: monospace;"><?= e($p['slug']) ?></div>
                            </td>
                            <td><?= e($p['district']) ?>, <?= e($p['division']) ?></td>
                            <td><?= e($p['category_name']) ?></td>
                            <td>
                                <span class="pill-status pill-<?= $p['verification_status'] === 'VERIFIED HISTORY' ? 'approved' : 'pending' ?>">
                                    <?= e($p['verification_status']) ?>
                                </span>
                            </td>
                            <td style="font-size: 0.78rem; font-family: monospace;">
                                <?= number_format($p['latitude'], 3) ?>, <?= number_format($p['longitude'], 3) ?>
                            </td>
                            <td><?= number_format($p['views_count']) ?></td>
                            <td style="text-align: right;">
                                <div class="action-btns" style="justify-content: flex-end;">
                                    <a href="edit-place.php?id=<?= $p['id'] ?>" class="btn-icon" title="Edit Place">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="../place.php?slug=<?= e($p['slug']) ?>" target="_blank" class="btn-icon" title="View Public Page">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                    <form method="POST" action="places.php" onsubmit="return confirmDeletion('<?= e(addslashes($p['name'])) ?>', 'place');" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_place">
                                        <input type="hidden" name="place_id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn-icon btn-icon-delete" title="Purge Record">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--admin-muted);">
                            No documented places matched your search query.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

