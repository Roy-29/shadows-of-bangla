<?php
/**
 * SHADOWS OF BANGLA — Category Management
 */

define('APP_INIT', true);
$adminPage = 'categories';
$adminTitle = 'Categories & Classifications';
$adminSubtitle = 'Manage folklore classifications, icons, and archival taxonomies.';

require_once __DIR__ . '/header.php';

$errors = [];

// Handle Adding a New Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_category') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $errors[] = 'Security token invalid.';
    } else {
        $name = sanitize_input($_POST['name'] ?? '');
        $slug = slugify($_POST['slug'] ?? $name);
        $icon = sanitize_input($_POST['icon'] ?? 'fa-ghost');
        $desc = sanitize_input($_POST['description'] ?? '');

        if (empty($name)) $errors[] = 'Category name is required.';

        if (empty($errors) && $pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name, slug, icon, description) VALUES (:name, :slug, :icon, :desc)");
                $stmt->execute([':name' => $name, ':slug' => $slug, ':icon' => $icon, ':desc' => $desc]);
                $_SESSION['flash_success'] = "Category [{$name}] created!";
                header("Location: categories.php");
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Handle Category Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_category') {
    $csrf = $_POST['csrf_token'] ?? '';
    $catId = (int)($_POST['category_id'] ?? 0);

    if (!verify_csrf_token($csrf)) {
        $_SESSION['flash_error'] = 'Security verification failed.';
    } elseif ($catId > 0 && $pdo) {
        try {
            // Check if places use this category
            $chk = $pdo->prepare("SELECT COUNT(*) FROM places WHERE category_id = :id");
            $chk->execute([':id' => $catId]);
            $count = (int)$chk->fetchColumn();

            if ($count > 0) {
                $_SESSION['flash_error'] = "Cannot purge category: {$count} location(s) are actively assigned to it.";
            } else {
                $del = $pdo->prepare("DELETE FROM categories WHERE id = :id");
                $del->execute([':id' => $catId]);
                $_SESSION['flash_success'] = 'Category deleted.';
            }
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Error deleting category: ' . $e->getMessage();
        }
    }
    header("Location: categories.php");
    exit;
}

$categories = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT c.*, 
            (SELECT COUNT(*) FROM places WHERE category_id = c.id) as places_count,
            (SELECT COUNT(*) FROM stories WHERE category_id = c.id) as stories_count
            FROM categories c ORDER BY c.id ASC");
        $categories = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Categories query error: " . $e->getMessage());
    }
}
?>

<div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 2rem;">
    <!-- Categories Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Current Classifications (<?= count($categories) ?>)</h2>
        </div>

        <div class="admin-table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 45px;">Icon</th>
                        <th>Category Name</th>
                        <th>Slug</th>
                        <th>Places</th>
                        <th>Stories</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>
                                <i class="fa-solid <?= e($cat['icon']) ?>" style="font-size: 1.1rem; color: var(--admin-accent);"></i>
                            </td>
                            <td>
                                <strong><?= e($cat['name']) ?></strong>
                                <div style="font-size: 0.75rem; color: var(--admin-muted);"><?= e($cat['description']) ?></div>
                            </td>
                            <td style="font-family: monospace; font-size: 0.8rem;"><?= e($cat['slug']) ?></td>
                            <td><?= $cat['places_count'] ?></td>
                            <td><?= $cat['stories_count'] ?></td>
                            <td style="text-align: right;">
                                <?php if ($cat['places_count'] == 0 && $cat['stories_count'] == 0): ?>
                                    <form method="POST" action="categories.php" onsubmit="return confirm('Delete this unused category?');" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_category">
                                        <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                        <button type="submit" class="btn-icon btn-icon-delete" title="Delete Category">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size: 0.7rem; color: var(--admin-muted);">Protected</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Category Form -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Add New Classification</h2>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert-box alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div>
                    <?php foreach ($errors as $err): ?>
                        <div><?= e($err) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="categories.php">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add_category">

            <div class="admin-form-group">
                <label for="name">Category Name *</label>
                <input type="text" id="name" name="name" class="admin-input" required placeholder="e.g. Submerged Relics">
            </div>

            <div class="admin-form-group">
                <label for="slug">URL Slug (Optional)</label>
                <input type="text" id="slug" name="slug" class="admin-input" placeholder="e.g. submerged-relics">
            </div>

            <div class="admin-form-group">
                <label for="icon">Font Awesome Icon Class</label>
                <input type="text" id="icon" name="icon" class="admin-input" value="fa-ghost" placeholder="e.g. fa-water, fa-dungeon">
            </div>

            <div class="admin-form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="admin-textarea" style="min-height: 80px;" placeholder="Brief summary of what this category encapsulates..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fa-solid fa-plus"></i> Create Classification
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

