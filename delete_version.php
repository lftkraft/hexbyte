<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Bejelentkezés ellenőrzése
if (!isLoggedIn()) {
    setFlashMessage('A verzió törléséhez be kell jelentkezned!', 'warning');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Verzió és plugin ID ellenőrzése
if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['plugin_id']) || empty($_GET['plugin_id'])) {
    setFlashMessage('Érvénytelen verzió vagy plugin azonosító!', 'danger');
    header('Location: ' . BASE_URL . '/plugins.php');
    exit;
}

$versionId = $_GET['id'];
$pluginId = $_GET['plugin_id'];

// Plugin és verzió adatok keresése
$plugins = loadJsonFile(PLUGINS_FILE);
$plugin = null;
$pluginIndex = -1;

foreach ($plugins as $index => $p) {
    if ($p['id'] === $pluginId) {
        $plugin = $p;
        $pluginIndex = $index;
        break;
    }
}

// Ha nem található a plugin
if (!$plugin) {
    setFlashMessage('A plugin nem található!', 'danger');
    header('Location: ' . BASE_URL . '/plugins.php');
    exit;
}

// Jogosultság ellenőrzése
$currentUser = getCurrentUser();
if ($currentUser['id'] != $plugin['owner_id'] && $currentUser['role'] !== 'admin') {
    setFlashMessage('Nincs jogosultságod a verzió törléséhez!', 'danger');
    header('Location: ' . BASE_URL . '/plugin.php?id=' . $pluginId);
    exit;
}

// Verzió keresése
$version = null;
$versionIndex = -1;

if (isset($plugin['versions']) && is_array($plugin['versions'])) {
    foreach ($plugin['versions'] as $index => $v) {
        if ($v['id'] === $versionId) {
            $version = $v;
            $versionIndex = $index;
            break;
        }
    }
}

// Ha nem található a verzió
if (!$version) {
    setFlashMessage('A verzió nem található!', 'danger');
    header('Location: ' . BASE_URL . '/plugin.php?id=' . $pluginId);
    exit;
}

// Megerősítés ellenőrzése
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    // Oldal címe
    $pageTitle = 'Verzió törlése';
    include 'includes/header.php';
    ?>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Verzió törlése</h4>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">Biztosan törölni szeretnéd a következő verziót?</p>
                        <h5 class="mt-3"><?= htmlspecialchars($plugin['name']) ?></h5>
                        <h6 class="mb-3">Verzió: v<?= htmlspecialchars($version['version_number']) ?></h6>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Ez a művelet nem vonható vissza! A verzió és a hozzá tartozó fájlok véglegesen törlődnek.
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= BASE_URL ?>/plugin.php?id=<?= $pluginId ?>" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Mégsem
                            </a>
                            <a href="<?= BASE_URL ?>/delete_version.php?id=<?= $versionId ?>&plugin_id=<?= $pluginId ?>&confirm=yes" 
                               class="btn btn-danger">
                                <i class="fas fa-trash me-2"></i>Végleges törlés
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    include 'includes/footer.php';
    exit;
}

// Verzió törlése
unset($plugins[$pluginIndex]['versions'][$versionIndex]);
$plugins[$pluginIndex]['versions'] = array_values($plugins[$pluginIndex]['versions']);

// Ha ez volt az utolsó verzió, töröljük a plugint is
if (empty($plugins[$pluginIndex]['versions'])) {
    unset($plugins[$pluginIndex]);
    $plugins = array_values($plugins);
    setFlashMessage('A plugin és az utolsó verziója sikeresen törölve!', 'success');
    header('Location: ' . BASE_URL . '/plugins.php');
    exit;
}

// Fájl mentése
if (saveJsonFile(PLUGINS_FILE, $plugins)) {
    setFlashMessage('A verzió sikeresen törölve!', 'success');
} else {
    setFlashMessage('Hiba történt a verzió törlése során!', 'danger');
}

// Átirányítás a plugin oldalra
header('Location: ' . BASE_URL . '/plugin.php?id=' . $pluginId);
exit;
?> 