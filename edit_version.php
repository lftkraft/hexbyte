<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Bejelentkezés ellenőrzése
if (!isLoggedIn()) {
    setFlashMessage('A verzió szerkesztéséhez be kell jelentkezned!', 'warning');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Plugin és verzió ID ellenőrzése
if (!isset($_GET['plugin_id']) || empty($_GET['plugin_id']) || !isset($_GET['version_id']) || empty($_GET['version_id'])) {
    setFlashMessage('Érvénytelen plugin vagy verzió azonosító!', 'danger');
    header('Location: ' . BASE_URL . '/plugins.php');
    exit;
}

$pluginId = $_GET['plugin_id'];
$versionId = $_GET['version_id'];

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
    setFlashMessage('Nincs jogosultságod a verzió szerkesztéséhez!', 'danger');
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

// Űrlap feldolgozása
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $versionNumber = trim($_POST['version_number'] ?? '');
    $downloadLink = trim($_POST['download_link'] ?? '');
    $changelog = trim($_POST['changelog'] ?? '');
    $minecraftVersionsSelected = $_POST['minecraft_versions'] ?? [];
    
    // Adatok validálása
    $errors = [];
    
    if (empty($versionNumber)) {
        $errors[] = 'A verzió száma kötelező!';
    }
    
    if (empty($downloadLink)) {
        $errors[] = 'A letöltési link megadása kötelező!';
    }
    
    if (empty($minecraftVersionsSelected)) {
        $errors[] = 'Legalább egy Minecraft verziót ki kell választani!';
    }
    
    // Ha nincs hiba, mentjük a változtatásokat
    if (empty($errors)) {
        $plugins[$pluginIndex]['versions'][$versionIndex]['version_number'] = $versionNumber;
        $plugins[$pluginIndex]['versions'][$versionIndex]['download_link'] = $downloadLink;
        $plugins[$pluginIndex]['versions'][$versionIndex]['changelog'] = $changelog;
        $plugins[$pluginIndex]['versions'][$versionIndex]['minecraft_versions'] = $minecraftVersionsSelected;
        
        // Plugin frissítési dátumának módosítása
        $plugins[$pluginIndex]['updated_at'] = date('Y-m-d H:i:s');
        
        // Fájl mentése
        if (saveJsonFile(PLUGINS_FILE, $plugins)) {
            setFlashMessage('A verzió sikeresen frissítve!', 'success');
            header('Location: ' . BASE_URL . '/plugin.php?id=' . $pluginId);
            exit;
        } else {
            $errors[] = 'Hiba történt a verzió mentése során!';
        }
    }
    
    // Hibák megjelenítése
    foreach ($errors as $error) {
        setFlashMessage($error, 'danger');
    }
}

// Minecraft verziók listája
$minecraftVersions = [
    '1.20.4', '1.20.3', '1.20.2', '1.20.1', '1.20',
    '1.19.4', '1.19.3', '1.19.2', '1.19.1', '1.19',
    '1.18.2', '1.18.1', '1.18',
    '1.17.1', '1.17',
    '1.16.5', '1.16.4', '1.16.3', '1.16.2', '1.16.1', '1.16',
    '1.15.2', '1.15.1', '1.15',
    '1.14.4', '1.14.3', '1.14.2', '1.14.1', '1.14',
    '1.13.2', '1.13.1', '1.13',
    '1.12.2', '1.12.1', '1.12'
];

$pageTitle = 'Verzió szerkesztése - ' . $plugin['name'] . ' v' . $version['version_number'];
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Verzió szerkesztése - <?= htmlspecialchars($plugin['name']) ?>
                    </h3>
                </div>
                <div class="card-body">
                    <form action="edit_version.php?plugin_id=<?= $pluginId ?>&version_id=<?= $versionId ?>" method="POST">
                        <div class="mb-3">
                            <label for="version_number" class="form-label">Verzió szám</label>
                            <input type="text" class="form-control" id="version_number" name="version_number" 
                                   value="<?= htmlspecialchars($version['version_number']) ?>" required>
                            <div class="form-text">Használj szemantikus verziószámozást (pl. 1.0.0)</div>
                        </div>

                        <div class="mb-3">
                            <label for="download_link" class="form-label">Letöltési link</label>
                            <input type="url" class="form-control" id="download_link" name="download_link" 
                                   value="<?= htmlspecialchars($version['download_link']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Minecraft verziók</label>
                            <div class="row" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($minecraftVersions as $mcVersion): ?>
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="minecraft_versions[]" 
                                                   value="<?= $mcVersion ?>" id="mc_<?= str_replace('.', '_', $mcVersion) ?>"
                                                   <?= in_array($mcVersion, $version['minecraft_versions'] ?? []) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="mc_<?= str_replace('.', '_', $mcVersion) ?>">
                                                <?= $mcVersion ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="changelog" class="form-label">Változásnapló</label>
                            <textarea class="form-control" id="changelog" name="changelog" rows="5"><?= htmlspecialchars($version['changelog'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= BASE_URL ?>/plugin.php?id=<?= $pluginId ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Vissza
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Mentés
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 