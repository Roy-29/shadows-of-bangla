<?php
/**
 * SHADOWS OF BANGLA — Edit Existing Chronicle
 */

define('APP_INIT', true);
$adminPage = 'stories';
$adminTitle = 'Edit Manuscript Chronicle';
$adminSubtitle = 'Update editorial text, reading metrics, or publishing status.';

require_once __DIR__ . '/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: stories.php");
    exit;
}

$errors = [];
$categories = get_categories($pdo);

// Fetch story
$stmt = $pdo->prepare("SELECT * FROM stories WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$story = $stmt->fetch();

if (!$story) {
    $_SESSION['flash_error'] = 'Chronicle not found.';
    header("Location: stories.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $errors[] = 'Security verification failed.';
    } else {
        $title = sanitize_input($_POST['title'] ?? '');
        $slug = slugify($_POST['slug'] ?? $title);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $author = sanitize_input($_POST['author'] ?? 'Shadows Archive');
        $excerpt = sanitize_input($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'draft';
        $coverImage = trim($_POST['cover_image'] ?? $story['cover_image']);
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
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
        if (empty($content)) $errors[] = 'Content cannot be empty.';

        if (empty($errors)) {
            $checkStmt = $pdo->prepare("SELECT id FROM stories WHERE slug = :slug AND id != :id LIMIT 1");
            $checkStmt->execute([':slug' => $slug, ':id' => $id]);
            if ($checkStmt->fetch()) {
                $slug .= '-' . time();
            }

            try {
                $updSql = "UPDATE stories SET 
                    title = :title,
                    slug = :slug,
                    excerpt = :excerpt,
                    content = :content,
                    category_id = :catId,
                    cover_image = :cover,
                    author = :author,
                    reading_time = :readTime,
                    is_featured = :feat,
                    status = :status
                    WHERE id = :id";
                
                $updStmt = $pdo->prepare($updSql);
                $updStmt->execute([
                    ':title' => $title,
                    ':slug' => $slug,
                    ':excerpt' => $excerpt,
                    ':content' => $content,
                    ':catId' => $categoryId,
                    ':cover' => $coverImage,
                    ':author' => $author,
                    ':readTime' => $readingTime,
                    ':feat' => $isFeatured,
                    ':status' => $status,
                    ':id' => $id
                ]);

                $_SESSION['flash_success'] = "Chronicle [{$title}] updated successfully!";
                header("Location: stories.php");
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Database update error: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h2>Editing Chronicle: <?= e($story['title']) ?></h2>
        <div style="display: flex; gap: 0.5rem;">
            <a href="../story.php?slug=<?= e($story['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> View Public Reader
            </a>
            <a href="stories.php" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Back to Stories
            </a>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert-box alert-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>
                <strong>Update halted:</strong>
                <ul style="margin-left: 1.25rem;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST" action="edit-story.php?id=<?= $id ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="admin-form-grid">
            <div class="admin-form-group col-span-2">
                <label for="title">Chronicle Title *</label>
                <input type="text" id="title" name="title" class="admin-input" required value="<?= e($_POST['title'] ?? $story['title']) ?>">
            </div>

            <div class="admin-form-group">
                <label for="slug">URL Slug *</label>
                <input type="text" id="slug" name="slug" class="admin-input" required value="<?= e($_POST['slug'] ?? $story['slug']) ?>">
            </div>

            <div class="admin-form-group">
                <label for="category_id">Category *</label>
                <select id="category_id" name="category_id" class="admin-select" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ((int)($_POST['category_id'] ?? $story['category_id']) === (int)$cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="author">Author / Archivist Credit</label>
                <input type="text" id="author" name="author" class="admin-input" value="<?= e($_POST['author'] ?? $story['author']) ?>">
            </div>

            <div class="admin-form-group">
                <label for="status">Publication Status</label>
                <select id="status" name="status" class="admin-select">
                    <?php $currStatus = $_POST['status'] ?? $story['status']; ?>
                    <option value="published" <?= $currStatus === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="draft" <?= $currStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="reading_time">Estimated Reading Minutes</label>
                <input type="number" id="reading_time" name="reading_time" class="admin-input" min="1" value="<?= e($_POST['reading_time'] ?? $story['reading_time']) ?>">
            </div>

            <div class="admin-form-group">
                <label for="cover_image">Cover Image Path</label>
                <input type="text" id="cover_image" name="cover_image" class="admin-input" value="<?= e($_POST['cover_image'] ?? $story['cover_image']) ?>">
            </div>

            <div class="admin-form-group col-span-2">
                <label for="cover_file">Replace with File Upload</label>
                <input type="file" id="cover_file" name="cover_file" class="admin-input" accept="image/*">
            </div>

            <div class="admin-form-group col-span-2">
                <label for="excerpt">Excerpt Summary *</label>
                <textarea id="excerpt" name="excerpt" class="admin-textarea" style="min-height: 80px;" required><?= e($_POST['excerpt'] ?? $story['excerpt']) ?></textarea>
            </div>

            <div class="admin-form-group col-span-2">
                <label for="content">Full Chronicle Content *</label>
                <textarea id="content" name="content" class="admin-textarea" style="min-height: 250px; font-family: monospace;" required><?= e($_POST['content'] ?? $story['content']) ?></textarea>
            </div>

            <div class="admin-form-group col-span-2" style="background: rgba(255,255,255,0.02); padding: 1rem; border-radius: 6px; border: 1px solid var(--admin-border);">
                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; color: #fff;">
                    <?php $isFeat = isset($_POST['is_featured']) ? true : (bool)$story['is_featured']; ?>
                    <input type="checkbox" name="is_featured" value="1" <?= $isFeat ? 'checked' : '' ?> style="accent-color: var(--admin-accent); width: 18px; height: 18px;">
                    <span>Highlight as Lead Featured Chronicle on Stories Page</span>
                </label>
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary" style="padding: 0.85rem 2rem;">
                <i class="fa-solid fa-save"></i> Save Changes
            </button>
            <a href="stories.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

