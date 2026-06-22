<?php
// ============================================================
//  Sultify - Database Configuration
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'sultify');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('BASE_URL', 'http://localhost/my_vibe');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('COVER_URL', BASE_URL . '/uploads/covers/');
define('SONG_URL',  BASE_URL . '/uploads/songs/');

function getDB(): PDO {
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
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

// ---- Helper functions ----

function formatDuration(int $seconds): string {
    $m = floor($seconds / 60);
    $s = $seconds % 60;
    return sprintf('%d:%02d', $m, $s);
}

function formatNumber(int $n): string {
    if ($n >= 1_000_000) return round($n / 1_000_000, 1) . 'M';
    if ($n >= 1_000)     return round($n / 1_000, 1) . 'K';
    return (string)$n;
}

function sanitize(string $val): string {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header('Location: ' . BASE_URL . $url);
    exit;
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['admin_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) redirect('/auth/login.php');
}

function requireAdmin(): void {
    if (!isAdmin()) redirect('/auth/login.php?admin=1');
}

function getCoverUrl(string $cover = null): string {
    if ($cover && file_exists(UPLOAD_PATH . $cover)) {
        return BASE_URL . '/uploads/' . $cover;
    }
    return BASE_URL . '/assets/images/default_cover.svg';
}
