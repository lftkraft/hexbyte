<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Bejelentkezés ellenőrzése
if (!isLoggedIn()) {
    setFlashMessage('A plugin szerkesztéséhez be kell jelentkezned!', 'warning');
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
    setFlashMessage('Nincs jogosultságod a plugin szerkesztéséhez!', 'danger');
    header('Location: ' . BASE_URL . '/plugin.php?id=' . $pluginId);
    exit;
}

// Űrlap feldolgozása
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $apiTypesSelected = $_POST['api_types'] ?? [];
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
    
    // Ha nincs hiba, mentjük a változtatásokat
    if (empty($errors)) {
        $plugins[$pluginIndex]['name'] = $name;
        $plugins[$pluginIndex]['category'] = $category;
        $plugins[$pluginIndex]['description'] = $description;
        $plugins[$pluginIndex]['api_types'] = $apiTypesSelected;
        $plugins[$pluginIndex]['source_code_url'] = $sourceCodeUrl;
        $plugins[$pluginIndex]['wiki_url'] = $wikiUrl;
        $plugins[$pluginIndex]['is_public'] = $isPublic;
        $plugins[$pluginIndex]['updated_at'] = date('Y-m-d H:i:s');
        
        // Fájl mentése
        if (saveJsonFile(PLUGINS_FILE, $plugins)) {
            setFlashMessage('A plugin sikeresen frissítve!', 'success');
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

$pageTitle = 'Plugin szerkesztése - ' . $plugin['name'];
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0"><i class="fas fa-edit me-2"></i>Plugin szerkesztése</h3>
                </div>
                <div class="card-body">
                    <form action="edit_plugin.php?id=<?= $pluginId ?>" method="POST">
                        <!-- Alap információk -->
                        <h5 class="mb-3">Alap információk</h5>
                        <div class="mb-3">
                            <label for="name" class="form-label">Plugin neve</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($plugin['name']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="category" class="form-label">Kategória</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Válassz kategóriát...</option>
                                <?php foreach ($categories as $key => $value): ?>
                                    <option value="<?= $key ?>" <?= $plugin['category'] === $key ? 'selected' : '' ?>>
                                        <?= $value ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Leírás</label>
                            <textarea class="form-control" id="description" name="description" rows="5" required><?= htmlspecialchars($plugin['description']) ?></textarea>
                            <div class="form-text">Markdown formázás támogatott.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">API típusok</label>
                            <div class="row">
                                <?php foreach ($apiTypes as $key => $value): ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="api_types[]" 
                                                   value="<?= $key ?>" id="api_<?= $key ?>"
                                                   <?= in_array($key, $plugin['api_types'] ?? []) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="api_<?= $key ?>"><?= $value ?></label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- További beállítások -->
                        <h5 class="mb-3 mt-4">További beállítások</h5>
                        <div class="mb-3">
                            <label for="source_code_url" class="form-label">Forráskód URL (opcionális)</label>
                            <input type="url" class="form-control" id="source_code_url" name="source_code_url" 
                                   value="<?= htmlspecialchars($plugin['source_code_url'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="wiki_url" class="form-label">Wiki URL (opcionális)</label>
                            <input type="url" class="form-control" id="wiki_url" name="wiki_url" 
                                   value="<?= htmlspecialchars($plugin['wiki_url'] ?? '') ?>">
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_public" name="is_public" 
                                   <?= ($plugin['is_public'] ?? true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_public">
                                Nyilvános plugin (mások is láthatják)
                            </label>
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