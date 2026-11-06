<?php
/**
 * SHADOWS OF BANGLA — Admin Logout
 */

define('APP_INIT', true);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

logout_admin();
header("Location: login.php");
exit;

