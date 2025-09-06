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
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    $errors = [];
    
    // Validálás
    if (empty($email)) {
        $errors[] = 'Az email cím megadása kötelező!';
    }
    
    if (empty($password)) {
        $errors[] = 'A jelszó megadása kötelező!';
    }
    
    // Ha nincs hiba, megpróbáljuk bejelentkeztetni
    if (empty($errors)) {
        $user = getUserByUsernameOrEmail($email);
        
        if ($user && verifyPassword($password, $user['password'])) {
            // Sikeres bejelentkezés
            $_SESSION['user_id'] = $user['id'];
            
            // "Emlékezz rám" funkció
            if ($remember) {
                $token = generateToken();
                $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                $tokens = loadJsonFile(TOKENS_FILE);
                $tokens[] = [
                    'user_id' => $user['id'],
                    'token' => $token,
                    'expires' => $expires
                ];
                saveJsonFile(TOKENS_FILE, $tokens);
                
                setcookie('remember_token', $token, time() + (86400 * 30), '/');
            }
            
            // Frissítjük az utolsó bejelentkezés idejét
            $user['last_login'] = date('Y-m-d H:i:s');
            saveUser($user);
            
            setFlashMessage('Sikeres bejelentkezés!', 'success');
            error_log('login.php: Sikeres bejelentkezés, flash üzenet beállítva. Átirányítás: ' . BASE_URL);
            header('Location: ' . BASE_URL);
            exit;
        } else {
            $errors[] = 'Hibás email cím vagy jelszó!';
            error_log('login.php: Hibás email cím vagy jelszó.');
        }
    }
    
    // Hibák megjelenítése
    foreach ($errors as $error) {
        setFlashMessage($error, 'danger');
        error_log('login.php: Hiba flash üzenet beállítva: ' . $error);
    }
    error_log('login.php: Hiba történt, oldal újratöltődik.');
}

$pageTitle = 'Bejelentkezés';
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Bejelentkezés</h3>
                </div>
                <div class="card-body">
                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email cím vagy felhasználónév</label>
                            <input type="text" class="form-control" id="email" name="email" 
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Jelszó</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Emlékezz rám</label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Bejelentkezés
                            </button>
                            <a href="register.php" class="btn btn-outline-secondary">
                                <i class="fas fa-user-plus me-2"></i>Regisztráció
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 