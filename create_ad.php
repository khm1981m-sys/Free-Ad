<?php
require __DIR__ . '/bootstrap.php';
require_method('POST'); check_csrf(); $u = require_login(); $d = body();
$title = trim((string)($d['title'] ?? ''));
$target = trim((string)($d['target_url'] ?? ''));
$image = trim((string)($d['image_url'] ?? ''));
$days = filter_var($d['days'] ?? null, FILTER_VALIDATE_INT);
if ($title === '' || mb_strlen($title) > 100) out(['error' => 'عنوان تبلیغ باید بین ۱ تا ۱۰۰ نویسه باشد.'], 422);
if (!valid_http_url($target) || !valid_http_url($image)) out(['error' => 'لینک مقصد و تصویر باید آدرس HTTP یا HTTPS معتبر باشند.'], 422);
if ($days === false || $days < 1 || $days > 30) out(['error' => 'مدت نمایش باید بین ۱ تا ۳۰ روز باشد.'], 422);
$q = db()->prepare("INSERT INTO ads (user_id, title, target_url, image_url, days, status, expires_at) VALUES (?, ?, ?, ?, ?, 'pending', DATE_ADD(UTC_TIMESTAMP(), INTERVAL ? DAY))");
$q->execute([(int)$u['id'], $title, $target, $image, $days, $days]);
out(['ok' => true, 'message' => 'تبلیغ برای بررسی مدیر ثبت شد.'], 201);
