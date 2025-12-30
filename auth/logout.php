<?php

declare(strict_types=1);

require __DIR__ . '/entra.php';

entra_start_session();
entra_logout();

header('Location: ../index.php');
exit;
