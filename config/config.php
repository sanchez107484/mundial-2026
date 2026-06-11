<?php
$dataDir = __DIR__ . '/../data';
$sessDir = $dataDir . '/sessions';
if (!is_dir($sessDir)) {
    @mkdir($sessDir, 0770, true);
}
if (is_dir($sessDir) && is_writable($sessDir)) {
    ini_set('session.save_path', $sessDir);
}

session_start();

define('DATA_DIR', $dataDir . '/');
define('TEAMS_FILE', DATA_DIR . 'teams.json');
define('USERS_FILE', DATA_DIR . 'users.json');
define('PREDICTIONS_FILE', DATA_DIR . 'predictions.json');
define('RESULTS_FILE', DATA_DIR . 'results.json');

$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$base = (strpos($scriptDir, 'mundial2026') !== false) ? '/mundial2026' : '';
define('BASE_URL', $base);

define('ADMIN_PIN', '2026');

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $users = readJSON(USERS_FILE);
    foreach ($users as $user) {
        if ($user['id'] === $_SESSION['user_id']) return $user;
    }
    return null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/pages/login.php');
        exit;
    }
}

function requireAdmin() {
    $user = getCurrentUser();
    if (!$user || !$user['is_admin']) {
        header('Location: ' . BASE_URL . '/pages/dashboard.php');
        exit;
    }
}
