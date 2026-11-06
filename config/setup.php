<?php
/**
 * SHADOWS OF BANGLA — Automated Database Installer & Health Check
 * Initializes tables and default records directly from shadows_of_bangla.sql
 */

define('APP_INIT', true);
require_once __DIR__ . '/database.php';

$message = '';
$status = '';
$diagnostics = [];

// Handle setup trigger
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'run_setup') {
    try {
        // Connect to MySQL server without selecting a DB first
        $rootDsn = sprintf('mysql:host=%s;port=%d;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $rootPdo = new PDO($rootDsn, DB_USER, DB_PASS, $options);

        // Create Database if needed
        $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $rootPdo->exec("USE `" . DB_NAME . "`;");

        // Read SQL schema file
        $sqlPath = dirname(__DIR__) . '/database/shadows_of_bangla.sql';
        if (!file_exists($sqlPath)) {
            throw new Exception("SQL file not found at: {$sqlPath}");
        }

        $sqlContent = file_get_contents($sqlPath);

        // Remove comments and split statements safely
        // Remove standard line comments and block comments
        $lines = explode("\n", $sqlContent);
        $cleanSql = '';
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (strpos($trimmed, '--') === 0 || strpos($trimmed, '/*') === 0) {
                continue;
            }
            $cleanSql .= $line . "\n";
        }

        // Split by semicolons followed by newline
        $statements = preg_split('/;\s*[\r\n]+/', $cleanSql);
        $executedCount = 0;

        foreach ($statements as $stmtSql) {
            $stmtSql = trim($stmtSql);
            if (!empty($stmtSql)) {
                $rootPdo->exec($stmtSql);
                $executedCount++;
            }
        }

        // Generate native bcrypt password for default admin
        $adminPassword = 'AdminShadows#2026';
        $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
        $adminStmt = $rootPdo->prepare("UPDATE `admins` SET `password_hash` = ? WHERE `username` = 'admin'");
        $adminStmt->execute([$hashedPassword]);

        $status = 'success';
        $message = "Database `shadows_of_bangla` initialized successfully! Executed {$executedCount} SQL statements and secured the default administrator.";
    } catch (Exception $e) {
        $status = 'error';
        $message = "Setup Error: " . $e->getMessage();
    }
}

// Check current DB status
$dbHealth = check_db_health();
$counts = [];
if ($dbHealth['connected'] && $dbHealth['tables_found']) {
    $pdo = get_db_connection();
    if ($pdo) {
        try {
            $counts['places'] = $pdo->query("SELECT COUNT(*) FROM places")->fetchColumn();
            $counts['stories'] = $pdo->query("SELECT COUNT(*) FROM stories")->fetchColumn();
            $counts['categories'] = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
            $counts['submissions'] = $pdo->query("SELECT COUNT(*) FROM story_submissions")->fetchColumn();
            $counts['admins'] = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
        } catch (Exception $e) {
            // Ignore for health check
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup & Verification — SHADOWS OF BANGLA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bg-color: #08090D;
            --secondary-bg: #11131A;
            --card-bg: #171922;
            --accent-red: #9E1B32;
            --crimson: #651C2A;
            --fog-grey: #A6A6AD;
            --text-main: #F2F0EF;
            --text-muted: #92929C;
            --border-color: rgba(255, 255, 255, 0.08);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background-image: radial-gradient(circle at 50% 15%, rgba(158, 27, 50, 0.15), transparent 60%);
        }
        .setup-container {
            width: 100%;
            max-width: 680px;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
        }
        .brand-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .brand-title {
            font-family: 'Cinzel', serif;
            font-size: 1.8rem;
            letter-spacing: 0.15em;
            color: var(--text-main);
            margin-bottom: 0.35rem;
        }
        .brand-tagline {
            font-size: 0.9rem;
            color: var(--accent-red);
            text-transform: uppercase;
            letter-spacing: 0.2em;
        }
        .status-box {
            padding: 1rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.95rem;
        }
        .status-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #4ade80;
        }
        .status-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }
        .status-warning {
            background: rgba(234, 179, 8, 0.1);
            border: 1px solid rgba(234, 179, 8, 0.3);
            color: #facc15;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .metric-card {
            background: var(--secondary-bg);
            border: 1px solid var(--border-color);
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }
        .metric-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-red);
            font-family: 'Cinzel', serif;
        }
        .metric-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 0.25rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.9rem 1.5rem;
            background: var(--accent-red);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            letter-spacing: 0.05em;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.25s ease;
            font-size: 0.95rem;
        }
        .btn:hover {
            background: #b5223c;
            box-shadow: 0 0 20px rgba(158, 27, 50, 0.4);
        }
        .btn-outline {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-main);
            margin-top: 0.75rem;
        }
        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: none;
        }
        .info-card {
            background: var(--secondary-bg);
            border-left: 3px solid var(--accent-red);
            padding: 1rem;
            border-radius: 0 6px 6px 0;
            margin-top: 1.5rem;
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.5;
        }
        .info-card strong { color: var(--text-main); }
        .info-card code { background: rgba(0,0,0,0.4); padding: 0.15rem 0.35rem; border-radius: 4px; color: #ff99a8; }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="brand-header">
            <h1 class="brand-title">SHADOWS OF BANGLA</h1>
            <p class="brand-tagline">Database Diagnostics & Installer</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="status-box status-<?= $status ?>">
                <i class="fa-solid <?= $status === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                <div><?= htmlspecialchars($message) ?></div>
            </div>
        <?php else: ?>
            <div class="status-box <?= $dbHealth['tables_found'] ? 'status-success' : ($dbHealth['connected'] ? 'status-warning' : 'status-error') ?>">
                <i class="fa-solid <?= $dbHealth['tables_found'] ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                <div>
                    <strong>Connection Status:</strong> <?= htmlspecialchars($dbHealth['message']) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($counts)): ?>
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-number"><?= $counts['places'] ?? 0 ?></div>
                    <div class="metric-label">Places</div>
                </div>
                <div class="metric-card">
                    <div class="metric-number"><?= $counts['stories'] ?? 0 ?></div>
                    <div class="metric-label">Stories</div>
                </div>
                <div class="metric-card">
                    <div class="metric-number"><?= $counts['categories'] ?? 0 ?></div>
                    <div class="metric-label">Categories</div>
                </div>
                <div class="metric-card">
                    <div class="metric-number"><?= $counts['submissions'] ?? 0 ?></div>
                    <div class="metric-label">Submissions</div>
                </div>
                <div class="metric-card">
                    <div class="metric-number"><?= $counts['admins'] ?? 0 ?></div>
                    <div class="metric-label">Admins</div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" onsubmit="return confirm('Initialize database? This will create tables and load authentic seed data.');">
            <input type="hidden" name="action" value="run_setup">
            <button type="submit" class="btn">
                <i class="fa-solid fa-bolt"></i> <?= $dbHealth['tables_found'] ? 'Re-Import / Reset Database' : 'Install & Initialize Database' ?>
            </button>
        </form>

        <a href="../index.php" class="btn btn-outline">
            <i class="fa-solid fa-compass"></i> Go to Homepage
        </a>
        <a href="../admin/login.php" class="btn btn-outline">
            <i class="fa-solid fa-lock"></i> Go to Admin Login
        </a>

        <div class="info-card">
            <strong>Default Administrator Credentials:</strong><br>
            Username: <code>admin</code><br>
            Password: <code>AdminShadows#2026</code><br>
            <span style="font-size: 0.8rem; color: #ff99a8;">(You can change your password immediately in the Admin Dashboard)</span>
        </div>
    </div>
</body>
</html>

