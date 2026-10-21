<?php
declare(strict_types=1);
$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'تنظیمات سرور کامل نیست. فایل api/config.php را بسازید.'], JSON_UNESCAPED_UNICODE);
    exit;
}
$config = require $configFile;
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_name('mehrsam_session');
session_set_cookie_params([
    'lifetime' => 0, 'path' => '/', 'secure' => (bool)($config['cookie_secure'] ?? $isHttps),
    'httponly' => true, 'samesite' => 'Lax'
]);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Cache-Control: no-store');
function out(array $data, int $status = 200): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function body(): array {
    $raw = file_get_contents('php://input');
    $d = json_decode($raw ?: '{}', true);
    if (!is_array($d)) out(['error' => 'درخواست نامعتبر است.'], 400);
    return $d;
}
function db(): PDO {
    static $pdo = null;
    global $config;
    if ($pdo instanceof PDO) return $pdo;
    $dsn = 'mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'] . ';charset=utf8mb4';
    try {
        $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (Throwable $e) {
        error_log('MEHRSAM DB connection failed: ' . $e->getMessage());
        out(['error' => 'اتصال به پایگاه داده برقرار نشد. تنظیمات سرور را بررسی کنید.'], 500);
    }
    return $pdo;
}
function require_method(string $method): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== $method) {
        header('Allow: ' . $method);
        out(['error' => 'روش درخواست پشتیبانی نمی‌شود.'], 405);
    }
}
function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    $q = db()->prepare('SELECT id, username, role FROM users WHERE id = ?');
    $q->execute([(int)$_SESSION['user_id']]);
    $u = $q->fetch();
    return $u ?: null;
}
function require_login(): array {
    $u = current_user();
    if (!$u) out(['error' => 'ابتدا وارد حساب کاربری شوید.'], 401);
    return $u;
}
function require_admin(): array {
    $u = require_login();
    if ($u['role'] !== 'admin') out(['error' => 'دسترسی مدیر لازم است.'], 403);
    return $u;
}
function check_csrf(): void {
    $sent = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($_SESSION['csrf']) || !is_string($sent) || !hash_equals($_SESSION['csrf'], $sent)) {
        out(['error' => 'درخواست امنیتی معتبر نیست؛ صفحه را تازه‌سازی کنید.'], 403);
    }
}
function valid_http_url(string $url): bool {
    if (strlen($url) > 2048 || !filter_var($url, FILTER_VALIDATE_URL)) return false;
    $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
    return in_array($scheme, ['http', 'https'], true);
}
