<?php

declare(strict_types=1);

return [
    'app' => [
        'timezone' => 'UTC',
    ],
    'api' => [
        'require_api_key' => true,
        'api_key' => 'replace-with-production-api-key',
    ],
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'dbname' => 'nol_module_idm',
        'username' => 'nol_module_idm',
        'password' => 'replace-with-production-db-password',
        'charset' => 'utf8mb4',
    ],
];
