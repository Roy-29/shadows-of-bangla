<?php
/**
 * SHADOWS OF BANGLA — Edit Existing Place
 */

define('APP_INIT', true);
$adminPage = 'places';
$adminTitle = 'Edit Location Dossier';
$adminSubtitle = 'Update coordinates, archaeological notes, or folklore classifications.';

require_once __DIR__ . '/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: places.php");
    exit;
}

$errors = [];
$divisions = get_bangladesh_divisions();
$categories = get_categories($pdo);

// Fetch existing place
$stmt = $pdo->prepare("SELECT * FROM places WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$place = $stmt->fetch();

if (!$place) {
    $_SESSION['flash_error'] = 'Location not found in the archives.';
    header("Location: places.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $errors[] = 'Security verification failed.';
    } else {
        $name = sanitize_input($_POST['name'] ?? '');
        $slug = slugify($_POST['slug'] ?? $name);
        $division = sanitize_input($_POST['division'] ?? '');
        $district = sanitize_input($_POST['district'] ?? '');
        $address = sanitize_input($_POST['address'] ?? '');
        $latitude = (float)($_POST['latitude'] ?? 0);
        $longitude = (float)($_POST['longitude'] ?? 0);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $verificationStatus = $_POST['verification_status'] ?? 'LOCAL FOLKLORE';
        $summary = sanitize_input($_POST['summary'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $historicalBackground = trim($_POST['historical_background'] ?? '');
        $localLegends = trim($_POST['local_legends'] ?? '');
        $paranormalClaims = trim($_POST['paranormal_claims'] ?? '');
        $whyMysterious = trim($_POST['why_mysterious'] ?? '');
        $sources = trim($_POST['sources'] ?? '');
        $coverImage = trim($_POST['cover_image'] ?? $place['cover_image']);
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;

        // Image upload handling
        if (isset($_FILES['cover_file']) && $_FILES['cover_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['cover_file'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];
            if (in_array($file['type'], $allowedTypes)) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $newFilename = 'place_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destPath = dirname(__DIR__) . '/uploads/' . $newFilename;
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $coverImage = 'uploads/' . $newFilename;
                }
            } else {
                $errors[] = 'Invalid image type uploaded.';
            }
        }

        if (empty($name)) $errors[] = 'Location name is required.';
        if (empty($division)) $errors[] = 'Division is required.';
        if (empty($district)) $errors[] = 'District is required.';
        if ($categoryId <= 0) $errors[] = 'Category selection is required.';
        if ($latitude == 0 || $longitude == 0) $errors[] = 'Valid geographic coordinates are required.';
        if (empty($summary)) $errors[] = 'Short summary is required.';

        // Check unique slug (excluding current record)
        if (empty($errors)) {
            $checkStmt = $pdo->prepare("SELECT id FROM places WHERE slug = :slug AND id != :id LIMIT 1");
            $checkStmt->execute([':slug' => $slug, ':id' => $id]);
            if ($checkStmt->fetch()) {
                $slug .= '-' . time();
            }

            try {
                $updSql = "UPDATE places SET 
                    name = :name,
                    slug = :slug,
                    summary = :summary,
                    description = :desc,
                    division = :div,
                    district = :dist,
                    address = :addr,
                    latitude = :lat,
                    longitude = :lng,
                    cover_image = :img,
                    historical_background = :hist,
                    local_legends = :folk,
                    paranormal_claims = :para,
                    why_mysterious = :why,
                    category_id = :cat,
                    verification_status = :status,
                    sources = :sources,
                    is_featured = :feat
                    WHERE id = :id";
                
                $updStmt = $pdo->prepare($updSql);
                $updStmt->execute([
                    ':name' => $name,
                    ':slug' => $slug,
                    ':summary' => $summary,
                    ':desc' => $description,
                    ':div' => $division,
                    ':dist' => $district,
                    ':addr' => $address,
                    ':lat' => $latitude,
                    ':lng' => $longitude,
                    ':img' => $coverImage,
                    ':hist' => $historicalBackground,
                    ':folk' => $localLegends,
                    ':para' => $paranormalClaims,
                    ':why' => $whyMysterious,
                    ':cat' => $categoryId,
                    ':status' => $verificationStatus,
                    ':sources' => $sources,
                    ':feat' => $isFeatured,
                    ':id' => $id
                ]);

                $_SESSION['flash_success'] = "Dossier [{$name}] updated successfully!";
                header("Location: places.php");
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
        <h2>Editing: <?= e($place['name']) ?></h2>
        <div style="display: flex; gap: 0.5rem;">
            <a href="../place.php?slug=<?= e($place['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> View Public Dossier
            </a>
            <a href="places.php" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Back to Places
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

    <form method="POST" action="edit-place.php?id=<?= $id ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label for="name">Place Name *</label>
                <input type="text" id="name" name="name" class="admin-input" required value="<?= e($_POST['name'] ?? $place['name']) ?>">
            </div>

            <div class="admin-form-group">
                <label for="slug">URL Slug *</label>
                <input type="text" id="slug" name="slug" class="admin-input" required value="<?= e($_POST['slug'] ?? $place['slug']) ?>">
            </div>

            <div class="admin-form-group">
                <label for="division">Division *</label>
                <select id="division" name="division" class="admin-select" required>
                    <?php foreach ($divisions as $div): ?>
                        <option value="<?= e($div) ?>" <?= (($_POST['division'] ?? $place['division']) === $div) ? 'selected' : '' ?>><?= e($div) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="district">District *</label>
                <input type="text" id="district" name="district" class="admin-input" required value="<?= e($_POST['district'] ?? $place['district']) ?>">
            </div>

            <div class="admin-form-group">
                <label for="latitude">Latitude *</label>
                <input type="number" step="any" id="latitude" name="latitude" class="admin-input" required value="<?= e($_POST['latitude'] ?? $place['latitude']) ?>">
            </div>

            <div class="admin-form-group">
                <label for="longitude">Longitude *</label>
                <input type="number" step="any" id="longitude" name="longitude" class="admin-input" required value="<?= e($_POST['longitude'] ?? $place['longitude']) ?>">
            </div>

            <div class="admin-form-group">
                <label for="category_id">Mystery Category *</label>
                <select id="category_id" name="category_id" class="admin-select" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ((int)($_POST['category_id'] ?? $place['category_id']) === (int)$cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="verification_status">Epistemic Verification Label *</label>
                <select id="verification_status" name="verification_status" class="admin-select" required>
                    <?php $currStatus = $_POST['verification_status'] ?? $place['verification_status']; ?>
                    <option value="VERIFIED HISTORY" <?= $currStatus === 'VERIFIED HISTORY' ? 'selected' : '' ?>>VERIFIED HISTORY (Documented record)</option>
                    <option value="LOCAL FOLKLORE" <?= $currStatus === 'LOCAL FOLKLORE' ? 'selected' : '' ?>>LOCAL FOLKLORE (Cultural memory)</option>
                    <option value="UNVERIFIED LEGEND" <?= $currStatus === 'UNVERIFIED LEGEND' ? 'selected' : '' ?>>UNVERIFIED LEGEND (Oral claim)</option>
                </select>
            </div>

            <div class="admin-form-group col-span-2">
                <label for="address">Full Physical Address</label>
                <input type="text" id="address" name="address" class="admin-input" value="<?= e($_POST['address'] ?? $place['address']) ?>">
            </div>

            <div class="admin-form-group">
                <label for="cover_image">Cover Image Path</label>
                <input type="text" id="cover_image" name="cover_image" class="admin-input" value="<?= e($_POST['cover_image'] ?? $place['cover_image']) ?>">
            </div>

            <div class="admin-form-group">
                <label for="cover_file">Replace with File Upload</label>
                <input type="file" id="cover_file" name="cover_file" class="admin-input" accept="image/*">
            </div>

            <div class="admin-form-group col-span-2">
                <label for="summary">Card Summary *</label>
                <textarea id="summary" name="summary" class="admin-textarea" style="min-height: 80px;" required><?= e($_POST['summary'] ?? $place['summary']) ?></textarea>
            </div>

            <div class="admin-form-group col-span-2">
                <label for="description">Introductory Atmosphere & Architecture</label>
                <textarea id="description" name="description" class="admin-textarea"><?= e($_POST['description'] ?? $place['description']) ?></textarea>
            </div>

            <div class="admin-form-group col-span-2">
                <label for="historical_background">Verified Historical Background *</label>
                <textarea id="historical_background" name="historical_background" class="admin-textarea" required><?= e($_POST['historical_background'] ?? $place['historical_background']) ?></textarea>
            </div>

            <div class="admin-form-group col-span-2">
                <label for="local_legends">Local Legends & Oral Folklore *</label>
                <textarea id="local_legends" name="local_legends" class="admin-textarea" required><?= e($_POST['local_legends'] ?? $place['local_legends']) ?></textarea>
            </div>

            <div class="admin-form-group col-span-2">
                <label for="paranormal_claims">Alleged Paranormal Claims</label>
                <textarea id="paranormal_claims" name="paranormal_claims" class="admin-textarea"><?= e($_POST['paranormal_claims'] ?? $place['paranormal_claims']) ?></textarea>
            </div>

            <div class="admin-form-group col-span-2">
                <label for="why_mysterious">Why The Place Remains Enigmatic</label>
                <textarea id="why_mysterious" name="why_mysterious" class="admin-textarea"><?= e($_POST['why_mysterious'] ?? $place['why_mysterious']) ?></textarea>
            </div>

            <div class="admin-form-group col-span-2">
                <label for="sources">Primary Sources & Citations</label>
                <textarea id="sources" name="sources" class="admin-textarea" style="min-height: 70px;"><?= e($_POST['sources'] ?? $place['sources']) ?></textarea>
            </div>

            <div class="admin-form-group col-span-2" style="background: rgba(255,255,255,0.02); padding: 1rem; border-radius: 6px; border: 1px solid var(--admin-border);">
                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; color: #fff;">
                    <?php $isFeat = isset($_POST['is_featured']) ? true : (bool)$place['is_featured']; ?>
                    <input type="checkbox" name="is_featured" value="1" <?= $isFeat ? 'checked' : '' ?> style="accent-color: var(--admin-accent); width: 18px; height: 18px;">
                    <span>Highlight as Featured Location on Homepage Showcase</span>
                </label>
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary" style="padding: 0.85rem 2rem;">
                <i class="fa-solid fa-save"></i> Save Changes
            </button>
            <a href="places.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

