<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Keresési és szűrési paraméterek
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'downloads';
$order = isset($_GET['order']) ? trim($_GET['order']) : 'desc';
$user = isset($_GET['user']) ? trim($_GET['user']) : '';

// Pluginok betöltése
$plugins = loadJsonFile(PLUGINS_FILE);

// Felhasználó szerinti szűrés
if ($user === 'me' && isLoggedIn()) {
    $currentUser = getCurrentUser();
    $plugins = array_filter($plugins, function($plugin) use ($currentUser) {
        return isset($plugin['owner_id']) && $plugin['owner_id'] == $currentUser['id'];
    });
} elseif (!empty($user)) {
    $plugins = array_filter($plugins, function($plugin) use ($user) {
        return isset($plugin['owner_id']) && $plugin['owner_id'] == $user;
    });
}

// Keresés és kategória szűrés
if (!empty($search) || !empty($category)) {
    $plugins = array_filter($plugins, function($plugin) use ($search, $category) {
        $matchesSearch = empty($search) || 
            stripos($plugin['name'], $search) !== false || 
            stripos($plugin['description'], $search) !== false;
            
        $matchesCategory = empty($category) || 
            (isset($plugin['category']) && $plugin['category'] === $category);
            
        return $matchesSearch && $matchesCategory;
    });
}

// Rendezés
usort($plugins, function($a, $b) use ($sort, $order) {
    $aVal = isset($a[$sort]) ? $a[$sort] : 0;
    $bVal = isset($b[$sort]) ? $b[$sort] : 0;
    
    if ($sort === 'created_at' || $sort === 'updated_at') {
        $aVal = strtotime($aVal);
        $bVal = strtotime($bVal);
    }
    
    return $order === 'desc' ? $bVal <=> $aVal : $aVal <=> $bVal;
});

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

// Oldal címe
$pageTitle = 'Pluginok böngészése';
include 'includes/header.php';
?>

<div class="container mt-4">
    <!-- Kereső és szűrő sáv -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="plugins.php" method="GET" class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Keresés...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="category">
                        <option value="">Minden kategória</option>
                        <?php foreach ($categories as $key => $value): ?>
                            <option value="<?= $key ?>" <?= $category === $key ? 'selected' : '' ?>>
                                <?= $value ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="sort">
                        <option value="downloads" <?= $sort === 'downloads' ? 'selected' : '' ?>>Letöltések szerint</option>
                        <option value="created_at" <?= $sort === 'created_at' ? 'selected' : '' ?>>Létrehozás szerint</option>
                        <option value="updated_at" <?= $sort === 'updated_at' ? 'selected' : '' ?>>Frissítés szerint</option>
                        <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Név szerint</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="order">
                        <option value="desc" <?= $order === 'desc' ? 'selected' : '' ?>>Csökkenő</option>
                        <option value="asc" <?= $order === 'asc' ? 'selected' : '' ?>>Növekvő</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>Szűrés
                    </button>
                    <a href="plugins.php" class="btn btn-secondary">
                        <i class="fas fa-undo me-1"></i>Alaphelyzet
                    </a>
                    <?php if (isLoggedIn()): ?>
                    <a href="upload.php" class="btn btn-success float-end">
                        <i class="fas fa-upload me-1"></i>Új plugin feltöltése
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Pluginok listája -->
    <?php if (empty($plugins)): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>Nem található plugin a megadott feltételekkel.
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($plugins as $plugin): ?>
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="plugin.php?id=<?= $plugin['id'] ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($plugin['name']) ?>
                                </a>
                            </h5>
                            <h6 class="card-subtitle mb-2 text-muted">
                                <?= htmlspecialchars($categories[$plugin['category']] ?? $plugin['category']) ?>
                            </h6>
                            <p class="card-text">
                                <?= htmlspecialchars(substr($plugin['description'], 0, 150)) ?>...
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-primary me-1">
                                        <i class="fas fa-download me-1"></i><?= number_format($plugin['downloads'] ?? 0) ?>
                                    </span>
                                    <?php if (isset($plugin['rating'])): ?>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-star me-1"></i><?= number_format($plugin['rating'], 1) ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted">
                                    <?= formatDate($plugin['created_at']) ?>
                                </small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    Készítette: <?= htmlspecialchars($plugin['owner_name'] ?? 'Ismeretlen') ?>
                                </small>
                                <a href="plugin.php?id=<?= $plugin['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    Részletek
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?> 