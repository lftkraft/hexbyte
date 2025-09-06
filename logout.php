<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Session változók törlése
$_SESSION = array();

// Cookie törlése, ha van
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Session megszüntetése
session_destroy();

// "Emlékezz rám" token törlése, ha létezik
if (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    // Token törlése a fájlból
    $tokens = loadJsonFile(TOKENS_FILE);
    foreach ($tokens as $key => $tokenData) {
        if ($tokenData['token'] === $token) {
            unset($tokens[$key]);
            break;
        }
    }
    
    // Token fájl mentése frissített adatokkal
    saveJsonFile(TOKENS_FILE, array_values($tokens));
    
    // Token cookie törlése
    setcookie('remember_token', '', time() - 3600, '/');
}

// Visszajelzés
setFlashMessage('Sikeresen kijelentkeztél!', 'success');

// Átirányítás a főoldalra
header('Location: ' . BASE_URL);
exit;
?> 