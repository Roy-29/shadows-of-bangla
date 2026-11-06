<?php
/**
 * SHADOWS OF BANGLA — Admin Authentication & Security
 */

if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

require_once __DIR__ . '/functions.php';

/**
 * Check if an administrator is currently authenticated.
 *
 * @return bool
 */
function is_admin_logged_in(): bool {
    return !empty($_SESSION['admin_logged_in']) && !empty($_SESSION['admin_id']);
}

/**
 * Enforce administrator authentication.
 * Redirects to login page if unauthorized.
 *
 * @param string $loginUrl
 */
function require_admin_auth(string $loginUrl = 'login.php'): void {
    if (!is_admin_logged_in()) {
        $_SESSION['flash_error'] = 'Access denied. Please authenticate as an archivist first.';
        header("Location: {$loginUrl}");
        exit;
    }
}

/**
 * Retrieve active administrator details.
 *
 * @return array|null
 */
function get_current_admin(): ?array {
    if (!is_admin_logged_in()) {
        return null;
    }
    return [
        'id' => $_SESSION['admin_id'] ?? null,
        'username' => $_SESSION['admin_username'] ?? '',
        'full_name' => $_SESSION['admin_name'] ?? 'Archivist',
        'email' => $_SESSION['admin_email'] ?? '',
        'role' => $_SESSION['admin_role'] ?? 'admin'
    ];
}

/**
 * Attempt to authenticate an administrator.
 *
 * @param string $identifier Username or Email
 * @param string $password Cleartext password
 * @return array ['success' => bool, 'message' => string]
 */
function login_admin(string $identifier, string $password): array {
    $identifier = trim($identifier);
    $password = trim($password);

    if (empty($identifier) || empty($password)) {
        return ['success' => false, 'message' => 'Please provide both username/email and password.'];
    }

    $pdo = get_db_connection();
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection failed. Please ensure MySQL is running in XAMPP.'];
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :id1 OR email = :id2 LIMIT 1");
        $stmt->execute([':id1' => $identifier, ':id2' => $identifier]);
        $admin = $stmt->fetch();

        if (!$admin) {
            return ['success' => false, 'message' => 'Invalid credentials.'];
        }

        $authenticated = false;
        $needsRehash = false;

        // Verify password with PHP native password_verify
        if (password_verify($password, $admin['password_hash'])) {
            $authenticated = true;
            if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
                $needsRehash = true;
            }
        } 
        // Seed migration fallback for first login
        elseif (hash_equals(hash('sha256', $password . 'shadows_salt_2026'), $admin['password_hash'])) {
            $authenticated = true;
            $needsRehash = true;
        }

        if (!$authenticated) {
            return ['success' => false, 'message' => 'Invalid credentials.'];
        }

        // Update password hash if rehash is needed
        if ($needsRehash) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $rehashStmt = $pdo->prepare("UPDATE admins SET password_hash = :hash WHERE id = :id");
            $rehashStmt->execute([':hash' => $newHash, ':id' => $admin['id']]);
        }

        // Session fixation protection: regenerate session ID
        session_regenerate_id(true);

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_name'] = $admin['full_name'] ?: $admin['username'];
        $_SESSION['admin_role'] = $admin['role'];

        // Update last login timestamp
        $updateStmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = :id");
        $updateStmt->execute([':id' => $admin['id']]);

        return ['success' => true, 'message' => 'Authentication successful. Welcome, ' . e($_SESSION['admin_name']) . '.'];
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An internal database error occurred.'];
    }
}

/**
 * Terminate administrator session and destroy cookies.
 */
function logout_admin(): void {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}

