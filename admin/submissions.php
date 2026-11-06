<?php
/**
 * SHADOWS OF BANGLA — Review & Moderate Story Submissions
 * Convert Submissions into Published Chronicles with One Click
 */

define('APP_INIT', true);
$adminPage = 'submissions';
$adminTitle = 'Story Submissions';
$adminSubtitle = 'Review, vet, approve, or convert inbound community folklore into published manuscripts.';

require_once __DIR__ . '/header.php';

// Handle Submissions Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    $action = $_POST['action'] ?? '';
    $subId = (int)($_POST['submission_id'] ?? 0);

    if (!verify_csrf_token($csrf)) {
        $_SESSION['flash_error'] = 'Security verification failed.';
    } elseif ($subId > 0 && $pdo) {
        if ($action === 'approve') {
            try {
                $notes = sanitize_input($_POST['admin_notes'] ?? 'Approved by archivist.');
                $stmt = $pdo->prepare("UPDATE story_submissions SET status = 'approved', admin_notes = :notes WHERE id = :id");
                $stmt->execute([':notes' => $notes, ':id' => $subId]);
                $_SESSION['flash_success'] = 'Submission marked as approved.';
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = 'Database error: ' . $e->getMessage();
            }
        } elseif ($action === 'reject') {
            try {
                $notes = sanitize_input($_POST['admin_notes'] ?? 'Rejected: Insufficient historical or folklore authenticity.');
                $stmt = $pdo->prepare("UPDATE story_submissions SET status = 'rejected', admin_notes = :notes WHERE id = :id");
                $stmt->execute([':notes' => $notes, ':id' => $subId]);
                $_SESSION['flash_success'] = 'Submission marked as rejected.';
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = 'Database error: ' . $e->getMessage();
            }
        } elseif ($action === 'convert_to_story') {
            // Fetch submission
            $subStmt = $pdo->prepare("SELECT * FROM story_submissions WHERE id = :id LIMIT 1");
            $subStmt->execute([':id' => $subId]);
            $subData = $subStmt->fetch();

            if ($subData) {
                try {
                    $storyTitle = $subData['story_title'];
                    $storySlug = slugify($storyTitle) . '-' . time();
                    $storyExcerpt = truncate_words($subData['story_content'], 200);
                    $storyContent = '<p>' . nl2br(e($subData['story_content'])) . '</p>';
                    $author = $subData['submitter_name'] ? ($subData['submitter_name'] . ' (Field Contributor)') : 'Anonymous Contributor';
                    $coverImage = !empty($subData['image_url']) ? $subData['image_url'] : 'assets/images/panam-city.svg';
                    $readTime = calculate_reading_time($subData['story_content']);

                    $convStmt = $pdo->prepare("INSERT INTO stories 
                        (title, slug, excerpt, content, category_id, cover_image, author, reading_time, status) 
                        VALUES 
                        (:title, :slug, :excerpt, :content, :catId, :cover, :author, :readTime, 'published')");
                    
                    $convStmt->execute([
                        ':title' => $storyTitle,
                        ':slug' => $storySlug,
                        ':excerpt' => $storyExcerpt,
                        ':content' => $storyContent,
                        ':catId' => $subData['category_id'],
                        ':cover' => $coverImage,
                        ':author' => $author,
                        ':readTime' => $readTime
                    ]);

                    // Update submission status to approved
                    $updSub = $pdo->prepare("UPDATE story_submissions SET status = 'approved', admin_notes = 'Promoted to published chronicle.' WHERE id = :id");
                    $updSub->execute([':id' => $subId]);

                    $_SESSION['flash_success'] = "Chronicle published successfully! View in stories archive.";
                    header("Location: stories.php");
                    exit;
                } catch (PDOException $e) {
                    $_SESSION['flash_error'] = 'Error promoting to chronicle: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'delete') {
            try {
                $delStmt = $pdo->prepare("DELETE FROM story_submissions WHERE id = :id");
                $delStmt->execute([':id' => $subId]);
                $_SESSION['flash_success'] = 'Submission record deleted.';
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = 'Error removing submission: ' . $e->getMessage();
            }
        }
    }
    header("Location: submissions.php");
    exit;
}

$activeId = (int)($_GET['id'] ?? 0);
$activeSubmission = null;
$statusFilter = trim($_GET['status'] ?? '');
$submissions = [];

if ($pdo) {
    try {
        $where = [];
        $params = [];
        if (!empty($statusFilter)) {
            $where[] = "sub.status = :status";
            $params[':status'] = $statusFilter;
        }
        $whereClause = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $stmt = $pdo->prepare("SELECT sub.*, c.name as category_name 
                               FROM story_submissions sub 
                               JOIN categories c ON sub.category_id = c.id 
                               {$whereClause} 
                               ORDER BY sub.id DESC");
        $stmt->execute($params);
        $submissions = $stmt->fetchAll();

        if ($activeId > 0) {
            $singleStmt = $pdo->prepare("SELECT sub.*, c.name as category_name 
                                         FROM story_submissions sub 
                                         JOIN categories c ON sub.category_id = c.id 
                                         WHERE sub.id = :id LIMIT 1");
            $singleStmt->execute([':id' => $activeId]);
            $activeSubmission = $singleStmt->fetch();
        }
    } catch (PDOException $e) {
        error_log("Submissions query error: " . $e->getMessage());
    }
}
?>

<!-- Single Submission Inspection Dossier Modal/Drawer -->
<?php if ($activeSubmission): ?>
<div class="admin-card" style="border-color: var(--admin-accent); margin-bottom: 2.5rem; background: #13151e;">
    <div class="admin-card-header">
        <h2 style="color: #fff;"><i class="fa-solid fa-magnifying-glass-location text-red"></i> Inbound Submission #<?= $activeSubmission['id'] ?></h2>
        <a href="submissions.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-xmark"></i> Close Dossier</a>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <div>
            <div style="font-size: 0.8rem; color: var(--admin-accent); font-weight: 700; text-transform: uppercase; margin-bottom: 0.25rem;">
                <?= e($activeSubmission['district']) ?> &bull; <?= e($activeSubmission['location_name']) ?>
            </div>
            <h3 style="font-size: 1.6rem; font-family: var(--font-serif); margin-bottom: 1rem; color: #fff;">
                <?= e($activeSubmission['story_title']) ?>
            </h3>

            <div style="background: rgba(0,0,0,0.3); border: 1px solid var(--admin-border); padding: 1.5rem; border-radius: 6px; line-height: 1.8; color: var(--admin-text); font-size: 1rem; margin-bottom: 1.5rem; white-space: pre-wrap;">
                <?= e($activeSubmission['story_content']) ?>
            </div>

            <?php if (!empty($activeSubmission['admin_notes'])): ?>
                <div style="background: rgba(255,255,255,0.03); padding: 0.75rem 1rem; border-radius: 4px; font-size: 0.85rem; color: var(--admin-muted); margin-bottom: 1.5rem;">
                    <strong>Archivist Notes:</strong> <?= e($activeSubmission['admin_notes']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <div style="background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: 6px; padding: 1.25rem; font-size: 0.88rem;">
                <div style="margin-bottom: 0.75rem;">
                    <span style="color: var(--admin-muted); display: block; font-size: 0.75rem;">CONTRIBUTOR</span>
                    <strong style="color: #fff;"><?= e($activeSubmission['submitter_name']) ?></strong>
                </div>
                <div style="margin-bottom: 0.75rem;">
                    <span style="color: var(--admin-muted); display: block; font-size: 0.75rem;">EMAIL</span>
                    <span><?= e($activeSubmission['email'] ?: 'Not provided') ?></span>
                </div>
                <div style="margin-bottom: 0.75rem;">
                    <span style="color: var(--admin-muted); display: block; font-size: 0.75rem;">CATEGORY</span>
                    <span><?= e($activeSubmission['category_name']) ?></span>
                </div>
                <div style="margin-bottom: 0.75rem;">
                    <span style="color: var(--admin-muted); display: block; font-size: 0.75rem;">STATUS</span>
                    <span class="pill-status pill-<?= $activeSubmission['status'] ?>"><?= e($activeSubmission['status']) ?></span>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <span style="color: var(--admin-muted); display: block; font-size: 0.75rem;">SUBMITTED</span>
                    <span><?= format_display_date($activeSubmission['created_at']) ?></span>
                </div>

                <!-- Action Triggers -->
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <form method="POST" action="submissions.php" onsubmit="return confirm('Promote this submission to a live published chronicle on the Stories archive?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="convert_to_story">
                        <input type="hidden" name="submission_id" value="<?= $activeSubmission['id'] ?>">
                        <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Convert to Published Story
                        </button>
                    </form>

                    <?php if ($activeSubmission['status'] !== 'approved'): ?>
                        <form method="POST" action="submissions.php">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="submission_id" value="<?= $activeSubmission['id'] ?>">
                            <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%; color: #4ade80;">
                                <i class="fa-solid fa-check"></i> Mark Approved
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if ($activeSubmission['status'] !== 'rejected'): ?>
                        <form method="POST" action="submissions.php">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="submission_id" value="<?= $activeSubmission['id'] ?>">
                            <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%; color: #f87171;">
                                <i class="fa-solid fa-ban"></i> Mark Rejected
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Submissions Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2>Submissions Inbox (<?= count($submissions) ?>)</h2>
        <div style="display: flex; gap: 0.5rem;">
            <a href="submissions.php" class="btn btn-secondary btn-sm <?= empty($statusFilter) ? 'active' : '' ?>">All</a>
            <a href="submissions.php?status=pending" class="btn btn-secondary btn-sm <?= $statusFilter === 'pending' ? 'active' : '' ?>">Pending</a>
            <a href="submissions.php?status=approved" class="btn btn-secondary btn-sm <?= $statusFilter === 'approved' ? 'active' : '' ?>">Approved</a>
            <a href="submissions.php?status=rejected" class="btn btn-secondary btn-sm <?= $statusFilter === 'rejected' ? 'active' : '' ?>">Rejected</a>
        </div>
    </div>

    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Story Title</th>
                    <th>Contributor</th>
                    <th>Location / District</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($submissions)): ?>
                    <?php foreach ($submissions as $sub): ?>
                        <tr style="<?= ($activeId === (int)$sub['id']) ? 'background: rgba(158,27,50,0.15);' : '' ?>">
                            <td><?= format_display_date($sub['created_at']) ?></td>
                            <td>
                                <strong><a href="submissions.php?id=<?= $sub['id'] ?>" style="color: #fff;"><?= e($sub['story_title']) ?></a></strong>
                            </td>
                            <td><?= e($sub['submitter_name']) ?></td>
                            <td><?= e($sub['location_name']) ?> (<?= e($sub['district']) ?>)</td>
                            <td><?= e($sub['category_name']) ?></td>
                            <td>
                                <span class="pill-status pill-<?= $sub['status'] ?>">
                                    <?= e($sub['status']) ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div class="action-btns" style="justify-content: flex-end;">
                                    <a href="submissions.php?id=<?= $sub['id'] ?>" class="btn-icon" title="Inspect & Review">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <form method="POST" action="submissions.php" onsubmit="return confirm('Promote this story to live published status?');" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="convert_to_story">
                                        <input type="hidden" name="submission_id" value="<?= $sub['id'] ?>">
                                        <button type="submit" class="btn-icon btn-icon-approve" title="Publish as Story">
                                            <i class="fa-solid fa-arrow-up-right-dots"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="submissions.php" onsubmit="return confirmDeletion('<?= e(addslashes($sub['story_title'])) ?>', 'submission');" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="submission_id" value="<?= $sub['id'] ?>">
                                        <button type="submit" class="btn-icon btn-icon-delete" title="Purge Submission">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--admin-muted);">
                            No submissions in this view.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

