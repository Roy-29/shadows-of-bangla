<?php
/**
 * SHADOWS OF BANGLA — Admin Panel Header & Sidebar Partial
 */

if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin_auth('login.php');

$currentAdmin = get_current_admin();
$adminPage = $adminPage ?? 'dashboard';
$pdo = get_db_connection();

// Count pending submissions for sidebar notification badge
$pendingSubmissionsCount = 0;
if ($pdo) {
    try {
        $pendingSubmissionsCount = (int)$pdo->query("SELECT COUNT(*) FROM story_submissions WHERE status = 'pending'")->fetchColumn();
    } catch (PDOException $e) {
        // ignore
    }
}

$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($adminTitle ?? 'Archivist Control Room') ?> — <?= e(SITE_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-brand">
            <div class="logo-symbol" style="width: 34px; height: 34px; font-size: 1rem;">
                <i class="fa-solid fa-ghost"></i>
            </div>
            <div>
                <h2>SHADOWS</h2>
                <span>Archivist Suite</span>
            </div>
        </div>

        <nav class="admin-nav">
            <a href="index.php" class="admin-nav-item <?= $adminPage === 'dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
            <a href="places.php" class="admin-nav-item <?= $adminPage === 'places' ? 'active' : '' ?>">
                <i class="fa-solid fa-monument"></i> Manage Places
            </a>
            <a href="add-place.php" class="admin-nav-item <?= $adminPage === 'add-place' ? 'active' : '' ?>">
                <i class="fa-solid fa-plus-circle"></i> Add Location
            </a>
            <a href="stories.php" class="admin-nav-item <?= $adminPage === 'stories' ? 'active' : '' ?>">
                <i class="fa-solid fa-book-skull"></i> Manage Stories
            </a>
            <a href="add-story.php" class="admin-nav-item <?= $adminPage === 'add-story' ? 'active' : '' ?>">
                <i class="fa-solid fa-feather-pointed"></i> Draft Story
            </a>
            <a href="submissions.php" class="admin-nav-item <?= $adminPage === 'submissions' ? 'active' : '' ?>">
                <i class="fa-solid fa-inbox"></i> Submissions
                <?php if ($pendingSubmissionsCount > 0): ?>
                    <span class="nav-badge-count"><?= $pendingSubmissionsCount ?></span>
                <?php endif; ?>
            </a>
            <a href="categories.php" class="admin-nav-item <?= $adminPage === 'categories' ? 'active' : '' ?>">
                <i class="fa-solid fa-tags"></i> Categories
            </a>

            <div style="margin: 1.5rem 0.5rem 0.5rem; border-top: 1px solid var(--admin-border); padding-top: 1rem;">
                <span style="font-size: 0.68rem; text-transform: uppercase; color: var(--admin-muted); letter-spacing: 0.1em; display: block; margin-bottom: 0.5rem;">System</span>
                <a href="../config/setup.php" class="admin-nav-item" target="_blank" style="font-size: 0.8rem;">
                    <i class="fa-solid fa-database"></i> DB Health / Setup
                </a>
                <a href="../index.php" class="admin-nav-item" target="_blank" style="font-size: 0.8rem;">
                    <i class="fa-solid fa-globe"></i> View Public Site
                </a>
            </div>
        </nav>

        <div class="admin-footer-profile">
            <div class="profile-info">
                <span class="profile-name"><?= e($currentAdmin['full_name']) ?></span>
                <span class="profile-role"><?= e($currentAdmin['role']) ?></span>
            </div>
            <a href="logout.php" class="btn-icon" title="Terminate Session">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </aside>

    <!-- Admin Main Body -->
    <main class="admin-main">
        <div class="admin-topbar">
            <div class="topbar-title">
                <h1><?= e($adminTitle ?? 'Archivist Control Room') ?></h1>
                <p><?= e($adminSubtitle ?? 'Manage verified Bangladeshi mysteries, editorial archives, and field submissions.') ?></p>
            </div>
            <div class="admin-actions">
                <a href="add-place.php" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> New Location
                </a>
                <a href="add-story.php" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-feather"></i> New Story
                </a>
            </div>
        </div>

        <?php if (!empty($flashSuccess)): ?>
            <div class="alert-box alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <div><?= e($flashSuccess) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div class="alert-box alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div><?= e($flashError) ?></div>
            </div>
        <?php endif; ?>

