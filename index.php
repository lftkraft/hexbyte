<?php
// Alapvető konfigurálás betöltése
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Pluginok betöltése
$plugins = loadJsonFile(PLUGINS_FILE);

// Népszerű pluginok lekérése (top 3)
usort($plugins, function($a, $b) {
    return ($b['downloads'] ?? 0) - ($a['downloads'] ?? 0);
});
$popularPlugins = array_slice($plugins, 0, 3);

// Legújabb pluginok lekérése
usort($plugins, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
$latestPlugins = array_slice($plugins, 0, 3);

// Statisztikák
$stats = [
    'total_plugins' => count($plugins),
    'total_downloads' => array_sum(array_column($plugins, 'downloads')),
    'total_users' => count(loadJsonFile(USERS_FILE)),
    'total_ratings' => count(loadJsonFile(RATINGS_FILE))
];

// Oldal fejléce
$pageTitle = 'Főoldal';
include 'includes/header.php';
?>

<!-- Hero szekció -->
<section class="hero py-5 position-relative overflow-hidden">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <h1 class="display-4 fw-bold mb-4 text-gradient">
                    Fedezd fel a Minecraft pluginok új generációját
                </h1>
                <p class="lead mb-4 text-light">
                    <!-- Removed: A HexByte egy modern platform, ahol megoszthatod és felfedezheted a legjobb Minecraft pluginokat. 
                    Csatlakozz a közösséghez és vidd új szintre a szervered! -->
                </p>
                <div class="d-flex gap-3">
                    <a href="plugins.php" class="btn btn-primary btn-lg" data-aos="fade-up" data-aos-delay="200">
                        <i class="fas fa-search me-2"></i>Pluginok böngészése
                    </a>
                    <?php if (!isLoggedIn()): ?>
                    <a href="register.php" class="btn btn-outline-light btn-lg" data-aos="fade-up" data-aos-delay="300">
                        <i class="fas fa-user-plus me-2"></i>Regisztráció
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block" data-aos="fade-left">
                <div class="position-relative">
                    <img src="assets/images/hero-illustration.svg" alt="HexByte" class="img-fluid floating-animation">
                    <div class="hero-shapes">
                        <div class="shape shape-1"></div>
                        <div class="shape shape-2"></div>
                        <div class="shape shape-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-waves">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="var(--background-light)" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
        </svg>
    </div>
</section>

<!-- Statisztikák -->
<section class="stats py-5 bg-gradient">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3" data-aos="fade-up">
                <div class="stat-card text-center p-4">
                    <i class="fas fa-cube fa-3x mb-3 text-primary"></i>
                    <h3 class="counter mb-2"><?= number_format($stats['total_plugins']) ?></h3>
                    <p class="mb-0">Plugin</p>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-card text-center p-4">
                    <i class="fas fa-download fa-3x mb-3 text-primary"></i>
                    <h3 class="counter mb-2"><?= number_format($stats['total_downloads']) ?></h3>
                    <p class="mb-0">Letöltés</p>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-card text-center p-4">
                    <i class="fas fa-users fa-3x mb-3 text-primary"></i>
                    <h3 class="counter mb-2"><?= number_format($stats['total_users']) ?></h3>
                    <p class="mb-0">Felhasználó</p>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-card text-center p-4">
                    <i class="fas fa-star fa-3x mb-3 text-primary"></i>
                    <h3 class="counter mb-2"><?= number_format($stats['total_ratings']) ?></h3>
                    <p class="mb-0">Értékelés</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Népszerű pluginok -->
<section class="popular-plugins py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="display-6 fw-bold mb-3" data-aos="fade-up">Népszerű Pluginok</h2>
            <p class="text-light" data-aos="fade-up" data-aos-delay="100">
                Fedezd fel a közösség által legjobbra értékelt és legtöbbet letöltött pluginokat
            </p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($popularPlugins as $index => $plugin): ?>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                <div class="plugin-card card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="plugin-icon me-3">
                                <i class="fas fa-puzzle-piece fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-0">
                                    <a href="plugin.php?id=<?= $plugin['id'] ?>" class="text-decoration-none">
                                        <?= sanitize($plugin['name']) ?>
                                    </a>
                                </h5>
                                <small class="text-muted">
                                    by <?= sanitize($plugin['owner_name']) ?>
                                </small>
                            </div>
                        </div>
                        
                        <p class="card-text text-light">
                            <?= substr(sanitize($plugin['description']), 0, 100) ?>...
                        </p>
                        
                        <div class="plugin-meta d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary me-2">
                                    <i class="fas fa-download me-1"></i><?= number_format($plugin['downloads']) ?>
                                </span>
                                <span class="badge bg-warning">
                                    <i class="fas fa-star me-1"></i><?= number_format($plugin['rating'], 1) ?>
                                </span>
                            </div>
                            <a href="plugin.php?id=<?= $plugin['id'] ?>" class="btn btn-sm btn-outline-primary">
                                Részletek
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="plugins.php" class="btn btn-outline-primary btn-lg" data-aos="fade-up">
                <i class="fas fa-th-list me-2"></i>Összes plugin böngészése
            </a>
        </div>
    </div>
</section>

<!-- Kategóriák -->
<section class="categories py-5 bg-gradient">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="display-6 fw-bold mb-3" data-aos="fade-up">Plugin Kategóriák</h2>
            <p class="text-light" data-aos="fade-up" data-aos-delay="100">
                Böngéssz kategóriák szerint és találd meg a számodra legmegfelelőbb pluginokat
            </p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-3" data-aos="fade-up">
                <a href="plugins.php?category=admin" class="category-card card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-shield-alt fa-3x mb-3 text-primary"></i>
                        <h5 class="card-title">Admin</h5>
                        <p class="card-text text-light">Adminisztrációs és moderációs eszközök</p>
                    </div>
                </a>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                <a href="plugins.php?category=economy" class="category-card card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-coins fa-3x mb-3 text-primary"></i>
                        <h5 class="card-title">Gazdaság</h5>
                        <p class="card-text text-light">Gazdasági rendszerek és kereskedelem</p>
                    </div>
                </a>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                <a href="plugins.php?category=fun" class="category-card card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-gamepad fa-3x mb-3 text-primary"></i>
                        <h5 class="card-title">Szórakozás</h5>
                        <p class="card-text text-light">Minijátékok és szórakoztató pluginok</p>
                    </div>
                </a>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                <a href="plugins.php?category=utility" class="category-card card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-tools fa-3x mb-3 text-primary"></i>
                        <h5 class="card-title">Segédprogramok</h5>
                        <p class="card-text text-light">Hasznos eszközök és segédprogramok</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- CTA szekció -->
<section class="cta py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <h2 class="display-6 fw-bold mb-3">Készen állsz a csatlakozásra?</h2>
                <p class="lead mb-4 text-light">
                    Regisztrálj most és kezdd el megosztani saját pluginjaidat a közösséggel!
                </p>
            </div>
            <div class="col-lg-4 text-lg-end" data-aos="fade-left">
                <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-rocket me-2"></i>Regisztráció
                </a>
                <?php else: ?>
                <a href="upload.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-cloud-upload-alt me-2"></i>Plugin feltöltése
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?> 