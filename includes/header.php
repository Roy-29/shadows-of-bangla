<?php
/**
 * SHADOWS OF BANGLA — Global Header Template
 */

if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? '';
$pageDesc = $pageDesc ?? 'Explore mysterious places, abandoned ruins, folk legends, and historical enigmas across Bangladesh with authentic research and verified archives.';
$pageImage = $pageImage ?? (SITE_URL . '/assets/images/panam-city.svg');
$canonicalUrl = $canonicalUrl ?? (SITE_URL . $_SERVER['REQUEST_URI']);
$fullTitle = !empty($pageTitle) ? ($pageTitle . ' — ' . SITE_NAME) : (SITE_NAME . ' — ' . SITE_TAGLINE);
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <title><?= e($fullTitle) ?></title>
    <meta name="description" content="<?= e($pageDesc) ?>">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:title" content="<?= e($fullTitle) ?>">
    <meta property="og:description" content="<?= e($pageDesc) ?>">
    <meta property="og:image" content="<?= e($pageImage) ?>">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= e($canonicalUrl) ?>">
    <meta name="twitter:title" content="<?= e($fullTitle) ?>">
    <meta name="twitter:description" content="<?= e($pageDesc) ?>">
    <meta name="twitter:image" content="<?= e($pageImage) ?>">

    <!-- Favicon (SVG Ghost Mask) -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='45' fill='%239E1B32'/><path d='M35 45a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm30 0a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm-15 20c-8 0-12-6-12-6s4-4 12-4 12 4 12 4-4 6-12 6z' fill='%23ffffff'/></svg>">

    <!-- Preconnect Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Leaflet.js CSS for Interactive Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <!-- Site Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <!-- Top Reading Progress Indicator -->
    <div id="reading-progress" class="reading-progress-bar"></div>

    <!-- Atmospheric Fog & Particles -->
    <div class="ambient-fog-layer"></div>
    <canvas id="particle-canvas"></canvas>

    <!-- Navigation Header -->
    <?php require_once __DIR__ . '/navbar.php'; ?>

