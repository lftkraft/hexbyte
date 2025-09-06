<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Admin jogosultság ellenőrzése
if (!isLoggedIn()) {
    setFlashMessage('A funkció használatához be kell jelentkezned!', 'warning');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user = getCurrentUser();
if (!$user || $user['role'] !== 'admin') {
    setFlashMessage('Nincs jogosultságod az admin panel megtekintéséhez!', 'danger');
    header('Location: ' . BASE_URL);
    exit;
}

// Statisztikák lekérése
$users = loadJsonFile(USERS_FILE);
$plugins = loadJsonFile(PLUGINS_FILE);
$downloads = loadJsonFile(DOWNLOADS_FILE);

$stats = [
    'total_users' => count($users),
    'total_plugins' => count($plugins),
    'total_downloads' => array_sum(array_column($plugins, 'downloads')),
    'active_users' => count(array_filter($users, function($u) {
        return isset($u['last_login']) && strtotime($u['last_login']) > strtotime('-30 days');
    }))
];

// Legújabb felhasználók
$latestUsers = array_slice(array_reverse($users), 0, 5);

// Legnépszerűbb pluginok
usort($plugins, function($a, $b) {
    return ($b['downloads'] ?? 0) - ($a['downloads'] ?? 0);
});
$popularPlugins = array_slice($plugins, 0, 5);

$pageTitle = 'Admin Panel';
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <!-- Statisztikák -->
        <div class="col-md-12 mb-4">
            <div class="row">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-users fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Felhasználók</h5>
                            <p class="card-text display-6"><?= number_format($stats['total_users']) ?></p>
                            <p class="text-muted mb-0">
                                Aktív: <?= number_format($stats['active_users']) ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-puzzle-piece fa-3x text-success mb-3"></i>
                            <h5 class="card-title">Pluginok</h5>
                            <p class="card-text display-6"><?= number_format($stats['total_plugins']) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-download fa-3x text-warning mb-3"></i>
                            <h5 class="card-title">Letöltések</h5>
                            <p class="card-text display-6"><?= number_format($stats['total_downloads']) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-chart-line fa-3x text-info mb-3"></i>
                            <h5 class="card-title">Aktivitás</h5>
                            <p class="card-text display-6"><?= round(($stats['active_users'] / $stats['total_users']) * 100) ?>%</p>
                            <p class="text-muted mb-0">
                                Aktív felhasználók aránya
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Legújabb felhasználók -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-clock me-2"></i>Legújabb felhasználók</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Felhasználó</th>
                                    <th>Email</th>
                                    <th>Regisztrált</th>
                                    <th>Műveletek</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($latestUsers as $u): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($u['username']) ?>
                                            <?php if ($u['role'] === 'admin'): ?>
                                                <span class="badge bg-danger">Admin</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($u['email']) ?></td>
                                        <td><?= formatDate($u['created_at']) ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/admin/edit_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-users me-1"></i>Összes felhasználó
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Legnépszerűbb pluginok -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-star me-2"></i>Legnépszerűbb pluginok</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Plugin</th>
                                    <th>Készítő</th>
                                    <th>Letöltések</th>
                                    <th>Műveletek</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($popularPlugins as $p): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= BASE_URL ?>/plugin.php?id=<?= $p['id'] ?>">
                                                <?= htmlspecialchars($p['name']) ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($p['owner_name'] ?? 'Ismeretlen') ?></td>
                                        <td><?= number_format($p['downloads'] ?? 0) ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/edit_plugin.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/delete_plugin.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Biztosan törölni szeretnéd ezt a plugint?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="<?= BASE_URL ?>/plugins.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-puzzle-piece me-1"></i>Összes plugin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 