<?php

declare(strict_types=1);

require __DIR__ . '/helpers.php';

$pageTitle = 'Student Help';
$brandHref = 'index.php';
$brandText = 'Exams Submission Portal';
$logoPath = 'logo.png';
$cssPath = 'style.css';
$navActions = '<a class="btn btn-outline-secondary btn-sm" href="index.php">Back to exams</a>';
require __DIR__ . '/header.php';

$instructionsPath = __DIR__ . '/STUDENT_INSTRUCTIONS.md';
$instructions = is_file($instructionsPath) ? file_get_contents($instructionsPath) : '';
$instructionsHtml = $instructions !== '' ? render_markdown_basic($instructions) : '';
?>
<main class="container py-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <?php if ($instructionsHtml !== ''): ?>
                <?php echo $instructionsHtml; ?>
            <?php else: ?>
                <div class="alert alert-warning mb-0">
                    Student instructions are not available.
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php require __DIR__ . '/footer.php'; ?>
