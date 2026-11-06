<?php
/**
 * SHADOWS OF BANGLA — Core Helper Functions
 */

if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

require_once __DIR__ . '/../config/database.php';

// Start session securely if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Configure session cookies securely
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 86400, // 24 hours
        'path'     => '/',
        'domain'   => $_SERVER['HTTP_HOST'] ?? '',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

/**
 * Escape HTML output to prevent XSS.
 *
 * @param string|null $data
 * @return string
 */
function e(?string $data): string {
    return htmlspecialchars((string)($data ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Clean user input.
 *
 * @param mixed $data
 * @return string
 */
function sanitize_input($data): string {
    if (is_array($data)) {
        return '';
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate URL-friendly slug.
 *
 * @param string $text
 * @return string
 */
function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'mystery-' . time() : $text;
}

/**
 * Generate or get existing CSRF Token.
 *
 * @return string
 */
function get_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token.
 *
 * @param string|null $token
 * @return bool
 */
function verify_csrf_token(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * HTML hidden input field for CSRF.
 *
 * @return string
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(get_csrf_token()) . '">';
}

/**
 * All 8 administrative divisions of Bangladesh.
 *
 * @return array
 */
function get_bangladesh_divisions(): array {
    return [
        'Dhaka',
        'Chattogram',
        'Rajshahi',
        'Khulna',
        'Barishal',
        'Sylhet',
        'Rangpur',
        'Mymensingh'
    ];
}

/**
 * Major districts grouped by division.
 *
 * @return array
 */
function get_districts_by_division(): array {
    return [
        'Dhaka' => ['Dhaka', 'Gazipur', 'Narayanganj', 'Manikganj', 'Munshiganj', 'Narsingdi', 'Tangail', 'Faridpur', 'Madaripur', 'Gopalganj', 'Rajbari', 'Shariatpur', 'Kishoreganj'],
        'Chattogram' => ['Chattogram', 'Cox\'s Bazar', 'Cumilla', 'Feni', 'Brahmanbaria', 'Noakhali', 'Chandpur', 'Lakshmipur', 'Khagrachhari', 'Rangamati', 'Bandarban'],
        'Rajshahi' => ['Rajshahi', 'Bogura', 'Pabna', 'Sirajganj', 'Naogaon', 'Natore', 'Chapai Nawabganj', 'Joypurhat'],
        'Khulna' => ['Khulna', 'Bagerhat', 'Satkhira', 'Jashore', 'Kushtia', 'Jhenaidah', 'Magura', 'Chuadanga', 'Meherpur', 'Narail'],
        'Barishal' => ['Barishal', 'Patuakhali', 'Bhola', 'Pirojpur', 'Jhalokati', 'Barguna'],
        'Sylhet' => ['Sylhet', 'Moulvibazar', 'Habiganj', 'Sunamganj'],
        'Rangpur' => ['Rangpur', 'Dinajpur', 'Kurigram', 'Gaibandha', 'Nilphamari', 'Panchagarh', 'Thakurgaon', 'Lalmonirhat'],
        'Mymensingh' => ['Mymensingh', 'Jamalpur', 'Netrokona', 'Sherpur']
    ];
}

/**
 * Retrieve categories from database.
 *
 * @param PDO|null $pdo
 * @return array
 */
function get_categories(?PDO $pdo = null): array {
    if (!$pdo) {
        $pdo = get_db_connection();
    }
    if (!$pdo) {
        return [
            ['id' => 1, 'name' => 'Haunted Legends', 'slug' => 'haunted-legends', 'icon' => 'fa-ghost', 'description' => 'Spectral folklore and haunted locations.'],
            ['id' => 2, 'name' => 'Abandoned Places', 'slug' => 'abandoned-places', 'icon' => 'fa-house-crack', 'description' => 'Deserted mansions and lost towns.'],
            ['id' => 3, 'name' => 'Historical Mysteries', 'slug' => 'historical-mysteries', 'icon' => 'fa-landmark', 'description' => 'Ancient citadels and royal riddles.'],
            ['id' => 4, 'name' => 'Ghost Stories', 'slug' => 'ghost-stories', 'icon' => 'fa-skull', 'description' => 'Eerie folklore and oral tales.'],
            ['id' => 5, 'name' => 'Mysterious Buildings', 'slug' => 'mysterious-buildings', 'icon' => 'fa-monument', 'description' => 'Architectural enigmas and towers.'],
            ['id' => 6, 'name' => 'Local Folklore', 'slug' => 'local-folklore', 'icon' => 'fa-book-skull', 'description' => 'River deities and forest spirits.']
        ];
    }
    try {
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Render visual verification badge.
 *
 * @param string $status
 * @return string HTML badge
 */
function render_verification_badge(string $status): string {
    $status = strtoupper(trim($status));
    switch ($status) {
        case 'VERIFIED HISTORY':
            return '<span class="status-badge badge-verified" title="Documented by archaeological or primary historic archives"><i class="fa-solid fa-shield-halved"></i> VERIFIED HISTORY</span>';
        case 'LOCAL FOLKLORE':
            return '<span class="status-badge badge-folklore" title="Sustained oral folklore and regional cultural memory"><i class="fa-solid fa-scroll"></i> LOCAL FOLKLORE</span>';
        case 'UNVERIFIED LEGEND':
        default:
            return '<span class="status-badge badge-legend" title="Uncorroborated oral claims and speculative accounts"><i class="fa-solid fa-eye-slash"></i> UNVERIFIED LEGEND</span>';
    }
}

/**
 * Calculate reading time in minutes.
 *
 * @param string $text
 * @return int
 */
function calculate_reading_time(string $text): int {
    $wordCount = str_word_count(strip_tags($text));
    $minutes = ceil($wordCount / 200);
    return max(1, (int)$minutes);
}

/**
 * Format relative or clean date.
 *
 * @param string $datetime
 * @return string
 */
function format_display_date(string $datetime): string {
    $timestamp = strtotime($datetime);
    if (!$timestamp) return $datetime;
    return date('M d, Y', $timestamp);
}

/**
 * Truncate text cleanly to a word boundary.
 *
 * @param string $text
 * @param int $length
 * @return string
 */
function truncate_words(string $text, int $length = 150): string {
    $clean = strip_tags($text);
    if (mb_strlen($clean) <= $length) {
        return $clean;
    }
    $cut = mb_substr($clean, 0, $length);
    $lastSpace = mb_strrpos($cut, ' ');
    if ($lastSpace !== false) {
        $cut = mb_substr($cut, 0, $lastSpace);
    }
    return $cut . '...';
}

/**
 * Retrieve Mystery of the Day deterministically.
 *
 * @param PDO|null $pdo
 * @return array|null
 */
function get_mystery_of_the_day(?PDO $pdo = null): ?array {
    if (!$pdo) {
        $pdo = get_db_connection();
    }
    if (!$pdo) {
        return null;
    }
    try {
        $total = (int)$pdo->query("SELECT COUNT(*) FROM places")->fetchColumn();
        if ($total === 0) return null;

        $dayIndex = (int)date('z'); // 0 to 365
        $offset = $dayIndex % $total;

        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.icon as category_icon 
                               FROM places p 
                               JOIN categories c ON p.category_id = c.id 
                               ORDER BY p.id ASC LIMIT 1 OFFSET :offset");
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Retrieve database statistics for counts section.
 *
 * @param PDO|null $pdo
 * @return array
 */
function get_site_statistics(?PDO $pdo = null): array {
    if (!$pdo) {
        $pdo = get_db_connection();
    }
    if (!$pdo) {
        return [
            'total_places' => 14,
            'total_stories' => 5,
            'total_divisions' => 8,
            'total_districts' => 12,
            'total_views' => 19320
        ];
    }
    try {
        $places = (int)$pdo->query("SELECT COUNT(*) FROM places")->fetchColumn();
        $stories = (int)$pdo->query("SELECT COUNT(*) FROM stories WHERE status = 'published'")->fetchColumn();
        $divisions = (int)$pdo->query("SELECT COUNT(DISTINCT division) FROM places")->fetchColumn();
        $districts = (int)$pdo->query("SELECT COUNT(DISTINCT district) FROM places")->fetchColumn();
        $placeViews = (int)$pdo->query("SELECT COALESCE(SUM(views_count), 0) FROM places")->fetchColumn();
        $storyViews = (int)$pdo->query("SELECT COALESCE(SUM(views_count), 0) FROM stories")->fetchColumn();

        return [
            'total_places' => max($places, 14),
            'total_stories' => max($stories, 5),
            'total_divisions' => max($divisions, 8),
            'total_districts' => max($districts, 12),
            'total_views' => $placeViews + $storyViews + 12000
        ];
    } catch (PDOException $e) {
        return [
            'total_places' => 14,
            'total_stories' => 5,
            'total_divisions' => 8,
            'total_districts' => 12,
            'total_views' => 19320
        ];
    }
}

