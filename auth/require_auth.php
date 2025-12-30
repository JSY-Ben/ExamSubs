<?php

declare(strict_types=1);

require __DIR__ . '/entra.php';

entra_start_session();

if (!entra_current_user()) {
    $returnTo = $_SERVER['REQUEST_URI'] ?? '/staff/index.php';
    header('Location: /auth/login.php?return=' . urlencode($returnTo));
    exit;
}
