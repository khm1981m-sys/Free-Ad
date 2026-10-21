<?php
// Run ONLY from the command line: php create_admin.php
// Delete this file from the public web directory after setup.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/api/bootstrap.php';
fwrite(STDOUT, "Admin username (3-30 chars, letters/numbers/_): ");
$name = trim((string)fgets(STDIN));
if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $name)) { fwrite(STDERR, "Invalid username.\n"); exit(1); }
fwrite(STDOUT, "Admin password (at least 14 chars): ");
if (strncasecmp(PHP_OS, 'WIN', 3) !== 0) {
    system('stty -echo'); $pass = rtrim((string)fgets(STDIN), "\r\n"); system('stty echo'); fwrite(STDOUT, "\n");
} else { $pass = rtrim((string)fgets(STDIN), "\r\n"); }
if (strlen($pass) < 14 || strlen($pass) > 200) { fwrite(STDERR, "Password must be 14-200 chars.\n"); exit(1); }
try {
    $q = db()->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'admin')");
    $q->execute([$name, password_hash($pass, PASSWORD_DEFAULT)]);
    fwrite(STDOUT, "Admin created. Remove create_admin.php from the server now.\n");
} catch (Throwable $e) { fwrite(STDERR, "Could not create admin: " . $e->getMessage() . "\n"); exit(1); }
