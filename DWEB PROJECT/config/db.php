<?php
/**
 * Fox Lab – Database Connection (PDO) & Authentication Helpers
 * Uses prepared statements for SQL injection prevention.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_NAME', 'foxlab_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function getDBConnection(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}

/**
 * Helper: sanitize output to prevent XSS
 */
function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Helper: get base URL for asset/link references
 */
function base_url(string $path = ''): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    
    $projectRoot = $scriptDir;
    if (strpos($scriptDir, '/pages') !== false) {
        $projectRoot = dirname($scriptDir);
    }
    $projectRoot = rtrim($projectRoot, '/');
    
    return $protocol . '://' . $host . $projectRoot . '/' . ltrim($path, '/');
}

// ===== Authentication Helpers =====

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

/**
 * Get current logged-in user data (cached per request)
 */
function currentUser(): ?array {
    static $user = null;
    static $fetched = false;
    if (!$fetched) {
        $fetched = true;
        if (isLoggedIn()) {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, full_name, email, role, avatar_url, created_at FROM users WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $_SESSION['user_id']]);
            $result = $stmt->fetch();
            $user = $result ?: null;
        }
    }
    return $user;
}

/**
 * Check if current user is an admin
 */
function isAdmin(): bool {
    $user = currentUser();
    return $user !== null && ($user['role'] ?? '') === 'admin';
}

/**
 * Require admin – redirect if not admin
 */
function requireAdmin(): void {
    if (!isAdmin()) {
        $isSubPage = (basename(dirname($_SERVER['SCRIPT_FILENAME'])) === 'pages');
        header('Location: ' . ($isSubPage ? '../index.php' : 'index.php'));
        exit;
    }
}

/**
 * Require login – redirect to login page if not authenticated
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        $isSubPage = (basename(dirname($_SERVER['SCRIPT_FILENAME'])) === 'pages');
        $loginPath = $isSubPage ? 'login.php' : 'pages/login.php';
        header('Location: ' . $loginPath);
        exit;
    }
}

/**
 * Set flash message in session
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash message
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
