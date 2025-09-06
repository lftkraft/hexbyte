<?php
session_start(); // Added this line
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Ha már be van jelentkezve, átirányítjuk a főoldalra
if (isLoggedIn()) {
    header('Location: ' . BASE_URL);
    exit;
}

// Űrlap feldolgozása
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $terms = isset($_POST['terms']);
    
    $errors = [];
    
    // Validálás
    if (empty($username)) {
        $errors[] = 'A felhasználónév megadása kötelező!';
    } elseif (strlen($username) < 3 || strlen($username) > 30) {
        $errors[] = 'A felhasználónévnek 3-30 karakter hosszúnak kell lennie!';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'A felhasználónév csak betűket, számokat és alulvonást tartalmazhat!';
    }
    
    if (empty($email)) {
        $errors[] = 'Az email cím megadása kötelező!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Érvénytelen email cím formátum!';
    }
    
    if (empty($password)) {
        $errors[] = 'A jelszó megadása kötelező!';
    } elseif (strlen($password) < 8) {
        $errors[] = 'A jelszónak legalább 8 karakter hosszúnak kell lennie!';
    }
    
    if ($password !== $passwordConfirm) {
        $errors[] = 'A két jelszó nem egyezik!';
    }
    
    if (!$terms) {
        $errors[] = 'A felhasználási feltételek elfogadása kötelező!';
    }
    
    // Ellenőrizzük, hogy létezik-e már a felhasználónév vagy email
    if (empty($errors)) {
        $existingUser = getUserByUsernameOrEmail($username);
        $existingEmail = getUserByUsernameOrEmail($email);
        
        if ($existingUser) {
            $errors[] = 'Ez a felhasználónév már foglalt!';
            error_log('register.php: Felhasználónév már foglalt: ' . $username);
        }
        
        if ($existingEmail) {
            $errors[] = 'Ez az email cím már regisztrálva van!';
            error_log('register.php: Email cím már regisztrálva: ' . $email);
        }
    }
    
    // Ha nincs hiba, létrehozzuk a felhasználót
    if (empty($errors)) {
        $userId = uniqid('u_');
        $newUser = [
            'id' => $userId,
            'username' => $username,
            'email' => $email,
            'password' => hashPassword($password),
            'role' => 'user',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'last_login' => null
        ];
        
        if (saveUser($newUser)) {
            // Automatikus bejelentkeztetés
            $_SESSION['user_id'] = $userId;
            
            setFlashMessage('Sikeres regisztráció! Üdvözlünk a CoolLab közösségében!', 'success');
            error_log('register.php: Sikeres regisztráció, flash üzenet beállítva. Átirányítás: ' . BASE_URL);
            header('Location: ' . BASE_URL);
            exit;
        } else {
            $errors[] = 'Hiba történt a regisztráció során. Kérjük, próbáld újra később!';
            error_log('register.php: Hiba a felhasználó mentése során.');
        }
    }
    
    // Hibák megjelenítése
    foreach ($errors as $error) {
        setFlashMessage($error, 'danger');
        error_log('register.php: Hiba flash üzenet beállítva: ' . $error);
    }
    error_log('register.php: Hiba történt, oldal újratöltődik.');
}

$pageTitle = 'Regisztráció';
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0"><i class="fas fa-user-plus me-2"></i>Regisztráció</h3>
                </div>
                <div class="card-body">
                    <form action="register.php" method="POST" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">Felhasználónév</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                            <div class="form-text">3-30 karakter, csak betűk, számok és alulvonás.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email cím</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Jelszó</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Legalább 8 karakter hosszú.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Jelszó megerősítése</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                Elfogadom a <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">felhasználási feltételeket</a>
                            </label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Regisztráció
                            </button>
                            <a href="login.php" class="btn btn-outline-secondary">
                                <i class="fas fa-sign-in-alt me-2"></i>Bejelentkezés
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Felhasználási feltételek modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Felhasználási feltételek</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>1. Általános rendelkezések</h5>
                <p>A weboldal használatával Ön elfogadja az alábbi feltételeket.</p>
                
                <h5>2. Adatvédelem</h5>
                <p>Az Ön által megadott személyes adatokat bizalmasan kezeljük.</p>
                
                <h5>3. Felhasználói fiókok</h5>
                <p>A felhasználók felelősek a fiókjukhoz kapcsolódó minden tevékenységért.</p>
                
                <h5>4. Tartalom</h5>
                <p>A felhasználók által feltöltött tartalmakért a felhasználók vállalják a felelősséget.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bezárás</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 