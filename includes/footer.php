<?php
/**
 * SHADOWS OF BANGLA — Global Footer Template
 */

if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}
?>
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand & Mission -->
                <div class="footer-brand">
                    <div class="brand-logo" style="margin-bottom: 0.5rem;">
                        <div class="logo-symbol">
                            <i class="fa-solid fa-ghost"></i>
                        </div>
                        <div class="brand-text">
                            <span class="brand-name">SHADOWS OF BANGLA</span>
                            <span class="brand-tagline-nav">Beyond the Known.</span>
                        </div>
                    </div>
                    <p>
                        A dedicated digital archive documenting the mysterious places, abandoned architectures, and oral folklore of Bangladesh. We research with reverence, separating documented archaeological history from local folk legends.
                    </p>
                    <div style="display: flex; gap: 0.75rem;">
                        <a href="https://github.com" target="_blank" rel="noopener" class="nav-icon-btn" title="GitHub Source" aria-label="GitHub">
                            <i class="fa-brands fa-github"></i>
                        </a>
                        <a href="map.php" class="nav-icon-btn" title="Interactive Map" aria-label="Interactive Map">
                            <i class="fa-solid fa-map-location-dot"></i>
                        </a>
                        <a href="submit-story.php" class="nav-icon-btn" title="Submit Local Lore" aria-label="Submit Story">
                            <i class="fa-solid fa-feather"></i>
                        </a>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div>
                    <h4 class="footer-title">Archive Index</h4>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; color: var(--accent-red);"></i> Home Portal</a></li>
                        <li><a href="explore.php"><i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; color: var(--accent-red);"></i> Explore Locations</a></li>
                        <li><a href="map.php"><i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; color: var(--accent-red);"></i> Interactive Map</a></li>
                        <li><a href="stories.php"><i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; color: var(--accent-red);"></i> Mystery Stories</a></li>
                        <li><a href="submit-story.php"><i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; color: var(--accent-red);"></i> Submit a Legend</a></li>
                        <li><a href="about.php"><i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; color: var(--accent-red);"></i> Research Mission</a></li>
                    </ul>
                </div>

                <!-- Explore by Division -->
                <div>
                    <h4 class="footer-title">Divisions</h4>
                    <ul class="footer-links">
                        <li><a href="explore.php?division=Dhaka">Dhaka Division</a></li>
                        <li><a href="explore.php?division=Chattogram">Chattogram Division</a></li>
                        <li><a href="explore.php?division=Rajshahi">Rajshahi Division</a></li>
                        <li><a href="explore.php?division=Khulna">Khulna Division</a></li>
                        <li><a href="explore.php?division=Sylhet">Sylhet Division</a></li>
                        <li><a href="explore.php?division=Barishal">Barishal Division</a></li>
                        <li><a href="explore.php?division=Rangpur">Rangpur Division</a></li>
                        <li><a href="explore.php?division=Mymensingh">Mymensingh Division</a></li>
                    </ul>
                </div>

                <!-- Research & Verification Disclaimer -->
                <div>
                    <h4 class="footer-title">Content Standards</h4>
                    <p style="font-size: 0.85rem; line-height: 1.6; margin-bottom: 1rem;">
                        <strong>Historical Integrity:</strong> All historical narratives are corroborated through official archives and academic publications. Spectral claims are strictly presented as cultural folklore or unverified oral tradition, never as scientific fact.
                    </p>
                    <div style="background: rgba(23, 25, 34, 0.8); border: 1px solid var(--border-subtle); padding: 0.75rem; border-radius: 6px; font-size: 0.75rem;">
                        <span style="color: var(--accent-red); font-weight: 700; text-transform: uppercase;">Admin Access:</span> 
                        <a href="admin/login.php" style="color: #fff; text-decoration: underline; margin-left: 0.35rem;">Archivist Login</a>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div>
                    &copy; <?= date('Y') ?> <strong>SHADOWS OF BANGLA</strong>. Dedicated to the cultural, architectural, and folklore preservation of Bangladesh.
                </div>
                <div style="display: flex; gap: 1.5rem; align-items: center;">
                    <span>Map Data &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener" style="color: var(--fog-grey); text-decoration: underline;">OpenStreetMap</a></span>
                    <a href="#" style="color: var(--accent-red); font-size: 0.95rem;" title="Return to Top">
                        <i class="fa-solid fa-arrow-up"></i> Top
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Leaflet.js Library for Maps -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- GSAP for Smooth Transitions -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

    <!-- Project Client Scripts -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/map.js"></script>
    <script src="assets/js/interactions.js"></script>
</body>
</html>

