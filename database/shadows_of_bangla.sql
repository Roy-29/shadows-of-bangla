-- ===================================================================
-- SHADOWS OF BANGLA — Database Schema and Initial Seed Data
-- Database Name: shadows_of_bangla
-- Compatible with MySQL 5.7+, MySQL 8.0+, MariaDB 10.3+ (XAMPP / phpMyAdmin)
-- ===================================================================

CREATE DATABASE IF NOT EXISTS `shadows_of_bangla` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `shadows_of_bangla`;

-- --------------------------------------------------------
-- Table structure for table `admins`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `place_images`;
DROP TABLE IF EXISTS `story_submissions`;
DROP TABLE IF EXISTS `stories`;
DROP TABLE IF EXISTS `places`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `admins`;

CREATE TABLE `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) DEFAULT NULL,
  `role` ENUM('admin', 'editor') NOT NULL DEFAULT 'admin',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `categories`
-- --------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `icon` VARCHAR(50) NOT NULL DEFAULT 'fa-ghost',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `places`
-- --------------------------------------------------------
CREATE TABLE `places` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(160) NOT NULL UNIQUE,
  `summary` VARCHAR(350) NOT NULL,
  `description` TEXT NOT NULL,
  `division` VARCHAR(50) NOT NULL,
  `district` VARCHAR(50) NOT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `latitude` DECIMAL(10, 7) NOT NULL,
  `longitude` DECIMAL(10, 7) NOT NULL,
  `cover_image` VARCHAR(255) NOT NULL,
  `historical_background` LONGTEXT NOT NULL,
  `local_legends` LONGTEXT NOT NULL,
  `paranormal_claims` LONGTEXT DEFAULT NULL,
  `why_mysterious` LONGTEXT DEFAULT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `verification_status` ENUM('VERIFIED HISTORY', 'LOCAL FOLKLORE', 'UNVERIFIED LEGEND') NOT NULL DEFAULT 'LOCAL FOLKLORE',
  `sources` TEXT DEFAULT NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `views_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_division` (`division`),
  INDEX `idx_district` (`district`),
  INDEX `idx_category` (`category_id`),
  INDEX `idx_featured` (`is_featured`),
  CONSTRAINT `fk_places_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `place_images`
-- --------------------------------------------------------
CREATE TABLE `place_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `place_id` INT UNSIGNED NOT NULL,
  `image_url` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) DEFAULT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_place_image_place` (`place_id`),
  CONSTRAINT `fk_place_images_place` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `stories`
-- --------------------------------------------------------
CREATE TABLE `stories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL UNIQUE,
  `excerpt` VARCHAR(350) NOT NULL,
  `content` LONGTEXT NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `cover_image` VARCHAR(255) NOT NULL,
  `author` VARCHAR(100) NOT NULL DEFAULT 'Shadows Archive',
  `reading_time` INT UNSIGNED NOT NULL DEFAULT 5,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('published', 'draft') NOT NULL DEFAULT 'published',
  `views_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_story_category` (`category_id`),
  INDEX `idx_story_status` (`status`),
  INDEX `idx_story_featured` (`is_featured`),
  CONSTRAINT `fk_stories_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `story_submissions`
-- --------------------------------------------------------
CREATE TABLE `story_submissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `submitter_name` VARCHAR(100) NOT NULL DEFAULT 'Anonymous Explorer',
  `email` VARCHAR(120) DEFAULT NULL,
  `story_title` VARCHAR(200) NOT NULL,
  `district` VARCHAR(50) NOT NULL,
  `location_name` VARCHAR(150) NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `story_content` LONGTEXT NOT NULL,
  `image_url` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `admin_notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_submission_status` (`status`),
  INDEX `idx_submission_category` (`category_id`),
  CONSTRAINT `fk_submissions_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- SEED DATA
-- ===================================================================

-- Initial Administrator
-- Username: admin
-- Email: admin@shadowsofbangla.com
-- Default Password: AdminShadows#2026
INSERT INTO `admins` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`) VALUES
(1, 'admin', 'admin@shadowsofbangla.com', '029f2e34f30483e981271651a1c14cfdf5a6696ccda4afc13a78b5ea0a758911', 'Lead Archivist', 'admin');

-- Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`) VALUES
(1, 'Haunted Legends', 'haunted-legends', 'Sites marked by tragic folklore, oral ghost tales, and longstanding spectral beliefs.', 'fa-ghost'),
(2, 'Abandoned Places', 'abandoned-places', 'Deserted townships, crumbling zamindar mansions, and forgotten settlements left to nature.', 'fa-house-crack'),
(3, 'Historical Mysteries', 'historical-mysteries', 'Ancient civilizations, lost forts, enigmatic trade ports, and archaeological puzzles.', 'fa-landmark'),
(4, 'Ghost Stories', 'ghost-stories', 'Eerie accounts passed down generations across riverbanks, marshes, and rural hamlets.', 'fa-skull'),
(5, 'Mysterious Buildings', 'mysterious-buildings', 'Architectural enigmas, forgotten clocktowers, hidden tunnels, and labyrinthine chambers.', 'fa-monument'),
(6, 'Local Folklore', 'local-folklore', 'Myths of river spirits, forest guardians, shape-shifters, and village folk traditions.', 'fa-book-skull');

-- Places across all 8 Divisions of Bangladesh with authentic history and real coordinates
INSERT INTO `places` (`id`, `name`, `slug`, `summary`, `description`, `division`, `district`, `address`, `latitude`, `longitude`, `cover_image`, `historical_background`, `local_legends`, `paranormal_claims`, `why_mysterious`, `category_id`, `verification_status`, `sources`, `is_featured`, `views_count`) VALUES
(1, 
 'Panam City (Panam Nagar)', 
 'panam-city-sonargaon', 
 'A silent 19th-century merchant township of 52 colonnaded brick mansions, abruptly abandoned after historic conflicts.', 
 'Panam City in Sonargaon stands as one of the most hauntingly preserved abandoned settlements in South Asia. A single 600-meter paved avenue is flanked by 52 derelict brick mansions blending Mughal, Classical Greek, and British colonial architecture. Walking through its overgrown lanes at twilight feels like stepping into a frozen, mournful past.', 
 'Dhaka', 
 'Narayanganj', 
 'Panam Nagar, Sonargaon, Narayanganj 1440', 
 23.6493000, 
 90.6017000, 
 'assets/images/panam-city.svg', 
 'Sonargaon was the historic medieval capital of Bengal under Isa Khan. During the late 19th and early 20th centuries, wealthy Hindu textile merchants (Tanti community) constructed Panam Nagar as a protected enclave. Following the communal riots of 1964 and the 1965 India-Pakistan war, the merchants fled suddenly, leaving their sprawling estates, secret vaults, and furnishings behind. Under the Vested Property Act, the buildings fell into state custody and gradual decay.', 
 'Local villagers living near the moat speak of nocturnal echoes along the central avenue—the sound of carriage wheels rolling on brickwork, faint clinking of gold coins, and hurried footsteps on empty verandahs. Traditional elders believe the spirits of merchants who died abroad returned to safeguard their hidden treasures.', 
 'Night guards and late visitors have reported cold drafts near Building No. 24, sudden drops in ambient temperature, and the faint scent of sandalwood burning in unoccupied rooms.', 
 'The eerie completeness of the town: 52 intact yet rotting mansions along one silent corridor, surrounded by water canals, completely deserted by their original builders.', 
 2, 
 'VERIFIED HISTORY', 
 'Department of Archaeology Bangladesh; Banglapedia - Sonargaon; World Monuments Fund Watch List (2006).', 
 1, 
 1420),

(2, 
 'Lalbagh Fort & The Unfinished Maze', 
 'lalbagh-fort-dhaka', 
 'A 17th-century Mughal river fortress shrouding an unfinished tomb and subterranean secret tunnels beneath Old Dhaka.', 
 'Lalbagh Fort (Fort Aurangabad) looms over the historic Buriganga riverbank. Commenced in 1678 by Prince Muhammad Azam, third son of Emperor Aurangzeb, its construction was halted forever following the tragic death of his beloved betrothed, Iran Dukht (Pari Bibi). Beneath its imposing bastions lies a notorious labyrinth of sealed subterranean passages.', 
 'Dhaka', 
 'Dhaka', 
 'Lalbagh, Lalbagh Road, Old Dhaka 1211', 
 23.7188000, 
 90.3882000, 
 'assets/images/lalbagh-fort.svg', 
 'Construction began under Prince Muhammad Azam and was continued by Shaista Khan. When Shaista Khan\'s daughter Pari Bibi suddenly died within the fortress walls, her grief-stricken father deemed the fort cursed and suspended construction. Pari Bibi was entombed in an octagonal chamber of Rajmahal black basalt and white marble. Secret tunnels connecting the fort to the Buriganga river and Zinzira Palace were carved for military escape.', 
 'During the British colonial era, colonial officers sent sepoy detachments and hunting dogs into the southern tunnel maze to map subterranean Dhaka. Rumor holds that the dogs and men never returned, or emerged raving mad, prompting British engineers to brick up the tunnel entrances permanently.', 
 'Careful local lore recounts a spectral maiden dressed in Mughal silk wandering the southern parapet on full moon nights, gazing toward the lost river channels. Visitors report disorienting auditory illusions near the southern water gate.', 
 'The sealed underground tunnel network whose exact depth and endpoints remain officially unmapped, combined with the abrupt superstitious cessation of imperial construction.', 
 1, 
 'VERIFIED HISTORY', 
 'Syed Muhammed Taifoor, Forgotten Dhaka (1952); Dani, A. H., Muslim Architecture in Bengal (1961); National Museum of Bangladesh Archives.', 
 1, 
 2150),

(3, 
 'Mahasthangarh & Parshuram\'s Palace', 
 'mahasthangarh-ancient-pundranagara', 
 'The 2,500-year-old fortified ruins of Pundranagara, bearing legends of the fish-riding saint and the Well of Life.', 
 'Mahasthangarh in Bogura is the earliest urban archaeological site identified in Bangladesh, dating back to at least the 3rd century BCE (Maurya Period). Vast red brick ramparts overlook the sacred Karatoya River, concealing strata of Buddhist, Hindu, and Muslim civilizations. It is steeped in mythological warfare between King Parshuram and Sufi saint Shah Sultan Balkhi.', 
 'Rajshahi', 
 'Bogura', 
 'Mahasthangarh, Shibganj Upazila, Bogura 5800', 
 24.9608000, 
 89.3456000, 
 'assets/images/mahasthangarh.svg', 
 'Identified with ancient Pundranagara from a Brahmi limestone inscription found in 1931. The fortified city served as a provincial capital under the Mauryas, Guptas, Pala Empire, and Sena dynasty before falling in the 13th century. The Karatoya river was once a roaring imperial waterway that provided maritime defense.', 
 'Folklore states that King Parshuram possessed the \'Jiat Kunda\' (Well of Life), whose water instantly revived fallen soldiers during battle. Sultan Balkhi Mahisawar (the Saint on a Fish) learned the secret, had a kite drop beef into the well, neutralizing its sacred properties, and defeated the king. Parshuram\'s sister, Princess Shiladevi, drowned herself in the Karatoya to protect her honor.', 
 'Local farmers claim the soil around Gobinda Bhita exudes an unexplained magnetic chill at night, and unbaptized travelers report hearing war trumpets and clashing blades when fog envelopes the Karatoya riverbed.', 
 'Centuries of unexcavated ruins buried dozens of feet underground, and the haunting oral memory of an ancient city that survived for two millennia before vanishing into mound ruins.', 
 3, 
 'VERIFIED HISTORY', 
 'Brahmi Inscription of Mahasthan (Epigraphia Indica); Ahmed, Nazimuddin, Mahasthan: A Survey of Ancient Citadel (1975); Banglapedia.', 
 1, 
 980),

(4, 
 'Puthia Rajbari Complex & The Sunken Shrines', 
 'puthia-rajbari-complex', 
 'A sprawling 19th-century royal palace surrounded by fourteen ornate terracotta temples and midnight sitar lore.', 
 'Puthia Rajbari in Rajshahi features one of the most stunning clusters of historic Hindu terracotta temples in South Asia. Constructed by the philanthropic Queen Hemanta Kumari Devi in 1895 in honor of her mother-in-law Maharani Sarat Sundari Devi, the palace features majestic Corinthian columns, grand banquet halls, and deep freshwater dighis (lakes) whose depths hold architectural secrets.', 
 'Rajshahi', 
 'Rajshahi', 
 'Puthia Upazila, Rajshahi 6260', 
 24.3644000, 
 88.8358000, 
 'assets/images/puthia-palace.svg', 
 'The Puthia Royal Family was founded in the late 16th century by Nilambar, given the title of Raja by Mughal Emperor Jahangir. The present Indo-Saracenic palace was rebuilt following the catastrophic 1897 earthquake. The estate comprises the Pancha Ratna Govinda Temple, the grand Shiva Temple, and the multi-tiered Dol Mandir.', 
 'Local tradition claims that secret submerged staircases connect the base of the Shiva Temple directly to the dark bed of the Shiv Sagar lake. Royal lore maintains that the deeply spiritual Maharani Sarat Sundari Devi engaged in nocturnal meditations that left behind a permanent ethereal aura in the temple corridors.', 
 'Caretakers and village night watchmen report hearing delicate sitar melodies floating from the second-floor royal balcony when the palace gates are locked, accompanied by footsteps along the hollow wooden floors.', 
 'Submerged temple vaults beneath artificial lakes that have never been drained or sonar-mapped, combined with the dramatic sudden fall of one of Bengal\'s wealthiest zamindar estates.', 
 5, 
 'LOCAL FOLKLORE', 
 'Hasan, Perween, Sultans and Mosques (2007); District Gazetteers of Rajshahi; Bangladesh Heritage Foundation.', 
 0, 
 750),

(5, 
 'Wari-Bateshwar & The Submerged Metropolis', 
 'wari-bateshwar-ancient-port', 
 'A 2,500-year-old forgotten fortified river metropolis trading with ancient Rome and Southeast Asia.', 
 'Hidden beneath the quiet villages of Wari and Bateshwar in Narsingdi lies an immense fortified urban centre dating back to 450 BCE. Rediscovered primarily through the tireless decades-long work of local schoolteacher Hanif Pathan, the site proved that ancient Bengal was deeply connected to trans-oceanic Indian Ocean and Mediterranean maritime networks.', 
 'Dhaka', 
 'Narsingdi', 
 'Belabo Upazila, Narsingdi 1640', 
 24.0933000, 
 90.8350000, 
 'assets/images/wari-bateshwar.svg', 
 'Excavations by Prof. Sufi Mustafizur Rahman uncovered pit-dwellings, rouletted pottery, Northern Black Polished Ware, semi-precious stone beads, and over 4,000 punch-marked silver coins. The city sat upon the Old Brahmaputra river channel and served as an emporium identified by scholars with Claudius Ptolemy\'s \'Sounagoura\'.', 
 'For centuries before archaeological validation, villagers told folk tales of \'Asur Dighi\' (The Giant\'s Moat) and warned children never to till the upper mounds after sundown, believing that an underground race of guardians punished those who disturbed their buried terracotta cities.', 
 'While the site is purely archaeological and free of confirmed supernatural claims, locals note unusual electrostatic disruptions during overcast weather near the mud ramparts.', 
 'The sheer scale of a vast international trade city that flourished for centuries and then completely vanished from all known dynastic written records of early Bengal.', 
 3, 
 'VERIFIED HISTORY', 
 'Rahman, Sufi Mustafizur et al., Wari-Bateshwar: Archaeological Heritage of Bangladesh (2009); Journal of Bengal Art.', 
 0, 
 620),

(6, 
 'Bhawal Rajbari & The Sannyasi of Joydebpur', 
 'bhawal-rajbari-sannyasi-court', 
 'The sprawling royal court of Gazipur, centre of the 20th century\'s greatest royal resurrection mystery.', 
 'Bhawal Estate was the second-largest zamindari in eastern Bengal. Its dramatic Victorian palace in Joydebpur became the center stage of the internationally famous Bhawal Case. In 1909, Prince Ramendra Narayan Roy supposedly died and was cremated during a monsoon night in Darjeeling. Twelve years later, an ash-covered wandering sadhu arrived at the palace gates claiming to be the returned prince.', 
 'Dhaka', 
 'Gazipur', 
 'Joydebpur Sadar, Gazipur 1700', 
 24.0016000, 
 90.4285000, 
 'assets/images/bhawal-palace.svg', 
 'Prince Ramendra Narayan Roy was pronounced dead on May 8, 1909. In 1921, a sannyasi arrived in Dhaka whose physical marks, speech, and intimate memories matched the deceased prince. The resulting Privy Council trial lasted over two decades, culminating in 1946 with London\'s highest imperial court ruling that the sannyasi was indeed the prince who had escaped his cremation during a torrential hailstorm.', 
 'Rumors swirled that the prince had been poisoned with arsenic by conspirators seeking control of the estate. Following his sudden real death just days after winning the final court verdict, the palace fell into disarray and was believed to be burdened by royal betrayal.', 
 'Locals claim that late at night, the eastern court room echoes with the sound of legal papers shuffling and gavel strikes, alongside cold chills near the ruined family temple.', 
 'A bizarre intersection of historical fact, medical anomaly, and legal drama where a dead man legally came back to life and reclaimed his kingdom, leaving behind a silent estate.', 
 3, 
 'VERIFIED HISTORY', 
 'Chatterjee, Partha, A Princely Impostor? The Strange and Universal History of the Kumar of Bhawal (2002); Privy Council Appeal No. 42 of 1943.', 
 0, 
 890),

(7, 
 'Foy\'s Lake & Batali Hill', 
 'foys-lake-batali-hill-chattogram', 
 'A century-old man-made mountain reservoir and the highest hill of Chattogram, home to the Lady in White legend.', 
 'Surrounded by undulating green ridges in the heart of Chattogram, Foy\'s Lake was engineered in 1924 by the Assam-Bengal Railway. Overlooking the water is Batali Hill, the highest natural elevation in the city. The dense hill tracks, WW2 artillery bunkers, and secluded reservoir waters have been the epicenter of coastal folklore for over eight decades.', 
 'Chattogram', 
 'Chattogram', 
 'Pahartali - Khulshi, Chattogram 4202', 
 22.3667000, 
 91.8028000, 
 'assets/images/foys-lake.svg', 
 'Foy\'s Lake was conceived and constructed under railway engineer Mr. Foy to supply water to railway colony steam engine sheds. Batali Hill was utilized during World War II by the British Royal Air Force as a key watchtower and anti-aircraft post against Japanese incursions from Burma.', 
 'A widespread urban legend speaks of \'Bada Meye\' (The Lady in White), a spectral figure described by nocturnal hikers and late-shift railway workers wandering the misty slopes above the lake. Stories suggest she was a young woman who drowned in the deep reservoir trenches in the 1940s.', 
 'Hikers along Tiger Pass and Batali peak at dusk report sudden disorientation, electronic compass drift near the abandoned military trenches, and phantom boat ripples on calm lake water.', 
 'The combination of secluded hilly topography, dark World War II military bunkers hidden under thick canopy, and unexplained drownings across the deep reservoir fingers.', 
 1, 
 'LOCAL FOLKLORE', 
 'Chattogram City Corporation Archives; Eastern Bengal and Assam District Gazetteers: Chittagong (1908).', 
 0, 
 1100),

(8, 
 'Jaintia Megalithic Stones of Jaintiapur', 
 'jaintia-megalithic-monuments-sylhet', 
 'Ancient sacrificial stone slabs, dolmens, and menhirs of the matrilineal Khasi-Jaintia Kingdom.', 
 'At the base of the Meghalaya hill ridges in Jaintiapur, Sylhet, lies an extraordinary cluster of megalithic stone monuments. Gigantic sandstone slabs resting upon stone pillars (dolmens) and towering upright stones (menhirs) mark the seat of the once sovereign Jaintia Kingdom. These ancient monuments bear witness to sacred rituals, treaties, and royal assemblies.', 
 'Sylhet', 
 'Sylhet', 
 'Jaintiapur Bazar, Jaintiapur Upazila, Sylhet 3150', 
 25.1328000, 
 92.1283000, 
 'assets/images/jaintia-stones.svg', 
 'The Jaintia Kingdom was an independent matrilineal state that flourished until British annexation in 1835. The stone megaliths were erected between the 15th and 18th centuries following ancient Khasi megalithic traditions to honor deceased royal ancestors and commemorate major military victories against neighboring Koch and Mughal armies.', 
 'Tribal oral history speaks of the stones as living spiritual pillars. It was believed that the largest flat stone (the sacrificial throne) possessed the ability to test truth: anyone swearing a false oath while touching the stone would be struck by thunderous mountain spirits.', 
 'Local villagers tell travelers that on rainy nights when lightning strikes the hills of Meghalaya, the megaliths emit a low, vibrating resonance that causes nearby cattle to panic.', 
 'The mysterious engineering behind transporting and hoisting multi-ton monolithic sandstone boulders across rugged river valleys centuries ago without modern machinery.', 
 6, 
 'VERIFIED HISTORY', 
 'Gait, Edward, A History of Assam (1906); Gurdon, P. R. T., The Khasis (1914); Directorate of Archaeology Sylhet.', 
 0, 
 540),

(9, 
 'The Ancient Wells of Kuakata & Rakhine Coast', 
 'kuakata-ancient-rakhine-wells', 
 'Deep coastal freshwater wells dug by 18th-century refugees on the edge of the turbulent Bay of Bengal.', 
 'Kuakata in Patuakhali is world-renowned for its rare panoramic sunrise and sunset over the Bay of Bengal. But behind the shoreline sands lies an older, quieter mystery: ancient wooden-framed freshwater wells (Kua) built by Rakhine settlers who fled Burmese royal persecution in 1784. One secluded ancient well, half-submerged by shifting ocean tides, is the origin of eerie coastal maritime legends.', 
 'Barishal', 
 'Patuakhali', 
 'Kuakata Parjatan Area, Kalapara, Patuakhali 8650', 
 21.8174000, 
 90.1189000, 
 'assets/images/kuakata-well.svg', 
 'Following King Bodawpaya\'s brutal conquest of the Kingdom of Arakan in 1784, thousands of Rakhine refugees crossed the sea in boats and settled along the uninhabited tidal islands of Patuakhali. To secure drinking water amidst the saline mangrove sands, they excavated deep wells protected by massive woven timber rings.', 
 'Local fishermen speak of the \'Shedir Kua\' (Ghost Well), an ancient well lost in the sea marshes. Legend holds that during stormy new moon nights, spectral chanting resembling ancient Arakanese Buddhist prayers rises above the roar of the surf.', 
 'Night fishermen report seeing phantom lanterns hovering over the wave line near the old well site, which vanish instantly when approached.', 
 'A historic community whose survival depended on sacred water carved out of an unforgiving salt desert, surrounded by shifting coastal sands that periodically bury and expose old relics.', 
 6, 
 'LOCAL FOLKLORE', 
 'Banglapedia - Rakhines in Bangladesh; Patuakhali District Gazetteer; Cultural Survey of Bangladesh.', 
 0, 
 670),

(10, 
 'Ramsagar Dighi & The Royal Sacrifice', 
 'ramsagar-dighi-dinajpur', 
 'The largest man-made reservoir in Bangladesh, built during famine and haunted by a prince\'s water sacrifice.', 
 'Spanning over 437,000 square meters in Dinajpur, Ramsagar is a colossal historic reservoir surrounded by quiet earthen embankments and mahogany groves. Commissioned between 1750 and 1755 by Raja Ramnath of Dinajpur, the lake was excavated to save millions from a devastating regional drought. Its mirror-like waters conceal a tragic human story etched into northern folklore.', 
 'Rangpur', 
 'Dinajpur', 
 'Tejpur, Dinajpur Sadar, Dinajpur 5200', 
 25.5539000, 
 88.6292000, 
 'assets/images/ramsagar.svg', 
 'Raja Ramnath spent approximately 30,000 rupees of the period to dig the massive tank during the great famine of 1750–1755, employing thousands of starving peasants to provide them with food wages. The reservoir measures 1,079 meters in length and continues to sustain local ecology today.', 
 'Folklore states that after months of agonizing excavation, the deep pit remained bone-dry and cracked. Desperate, the Raja received a dream vision demanding the sacrifice of his only son, Prince Ram. When the prince dressed in white stepped into the pit\'s center, water miraculously surged from the depths so violently that he was submerged before he could climb the embankments.', 
 'Visitors walking along the southern ghats during late winter twilights claim to see the shimmering silhouette of a youth standing just inches below the water surface, disappearing when ripples break.', 
 'The eerie silence of the northern plains, the massive scale of an eighteenth-century hand-dug reservoir, and the unbroken chain of tragic oral folklore passed down for 270 years.', 
 1, 
 'LOCAL FOLKLORE', 
 'Martin, Montgomery, The History, Antiquities, Topography, and Statistics of Eastern India (1838); Dinajpur District Gazetteer.', 
 0, 
 830),

(11, 
 'Sundarbans Aleya & The Bonbibi Lore', 
 'sundarbans-aleya-ghost-lights', 
 'The deepest mangrove wilderness on Earth, where mysterious ghost lights and forest deities guard the tiger realm.', 
 'The Sundarbans delta, spanning thousands of square kilometers of tidal waterways and mangrove forests, is a UNESCO World Heritage site and the sanctuary of the Bengal Tiger. For centuries, woodcutters (bawalis), honey collectors (mouals), and fishermen have navigated these dangerous channels under the spiritual protection of Bonbibi, confronting mysterious atmospheric phenomena and unexplained disappearances.', 
 'Khulna', 
 'Bagerhat', 
 'Sundarbans Biosphere Reserve, Sharankhola, Bagerhat 9320', 
 21.8544000, 
 89.7828000, 
 'assets/images/sundarbans.svg', 
 'Settlement attempts in the lower delta date back to the Chandraketugarh and Sena eras, evidenced by isolated brick temple ruins found deep within the mangrove wilderness. The harsh ecology forged a syncretic folk culture where both Muslim and Hindu forest dwellers pray together to forest guardian Bonbibi and avoid uttering certain words in the jungle.', 
 'Folk beliefs centre around \'Dakshin Rai\', a legendary shapeshifting demon king of tigers who preys on those who enter the forest with greed. The folklore also chronicles \'Aleya\'—unexplained ghostly glowing lights that hover above the marshy water at night, rumored to bewilder boatmen and lure them into quicksand or deep river currents.', 
 'Scientifically, Aleya lights are attributed to methane and phosphine bioluminescent gas ignition from decomposing organic matter in mangrove mud. However, local boatmen steadfastly avoid moving toward hovering fireballs, believing they embody souls of perished honey-hunters.', 
 'One of the last truly untamed frontiers where nature, bioluminescence, and oral mythology blur the line between scientific reality and primal human fear.', 
 6, 
 'LOCAL FOLKLORE', 
 'Eaton, Richard M., The Rise of Islam and the Bengal Frontier (1993); Mitra, A., The Tribes and Castes of Bengal; WWF Forest Survey.', 
 1, 
 1890),

(12, 
 'Baliati Palace & The Inner Sanctum', 
 'baliati-palace-manikganj', 
 'A colossal 19th-century zamindar estate of 200 rooms, towering Greco-Roman pillars, and hidden family courtyards.', 
 'Located in Saturia, Manikganj, Baliati Palace is one of the grandest colonial zamindar mansions in Bangladesh. Spanning over 5.88 acres, the complex consists of seven grand detached palaces, over 200 chambers, soaring neoclassical Greco-Roman colonnades, and four massive boundary walls enclosing the mysterious \'Andar Mahal\' (Inner Quarters).', 
 'Dhaka', 
 'Manikganj', 
 'Baliati, Saturia Upazila, Manikganj 1810', 
 23.9961000, 
 90.0406000, 
 'assets/images/baliati-palace.svg', 
 'Founded by Gobinda Ram Saha in the late 18th century, who amassed immense wealth in the salt trade. His descendants expanded the estate into four distinct lineages (Baro Taraf, Madhyam Taraf, Chhota Taraf, etc.), competing to build increasingly lavish neoclassical mansions. Following the 1947 partition, the zamindars migrated to India, leaving the labyrinthine complex behind.', 
 'Village elders speak of an underground vault beneath the northern pond where the family\'s silver bullion was rumored to have been sealed before their flight. Another tragic tale tells of a young bride locked within the inner chambers who took her own life, whose anklets are supposedly heard pacing the colonnades.', 
 'Tourists and staff have occasionally reported the sound of marble rolling across upper wooden floorings in rooms that have remained padlocked for decades.', 
 'The dramatic scale of the architecture—monumental European pillars rising out of rural Bangladeshi paddy fields—standing silent, vast, and mostly empty.', 
 5, 
 'VERIFIED HISTORY', 
 'Department of Archaeology Bangladesh; Hunter, W. W., A Statistical Account of Bengal (1875); Local Records of Manikganj.', 
 0, 
 910),

(13, 
 'Uttara Ganabhaban (Dighapatia Palace)', 
 'uttara-ganabhaban-dighapatia-palace', 
 'The historic fortified palace of Natore, famous for its grand clock tower, defensive moat, and Italian marble statues.', 
 'Uttara Ganabhaban, formerly known as Dighapatia Rajbari, sits surrounded by a wide defensive moat and high ramparts in Natore. Built in 1734 and rebuilt into an architectural masterpiece after the 1897 earthquake, it features an imposing entrance gate topped by a four-dialed mechanical clock tower imported from England, lush gardens filled with Italian marble statues, and royal suites.', 
 'Rajshahi', 
 'Natore', 
 'Dighapatia, Natore Sadar, Natore 6400', 
 24.4339000, 
 89.0064000, 
 'assets/images/uttara-ganabhaban.svg', 
 'Dayaram Roy, the brilliant general and dewan of Rani Bhabani of Natore Raj, founded the Dighapatia dynasty. Rebuilt by Raja Pramada Nath Roy in 1897, the estate served as the official northern residence for the President and later the Prime Minister of Bangladesh (Uttara Ganabhaban).', 
 'Local lore maintains that the grand mechanical clock in the entrance gate possessed an uncanny rhythm, chiming mournfully on days when royal family members fell ill miles away. Stories also linger of Italian marble statues in the inner courtyard appearing to weep dew during rare winter eclipses.', 
 'Night security staff mention seeing shadows cross the bridge over the moat when the drawbridge gate is barred, and hearing muffled footsteps in the royal reception hall.', 
 'The juxtaposition of sovereign power, defensive moats, and an eccentric collection of Victorian clockwork and European statues isolated in the quiet northern heartland.', 
 5, 
 'VERIFIED HISTORY', 
 'District Gazetteer of Rajshahi and Natore; Bangladesh Tourism Board; Department of Archaeology.', 
 0, 
 720),

(14, 
 'Choto Sona Mosque & Gaur Border Ruins', 
 'choto-sona-mosque-gaur-ruins', 
 'The forgotten borderland ruins of Gaur, once one of medieval Asia\'s greatest imperial capitals.', 
 'Situated on the extreme western border of Bangladesh in Chapai Nawabganj, Choto Sona Mosque (Little Golden Mosque) is a jewel of Bengal Sultanate architecture built during the reign of Sultan Alauddin Husain Shah (1493–1519). The surrounding territory holds the forgotten, overgrown ramparts of Gaur, a city that rivaled contemporary European capitals before being struck down by devastating plague and river shifts.', 
 'Rajshahi', 
 'Chapai Nawabganj', 
 'Shahbazpur Union, Shibganj, Chapai Nawabganj 6340', 
 24.8014000, 
 88.1478000, 
 'assets/images/choto-sona.svg', 
 'Gaur was the glorious capital of the Bengal Sultanate. The mosque features 15 gilded domes (which once earned it the name Golden Mosque), exquisite black basalt stone carving, and ornamental rosettes. In 1575, a catastrophic outbreak of epidemic disease wiped out much of the population, leading to the city\'s rapid abandonment and reclamation by jungle.', 
 'Border villagers share legends of \'The Golden Brigade\'—spectral cavalry patrols that ride past the Kotwali Darwaza (ancient police gate) during pitch-dark monsoon squalls, guarding an empire that collapsed nearly half a millennium ago.', 
 'Visitors to the solitary royal tombs nearby report intense feelings of sorrow and occasional temporal disorientation along the overgrown moat embankments.', 
 'The sudden apocalyptic demise of a metropolis of several hundred thousand people in a single season, leaving behind magnificent stone palaces and mosques swallowed by silence.', 
 3, 
 'VERIFIED HISTORY', 
 'Creighton, Henry, The Ruins of Gaur (1817); Dani, A. H., Muslim Architecture in Bengal (1961); Archaeological Survey of India Records.', 
 0, 
 860);

-- Stories
INSERT INTO `stories` (`id`, `title`, `slug`, `excerpt`, `content`, `category_id`, `cover_image`, `author`, `reading_time`, `is_featured`, `status`, `views_count`) VALUES
(1,
 'The Vanishing Merchants of Panam Nagar: 52 Mansions in Mourning',
 'the-vanishing-merchants-of-panam-nagar',
 'How an entire class of wealthy cloth merchants packed their gold, sealed their brick vaults, and vanished overnight from Sonargaon.',
 '<p>On a humid dusk in Sonargaon, the light catches the cracked terracotta moldings of Panam City at an angle that reveals centuries of sorrow. Fifty-two brick mansions stand shoulder to shoulder along a single 600-meter paved road. Once, this corridor buzzed with the brisk trade of Bengal\'s legendary Muslin and fine English textiles. Rich merchants in silk kurtas negotiated fortunes beneath chandeliers imported from Venice.</p>

<p>Today, there is only the rustle of banyan roots splitting brickwork. The windows, once adorned with painted timber shutters, look onto the street like hollow eye sockets.</p>

<h3>The Golden Age of Panam</h3>
<p>Panam Nagar was not built haphazardly. Constructed in the late 19th and early 20th centuries, it was an exclusively affluent urban enclave. Each residence—some single-storied, others rising three levels—was conceived as both home and vault. High ceilings kept rooms cool during scorching monsoon summers, while inner open-air courtyards ensured women of the merchant households could conduct daily domestic life away from public view.</p>

<p>The architecture was eclectic and confident: Corinthian pillars sat beside traditional Mughal cusped arches; intricate European floor tiles were laid alongside Bengali terracotta rosettes. It represented the peak of Bengal\'s mercantile cosmopolitanism.</p>

<h3>The Night of Flight</h3>
<p>The tragedy of Panam was neither sudden earthquake nor violent fire. It was the slower, more agonizing death brought by geopolitical upheaval. Following communal tensions and the 1965 India-Pakistan war, security deteriorated rapidly. Families who had lived in Sonargaon for seven generations realized their world had ended.</p>

<p>According to elder oral accounts collected in nearby Mograpara, the departures were carried out under darkness. Merchants could carry only what fit into wooden chests and bullock carts. Gold coins were reportedly dropped into deep household wells or mortared inside cavity walls, in the vain hope that the families would return once the storm had passed.</p>

<p>They never returned.</p>

<h3>Echoes on the Cobblestones</h3>
<p>In the decades that followed, the Vested Property Act placed the entire city under government administration, yet enforcement was sparse. Local thieves broke open floorboards seeking the rumored merchant gold; roots of peepal trees slowly dismantled masonry balconies.</p>

<p>Those who visit the street when the tourist gate closes describe an unmistakable atmosphere. The wind moving through the open arches carries what locals call \'Hawa-e-Awaz\'—a subtle whispering sound that mimics the distant hum of a crowded market. Whether acoustical resonance of the dense brick corridor or the lingering imprint of human loss, Panam City remains Bangladesh\'s most poignant monument to sudden abandonment.</p>',
 2,
 'assets/images/panam-city.svg',
 'Tariqur Rahman, Heritage Researcher',
 6,
 1,
 'published',
 3420),

(2,
 'The Subterranean Labyrinth of Lalbagh: Where British Troops Vanished',
 'the-subterranean-labyrinth-of-lalbagh',
 'Investigating the infamous southern tunnels beneath Old Dhaka and why imperial British officers ordered them sealed.',
 '<p>Old Dhaka is a city built upon layers. Beneath the chaos of chawk and the narrow alleys of Lalbagh, there are secrets that have resisted light for more than three centuries. Foremost among them is the subterranean tunnel network of Fort Aurangabad—better known today as Lalbagh Fort.</p>

<p>Commissioned in 1678 by Mughal Prince Muhammad Azam, the fort was never completed. When his young betrothed, Iran Dukht (Pari Bibi), died suddenly within its walls, construction ceased as if struck down by imperial grief. But before the trowels were dropped, Mughal military engineers had already dug deep into the delta soil.</p>

<h3>The Escape Arteries of the Subahdar</h3>
<p>Mughal river fortifications were designed for defense and rapid covert evacuation. The southern wall of Lalbagh Fort once met the flowing waters of the Buriganga River. Military chronicles suggest the presence of multiple subterranean conduits designed to allow the Subahdar (Governor) and his inner guard to slip beneath the riverbed to Zinzira Palace on the southern bank should the fort ever be besieged.</p>

<p>What remains documented is the existence of arched brick entranceways descending deep into the earth along the southern ramparts. What remains unexplained is what happened inside them.</p>

<h3>The Colonial Legend</h3>
<p>During the 19th century, following the 1857 Sepoy Mutiny, British imperial officers took control of the fort complex. Driven by military caution and curiosity regarding rumors of hidden Mughal treasuries, commanders dispatched mapping expeditions into the subterranean tunnels.</p>

<p>According to persistent local historical legend, two sepoy soldiers accompanied by hunting hounds were sent into the southernmost vault with lanterns. Hours passed without signal. When search parties ventured to the threshold, the hounds emerged in a state of terror, but the soldiers were never found. A second expedition reportedly suffered unexplained asphyxiation and disorientation, prompting the British military administration to order the tunnel mouths filled and walled over with heavy lime brickwork.</p>

<h3>Fact Versus Folklore</h3>
<p>Modern architectural historians and spelunkers suggest a rational explanation for the tunnel horror: the Buriganga\'s shifting water table likely flooded deeper chambers, creating pockets of poisonous subterranean methane and hydrogen sulfide gases. Any explorer descending without modern ventilation equipment would succumb to oxygen deprivation within minutes.</p>

<p>Yet even today, stand near the sealed arches at twilight and you will hear Old Dhaka elders warn young visitors against resting their hands on the brickwork. Some doors, they say, were closed not to keep intruders out, but to keep the dark safely inside.</p>',
 1,
 'assets/images/lalbagh-fort.svg',
 'Farhana Chowdhury, Historic Archivist',
 5,
 1,
 'published',
 4890),

(3,
 'The Sannyasi Prince of Bhawal: Dead in Darjeeling, Alive in Dhaka',
 'the-sannyasi-prince-of-bhawal-mystery',
 'The astonishing legal battle of a Bengali prince who was cremated in the Himalayas only to return a decade later.',
 '<p>On the rain-swept night of May 8, 1909, in the Himalayan hill station of Darjeeling, Prince Ramendra Narayan Roy, the second Kumar of Bhawal, was declared dead of acute biliary colic. He was 25 years old. Family members, his young wife Bibhabati Devi, and his personal physician Dr. Ashutosh Dasgupta arranged for an immediate Hindu cremation at the stormy burning ghat.</p>

<p>Twelve years later, a naked, ash-smeared sannyasi with matted hair sat upon the banks of the Buriganga River in Buckland Bund, Dhaka. Thousands gathered to gaze upon him. When asked who he was, he broke into tears and spoke in the distinct rural Bengali dialect of Joydebpur. The dead prince had returned.</p>

<h3>The Prince\'s Tale of Escape</h3>
<p>The story told by the sadhu was stranger than any fiction. He claimed that on the night of his cremation, a sudden torrential hailstorm scattered the funeral party before the pyre could be lit. A band of wandering Naga sadhus traveling along the hill road found the comatose body, detected faint breath, and carried him away into the mountains. For a decade, suffering from amnesia, he wandered India under the guidance of a guru, only recovering his memory when he heard Bengali spoken at the Kumbh Mela.</p>

<h3>The Imperial Trial of the Century</h3>
<p>What followed was one of the longest, most sensational trials in the legal history of the British Empire. Bibhabati Devi and the British Court of Wards, which had taken control of the vast Bhawal estate, insisted the man was an imposter named Sundardas. The sisters and tenant peasants of Bhawal embraced him as their rightful master.</p>

<p>Over hundreds of court days, medical experts compared 24 distinct bodily marks: a tiger-claw scar on his arm, a deformed earlobe, syphilis lesions, and identical dental records. In 1936, the Dhaka District Judge ruled in favor of the sannyasi. The decision was appealed all the way to the Privy Council in London.</p>

<p>On July 30, 1946, the Privy Council delivered its final verdict: the sadhu was indeed the resurrected Kumar of Bhawal. But fate had one final cruel twist. Just days after receiving the news of his total legal vindication, the prince suffered a fatal stroke while offering thanksgiving prayers at a temple in Calcutta. He was cremated for a second time—this time beyond any doubt.</p>',
 3,
 'assets/images/bhawal-palace.svg',
 'S. M. Ashraful Alam',
 7,
 0,
 'published',
 2950),

(4,
 'Voices in the Mangrove: Aleya Lights and Bonbibi\'s Protection',
 'voices-in-the-mangrove-aleya-lights-bonbibi',
 'Into the tidal waterways of the Sundarbans, where fishermen navigate ghost lights and ancient forest deities.',
 '<p>The Sundarbans does not tolerate arrogance. In the world\'s largest mangrove forest, where the muddy water changes direction twice a day with the moon\'s pull, humans are merely temporary intruders. Here, the Royal Bengal Tiger does not simply hunt; it is regarded by the forest dwellers as the physical embodiment of Dakshin Rai, the demonic king of the wild.</p>

<h3>The Law of the Jungle</h3>
<p>Before any team of bawalis (woodcutters) or mouals (honey-hunters) sets foot on the muddy forest banks, a solemn ritual must be observed. In tiny wooden shrines carved into banyan trees, offerings of sweets and red cloth are laid before idols of Bonbibi—the forest mother revered equally by Muslim and Hindu gatherers.</p>

<p>The rules of Bonbibi are strict: take only what your family needs to survive; never enter with greed; never speak foul language; and never look directly at the Aleya.</p>

<h3>The Phantoms of the Marshes</h3>
<p>The Aleya, known in Western folklore as will-o\'-the-wisp, are atmospheric light phenomena reported across the saline marshes of Bagerhat and the outer Sundarbans islands. To modern chemists, they are cold flame flares caused by the spontaneous ignition of marsh gases—methane, phosphine, and diphosphane released by anaerobic decay of organic vegetation.</p>

<p>To a lonely fisherman rowing through narrow khals (canals) on a pitch-black midnight, the science offers cold comfort. The lights do not stay stationary. They hover inches above the river, drifting gracefully against the wind, sometimes dividing into twin spheres before merging once again.</p>

<p>Old fishermen tell of comrades who, entranced by the warm glow on freezing nights, steered their boats toward the lights, only to run aground on hidden mudflats or capsize in whirlpools. The spirits, they say, are the lost souls of those who died in the tiger\'s jaws, condemned to wander the tidal channels seeking companions for their eternity.</p>',
 6,
 'assets/images/sundarbans.svg',
 'Kazi Mehedi Hasan',
 5,
 0,
 'published',
 1980),

(5,
 'The Sacrificial Waters of Ramsagar: A Prince\'s Tragic Thirst',
 'the-sacrificial-waters-of-ramsagar-prince',
 'Why a 270-year-old artificial lake in Dinajpur carries an unbroken oral memory of a royal sacrifice.',
 '<p>Between 1750 and 1755, the northern plains of Bengal baked beneath an unyielding drought. In the zamindari of Dinajpur, crops withered in cracked earth, and cholera followed the famine. Raja Ramnath, desperate to save his subjects from starvation, mobilized an army of more than a thousand men to excavate the largest freshwater reservoir the region had ever seen.</p>

<p>For months, the spades bit into the sun-hardened clay of Tejpur. The excavation grew to monumental proportions: over a kilometer long, 250 meters wide, and dozens of cubits deep. Yet when the pickaxes reached the planned depth, not a single drop of water seeped from the subterranean aquifers. The giant trench remained dry as dust.</p>

<h3>The Nightmare of Raja Ramnath</h3>
<p>Northern folk songs preserved in the Dinajpur rural archives recount what happened next. The Raja, heartbroken and facing the imminent rebellion of his dying people, spent the night in fasting and prayer. In his sleep, a voice echoed from the abyss: <em>"The earth will not yield its water without royal blood."</em></p>

<p>The demand was unbearable: his only son, the young Prince Ram, must step into the center of the lake bed and offer himself as a willing sacrifice to the earth deities.</p>

<h3>The Morning of White Silk</h3>
<p>Tradition holds that the prince himself agreed to the ordeal to spare his father\'s subjects. Dressed in white unstitched silk, accompanied by priests chanting Vedic mantras, Prince Ram descended the grand brick staircase into the cavernous dry bed.</p>

<p>As his bare feet touched the center of the pit, the earth groaned. Deep fissures ruptured in the basin floor, and subterranean springs erupted with deafening fury. Before the prince could turn toward the stairs, a wall of clear cold water rose up around him. The peasants on the embankments watched in paralyzed awe as the lake filled in a matter of hours, swallowing their prince forever.</p>

<p>Whether historical reality or a tragic allegorical myth constructed to explain the heroic cost of famine relief, Ramsagar remains to this day one of the most serene and subtly sorrowful landscapes in Bangladesh.</p>',
 1,
 'assets/images/ramsagar.svg',
 'Mahmudul Karim',
 4,
 0,
 'published',
 1540);

-- Story Submissions (Initial seed with pending and approved records for testing admin workflow)
INSERT INTO `story_submissions` (`id`, `submitter_name`, `email`, `story_title`, `district`, `location_name`, `category_id`, `story_content`, `image_url`, `status`, `admin_notes`, `created_at`) VALUES
(1, 'Nayeem Islam', 'nayeem.explorer@gmail.com', 'The Spectral Bell of Batali Hill', 'Chattogram', 'Batali Hill WW2 Bunkers', 1, 'During my college trekking trip to Batali Hill last winter, my three friends and I stayed past sunset near the old British anti-aircraft gun stations. As the sea mist rolled in from the port, we distinctly heard a heavy brass ship bell ringing rhythmically three times, though there is no ship or temple within miles of that ridge. Our phone compasses were spinning completely erratically.', '', 'pending', NULL, '2026-11-01 19:30:00'),
(2, 'Shaila Afroze', 'shaila.afroze@yahoo.com', 'Whispers at the Edge of Baliati Inner Court', 'Manikganj', 'Baliati Palace Andar Mahal', 5, 'My uncle was a caretaker at Baliati Palace during the late 1990s. He always warned us never to enter the third courtyard after the main iron gates were padlocked for the evening. One evening while inspecting window latches, he saw light flickering in the locked second floor chamber. When he peeked through the keyhole, he saw the dust motes swirling in a tight spiral as if someone were dancing in the center of the room.', '', 'pending', NULL, '2026-11-03 14:15:00'),
(3, 'Kabir Hossain', 'kabir.hossain@outlook.com', 'The Night Fisherman of Kuakata', 'Patuakhali', 'Kuakata Rakhine Coast', 6, 'Old fishermen in Kalapara still refuse to cast their nets near the submerged ancient timber well during the new moon. They say the tides churn in reverse and faint wooden gongs can be heard beneath the water surface.', '', 'approved', 'Approved for archive drafting.', '2026-10-25 11:20:00');

-- Place Images (Supplementary gallery images)
INSERT INTO `place_images` (`place_id`, `image_url`, `caption`, `is_primary`) VALUES
(1, 'assets/images/panam-city.svg', 'The haunting 600m colonnaded avenue of Panam Nagar', 1),
(1, 'assets/images/panam-city-2.svg', 'Detail of decaying European-style Corinthian pilasters', 0),
(2, 'assets/images/lalbagh-fort.svg', 'The imposing southern bastion and tomb complex of Lalbagh Fort', 1),
(2, 'assets/images/lalbagh-fort-2.svg', 'The arched water gate leading to the subterranean vaults', 0),
(3, 'assets/images/mahasthangarh.svg', 'Ancient brick ramparts of Pundranagara overlooking Karatoya', 1),
(4, 'assets/images/puthia-palace.svg', 'Puthia Rajbari facade and ornate terracotta temple cluster', 1),
(11, 'assets/images/sundarbans.svg', 'Tidal mangrove waterways and misty channels of the Sundarbans', 1),
(12, 'assets/images/baliati-palace.svg', 'Greco-Roman classical colonnades of Baliati Palace', 1);

