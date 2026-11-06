<?php
/**
 * SHADOWS OF BANGLA — Administrator Authentication Portal
 */

define('APP_INIT', true);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// If already authenticated, redirect to dashboard
if (is_admin_logged_in()) {
    header("Location: index.php");
    exit;
}

$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrfToken)) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $identifier = trim($_POST['identifier'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $result = login_admin($identifier, $password);
        if ($result['success']) {
            header("Location: index.php");
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archivist Login — <?= e(SITE_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        body {
            background-color: #08090D;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
            background-image: radial-gradient(circle at 50% 20%, rgba(158, 27, 50, 0.2), transparent 70%);
        }
        .login-card {
            width: 100%;
            max-width: 440px;
            background: #171922;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8);
            position: relative;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div class="logo-symbol" style="margin: 0 auto 1rem; width: 48px; height: 48px; font-size: 1.35rem;">
                <i class="fa-solid fa-ghost"></i>
            </div>
            <h1 style="font-family: var(--font-serif); font-size: 1.6rem; letter-spacing: 0.1em; color: #fff; margin-bottom: 0.25rem;">
                SHADOWS OF BANGLA
            </h1>
            <span style="font-size: 0.72rem; color: var(--accent-red); text-transform: uppercase; letter-spacing: 0.2em; font-weight: 700;">
                Archivist Control Room
            </span>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-box alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div><?= e($error) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <?= csrf_field() ?>

            <div class="admin-form-group">
                <label for="identifier">Archivist Username or Email</label>
                <input type="text" id="identifier" name="identifier" class="admin-input" placeholder="admin or email..." required autofocus value="<?= e($_POST['identifier'] ?? '') ?>">
            </div>

            <div class="admin-form-group" style="margin-bottom: 1.75rem;">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="admin-input" placeholder="••••••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.9rem;">
                <i class="fa-solid fa-key"></i> Authenticate Credentials
            </button>
        </form>

        <div style="margin-top: 1.75rem; text-align: center;">
            <a href="../index.php" style="color: var(--admin-muted); font-size: 0.82rem; text-decoration: underline;">
                <i class="fa-solid fa-arrow-left"></i> Return to Public Site
            </a>
        </div>

        <div style="margin-top: 2rem; padding: 0.85rem; background: rgba(0,0,0,0.3); border-radius: 6px; font-size: 0.75rem; color: var(--admin-muted); border: 1px solid rgba(255,255,255,0.05); text-align: center;">
            Default Archivist: <code>admin</code> / <code>AdminShadows#2026</code><br>
            <a href="../config/setup.php" style="color: var(--accent-red); margin-top: 0.35rem; display: inline-block;">Database Health & Setup Assistant</a>
        </div>
    </div>
</body>
</html>

