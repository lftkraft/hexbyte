<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Bejelentkezés ellenőrzése
if (!isLoggedIn()) {
    setFlashMessage('A plugin törléséhez be kell jelentkezned!', 'warning');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Plugin ID ellenőrzése
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setFlashMessage('Érvénytelen plugin azonosító!', 'danger');
    header('Location: ' . BASE_URL . '/plugins.php');
    exit;
}

$pluginId = $_GET['id'];

// Plugin adatok keresése
$plugins = loadJsonFile(PLUGINS_FILE);
$pluginIndex = -1;

foreach ($plugins as $index => $plugin) {
    if ($plugin['id'] === $pluginId) {
        $pluginIndex = $index;
        break;
    }
}

// Ha nem található a plugin
if ($pluginIndex === -1) {
    setFlashMessage('A plugin nem található!', 'danger');
    header('Location: ' . BASE_URL . '/plugins.php');
    exit;
}

// Jogosultság ellenőrzése
$currentUser = getCurrentUser();
if ($currentUser['id'] != $plugins[$pluginIndex]['owner_id'] && $currentUser['role'] !== 'admin') {
    setFlashMessage('Nincs jogosultságod a plugin törléséhez!', 'danger');
    header('Location: ' . BASE_URL . '/plugin.php?id=' . $pluginId);
    exit;
}

// Megerősítés ellenőrzése
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    // Oldal címe
    $pageTitle = 'Plugin törlése';
    include 'includes/header.php';
    ?>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Plugin törlése</h4>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">Biztosan törölni szeretnéd a következő plugint?</p>
                        <h5 class="mt-3 mb-3"><?= htmlspecialchars($plugins[$pluginIndex]['name']) ?></h5>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Ez a művelet nem vonható vissza! A plugin és az összes verziója véglegesen törlődik.
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= BASE_URL ?>/plugin.php?id=<?= $pluginId ?>" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Mégsem
                            </a>
                            <a href="<?= BASE_URL ?>/delete_plugin.php?id=<?= $pluginId ?>&confirm=yes" class="btn btn-danger">
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

// Plugin törlése
unset($plugins[$pluginIndex]);
$plugins = array_values($plugins);

// Fájl mentése
if (saveJsonFile(PLUGINS_FILE, $plugins)) {
    setFlashMessage('A plugin sikeresen törölve!', 'success');
} else {
    setFlashMessage('Hiba történt a plugin törlése során!', 'danger');
}

// Átirányítás a pluginok oldalra
header('Location: ' . BASE_URL . '/plugins.php');
exit;
?> 