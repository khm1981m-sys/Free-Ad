<?php
require __DIR__ . '/bootstrap.php';
require_method('POST'); check_csrf(); require_admin(); $d = body();
$id = filter_var($d['id'] ?? null, FILTER_VALIDATE_INT);
$status = (string)($d['status'] ?? '');
if (!$id || !in_array($status, ['approved', 'pending', 'rejected'], true)) out(['error' => 'اطلاعات نامعتبر است.'], 422);
$q = db()->prepare('UPDATE ads SET status = ? WHERE id = ?'); $q->execute([$status, $id]);
if (!$q->rowCount()) out(['error' => 'تبلیغ پیدا نشد یا تغییری اعمال نشد.'], 404);
out(['ok' => true]);
