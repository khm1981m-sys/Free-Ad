<?php
require __DIR__ . '/bootstrap.php';
require_method('GET');
$q = db()->query("SELECT id, title, target_url, image_url, days FROM ads WHERE status = 'approved' AND expires_at > UTC_TIMESTAMP() ORDER BY created_at DESC LIMIT 100");
out(['ads' => $q->fetchAll()]);
