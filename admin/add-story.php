<?php
/**
 * SHADOWS OF BANGLA — Draft New Story
 */

define('APP_INIT', true);
$adminPage = 'add-story';
$adminTitle = 'Draft New Chronicle';
$adminSubtitle = 'Author an editorial investigation into abandoned places or regional myths.';

require_once __DIR__ . '/header.php';

$errors = [];
$categories = get_categories($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $errors[] = 'Security token invalid.';
    } else {
        $title = sanitize_input($_POST['title'] ?? '');
        $slug = slugify($_POST['slug'] ?? $title);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $author = sanitize_input($_POST['author'] ?? 'Shadows Archive');
        $excerpt = sanitize_input($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'draft';
        $coverImage = trim($_POST['cover_image'] ?? 'assets/images/panam-city.svg');
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;

        // Auto-calculate reading time if left 0
        $readingTime = (int)($_POST['reading_time'] ?? 0);
        if ($readingTime <= 0) {
            $readingTime = calculate_reading_time($content);
        }

        // Image upload handling
        if (isset($_FILES['cover_file']) && $_FILES['cover_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['cover_file'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];
            if (in_array($file['type'], $allowedTypes)) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $newFilename = 'story_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destPath = dirname(__DIR__) . '/uploads/' . $newFilename;
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $coverImage = 'uploads/' . $newFilename;
                }
            } else {
                $errors[] = 'Invalid image type uploaded.';
            }
        }

        if (empty($title)) $errors[] = 'Story title is required.';
        if ($categoryId <= 0) $errors[] = 'Category is required.';
        if (empty($excerpt)) $errors[] = 'Excerpt summary is required.';
        if (empty($content)) $errors[] = 'Story content cannot be empty.';

        // Check unique slug
        if (empty($errors) && $pdo) {
            $checkStmt = $pdo->prepare("SELECT id FROM stories WHERE slug = :slug LIMIT 1");
            $checkStmt->execute([':slug' => $slug]);
            if ($checkStmt->fetch()) {
                $slug .= '-' . time();
            }

            try {
                $insStmt = $pdo->prepare("INSERT INTO stories 
                    (title, slug, excerpt, content, category_id, cover_image, author, reading_time, is_featured, status) 
                    VALUES 
                    (:title, :slug, :excerpt, :content, :catId, :cover, :author, :readTime, :feat, :status)");
                
                $insStmt->execute([
                    ':title' => $title,
                    ':slug' => $slug,
                    ':excerpt' => $excerpt,
                    ':content' => $content,
                    ':catId' => $categoryId,
                    ':cover' => $coverImage,
                    ':author' => $author,
                    ':readTime' => $readingTime,
                    ':feat' => $isFeatured,
                    ':status' => $status
                ]);

                $_SESSION['flash_success'] = "Chronicle [{$title}] successfully archived!";
                header("Location: stories.php");
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h2>Compose New Editorial Manuscript</h2>
        <a href="stories.php" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Back to Stories
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert-box alert-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>
                <strong>Drafting halted:</strong>
                <ul style="margin-left: 1.25rem;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST" action="add-story.php" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="admin-form-grid">
            <div class="admin-form-group col-span-2">
                <label for="title">Chronicle Title *</label>
                <input type="text" id="title" name="title" class="admin-input" required placeholder="e.g. The Vanishing Merchants of Panam Nagar" value="<?= e($_POST['title'] ?? '') ?>">
            </div>

            <div class="admin-form-group">
                <label for="slug">URL Slug (Leave blank to auto-generate)</label>
                <input type="text" id="slug" name="slug" class="admin-input" placeholder="e.g. vanishing-merchants-panam" value="<?= e($_POST['slug'] ?? '') ?>">
            </div>

            <div class="admin-form-group">
                <label for="category_id">Category *</label>
                <select id="category_id" name="category_id" class="admin-select" required>
                    <option value="">Select Category...</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (isset($_POST['category_id']) && (int)$_POST['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="author">Author / Archivist Credit</label>
                <input type="text" id="author" name="author" class="admin-input" placeholder="e.g. Tariqur Rahman" value="<?= e($_POST['author'] ?? $currentAdmin['full_name']) ?>">
            </div>

            <div class="admin-form-group">
                <label for="status">Publication Status</label>
                <select id="status" name="status" class="admin-select">
                    <option value="published" <?= (isset($_POST['status']) && $_POST['status'] === 'published') ? 'selected' : '' ?>>Published Immediately</option>
                    <option value="draft" <?= (!isset($_POST['status']) || $_POST['status'] === 'draft') ? 'selected' : '' ?>>Save as Draft</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="reading_time">Estimated Reading Minutes (0 to auto-calculate)</label>
                <input type="number" id="reading_time" name="reading_time" class="admin-input" min="0" value="<?= e($_POST['reading_time'] ?? 0) ?>">
            </div>

            <div class="admin-form-group">
                <label for="cover_image">Cover Image Path</label>
                <input type="text" id="cover_image" name="cover_image" class="admin-input" placeholder="assets/images/panam-city.svg" value="<?= e($_POST['cover_image'] ?? 'assets/images/panam-city.svg') ?>">
            </div>

            <div class="admin-form-group col-span-2">
                <label for="cover_file">Or Upload Image File</label>
                <input type="file" id="cover_file" name="cover_file" class="admin-input" accept="image/*">
            </div>

            <div class="admin-form-group col-span-2">
                <label for="excerpt">Excerpt Summary (Lead callout paragraph) *</label>
                <textarea id="excerpt" name="excerpt" class="admin-textarea" style="min-height: 80px;" required placeholder="A captivating 1-2 sentence lead..."><?= e($_POST['excerpt'] ?? '') ?></textarea>
            </div>

            <div class="admin-form-group col-span-2">
                <label for="content">Full Chronicle Content (HTML supported: &lt;p&gt;, &lt;h3&gt;, &lt;blockquote&gt;) *</label>
                <textarea id="content" name="content" class="admin-textarea" style="min-height: 250px; font-family: monospace;" required placeholder="<p>Begin narrating the manuscript chronicle here...</p>"><?= e($_POST['content'] ?? '') ?></textarea>
            </div>

            <div class="admin-form-group col-span-2" style="background: rgba(255,255,255,0.02); padding: 1rem; border-radius: 6px; border: 1px solid var(--admin-border);">
                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; color: #fff;">
                    <input type="checkbox" name="is_featured" value="1" <?= isset($_POST['is_featured']) ? 'checked' : '' ?> style="accent-color: var(--admin-accent); width: 18px; height: 18px;">
                    <span>Highlight as Lead Featured Chronicle on Stories Page</span>
                </label>
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary" style="padding: 0.85rem 2rem;">
                <i class="fa-solid fa-save"></i> Save Chronicle
            </button>
            <a href="stories.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

