<?php
/**
 * SHADOWS OF BANGLA — About the Archive
 * Philosophy, Research Standards, Field Methodologies & Content Disclaimers
 */

define('APP_INIT', true);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'About the Archive';
$activePage = 'about';

require_once __DIR__ . '/includes/header.php';
?>

<!-- About Hero Header -->
<section style="padding: 8.5rem 0 4rem; background: linear-gradient(180deg, #050608 0%, #11131a 100%); border-bottom: 1px solid var(--border-subtle); position: relative;">
    <div class="container text-center" style="max-width: 880px;">
        <span class="section-subtitle">Archival Ethos</span>
        <h1 class="section-title" style="font-size: clamp(2.2rem, 4vw, 3.25rem); margin-bottom: 1.25rem;">Preserving the Forgotten Delta</h1>
        <p class="section-description" style="font-size: 1.2rem; line-height: 1.8; color: var(--text-main); font-style: italic;">
            &ldquo;Bangladesh is a land of ancient architecture, forgotten places, fascinating folklore, and unexplained stories. Shadows of Bangla is a digital journey through these mysteries. We explore the places, preserve their stories, and separate documented history from local legends.&rdquo;
        </p>
    </div>
</section>

<!-- Core Pillars Section -->
<section class="section-padding">
    <div class="container" style="max-width: 960px;">
        <div class="content-box">
            <h2><i class="fa-solid fa-compass text-red"></i> Our Mission</h2>
            <div class="prose">
                <p>
                    Across the floodplains, river deltas, and hill tracts of Bangladesh lie monuments that have quietly watched empires rise and collapse: 2,500-year-old fortified trade emporiums in Narsingdi, silent colonnaded merchant avenues in Sonargaon, half-submerged timber wells along the Rakhine coastline, and Mughal water bastions under Old Dhaka.
                </p>
                <p>
                    With rapid urbanization and changing cultural landscapes, hundreds of historical sites and centuries of oral memory face permanent extinction. <strong>SHADOWS OF BANGLA</strong> was founded to build a permanent, dignified, and scientifically honest digital repository for these places.
                </p>
            </div>
        </div>

        <div class="content-box">
            <h2><i class="fa-solid fa-layer-group text-red"></i> What We Document</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-subtle);">
                    <div style="font-size: 1.5rem; color: var(--accent-red); margin-bottom: 0.75rem;"><i class="fa-solid fa-house-crack"></i></div>
                    <h3 style="font-size: 1.15rem; margin-bottom: 0.5rem;">Abandoned Settlements</h3>
                    <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.6;">
                        Townships and zamindar estates deserted following geopolitical fractures, wars, or natural calamities, preserved in brick silence.
                    </p>
                </div>

                <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-subtle);">
                    <div style="font-size: 1.5rem; color: var(--accent-red); margin-bottom: 0.75rem;"><i class="fa-solid fa-landmark"></i></div>
                    <h3 style="font-size: 1.15rem; margin-bottom: 0.5rem;">Archaeological Riddles</h3>
                    <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.6;">
                        Ancient fortified citadels, subterranean tunnel systems, sacrificial megaliths, and lost trade capitals spanning over 25 centuries.
                    </p>
                </div>

                <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-subtle);">
                    <div style="font-size: 1.5rem; color: var(--accent-red); margin-bottom: 0.75rem;"><i class="fa-solid fa-scroll"></i></div>
                    <h3 style="font-size: 1.15rem; margin-bottom: 0.5rem;">Delta Folklore & Myths</h3>
                    <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.6;">
                        Generational oral traditions: marshland ghost lights (Aleya), forest deities (Bonbibi), phantom cavalry, and water sacrifice ballads.
                    </p>
                </div>
            </div>
        </div>

        <div class="content-box">
            <h2><i class="fa-solid fa-microscope text-red"></i> Our Research Methodology</h2>
            <div class="prose">
                <p>
                    Unlike sensationalist horror blogs, we hold ourselves to rigorous archival standards. Every dossier published on this platform adheres to three strict epistemic principles:
                </p>
                <div style="display: flex; flex-direction: column; gap: 1rem; margin: 1.5rem 0;">
                    <div style="background: var(--bg-secondary); border-left: 4px solid #10b981; padding: 1.25rem; border-radius: 0 6px 6px 0;">
                        <h4 style="color: #34d399; font-size: 1.05rem; margin-bottom: 0.25rem;"><i class="fa-solid fa-shield-halved"></i> VERIFIED HISTORY</h4>
                        <p style="font-size: 0.9rem; margin: 0;">
                            Facts corroborated through state archaeological surveys, peer-reviewed historiography, colonial gazetteers (e.g. W.W. Hunter, Francis Buchanan), and archaeological inscriptions.
                        </p>
                    </div>

                    <div style="background: var(--bg-secondary); border-left: 4px solid #f59e0b; padding: 1.25rem; border-radius: 0 6px 6px 0;">
                        <h4 style="color: #fbbf24; font-size: 1.05rem; margin-bottom: 0.25rem;"><i class="fa-solid fa-scroll"></i> LOCAL FOLKLORE</h4>
                        <p style="font-size: 0.9rem; margin: 0;">
                            Oral accounts, mythological narratives, and traditional ballads recorded from village elders, fishermen, and indigenous communities. Labelled clearly as cultural heritage, not empirical science.
                        </p>
                    </div>

                    <div style="background: var(--bg-secondary); border-left: 4px solid #ef4444; padding: 1.25rem; border-radius: 0 6px 6px 0;">
                        <h4 style="color: #f87171; font-size: 1.05rem; margin-bottom: 0.25rem;"><i class="fa-solid fa-eye-slash"></i> UNVERIFIED LEGEND</h4>
                        <p style="font-size: 0.9rem; margin: 0;">
                            Anecdotal supernatural sightings, modern urban legends, and unverifiable rumors. Explicitly flagged as unverified claims to prevent misleading the public.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-box" style="border-color: rgba(158, 27, 50, 0.4);">
            <h2><i class="fa-solid fa-triangle-exclamation text-red"></i> Content & Ethical Disclaimer</h2>
            <div class="prose" style="font-size: 0.95rem;">
                <p>
                    <strong>1. No Supernatural Fact Claims:</strong> We never present alleged paranormal encounters, ghosts, or curses as scientifically established facts. They are analyzed as cultural phenomena and folklore.
                </p>
                <p>
                    <strong>2. Heritage Protection:</strong> We vehemently discourage trespassing, vandalism, metal detecting, illegal digging, or defacing historical structures. Many sites listed are protected under the Antiquities Act of Bangladesh (1968). Always respect archaeological staff and local community customs.
                </p>
                <p>
                    <strong>3. Geographic Truth:</strong> All geographical coordinates and district names published on our interactive map are rigorously verified. We do not invent fictional coordinates.
                </p>
            </div>
        </div>

        <div style="text-align: center; margin-top: 3rem;">
            <a href="explore.php" class="btn btn-primary">
                Begin Exploring the Archive <i class="fa-solid fa-arrow-right"></i>
            </a>
            <a href="submit-story.php" class="btn btn-secondary" style="margin-left: 0.75rem;">
                Submit Local Story
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

