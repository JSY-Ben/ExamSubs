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
}

header('Location: index.php');
exit;
