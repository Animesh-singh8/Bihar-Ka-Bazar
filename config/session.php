<?php
// Start session with secure settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

// CSRF Protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function generateCSRFToken() {
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Session Management Functions
function setUserSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['profile_image'] = $user['profile_image'] ?? 'default-avatar.jpg';
    $_SESSION['is_active'] = $user['is_active'];
    $_SESSION['last_activity'] = time();
    
    // Regenerate session ID for security
    session_regenerate_id(true);
}

function destroyUserSession() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['is_active']) && $_SESSION['is_active'] == 1;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
    
    // Check session timeout (30 minutes)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        destroyUserSession();
        header("Location: login.php?message=session_expired");
        exit();
    }
    
    $_SESSION['last_activity'] = time();
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUsername() {
    return $_SESSION['username'] ?? '';
}

function getFullName() {
    return $_SESSION['full_name'] ?? '';
}

function getUserType() {
    return $_SESSION['user_type'] ?? '';
}

function isFarmer() {
    return getUserType() === 'farmer';
}

function isBuyer() {
    return getUserType() === 'buyer';
}

function isAdmin() {
    return getUserType() === 'admin';
}

// Remember Me Functionality
function setRememberToken($user_id) {
    $token = bin2hex(random_bytes(32));
    $expires = time() + (30 * 24 * 60 * 60); // 30 days
    
    // Store in database
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?) 
              ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id, hash('sha256', $token), date('Y-m-d H:i:s', $expires)]);
    
    // Set cookie
    setcookie('remember_token', $token, $expires, '/', '', false, true);
}

function checkRememberToken() {
    if (!isset($_COOKIE['remember_token'])) {
        return false;
    }
    
    $token = $_COOKIE['remember_token'];
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT rt.user_id, u.* FROM remember_tokens rt 
              JOIN users u ON rt.user_id = u.id 
              WHERE rt.token = ? AND rt.expires_at > NOW() AND u.is_active = 1";
    $stmt = $db->prepare($query);
    $stmt->execute([hash('sha256', $token)]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        setUserSession($user);
        return true;
    }
    
    // Invalid token, remove cookie
    setcookie('remember_token', '', time() - 3600, '/');
    return false;
}

// Auto-login with remember token if not logged in
if (!isLoggedIn() && isset($_COOKIE['remember_token'])) {
    checkRememberToken();
}

// Input Sanitization
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function sanitizeArray($array) {
    $sanitized = [];
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $sanitized[$key] = sanitizeArray($value);
        } else {
            $sanitized[$key] = sanitize($value);
        }
    }
    return $sanitized;
}

// Rate Limiting
function checkRateLimit($action, $limit = 5, $window = 300) {
    $key = $action . '_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    
    if (!isset($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }
    
    $now = time();
    
    if (!isset($_SESSION['rate_limits'][$key])) {
        $_SESSION['rate_limits'][$key] = ['count' => 1, 'start' => $now];
        return true;
    }
    
    $data = $_SESSION['rate_limits'][$key];
    
    if ($now - $data['start'] > $window) {
        $_SESSION['rate_limits'][$key] = ['count' => 1, 'start' => $now];
        return true;
    }
    
    if ($data['count'] >= $limit) {
        return false;
    }
    
    $_SESSION['rate_limits'][$key]['count']++;
    return true;
}

// Security Headers
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

setSecurityHeaders();
?>
