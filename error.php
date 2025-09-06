<?php
// Alapvető konfigurálás betöltése
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Hibakód lekérése
$errorCode = isset($_GET['code']) ? intval($_GET['code']) : 404;

// Hibaüzenetek definiálása
$errors = [
    400 => [
        'title' => 'Hibás kérés',
        'message' => 'A szerver nem tudta értelmezni a kérést.',
        'icon' => 'fas fa-exclamation-triangle'
    ],
    401 => [
        'title' => 'Hozzáférés megtagadva',
        'message' => 'A kért tartalom megtekintéséhez be kell jelentkezned.',
        'icon' => 'fas fa-lock'
    ],
    403 => [
        'title' => 'Hozzáférés tiltva',
        'message' => 'Nincs jogosultságod a kért tartalom megtekintéséhez.',
        'icon' => 'fas fa-ban'
    ],
    404 => [
        'title' => 'Az oldal nem található',
        'message' => 'A keresett oldal nem található vagy már nem elérhető.',
        'icon' => 'fas fa-search'
    ],
    500 => [
        'title' => 'Szerver hiba',
        'message' => 'A szerver nem tudta teljesíteni a kérést. Kérjük, próbáld újra később.',
        'icon' => 'fas fa-bug'
    ]
];

// Ha nincs ilyen hibakód, használjuk a 404-et
if (!isset($errors[$errorCode])) {
    $errorCode = 404;
}

$error = $errors[$errorCode];

// HTTP státuszkód beállítása
http_response_code($errorCode);

// Oldal címe
$pageTitle = $error['title'];
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-page animate__animated animate__fadeIn">
                <!-- Hiba ikon -->
                <div class="error-icon mb-4">
                    <i class="<?= $error['icon'] ?> fa-5x text-warning"></i>
                </div>
                
                <!-- Hibakód -->
                <h1 class="display-1 fw-bold text-warning mb-4">
                    <?= $errorCode ?>
                </h1>
                
                <!-- Hibaüzenet -->
                <h2 class="display-6 mb-4">
                    <?= $error['title'] ?>
                </h2>
                
                <p class="lead text-light mb-5">
                    <?= $error['message'] ?>
                </p>
                
                <!-- Navigációs gombok -->
                <div class="d-flex justify-content-center gap-3">
                    <a href="javascript:history.back()" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Vissza
                    </a>
                    <a href="<?= BASE_URL ?>" class="btn btn-warning btn-lg">
                        <i class="fas fa-home me-2"></i>Főoldal
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-page {
    padding: 4rem 0;
}

.error-icon {
    animation: bounce 2s ease infinite;
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-20px);
    }
}

.text-gradient {
    background: linear-gradient(45deg, #ffc107, #ff6b6b);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    animation: gradient 8s ease infinite;
    background-size: 200% 200%;
}

@keyframes gradient {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}
</style>

<?php include 'includes/footer.php'; ?> 