<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Verzió ID ellenőrzése
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setFlashMessage('Érvénytelen verzió azonosító!', 'danger');
    header('Location: ' . BASE_URL . '/plugins.php');
    exit;
}

$versionId = $_GET['id'];

// Plugin és verzió adatok keresése
$plugins = loadJsonFile(PLUGINS_FILE);
$plugin = null;
$version = null;

foreach ($plugins as &$p) {
    if (isset($p['versions']) && is_array($p['versions'])) {
        foreach ($p['versions'] as &$v) {
            if ($v['id'] === $versionId) {
                $plugin = &$p;
                $version = &$v;
                break 2;
            }
        }
    }
}

// Ha nem található a verzió
if (!$version) {
    setFlashMessage('A keresett verzió nem található!', 'danger');
    header('Location: ' . BASE_URL . '/plugins.php');
    exit;
}

// Letöltés naplózása
$userId = isLoggedIn() ? getCurrentUser()['id'] : null;
$ipAddress = $_SERVER['REMOTE_ADDR'];

// Letöltések számának növelése
$version['downloads'] = ($version['downloads'] ?? 0) + 1;
$plugin['downloads'] = ($plugin['downloads'] ?? 0) + 1;

// Letöltés mentése
$downloads = loadJsonFile(DOWNLOADS_FILE);
$downloads[] = [
    'plugin_id' => $plugin['id'],
    'version_id' => $version['id'],
    'user_id' => $userId,
    'ip_address' => $ipAddress,
    'downloaded_at' => date('Y-m-d H:i:s')
];

// Adatok mentése
saveJsonFile(PLUGINS_FILE, $plugins);
saveJsonFile(DOWNLOADS_FILE, $downloads);

// Visszajelzés és átirányítás
setFlashMessage('A letöltés megkezdődik...', 'success');

// Átirányítás a letöltési linkre
header('Location: ' . $version['download_link']);
exit;
?> 