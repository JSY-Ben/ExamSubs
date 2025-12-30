<?php

declare(strict_types=1);

require __DIR__ . '/entra.php';

entra_start_session();

$state = (string) ($_GET['state'] ?? '');
$code = (string) ($_GET['code'] ?? '');
$error = (string) ($_GET['error'] ?? '');

if ($error !== '') {
    echo 'Login failed.';
    exit;
}

if ($state === '' || $state !== ($_SESSION['entra_state'] ?? '')) {
    echo 'Invalid login state.';
    exit;
}

if ($code === '') {
    echo 'Missing authorization code.';
    exit;
}

try {
    $token = entra_exchange_code($code);
    $idToken = (string) ($token['id_token'] ?? '');
    if ($idToken === '') {
        throw new RuntimeException('Missing ID token.');
    }

    $claims = entra_verify_id_token($idToken);

    $_SESSION['entra_user'] = [
        'name' => $claims['name'] ?? '',
        'email' => $claims['preferred_username'] ?? ($claims['email'] ?? ''),
        'oid' => $claims['oid'] ?? '',
    ];

    $returnTo = $_SESSION['entra_return_to'] ?? '/staff/index.php';
    header('Location: ' . $returnTo);
    exit;
} catch (Throwable $e) {
    echo 'Login failed.';
    exit;
}
