<?php
require __DIR__ . '/bootstrap.php';
require_method('POST');
$d = body();
$name = trim((string)($d['username'] ?? ''));
$pass = (string)($d['password'] ?? '');
if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $name)) out(['error' => 'نام کاربری باید ۳ تا ۳۰ حرف انگلیسی، عدد یا زیرخط باشد.'], 422);
if (strlen($pass) < 10 || strlen($pass) > 200) out(['error' => 'رمز عبور باید بین ۱۰ تا ۲۰۰ نویسه باشد.'], 422);
try {
    $q = db()->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'user')");
    $q->execute([$name, password_hash($pass, PASSWORD_DEFAULT)]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') out(['error' => 'این نام کاربری قبلاً ثبت شده است.'], 409);
    error_log($e->getMessage()); out(['error' => 'ثبت‌نام انجام نشد.'], 500);
}
session_regenerate_id(true);
$_SESSION['user_id'] = (int)db()->lastInsertId();
$_SESSION['csrf'] = bin2hex(random_bytes(32));
out(['user' => current_user()], 201);
