<?php
// Alapvető beállítások
define('BASE_URL', '/hexbyte');
define('SITE_NAME', 'HexByte');
define('SITE_DESCRIPTION', 'Modern Minecraft Plugin Platform');

// Könyvtárak
define('ROOT_DIR', dirname(__DIR__));
define('INCLUDES_DIR', ROOT_DIR . '/includes');
define('DATA_DIR', ROOT_DIR . '/data');
define('UPLOADS_DIR', ROOT_DIR . '/uploads');
define('PLUGINS_DIR', UPLOADS_DIR . '/plugins');
define('ASSETS_DIR', ROOT_DIR . '/assets');

// JSON fájlok
define('USERS_FILE', DATA_DIR . '/users.json');
define('PLUGINS_FILE', DATA_DIR . '/plugins.json');
define('RATINGS_FILE', DATA_DIR . '/ratings.json');
define('DOWNLOADS_FILE', DATA_DIR . '/downloads.json');
define('TOKENS_FILE', DATA_DIR . '/tokens.json');

// Időzóna beállítása
date_default_timezone_set('Europe/Budapest');

// Munkamenet beállítások
ini_set('session.cookie_lifetime', 0);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Lax');

// Hibajelentés beállítása
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Feltöltési beállítások
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10 MB
define('ALLOWED_EXTENSIONS', ['jar']);

// Biztonsági beállítások
define('PASSWORD_MIN_LENGTH', 8);
define('USERNAME_MIN_LENGTH', 3);
define('USERNAME_MAX_LENGTH', 30);

// API beállítások
define('API_RATE_LIMIT', 60); // kérés/perc
define('API_TOKEN_EXPIRY', 30 * 24 * 60 * 60); // 30 nap

// Cache beállítások
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 3600); // 1 óra

// Értesítési beállítások
define('NOTIFICATIONS_ENABLED', true);
define('NOTIFICATIONS_EMAIL', 'admin@hexbyte.local');

// Verzió információ
define('VERSION', '1.0.0');
define('MIN_PHP_VERSION', '7.4.0');

// Ellenőrizzük a PHP verziót
if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) {
    die('A HexByte futtatásához PHP ' . MIN_PHP_VERSION . ' vagy újabb verzió szükséges.');
}

// Ellenőrizzük a szükséges könyvtárakat
$requiredDirs = [DATA_DIR, UPLOADS_DIR, PLUGINS_DIR];
foreach ($requiredDirs as $dir) {
    if (!file_exists($dir)) {
        if (!mkdir($dir, 0755, true)) {
            die('Nem sikerült létrehozni a következő könyvtárat: ' . $dir);
        }
    }
}

// Ellenőrizzük a JSON fájlokat
$jsonFiles = [
    USERS_FILE => [],
    PLUGINS_FILE => [],
    RATINGS_FILE => [],
    DOWNLOADS_FILE => [],
    TOKENS_FILE => []
];

foreach ($jsonFiles as $file => $defaultContent) {
    if (!file_exists($file)) {
        if (!file_put_contents($file, json_encode($defaultContent, JSON_PRETTY_PRINT))) {
            die('Nem sikerült létrehozni a következő fájlt: ' . $file);
        }
    }
}

// Segédfüggvények
function sanitize($string) {
    if ($string === null) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function formatDate($date) {
    if ($date === null) {
        return '';
    }
    return date('Y.m.d. H:i', strtotime($date));
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function isSecure() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $_SERVER['SERVER_PORT'] == 443;
}

// Flash üzenetek kezelése
function setFlashMessage($message, $type = 'info') {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    if (!isset($_SESSION['flash_messages'][$type])) {
        $_SESSION['flash_messages'][$type] = [];
    }
    $_SESSION['flash_messages'][$type][] = $message;
}

function getFlashMessages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

// JSON fájl kezelés
function loadJsonFile($file) {
    if (!file_exists($file)) {
        return [];
    }
    $content = file_get_contents($file);
    if ($content === false) {
        return [];
    }
    $data = json_decode($content, true);
    return $data !== null ? $data : [];
}

function saveJsonFile($file, $data) {
    $dir = dirname($file);
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Munkamenet inicializálása
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF védelem
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function getCsrfToken() {
    return $_SESSION['csrf_token'];
}

// Automatikus betöltés
spl_autoload_register(function ($class) {
    $file = INCLUDES_DIR . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
}); 