<?php
/**
 * SHADOWS OF BANGLA — Navigation Bar Component
 */

if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

require_once __DIR__ . '/auth.php';

$activePage = $activePage ?? '';
$isAdmin = is_admin_logged_in();
?>
<header class="site-navbar">
    <div class="container nav-wrapper">
        <!-- Brand Logo -->
        <a href="index.php" class="brand-logo" title="<?= e(SITE_NAME) ?> — Return to Home">
            <div class="logo-symbol">
                <i class="fa-solid fa-ghost"></i>
            </div>
            <div class="brand-text">
                <span class="brand-name">SHADOWS OF BANGLA</span>
                <span class="brand-tagline-nav">Beyond the Known.</span>
            </div>
        </a>

        <!-- Main Navigation Links -->
        <nav>
            <ul class="nav-menu">
                <li>
                    <a href="index.php" class="nav-link <?= $activePage === 'home' ? 'active' : '' ?>">Home</a>
                </li>
                <li>
                    <a href="explore.php" class="nav-link <?= $activePage === 'explore' ? 'active' : '' ?>">Explore Places</a>
                </li>
                <li>
                    <a href="map.php" class="nav-link <?= $activePage === 'map' ? 'active' : '' ?>">Interactive Map</a>
                </li>
                <li>
                    <a href="stories.php" class="nav-link <?= $activePage === 'stories' ? 'active' : '' ?>">Stories</a>
                </li>
                <li>
                    <a href="submit-story.php" class="nav-link <?= $activePage === 'submit' ? 'active' : '' ?>">Submit Story</a>
                </li>
                <li>
                    <a href="about.php" class="nav-link <?= $activePage === 'about' ? 'active' : '' ?>">About</a>
                </li>
                <?php if ($isAdmin): ?>
                    <li>
                        <a href="admin/index.php" class="nav-link text-red" title="Archivist Control Room">
                            <i class="fa-solid fa-shield-halved"></i> Admin
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Right Side Utility Actions -->
        <div class="nav-actions">
            <!-- Ambient Audio Toggle Pill -->
            <div style="position: relative;">
                <div id="ambient-sound-toggle" class="audio-control-pill" title="Toggle Atmospheric Soundscape (Off by default)">
                    <div class="equalizer-bars">
                        <span class="equalizer-bar"></span>
                        <span class="equalizer-bar"></span>
                        <span class="equalizer-bar"></span>
                    </div>
                    <span class="pill-text">Atmosphere OFF</span>
                    <i class="fa-solid fa-volume-high" style="font-size: 0.7rem; margin-left: 2px;"></i>
                </div>

                <!-- Hover/Context Volume Slider -->
                <div class="volume-slider-wrapper">
                    <label style="font-size: 0.7rem; color: var(--text-muted); display: block; margin-bottom: 0.25rem; text-transform: uppercase;">Volume</label>
                    <input type="range" id="ambient-volume" class="volume-range" min="0" max="1" step="0.05" value="0.4">
                </div>
            </div>

            <!-- Global Search Trigger -->
            <button id="open-search-modal" class="nav-icon-btn" title="Search Archive (Ctrl+K)" aria-label="Search">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>

            <!-- Bookmarked Places Drawer Trigger -->
            <button id="open-favorites-modal" class="nav-icon-btn" title="Saved Places" aria-label="Saved Places">
                <i class="fa-solid fa-bookmark"></i>
                <span class="nav-badge-count favorites-count-badge" style="display: none;">0</span>
            </button>

            <!-- Mobile Menu Toggle Button -->
            <button class="mobile-nav-toggle" aria-label="Toggle navigation menu" aria-expanded="false">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>
</header>

<!-- Global Search Modal -->
<div id="global-search-modal" class="modal-overlay">
    <div class="modal-content">
        <button id="close-search-modal" class="modal-close" aria-label="Close search">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem; font-family: var(--font-serif);">Search the Shadows</h3>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">Explore places, districts, ruins, and legends across Bangladesh</p>
        
        <form action="explore.php" method="GET" style="position: relative;">
            <input type="text" id="modal-search-input" name="search" class="form-control" placeholder="Search by name, district, or keyword..." autocomplete="off" style="padding-left: 2.75rem;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
            <div style="margin-top: 1rem; display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary btn-sm">Search Archive</button>
            </div>
        </form>

        <div style="margin-top: 1.5rem; border-top: 1px solid var(--border-subtle); padding-top: 1rem;">
            <span style="font-size: 0.72rem; text-transform: uppercase; color: var(--accent-red); font-weight: 700; letter-spacing: 0.1em;">Popular Inquiries</span>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem;">
                <a href="explore.php?search=Panam" class="category-pill" style="font-size: 0.72rem;">Panam City</a>
                <a href="explore.php?search=Lalbagh" class="category-pill" style="font-size: 0.72rem;">Lalbagh Fort</a>
                <a href="explore.php?search=Sundarbans" class="category-pill" style="font-size: 0.72rem;">Sundarbans</a>
                <a href="explore.php?search=Mahasthangarh" class="category-pill" style="font-size: 0.72rem;">Mahasthangarh</a>
                <a href="explore.php?category=haunted-legends" class="category-pill" style="font-size: 0.72rem;">Haunted Legends</a>
            </div>
        </div>
    </div>
</div>

<!-- Favorites / Bookmarks Modal -->
<div id="favorites-modal" class="modal-overlay">
    <div class="modal-content">
        <button id="close-favorites-modal" class="modal-close" aria-label="Close bookmarks">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-bookmark" style="color: var(--accent-red); font-size: 1.25rem;"></i>
            <h3 style="font-size: 1.35rem; font-family: var(--font-serif); margin: 0;">Bookmarked Sanctuaries</h3>
        </div>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem;">Places you have marked for future exploration. Stored locally in your browser.</p>

        <div id="favorites-list-container" style="max-height: 360px; overflow-y: auto; margin-bottom: 1.5rem;">
            <!-- Populated via main.js -->
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-subtle); padding-top: 1rem;">
            <a href="explore.php" class="btn btn-outline-red btn-sm">Explore More Places</a>
            <a href="map.php" class="btn btn-primary btn-sm">View on Map <i class="fa-solid fa-map-location-dot"></i></a>
        </div>
    </div>
</div>

