<?php

declare(strict_types=1);

require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$examId = (int) ($_POST['exam_id'] ?? 0);
$examPassword = trim((string) ($_POST['exam_password'] ?? ''));
$studentPassword = trim((string) ($_POST['student_password'] ?? ''));
$returnTo = trim((string) ($_POST['return_to'] ?? ''));

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

if ($needsExamPassword) {
    $hash = (string) $exam['access_password_hash'];
    if ($examPassword === '' || $hash === '' || !password_verify($examPassword, $hash)) {
        $_SESSION['preauth_exam_error'][$examId] = 'Incorrect exam access password.';
        header('Location: index.php');
        exit;
    }
    $_SESSION['exam_access_' . $examId] = true;
}

if ($needsRosterPassword) {
    if ($studentPassword === '') {
        $_SESSION['preauth_exam_error'][$examId] = 'Student password required.';
        header('Location: index.php');
        exit;
    }
    $stmt = db()->prepare('SELECT * FROM exam_students WHERE exam_id = ? AND access_password = ? LIMIT 1');
    $stmt->execute([$examId, $studentPassword]);
    $student = $stmt->fetch();
    if (!$student) {
        $_SESSION['preauth_exam_error'][$examId] = 'Invalid student password.';
        header('Location: index.php');
        exit;
    }
    $_SESSION['exam_roster_student_' . $examId] = (int) $student['id'];
}

if ($returnTo === '' || strpos($returnTo, '://') !== false || str_contains($returnTo, "\n") || str_contains($returnTo, "\r")) {
    $returnTo = 'student_exam.php?id=' . $examId;
}

header('Location: ' . $returnTo);
exit;
