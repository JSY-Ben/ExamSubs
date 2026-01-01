<?php

declare(strict_types=1);

require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';

$examId = (int) ($_GET['exam_id'] ?? 0);
if ($examId <= 0) {
    http_response_code(400);
    echo 'Invalid exam.';
    exit;
}

$stmt = db()->prepare('SELECT * FROM exams WHERE id = ?');
$stmt->execute([$examId]);
$exam = $stmt->fetch();

if (!$exam) {
    http_response_code(404);
    echo 'Exam not found.';
    exit;
}

$now = new DateTimeImmutable('now');
if (!exam_is_active($exam, $now)) {
    http_response_code(403);
    echo 'Exam not accepting submissions.';
    exit;
}

$stmt = db()->prepare('SELECT * FROM exam_files WHERE exam_id = ? ORDER BY id ASC');
$stmt->execute([$examId]);
$files = $stmt->fetchAll();

if (count($files) === 0) {
    http_response_code(404);
    echo 'No exam files available.';
    exit;
}

$config = require __DIR__ . '/config.php';
$uploadsDir = rtrim($config['uploads_dir'], '/');
$uploadsRoot = realpath($uploadsDir);
if (!$uploadsRoot) {
    http_response_code(500);
    echo 'Storage not available.';
    exit;
}

$zipNameBase = sanitize_name_component((string) ($exam['exam_code'] ?? $exam['title'] ?? 'exam'));
$zipName = ($zipNameBase !== '' ? $zipNameBase : 'exam') . '_files.zip';
$tmpZip = tempnam(sys_get_temp_dir(), 'exam_zip_');
if ($tmpZip === false) {
    http_response_code(500);
    echo 'Unable to prepare download.';
    exit;
}

$zip = new ZipArchive();
if ($zip->open($tmpZip, ZipArchive::OVERWRITE) !== true) {
    @unlink($tmpZip);
    http_response_code(500);
    echo 'Unable to prepare download.';
    exit;
}

foreach ($files as $file) {
    $storedPath = $uploadsDir . '/' . ltrim((string) $file['stored_path'], '/');
    $realPath = realpath($storedPath);
    if (!$realPath || strpos($realPath, $uploadsRoot) !== 0 || !is_file($realPath)) {
        continue;
    }
    $zip->addFile($realPath, basename((string) $file['original_name']));
}

$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipName . '"');
header('Content-Length: ' . (string) filesize($tmpZip));
readfile($tmpZip);
@unlink($tmpZip);
exit;
