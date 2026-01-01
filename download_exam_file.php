<?php

declare(strict_types=1);

require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';

$fileId = (int) ($_GET['id'] ?? 0);
if ($fileId <= 0) {
    http_response_code(400);
    echo 'Invalid file.';
    exit;
}

$stmt = db()->prepare(
    'SELECT ef.*, e.start_time, e.end_time, e.buffer_pre_minutes, e.buffer_post_minutes, e.is_completed
     FROM exam_files ef
     JOIN exams e ON e.id = ef.exam_id
     WHERE ef.id = ?'
);
$stmt->execute([$fileId]);
$file = $stmt->fetch();

if (!$file) {
    http_response_code(404);
    echo 'File not found.';
    exit;
}

$now = new DateTimeImmutable('now');
if (!exam_is_active($file, $now)) {
    http_response_code(403);
    echo 'Exam not accepting submissions.';
    exit;
}

$config = require __DIR__ . '/config.php';
$uploadsDir = rtrim($config['uploads_dir'], '/');
$storedPath = $uploadsDir . '/' . ltrim((string) $file['stored_path'], '/');
$realPath = realpath($storedPath);
$uploadsRoot = realpath($uploadsDir);

if (!$realPath || !$uploadsRoot || strpos($realPath, $uploadsRoot) !== 0 || !is_file($realPath)) {
    http_response_code(404);
    echo 'File not available.';
    exit;
}

$originalName = (string) $file['original_name'];
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($originalName) . '"');
header('Content-Length: ' . (string) filesize($realPath));
readfile($realPath);
exit;
