<?php
require __DIR__ . '/bootstrap.php';
$u = current_user();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
out(['user' => $u, 'csrf' => $_SESSION['csrf']]);
