<?php

declare(strict_types=1);

require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';

$now = new DateTimeImmutable('now');

$stmt = db()->query('SELECT * FROM exams WHERE is_completed = 0 ORDER BY start_time ASC');
$exams = [];
foreach ($stmt->fetchAll() as $exam) {
    if (exam_is_active($exam, $now)) {
        $exams[] = $exam;
    }
}
$pageTitle = 'Exams Submission Portal';
$brandHref = 'index.php';
$brandText = 'Exams Submission Portal';
$logoPath = 'logo.png';
$cssPath = 'style.css';
$navActions = '<a class="btn btn-outline-secondary btn-sm" href="staff/index.php">Exam Administration</a>';
require __DIR__ . '/header.php';
?>
<main class="container py-4">
    <div class="mb-4">
        <h1 class="h3">Active Exams</h1>
        <p class="text-muted">Only exams currently open for submissions are shown.</p>
    </div>

    <?php if (count($exams) === 0): ?>
        <div class="alert alert-info">No active exams right now.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($exams as $exam): ?>
                <div class="col-12">
                    <div class="card shadow-sm h-100 exam-card">
                        <div class="card-body">
                            <h2 class="h5 mb-2"><?php echo e($exam['title']); ?></h2>
                            <p class="text-muted mb-3">
                                Window: <?php echo e(format_datetime_display($exam['start_time'])); ?> to <?php echo e(format_datetime_display($exam['end_time'])); ?>
                            </p>
                            <a class="btn btn-primary" href="student_exam.php?id=<?php echo (int) $exam['id']; ?>">
                                Submit Files
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/footer.php'; ?>
