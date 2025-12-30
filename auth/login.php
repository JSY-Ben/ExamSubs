<?php

declare(strict_types=1);

require __DIR__ . '/entra.php';

entra_start_session();

$returnTo = (string) ($_GET['return'] ?? '/staff/index.php');
$authorizeUrl = entra_build_authorize_url($returnTo);

header('Location: ' . $authorizeUrl);
exit;
