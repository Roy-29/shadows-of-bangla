<?php
/**
 * SHADOWS OF BANGLA — Submit a Local Mystery Story
 * Server-side Validation, CSRF Protection, Anti-Spam Honeypot & Pending Storage
 */

define('APP_INIT', true);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Submit a Local Mystery';
$activePage = 'submit';

$pdo = get_db_connection();
$categories = get_categories($pdo);
$divisions = get_bangladesh_divisions();
$districtsByDiv = get_districts_by_division();

$errors = [];
$successMessage = '';
$submissionId = null;

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Check Anti-Spam Honeypot
    if (!empty($_POST['website_url'])) {
        // Silently exit or show generic message for automated bots
        $errors[] = 'Automated submission rejected.';
    }

    // 2. Validate CSRF Token
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrfToken)) {
        $errors[] = 'Security token expired or invalid. Please refresh the page and try again.';
    }

    // 3. Extract and Sanitize Inputs
    $name = sanitize_input($_POST['submitter_name'] ?? '');
    if (empty($name)) {
        $name = 'Anonymous Explorer';
    }
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL) ? trim($_POST['email']) : null;
    $title = sanitize_input($_POST['story_title'] ?? '');
    $district = sanitize_input($_POST['district'] ?? '');
    $location = sanitize_input($_POST['location_name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $content = trim($_POST['story_content'] ?? '');
    $imageUrl = filter_var(trim($_POST['image_url'] ?? ''), FILTER_VALIDATE_URL) ? trim($_POST['image_url']) : null;
    $agreed = isset($_POST['agree_terms']);

    // 4. Validate Required Fields
    if (empty($title) || mb_strlen($title) < 5) {
        $errors[] = 'Please provide a descriptive story title (at least 5 characters).';
    }
    if (empty($district)) {
        $errors[] = 'Please specify the district in Bangladesh where this story originates.';
    }
    if (empty($location)) {
        $errors[] = 'Please specify the location or landmark name.';
    }
    if ($categoryId <= 0) {
        $errors[] = 'Please select a mystery category.';
    }
    if (empty($content) || mb_strlen($content) < 50) {
        $errors[] = 'Please describe the story or folklore with sufficient detail (at least 50 characters).';
    }
    if (!$agreed) {
        $errors[] = 'You must confirm that your submission represents genuine regional folklore or personal witness accounts.';
    }

    // 5. Insert into Database as Pending
    if (empty($errors)) {
        if (!$pdo) {
            $errors[] = 'Database is currently offline. Please ensure MySQL is running in XAMPP.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO story_submissions 
                    (submitter_name, email, story_title, district, location_name, category_id, story_content, image_url, status) 
                    VALUES (:name, :email, :title, :district, :loc, :catId, :content, :img, 'pending')");
                
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':title' => $title,
                    ':district' => $district,
                    ':loc' => $location,
                    ':catId' => $categoryId,
                    ':content' => $content,
                    ':img' => $imageUrl
                ]);

                $submissionId = $pdo->lastInsertId();
                $successMessage = "Your chronicle has been securely received (Ref #SUB-{$submissionId}). Our historical archivist team will review the submission before publishing.";
                
                // Clear POST variables
                $_POST = [];
            } catch (PDOException $e) {
                error_log("Submission insertion error: " . $e->getMessage());
                $errors[] = 'A server error occurred while storing your chronicle. Please try again later.';
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Submission Header -->
<section style="padding: 7.5rem 0 3rem; background: linear-gradient(180deg, #050608 0%, #11131a 100%); border-bottom: 1px solid var(--border-subtle);">
    <div class="container text-center" style="max-width: 800px;">
        <span class="section-subtitle">Community Chronicles</span>
        <h1 class="section-title" style="font-size: 2.75rem; margin-bottom: 0.75rem;">Submit a Local Mystery</h1>
        <p class="section-description">
            Do you know of an abandoned zamindar mansion, an unmapped ancient pond, or an oral legend whispered in your village? Preserve Bengal’s hidden stories.
        </p>
    </div>
</section>

<!-- Main Form Section -->
<section class="section-padding">
    <div class="container" style="max-width: 840px;">
        <?php if (!empty($successMessage)): ?>
            <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #34d399; padding: 2rem; border-radius: 8px; margin-bottom: 2.5rem; text-align: center;">
                <i class="fa-solid fa-circle-check" style="font-size: 2.5rem; margin-bottom: 1rem;"></i>
                <h3 style="font-size: 1.5rem; font-family: var(--font-serif); margin-bottom: 0.5rem; color: #fff;">Chronicle Sealed & Received</h3>
                <p style="color: var(--text-main); font-size: 1.05rem;"><?= e($successMessage) ?></p>
                <div style="margin-top: 1.5rem;">
                    <a href="stories.php" class="btn btn-secondary btn-sm">Explore Other Stories</a>
                    <a href="index.php" class="btn btn-primary btn-sm">Return Home</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; padding: 1.25rem 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                <div style="font-weight: 700; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> Please address the following issues:
                </div>
                <ul style="margin-left: 1.5rem; font-size: 0.92rem;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="content-box">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.75rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
                <i class="fa-solid fa-feather-pointed" style="font-size: 1.5rem; color: var(--accent-red);"></i>
                <div>
                    <h3 style="font-size: 1.3rem; margin: 0; font-family: var(--font-serif);">Manuscript Submission Form</h3>
                    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">Submissions are vetted by curators to prevent fabrication or defamatory rumors.</p>
                </div>
            </div>

            <form action="submit-story.php" method="POST">
                <?= csrf_field() ?>

                <!-- Honeypot anti-spam field (hidden from real users) -->
                <input type="text" name="website_url" class="honeypot-field" tabindex="-1" autocomplete="off">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <!-- Submitter Name -->
                    <div class="form-group">
                        <label class="form-label" for="submitter_name">Your Name (Optional)</label>
                        <input type="text" id="submitter_name" name="submitter_name" class="form-control" placeholder="e.g. Tariqur Rahman (or leave blank for Anonymous)" value="<?= e($_POST['submitter_name'] ?? '') ?>">
                    </div>

                    <!-- Submitter Email -->
                    <div class="form-group">
                        <label class="form-label" for="email">Contact Email (Optional)</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="For verification questions only" value="<?= e($_POST['email'] ?? '') ?>">
                    </div>
                </div>

                <!-- Story Title -->
                <div class="form-group">
                    <label class="form-label" for="story_title">Story / Chronicle Title <span style="color: var(--accent-red);">*</span></label>
                    <input type="text" id="story_title" name="story_title" class="form-control" placeholder="e.g. The Night Lantern of Baliati Inner Garden" required value="<?= e($_POST['story_title'] ?? '') ?>">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <!-- District -->
                    <div class="form-group">
                        <label class="form-label" for="district">District in Bangladesh <span style="color: var(--accent-red);">*</span></label>
                        <select id="district" name="district" class="map-select form-control" required>
                            <option value="">Select District...</option>
                            <?php foreach ($districtsByDiv as $divName => $distList): ?>
                                <optgroup label="<?= e($divName) ?> Division">
                                    <?php foreach ($distList as $dist): ?>
                                        <option value="<?= e($dist) ?>" <?= (isset($_POST['district']) && $_POST['district'] === $dist) ? 'selected' : '' ?>><?= e($dist) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Location Name -->
                    <div class="form-group">
                        <label class="form-label" for="location_name">Specific Location / Landmark <span style="color: var(--accent-red);">*</span></label>
                        <input type="text" id="location_name" name="location_name" class="form-control" placeholder="e.g. Old Post Office, Saturia" required value="<?= e($_POST['location_name'] ?? '') ?>">
                    </div>
                </div>

                <!-- Category -->
                <div class="form-group">
                    <label class="form-label" for="category_id">Classification Category <span style="color: var(--accent-red);">*</span></label>
                    <select id="category_id" name="category_id" class="map-select form-control" required>
                        <option value="">Choose Mystery Category...</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat['id']) ?>" <?= (isset($_POST['category_id']) && (int)$_POST['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?> &mdash; <?= e($cat['description']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Story Description -->
                <div class="form-group">
                    <label class="form-label" for="story_content">Full Story & Anecdotal Evidence <span style="color: var(--accent-red);">*</span></label>
                    <textarea id="story_content" name="story_content" class="form-control" rows="8" placeholder="Narrate the folklore, what was witnessed, the history behind the place, and who told you the story..." required><?= e($_POST['story_content'] ?? '') ?></textarea>
                    <small style="color: var(--text-muted); font-size: 0.78rem; display: block; margin-top: 0.35rem;">
                        Minimum 50 characters. Provide context: is this a generational oral myth, a personal expedition, or local village gossip?
                    </small>
                </div>

                <!-- Optional Image URL -->
                <div class="form-group">
                    <label class="form-label" for="image_url">Optional Image Link (Web URL)</label>
                    <input type="url" id="image_url" name="image_url" class="form-control" placeholder="https://..." value="<?= e($_POST['image_url'] ?? '') ?>">
                </div>

                <!-- Guidelines Agreement Checkbox -->
                <div class="form-group" style="background: var(--bg-secondary); padding: 1.25rem; border-radius: 6px; border: 1px solid var(--border-subtle); margin-top: 1.5rem;">
                    <label style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; font-size: 0.88rem; color: var(--text-main);">
                        <input type="checkbox" name="agree_terms" value="1" required style="accent-color: var(--accent-red); margin-top: 0.25rem;">
                        <span>
                            I confirm that this submission accurately describes authentic regional folklore, personal experience, or documented oral history, and does not contain defamatory claims or fabricated hoaxes. I understand that all submissions require administrator approval prior to public release.
                        </span>
                    </label>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">
                        <i class="fa-solid fa-paper-plane"></i> Submit Chronicle to Archivists
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

