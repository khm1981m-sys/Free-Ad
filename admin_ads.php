<?php
require __DIR__ . '/bootstrap.php';
require_method('GET'); require_admin();
$q = db()->query("SELECT ads.id, ads.title, ads.days, ads.status, users.username FROM ads JOIN users ON users.id = ads.user_id ORDER BY ads.created_at DESC LIMIT 300");
out(['ads' => $q->fetchAll()]);
