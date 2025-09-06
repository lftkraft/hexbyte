<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Plugin ID ellenőrzése
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setFlashMessage('Érvénytelen plugin azonosító!', 'danger');
    header('Location: ' . BASE_URL . '/plugins.php');
    exit;
}

$pluginId = $_GET['id'];

// Plugin adatok lekérése
$plugins = loadJsonFile(PLUGINS_FILE);
$plugin = null;

foreach ($plugins as $p) {
    if ($p['id'] === $pluginId) {
        $plugin = $p;
        break;
    }
}

// Ha nem található a plugin
if (!$plugin) {
    setFlashMessage('A keresett plugin nem található!', 'danger');
    header('Location: ' . BASE_URL . '/plugins.php');
    exit;
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

// Verziók rendezése (legújabb elöl)
if (isset($plugin['versions']) && is_array($plugin['versions'])) {
    uasort($plugin['versions'], function($a, $b) {
        return strtotime($b['released_at']) - strtotime($a['released_at']);
    });
}

// OpenGraph meta tagek beállítása
$isPluginPage = true;
$pageTitle = $plugin['name'];
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <!-- Plugin információk -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><?= htmlspecialchars($plugin['name']) ?></h3>
                    <?php if (isLoggedIn() && getCurrentUser()['id'] == $plugin['owner_id']): ?>
                        <div>
                            <a href="<?= BASE_URL ?>/edit_plugin.php?id=<?= $plugin['id'] ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit me-1"></i>Szerkesztés
                            </a>
                            <a href="<?= BASE_URL ?>/add_version.php?plugin_id=<?= $plugin['id'] ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-plus me-1"></i>Új verzió
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge bg-primary">
                                <?= htmlspecialchars($categories[$plugin['category']] ?? $plugin['category']) ?>
                            </span>
                            <?php foreach ($plugin['api_types'] as $api): ?>
                                <span class="badge bg-secondary">
                                    <?= htmlspecialchars($apiTypes[$api] ?? $api) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="plugin-description mb-4">
                            <?= nl2br(htmlspecialchars($plugin['description'])) ?>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-4">
                                <h5 class="mb-0"><?= number_format($plugin['downloads'] ?? 0) ?></h5>
                                <small class="text-muted">Letöltés</small>
                            </div>
                            <div class="col-4">
                                <h5 class="mb-0"><?= count($plugin['versions'] ?? []) ?></h5>
                                <small class="text-muted">Verzió</small>
                            </div>
                            <div class="col-4">
                                <h5 class="mb-0"><?= formatDate($plugin['created_at']) ?></h5>
                                <small class="text-muted">Létrehozva</small>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($plugin['source_code_url']) || !empty($plugin['wiki_url'])): ?>
                        <div class="d-flex gap-2 mb-4">
                            <?php if (!empty($plugin['source_code_url'])): ?>
                                <a href="<?= htmlspecialchars($plugin['source_code_url']) ?>" class="btn btn-outline-primary" target="_blank">
                                    <i class="fab fa-github me-1"></i>Forráskód
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($plugin['wiki_url'])): ?>
                                <a href="<?= htmlspecialchars($plugin['wiki_url']) ?>" class="btn btn-outline-info" target="_blank">
                                    <i class="fas fa-book me-1"></i>Wiki
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <img src="<?= BASE_URL ?>/assets/images/avatars/default.png" alt="<?= htmlspecialchars($plugin['owner_name']) ?>" 
                                 class="rounded-circle me-2" style="width: 32px; height: 32px;">
                            <div>
                                Készítette: <strong><?= htmlspecialchars($plugin['owner_name']) ?></strong><br>
                                <small class="text-muted">Utolsó frissítés: <?= formatDate($plugin['updated_at']) ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Verziók listája -->
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-code-branch me-2"></i>Verziók</h4>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($plugin['versions'])): ?>
                        <div class="alert alert-warning m-3">
                            Ehhez a pluginhoz még nem tartozik verzió.
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($plugin['versions'] as $version): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5 class="mb-0">
                                            <span class="text-warning">v<?= htmlspecialchars($version['version_number']) ?></span>
                                        </h5>
                                        <small class="text-muted">
                                            Kiadva: <?= formatDate($version['released_at']) ?>
                                        </small>
                                    </div>
                                    
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <?php foreach ($version['minecraft_versions'] as $mcVersion): ?>
                                            <span class="badge bg-success"><?= htmlspecialchars($mcVersion) ?></span>
                                        <?php endforeach; ?>
                                        <span class="badge bg-primary">
                                            <i class="fas fa-download me-1"></i><?= number_format($version['downloads'] ?? 0) ?>
                                        </span>
                                    </div>
                                    
                                    <?php if (!empty($version['changelog'])): ?>
                                        <div class="mb-3">
                                            <h6 class="text-muted">Változások:</h6>
                                            <pre class="changelog-pre"><?= htmlspecialchars($version['changelog']) ?></pre>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="<?= BASE_URL ?>/download.php?id=<?= $version['id'] ?>" class="btn btn-primary">
                                            <i class="fas fa-download me-1"></i>Letöltés
                                        </a>
                                        
                                        <?php if (isLoggedIn() && getCurrentUser()['id'] == $plugin['owner_id']): ?>
                                            <div>
                                                <a href="<?= BASE_URL ?>/edit_version.php?plugin_id=<?= $plugin['id'] ?>&version_id=<?= $version['id'] ?>" 
                                                   class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= BASE_URL ?>/delete_version.php?id=<?= $version['id'] ?>&project=<?= $plugin['id'] ?>" 
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Biztosan törölni szeretnéd ezt a verziót?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Oldalsáv -->
        <div class="col-md-4">
            <!-- Letöltés gomb -->
            <?php if (!empty($plugin['versions'])): ?>
                <?php $latestVersion = reset($plugin['versions']); ?>
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <h5 class="mb-3">Legújabb verzió: v<?= htmlspecialchars($latestVersion['version_number']) ?></h5>
                        <a href="<?= BASE_URL ?>/download.php?id=<?= $latestVersion['id'] ?>" class="btn btn-lg btn-success w-100">
                            <i class="fas fa-download me-2"></i>Letöltés
                        </a>
                        <div class="mt-2">
                            <small class="text-muted">
                                Minecraft verziók: <?= implode(', ', $latestVersion['minecraft_versions']) ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Hasonló pluginok -->
            <?php
            $similarPlugins = array_filter($plugins, function($p) use ($plugin) {
                return $p['id'] !== $plugin['id'] && $p['category'] === $plugin['category'];
            });
            $similarPlugins = array_slice($similarPlugins, 0, 3);
            ?>
            
            <?php if (!empty($similarPlugins)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-puzzle-piece me-2"></i>Hasonló pluginok</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach ($similarPlugins as $similar): ?>
                                <a href="<?= BASE_URL ?>/plugin.php?id=<?= $similar['id'] ?>" 
                                   class="list-group-item list-group-item-action">
                                    <h6 class="mb-1"><?= htmlspecialchars($similar['name']) ?></h6>
                                    <p class="mb-1 small text-muted">
                                        <?= substr(htmlspecialchars($similar['description']), 0, 100) ?>...
                                    </p>
                                    <small>
                                        <i class="fas fa-download me-1"></i><?= number_format($similar['downloads'] ?? 0) ?> letöltés
                                    </small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.changelog-pre {
    background-color: #2a2a2a;
    border: 1px solid #444;
    border-radius: 4px;
    padding: 10px;
    white-space: pre-wrap;
    font-family: monospace;
    font-size: 14px;
    color: #f8f9fa;
}

.plugin-description {
    line-height: 1.6;
}
</style>

<?php include 'includes/footer.php'; ?> 