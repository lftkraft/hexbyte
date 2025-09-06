<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Bejelentkezés ellenőrzése
if (!isLoggedIn()) {
    setFlashMessage('A profil megtekintéséhez be kell jelentkezned!', 'warning');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Felhasználó adatainak lekérése
$user = getCurrentUser();
if (!$user) {
    setFlashMessage('Hiba történt a felhasználói adatok betöltése során!', 'danger');
    header('Location: ' . BASE_URL);
    exit;
}

// Űrlap feldolgozása
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $email = trim($_POST['email'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $newPasswordConfirm = $_POST['new_password_confirm'] ?? '';
        
        $errors = [];
        
        // Email validálás
        if (empty($email)) {
            $errors[] = 'Az email cím megadása kötelező!';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Érvénytelen email cím formátum!';
        }
        
        // Ha van új jelszó megadva
        if (!empty($newPassword)) {
            if (empty($currentPassword)) {
                $errors[] = 'A jelenlegi jelszó megadása kötelező!';
            } elseif (!verifyPassword($currentPassword, $user['password'])) {
                $errors[] = 'A jelenlegi jelszó nem megfelelő!';
            }
            
            if (strlen($newPassword) < 8) {
                $errors[] = 'Az új jelszónak legalább 8 karakter hosszúnak kell lennie!';
            }
            
            if ($newPassword !== $newPasswordConfirm) {
                $errors[] = 'Az új jelszavak nem egyeznek!';
            }
        }
        
        // Ha nincs hiba, mentjük a változtatásokat
        if (empty($errors)) {
            $user['email'] = $email;
            $user['bio'] = $bio;
            
            if (!empty($newPassword)) {
                $user['password'] = hashPassword($newPassword);
            }
            
            if (saveUser($user)) {
                setFlashMessage('A profil sikeresen frissítve!', 'success');
                header('Location: ' . BASE_URL . '/profile.php');
                exit;
            } else {
                $errors[] = 'Hiba történt a profil mentése során!';
            }
        }
        
        // Hibák megjelenítése
        foreach ($errors as $error) {
            setFlashMessage($error, 'danger');
        }
    }
}

// Felhasználó pluginjainak lekérése
$plugins = loadJsonFile(PLUGINS_FILE);
$userPlugins = array_filter($plugins, function($plugin) use ($user) {
    return isset($plugin['owner_id']) && $plugin['owner_id'] == $user['id'];
});

$pageTitle = 'Profilom';
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <!-- Profil információk -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-user me-2"></i>Profil információk</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img src="<?= !empty($user['avatar']) ? $user['avatar'] : BASE_URL . '/assets/images/default-avatar.png' ?>" 
                             alt="<?= htmlspecialchars($user['username']) ?>" 
                             class="rounded-circle mb-3" 
                             style="width: 128px; height: 128px; object-fit: cover;">
                        <h5 class="mb-0"><?= htmlspecialchars($user['username']) ?></h5>
                        <p class="text-muted">
                            <?= $user['role'] === 'admin' ? '<span class="badge bg-danger">Admin</span>' : '<span class="badge bg-primary">Felhasználó</span>' ?>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Regisztrált:</h6>
                        <p class="text-muted mb-0"><?= formatDate($user['created_at']) ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Utolsó bejelentkezés:</h6>
                        <p class="text-muted mb-0"><?= formatDate($user['last_login']) ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Pluginok száma:</h6>
                        <p class="text-muted mb-0"><?= count($userPlugins) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Profil szerkesztése -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Profil szerkesztése</h4>
                </div>
                <div class="card-body">
                    <form action="profile.php" method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Felhasználónév</label>
                            <input type="text" class="form-control" id="username" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                            <div class="form-text">A felhasználónév nem módosítható.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email cím</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bemutatkozás</label>
                            <textarea class="form-control" id="bio" name="bio" rows="3"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                        </div>
                        
                        <hr>
                        
                        <h5>Jelszó módosítása</h5>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Jelenlegi jelszó</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Új jelszó</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                            <div class="form-text">Legalább 8 karakter hosszú.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password_confirm" class="form-label">Új jelszó megerősítése</label>
                            <input type="password" class="form-control" id="new_password_confirm" name="new_password_confirm">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Mentés
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Pluginok listája -->
            <div class="card mt-4">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-puzzle-piece me-2"></i>Saját pluginok</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($userPlugins)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Még nincs feltöltött pluginod.
                            <a href="<?= BASE_URL ?>/upload.php" class="alert-link">Töltsd fel az elsőt!</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Név</th>
                                        <th>Kategória</th>
                                        <th>Letöltések</th>
                                        <th>Létrehozva</th>
                                        <th>Műveletek</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($userPlugins as $plugin): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= BASE_URL ?>/plugin.php?id=<?= $plugin['id'] ?>">
                                                    <?= htmlspecialchars($plugin['name']) ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($categories[$plugin['category']] ?? $plugin['category']) ?></td>
                                            <td><?= number_format($plugin['downloads'] ?? 0) ?></td>
                                            <td><?= formatDate($plugin['created_at']) ?></td>
                                            <td>
                                                <a href="<?= BASE_URL ?>/edit_plugin.php?id=<?= $plugin['id'] ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= BASE_URL ?>/delete_plugin.php?id=<?= $plugin['id'] ?>" class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Biztosan törölni szeretnéd ezt a plugint?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 