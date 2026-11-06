<?php
/**
 * SHADOWS OF BANGLA — Add New Place
 */

define('APP_INIT', true);
$adminPage = 'add-place';
$adminTitle = 'Add New Location';
$adminSubtitle = 'Record a new mysterious sanctuary with real coordinates and archival documentation.';

require_once __DIR__ . '/header.php';

$errors = [];
$divisions = get_bangladesh_divisions();
$districtsByDiv = get_districts_by_division();
$categories = get_categories($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $errors[] = 'Security token invalid. Please refresh the page.';
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
        $coverImage = trim($_POST['cover_image'] ?? 'assets/images/panam-city.svg');
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;

        // Image upload handling if a file was provided
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
                $errors[] = 'Only JPG, PNG, WEBP, or SVG images are allowed for cover upload.';
            }
        }

        // Validations
        if (empty($name)) $errors[] = 'Location name is required.';
        if (empty($division)) $errors[] = 'Division is required.';
        if (empty($district)) $errors[] = 'District is required.';
        if ($categoryId <= 0) $errors[] = 'Category selection is required.';
        if ($latitude == 0 || $longitude == 0) $errors[] = 'Valid geographic coordinates (latitude and longitude) are required.';
        if (empty($summary)) $errors[] = 'Short summary is required.';
        if (empty($historicalBackground)) $errors[] = 'Historical background is required.';
        if (empty($localLegends)) $errors[] = 'Local legends / folklore documentation is required.';

        // Check unique slug
        if (empty($errors) && $pdo) {
            $checkStmt = $pdo->prepare("SELECT id FROM places WHERE slug = :slug LIMIT 1");
            $checkStmt->execute([':slug' => $slug]);
            if ($checkStmt->fetch()) {
                $slug .= '-' . time();
            }

            try {
                $insSql = "INSERT INTO places 
                    (name, slug, summary, description, division, district, address, latitude, longitude, cover_image, historical_background, local_legends, paranormal_claims, why_mysterious, category_id, verification_status, sources, is_featured) 
                    VALUES 
                    (:name, :slug, :summary, :desc, :div, :dist, :addr, :lat, :lng, :img, :hist, :folk, :para, :why, :cat, :status, :sources, :feat)";
                
                $insStmt = $pdo->prepare($insSql);
                $insStmt->execute([
                    ':name' => $name,
                    ':slug' => $slug,
                    ':summary' => $summary,
                    ':desc' => $description ?: $summary,
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
                    ':feat' => $isFeatured
                ]);

                $_SESSION['flash_success'] = "Sanctuary [{$name}] successfully archived!";
                header("Location: places.php");
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Database insert error: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h2>Enter New Archival Dossier</h2>
        <a href="places.php" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Back to Places
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert-box alert-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>
                <strong>Submission halted:</strong>
                <ul style="margin-left: 1.25rem; margin-top: 0.25rem;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST" action="add-place.php" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="admin-form-grid">
            <!-- Name -->
            <div class="admin-form-group">
                <label for="name">Place Name *</label>
                <input type="text" id="name" name="name" class="admin-input" required placeholder="e.g. Sonakanda Water Fort" value="<?= e($_POST['name'] ?? '') ?>">
            </div>

            <!-- Slug -->
            <div class="admin-form-group">
                <label for="slug">URL Slug (Leave blank to auto-generate)</label>
                <input type="text" id="slug" name="slug" class="admin-input" placeholder="e.g. sonakanda-water-fort" value="<?= e($_POST['slug'] ?? '') ?>">
            </div>

            <!-- Division -->
            <div class="admin-form-group">
                <label for="division">Division *</label>
                <select id="division" name="division" class="admin-select" required>
                    <option value="">Select Division...</option>
                    <?php foreach ($divisions as $div): ?>
                        <option value="<?= e($div) ?>" <?= (isset($_POST['division']) && $_POST['division'] === $div) ? 'selected' : '' ?>><?= e($div) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- District -->
            <div class="admin-form-group">
                <label for="district">District *</label>
                <input type="text" id="district" name="district" class="admin-input" required placeholder="e.g. Narayanganj" value="<?= e($_POST['district'] ?? '') ?>">
            </div>

            <!-- Latitude -->
            <div class="admin-form-group">
                <label for="latitude">Latitude (e.g. 23.6493000) *</label>
                <input type="number" step="any" id="latitude" name="latitude" class="admin-input" required placeholder="23.6493" value="<?= e($_POST['latitude'] ?? '') ?>">
            </div>

            <!-- Longitude -->
            <div class="admin-form-group">
                <label for="longitude">Longitude (e.g. 90.6017000) *</label>
                <input type="number" step="any" id="longitude" name="longitude" class="admin-input" required placeholder="90.6017" value="<?= e($_POST['longitude'] ?? '') ?>">
            </div>

            <!-- Category -->
            <div class="admin-form-group">
                <label for="category_id">Mystery Category *</label>
                <select id="category_id" name="category_id" class="admin-select" required>
                    <option value="">Select Category...</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (isset($_POST['category_id']) && (int)$_POST['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Verification Status -->
            <div class="admin-form-group">
                <label for="verification_status">Epistemic Verification Label *</label>
                <select id="verification_status" name="verification_status" class="admin-select" required>
                    <option value="VERIFIED HISTORY" <?= (isset($_POST['verification_status']) && $_POST['verification_status'] === 'VERIFIED HISTORY') ? 'selected' : '' ?>>VERIFIED HISTORY (Documented record)</option>
                    <option value="LOCAL FOLKLORE" <?= (!isset($_POST['verification_status']) || $_POST['verification_status'] === 'LOCAL FOLKLORE') ? 'selected' : '' ?>>LOCAL FOLKLORE (Cultural memory)</option>
                    <option value="UNVERIFIED LEGEND" <?= (isset($_POST['verification_status']) && $_POST['verification_status'] === 'UNVERIFIED LEGEND') ? 'selected' : '' ?>>UNVERIFIED LEGEND (Oral claim)</option>
                </select>
            </div>

            <!-- Address -->
            <div class="admin-form-group col-span-2">
                <label for="address">Full Physical Address</label>
                <input type="text" id="address" name="address" class="admin-input" placeholder="e.g. Bandar Upazila, Narayanganj" value="<?= e($_POST['address'] ?? '') ?>">
            </div>

            <!-- Cover Image Path or Upload -->
            <div class="admin-form-group">
                <label for="cover_image">Cover Image Path (Default or existing assets/images/...)</label>
                <input type="text" id="cover_image" name="cover_image" class="admin-input" placeholder="assets/images/panam-city.svg" value="<?= e($_POST['cover_image'] ?? 'assets/images/panam-city.svg') ?>">
            </div>
            <div class="admin-form-group">
                <label for="cover_file">Or Upload Image File (JPG/PNG/WEBP)</label>
                <input type="file" id="cover_file" name="cover_file" class="admin-input" accept="image/*">
            </div>

            <!-- Summary -->
            <div class="admin-form-group col-span-2">
                <label for="summary">Card Summary (Short punchy excerpt) *</label>
                <textarea id="summary" name="summary" class="admin-textarea" style="min-height: 80px;" required placeholder="A brief 1-2 sentence overview shown on cards..."><?= e($_POST['summary'] ?? '') ?></textarea>
            </div>

            <!-- Detailed Description -->
            <div class="admin-form-group col-span-2">
                <label for="description">Introductory Atmosphere & Architecture</label>
                <textarea id="description" name="description" class="admin-textarea" placeholder="Comprehensive introduction to the site..."><?= e($_POST['description'] ?? '') ?></textarea>
            </div>

            <!-- Historical Background -->
            <div class="admin-form-group col-span-2">
                <label for="historical_background">Verified Historical Background *</label>
                <textarea id="historical_background" name="historical_background" class="admin-textarea" required placeholder="Archaeological context, imperial dynasties, dates, treaties, builders..."><?= e($_POST['historical_background'] ?? '') ?></textarea>
            </div>

            <!-- Local Legends -->
            <div class="admin-form-group col-span-2">
                <label for="local_legends">Local Legends & Oral Folklore *</label>
                <textarea id="local_legends" name="local_legends" class="admin-textarea" required placeholder="Generational oral tales, village rumors, supernatural beliefs..."><?= e($_POST['local_legends'] ?? '') ?></textarea>
            </div>

            <!-- Paranormal Claims -->
            <div class="admin-form-group col-span-2">
                <label for="paranormal_claims">Alleged Paranormal Claims (If Documented)</label>
                <textarea id="paranormal_claims" name="paranormal_claims" class="admin-textarea" placeholder="Specific witness accounts, temperature drops, auditory illusions..."><?= e($_POST['paranormal_claims'] ?? '') ?></textarea>
            </div>

            <!-- Why Mysterious -->
            <div class="admin-form-group col-span-2">
                <label for="why_mysterious">Why The Place Remains Enigmatic</label>
                <textarea id="why_mysterious" name="why_mysterious" class="admin-textarea" placeholder="The unresolved paradox, sealed passages, lost wealth..."><?= e($_POST['why_mysterious'] ?? '') ?></textarea>
            </div>

            <!-- Sources -->
            <div class="admin-form-group col-span-2">
                <label for="sources">Primary Sources & Citations</label>
                <textarea id="sources" name="sources" class="admin-textarea" style="min-height: 70px;" placeholder="Academic books, District Gazetteers, Department of Archaeology publications..."><?= e($_POST['sources'] ?? '') ?></textarea>
            </div>

            <!-- Featured Checkbox -->
            <div class="admin-form-group col-span-2" style="background: rgba(255,255,255,0.02); padding: 1rem; border-radius: 6px; border: 1px solid var(--admin-border);">
                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; color: #fff;">
                    <input type="checkbox" name="is_featured" value="1" <?= isset($_POST['is_featured']) ? 'checked' : '' ?> style="accent-color: var(--admin-accent); width: 18px; height: 18px;">
                    <span>Highlight as Featured Location on Homepage Showcase</span>
                </label>
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary" style="padding: 0.85rem 2rem;">
                <i class="fa-solid fa-save"></i> Save & Archive Location
            </button>
            <a href="places.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

