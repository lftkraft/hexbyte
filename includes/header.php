<?php
// Biztosítjuk, hogy a munkamenet el van indítva
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
$loggedIn = isset($_SESSION['user_id']);
$currentUser = $loggedIn ? getCurrentUser() : null;

// Flash üzenetek lekérése
$flashMessages = getFlashMessages();

// Ha be van jelentkezve, de nincs felhasználói adat, kijelentkeztetjük
if ($loggedIn && !$currentUser) {
    unset($_SESSION['user_id']);
    $loggedIn = false;
    setFlashMessage('A munkameneted lejárt. Kérlek, jelentkezz be újra!', 'warning');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="hu" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - CoolLab' : 'CoolLab - Minecraft Plugin Megosztó Platform'; ?></title>
    
    <!-- Meta tagek -->
    <?php if (isset($isPluginPage) && $isPluginPage && isset($plugin)): ?>
    <meta property="og:title" content="<?= sanitize($plugin['name'] ?? '') ?> - CoolLab">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>">
    <meta property="og:description" content="<?= sanitize(substr(strip_tags($plugin['description'] ?? ''), 0, 200)) . (isset($plugin['description']) && strlen($plugin['description']) > 200 ? '...' : '') ?>">
    <?php if (!empty($plugin['image'])): ?>
    <meta property="og:image" content="<?= 'https://' . $_SERVER['HTTP_HOST'] . BASE_URL . '/' . $plugin['image'] ?>">
    <?php else: ?>
    <meta property="og:image" content="<?= 'https://' . $_SERVER['HTTP_HOST'] . BASE_URL . '/assets/images/logo.png' ?>">
    <?php endif; ?>
    <meta name="theme-color" content="#6C63FF">
    <meta name="twitter:card" content="summary_large_image">
    <?php else: ?>
    <meta property="og:title" content="HexByte - Modern Minecraft Plugin Platform">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= 'https://' . $_SERVER['HTTP_HOST'] ?>">
    <meta property="og:description" content="Fedezd fel és oszd meg Minecraft pluginjaidat a HexByte modern platformján. Csatlakozz a közösséghez!">
    <meta property="og:image" content="<?= 'https://' . $_SERVER['HTTP_HOST'] . '/assets/images/logo.png' ?>">
    <meta name="theme-color" content="#6C63FF">
    <meta name="twitter:card" content="summary_large_image">
    <?php endif; ?>
    
    <!-- Fontok -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- AOS - Animate On Scroll -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Saját CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/animations.css">
    
    <!-- Saját JavaScript -->
    <script src="<?php echo BASE_URL; ?>/assets/js/main.js" defer></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/toast-manager.js" defer></script>
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Navigáció -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <i class="fas fa-cube me-2"></i>CoolLab
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>"><i class="fas fa-home me-1"></i>Főoldal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/plugins.php"><i class="fas fa-puzzle-piece me-1"></i>Pluginok</a>
                    </li>
                    <?php if ($loggedIn && $currentUser): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/upload.php"><i class="fas fa-upload me-1"></i>Feltöltés</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/docs.php"><i class="fas fa-book me-1"></i>Dokumentáció</a>
                    </li>
                </ul>
                
                <div class="d-flex">
                    <?php if ($loggedIn && $currentUser): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-warning dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo sanitize($currentUser['username'] ?? ''); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/profile.php"><i class="fas fa-user-circle me-2"></i>Profilom</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/plugins.php?user=me"><i class="fas fa-cubes me-2"></i>Pluginjaim</a></li>
                                <?php if (isset($currentUser['role']) && $currentUser['role'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin.php"><i class="fas fa-cog me-2"></i>Admin Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Kijelentkezés</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-outline-light me-2"><i class="fas fa-sign-in-alt me-1"></i>Bejelentkezés</a>
                        <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-warning"><i class="fas fa-user-plus me-1"></i>Regisztráció</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Toast konténer -->
    <div class="toast-container"></div>

    <!-- Flash üzenetek megjelenítése -->
    <?php if (!empty($flashMessages)): ?>
    <div class="container mt-3">
        <?php foreach ($flashMessages as $type => $messages): ?>
            <?php foreach ($messages as $message): ?>
                <div class="alert alert-<?= $type ?> alert-dismissible fade show" role="alert">
                    <span class="alert-message"><?= sanitize($message) ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Fő tartalom konténer -->
    <main class="flex-grow-1"> 