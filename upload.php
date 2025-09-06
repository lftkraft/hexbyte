<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Bejelentkezés ellenőrzése
if (!isLoggedIn()) {
    setFlashMessage('A plugin feltöltéshez be kell jelentkezned!', 'warning');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Aktuális felhasználó lekérése
$currentUser = getCurrentUser();

// Űrlap feldolgozása
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $apiTypesSelected = $_POST['api_types'] ?? [];
    $versionNumber = trim($_POST['version_number'] ?? '');
    $downloadLink = trim($_POST['download_link'] ?? '');
    $changelog = trim($_POST['changelog'] ?? '');
    $minecraftVersionsSelected = $_POST['minecraft_versions'] ?? [];
    $sourceCodeUrl = trim($_POST['source_code_url'] ?? '');
    $wikiUrl = trim($_POST['wiki_url'] ?? '');
    $isPublic = isset($_POST['is_public']) ? true : false;
    
    // Adatok validálása
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'A plugin neve kötelező!';
    }
    
    if (empty($category)) {
        $errors[] = 'A kategória kiválasztása kötelező!';
    }
    
    if (empty($description)) {
        $errors[] = 'A leírás megadása kötelező!';
    }
    
    if (empty($versionNumber)) {
        $errors[] = 'A verzió száma kötelező!';
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
            } else {
                $errors[] = 'Hiba történt a fájl feltöltése során!';
            }
        }
    }
    
    // Ha sem fájl, sem letöltési link nincs megadva
    if (!$uploadSuccess && empty($downloadLink)) {
        $errors[] = 'Plugin fájl feltöltése vagy letöltési link megadása kötelező!';
    }

    // Ha nincs hiba, mentjük a plugint
    if (empty($errors)) {
        $pluginId = uniqid('p_');
        $versionId = uniqid('v_');
        
        // Verzió objektum létrehozása
        $version = [
            'id' => $versionId,
            'version_number' => $versionNumber,
            'download_link' => $uploadSuccess ? BASE_URL . '/uploads/plugins/' . $fileName : $downloadLink,
            'changelog' => $changelog,
            'minecraft_versions' => $minecraftVersionsSelected,
            'released_at' => date('Y-m-d H:i:s'),
            'downloads' => 0
        ];
        
        // Plugin objektum létrehozása
        $plugin = [
            'id' => $pluginId,
            'name' => $name,
            'category' => $category,
            'description' => $description,
            'api_types' => $apiTypesSelected,
            'owner_id' => $currentUser['id'],
            'owner_name' => $currentUser['username'],
            'source_code_url' => $sourceCodeUrl,
            'wiki_url' => $wikiUrl,
            'is_public' => $isPublic,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'downloads' => 0,
            'versions' => [$versionId => $version]
        ];
        
        // Plugin mentése
        $plugins = loadJsonFile(PLUGINS_FILE);
        $plugins[] = $plugin;
        
        if (saveJsonFile(PLUGINS_FILE, $plugins)) {
            setFlashMessage('A plugin sikeresen feltöltve!', 'success');
            header('Location: ' . BASE_URL . '/plugin.php?id=' . $pluginId);
            exit;
        } else {
            $errors[] = 'Hiba történt a plugin mentése során!';
        }
    }
    
    // Hibák megjelenítése
    foreach ($errors as $error) {
        setFlashMessage($error, 'danger');
    }
}

// Kategóriák listája
$categories = [
    'admin' => 'Adminisztráció',
    'economy' => 'Gazdaság',
    'fun' => 'Szórakozás',
    'games' => 'Játékok',
    'protection' => 'Védelem',
    'chat' => 'Chat',
    'mechanics' => 'Játékmechanika',
    'developer' => 'Fejlesztői eszközök',
    'misc' => 'Egyéb'
];

// API típusok
$apiTypes = [
    'spigot' => 'Spigot API',
    'paper' => 'Paper API',
    'bukkit' => 'Bukkit API',
    'bungeecord' => 'BungeeCord API',
    'velocity' => 'Velocity API',
    'sponge' => 'Sponge API',
    'forge' => 'Forge API',
    'fabric' => 'Fabric API'
];

// Minecraft verziók
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

$pageTitle = 'Plugin feltöltése';
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0"><i class="fas fa-upload me-2"></i>Plugin feltöltése</h3>
                </div>
                <div class="card-body">
                    <form action="upload.php" method="POST" enctype="multipart/form-data">
                        <!-- Alap információk -->
                        <h5 class="mb-3">Alap információk</h5>
                        <div class="mb-3">
                            <label for="name" class="form-label">Plugin neve</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="category" class="form-label">Kategória</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Válassz kategóriát...</option>
                                <?php foreach ($categories as $key => $value): ?>
                                    <option value="<?= $key ?>"><?= $value ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Leírás</label>
                            <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
                            <div class="form-text">Markdown formázás támogatott.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">API típusok</label>
                            <div class="row">
                                <?php foreach ($apiTypes as $key => $value): ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="api_types[]" 
                                                   value="<?= $key ?>" id="api_<?= $key ?>">
                                            <label class="form-check-label" for="api_<?= $key ?>"><?= $value ?></label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Verzió információk -->
                        <h5 class="mb-3 mt-4">Verzió információk</h5>
                        <div class="mb-3">
                            <label for="version_number" class="form-label">Verzió szám</label>
                            <input type="text" class="form-control" id="version_number" name="version_number" 
                                   placeholder="pl. 1.0.0" required>
                            <div class="form-text">Használj szemantikus verziószámozást (pl. 1.0.0)</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Minecraft verziók</label>
                            <div class="row" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($minecraftVersions as $version): ?>
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="minecraft_versions[]" 
                                                   value="<?= $version ?>" id="mc_<?= str_replace('.', '_', $version) ?>">
                                            <label class="form-check-label" for="mc_<?= str_replace('.', '_', $version) ?>">
                                                <?= $version ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="changelog" class="form-label">Változásnapló</label>
                            <textarea class="form-control" id="changelog" name="changelog" rows="5"></textarea>
                        </div>

                        <!-- Fájl feltöltés vagy link -->
                        <div class="mb-3">
                            <label for="plugin_file" class="form-label">Plugin fájl (.jar)</label>
                            <input type="file" class="form-control" id="plugin_file" name="plugin_file" accept=".jar">
                            <div class="form-text">Maximum méret: 10MB</div>
                        </div>

                        <div class="mb-3">
                            <label for="download_link" class="form-label">Vagy külső letöltési link</label>
                            <input type="url" class="form-control" id="download_link" name="download_link" 
                                   placeholder="https://example.com/plugin.jar">
                        </div>

                        <!-- További beállítások -->
                        <h5 class="mb-3 mt-4">További beállítások</h5>
                        <div class="mb-3">
                            <label for="source_code_url" class="form-label">Forráskód URL (opcionális)</label>
                            <input type="url" class="form-control" id="source_code_url" name="source_code_url">
                        </div>

                        <div class="mb-3">
                            <label for="wiki_url" class="form-label">Wiki URL (opcionális)</label>
                            <input type="url" class="form-control" id="wiki_url" name="wiki_url">
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_public" name="is_public" checked>
                            <label class="form-check-label" for="is_public">
                                Nyilvános plugin (mások is láthatják)
                            </label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-2"></i>Plugin feltöltése
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 