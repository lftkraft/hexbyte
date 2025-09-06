<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Bejelentkezés ellenőrzése
if (!isLoggedIn()) {
    setFlashMessage('A verzió hozzáadásához be kell jelentkezned!', 'warning');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Plugin ID ellenőrzése
if (!isset($_GET['plugin_id']) || empty($_GET['plugin_id'])) {
    setFlashMessage('Érvénytelen plugin azonosító!', 'danger');
    header('Location: ' . BASE_URL . '/plugins.php');
    exit;
}

$pluginId = $_GET['plugin_id'];

// Plugin adatok keresése
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
    setFlashMessage('Nincs jogosultságod új verzió hozzáadásához!', 'danger');
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
    
    // Plugin fájl feltöltés ellenőrzése
    $fileName = '';
    $uploadSuccess = false;

    if (!empty($_FILES['plugin_file']['name'])) {
        $uploadFile = $_FILES['plugin_file'];
        
        // Ellenőrizzük a fájl kiterjesztését
        $allowedExtensions = ['jar'];
        $fileExtension = strtolower(pathinfo($uploadFile['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            $errors[] = 'Csak .jar fájlok tölthetők fel!';
        }
        
        // Ellenőrizzük a fájl méretét (max 10MB)
        if ($uploadFile['size'] > 10 * 1024 * 1024) {
            $errors[] = 'A fájl mérete nem haladhatja meg a 10MB-ot!';
        }
        
        // Ha nincs hiba, feltöltjük a fájlt
        if (empty($errors) && $uploadFile['error'] === UPLOAD_ERR_OK) {
            $fileName = uniqid() . '_' . basename($uploadFile['name']);
            $uploadPath = UPLOADS_DIR . '/plugins/' . $fileName;
            
            // Ellenőrizzük, hogy létezik-e a mappa
            if (!file_exists(UPLOADS_DIR . '/plugins')) {
                mkdir(UPLOADS_DIR . '/plugins', 0755, true);
            }
            
            if (move_uploaded_file($uploadFile['tmp_name'], $uploadPath)) {
                $uploadSuccess = true;
                $downloadLink = BASE_URL . '/uploads/plugins/' . $fileName;
            } else {
                $errors[] = 'Hiba történt a fájl feltöltése során!';
            }
        }
    }
    
    // Ha nincs hiba, mentjük az új verziót
    if (empty($errors)) {
        $versionId = uniqid('v_');
        
        // Új verzió adatai
        $newVersion = [
            'id' => $versionId,
            'version_number' => $versionNumber,
            'download_link' => $downloadLink,
            'changelog' => $changelog,
            'minecraft_versions' => $minecraftVersionsSelected,
            'released_at' => date('Y-m-d H:i:s'),
            'downloads' => 0
        ];
        
        // Verzió hozzáadása a pluginhoz
        if (!isset($plugins[$pluginIndex]['versions'])) {
            $plugins[$pluginIndex]['versions'] = [];
        }
        
        $plugins[$pluginIndex]['versions'][] = $newVersion;
        
        // Plugin frissítési dátumának módosítása
        $plugins[$pluginIndex]['updated_at'] = date('Y-m-d H:i:s');
        
        // Fájl mentése
        if (saveJsonFile(PLUGINS_FILE, $plugins)) {
            setFlashMessage('Az új verzió sikeresen hozzáadva!', 'success');
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

$pageTitle = 'Új verzió hozzáadása - ' . $plugin['name'];
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">
                        <i class="fas fa-plus me-2"></i>Új verzió hozzáadása - <?= htmlspecialchars($plugin['name']) ?>
                    </h3>
                </div>
                <div class="card-body">
                    <form action="add_version.php?plugin_id=<?= $pluginId ?>" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="version_number" class="form-label">Verzió szám</label>
                            <input type="text" class="form-control" id="version_number" name="version_number" 
                                   placeholder="pl. 1.0.0" required>
                            <div class="form-text">Használj szemantikus verziószámozást (pl. 1.0.0)</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Minecraft verziók</label>
                            <div class="row" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($minecraftVersions as $mcVersion): ?>
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="minecraft_versions[]" 
                                                   value="<?= $mcVersion ?>" id="mc_<?= str_replace('.', '_', $mcVersion) ?>">
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
                            <textarea class="form-control" id="changelog" name="changelog" rows="5" 
                                      placeholder="Írd le az új verzióban található változtatásokat..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="plugin_file" class="form-label">Plugin fájl (.jar)</label>
                            <input type="file" class="form-control" id="plugin_file" name="plugin_file" accept=".jar">
                            <div class="form-text">Maximum méret: 10MB</div>
                        </div>

                        <div class="mb-3">
                            <label for="download_link" class="form-label">Vagy külső letöltési link</label>
                            <input type="url" class="form-control" id="download_link" name="download_link" 
                                   placeholder="https://example.com/plugin.jar">
                            <div class="form-text">Ha nem töltesz fel fájlt, adj meg egy külső letöltési linket.</div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Vagy tölts fel egy fájlt, vagy adj meg egy külső letöltési linket.
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= BASE_URL ?>/plugin.php?id=<?= $pluginId ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Vissza
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Verzió hozzáadása
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 