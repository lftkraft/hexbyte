<?php
// Felhasználói függvények
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $users = loadJsonFile(USERS_FILE);
    foreach ($users as $user) {
        if (isset($user['id']) && $user['id'] == $_SESSION['user_id']) {
            return $user;
        }
    }
    
    // Ha nem találtuk meg a felhasználót, töröljük a session-t
    unset($_SESSION['user_id']);
    return null;
}

function getUserById($id) {
    if (empty($id)) {
        return null;
    }
    
    $users = loadJsonFile(USERS_FILE);
    foreach ($users as $user) {
        if (isset($user['id']) && $user['id'] == $id) {
            return $user;
        }
    }
    return null;
}

function getUserByUsernameOrEmail($usernameOrEmail) {
    if (empty($usernameOrEmail)) {
        return null;
    }
    
    $users = loadJsonFile(USERS_FILE);
    foreach ($users as $user) {
        if ((isset($user['username']) && $user['username'] === $usernameOrEmail) || 
            (isset($user['email']) && $user['email'] === $usernameOrEmail)) {
            return $user;
        }
    }
    return null;
}

function saveUser($userData) {
    if (empty($userData)) {
        return false;
    }
    
    $users = loadJsonFile(USERS_FILE);
    
    // Ha nincs ID, generálunk egyet
    if (!isset($userData['id'])) {
        $userData['id'] = uniqid('u_');
    }
    
    // Meglévő felhasználó frissítése vagy új hozzáadása
    $found = false;
    foreach ($users as $key => $user) {
        if (isset($user['id']) && $user['id'] === $userData['id']) {
            $users[$key] = $userData;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $users[] = $userData;
    }
    
    return saveJsonFile(USERS_FILE, $users);
}

// Plugin függvények
function getPluginById($id) {
    $plugins = loadJsonFile(PLUGINS_FILE);
    foreach ($plugins as $plugin) {
        if ($plugin['id'] === $id) {
            return $plugin;
        }
    }
    return null;
}

function savePlugin($pluginData) {
    $plugins = loadJsonFile(PLUGINS_FILE);
    
    // Ha nincs ID, generálunk egyet
    if (!isset($pluginData['id'])) {
        $pluginData['id'] = uniqid('p_');
    }
    
    // Meglévő plugin frissítése vagy új hozzáadása
    $found = false;
    foreach ($plugins as $key => $plugin) {
        if ($plugin['id'] === $pluginData['id']) {
            $plugins[$key] = $pluginData;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $plugins[] = $pluginData;
    }
    
    return saveJsonFile(PLUGINS_FILE, $plugins);
}

function deletePlugin($id) {
    $plugins = loadJsonFile(PLUGINS_FILE);
    foreach ($plugins as $key => $plugin) {
        if ($plugin['id'] === $id) {
            // Töröljük a plugin fájljait
            if (isset($plugin['versions'])) {
                foreach ($plugin['versions'] as $version) {
                    if (isset($version['file_name'])) {
                        $filePath = PLUGINS_DIR . '/' . $version['file_name'];
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                }
            }
            
            // Töröljük a plugint a listából
            unset($plugins[$key]);
            return saveJsonFile(PLUGINS_FILE, array_values($plugins));
        }
    }
    return false;
}

// Értékelés függvények
function getRatingsByPluginId($pluginId) {
    $ratings = loadJsonFile(RATINGS_FILE);
    return array_filter($ratings, function($rating) use ($pluginId) {
        return $rating['plugin_id'] === $pluginId;
    });
}

function saveRating($ratingData) {
    $ratings = loadJsonFile(RATINGS_FILE);
    
    // Ha nincs ID, generálunk egyet
    if (!isset($ratingData['id'])) {
        $ratingData['id'] = uniqid('r_');
    }
    
    // Meglévő értékelés frissítése vagy új hozzáadása
    $found = false;
    foreach ($ratings as $key => $rating) {
        if ($rating['id'] === $ratingData['id']) {
            $ratings[$key] = $ratingData;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $ratings[] = $ratingData;
    }
    
    return saveJsonFile(RATINGS_FILE, $ratings);
}

// Letöltés függvények
function logDownload($pluginId, $userId = null, $ipAddress = null) {
    $downloads = loadJsonFile(DOWNLOADS_FILE);
    
    $downloadData = [
        'id' => uniqid('d_'),
        'plugin_id' => $pluginId,
        'user_id' => $userId,
        'ip_address' => $ipAddress,
        'date' => date('Y-m-d H:i:s')
    ];
    
    $downloads[] = $downloadData;
    return saveJsonFile(DOWNLOADS_FILE, $downloads);
}

// Biztonság
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Fájl kezelés
function hasAllowedExtension($filename, $allowedExtensions) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowedExtensions);
}

function generateUniqueId($prefix = '') {
    return uniqid($prefix);
}

// Navigáció
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// Keresés és szűrés
function searchPlugins($query = '', $category = '', $sort = 'downloads', $order = 'desc') {
    $plugins = loadJsonFile(PLUGINS_FILE);
    
    // Szűrés keresési kifejezés alapján
    if (!empty($query)) {
        $plugins = array_filter($plugins, function($plugin) use ($query) {
            return stripos($plugin['name'], $query) !== false ||
                   stripos($plugin['description'], $query) !== false;
        });
    }
    
    // Szűrés kategória alapján
    if (!empty($category)) {
        $plugins = array_filter($plugins, function($plugin) use ($category) {
            return $plugin['category'] === $category;
        });
    }
    
    // Rendezés
    usort($plugins, function($a, $b) use ($sort, $order) {
        $aVal = $a[$sort] ?? 0;
        $bVal = $b[$sort] ?? 0;
        
        if ($sort === 'created_at' || $sort === 'updated_at') {
            $aVal = strtotime($aVal);
            $bVal = strtotime($bVal);
        }
        
        return $order === 'desc' ? $bVal <=> $aVal : $aVal <=> $bVal;
    });
    
    return $plugins;
}

// Értesítések
function sendNotification($userId, $message, $type = 'info') {
    if (!NOTIFICATIONS_ENABLED) {
        return false;
    }
    
    $user = getUserById($userId);
    if (!$user || empty($user['email'])) {
        return false;
    }
    
    // Email küldése (példa)
    $to = $user['email'];
    $subject = SITE_NAME . ' - Értesítés';
    $headers = 'From: ' . NOTIFICATIONS_EMAIL . "\r\n" .
               'Reply-To: ' . NOTIFICATIONS_EMAIL . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    
    return mail($to, $subject, $message, $headers);
}

// Cache kezelés
function getCache($key) {
    if (!CACHE_ENABLED) {
        return false;
    }
    
    $cacheFile = DATA_DIR . '/cache/' . md5($key) . '.cache';
    if (!file_exists($cacheFile)) {
        return false;
    }
    
    $data = file_get_contents($cacheFile);
    if ($data === false) {
        return false;
    }
    
    $data = unserialize($data);
    if ($data['expires'] < time()) {
        unlink($cacheFile);
        return false;
    }
    
    return $data['value'];
}

function setCache($key, $value, $duration = null) {
    if (!CACHE_ENABLED) {
        return false;
    }
    
    if ($duration === null) {
        $duration = CACHE_DURATION;
    }
    
    $cacheDir = DATA_DIR . '/cache';
    if (!file_exists($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    $data = serialize([
        'value' => $value,
        'expires' => time() + $duration
    ]);
    
    return file_put_contents($cacheDir . '/' . md5($key) . '.cache', $data);
}

// Jogosultság ellenőrzés
function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('A funkció használatához be kell jelentkezned!', 'warning');
        redirect('login.php');
    }
}

function requireAdmin() {
    requireLogin();
    
    $user = getCurrentUser();
    if ($user['role'] !== 'admin') {
        setFlashMessage('Nincs jogosultságod az admin funkciók használatához!', 'danger');
        redirect('index.php');
    }
}

// API függvények
function apiResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function validateApiToken($token) {
    $tokens = loadJsonFile(TOKENS_FILE);
    foreach ($tokens as $t) {
        if ($t['token'] === $token && $t['expires'] > time()) {
            return true;
        }
    }
    return false;
}

// Verzió kezelés
function compareVersions($version1, $version2) {
    return version_compare($version1, $version2);
}

function isCompatibleVersion($pluginVersion, $minecraftVersion) {
    // Példa implementáció
    return true;
} 