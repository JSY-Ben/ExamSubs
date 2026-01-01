<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$examId = (int) ($_POST['exam_id'] ?? 0);
if ($examId > 0) {
    $rosterKey = 'exam_roster_student_' . $examId;
    if (isset($_SESSION[$rosterKey])) {
        unset($_SESSION[$rosterKey]);
    }
    $pendingKey = 'pending_submission_' . $examId;
    if (isset($_SESSION[$pendingKey])) {
        unset($_SESSION[$pendingKey]);
    }
    $pendingTokensKey = 'pending_upload_tokens_' . $examId;
    if (isset($_SESSION[$pendingTokensKey])) {
        unset($_SESSION[$pendingTokensKey]);
    }
    $pendingNamesKey = 'pending_upload_names_' . $examId;
    if (isset($_SESSION[$pendingNamesKey])) {
        unset($_SESSION[$pendingNamesKey]);
    }
}

header('Location: index.php');
exit;
