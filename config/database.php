<?php
/**
 * SHADOWS OF BANGLA — Database Configuration
 * PDO Connection Handler with Prepared Statement Enforcements
 */

// Prevent direct script execution if accessed outside standard entry points
if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

// Database Connection Parameters (Default XAMPP configuration)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: 3306);
define('DB_NAME', getenv('DB_NAME') ?: 'shadows_of_bangla');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// Site URL Configuration
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = str_replace('\\', '/', $scriptDir);
// If accessed inside /admin/, strip the admin part for base URL
$baseUrl = rtrim($protocol . $host . preg_replace('/\/admin(\/.*)?$/', '', $scriptDir), '/');
if (empty($baseUrl)) {
    $baseUrl = 'http://localhost';
}
define('SITE_URL', $baseUrl);
define('SITE_NAME', 'SHADOWS OF BANGLA');
define('SITE_TAGLINE', 'Beyond the Known.');

/**
 * Returns a singleton PDO connection instance.
 *
 * @return PDO|null Returns PDO on success, null on failure.
 */
function get_db_connection(): ?PDO {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Log the error internally
        error_log('Database Connection Error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Check if the database connection and required tables exist.
 *
 * @return array Status information
 */
function check_db_health(): array {
    $pdo = get_db_connection();
    if (!$pdo) {
        return [
            'connected' => false,
            'tables_found' => false,
            'message' => 'Cannot connect to MySQL server. Ensure MySQL is running in XAMPP on port ' . DB_PORT . '.'
        ];
    }

    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'places'");
        $exists = $stmt->rowCount() > 0;
        return [
            'connected' => true,
            'tables_found' => $exists,
            'message' => $exists ? 'Database connected and tables verified.' : 'Database connected, but tables are missing. Import database/shadows_of_bangla.sql or run config/setup.php.'
        ];
    } catch (PDOException $e) {
        return [
            'connected' => true,
            'tables_found' => false,
            'message' => 'Error querying tables: ' . $e->getMessage()
        ];
    }
}

