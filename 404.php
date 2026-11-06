<?php
/**
 * SHADOWS OF BANGLA — 404 Error Page
 */

define('APP_INIT', true);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Lost in the Void (404)';
$activePage = '';

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero-section" style="min-height: 85vh;">
    <img src="assets/images/hero-bg.svg" alt="Lost in Fog" class="hero-backdrop-image" style="opacity: 0.25;">
    <div class="hero-overlay"></div>

    <div class="container hero-content text-center">
        <div style="font-size: 5rem; font-family: var(--font-serif); font-weight: 900; color: var(--accent-red); margin-bottom: 0.5rem; text-shadow: 0 0 35px var(--accent-glow);">
            404
        </div>
        <h1 style="font-size: 2.25rem; font-family: var(--font-serif); margin-bottom: 1rem; color: #fff;">
            Swallowed by the Delta Fog
        </h1>
        <p style="font-size: 1.1rem; color: var(--fog-grey); max-width: 580px; margin: 0 auto 2.5rem; line-height: 1.7;">
            The coordinates you sought do not exist in the imperial archives, or have been erased by time and changing rivers.
        </p>

        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="index.php" class="btn btn-primary">
                <i class="fa-solid fa-compass"></i> Return to Safety (Home)
            </a>
            <a href="explore.php" class="btn btn-secondary">
                <i class="fa-solid fa-landmark"></i> Explore All Places
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

