<?php
require __DIR__ . '/bootstrap.php';
require_method('POST'); check_csrf(); require_admin(); $d = body();
$id = filter_var($d['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) out(['error' => 'شناسه تبلیغ نامعتبر است.'], 422);
$q = db()->prepare('DELETE FROM ads WHERE id = ?'); $q->execute([$id]);
if (!$q->rowCount()) out(['error' => 'تبلیغ پیدا نشد.'], 404);
out(['ok' => true]);
