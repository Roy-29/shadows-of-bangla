<?php
/**
 * SHADOWS OF BANGLA — Manage Stories
 */

define('APP_INIT', true);
$adminPage = 'stories';
$adminTitle = 'Manage Chronicles';
$adminSubtitle = 'Publish, edit, draft, or delete editorial manuscripts and folklore chronicles.';

require_once __DIR__ . '/header.php';

// Handle Actions (Toggle status or Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    $action = $_POST['action'] ?? '';
    $storyId = (int)($_POST['story_id'] ?? 0);

    if (!verify_csrf_token($csrf)) {
        $_SESSION['flash_error'] = 'Security verification failed.';
    } elseif ($storyId > 0 && $pdo) {
        if ($action === 'delete_story') {
            try {
                $stmt = $pdo->prepare("DELETE FROM stories WHERE id = :id");
                $stmt->execute([':id' => $storyId]);
                $_SESSION['flash_success'] = 'Chronicle successfully purged from the archive.';
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = 'Error deleting story: ' . $e->getMessage();
            }
        } elseif ($action === 'toggle_status') {
            try {
                $stmt = $pdo->prepare("UPDATE stories SET status = IF(status = 'published', 'draft', 'published') WHERE id = :id");
                $stmt->execute([':id' => $storyId]);
                $_SESSION['flash_success'] = 'Publication status toggled.';
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = 'Error updating status: ' . $e->getMessage();
            }
        }
    }
    header("Location: stories.php");
    exit;
}

$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$stories = [];

if ($pdo) {
    try {
        $where = [];
        $params = [];

        if (!empty($search)) {
            $where[] = "(s.title LIKE :s1 OR s.author LIKE :s2 OR s.excerpt LIKE :s3)";
            $params[':s1'] = "%{$search}%";
            $params[':s2'] = "%{$search}%";
            $params[':s3'] = "%{$search}%";
        }
        if (!empty($statusFilter)) {
            $where[] = "s.status = :status";
            $params[':status'] = $statusFilter;
        }

        $whereClause = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';
        $sql = "SELECT s.*, c.name as category_name 
                FROM stories s 
                JOIN categories c ON s.category_id = c.id 
                {$whereClause} 
                ORDER BY s.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $stories = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Stories admin query error: " . $e->getMessage());
    }
}
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h2>Manuscripts & Chronicles (<?= count($stories) ?>)</h2>
        <a href="add-story.php" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-feather"></i> Draft New Chronicle
        </a>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="stories.php" style="display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
        <input type="text" name="search" class="admin-input" placeholder="Search by title, author..." value="<?= e($search) ?>" style="max-width: 280px;">
        <select name="status" class="admin-select" onchange="this.form.submit()" style="max-width: 180px;">
            <option value="">All Statuses</option>
            <option value="published" <?= $statusFilter === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
        </select>
        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
        <?php if (!empty($search) || !empty($statusFilter)): ?>
            <a href="stories.php" class="btn btn-secondary btn-sm">Reset</a>
        <?php endif; ?>
    </form>

    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 60px;">Cover</th>
                    <th>Chronicle Title</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Read Time</th>
                    <th>Status</th>
                    <th>Views</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($stories)): ?>
                    <?php foreach ($stories as $st): ?>
                        <tr>
                            <td>
                                <img src="../<?= e($st['cover_image']) ?>" alt="" style="width: 48px; height: 48px; border-radius: 4px; object-fit: cover;">
                            </td>
                            <td>
                                <strong><a href="../story.php?slug=<?= e($st['slug']) ?>" target="_blank" style="color: #fff;"><?= e($st['title']) ?></a></strong>
                                <div style="font-size: 0.75rem; color: var(--admin-muted); font-family: monospace;"><?= e($st['slug']) ?></div>
                            </td>
                            <td><?= e($st['category_name']) ?></td>
                            <td><?= e($st['author']) ?></td>
                            <td><?= e($st['reading_time']) ?> min</td>
                            <td>
                                <form method="POST" action="stories.php" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="story_id" value="<?= $st['id'] ?>">
                                    <button type="submit" class="pill-status pill-<?= $st['status'] ?>" style="border: none; cursor: pointer;" title="Click to toggle published / draft">
                                        <?= e($st['status']) ?>
                                    </button>
                                </form>
                            </td>
                            <td><?= number_format($st['views_count']) ?></td>
                            <td style="text-align: right;">
                                <div class="action-btns" style="justify-content: flex-end;">
                                    <a href="edit-story.php?id=<?= $st['id'] ?>" class="btn-icon" title="Edit Chronicle">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="../story.php?slug=<?= e($st['slug']) ?>" target="_blank" class="btn-icon" title="View Public Reader">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                    <form method="POST" action="stories.php" onsubmit="return confirmDeletion('<?= e(addslashes($st['title'])) ?>', 'chronicle');" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_story">
                                        <input type="hidden" name="story_id" value="<?= $st['id'] ?>">
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
                            No chronicles found matching your filter criteria.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

