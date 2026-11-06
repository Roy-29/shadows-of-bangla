# SHADOWS OF BANGLA — Beyond the Known.

> An interactive, cinematic dark-horror web platform exploring mysterious places, abandoned locations, local legends, historical enigmas, and folklore across Bangladesh.

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![MySQL Version](https://img.shields.io/badge/MySQL-5.7%2B%20%2F%208.0%2B-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![Leaflet.js](https://img.shields.io/badge/Leaflet-1.9.4-199900?style=flat-square&logo=leaflet&logoColor=white)](https://leafletjs.com)
[![Status](https://img.shields.io/badge/Status-Complete%20%26%20Tested-10B981?style=flat-square)]()

---

## 1. PROJECT OVERVIEW

**SHADOWS OF BANGLA** is an editorial-grade exploration platform crafted to preserve the cultural memory, architectural heritage, and oral folklore of Bangladesh. From 2,500-year-old fortified trade citadels in Narsingdi to the silent 19th-century merchant mansions of Panam Nagar and the bioluminescent *Aleya* ghost lights of the Sundarbans, the archive provides a serious, respectful, and aesthetically thrilling journey through the delta's greatest mysteries.

### Core Distinctions
- **No Pseudo-Science:** All content is strictly cataloged under three transparent labels:
  - `VERIFIED HISTORY`: Documented by the Department of Archaeology, primary chronicles, and historical gazetteers.
  - `LOCAL FOLKLORE`: Sustained oral traditions, ballads, and cultural myths passed down through generations.
  - `UNVERIFIED LEGEND`: Spectral sightings and witness accounts presented as folklore, never as scientific fact.
- **Real Geographic Coordinates:** Every location marker is calibrated to verified real-world latitude and longitude.
- **Zero Framework Bloat:** Engineered purely in **modular PHP 8+**, **Vanilla ES6+ JavaScript**, **CSS3**, and **MySQL**, requiring no Node.js build steps or paid external APIs.

---

## 2. TECHNOLOGY STACK

| Layer | Technologies |
|---|---|
| **Backend** | PHP 8.0+ (PDO, Prepared Statements, Secure Sessions, BCrypt) |
| **Database** | MySQL 5.7+ / MySQL 8.0+ / MariaDB 10.3+ |
| **Frontend** | Semantic HTML5, CSS3 Custom Properties, Vanilla ES6+ JavaScript |
| **Interactive Cartography** | Leaflet.js 1.9.4 + OpenStreetMap / CartoDB Dark Matter |
| **Audio Ambiance** | Custom Web Audio API Synthesizer (100% offline, zero bandwidth) |
| **Typography** | Google Fonts (*Cinzel* & *Plus Jakarta Sans*) |
| **Icons** | Font Awesome 6.5.1 CDN |
| **Local Runtime** | XAMPP (Apache + MySQL) / PHP Built-in Server |

---

## 3. REPOSITORY DIRECTORY STRUCTURE

```
shadows-of-bangla/
│
├── index.php                 # Home portal (Hero, featured sites, map preview, quiz, stats)
├── explore.php               # Locations discovery (Search, division/category filters, sorting)
├── map.php                   # Full-screen interactive Leaflet map of Bangladesh
├── place.php                 # Dynamic place dossier (History, folklore, mini-map, gallery)
├── stories.php               # Chronicles archive (Editorial stories, categories, reading time)
├── story.php                 # Single chronicle reader (Reading progress bar, rich typography)
├── submit-story.php          # Community submission form (Honeypot spam filter, CSRF protection)
├── about.php                 # Philosophy, research standards, and content disclaimers
├── 404.php                   # Atmospheric error page
│
├── config/
│   ├── database.php          # Database PDO connection handler & environment credentials
│   └── setup.php             # Automated web installer & database health diagnostic
│
├── includes/
│   ├── header.php            # HTML head, OpenGraph, icons, canvas particles, progress bar
│   ├── navbar.php            # Sticky header, search trigger, bookmarks modal, audio pill
│   ├── footer.php            # Atmospheric footer, division index, legal & map citations
│   ├── functions.php         # Sanitize, slugify, CSRF, verification badges, reading time
│   └── auth.php              # Session checks, BCrypt password hashing, session regeneration
│
├── admin/
│   ├── index.php             # Archivist Dashboard (Overview metrics, pending alerts)
│   ├── login.php             # Secure authentication portal
│   ├── logout.php            # Session termination
│   ├── places.php            # Places CRUD list & search
│   ├── add-place.php         # Add location dossier with coordinates & file upload
│   ├── edit-place.php        # Edit existing location dossier
│   ├── stories.php           # Chronicles CRUD list & status toggle
│   ├── add-story.php         # Compose editorial manuscript
│   ├── edit-story.php        # Edit chronicle
│   ├── submissions.php       # Inbound submissions inbox with 1-click promotion to story
│   ├── categories.php        # Category taxonomies and icon management
│   ├── header.php            # Admin sidebar & topbar partial
│   └── footer.php            # Admin scripts & modal helpers
│
├── assets/
│   ├── css/
│   │   ├── style.css         # Cinematic dark-horror design system (#08090D, #9E1B32)
│   │   ├── responsive.css    # Mobile, tablet, and touch optimizations
│   │   └── admin.css         # Admin dashboard styling
│   ├── js/
│   │   ├── main.js           # Audio synthesizer, particle canvas, bookmarks, recent places
│   │   ├── map.js            # Leaflet map controller, custom pulsing markers, dark tiles
│   │   └── interactions.js   # Reading progress bar, random place summoner, folklore quiz
│   ├── images/               # Cinematic SVG place artwork, banners, and textures
│   └── audio/                # Audio scripts & Web Audio synthesis pipeline
│
├── database/
│   └── shadows_of_bangla.sql # Complete MySQL database schema and 14 authentic seed records
│
├── uploads/                  # Safe upload directory for user/admin field imagery
├── .htaccess                 # Apache server rewrite rules, security headers & 404 routing
├── robots.txt                # Search crawler instructions
├── sitemap.xml               # Search engine index with all verified locations
└── README.md                 # Complete documentation
```

---

## 4. REQUIREMENTS & PREREQUISITES

To run the website locally, ensure you have:
- **XAMPP** (recommended) with **PHP 8.0+** and **MySQL / MariaDB**
- Alternatively: Standard standalone PHP 8.0+ and MySQL 5.7+ installed.
- A modern web browser (Chrome, Firefox, Edge, Safari) with JavaScript enabled.

---

## 5. INSTALLATION & SETUP GUIDE (STEP-BY-STEP)

### Method A: Using XAMPP (Standard)

#### Step 1: Place the Project in XAMPP `htdocs`
Copy the project folder `d:\A Haunt` (or clone the repository) into your XAMPP web root:
```
C:\xampp\htdocs\shadows-of-bangla
```
*(Or keep it at `D:\A Haunt` and configure an Apache VirtualHost pointing to `D:\A Haunt`)*

#### Step 2: Start Apache and MySQL
1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.

#### Step 3: Initialize the Database

You can initialize the database using either **Option 1 (Automated Web Installer)** or **Option 2 (phpMyAdmin)**:

##### Option 1: Automated Web Setup (One-Click)
Open your browser and navigate to:
```
http://localhost/shadows-of-bangla/config/setup.php
```
Click **Install & Initialize Database**. The installer will:
1. Create the `shadows_of_bangla` database automatically.
2. Execute all schema definitions and insert the 14 verified locations, 5 chronicles, and default classifications.
3. Automatically secure the default administrator account with native BCrypt password hashing.

##### Option 2: Manual Import via phpMyAdmin
1. Open your browser and go to `http://localhost/phpmyadmin`.
2. Click the **Import** tab in the top navigation.
3. Click **Choose File** and select:
   ```
   database/shadows_of_bangla.sql
   ```
4. Click **Import** at the bottom of the page. The database `shadows_of_bangla` and all tables will be created immediately.

---

### Method B: Using PHP Built-In Web Server

If running without full XAMPP Apache, you can launch PHP's internal server directly from the project directory:

```powershell
# Navigate to the workspace directory
cd "d:\A Haunt"

# Launch PHP server
php -S localhost:8000
```

Then visit:
```
http://localhost:8000/config/setup.php
```
to initialize the database, and:
```
http://localhost:8000
```
to explore the website!

---

## 6. DATABASE CONFIGURATION

The database connection parameters are defined in `config/database.php`:

```php
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: 3306);
define('DB_NAME', getenv('DB_NAME') ?: 'shadows_of_bangla');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: ''); // Default XAMPP has no password
```

If your local MySQL uses a custom password or port (e.g. `3307`), update `config/database.php` or set environment variables `DB_PASS` and `DB_PORT`.

---

## 7. ADMINISTRATOR ACCESS & CREDENTIALS

The administrative panel allows curators to manage places, compose manuscripts, and review user submissions.

### Admin Portal URL
```
http://localhost/shadows-of-bangla/admin/login.php
```

### Default Credentials
| Field | Value |
|---|---|
| **Username** | `admin` |
| **Email** | `admin@shadowsofbangla.com` |
| **Password** | `AdminShadows#2026` |

> **Security Note:** On your first login, the system automatically migrates and upgrades the password hash to native PHP `PASSWORD_DEFAULT` (BCrypt). You can also update your credentials directly in the database or admin interface.

---

## 8. FEATURES & CAPABILITIES

### Public Exploration Platform
1. **Atmospheric Homepage (`index.php`):**
   - Cinematic hero featuring abandoned Bangladeshi architecture.
   - Mystery of the Day algorithm deterministically changing with the day-of-year.
   - "Summon Random Place" button with screen flicker animation.
   - Live database statistics counters animated via `IntersectionObserver`.
   - Division-by-division exploration links.
   - Recently Viewed places tray powered by browser `localStorage`.
2. **Explore Directory (`explore.php`):**
   - Responsive multi-column grid of place cards with hover glows.
   - Real-time search by place name, district, or keyword.
   - Division filter (Dhaka, Chattogram, Rajshahi, Khulna, Barishal, Sylhet, Rangpur, Mymensingh).
   - Category filter (Haunted Legends, Abandoned Places, Historical Mysteries, etc.).
   - Sorting options (Newest, Alphabetical A-Z, Most Explored, Random Order).
   - Pagination with dynamic page offsets.
3. **Interactive Bangladesh Map (`map.php`):**
   - Powered by Leaflet.js and CartoDB Dark Matter styling.
   - Custom glowing red pulse markers.
   - Real verified coordinates for all 14 Bangladeshi locations.
   - Clickable popups with thumbnail image, division/district tag, and direct links.
   - Division filter, category filter, search box, and Reset-to-Bangladesh button.
4. **Place Dossier Page (`place.php`):**
   - High-definition cinematic cover hero.
   - Clear visual status badges (`VERIFIED HISTORY`, `LOCAL FOLKLORE`, `UNVERIFIED LEGEND`).
   - Deep historical background, oral legends, and paranormal claims.
   - Interactive Leaflet mini-map centered on the location.
   - Supplementary gallery photos with captions.
   - Related places recommendations and primary archival citations.
   - Bookmark location toggle stored in `localStorage`.
5. **Mystery Stories Archive (`stories.php` & `story.php`):**
   - Editorial manuscripts exploring royal disappearances and delta lore.
   - Real-time reading progress bar pinned to the top of the browser.
   - Word-count based reading time calculator (e.g. 5 min read).
   - Related stories recommendations.
6. **Community Story Submission (`submit-story.php`):**
   - Client and server-side validation.
   - Anti-spam hidden honeypot field.
   - CSRF token validation.
   - Submissions stored safely in the database with status `pending`.
   - Submissions are never published automatically without admin approval.
7. **Interactive Mystery Quiz:**
   - 5-question test on Bangladeshi history, archaeology, and folklore.
   - Immediate feedback (green for correct, red for incorrect) with explanations.
   - Dynamic score calculation and spooky archivist rank awards (*Novice Wanderer*, *Shadow Archivist*, *Grand Inquisitor of Bengal*).
8. **Atmospheric Web Audio Soundscape:**
   - **Sound is strictly OFF by default** (respecting modern UX standards).
   - Clickable sound toggle pill in the navbar.
   - Real-time synthesized low-frequency horror drone, harmonic beat frequency, and bandpass-filtered wind noise using the HTML5 Web Audio API.
   - Volume control slider stored in `localStorage`.
   - Equalizer visualizer bars animate when sound is active.

### Archivist Administration Panel (`/admin/`)
- **Dashboard (`index.php`):** Metric counters for places, stories, pending submissions, and views.
- **Places Manager (`places.php`):** Full CRUD table with cover preview, search, division filter, and delete confirmation.
- **Add Place (`add-place.php`):** Form supporting coordinates, multi-division select, categories, verification status, rich text fields, and image uploads.
- **Edit Place (`edit-place.php`):** Pre-filled form with validation and file replacement.
- **Stories Manager (`stories.php`):** Publish / draft toggle with one click, delete action with CSRF.
- **Add / Edit Story (`add-story.php`, `edit-story.php`):** Full editorial composition suite with HTML support.
- **Submissions Review (`submissions.php`):** View submitted community stories, approve, reject with notes, or **convert directly into a published story** with a single click!
- **Categories Manager (`categories.php`):** Add new categories or view usage metrics.

---

## 9. DOCUMENTED BANGLADESHI LOCATIONS IN SEED DATA

All 14 locations in the initial database feature verified real-world coordinates and authentic historical documentation:

| # | Location | District | Division | Category | Status | Coordinates |
|---|---|---|---|---|---|---|
| 1 | **Panam City (Panam Nagar)** | Narayanganj | Dhaka | Abandoned Places | VERIFIED HISTORY | 23.6493° N, 90.6017° E |
| 2 | **Lalbagh Fort** | Dhaka | Dhaka | Haunted Legends | VERIFIED HISTORY | 23.7188° N, 90.3882° E |
| 3 | **Mahasthangarh (Pundranagara)** | Bogura | Rajshahi | Historical Mysteries | VERIFIED HISTORY | 24.9608° N, 89.3456° E |
| 4 | **Puthia Rajbari Complex** | Rajshahi | Rajshahi | Mysterious Buildings | LOCAL FOLKLORE | 24.3644° N, 88.8358° E |
| 5 | **Wari-Bateshwar Ancient Port** | Narsingdi | Dhaka | Historical Mysteries | VERIFIED HISTORY | 24.0933° N, 90.8350° E |
| 6 | **Bhawal Rajbari (Sannyasi Court)** | Gazipur | Dhaka | Historical Mysteries | VERIFIED HISTORY | 24.0016° N, 90.4285° E |
| 7 | **Foy's Lake & Batali Hill** | Chattogram | Chattogram | Haunted Legends | LOCAL FOLKLORE | 22.3667° N, 91.8028° E |
| 8 | **Jaintia Megalithic Monuments** | Sylhet | Sylhet | Local Folklore | VERIFIED HISTORY | 25.1328° N, 92.1283° E |
| 9 | **Ancient Wells of Kuakata** | Patuakhali | Barishal | Local Folklore | LOCAL FOLKLORE | 21.8174° N, 90.1189° E |
| 10 | **Ramsagar Dighi** | Dinajpur | Rangpur | Haunted Legends | LOCAL FOLKLORE | 25.5539° N, 88.6292° E |
| 11 | **Sundarbans & Bonbibi Lore** | Bagerhat | Khulna | Local Folklore | LOCAL FOLKLORE | 21.8544° N, 89.7828° E |
| 12 | **Baliati Palace** | Manikganj | Dhaka | Mysterious Buildings | VERIFIED HISTORY | 23.9961° N, 90.0406° E |
| 13 | **Uttara Ganabhaban (Dighapatia)** | Natore | Rajshahi | Mysterious Buildings | VERIFIED HISTORY | 24.4339° N, 89.0064° E |
| 14 | **Choto Sona Mosque & Gaur Ruins** | Chapai Nawabganj | Rajshahi | Historical Mysteries | VERIFIED HISTORY | 24.8014° N, 88.1478° E |

---

## 10. SECURITY & BEST PRACTICES

- **SQL Injection Prevention:** 100% of database queries use PDO prepared statements with strict parameter binding.
- **XSS Protection:** Output is sanitized using `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- **CSRF Defense:** Cryptographic CSRF tokens (`hash_equals()`) are embedded in all POST forms.
- **Anti-Spam:** Community submission forms contain an invisible CSS/tabindex honeypot field to block automated bots.
- **Session Security:**
  - Session cookies configured with `HttpOnly`, `SameSite=Lax`, and `Secure` where HTTPS is available.
  - `session_regenerate_id(true)` executed upon login to prevent session fixation attacks.
- **File Upload Safeguards:** Uploaded image filenames are randomized with cryptographic hashes, restricted to JPG/PNG/WEBP/SVG, and protected by `.htaccess` inside `uploads/` against executable scripts.

---

## 11. TROUBLESHOOTING

### 1. "Cannot connect to MySQL server"
- Ensure MySQL is running in your XAMPP Control Panel.
- If MySQL is on a custom port (e.g. `3307`), update `DB_PORT` in `config/database.php`.

### 2. "Database connected, but tables are missing"
- Visit `http://localhost/shadows-of-bangla/config/setup.php` and click **Install & Initialize Database**.
- Or import `database/shadows_of_bangla.sql` via phpMyAdmin.

### 3. Audio Does Not Play
- Modern browsers require user interaction (a click) before audio can play. Click the **Atmosphere OFF** pill in the navbar to start the audio synthesizer.
- Sound is disabled by default to maintain compliance with web accessibility standards.

---

## 12. LICENSE & CREDITS

- **Concept & Archival Direction:** Shadows of Bangla Heritage Initiative
- **Map Tiles:** © [OpenStreetMap](https://www.openstreetmap.org/copyright) contributors, © [CARTO](https://carto.com/attributions)
- **Icons:** [Font Awesome Free](https://fontawesome.com)
- **Fonts:** [Cinzel](https://fonts.google.com/specimen/Cinzel) & [Plus Jakarta Sans](https://fonts.google.com/specimen/Plus+Jakarta+Sans) via Google Fonts

