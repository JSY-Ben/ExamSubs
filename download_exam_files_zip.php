<?php

declare(strict_types=1);

require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function build_exam_file_download_name(string $title, string $original): string
{
    $title = trim($title);
    if ($title === '') {
        return basename($original);
    }
    $ext = pathinfo($original, PATHINFO_EXTENSION);
    $safeTitle = sanitize_name_component($title);
    if ($safeTitle === '') {
        return basename($original);
    }
    return $ext !== '' ? $safeTitle . '.' . $ext : $safeTitle;
}

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

$needsExamPassword = !empty($exam['access_password_hash']);
$needsRosterPassword = !empty($exam['student_roster_enabled']) && ($exam['student_roster_mode'] ?? '') === 'password';
if ($needsExamPassword && empty($_SESSION['exam_access_' . $examId])) {
    http_response_code(403);
    echo 'Exam access password required.';
    exit;
}
if ($needsRosterPassword && empty($_SESSION['exam_roster_student_' . $examId])) {
    http_response_code(403);
    echo 'Student password required.';
    exit;
}

if ($needsRosterPassword) {
    $studentId = (int) ($_SESSION['exam_roster_student_' . $examId] ?? 0);
    if ($studentId > 0) {
        $stmt = db()->prepare(
            'INSERT INTO exam_material_downloads (exam_id, exam_student_id, downloaded_at)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE downloaded_at = VALUES(downloaded_at)'
        );
        $stmt->execute([$examId, $studentId, now_utc_string()]);
    }
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

$usedNames = [];
foreach ($files as $file) {
    $storedPath = $uploadsDir . '/' . ltrim((string) $file['stored_path'], '/');
    $realPath = realpath($storedPath);
    if (!$realPath || strpos($realPath, $uploadsRoot) !== 0 || !is_file($realPath)) {
        continue;
    }
    $entryName = build_exam_file_download_name((string) ($file['title'] ?? ''), (string) $file['original_name']);
    if ($entryName === '') {
        $entryName = 'file_' . (int) $file['id'];
    }
    if (isset($usedNames[$entryName])) {
        $base = pathinfo($entryName, PATHINFO_FILENAME);
        $ext = pathinfo($entryName, PATHINFO_EXTENSION);
        $entryName = $base . '_' . (int) $file['id'] . ($ext !== '' ? '.' . $ext : '');
    }
    $usedNames[$entryName] = true;
    $zip->addFile($realPath, $entryName);
}

$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipName . '"');
header('Content-Length: ' . (string) filesize($tmpZip));
readfile($tmpZip);
@unlink($tmpZip);
exit;
