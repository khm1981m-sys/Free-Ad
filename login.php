<?php
require __DIR__ . '/bootstrap.php';
require_method('POST');
$d = body();
$name = trim((string)($d['username'] ?? ''));
$pass = (string)($d['password'] ?? '');
$q = db()->prepare('SELECT id, password_hash FROM users WHERE username = ?');
$q->execute([$name]); $row = $q->fetch();
if (!$row || !password_verify($pass, $row['password_hash'])) out(['error' => 'نام کاربری یا رمز عبور نادرست است.'], 401);
session_regenerate_id(true);
$_SESSION['user_id'] = (int)$row['id'];
$_SESSION['csrf'] = bin2hex(random_bytes(32));
out(['user' => current_user()]);
