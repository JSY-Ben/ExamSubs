<?php

declare(strict_types=1);

date_default_timezone_set('Europe/London');

return [
    'db' => [
        'host' => '127.0.0.1',
        'name' => 'exam_portal',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'uploads_dir' => __DIR__ . '/uploads',
    'debug' => true,
    'entra' => [
        'tenant_id' => 'YOUR_TENANT_ID',
        'client_id' => 'YOUR_CLIENT_ID',
        'client_secret' => 'YOUR_CLIENT_SECRET',
        'redirect_uri' => 'https://your-domain.example.com/auth/callback.php',
        'scope' => 'openid profile email',
    ],
];
